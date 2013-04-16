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


        if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/svea_partpayment.tpl')) {
            $this->template = $this->config->get('config_template') . '/template/payment/svea_partpayment.tpl';
        } else {
            $this->template = 'default/template/payment/svea_partpayment.tpl';
            $this->data['partpayment_fail'] = $this->language->get('text_partpayment_fail');
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

        //Load models
        $this->load->model('checkout/order');
        $this->load->model('payment/svea_partpayment');
        $this->load->model('checkout/coupon');
        floatval(VERSION) >= 1.5 ? $this->load->model('checkout/voucher') : $this->load->model('checkout/extension');

        //Load SVEA includes
        include('svea/Includes.php');
               //Testmode
        $conf = ($this->config->get('svea_partpayment_testmode') == 1) ? (new OpencartSveaConfigTest($this->config)) : new OpencartSveaConfig($this->config);

        $svea = WebPay::createOrder($conf);


        //Get order information
        $order = $this->model_checkout_order->getOrder($this->session->data['order_id']);
        $countryCode = $order['payment_iso_code_2'];

        // Get the products in the cart
        $products = $this->cart->getProducts();

        //Product rows
        foreach ($products as $product) {

            //Get the tax, difference in version 1.4.x
            $productTax = (floatval(VERSION) >= 1.5) ? $this->currency->format($this->tax->getTax($product['price'], $product['tax_class_id']),'',false,false) : $this->currency->format($this->tax->getRate($product['tax_class_id']));

            //Get and set prices
            $productPriceExVat  = $this->currency->format($product['price'],'',false,false);
            $productPriceIncVat = $productPriceExVat + $productTax;

            $svea = $svea
                    ->addOrderRow(Item::orderRow()
                        ->setQuantity($product['quantity'])
                        ->setAmountExVat($productPriceExVat)
                        ->setAmountIncVat($productPriceIncVat)
                        ->setName($product['name'])
                        ->setUnit('st')//($this->language->get('unit'))
                        ->setArticleNumber($product['product_id'])
                        ->setDescription($product['model'])
                    );

        }


        //Shipping Fee
        if ($this->cart->hasShipping() == 1) {
            $shipping_info = $this->session->data['shipping_method'];
            $shippingCost = $this->currency->format($shipping_info["cost"],'',false,false);

            $shippingTax = (floatval(VERSION) >= 1.5) ? $this->tax->getTax($shippingCost, $shipping_info["tax_class_id"]) : $this->tax->getRate($shipping_info["tax_class_id"]) ;


            $shippingExVat  = $shippingCost;
            $shippingIncVat = $shippingExVat + $shippingTax;

            $svea = $svea
                    ->addFee(
                        Item::shippingFee()
                            ->setAmountExVat($shippingExVat)
                            ->setAmountIncVat($shippingIncVat)
                            ->setName($shipping_info["title"])
                            ->setDescription($shipping_info["text"])
                            ->setUnit($this->language->get('pcs'))
                       );

        }


        //Get coupons
        if (isset($this->session->data['coupon'])) {
            $coupon = $this->model_checkout_coupon->getCoupon($this->session->data['coupon']);

            $totalPrice = $this->cart->getTotal();

            if ($coupon['type'] == 'F') {
                $discount = $this->currency->format($coupon['discount'],'',false,false);;

                $svea = $svea
                        ->addDiscount(
                            Item::fixedDiscount()
                                ->setAmountIncVat($discount)
                                ->setName($coupon['name'])
                                ->setUnit($this->language->get('pcs'))
                            );


            } elseif ($coupon['type'] == 'P') {

                $svea = $svea
                        ->addDiscount(
                            Item::relativeDiscount()
                                ->setDiscountPercent($coupon['discount'])
                                ->setName($coupon['name'])
                                ->setUnit($this->language->get('pcs'))
                            );
                }
        }


        //Get vouchers WIP For version 1.4!
        if (isset($this->session->data['voucher'])) {
            $voucher = floatval(VERSION) >= 1.5 ? $this->model_checkout_voucher->getVoucher($this->session->data['voucher']) : $this->model_checkout_extension->getExtensions($this->session->data['voucher']);

            $totalPrice = $this->cart->getTotal();
            $voucherAmount =  $this->currency->format($voucher['amount'],'',false,false);

            $svea = $svea
                    ->addDiscount(
                        Item::fixedDiscount()
                            ->setAmountIncVat($voucherAmount)
                            ->setName($voucher['code'])
                            ->setDescription($voucher["message"])
                            ->setUnit($this->language->get('pcs'))
                        );
        }


        preg_match_all('!\d+!',$order['payment_address_1'],$houseNoArr);
        $houseNo = $houseNoArr[0][0];

        preg_match_all('!\w+!',$order['payment_address_1'],$streetArr);
        $street = $streetArr[0][0];


        $ssn = (isset($_GET['ssn'])) ? $_GET['ssn'] : 0;

        $item = Item::individualCustomer();

        $item = $item->setNationalIdNumber($ssn)
                     ->setEmail($order['email'])
                     ->setName($order['payment_firstname'],$order['payment_lastname'])
                     ->setStreetAddress($street,$houseNo)
                     ->setZipCode($order['payment_postcode'])
                     ->setLocality($order['payment_city'])
                     ->setIpAddress($order['ip'])
                     ->setPhoneNumber($order['telephone']);


        if($order["payment_iso_code_2"] == "DE" || $order["payment_iso_code_2"] == "NL"){

        $item = $item->setInitials($_GET['initials'])
                     ->setBirthDate($_GET['birthYear'], $_GET['birthMonth'], $_GET['birthDay']);

        }

        $svea = $svea->addCustomerDetails($item);


        $svea = $svea
                  ->setCountryCode($countryCode)
                  ->setCurrency($this->session->data['currency'])
                  ->setClientOrderNumber($this->session->data['order_id'])
                  ->setOrderDate(date('c'))
                  ->usePaymentPlanPayment($_GET["paySel"])
                    ->doRequest();


        $response = array();

        //If response accepted redirect to thankyou page
        if ($svea->accepted == 1) {
            $this->model_checkout_order->confirm($this->session->data['order_id'], $this->config->get('svea_invoice_order_status_id'));

            $response = array("success" => true);
        } else {

            $response = array("error" => $this->responseCodes($this->resultcode,$svea->errormessage));

        }

        echo json_encode($response);
    }



    private function getAddress($ssn){

        include('svea/Includes.php');

        $this->load->model('payment/svea_partpayment');
        $this->load->model('checkout/order');

        $order = $this->model_checkout_order->getOrder($this->session->data['order_id']);
        $countryCode = $order['payment_iso_code_2'];

        //Testmode
        $conf = ($this->config->get('svea_partpayment_testmode') == 1) ? (new OpencartSveaConfigTest($this->config)) : new OpencartSveaConfig($this->config);

        $svea = WebPay::getAddresses($conf)
            ->setOrderTypeInvoice()
            ->setCountryCode($countryCode);

        $svea = $svea->setIndividual($ssn);
        $svea = $svea->doRequest();

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

        return $result;
    }


    private function getPaymentOptions(){

        include('svea/Includes.php');

        $this->load->model('payment/svea_partpayment');
        $this->load->model('checkout/order');
        $order = $this->model_checkout_order->getOrder($this->session->data['order_id']);
        $countryCode = $order['payment_iso_code_2'];

        //Testmode
        $sveaConf = ($this->config->get('svea_partpayment_testmode') == 1) ? (new OpencartSveaConfigTest($this->config)) : new OpencartSveaConfig($this->config);
        $svea = WebPay::getPaymentPlanParams($sveaConf);
        $svea = $svea->setCountryCode($countryCode)
                    ->doRequest();

        $result = array();
        if (isset($svea->errormessage)) {
            $result = array("error" => $svea->errormessage);
        }else{
            foreach ($svea->campaignCodes as $cc){
                $result[] = array("campaignCode" => $cc->campaignCode,
                                  "description"    => $cc->description,
                                    "price_per_month" => (string)round(($cc->monthlyAnnuityFactor * $order['total']),2)." ".$order['currency']);

            }
        }

        return $result;
    }


    public function getAddressAndPaymentOptions(){

        $this->load->model('checkout/order');
        $order = $this->model_checkout_order->getOrder($this->session->data['order_id']);
        $countryCode = $order['payment_iso_code_2'];

        $paymentOptions = $this->getPaymentOptions();

        if ($countryCode == "SE" || $countryCode == "DK" || $countryCode == "NO")
            $adresses = $this->getAddress($_GET['ssn']);
        else
            $adresses = array();

        $result = array("addresses" => $adresses, "paymentOptions" => $paymentOptions);

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
}
?>