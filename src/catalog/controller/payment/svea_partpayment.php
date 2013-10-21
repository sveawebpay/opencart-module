<?php
class ControllerPaymentsveapartpayment extends Controller {

    protected function index() {
        $this->load->language('payment/svea_partpayment');
        $this->data['button_confirm'] = $this->language->get('button_confirm');
        $this->data['button_back'] = $this->language->get('button_back');

        $this->data['continue'] = 'index.php?route=checkout/success';

        if ($this->request->get['route'] != 'checkout/guest_step_3') {
            $this->data['back'] = 'index.php?route=checkout/payment';
        } else {
            $this->data['back'] = 'index.php?rout=checkout/guest_step_2';
        }

        $this->id = 'payment';

        //Get the country
        $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
        $this->data['countryCode'] = $order_info['payment_iso_code_2'];

        $this->data['logo'] = "<img src='admin/view/image/payment/".$this->getLogo($order_info['payment_iso_code_2'])."/svea_partpayment.png'>";

        if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/svea_partpayment.tpl')) {
            $this->template = $this->config->get('config_template') . '/template/payment/svea_partpayment.tpl';
        } else {
            $this->template = 'default/template/payment/svea_partpayment.tpl';
            $this->data['partpayment_fail'] = $this->language->get('text_partpayment_fail');
        }
        $this->render();
    }


     private function responseCodes($err,$msg = "") {
        $this->load->language('payment/svea_partpayment');

        $definition = $this->language->get("response_$err");

        if (preg_match("/^response/", $definition))
             $definition = $this->language->get("response_error"). " $msg";

        return $definition;
    }

    public function confirm() {
        $this->load->language('payment/svea_partpayment');

        //Load models
        $this->load->model('checkout/order');
        $this->load->model('payment/svea_partpayment');
        $this->load->model('checkout/coupon');
        floatval(VERSION) >= 1.5 ? $this->load->model('checkout/voucher') : $this->load->model('checkout/extension');

        //Load SVEA includes
        include(DIR_APPLICATION.'../svea/Includes.php');
         $response = array();
        //Get order information
        $order = $this->model_checkout_order->getOrder($this->session->data['order_id']);
        $countryCode = $order['payment_iso_code_2'];
        //Testmode
        if($this->config->get('svea_partpayment_testmode_'.$countryCode) !== NULL){
            $conf = $this->config->get('svea_partpayment_testmode_'.$countryCode) == "1" ? new OpencartSveaConfigTest($this->config) : new OpencartSveaConfig($this->config);

        } else {
            $response = array("error" => $this->responseCodes(40001,"The country is not supported for this paymentmethod"));
            echo json_encode($response);
            exit();
        }

        $svea = WebPay::createOrder($conf);

        // Get the products in the cart
        $products = $this->cart->getProducts();
        $currencyValue = 1.00000000;
        if (floatval(VERSION) >= 1.5) {
             $currencyValue = $order['currency_value'];
         }else{
             $currencyValue = $order['value'];
         }
        //products
        $svea = $this->formatOrderRows($svea,$products,$currencyValue);
        //get all addons
        $addons = $this->formatAddons();
        //extra charge addons like shipping and invoice fee
        foreach ($addons as $addon) {
            if($addon['value'] >= 0){
                 $svea = $svea
                   ->addOrderRow(Item::orderRow()
                   ->setQuantity(1)
                   ->setAmountExVat(floatval($addon['value'] * $currencyValue))
                   ->setVatPercent(intval($addon['tax_rate']))
                   ->setName(isset($addon['title']) ? $addon['title'] : "")
                   ->setUnit($this->language->get('unit'))
                   ->setArticleNumber($addon['code'])
                   ->setDescription(isset($addon['text']) ? $addon['text'] : "")
           );
           //discounts
            }  elseif($addon['value'] < 0) {
                 $svea = $svea
                   ->addDiscount(
                       Item::fixedDiscount()
                           ->setAmountIncVat(floatval($addon['value']))
                           ->setName(isset($addon['name']) ? $addon['name'] : "")
                           ->setDescription(isset($addon['text']) ? $addon['text'] : "")
                           ->setUnit($this->language->get('unit'))
                       );
            }

        }

        //Seperates the street from the housenumber according to testcases
        $pattern = "/^(?:\s)*([0-9]*[A-ZÄÅÆÖØÜßäåæöøüa-z]*\s*[A-ZÄÅÆÖØÜßäåæöøüa-z]+)(?:\s*)([0-9]*\s*[A-ZÄÅÆÖØÜßäåæöøüa-z]*[^\s])?(?:\s)*$/";
        preg_match($pattern, $order['payment_address_1'], $addressArr);
        if( !array_key_exists( 2, $addressArr ) ) { $addressArr[2] = ""; } //fix for addresses w/o housenumber

        $ssn = (isset($_GET['ssn'])) ? $_GET['ssn'] : 0;

        $item = Item::individualCustomer();
        $item = $item->setNationalIdNumber($ssn)
                     ->setEmail($order['email'])
                     ->setName($order['payment_firstname'],$order['payment_lastname'])
                     ->setStreetAddress($addressArr[1],$addressArr[2])
                     ->setZipCode($order['payment_postcode'])
                     ->setLocality($order['payment_city'])
                     ->setIpAddress($order['ip'])
                     ->setPhoneNumber($order['telephone']);

        if($order["payment_iso_code_2"] == "DE" || $order["payment_iso_code_2"] == "NL"){

        $item = $item->setInitials($_GET['initials'])
                     ->setBirthDate($_GET['birthYear'], $_GET['birthMonth'], $_GET['birthDay']);

        }

        $svea = $svea->addCustomerDetails($item);
        try{
            $svea = $svea
                      ->setCountryCode($countryCode)
                      ->setCurrency($this->session->data['currency'])
                      ->setClientOrderNumber($this->session->data['order_id'])
                      ->setOrderDate(date('c'))
                      ->usePaymentPlanPayment($_GET["paySel"])
                        ->doRequest();
        }  catch (Exception $e){
            $this->log->write($e->getMessage());
            $response = array("error" => $this->responseCodes(0,$e->getMessage()));
            echo json_encode($response);
            exit();
        }


        //If response accepted redirect to thankyou page
        if ($svea->accepted == 1) {
                //If Auto deliver order is set, DeliverOrder
                if($this->config->get('svea_partpayment_auto_deliver') == 1){
                    $deliverObj = WebPay::deliverOrder($conf);
                    //Product rows
                    try{
                        $deliverObj = $deliverObj
                                ->setCountryCode($countryCode)
                                ->setOrderId($svea->sveaOrderId)
                                    ->deliverPaymentPlanOrder()
                                    ->doRequest();
                    }  catch (Exception $e){
                        $this->log->write($e->getMessage());
                        $response = array("error" => $this->responseCodes(0,$e->getMessage()));
                        echo json_encode($response);
                        exit();
                    }

                  //If DeliverOrder returns true, send true to veiw
                    if($deliverObj->accepted == 1){
                       $response = array("success" => true);
                       //update order status for delivered
                       $this->model_checkout_order->confirm($this->session->data['order_id'], $this->config->get('svea_partpayment_auto_deliver_status_id'));
                    //I not, send error codes
                    }  else {
                        $response = array("error" => $this->responseCodes($deliverObj->resultcode,$deliverObj->errormessage));
                    }
                //if auto deliver not set, send true to view
                }  else {
                     $response = array("success" => true);
                    //update order status for created
                    $this->model_checkout_order->confirm($this->session->data['order_id'], $this->config->get('svea_partpayment_order_status_id'));
                }

            //else send errors to view
            }  else {
                $response = array("error" => $this->responseCodes($svea->resultcode,$svea->errormessage));
            }
        echo json_encode($response);
    }



    private function getAddress($ssn){

        include(DIR_APPLICATION.'../svea/Includes.php');

        $this->load->model('payment/svea_partpayment');
        $this->load->model('checkout/order');

        $order = $this->model_checkout_order->getOrder($this->session->data['order_id']);
        $countryCode = $order['payment_iso_code_2'];
        //Testmode
        $conf = $this->config->get('svea_partpayment_testmode_'.$countryCode) == "1" ? new OpencartSveaConfigTest($this->config) : new OpencartSveaConfig($this->config);

        $svea = WebPay::getAddresses($conf)
            ->setOrderTypePaymentPlan()
            ->setCountryCode($countryCode);

        $svea = $svea->setIndividual($ssn);
        $result = array();
        try{
            $svea = $svea->doRequest();
        }  catch (Exception $e){
            $this->log->write($e->getMessage());
            $result = array("error" => $this->responseCodes(0,$e->getMessage()));
        }

        if (isset($svea->errormessage)) {
            $result = array("error" => $svea->errormessage);
        }else{
            foreach ($svea->customerIdentity as $ci){

                $name = ($ci->fullName) ? $ci->fullName : $ci->legalName;

                $result = array("fullName"  => $name,
                                  "street"    => $ci->street,
                                  "zipCode"   => $ci->zipCode,
                                  "locality"  => $ci->locality,
                                  "addressSelector" => $ci->addressSelector);
            }
        }
        return $result;
       // echo json_encode($result);
    }


    private function getPaymentOptions(){
        include(DIR_APPLICATION.'../svea/Includes.php');
        $this->load->language('payment/svea_partpayment');
        $this->load->model('payment/svea_partpayment');
        $this->load->model('checkout/order');
        $order = $this->model_checkout_order->getOrder($this->session->data['order_id']);
        $countryCode = $order['payment_iso_code_2'];

        $result = array();
        if($this->config->get('svea_partpayment_testmode_'.$countryCode) !== NULL){
            $sveaConf = ($this->config->get('svea_partpayment_testmode_'.$countryCode) == "1") ? (new OpencartSveaConfigTest($this->config)) : new OpencartSveaConfig($this->config);
        } else {
              $result = array("error" => $this->responseCodes(40001,"The country is not supported for this paymentmethod"));
              return $result;
        }
        $svea = WebPay::getPaymentPlanParams($sveaConf);
        try{
            $svea = $svea->setCountryCode($countryCode)
                    ->doRequest();
        }  catch (Exception $e){
            $this->log->write($e->getMessage());
            $result[] = array("error" => $e->getMessage());
        }


        if (isset($svea->errormessage)) {
            $result[] = array("error" => $svea->errormessage);
        }else{
            $currency = floatval(VERSION) >= 1.5 ? $order['currency_code'] : $order['currency'];
            $this->load->model('localisation/currency');
            $currencies = $this->model_localisation_currency->getCurrencies();
            $decimals = "";
            foreach ($currencies as $key => $val) {
                if($key == $currency){
                    $decimals = $val['decimal_place'];
                }
            }
            $formattedPrice = round($this->currency->format(($order['total']),'',false,false),2);
            $campaigns = WebPay::paymentPlanPricePerMonth($formattedPrice, $svea);
                foreach ($campaigns->values as $cc)
                $result[] = array("campaignCode" => $cc['campaignCode'],
                                  "description"    => $cc['description'],
                                  "price_per_month" => (string)round($cc['pricePerMonth'],$decimals)." ".$currency."/".$this->language->get('month'));

            }


        return $result;
    }


    public function getAddressAndPaymentOptions(){
        $this->load->model('checkout/order');
        $order = $this->model_checkout_order->getOrder($this->session->data['order_id']);
        $countryCode = $order['payment_iso_code_2'];
        $paymentOptions = $this->getPaymentOptions();

        if ($countryCode == "SE" || $countryCode == "DK" || $countryCode == "NO"){
            $addresses = $this->getAddress($_GET['ssn']);
        }elseif ($countryCode != "SE" && $countryCode != "NO" && $countryCode != "DK" && $countryCode != "FI" && $countryCode != "NL" && $countryCode != "DE") {
            $addresses = array("error" => $this->responseCodes(40001,"The country is not supported for this paymentmethod"));
        }  else {
            $addresses = array();
        }
        $result = array("addresses" => $addresses, "paymentOptions" => $paymentOptions);

        echo json_encode($result);

    }


    private function ShowErrorMessage($response = null) {
        $message = ($response !== null && isset($response->ErrorMessage)) ? $response->ErrorMessage : "Could not get any partpayment alternatives.";
         echo '$("#svea_partpayment_div").hide();
              $("#svea_partpayment_alt").hide();
              $("#svea_partpayment_err").show();
              $("#svea_partpayment_err").append("' . $message . '");
              $("a#checkout").hide();';
    }

        private function formatOrderRows($svea,$products,$currencyValue){
        $this->load->language('payment/svea_partpayment');
        //Product rows
        foreach ($products as $product) {
            $productPriceExVat  = $product['price'] * $currencyValue;

            //Get the tax, difference in version 1.4.x
            if (floatval(VERSION) >= 1.5) {
                $productTax = $this->tax->getTax($product['price'], $product['tax_class_id']);
                $tax = $this->tax->getRates($product['price'], $product['tax_class_id']);
                $taxPercent = 0;
                foreach ($tax as $key => $value) {
                    $taxPercent = $value['rate'];
                }
            } else {
                $taxPercent = $this->tax->getRate($product['tax_class_id']);
            }
            $svea = $svea
                    ->addOrderRow(Item::orderRow()
                        ->setQuantity($product['quantity'])
                        ->setAmountExVat(floatval($productPriceExVat))
                       ->setVatPercent(intval($taxPercent))
                        ->setName($product['name'])
                        ->setUnit($this->language->get('unit'))
                        ->setArticleNumber($product['product_id'])
                        ->setDescription($product['model'])
                    );

        }

        return $svea;
    }

    public function formatAddons() {
        //Get all addons
        $this->load->model('setting/extension');
        $total_data = array();
        $total = 0;
        $svea_tax = array();
        $cartTax = $this->cart->getTaxes();
        $results = $this->model_setting_extension->getExtensions('total');
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
                    $total_data[$key]['tax_rate'] = $svea_tax[$value['code']] / $value['value'] * 100;
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
}
?>