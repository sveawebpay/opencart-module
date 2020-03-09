<?php
include_once(dirname(__FILE__).'/svea_common.php');
require_once(DIR_APPLICATION . '../svea/config/configInclude.php');

use \Svea\WebPay\Helper\Helper;

class ControllerExtensionPaymentSveainvoice extends SveaCommon {

    //backwards compatability
    private $paymentString = "payment_";

    public function setVersionStrings()
    {
        if(VERSION < 3.0)
        {
            $this->paymentString = "";
        }
    }
    /**
     * Returns the currency used for an invoice country.
     */
    protected function getInvoiceCurrency( $countryCode ) {
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

    public function index()
    {
        $this->setVersionStrings();
        $this->load->language('extension/payment/svea_invoice');
        $this->load->model('checkout/order');
        //Definitions
        $data['text_private_or_company'] = $this->language->get("text_private_or_company");
        $data['text_company'] = $this->language->get("text_company");
        $data['text_private'] = $this->language->get("text_private");
        $data['text_ssn'] = $this->language->get("text_ssn");
        $data['text_vat_no'] = $this->language->get("text_vat_no");
        $data['text_get_address'] = $this->language->get("text_get_address");
        $data['text_invoice_address'] = $this->language->get('text_invoice_address');
        $data['text_shipping_address'] = $this->language->get('text_shipping_address');
        $data['text_birthdate'] = $this->language->get("text_birthdate");
        $data['text_initials'] = $this->language->get("text_initials");
        $data['text_vat_no'] = $this->language->get("text_vat_no");
        $data['text_customerreference'] = $this->language->get('text_customerreference');
        $data['text_peppolid'] = $this->language->get('text_peppolid');
        $data[$this->paymentString . 'svea_invoice_shipping_billing'] = $this->config->get($this->paymentString . 'svea_invoice_shipping_billing');
        $data[$this->paymentString . 'svea_invoice_peppol'] = $this->config->get($this->paymentString . 'svea_invoice_peppol');
        $data['text_required'] = $this->language->get('text_required');
        $data['button_confirm'] = $this->language->get('button_confirm');
        $data['button_back'] = $this->language->get('button_back');
        $data['continue'] = 'index.php?route=checkout/success';

        if ($this->request->get['route'] != 'checkout/guest_step_3') {
            $data['back'] = 'index.php?route=checkout/payment';
        } else {
            $data['back'] = 'index.php?rout=checkout/guest_step_2';
        }

        //Get the country
        $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

        $data['countryCode'] = $order_info['payment_iso_code_2'];
        $data['logo'] = '<svg style="fill: #002c50;" xmlns="http://www.w3.org/2000/svg" xml:space="preserve" width="94" height="35" version="1.1" viewBox="0 0 2540 930" xmlns:xlink="http://www.w3.org/1999/xlink">
 <g>
  <path d="M403 256l-172 0c-62,0 -70,-31 -70,-55 0,-49 25,-69 88,-69l334 0 0 -135 -353 0c-157,0 -230,64 -230,202 0,130 69,190 219,190l154 0c60,0 80,14 80,59 0,37 -14,57 -89,57l-338 0 0 135 359 0c156,0 229,-63 229,-198 0,-133 -61,-187 -210,-187z"></path>
  <polygon points="1137,-3 955,438 777,-3 602,-3 883,641 1034,641 1303,-3 "></polygon>
  <path d="M1572 129l226 0 0 -133 -229 0c-207,0 -304,106 -304,333 0,117 33,200 100,254 66,53 130,57 201,57l232 0 0 -133 -226 0c-94,0 -131,-35 -135,-127l361 0 0 -133 -360 0c8,-81 51,-119 133,-119z"></path>
  <path d="M2097 358l73 -191 76 191 -149 0zm1 -361l-273 644 165 0 55 -148 252 0 57 148 172 0 -275 -644 -154 0z"></path>
  <path id="streck-underline" style="fill: #00aece;" d="M2496 931l-2445 0c-17,0 -31,-14 -31,-31l0 -106c0,-17 14,-31 31,-31l2445 0c17,0 31,14 31,31l0 106c0,17 -14,31 -31,31z"></path>
 </g>
</svg>';

        if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/extension/payment/svea_invoice')) {
            return $this->load->view($this->config->get('config_template') . '/template/extension/payment/svea_invoice', $data);
        } elseif(floatval(VERSION) >= 2.2) {
            return $this->load->view('extension/payment/svea_invoice', $data);
        }else {
            return $this->load->view('default/template/extension/payment/svea_invoice', $data);
        }
    }

    private function responseCodes($err,$msg = "") {
        $this->load->language('payment/svea_invoice');

        $definition = $this->language->get("response_$err");
        if (preg_match("/^response/", $definition))
             $definition = $this->language->get("response_error"). " $msg";

        return $definition;
    }

    public function confirm() {
        $this->load->language('extension/payment/svea_invoice');
        if(isset($_GET['peppolid']) && $_GET['peppolid'] != "")
        {
            if(!Helper::isValidPeppolId($_GET['peppolid']))
            {
                $response = array("error" => $this->language->get("text_peppol_invalid_format"));
                $this->response->addHeader('Content-Type: application/json');
                $this->response->setOutput(json_encode($response));
                return;
            }
        }

        $this->setVersionStrings();
        $this->load->language('extension/payment/svea_invoice');
        $this->load->language('extension/total/svea_fee');

        //Load models
        $this->load->model('checkout/order');
        $this->load->model('extension/payment/svea_invoice');
        $this->load->model('account/address');

        //Get order information
        $order = $this->model_checkout_order->getOrder($this->session->data['order_id']);
        $countryCode = $order['payment_iso_code_2'];
        //Testmode
        if($this->config->get($this->paymentString . 'svea_invoice_testmode_'.$countryCode) !== NULL){
            $conf = ( $this->config->get($this->paymentString . 'svea_invoice_testmode_'.$countryCode) == "1" )
                    ? new OpencartSveaConfigTest($this->config,$this->paymentString . 'svea_invoice') : new OpencartSveaConfig($this->config,$this->paymentString . 'svea_invoice');
        }
        else {
            $response = array("error" => $this->responseCodes(40001,"The country is not supported for this paymentmethod"));
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($response));

        }

        $svea = \Svea\WebPay\WebPay::createOrder($conf);

        // Get the products in the cart
        $products = $this->cart->getProducts();

        // make sure we use the currency matching the invoice clientno
        $this->load->model('localisation/currency');
        $currency_info = $this->model_localisation_currency->getCurrencyByCode( $this->getInvoiceCurrency($countryCode) );
        $currencyValue = $currency_info['value'];

        //Products
        $this->load->language('extension/payment/svea_invoice');
        $svea = $this->addOrderRowsToWebServiceOrder($svea, $products, $currencyValue);

        //extra charge addons like shipping and invoice fee
        $addons = $this->addTaxRateToAddons();

        $svea = $this->addAddonRowsToSveaOrder($svea, $addons, $currencyValue);

        $svea = $this->addRoundingRowIfApplicable($svea, $this->cart->getTotal(), $addons, $currencyValue);

        //Seperates the street from the housenumber according to testcases for NL and DE
        if($order["payment_iso_code_2"] == "DE" || $order["payment_iso_code_2"] == "NL") {
            $addressArr = \Svea\WebPay\Helper\Helper::splitStreetAddress( $order[$this->paymentString . 'address_1'] );
        }  else {
            $addressArr[1] =  $order['payment_address_1'];
            $addressArr[2] =  "";
        }
        $company = ($_GET['company'] == 'true') ? true : false;

        if ($company == TRUE){  // company customer

            $item = \Svea\WebPay\BuildOrder\RowBuilders\Item::companyCustomer();

            $item = $item->setEmail($order['email'])
                         ->setCompanyName($order['payment_company'])
                         ->setStreetAddress($addressArr[1],$addressArr[2])
                         ->setCoAddress($order['payment_address_2'])
                         ->setZipCode($order['payment_postcode'])
                         ->setLocality($order['payment_city'])
                         ->setIpAddress($order['ip'])
                         ->setPhoneNumber($order['telephone']);
            if($order["payment_iso_code_2"] == "DE" || $order["payment_iso_code_2"] == "NL") {
                $item = $item->setVatNumber(isset($_GET['vatno']) ? $_GET['vatno'] : $_GET['ssn'] );
            }
            else{
                $item = $item->setNationalIdNumber($_GET['ssn']);
            }
            //only for SE, NO, DK where getAddress has been performed
            if($order["payment_iso_code_2"] == "SE" || $order["payment_iso_code_2"] == "NO" || $order["payment_iso_code_2"] == "DK") {
                $item = $item->setAddressSelector($_GET['addSel']);
            }
            $svea = $svea->addCustomerDetails($item);
            if(isset( $_GET['customerreference']) ) {
                  $svea = $svea->setCustomerReference($_GET['customerreference']);
            }
            if(isset($_GET['peppolid']) && $_GET['peppolid'] != "")
            {
                $svea = $svea->setPeppolId($_GET['peppolid']);
            }


        }
        else {  // private customer

            $ssn = (isset($_GET['ssn'])) ? $_GET['ssn'] : 0;

            $item = \Svea\WebPay\BuildOrder\RowBuilders\Item::individualCustomer();
            //send customer filled address to svea. Svea will use address from getAddress for the invoice.
            $item = $item
                ->setNationalIdNumber($ssn)
                ->setEmail($order['email'])
                ->setName($order['payment_firstname'],$order['payment_lastname'])
                ->setStreetAddress($addressArr[1],$addressArr[2])
                ->setCoAddress($order['payment_address_2'])
                ->setZipCode($order['payment_postcode'])
                ->setLocality($order['payment_city'])
                ->setIpAddress($order['ip'])
                ->setPhoneNumber($order['telephone']);

            if($order["payment_iso_code_2"] == "DE" || $order["payment_iso_code_2"] == "NL"){
                $item = $item->setBirthDate($_GET['birthYear'], $_GET['birthMonth'], $_GET['birthDay']);
            }
            if($order["payment_iso_code_2"] == "NL"){
                $item = $item->setInitials($_GET['initials']);
            }
            $svea = $svea->addCustomerDetails($item);
        }

        try {
            $svea = $svea
                ->setCountryCode($countryCode)
                ->setCurrency($this->session->data['currency'])
                ->setClientOrderNumber($this->session->data['order_id'])
                ->setOrderDate(date('c'))
                ->useInvoicePayment()
                ->doRequest();

            //If CreateOrder accepted redirect to thankyou page
            if ($svea->accepted == 1) {
                $sveaOrderAddress = $this->buildPaymentAddressQuery($svea,$countryCode,$order['comment']);

                // if set to enforce shipping = billing, fetch billing address
                if($this->config->get($this->paymentString . 'svea_invoice_shipping_billing') == '1') {
                    $sveaOrderAddress = $this->buildShippingAddressQuery($svea,$sveaOrderAddress,$countryCode);
                }
                $this->model_extension_payment_svea_invoice->updateAddressField($this->session->data['order_id'],$sveaOrderAddress);

                $response = array();

                //If Auto deliver order is set, DeliverOrder
                if($this->config->get($this->paymentString . 'svea_invoice_auto_deliver') === '1') {
                    $deliverObj = \Svea\WebPay\WebPay::deliverOrder($conf);
                    // try to do deliverOrder request
                    try {
                        $deliverObj = $deliverObj
                            ->setCountryCode($countryCode)
                            ->setOrderId($svea->sveaOrderId)    // match doRequest orderId
                            ->setInvoiceDistributionType($this->config->get($this->paymentString . 'svea_invoice_distribution_type'))
                            ->deliverInvoiceOrder()
                            ->doRequest();

                        //if DeliverOrder returns true, send true to view
                        if($deliverObj->accepted == 1){
                            $response = array("success" => true);
                            //update order status for delivered
                            $this->model_checkout_order->addOrderHistory($this->session->data['order_id'], $this->config->get('config_order_status_id'),'Svea order id '. $svea->sveaOrderId, true);
                            $completeStatus = $this->config->get('config_complete_status');
                            $this->model_checkout_order->addOrderHistory($this->session->data['order_id'], $completeStatus[0], 'Svea: Order was delivered. Svea invoiceId '.$deliverObj->invoiceId, true);
                        }
                        //if not, send error codes
                        else {
                            $response = array("error" => $this->responseCodes($deliverObj->resultcode,$deliverObj->errormessage));
                        }
                     }
                    catch (Exception $e) {
                        $this->log->write($e->getMessage());
                        $response = array("error" => $this->responseCodes(0,$e->getMessage()));
                        $this->response->addHeader('Content-Type: application/json');
                        $this->response->setOutput(json_encode($response));

                    }

                }
                //if auto deliver not set, send true to view
                else {
                    $response = array("success" => true);
                    //update order status for created
                    $this->model_checkout_order->addOrderHistory($this->session->data['order_id'], $this->config->get('config_order_status_id'),'Svea order id '. $svea->sveaOrderId, true);
                }

            // not accepted, send errors to view
            }  else {
                $response = array("error" => $this->responseCodes($svea->resultcode,$svea->errormessage));
            }


            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($response));

        }
        catch (Exception $e){
            $this->log->write($e->getMessage());
            $response = array("error" => $this->responseCodes(0,$e->getMessage()));
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($response));
        }
    }

    private function handleGetAddressesError($getAddressesResult)
    {
        $this->load->language('extension/payment/svea_invoice');
        if($getAddressesResult->resultcode == "NoSuchEntity")
        {
            return array("error" => $this->language->get('response_error') . $this->language->get('response_nosuchentity'));
        }
        elseif($getAddressesResult->errormessage = "Invalid checkdigit") // We have to match exact message because there are several error with the same resultcode
        {
            return array("error" => $this->language->get('response_error') . $this->language->get('response_checkdigit'));
        }
        elseif($getAddressesResult->errormessage = "Must be exactly ten digits") // We have to match exact message because there are several error with the same resultcode
        {
            return array("error" => $this->language->get('response_error') . $this->language->get('response_invalidlength'));
        }
        else
        {
            return array("error" => $getAddressesResult->errormessage);
        }
    }

    public function getAddress() {
        $this->setVersionStrings();
        $this->load->model('extension/payment/svea_invoice');
        $this->load->model('checkout/order');
        $order = $this->model_checkout_order->getOrder($this->session->data['order_id']);
        $countryCode = $order['payment_iso_code_2'];
        //Testmode
        $conf = ( $this->config->get($this->paymentString . 'svea_invoice_testmode_'.$countryCode) == '1' )
                ? new OpencartSveaConfigTest($this->config,$this->paymentString . 'svea_invoice') : new OpencartSveaConfig($this->config,$this->paymentString . 'svea_invoice');

        $svea = \Svea\WebPay\WebPay::getAddresses($conf)
            ->setOrderTypeInvoice()
            ->setCountryCode($countryCode)
            ->setCustomerIdentifier($this->request->post['ssn']);

        if($this->request->post['company'] == 'true') {
            $svea = $svea->getCompanyAddresses();
        }
        else {
            $svea = $svea->getIndividualAddresses();
        }

        try{
            $svea = $svea->doRequest();

            $result = array();
            if ($svea->accepted != TRUE) {
                $result = $this->handleGetAddressesError($svea);
            }
            else {
                foreach ($svea->customerIdentity as $ci)
                {
                    $name = ($ci->fullName) ? $ci->fullName : $ci->legalName;

                    $result[] = array(  "fullName"  => $name,
                        "street"    => $ci->street,
                        "address_2" => $ci->coAddress,
                        "zipCode"  =>  $ci->zipCode,
                        "locality"  => $ci->locality,
                        "addressSelector" => $ci->addressSelector);
                }
            }
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($result));
        }
        catch (Exception $e){
            $response = array("error" => $this->responseCodes(0,$e->getMessage()));
            $this->log->write($e->getMessage());
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($response));
        }
    }

    // update order billingaddress
    private function buildPaymentAddressQuery($svea,$countryCode,$order_comment) {
         $countryId = $this->model_extension_payment_svea_invoice->getCountryIdFromCountryCode(strtoupper($countryCode));
         $paymentAddress = array();

        if ($svea->customerIdentity->customerType == 'Company'){
            if( isset($svea->customerIdentity->firstName)){ $paymentAddress["payment_firstname"] = $svea->customerIdentity->firstName; }
            if( isset($svea->customerIdentity->lastName)){ $paymentAddress["payment_lastname"] = $svea->customerIdentity->lastName; }
            if( isset($svea->customerIdentity->fullName))
            {
                $paymentAddress["payment_company"] = $svea->customerIdentity->fullName;
            }

            if( isset($svea->customerIdentity->street)){ $paymentAddress["payment_address_1"] = $svea->customerIdentity->street; }
            if( isset($svea->customerIdentity->coAddress)){ $paymentAddress["payment_address_2"] = $svea->customerIdentity->coAddress; }
            if( isset($svea->customerIdentity->locality)){ $paymentAddress["payment_city"] = $svea->customerIdentity->locality; }
            if( isset($svea->customerIdentity->zipCode)){ $paymentAddress["payment_postcode"] = $svea->customerIdentity->zipCode; }

            $paymentAddress["payment_country_id"] = $countryId['country_id'];
            $paymentAddress["payment_country"] = $countryId['country_name'];
            $paymentAddress["payment_method"] = $this->language->get('text_title');
        }

        else {  // private customer

            if( isset($svea->customerIdentity->firstName)){ $paymentAddress["payment_firstname"] = $svea->customerIdentity->firstName; }
            if( isset($svea->customerIdentity->lastName)){ $paymentAddress["payment_lastname"] = $svea->customerIdentity->lastName; }
            // for private individuals, if firstName, lastName is not set in GetAddresses response, we put the entire getAddress LegalName in lastName
            if( isset($svea->customerIdentity->fullName))
            {
                $fullName = $svea->customerIdentity->fullName;
                $fullName = str_replace(",", "", $fullName);
                $fullName = explode(" ", $fullName, 2);


                $paymentAddress["payment_firstname"] = isset($fullName[1]) ? $fullName[1] : "";
                $paymentAddress["payment_lastname"] = isset($fullName[0]) ? $fullName[0] : "";
                $paymentAddress["firstName"] = isset($fullName[1]) ? $fullName[1] : "";
                $paymentAddress["lastName"] = isset($fullName[0]) ? $fullName[0] : "";
            }

            if( isset($svea->customerIdentity->street)){ $paymentAddress["payment_address_1"] = $svea->customerIdentity->street; }
            if( isset($svea->customerIdentity->coAddress)){ $paymentAddress["payment_address_2"] = $svea->customerIdentity->coAddress; }
            if( isset($svea->customerIdentity->locality)){ $paymentAddress["payment_city"] = $svea->customerIdentity->locality; }
            if( isset($svea->customerIdentity->zipCode)){ $paymentAddress["payment_postcode"] = $svea->customerIdentity->zipCode; }

            $paymentAddress["payment_country_id"] = $countryId['country_id'];
            $paymentAddress["payment_country"] = $countryId['country_name'];
            $paymentAddress["payment_method"] = $this->language->get('text_title');
        }

        return $paymentAddress;
    }

    // update shipping address
    private function buildShippingAddressQuery($svea,$shippingAddress,$countryCode) {
        $countryId = $this->model_extension_payment_svea_invoice->getCountryIdFromCountryCode(strtoupper($countryCode));

        if ($svea->customerIdentity->customerType == 'Company'){
            if( isset($svea->customerIdentity->firstName)){ $shippingAddress["shipping_firstname"] = $svea->customerIdentity->firstName; }
            if( isset($svea->customerIdentity->lastName)){ $shippingAddress["shipping_lastname"] = $svea->customerIdentity->lastName; }
            if( isset($svea->customerIdentity->fullName))
            {
                $shippingAddress["shipping_company"] = $svea->customerIdentity->fullName;
            }
            if( isset($svea->customerIdentity->street)){ $shippingAddress["shipping_address_1"] = $svea->customerIdentity->street; }
            if( isset($svea->customerIdentity->coAddress)){ $shippingAddress["shipping_address_2"] = $svea->customerIdentity->coAddress; }
            if( isset($svea->customerIdentity->locality)){ $shippingAddress["shipping_city"] = $svea->customerIdentity->locality; }
            if( isset($svea->customerIdentity->zipCode)){ $shippingAddress["shipping_postcode"] = $svea->customerIdentity->zipCode; }

            $shippingAddress["shipping_country_id"] = $countryId['country_id'];
            $shippingAddress["shipping_country"] = $countryId['country_name'];
        }
        else {  // private customer
            if( isset($svea->customerIdentity->firstName)){ $shippingAddress["shipping_firstname"] = $svea->customerIdentity->firstName; }
            if( isset($svea->customerIdentity->lastName)){ $shippingAddress["shipping_lastname"] = $svea->customerIdentity->lastName; }
            if( isset($svea->customerIdentity->fullName))
            {
                $fullName = $svea->customerIdentity->fullName;
                $fullName = str_replace(",", "", $fullName);
                $fullName = explode(" ", $fullName, 2);

                $shippingAddress["shipping_firstname"] = isset($fullName[1]) ? $fullName[1] : "";
                $shippingAddress["shipping_lastname"] = isset($fullName[0]) ? $fullName[0] : "";
            }
            if( isset($svea->customerIdentity->street)){ $shippingAddress["shipping_address_1"] = $svea->customerIdentity->street; }
            if( isset($svea->customerIdentity->coAddress)){ $shippingAddress["shipping_address_2"] = $svea->customerIdentity->coAddress; }
            if( isset($svea->customerIdentity->locality)){ $shippingAddress["shipping_city"] = $svea->customerIdentity->locality; }
            if( isset($svea->customerIdentity->zipCode)){ $shippingAddress["shipping_postcode"] = $svea->customerIdentity->zipCode; }
            $shippingAddress["shipping_country_id"] = $countryId['country_id'];
            $shippingAddress["shipping_country"] = $countryId['country_name'];
        }

        return $shippingAddress;
    }
}
