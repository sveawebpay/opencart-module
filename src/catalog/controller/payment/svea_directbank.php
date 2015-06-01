<?php
include_once(dirname(__FILE__).'/svea_common.php');

class ControllerPaymentsveadirectbank extends SveaCommon {
    public function index() {
        $this->load->model('checkout/order');
    	//$data['button_confirm'] = $this->language->get('button_confirm');
    	$data['button_continue'] = $this->language->get('button_continue');
        $data['button_back'] = $this->language->get('button_back');

        if ($this->request->get['route'] != 'checkout/guest_step_3') {
                $data['back'] = 'index.php?route=checkout/payment';
        } else {
                $data['back'] = 'index.php?rout=checkout/guest_step_2';
        }
        $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
        $data['countryCode'] = $order_info['payment_iso_code_2'];
        $this->id = 'payment';


        if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/svea_directbank.tpl')) {
                $this->template = $this->config->get('config_template') . '/template/payment/svea_directbank.tpl';
        } else {
                $this->template = 'default/template/payment/svea_directbank.tpl';
        }

        $data['logo'] = "";
        $data['svea_banks_base'] = "admin/view/image/payment/svea_direct/";


        /*
         **get my methods, present page
         */
        $this->load->language('payment/svea_directbank');
        include(DIR_APPLICATION.'../svea/Includes.php');

       //Testmode
        $conf = ($this->config->get('svea_directbank_testmode') == 1) ? (new OpencartSveaConfigTest($this->config)) : new OpencartSveaConfig($this->config);
        try {
            $svea = WebPay::getPaymentMethods($conf);
            $data['sveaMethods'] = $svea
            ->setContryCode($order_info['payment_iso_code_2'])
            ->doRequest();
        } catch (Exception $e) {
            $this->log->write($e->getMessage());
            $response = array("error" => $this->responseCodes(0,$e->getMessage()));
            echo '<div class="attention">Svea '.$this->responseCodes(0,$e->getMessage()).'</div>';
            exit();
        }


        $data['continue'] = 'index.php?route=payment/svea_directbank/redirectSvea';

        if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/svea_directbank.tpl')) {
                return $this->load->view($this->config->get('config_template') . '/template/payment/svea_directbank.tpl', $data);
        } else {
                return $this->load->view('default/template/payment/svea_directbank.tpl', $data);
        }


    }

    public function redirectSvea(){

        $this->load->model('checkout/coupon');
        $this->load->model('checkout/order');
        $this->load->model('payment/svea_directbank');
        $this->load->model('localisation/currency');
        $this->load->language('payment/svea_directbank');
        include(DIR_APPLICATION.'../svea/Includes.php');

       //Testmode
        $conf = ($this->config->get('svea_directbank_testmode') == 1) ? (new OpencartSveaConfigTest($this->config)) : new OpencartSveaConfig($this->config);
        $svea = WebPay::createOrder($conf);

          //Get order information
        $order = $this->model_checkout_order->getOrder($this->session->data['order_id']);
        $currencyValue = 1.00000000;
        if (floatval(VERSION) >= 1.5) {
             $currencyValue = $order['currency_value'];
         }else{
             $currencyValue = $order['value'];
         }
         //Product rows
        $products = $this->cart->getProducts();

        //Product rows
        foreach($products as $product){
             $productPriceExVat  = $product['price'] * $currencyValue;
            $taxPercent = 0;
            //Get the tax, difference in version 1.4.x
            if(floatval(VERSION) >= 1.5){
                $tax = $this->tax->getRates($product['price'], $product['tax_class_id']);
                foreach ($tax as $key => $value) {
                    $taxPercent = $value['rate'];
                }
            }  else {
                 $taxPercent = $this->tax->getRate($product['tax_class_id']);
            }

            $svea = $svea
                    ->addOrderRow(Item::orderRow()
                        ->setQuantity($product['quantity'])
                        ->setAmountExVat(floatval($productPriceExVat))
                        ->setVatPercent(intval($taxPercent))
                        ->setName($product['name'])
                        ->setUnit($this->language->get('unit'))
                        ->setArticleNumber($product['model'])
//                ->setDescription($product['model'])//should be used for $product['option'] wich is array, but to risky because limit is String(40)
                    );
        }

         $addons = $this->addTaxRateToAddons();
         //extra charge addons like shipping and invoice fee
         foreach ($addons as $addon) {
            if($addon['value'] >= 0){
                $svea = $svea->addOrderRow(Item::orderRow()
                    ->setQuantity(1)
                    ->setAmountExVat(floatval($addon['value'] * $currencyValue))
                    ->setVatPercent(intval($addon['tax_rate']))
                    ->setName(isset($addon['title']) ? $addon['title'] : "")
                    ->setUnit($this->language->get('unit'))
                    ->setArticleNumber($addon['code'])
                    ->setDescription(isset($addon['text']) ? $addon['text'] : "")
                );
            }
              //voucher(-)
            elseif ($addon['value'] < 0 && $addon['code'] == 'voucher') {
                $svea = $svea
                    ->addDiscount(WebPayItem::fixedDiscount()
                        ->setDiscountId($addon['code'])
                        ->setAmountIncVat(floatval(abs($addon['value']) * $currencyValue))
                        ->setVatPercent(0)//no vat when using a voucher
                        ->setName(isset($addon['title']) ? $addon['title'] : "")
                        ->setUnit($this->language->get('unit'))
                        ->setDescription(isset($addon['text']) ? $addon['text'] : "")
                );
            }
            //discounts
            else {
                $taxRates = $this->getTaxRatesInOrder($svea);
                $discountRows = Svea\Helper::splitMeanToTwoTaxRates( (abs($addon['value']) * $currencyValue), $addon['tax_rate'], $addon['title'], $addon['text'], $taxRates );
                foreach($discountRows as $row) {
                    $svea = $svea->addDiscount( $row );
                }
            }
         }

         $payPageLanguage = "";
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

        $server_url = $this->setServerURL();
        $returnUrl = $server_url.'index.php?route=payment/svea_directbank/responseSvea';
        $callbackUrl = $server_url.'index.php?route=payment/svea_directbank/callbackSvea';

         try{
              $form = $svea
                ->setCountryCode($order['payment_iso_code_2'])
                ->setCurrency($this->session->data['currency'])
                ->setClientOrderNumber($this->session->data['order_id'])
//            ->setClientOrderNumber($this->session->data['order_id'].rand(0, 1000))//use for testing to avoid duplication of order number. Warning - callback will fail if it does not match order_id

                ->setOrderDate(date('c'))
                ->usePaymentMethod($_POST['svea_directbank_payment_method'])
                    ->setCancelUrl($returnUrl)
                    ->setReturnUrl($returnUrl)
                    ->setCallbackUrl($callbackUrl)
                    ->setCardPageLanguage($payPageLanguage)
                    ->getPaymentForm();
         }  catch (Exception $e){
            $this->log->write($e->getMessage());
             echo '<div class="attention">Logged Svea Error</div>';
            exit();
         }
         //Save order but Void it while order status is unsure
        $this->model_checkout_order->addOrderHistory($this->session->data['order_id'], 0,'Sent to Svea gateway.');

         echo '<html><head>
                <script type="text/javascript">
                    function doPost(){
                        document.forms[0].submit();
                        }
                </script>
                </head>
                <body onload="doPost()">
                ';

        //print form with hidden buttons
        $fields = $form->htmlFormFieldsAsArray;
        $hiddenForm = $fields['form_start_tag'];
        $hiddenForm .= $fields['input_merchantId'];
        $hiddenForm .= $fields['input_message'];
        $hiddenForm .= $fields['input_mac'];
        $hiddenForm .= $fields['form_end_tag'];

        echo $hiddenForm;
        echo'
            </body></html>
        ';
        exit();

    }
    /**
     * This redirects the customer depending on ok or not from Svea
     */
    public function responseSvea(){
        $this->load->model('checkout/order');
        $this->load->model('payment/svea_directbank');
        include(DIR_APPLICATION.'../svea/Includes.php');

        //Get the country
        $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
        $countryCode = $order_info['payment_iso_code_2'];

        //Testmode
        $conf = ($this->config->get('svea_directbank_testmode') == 1) ? (new OpencartSveaConfigTest($this->config)) : new OpencartSveaConfig($this->config);

        $resp = new SveaResponse($_REQUEST, $countryCode, $conf);
        $response = $resp->getResponse();
        $clean_clientOrderNumber = str_replace('.err', '', $response->clientOrderNumber);//bugfix for gateway concatinating ".err" on number
        if($response->resultcode !== '0'){
        if ($response->accepted === 1){
                $this->response->redirect($this->url->link('checkout/success', '','SSL'));
            }else{
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
        $this->load->model('payment/svea_directbank');
        $this->load->language('payment/svea_directbank');
        include(DIR_APPLICATION.'../svea/Includes.php');

        $conf = ($this->config->get('svea_directbank_testmode') == 1) ? (new OpencartSveaConfigTest($this->config)) : new OpencartSveaConfig($this->config);
        $resp = new SveaResponse($_REQUEST, 'SE', $conf); //HostedPaymentResponse. Countrycode not important on hosted payments.
        $response = $resp->getResponse();
         $clean_clientOrderNumber = str_replace('.err', '', $response->clientOrderNumber);//bugfix for gateway concatinating ".err" on number
            if ($response->accepted === 1){
                 //sets orderhistory
                $this->model_checkout_order->addOrderHistory($clean_clientOrderNumber,$this->config->get('svea_directbank_order_status_id'),'Svea transactionId: '.$response->transactionId,true);
                //adds comments to edit order comment field to use when edit order
                $this->db->query("UPDATE `" . DB_PREFIX . "order` SET date_modified = NOW(), comment = 'Payment accepted. Svea transactionId: ".$response->transactionId."' WHERE order_id = '" . (int)$response->clientOrderNumber . "'");

            }else{
                $error = $this->responseCodes($response->resultcode, $response->errormessage);
//                $this->model_checkout_order->addOrderHistory($clean_clientOrderNumber,10,$error,FALSE); //status 10 equals failed
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

        $this->load->language('payment/svea_directbank');

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

     public function addTaxRateToAddons() {
        //Get all addons
      $this->load->model('extension/extension');
        $total_data = array();
        $total = 0;
        $svea_tax = array();
        $cartTax = $this->cart->getTaxes();
        $results = $this->model_extension_extension->getExtensions('total');
        foreach ($results as $result) {
          //if this result is activated
           if($this->config->get($result['code'] . '_status')){
               $amount = 0;
               $taxes = array();
               foreach ($cartTax as $key => $value) {
                   $taxes[$key] = 0;
               }
               $this->load->model('total/' . $result['code']);

               $this->{'model_total_' . $result['code']}->getTotal($total_data, $total, $taxes);

               foreach ($taxes as $tax_id => $value) {
                   $amount += $value;
               }

               $svea_tax[$result['code']] = $amount;
           }

        }
        foreach ($total_data as $key => $value) {

            if (isset($svea_tax[$value['code']])) {
                if ($svea_tax[$value['code']]) {
                    $total_data[$key]['tax_rate'] = (int)round( $svea_tax[$value['code']] / $value['value'] * 100 ); // round and cast, or may get i.e. 24.9999, which shows up as 25f in debugger & written to screen, but converts to 24i
                } else {
                    $total_data[$key]['tax_rate'] = 0;
                }
            } else {
                $total_data[$key]['tax_rate'] = '0';
            }
        }
          $ignoredTotals = 'sub_total, total, taxes';
           $ignoredOrderTotals = array_map('trim', explode(',', $ignoredTotals));
            foreach ($total_data as $key => $orderTotal) {
                if (in_array($orderTotal['code'], $ignoredOrderTotals)) {
                    unset($total_data[$key]);
                }
            }
            return $total_data;
    }

    /**
     * TODO replace these with the one in php integration package Helper class in next release
     *
     * Takes a createOrderBuilder object, iterates over its orderRows, and
     * returns an array containing the distinct taxrates present in the order
     */
    private function getTaxRatesInOrder($order) {
        $taxRates = array();

        foreach( $order->orderRows as $orderRow ) {

            if( isset($orderRow->vatPercent) ) {
                $seenRate = $orderRow->vatPercent; //count
            }
            elseif( isset($orderRow->amountIncVat) && isset($orderRow->amountExVat) ) {
                $seenRate = Svea\Helper::bround( (($orderRow->amountIncVat - $orderRow->amountExVat) / $orderRow->amountExVat) ,2) *100;
            }

            if(isset($seenRate)) {
                isset($taxRates[$seenRate]) ? $taxRates[$seenRate] +=1 : $taxRates[$seenRate] =1;   // increase count of seen rate
            }
        }
        return array_keys($taxRates);   //we want the keys
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