<?php
include_once(dirname(__FILE__).'/svea_common.php');

class ControllerPaymentsveacard extends SveaCommon {
    public function index() {
        $this->load->model('checkout/order');

    	$this->data['button_confirm'] = $this->language->get('button_confirm');
        $this->data['button_back'] = $this->language->get('button_back');

        if ($this->request->get['route'] != 'checkout/guest_step_3') {
            $this->data['back'] = 'index.php?route=checkout/payment';
        } else {
            $this->data['back'] = 'index.php?rout=checkout/guest_step_2';
        }
        //Get the country
        $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
        $this->data['countryCode'] = $order_info['payment_iso_code_2'];
        $this->id = 'payment';

        if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/svea_card.tpl')) {
            $this->template = $this->config->get('config_template') . '/template/payment/svea_card.tpl';
        } else {
            $this->template = 'default/template/payment/svea_card.tpl';
        }

        $this->data['logo'] = "";
        $this->data['cardLogos'] = "<img src='admin/view/image/payment/svea_direct/MASTERCARD.png'>
                                        <img src='admin/view/image/payment/svea_direct/VISA.png'>
                                    <img src='admin/view/image/payment/svea_direct/AMEX.png'>
                                    <img src='admin/view/image/payment/svea_direct/DINERS.png'>
                                    ";
        $this->data['continue'] = 'index.php?route=payment/svea_card/redirectSvea';

        $this->load->model('checkout/coupon');
        $this->load->model('checkout/order');
        $this->load->model('payment/svea_card');
        $this->load->model('localisation/currency');
        $this->load->language('payment/svea_card');
        include(DIR_APPLICATION.'../svea/Includes.php');

        $conf = ($this->config->get('svea_card_testmode') == 1) ? (new OpencartSveaConfigTest($this->config, 'svea_card')) : new OpencartSveaConfig($this->config, 'svea_card');

        $svea = WebPay::createOrder($conf);

        //Get order information
        $order = $this->model_checkout_order->getOrder($this->session->data['order_id']);
        $currencyValue = (floatval(VERSION) >= 1.5) ? $order['currency_value'] : $order['value'];

        //Product rows
        $products = $this->cart->getProducts();
        $svea = $this->addOrderRowsToHostedServiceOrder($svea, $products, $currencyValue);

        $addons = $this->addTaxRateToAddons();
        $svea = $this->addAddonRowsToSveaOrder($svea, $addons, $currencyValue);

        $server_url = $this->setServerURL();
        $returnUrl = $server_url.'index.php?route=payment/svea_card/responseSvea';
        $callbackUrl = $server_url.'index.php?route=payment/svea_card/callbackSvea';

        $form = $svea
            ->setCountryCode($order['payment_iso_code_2'])
            ->setCurrency($this->session->data['currency'])
            ->setClientOrderNumber($this->session->data['order_id'])
            ->setOrderDate(date('c'))
        ;
        try {
            $form =  $form
                ->usePaymentMethod(PaymentMethod::KORTCERT)
                    ->setCancelUrl($returnUrl)
                    ->setCallbackUrl($callbackUrl)
                    ->setReturnUrl($returnUrl)
                    ->setCardPageLanguage(strtolower($order['language_code']))
                    ->getPaymentForm()
            ;
        }
        catch (Exception $e) {
            $this->log->write($e->getMessage());
            echo '<div class="attention">Logged Svea Error</div>';
            exit();
        }

        // 2.x below
        //Save order but Void it while order status is unsure
        //$this->model_checkout_order->addOrderHistory($this->session->data['order_id'], 0,'Sent to Svea gateway.'); // 2.x

        //print form with hidden buttons
        $fields = $form->htmlFormFieldsAsArray;

        $this->data['form_start_tag'] = $fields['form_start_tag'];
        $this->data['merchant_id'] = $fields['input_merchantId'];
        $this->data['input_message'] = $fields['input_message'];
        $this->data['input_mac'] = $fields['input_mac'];
        $this->data['input_submit'] = $fields['input_submit'];
        $this->data['form_end_tag'] = $fields['form_end_tag'];
        $this->data['submitMessage'] = $this->language->get('button_confirm');

        $this->render();

    }

    public function responseSvea(){
        $this->load->model('checkout/order');
        $this->load->model('payment/svea_card');
        $this->load->language('payment/svea_card');
        include(DIR_APPLICATION.'../svea/Includes.php');

        //Get the country
        $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
        $countryCode = $order_info['payment_iso_code_2'];

        //Testmode
        $conf = ($this->config->get('svea_card_testmode') == 1) ? (new OpencartSveaConfigTest($this->config, 'svea_card')) : new OpencartSveaConfig($this->config, 'svea_card');
        $resp = new SveaResponse($_REQUEST, $countryCode, $conf); //HostedPaymentResponse
        $response = $resp->getResponse();
        $clean_clientOrderNumber = str_replace('.err', '', $response->clientOrderNumber);//bugfix for gateway concatinating ".err" on number
        $this->session->data['order_id'] = $clean_clientOrderNumber;
        if($resp->response->resultcode !== '0'){
            if ($resp->response->accepted === 1){
                header("Location: index.php?route=checkout/success");
                flush();
            } else {
                $this->session->data['error_warning'] = $this->responseCodes($resp->response->resultcode, $resp->response->errormessage);
                $this->renderFailure($resp->response);
            }
        } else {
            $this->renderFailure($resp->response);
        }

    }

     /**
     * Update order history with status
     */
    public function callbackSvea(){
        $this->load->model('checkout/order');
        $this->load->model('payment/svea_card');
        $this->load->language('payment/svea_card');
        include(DIR_APPLICATION.'../svea/Includes.php');

        $conf = ($this->config->get('svea_card_testmode') == 1) ? (new OpencartSveaConfigTest($this->config, 'svea_card')) : new OpencartSveaConfig($this->config, 'svea_card');
        $resp = new SveaResponse($_REQUEST, 'SE', $conf); //HostedPaymentResponse. Countrycode not important on hosted payments.
        $response = $resp->getResponse();
        $clean_clientOrderNumber = str_replace('.err', '', $response->clientOrderNumber);//bugfix for gateway concatinating ".err" on number
            if ($response->accepted === 1){
                $this->model_checkout_order->confirm($clean_clientOrderNumber, $this->config->get('svea_card_order_status_id'),'Svea transactionId: '.$response->transactionId, false);
                $this->db->query("UPDATE `" . DB_PREFIX . "order` SET date_modified = NOW(), comment = 'Payment accepted. Svea transactionId: ".$response->transactionId."' WHERE order_id = '" . $clean_clientOrderNumber . "'");
                $this->db->query("INSERT INTO " . DB_PREFIX . "order_history SET order_id = '" . $clean_clientOrderNumber. "', order_status_id = '" . (int)$this->config->get('svea_card_order_status_id') . "', notify = '" . 1 . "', comment = 'Payment accepted. Svea transactionId: " . $response->transactionId . "', date_added = NOW()");

            }
    }


    private function renderFailure($rejection){
        $this->data['continue'] = 'index.php?route=checkout/cart';
		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/svea_hostedg_failure.tpl')) {
			$this->template = $this->config->get('config_template') . '/template/payment/svea_hostedg_failure.tpl';
		} else {
			$this->template = 'default/template/payment/svea_hostedg_failure.tpl';
		}


		$this->children = array(
			'common/column_right',
			'common/footer',
			'common/column_left',
			'common/header'
		);
        $this->data['text_message'] = "<br />".  $this->responseCodes($rejection->resultcode, $rejection->errormessage)."<br /><br /><br />";
        $this->data['heading_title'] = $this->language->get('error_heading');
        $this->data['footer'] = "";

        $this->data['button_continue'] = $this->language->get('button_continue');
		$this->data['button_back'] = $this->language->get('button_back');

        $this->data['continue'] = 'index.php?route=checkout/cart';
        $this->response->setOutput($this->render(TRUE), $this->config->get('config_compression'));
    }

     private function responseCodes($err,$msg = "") {
        $err = (phpversion()>= 5.3) ? $err = strstr($err, "(", TRUE) : $err = mb_strstr($err, "(", TRUE);

        $this->load->language('payment/svea_card');

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
            $server_url = $this->config->get('config_url');
            $server_name = $_SERVER['SERVER_NAME'];
            $server_port = $_SERVER['SERVER_PORT'];
            $type = substr( $server_url, 0, strpos($server_url, "//")+2 );
            $subpath = substr( $server_url, strpos($server_url, "//")+2 );
            if($server_port != "" || $server_port != "80"){
                $server_port = ":" . $server_port;
            }  else {
                $server_port = "";
            }
            $return_url = $type . $server_name . $server_port . substr( $subpath, strpos($subpath, "/") );

            return $return_url;
    }
}
?>