<?php
include_once(dirname(__FILE__).'/svea_common.php');

class ControllerPaymentsveapartpayment extends SveaCommon {

    /**
     * Returns the currency used for an invoice country.
     */
    protected function getPartpaymentCurrency( $countryCode ) {
        $country_currencies = array(
            'SE' => 'SEK',
            'NO' => 'NOK',
            'FI' => 'EUR',
            'DK' => 'DKK',
            'NL' => 'EUR',
            'DE' => 'EUR'
        );
        return $country_currencies[$countryCode];
    }

    public function index() {
        // populate data array for use in template
        $this->load->language('payment/svea_partpayment');
        $this->load->model('checkout/order');

        $data['text_payment_options'] = $this->language->get('text_payment_options');
        $data['text_ssn'] = $this->language->get('text_ssn');
        $data['text_birthdate'] = $this->language->get('text_birthdate');
        $data['text_initials'] = $this->language->get('text_initials');
        $data['text_get_address'] = $this->language->get('text_get_address');
        $data['text_invoice_address'] = $this->language->get('text_invoice_address');
        $data['text_shipping_address'] = $this->language->get('text_shipping_address');
        $data['svea_partpayment_shipping_billing'] = $this->config->get('svea_partpayment_shipping_billing');
        $data['response_no_campaign_on_amount'] = $this->language->get('response_no_campaign_on_amount');

        $data['button_confirm'] = $this->language->get('button_confirm');
        $data['button_back'] = $this->language->get('button_back');

        $data['continue'] = 'index.php?route=checkout/success';

        if ($this->request->get['route'] != 'checkout/guest_step_3') {
            $data['back'] = 'index.php?route=checkout/payment';
        } else {
            $data['back'] = 'index.php?rout=checkout/guest_step_2';
        }

        //Get the country from the order
        $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

        $data['countryCode'] = $order_info['payment_iso_code_2'];
        if($data['countryCode'] == "NO" || $data['countryCode'] == "DK" || $data['countryCode'] == "NL"){
            $logoImg = "http://cdn.svea.com/sveafinans/rgb_svea-finans_small.png";
        } else {
            $logoImg = "http://cdn.svea.com/sveaekonomi/rgb_ekonomi_small.png";
        }
        $data['logo'] = "<img src='$logoImg' alt='Svea Ekonomi'>";

        // we show the available payment plans w/monthly amounts as radiobuttons under the logo
        $data['paymentOptions'] = $this->getPaymentOptions();

        if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/svea_partpayment.tpl')) {
                return $this->load->view($this->config->get('config_template') . '/template/payment/svea_partpayment.tpl', $data);
        } else {
                return $this->load->view('default/template/payment/svea_partpayment.tpl', $data);
        }
    }

    private function responseCodes($err, $msg = "") {
        $this->load->language('payment/svea_partpayment');

        $definition = $this->language->get("response_$err");

        if (preg_match("/^response/", $definition))
            $definition = $this->language->get("response_error") . " $msg";

        return $definition;
    }

    public function confirm() {
        $this->load->language('payment/svea_partpayment');
        //Load models
        $this->load->model('payment/svea_invoice');
        $this->load->model('checkout/order');
        $this->load->model('payment/svea_partpayment');
        $this->load->model('checkout/coupon');
        $this->load->model('account/address');

        floatval(VERSION) >= 1.5 ? $this->load->model('checkout/voucher') : $this->load->model('checkout/extension');

        //Load SVEA includes
        include(DIR_APPLICATION . '../svea/Includes.php');
        $response = array();
        //Get order information
        $order = $this->model_checkout_order->getOrder($this->session->data['order_id']);
        $countryCode = $order['payment_iso_code_2'];
        //Testmode
        if ($this->config->get('svea_partpayment_testmode_' . $countryCode) !== NULL) {
            $conf = $this->config->get('svea_partpayment_testmode_' . $countryCode) == "1" ? new OpencartSveaConfigTest($this->config) : new OpencartSveaConfig($this->config);
        } else {
            $response = array("error" => $this->responseCodes(40001, "The country is not supported for this paymentmethod"));
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($response));

        }

        $svea = WebPay::createOrder($conf);

        // Get the products in the cart
        $products = $this->cart->getProducts();

        // make sure we use the currency matching the clientno
        $this->load->model('localisation/currency');
        $currency_info = $this->model_localisation_currency->getCurrencyByCode( $this->getPartpaymentCurrency($countryCode) );
        $currencyValue = $currency_info['value'];

        //Products
        $this->load->language('payment/svea_partpayment');        
        $svea = $this->addOrderRowsToSveaOrder($svea, $products, $currencyValue);

        //extra charge addons like shipping and invoice fee        
        $addons = $this->addTaxRateToAddons();

        $svea = $this->addAddonRowsToSveaOrder($svea, $addons, $currencyValue);

         if($order["payment_iso_code_2"] == "DE" || $order["payment_iso_code_2"] == "NL") {
           $addressArr = Svea\Helper::splitStreetAddress( $order['payment_address_1'] );
        }  else {
            $addressArr[1] =  $order['payment_address_1'];
            $addressArr[2] =  "";
        }
        $ssn = (isset($_GET['ssn'])) ? $_GET['ssn'] : 0;

        $item = Item::individualCustomer();
        $item = $item->setNationalIdNumber($ssn)
                ->setEmail($order['email'])
                ->setName($order['payment_firstname'], $order['payment_lastname'])
                ->setStreetAddress($addressArr[1], $addressArr[2])
                ->setZipCode($order['payment_postcode'])
                ->setLocality($order['payment_city'])
                ->setIpAddress($order['ip'])
                ->setPhoneNumber($order['telephone']);

        if ($order["payment_iso_code_2"] == "DE" || $order["payment_iso_code_2"] == "NL") {

            $item = $item->setInitials($_GET['initials'])
                    ->setBirthDate($_GET['birthYear'], $_GET['birthMonth'], $_GET['birthDay']);
        }

        $svea = $svea->addCustomerDetails($item);
        try {
            $svea = $svea
                    ->setCountryCode($countryCode)
                    ->setCurrency($this->session->data['currency'])
                    ->setClientOrderNumber($this->session->data['order_id'])
                    ->setOrderDate(date('c'))
                    ->usePaymentPlanPayment($_GET["paySel"])
                    ->doRequest();
        } catch (Exception $e) {
            $this->log->write($e->getMessage());
            $response = array("error" => $this->responseCodes(0, $e->getMessage()));
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($response));

        }


        //If response accepted redirect to thankyou page
        if ($svea->accepted == 1) {

             $sveaOrderAddress = $this->buildPaymentAddressQuery($svea,$countryCode,$order['comment']);

            if($this->config->get('svea_partpayment_shipping_billing') == '1')
                $sveaOrderAddress = $this->buildShippingAddressQuery($svea,$sveaOrderAddress,$countryCode);

            $this->model_payment_svea_invoice->updateAddressField($this->session->data['order_id'],$sveaOrderAddress);
            //If Auto deliver order is set, DeliverOrder

            if ($this->config->get('svea_partpayment_auto_deliver') === '1') {
                $deliverObj = WebPay::deliverOrder($conf);
                //Product rows
                try {
                    $deliverObj = $deliverObj
                            ->setCountryCode($countryCode)
                            ->setOrderId($svea->sveaOrderId)
                            ->deliverPaymentPlanOrder()
                            ->doRequest();
                } catch (Exception $e) {
                    $this->log->write($e->getMessage());
                    $response = array("error" => $this->responseCodes(0, $e->getMessage()));
                    $this->response->addHeader('Content-Type: application/json');
                    $this->response->setOutput(json_encode($response));

                }

                //If DeliverOrder returns true, send true to veiw
                if ($deliverObj->accepted == 1) {
                    $response = array("success" => true);
                    //update order status for delivered
                    $this->model_checkout_order->addOrderHistory($this->session->data['order_id'], $this->config->get('svea_partpayment_deliver_status_id'), 'Svea contractNumber '.$deliverObj->contractNumber);
                    $this->db->query("INSERT INTO " . DB_PREFIX . "order_history SET order_id = '" . (int)$this->session->data['order_id'] . "', order_status_id = '" . (int)$this->config->get('svea_partpayment_deliver_status_id') . "', notify = '" . 1 . "', comment = 'Order delivered. Svea contractNumber: " . $deliverObj->contractNumber . "', date_added = NOW()");
                    //I not, send error codes
                } else {
                    $response = array("error" => $this->responseCodes($deliverObj->resultcode, $deliverObj->errormessage));
                }
                //if auto deliver not set, send true to view
            } else {
                $response = array("success" => true);
                //update order status for created
                $this->model_checkout_order->addOrderHistory($this->session->data['order_id'], $this->config->get('svea_partpayment_order_status_id'),'Svea order id: '. $svea->sveaOrderId);
            }

            //else send errors to view
        } else {
            $response = array("error" => $this->responseCodes($svea->resultcode, $svea->errormessage));
        }
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($response));

    }

    private function getAddress($ssn) {

        include(DIR_APPLICATION . '../svea/Includes.php');

        $this->load->model('payment/svea_partpayment');
        $this->load->model('checkout/order');

        $order = $this->model_checkout_order->getOrder($this->session->data['order_id']);
        $countryCode = $order['payment_iso_code_2'];
        //Testmode
        $conf = $this->config->get('svea_partpayment_testmode_' . $countryCode) == "1" ? new OpencartSveaConfigTest($this->config) : new OpencartSveaConfig($this->config);

        $svea = WebPay::getAddresses($conf)
                ->setOrderTypePaymentPlan()
                ->setCountryCode($countryCode);

        $svea = $svea->setIndividual($ssn);
        $result = array();
        try {
            $svea = $svea->doRequest();
        } catch (Exception $e) {
            $this->log->write($e->getMessage());
            $result = array("error" => $this->responseCodes(0, $e->getMessage()));
        }

        if ($svea->accepted != TRUE) {
            $result = array("error" => $svea->errormessage);
        } else {
            foreach ($svea->customerIdentity as $ci) {

                $name = ($ci->fullName) ? $ci->fullName : $ci->legalName;

                $result[] = array("fullName" => $name,
                    "street" => $ci->street,
                    "address_2" => $ci->coAddress,
                    "zipCode" => $ci->zipCode,
                    "locality" => $ci->locality);
            }
        }
        return $result;
        // echo json_encode($result);
    }


    /**
     * getPaymentOptions gets the available paymentmethods for this country and the order value and returns campaigns w/monthly cost
     *
     * @return array of array("campaignCode" => same, "description" => same , "price_per_month" => (string) price/month in selected currency)
     */

    private function getPaymentOptions() {
        include(DIR_APPLICATION . '../svea/Includes.php');
        $this->load->language('payment/svea_partpayment');
        $this->load->model('payment/svea_partpayment');
        $this->load->model('checkout/order');
        $order = $this->model_checkout_order->getOrder($this->session->data['order_id']);
        $countryCode = $order['payment_iso_code_2'];

        $result = array();
        if ($this->config->get('svea_partpayment_testmode_' . $countryCode) !== NULL) {
            $svea = $this->model_payment_svea_partpayment->getPaymentPlanParams($countryCode);
        } else {
            $result = array("error" => $this->responseCodes(40001, "The country is not supported for this paymentmethod"));
            return $result;
        }

       if (sizeof($svea) < 1) {
            $result = array("error" => 'Svea error: '.$this->language->get('response_27000'));
        } else {
            $currency = floatval(VERSION) >= 1.5 ? $order['currency_code'] : $order['currency'];
            $this->load->model('localisation/currency');
            $currencies = $this->model_localisation_currency->getCurrencies();
            $decimals = "";
            foreach ($currencies as $key => $val) {
                if ($key == $currency) {
                    if($key == 'EUR'){
                        $decimals = 2;
                    }  else {
                        $decimals = 0;
                    }
                }
            }
            $formattedPrice = round($this->currency->format(($order['total']), '', false, false), 2);
            $campaigns = WebPay::paymentPlanPricePerMonth($formattedPrice, $svea);
            foreach ($campaigns->values as $cc)
                $result[] = array("campaignCode" => $cc['campaignCode'],
                    "description" => $cc['description'],
                    "price_per_month" => (string) round($cc['pricePerMonth'], $decimals) . " " . $currency . "/" . $this->language->get('month'));
        }

        return $result;
    }

    public function getAddressAndPaymentOptions() {
        $this->load->model('checkout/order');
        $order = $this->model_checkout_order->getOrder($this->session->data['order_id']);
        $countryCode = $order['payment_iso_code_2'];
        $paymentOptions = $this->getPaymentOptions();

        if ($countryCode == "SE" || $countryCode == "DK") { //|| $countryCode == "NO") {    // getAddresses() turned off for Norway oct'13
            $addresses = $this->getAddress($this->request->post['ssn']);
        } elseif ($countryCode != "SE" && $countryCode != "NO" && $countryCode != "DK" && $countryCode != "FI" && $countryCode != "NL" && $countryCode != "DE") {
            $addresses = array("error" => $this->responseCodes(40001, "The country is not supported for this paymentmethod"));
        } else {
            $addresses = array();
        }
        $result = array("addresses" => $addresses, "paymentOptions" => $paymentOptions);

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($result));

    }

    private function ShowErrorMessage($response = null) {
        $message = ($response !== null && isset($response->ErrorMessage)) ? $response->ErrorMessage : "Could not get any partpayment alternatives.";
        echo '$("#svea_partpayment_div").hide();
              $("#svea_partpayment_alt").hide();
              $("#svea_partpayment_err").show();
              $("#svea_partpayment_err").append("' . $message . '");
              $("a#checkout").hide();';
    }

     //update order billingaddress
     private function buildPaymentAddressQuery($svea,$countryCode,$order_comment) {
         $countryId = $this->model_payment_svea_invoice->getCountryIdFromCountryCode(strtoupper($countryCode));
         $paymentAddress = array();

//        if ($svea->customerIdentity->customerType == 'Company'){
//                // no companies for partpayment
//        }
//        else {  // private customer

            if( isset($svea->customerIdentity->firstName)){ $paymentAddress["payment_firstname"] = $svea->customerIdentity->firstName; }
            if( isset($svea->customerIdentity->lastName)){ $paymentAddress["payment_lastname"] = $svea->customerIdentity->lastName; }
            // for private individuals, if firstName, lastName is not set in GetAddresses response, we put the entire getAddress LegalName in lastName
            if( isset($svea->customerIdentity->fullName)){ $paymentAddress["payment_lastname"] = $svea->customerIdentity->fullName; }

            if( isset($svea->customerIdentity->street)){ $paymentAddress["payment_address_1"] = $svea->customerIdentity->street; }
            if( isset($svea->customerIdentity->coAddress)){ $paymentAddress["payment_address_2"] = $svea->customerIdentity->coAddress; }
            if( isset($svea->customerIdentity->locality)){ $paymentAddress["payment_city"] = $svea->customerIdentity->locality; }
            if( isset($svea->customerIdentity->zipCode)){ $paymentAddress["payment_postcode"] = $svea->customerIdentity->zipCode; }

            $paymentAddress["payment_country_id"] = $countryId['country_id'];
            $paymentAddress["payment_country"] = $countryId['country_name'];
            $paymentAddress["payment_method"] = $this->language->get('text_title');
//        }

        $paymentAddress["comment"] = $order_comment . "\nSvea order id: ".$svea->sveaOrderId;

        return $paymentAddress;
    }

    // update shipping address
    private function buildShippingAddressQuery($svea,$shippingAddress,$countryCode) {
        $countryId = $this->model_payment_svea_invoice->getCountryIdFromCountryCode(strtoupper($countryCode));

//        if ($svea->customerIdentity->customerType == 'Company'){
//                // no companies for partpayment
//        }
//        else {  // private customer
            if( isset($svea->customerIdentity->firstName)){ $shippingAddress["shipping_firstname"] = $svea->customerIdentity->firstName; }
            if( isset($svea->customerIdentity->lastName)){ $shippingAddress["shipping_lastname"] = $svea->customerIdentity->lastName; }
            if( isset($svea->customerIdentity->fullName)){ $shippingAddress["shipping_lastname"] = $svea->customerIdentity->fullName; }

            if( isset($svea->customerIdentity->street)){ $shippingAddress["shipping_address_1"] = $svea->customerIdentity->street; }
            if( isset($svea->customerIdentity->coAddress)){ $shippingAddress["shipping_address_2"] = $svea->customerIdentity->coAddress; }
            if( isset($svea->customerIdentity->locality)){ $shippingAddress["shipping_city"] = $svea->customerIdentity->locality; }
            if( isset($svea->customerIdentity->zipCode)){ $shippingAddress["shipping_postcode"] = $svea->customerIdentity->zipCode; }
            $shippingAddress["shipping_country_id"] = $countryId['country_id'];
            $shippingAddress["shipping_country"] = $countryId['country_name'];
//        }

        return $shippingAddress;
    }
}
?>