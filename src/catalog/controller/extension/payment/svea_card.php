<?php
include_once(dirname(__FILE__).'/svea_common.php');
require_once(DIR_APPLICATION . '../svea/config/configInclude.php');

class ControllerExtensionPaymentSveacard extends SveaCommon {
    public function index() {
        $this->load->model('checkout/order');

    	$data['button_confirm'] = $this->language->get('button_confirm');
        $data['button_back'] = $this->language->get('button_back');

        if ($this->request->get['route'] != 'checkout/guest_step_3') {
            $data['back'] = 'index.php?route=checkout/payment';
        } else {
            $data['back'] = 'index.php?rout=checkout/guest_step_2';
        }
        //Get the country
        $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
        $data['countryCode'] = $order_info['payment_iso_code_2'];
        $this->id = 'payment';


        if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/extension/payment/svea_card')) {
            $this->template = $this->config->get('config_template') . '/template/extension/payment/svea_card';
        } else {
            $this->template = 'default/template/extension/payment/svea_card';
        }

        $data['logo'] = "";

        $data['cardLogos']  = "";
        $svea_card_logos = $this->config->get('payment_svea_card_logos');
        foreach ($svea_card_logos as $logo) {
            $data['cardLogos']  .= "<img src='admin/view/image/payment/svea_direct/" . $logo . "'>";
        }

        $data['continue'] = 'index.php?route=extension/payment/svea_card/redirectSvea';

        $this->load->model('checkout/order');
        $this->load->model('extension/payment/svea_card');
        $this->load->model('localisation/currency');
        $this->load->language('extension/payment/svea_card');

        $conf = ($this->config->get('payment_svea_card_testmode') == 1) ? (new OpencartSveaConfigTest($this->config, 'payment_svea_card')) : new OpencartSveaConfig($this->config, 'payment_svea_card');

        $svea = \Svea\WebPay\WebPay::createOrder($conf);

        //Get order information
        $order = $this->model_checkout_order->getOrder($this->session->data['order_id']);
        $currencyValue = $order['currency_value'];

        //Product rows
        $products = $this->cart->getProducts();
        $svea = $this->addOrderRowsToHostedServiceOrder($svea, $products, $currencyValue);

        $addons = $this->addTaxRateToAddons();
        $svea = $this->addAddonRowsToSveaOrder($svea, $addons, $currencyValue);

        $server_url = $this->setServerURL();
        $returnUrl = $server_url.'index.php?route=extension/payment/svea_card/responseSvea';
        $callbackUrl = $server_url.'index.php?route=extension/payment/svea_card/callbackSvea';
        // $callbackUrl = 'http://194.14.250.189:8080/modulerDev/Opencart/2/index.php?route=payment/svea_card/callbackSvea'; //svea test

        switch ($order['payment_iso_code_2']) {
            case "DE":
                $payPageLanguage = "de";
                break;
            case "NL":
                $payPageLanguage = "nl";
                break;
            case "SE":
                $payPageLanguage = "sv";
                break;
            case "NO":
                $payPageLanguage = "no";
                break;
            case "DK":
                $payPageLanguage = "da";
                break;
            case "FI":
                $payPageLanguage = "fi";
                break;
            default:
                $payPageLanguage = "en";
                break;
        }

        $form = $svea
            ->setCountryCode($order['payment_iso_code_2'])
            ->setCurrency($this->session->data['currency'])
            ->setClientOrderNumber($this->session->data['order_id'])
            ->setOrderDate(date('c'))
        ;
        $this->log->write($order['payment_iso_code_2']);
        try {
            $form =  $form
                ->usePaymentMethod(\Svea\WebPay\Constant\PaymentMethod::SVEACARDPAY)
                    ->setCancelUrl($returnUrl)
                    ->setCallbackUrl($callbackUrl)
                    ->setReturnUrl($returnUrl)
                    ->setCardPageLanguage($payPageLanguage)
                    ->getPaymentForm()
            ;
        }
        catch (Exception $e) {
            $this->log->write($e->getMessage());
            echo '<div class="attention">Logged Svea Error</div>';
            exit();

        }

        //Save order but Void it while order status is unsure
        $this->model_checkout_order->addOrderHistory($this->session->data['order_id'], 0,'Sent to Svea gateway.');

        //print form with hidden buttons
        $fields = $form->htmlFormFieldsAsArray;
        $data['form_start_tag'] = $fields['form_start_tag'];
        $data['merchant_id'] = $fields['input_merchantId'];
        $data['input_message'] = $fields['input_message'];
        $data['input_mac'] = $fields['input_mac'];
        $data['input_submit'] = $fields['input_submit'];
        $data['form_end_tag'] = $fields['form_end_tag'];
        $data['submitMessage'] = $this->language->get('button_confirm');

        //$this->render();
        if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/extension/payment/svea_card')) {
            return $this->load->view($this->config->get('config_template') . '/template/extension/payment/svea_card', $data);
        } elseif(floatval(VERSION) >= 2.2) {
            return $this->load->view('extension/payment/svea_card', $data);
        } else {
            return $this->load->view('default/template/extension/payment/svea_card', $data);
        }

    }
    /**
     * This redirects the customer depending on ok or not from Svea
     */
    public function responseSvea(){
        $this->load->model('checkout/order');
        $this->load->model('extension/payment/svea_card');

        //Get the country
        $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
        $countryCode = $order_info['payment_iso_code_2'];

        $conf = ($this->config->get('payment_svea_card_testmode') == 1) ? (new OpencartSveaConfigTest($this->config, 'payment_svea_card')) : new OpencartSveaConfig($this->config, 'payment_svea_card');
        $resp = new \Svea\WebPay\Response\SveaResponse($_REQUEST, $countryCode, $conf); //HostedPaymentResponse
        $response = $resp->getResponse();
        $clean_clientOrderNumber = str_replace('.err', '', $response->clientOrderNumber);//bugfix for gateway concatinating ".err" on number
        if($response->resultcode !== '0'){
            if ($response->accepted === 1){
                if($this->session->data['comment'] != "") {
                    $this->model_checkout_order->addOrderHistory($this->session->data['order_id'], $this->config->get('config_order_status_id'), 'Customer comment: ' . $this->session->data['comment']);
                }
                   //sets orderhistory
                $this->model_checkout_order->addOrderHistory($clean_clientOrderNumber,$this->config->get('config_order_status_id'),'Svea transactionId: '.$response->transactionId, false);
                $this->response->redirect($this->url->link('checkout/success', '','SSL'));
            }else{
//                $error = $this->responseCodes($response->resultcode, $response->errormessage);
////                $this->model_checkout_order->addOrderHistory($clean_clientOrderNumber,10,$error,FALSE);
//                $this->model_checkout_order->addOrderHistory($clean_clientOrderNumber,0,$error,FALSE);//void it. Won't show upp in order history, but won't cause trouble
//                //adds comments to edit order comment field to use when edit order
//                $this->db->query("UPDATE `" . DB_PREFIX . "order` SET date_modified = NOW(), comment = 'Payment failed. ".$error);

                $this->renderFailure($response);
            }
        }else{
            $this->renderFailure($response);
        }

    }
    /**
     * Update order history with status
     */
    public function callbackSvea(){
        $this->load->model('checkout/order');
        $this->load->model('extension/payment/svea_card');
        $this->load->language('payment/svea_card');

        $conf = ($this->config->get('payment_svea_card_testmode') == 1) ? (new OpencartSveaConfigTest($this->config, 'payment_svea_card')) : new OpencartSveaConfig($this->config, 'payment_svea_card');
        $resp = new \Svea\WebPay\Response\SveaResponse($_REQUEST, 'SE', $conf); //HostedPaymentResponse. Countrycode not important on hosted payments.
        $response = $resp->getResponse();
        $clean_clientOrderNumber = str_replace('.err', '', $response->clientOrderNumber);//bugfix for gateway concatinating ".err" on number
        $clean_clientOrderNumber = str_replace('annelitest', '',$clean_clientOrderNumber);//bugfix for gateway concatinating ".err" on number

            if ($response->accepted === 1){
                $this->model_checkout_order->addOrderHistory($clean_clientOrderNumber,$this->config->get('config_order_status_id'),'Svea transactionId: '.$response->transactionId,true);
            }else{
                $error = $this->responseCodes($response->resultcode, $response->errormessage);
                  $this->model_checkout_order->addOrderHistory($clean_clientOrderNumber,0,$error,FALSE);//void it. Won't show upp in order history, but won't cause trouble
                //adds comments to edit order comment field to use when edit order
                $this->db->query("UPDATE `" . DB_PREFIX . "order` SET date_modified = NOW(), comment = 'Payment failed. ".$error);
            }
    }

    private function renderFailure($rejection){
        $this->session->data['error'] = $this->responseCodes($rejection->resultcode, $rejection->errormessage);
        $this->response->redirect($this->url->link('checkout/checkout', 'error=' . $this->responseCodes($rejection->resultcode, $rejection->errormessage),'SSL'));

    }

     private function responseCodes($err,$msg = "") {
        $err = (phpversion()>= 5.3) ? $err = strstr($err, "(", TRUE) : $err = mb_strstr($err, "(", TRUE);

        $this->load->language('extension/payment/svea_card');

        $definition = $this->language->get("response_$err");

        if (preg_match("/^response/", $definition))
             $definition = $this->language->get("response_error"). " $msg";

        return $definition;
    }

    private function getLogo($countryCode){

        switch ($countryCode){
            case "SE": $country = "swedish";    break;
            case "NO": $country = "norwegian";  break;
            case "DK": $country = "danish";     break;
            case "FI": $country = "finnish";    break;
            case "NL": $country = "dutch";      break;
            case "DE": $country = "german";     break;
            default:   $country = "english";    break;
        }

        return $country;
    }

    /**
     * Gets the current server name, adds the path from the server url settings (for installs below server root)
     * this aims to accommodate sites that rewrite the server name dynamically on i.e. user language change
     * Also adds server port if exists. (e.g. :8080)
     */
    private function setServerURL() {
        if ($this->config->get('config_secure')) {
            $server_url = $this->config->get('config_ssl');
        } else {
            $server_url = $this->config->get('config_url');
        }

        $server_name = $_SERVER['SERVER_NAME'];
        $server_port = $_SERVER['SERVER_PORT'];
        $type = substr( $server_url, 0, strpos($server_url, "//")+2 );
        $subpath = substr( $server_url, strpos($server_url, "//")+2 );
        if($server_port != "" || $server_port != "80" || $server_port != "443"){
            $server_port = ":" . $server_port;
        }  else {
            $server_port = "";
        }
        $return_url = $type . $server_name . $server_port . substr( $subpath, strpos($subpath, "/") );
        return $return_url;
    }
}
