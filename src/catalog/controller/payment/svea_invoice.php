<?php

class ControllerPaymentsveainvoice extends Controller {

    protected function index() {
        $this->load->language('payment/svea_invoice');

        //Definitions
        $this->data['button_confirm'] = $this->language->get('button_confirm');
        $this->data['button_back'] = $this->language->get('button_back');

        $this->data['continue'] = 'index.php?route=checkout/success';

        if ($this->request->get['route'] != 'checkout/guest_step_3') {
            $this->data['back'] = 'index.php?route=checkout/payment';
        } else {
            $this->data['back'] = 'index.php?rout=checkout/guest_step_2';
        }

        //Get the country
        $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
        $this->data['countryCode'] = $order_info['payment_iso_code_2'];

        $this->data['logo'] = "<img src='admin/view/image/payment/".$this->getLogo($order_info['payment_iso_code_2'])."/svea_invoice.png'>";

        $this->id = 'payment';

        if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/svea_invoice.tpl')) {
            $this->template = $this->config->get('config_template') . '/template/payment/svea_invoice.tpl';
        } else {
            $this->template = 'default/template/payment/svea_invoice.tpl';
        }
        $this->render();
    }

    private function responseCodes($err,$msg = "") {
        $this->load->language('payment/svea_invoice');

        $definition = $this->language->get("response_$err");
        if (preg_match("/^response/", $definition))
             $definition = $this->language->get("response_error"). " $msg";

        return $definition;
    }

    public function confirm() {

        $this->load->language('payment/svea_invoice');
        $this->load->language('total/svea_fee');

        //Load models
        $this->load->model('checkout/order');
        $this->load->model('payment/svea_invoice');
        $this->load->model('checkout/coupon');
        if (floatval(VERSION) >= 1.5) {
            $this->load->model('checkout/voucher');
        }
        //Load SVEA includes
        include(DIR_APPLICATION.'../svea/Includes.php');

        //Get order information
        $order = $this->model_checkout_order->getOrder($this->session->data['order_id']);
        $countryCode = $order['payment_iso_code_2'];

        //Testmode
        if($this->config->get('svea_invoice_testmode_'.$countryCode) !== NULL){
            $conf = $this->config->get('svea_invoice_testmode_'.$countryCode) == "1" ? new OpencartSveaConfigTest($this->config) : new OpencartSveaConfig($this->config);

        } else {
            $response = array("error" => $this->responseCodes(40001,"The country is not supported for this paymentmethod"));
            echo json_encode($response);
            exit();
        }

        $svea = WebPay::createOrder($conf);
        //Check if company or private
        $company = ($_GET['company'] == 'true') ? true : false;

        // Get the products in the cart
        $products = $this->cart->getProducts();
        $currencyValue = 1.00000000;
        if (floatval(VERSION) >= 1.5) {
             $currencyValue = $order['currency_value'];
         }else{
             $currencyValue = $order['value'];
         }
        //Products
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
                    ->setName($addon['title'])
                    ->setUnit($this->language->get('unit'))
                    ->setArticleNumber($addon['code'])
                    ->setDescription($addon['text'])
            );
            //discounts
             }  elseif($addon['value'] < 0) {
                  $svea = $svea
                    ->addDiscount(
                        Item::fixedDiscount()
                            ->setAmountIncVat(floatval($addon['value']))
                            ->setName($addon['name'])
                            ->setDescription($addon['text'])
                            ->setUnit($this->language->get('unit'))
                        );
             }

         }
        //Seperates the street from the housenumber according to testcases
        $pattern = "/^(?:\s)*([0-9]*[A-ZÄÅÆÖØÜßäåæöøüa-z]*\s*[A-ZÄÅÆÖØÜßäåæöøüa-z]+)(?:\s*)([0-9]*\s*[A-ZÄÅÆÖØÜßäåæöøüa-z]*[^\s])?(?:\s)*$/";
        preg_match($pattern, $order['payment_address_1'], $addressArr);
        if( !array_key_exists( 2, $addressArr ) ) { $addressArr[2] = ""; } //fix for addresses w/o housenumber

        //Set order detials if company or private
        if ($company == TRUE){
            $item = Item::companyCustomer();

            $item = $item->setEmail($order['email'])
                         ->setCompanyName($order['payment_company'])
                         ->setStreetAddress($addressArr[1],$addressArr[2])
                         ->setZipCode($order['payment_postcode'])
                         ->setLocality($order['payment_city'])
                         ->setIpAddress($order['ip'])
                         ->setPhoneNumber($order['telephone']);
            if($order["payment_iso_code_2"] == "DE" || $order["payment_iso_code_2"] == "NL"){

                $item = $item->setVatNumber($_GET['vatno']);
            }else{
                $item = $item->setNationalIdNumber($_GET['ssn']);
                $item = $item->setAddressSelector($_GET['addSel']);
            }
            $svea = $svea->addCustomerDetails($item);


        }else{
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
                $item = $item->setBirthDate($_GET['birthYear'], $_GET['birthMonth'], $_GET['birthDay']);
            }
            if($order["payment_iso_code_2"] == "NL"){
                $item = $item->setInitials($_GET['initials']);
            }
            $svea = $svea->addCustomerDetails($item);
            }
             try{
             $svea = $svea
                      ->setCountryCode($countryCode)
                      ->setCurrency($this->session->data['currency'])
                      ->setClientOrderNumber($this->session->data['order_id'])
                      ->setOrderDate(date('c'))
                      ->useInvoicePayment()
                        ->doRequest();
            }  catch (Exception $e){
                $this->log->write($e->getMessage());
                $response = array("error" => $this->responseCodes(0,$e->getMessage()));
                echo json_encode($response);
                exit();

            }
            //If CreateOrder accepted redirect to thankyou page
            if ($svea->accepted == 1) {
                $response = array();
                //If Auto deliver order is set, DeliverOrder
                if($this->config->get('svea_invoice_auto_deliver') == 1){
                    $deliverObj = WebPay::deliverOrder($conf);
                    //Product rows
                    $deliverObj = $this->formatOrderRows($deliverObj, $products,$currencyValue);
                    //InvoiceFee
                    if ($this->config->get('svea_fee_status') == 1) {
                    $deliverObj = $this->formatInvoiceFeeRows($deliverObj,$currencyValue);
                    }
                     //Shipping
                    if ($this->cart->hasShipping() == 1) {
                        if($this->session->data['shipping_method']['cost'] > 0){
                          $deliverObj = $this->formatShippingFeeRows($deliverObj,$currencyValue);
                        }
                    }
                     //Get coupons
                    if (isset($this->session->data['coupon'])) {
                        $coupon = $this->model_checkout_coupon->getCoupon($this->session->data['coupon']);
                        $deliverObj = $this->formatCouponRows($deliverObj,$coupon,$currencyValue);
                    }
                     //Get vouchers
                    if (isset($this->session->data['voucher']) && floatval(VERSION) >= 1.5) {
                        $voucher = $this->model_checkout_voucher->getVoucher($this->session->data['voucher']);
                        $deliverObj = $this->formatVoucher($deliverObj,$voucher,$currencyValue);
                        //$totalPrice = $this->cart->getTotal();
                   }
                   try{

                        $deliverObj = $deliverObj->setCountryCode($countryCode)
                                ->setOrderId($svea->sveaOrderId)
                                ->setInvoiceDistributionType($this->config->get('svea_invoice_distribution_type'))
                                    ->deliverInvoiceOrder()
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
                       $this->model_checkout_order->confirm($this->session->data['order_id'], $this->config->get('svea_invoice_auto_deliver_status_id'));
                    //I not, send error codes
                    }  else {
                        $response = array("error" => $this->responseCodes($deliverObj->resultcode,$deliverObj->errormessage));
                    }
                //if auto deliver not set, send true to view
                }  else {
                     $response = array("success" => true);
                    //update order status for created
                    $this->model_checkout_order->confirm($this->session->data['order_id'], $this->config->get('svea_invoice_order_status_id'));
                }

            //else send errors to view
            }  else {
                $response = array("error" => $this->responseCodes($svea->resultcode,$svea->errormessage));
            }

//            $tmp =  sprintf("%c",31) . json_encode($response);  // reproduce JSON.parse error (junk chars) that shows up w/i.e. quickcheckout plugin
//            echo $tmp;
            echo json_encode($response);
        }



        public function getAddress() {
                include(DIR_APPLICATION.'../svea/Includes.php');

                $this->load->model('payment/svea_invoice');
                $this->load->model('checkout/order');

                $order = $this->model_checkout_order->getOrder($this->session->data['order_id']);

                $countryCode = $order['payment_iso_code_2'];
                 //Testmode
                $conf = $this->config->get('svea_invoice_testmode_'.$countryCode) == '1' ? new OpencartSveaConfigTest($this->config) : new OpencartSveaConfig($this->config);
                $svea = WebPay::getAddresses($conf)
                    ->setOrderTypeInvoice()
                    ->setCountryCode($countryCode);

                if($_GET['company'] == 'true')
                    $svea = $svea->setCompany($_GET['ssn']);
                else
                    $svea = $svea->setIndividual($_GET['ssn']);
                try{
                    $svea = $svea->doRequest();
                }  catch (Exception $e){
                      $response = array("error" => $this->responseCodes(0,$e->getMessage()));
                      $this->log->write($e->getMessage());
                    echo json_encode($response);
                    exit();
                }

                $result = array();

                if (isset($svea->errormessage)) {
                    $result = array("error" => $svea->errormessage);
                }else{

                    foreach ($svea->customerIdentity as $ci){

                        $name = ($ci->fullName) ? $ci->fullName : $ci->legalName;

                        $result[] = array("fullName" => $name,
                                          "street"    => $ci->street,
                                          "zipCode"   => $ci->zipCode,
                                          "locality"  => $ci->locality,
                                          "addressSelector" => $ci->addressSelector);

                    }


                }

                echo json_encode($result);

            }

    private function formatOrderRows($svea,$products,$currencyValue){
        $this->load->language('payment/svea_invoice');

        //Product rows
        foreach ($products as $product) {
            $productPriceExVat = $product['price'] * $currencyValue;
             $taxPercent = 0;
            //Get the tax, difference in version 1.4.x
            if (floatval(VERSION) >= 1.5) {
                $tax = $this->tax->getRates($product['price'], $product['tax_class_id']);
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

    private function formatInvoiceFeeRows($svea,$currencyValue) {
         $this->load->language('payment/svea_invoice');
         $this->load->language('total/svea_fee');
            //Invoice Fee
            $invoiceFeeExTax = $this->config->get('svea_fee_fee') * $currencyValue;
            $invoiceFeeTaxId = $this->config->get('svea_fee_tax_class_id');
            $invoiceTax = 0;

            if($invoiceFeeTaxId > 0){
                if(floatval(VERSION) >= 1.5){
                   $invoiceTax =$this->tax->getTax($invoiceFeeExTax, $invoiceFeeTaxId);
                   $invoiceFeeIncVat = $invoiceFeeExTax + $invoiceTax;
               }  else {
                   $taxRate = $this->tax->getRate($invoiceFeeTaxId);
                   $invoiceFeeIncVat = (($taxRate / 100) +1) * $invoiceFeeExTax;
               }
            }  else {
                //no tax for invoicefee is set
                $invoiceFeeIncVat = $invoiceFeeExTax;
            }

            $svea = $svea
                    ->addFee(
                        Item::invoiceFee()
                            ->setAmountExVat(floatval($invoiceFeeExTax))
                            ->setAmountIncVat(floatval($invoiceFeeIncVat))
                            ->setDescription($this->language->get('text_svea_fee'))
                            ->setUnit($this->language->get('unit'))
                        );

        return $svea;
    }

    public function formatShippingFeeRows($svea,$currencyValue) {
         $this->load->language('payment/svea_invoice');
        //Shipping Fee
            $shipping_info = $this->session->data['shipping_method'];
            $shippingExVat = $shipping_info["cost"] * $currencyValue;
             $shippingIncVat = 0;
            if (floatval(VERSION) >= 1.5){
                $shippingTax = $this->tax->getTax($shippingExVat, $shipping_info["tax_class_id"]);
                $shippingIncVat = $shippingExVat + $shippingTax;
            }else{
                $taxRate = $this->tax->getRate($shipping_info['tax_class_id']);
                $shippingIncVat = (($taxRate / 100) +1) * $shippingExVat;
            }
            $svea = $svea
                    ->addFee(
                        Item::shippingFee()
                            ->setAmountExVat(floatval($shippingExVat))
                            ->setAmountIncVat( floatval($shippingIncVat))
                            ->setName($shipping_info["title"])
                            ->setDescription($shipping_info["text"])
                            ->setUnit($this->language->get('unit'))
                       );


        return $svea;
    }

    private function formatCouponRows($svea, $coupon,$currencyValue) {
        if ($coupon['discount'] > 0 && $coupon['type'] == 'F') {
            $discount = $coupon['discount'] * $currencyValue;

            $svea = $svea
                    ->addDiscount(
                        Item::fixedDiscount()
                            ->setAmountIncVat(floatval($discount))
                            ->setName($coupon['name'])
                            ->setUnit($this->language->get('unit'))
                        );


        } elseif ($coupon['discount'] > 0 && $coupon['type'] == 'P') {

            $svea = $svea
                    ->addDiscount(
                        Item::relativeDiscount()
                            ->setDiscountPercent($coupon['discount'])
                            ->setName($coupon['name'])
                            ->setUnit($this->language->get('unit'))
                        );

            }
            return $svea;
    }

    private function formatVoucher($svea, $voucher,$currencyValue) {
        $voucherAmount = $voucher['amount'] * $currencyValue;
        $svea = $svea
                ->addDiscount(
                    Item::fixedDiscount()
                        ->setVatPercent(0)//No vat on voucher. Concidered a debt.
                        ->setAmountIncVat(floatval($voucherAmount))
                        ->setName($voucher['code'])
                        ->setDescription($voucher["message"])
                        ->setUnit($this->language->get('unit'))
                    );
        return $svea;
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

}
?>
