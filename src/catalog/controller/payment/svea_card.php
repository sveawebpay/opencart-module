<?php
class ControllerPaymentsveacard extends Controller {
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

        $data['logo'] = "<img src='admin/view/image/payment/".$this->getLogo($order_info['payment_iso_code_2'])."/svea_card.png'>";
        $data['cardLogos'] = "<img src='admin/view/image/payment/svea_direct/KORTCERT.png'>
                                    <img src='admin/view/image/payment/svea_direct/AMEX.png'>
                                    <img src='admin/view/image/payment/svea_direct/DINERS.png'>
                                    ";
        $data['continue'] = 'index.php?route=payment/svea_card/redirectSvea';


        $this->load->model('checkout/coupon');
        $this->load->model('checkout/order');
        $this->load->model('payment/svea_card');
        $this->load->model('localisation/currency');
        $this->load->language('payment/svea_card');
        include(DIR_APPLICATION.'../svea/Includes.php');

        //Testmode
        $conf = ($this->config->get('svea_card_testmode') == 1) ? (new OpencartSveaConfigTest($this->config)) : new OpencartSveaConfig($this->config);

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
         $addons = $this->formatAddons();

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
            }    //voucher(-)
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
                $discountRows = $this->splitMeanToTwoTaxRates( abs($addon['value']), $addon['tax_rate'], $addon['title'], $addon['text'], $taxRates );
                foreach($discountRows as $row) {
                    $svea = $svea->addDiscount( $row );
                }
            }
        }

        $server_url = $this->setServerURL();
        $returnUrl = $server_url.'index.php?route=payment/svea_card/responseSvea';
        $callbackUrl = $server_url.'index.php?route=payment/svea_card/callbackSvea';
        // $callbackUrl = 'http://194.14.250.189:8080/modulerDev/Opencart/2/index.php?route=payment/svea_card/callbackSvea'; //svea test
        $form = $svea
            ->setCountryCode($order['payment_iso_code_2'])
            ->setCurrency($this->session->data['currency'])
            ->setClientOrderNumber('annelitest'.$this->session->data['order_id'])
//            ->setClientOrderNumber($this->session->data['order_id'].rand(0, 1000))//use for testing to avoid duplication of order number. Warning - callback will fail if it does not match order_id
            ->setOrderDate(date('c'));
        try{
            $form =  $form->usePaymentMethod(PaymentMethod::KORTCERT)
            ->setCancelUrl($returnUrl)
            ->setCallbackUrl($callbackUrl)
            ->setReturnUrl($returnUrl)
            ->setCardPageLanguage(strtolower($order['language_code']))
            ->getPaymentForm();
        }  catch (Exception $e){
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

//        $this->render();
        if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/svea_card.tpl')) {
                return $this->load->view($this->config->get('config_template') . '/template/payment/svea_card.tpl', $data);
        } else {
                return $this->load->view('default/template/payment/svea_card.tpl', $data);
        }
    }
    /**
     * This redirects the customer depending on ok or not from Svea
     */
    public function responseSvea(){
        $this->load->model('checkout/order');
        $this->load->model('payment/svea_card');
        include(DIR_APPLICATION.'../svea/Includes.php');
        //Get the country
        $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
        $countryCode = $order_info['payment_iso_code_2'];

        $conf = ($this->config->get('svea_card_testmode') == 1) ? (new OpencartSveaConfigTest($this->config)) : new OpencartSveaConfig($this->config);
        $resp = new SveaResponse($_REQUEST, $countryCode, $conf); //HostedPaymentResponse
        $response = $resp->getResponse();
        $clean_clientOrderNumber = str_replace('.err', '', $response->clientOrderNumber);//bugfix for gateway concatinating ".err" on number
        $clean_clientOrderNumber = str_replace('annelitest', '',$clean_clientOrderNumber);//bugfix for gateway concatinating ".err" on number
        if($response->resultcode !== '0'){
            if ($response->accepted === 1){
                   //sets orderhistory
//                $this->model_checkout_order->addOrderHistory($clean_clientOrderNumber,$this->config->get('svea_card_order_status_id'),'Svea transactionId: '.$response->transactionId,TRUE);
//                //adds comments to edit order comment field to use when edit order
//                $this->db->query("UPDATE `" . DB_PREFIX . "order` SET date_modified = NOW(), comment = 'Payment accepted. Svea transactionId: ".$response->transactionId."' WHERE order_id = '" . (int)$response->clientOrderNumber . "'");

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
        $this->load->model('payment/svea_card');
        $this->load->language('payment/svea_card');
        include(DIR_APPLICATION.'../svea/Includes.php');

        $conf = ($this->config->get('svea_card_testmode') == 1) ? (new OpencartSveaConfigTest($this->config)) : new OpencartSveaConfig($this->config);
        $resp = new SveaResponse($_REQUEST, 'SE', $conf); //HostedPaymentResponse. Countrycode not important on hosted payments.
        $response = $resp->getResponse();
        $clean_clientOrderNumber = str_replace('.err', '', $response->clientOrderNumber);//bugfix for gateway concatinating ".err" on number
        $clean_clientOrderNumber = str_replace('annelitest', '',$clean_clientOrderNumber);//bugfix for gateway concatinating ".err" on number

            if ($response->accepted === 1){
                 //sets orderhistory
                $this->model_checkout_order->addOrderHistory($clean_clientOrderNumber,$this->config->get('svea_card_order_status_id'),'Svea transactionId: '.$response->transactionId,true);
                //adds comments to edit order comment field to use when edit order
                $this->db->query("UPDATE `" . DB_PREFIX . "order` SET date_modified = NOW(), comment = 'Payment accepted. Svea transactionId: ".$response->transactionId."' WHERE order_id = '" . (int)$response->clientOrderNumber . "'");

            }else{
                $error = $this->responseCodes($response->resultcode, $response->errormessage);
//                $this->model_checkout_order->addOrderHistory($clean_clientOrderNumber,10,$error,false);
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

     public function formatAddons() {
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
     * Takes a total discount value ex. vat, a mean tax rate & an array of allowed tax rates.
     * returns an array of FixedDiscount objects representing the discount split
     * over the allowed Tax Rates, defined using AmountExVat & VatPercent.
     *
     * Note: only supports two allowed tax rates for now.
     */
    private function splitMeanToTwoTaxRates( $discountAmountExVat, $discountMeanVat, $discountName, $discountDescription, $allowedTaxRates ) {

        $fixedDiscounts = array();

        if( sizeof( $allowedTaxRates ) > 1 ) {

            // m = $discountMeanVat
            // r0 = allowedTaxRates[0]; r1 = allowedTaxRates[1]
            // m = a r0 + b r1 => m = a r0 + (1-a) r1 => m = (r0-r1) a + r1 => a = (m-r1)/(r0-r1)
            // d = $discountAmountExVat;
            // d = d (a+b) => 1 = a+b => b = 1-a

            $a = ($discountMeanVat - $allowedTaxRates[1]) / ( $allowedTaxRates[0] - $allowedTaxRates[1] );
            $b = 1 - $a;

            $discountA = WebPayItem::fixedDiscount()
                            ->setAmountExVat( Svea\Helper::bround(($discountAmountExVat * $a),2) )
                            ->setVatPercent( $allowedTaxRates[0] )
                            ->setName( isset( $discountName) ? $discountName : "" )
                            ->setDescription( (isset( $discountDescription) ? $discountDescription : "") . ' (' .$allowedTaxRates[0]. '%)' )
            ;

            $discountB = WebPayItem::fixedDiscount()
                            ->setAmountExVat( Svea\Helper::bround(($discountAmountExVat * $b),2) )
                            ->setVatPercent(  $allowedTaxRates[1] )
                            ->setName( isset( $discountName) ? $discountName : "" )
                            ->setDescription( (isset( $discountDescription) ? $discountDescription : "") . ' (' .$allowedTaxRates[1]. '%)' )
            ;

            $fixedDiscounts[] = $discountA;
            $fixedDiscounts[] = $discountB;
        }
        // single tax rate, so use shop supplied mean as vat rate
        else {
            $discountA = WebPayItem::fixedDiscount()
                ->setAmountExVat( Svea\Helper::bround(($discountAmountExVat),2) )
                ->setVatPercent( $allowedTaxRates[0] )
                ->setName( isset( $discountName) ? $discountName : "" )
                ->setDescription( (isset( $discountDescription) ? $discountDescription : "") )
            ;
            $fixedDiscounts[] = $discountA;
        }

        return $fixedDiscounts;
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