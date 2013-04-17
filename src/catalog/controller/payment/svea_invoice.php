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
        include('svea/Includes.php');

        //Testmode
        $conf = ($this->config->get('svea_invoice_testmode') == 1) ? (new OpencartSveaConfigTest($this->config)) : new OpencartSveaConfig($this->config);

        $svea = WebPay::createOrder($conf);

        //Get order information
        $order = $this->model_checkout_order->getOrder($this->session->data['order_id']);
        $countryCode = $order['payment_iso_code_2'];

        //Check if company or private
        $company = ($_GET['company'] == 'true') ? true : false;

        // Get the products in the cart
        $products = $this->cart->getProducts();

        //Products
        $svea = $this->formatOrderRows($svea,$products);
        if ($this->config->get('svea_fee_status') == 1) {
            $svea = $this->formatInvoiceFeeRows($svea);

        }
        //Shipping
        if ($this->cart->hasShipping() == 1) {

            $svea = $this->formatShippingFeeRows($svea);

        }
        //Get coupons
        if (isset($this->session->data['coupon'])) {
            $coupon = $this->model_checkout_coupon->getCoupon($this->session->data['coupon']);
            $svea = $this->formatCouponRows($svea,$coupon);
        }
        //Get vouchers
        if (isset($this->session->data['voucher']) && floatval(VERSION) >= 1.5) {
            $voucher = $this->model_checkout_voucher->getVoucher($this->session->data['voucher']);
            $svea = $this->formatVoucher($svea,$voucher);
       }
        preg_match_all('!\d+!',$order['payment_address_1'],$houseNoArr);
        $houseNo = $houseNoArr[0][0];

        preg_match_all('!\w+!',$order['payment_address_1'],$streetArr);
        $street = $streetArr[0][0];

        //Set order detials if company or private
        if ($company == TRUE){
            $item = Item::companyCustomer();

            $item = $item->setEmail($order['email'])
                         ->setCompanyName($order['payment_company'])
                         ->setStreetAddress($street,$houseNo)
                         ->setZipCode($order['payment_postcode'])
                         ->setLocality($order['payment_city'])
                         ->setIpAddress($order['ip'])
                         ->setPhoneNumber($order['telephone']);

            if($order["payment_iso_code_2"] == "DE" || $order["payment_iso_code_2"] == "NL"){

                $item = $item->setVatNumber($_GET['vatno']);
            }else{
                $item = $item->setNationalIdNumber($_GET['ssn']);
            }
             $svea = $svea->addCustomerDetails($item);
        }else{
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
                $item = $item->setBirthDate($_GET['birthYear'], $_GET['birthMonth'], $_GET['birthDay']);
            }
            if($order["payment_iso_code_2"] == "NL"){
                $item = $item->setInitials($_GET['initials']);
            }
            $svea = $svea->addCustomerDetails($item);
            }
            $svea = $svea
                      ->setCountryCode($countryCode)
                      ->setCurrency($this->session->data['currency'])
                      ->setClientOrderNumber($this->session->data['order_id'])
                      ->setOrderDate(date('c'))
                      ->useInvoicePayment()
                        ->doRequest();

            //If CreateOrder accepted redirect to thankyou page

            if ($svea->accepted == 1) {
                $response = array();
                //If Auto deliver order is set, DeliverOrder
                if($this->config->get('svea_invoice_auto_deliver') == 1){
                    $deliverObj = WebPay::deliverOrder($conf);
                    //Product rows
                    $deliverObj = $this->formatOrderRows($deliverObj, $products);
                    //InvoiceFee
                    if ($this->config->get('svea_fee_status') == 1) {
                    $deliverObj = $this->formatInvoiceFeeRows($deliverObj);
                    }
                     //Shipping
                    if ($this->cart->hasShipping() == 1) {
                    $deliverObj = $this->formatShippingFeeRows($deliverObj);
                    }
                     //Get coupons
                    if (isset($this->session->data['coupon'])) {
                        $coupon = $this->model_checkout_coupon->getCoupon($this->session->data['coupon']);
                        $deliverObj = $this->formatCouponRows($deliverObj,$coupon);
                    }
                     //Get vouchers
                    if (isset($this->session->data['voucher']) && floatval(VERSION) >= 1.5) {
                        $voucher = $this->model_checkout_voucher->getVoucher($this->session->data['voucher']);
                        $deliverObj = $this->formatVoucher($deliverObj,$voucher);
                        //$totalPrice = $this->cart->getTotal();
                   }
                   $deliverObj = $deliverObj->setCountryCode($countryCode)
                                ->setOrderId($svea->sveaOrderId)
                                ->setInvoiceDistributionType('Post') //set in admin interface
                                    ->deliverInvoiceOrder()
                                    ->doRequest();
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
                $response = array("error" => $this->responseCodes($this->resultcode,$svea->errormessage));
            }

            echo json_encode($response);

        }



        public function getAddress() {
                include('svea/Includes.php');

                $this->load->model('payment/svea_invoice');
                $this->load->model('checkout/order');

                $order = $this->model_checkout_order->getOrder($this->session->data['order_id']);
                $countryCode = $order['payment_iso_code_2'];

                 //Testmode
                $conf = ($this->config->get('svea_invoice_testmode') == 1) ? (new OpencartSveaConfigTest($this->config)) : new OpencartSveaConfig($this->config);
                $svea = WebPay::getAddresses($conf)
                    ->setOrderTypeInvoice()
                    ->setCountryCode($countryCode);

                if($_GET['company'] == 'true')
                    $svea = $svea->setCompany($_GET['ssn']);
                else
                    $svea = $svea->setIndividual($_GET['ssn']);

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

                echo json_encode($result);

            }

    private function formatOrderRows($svea,$products){
        $this->load->language('payment/svea_invoice');
                //Product rows
        foreach ($products as $product) {
            $productPriceExVat  = $this->currency->format($product['price'],'',false,false);
            //Get the tax, difference in version 1.4.x
            if(floatval(VERSION) >= 1.5){
                $productTax = $this->currency->format($this->tax->getTax($product['price'], $product['tax_class_id']),'',false,false);
                 $productPriceIncVat = $productPriceExVat + $productTax;
            }  else {

                $taxRate = $this->currency->format($this->tax->getRate($product['tax_class_id']));
                $productPriceIncVat = (($taxRate * 0.01) +1) * $productPriceExVat;

            }
            $svea = $svea
                    ->addOrderRow(Item::orderRow()
                        ->setQuantity($product['quantity'])
                        ->setAmountExVat($productPriceExVat)
                        ->setAmountIncVat($productPriceIncVat)
                        ->setName($product['name'])
                        ->setUnit($this->language->get('unit'))
                        ->setArticleNumber($product['product_id'])
                        ->setDescription($product['model'])
                    );

        }
        return $svea;
    }

    private function formatInvoiceFeeRows($svea) {
         $this->load->language('payment/svea_invoice');
         $this->load->language('total/svea_fee');
            //Invoice Fee
            $invoiceFeeExTax = $this->currency->format($this->config->get('svea_fee_fee'),'',false,false);
            $invoiceFeeTaxId = $this->config->get('svea_fee_tax_class_id');

            $invoiceTax = 0;

            if($invoiceFeeTaxId > 0){
                    if(floatval(VERSION) >= 1.5){
                   $invoiceTax =$this->tax->getTax($invoiceFeeExTax, $invoiceFeeTaxId);
                    $invoiceFeeIncVat = $invoiceFeeExTax + $invoiceTax;
               }  else {

                   $taxRate = $this->currency->format($this->tax->getRate($invoiceFeeTaxId));
                   $invoiceFeeIncVat = (($taxRate * 0.01) +1) * $invoiceFeeExTax;

               }
            }

            $svea = $svea
                    ->addFee(
                        Item::invoiceFee()
                            ->setAmountExVat($invoiceFeeExTax)
                            ->setAmountIncVat($invoiceFeeIncVat)
                            ->setName($this->language->get('text_svea_fee'))
                            ->setUnit($this->language->get('unit'))
                        );

        return $svea;
    }

    public function formatShippingFeeRows($svea) {
         $this->load->language('payment/svea_invoice');
        //Shipping Fee
           $shipping_info = $this->session->data['shipping_method'];
            $shippingExVat = $this->currency->format($shipping_info["cost"],'',false,false);

            if (floatval(VERSION) >= 1.5){
                $shippingTax = $this->tax->getTax($shippingExVat, $shipping_info["tax_class_id"]);
                $shippingIncVat = $shippingExVat + $shippingTax;
            }else{
                $taxRate = $this->currency->format($this->tax->getRate($shipping_info['tax_class_id']));
                $shippingIncVat = (($taxRate * 0.01) +1) * $shippingExVat;
            }

            $svea = $svea
                    ->addFee(
                        Item::shippingFee()
                            ->setAmountExVat($shippingExVat)
                            ->setAmountIncVat($shippingIncVat)
                            ->setName($shipping_info["title"])
                            ->setDescription($shipping_info["text"])
                            ->setUnit($this->language->get('unit'))
                       );


        return $svea;
    }

    private function formatCouponRows($svea, $coupon) {
        if ($coupon['type'] == 'F') {
            $discount = $this->currency->format($coupon['discount'],'',false,false);

            $svea = $svea
                    ->addDiscount(
                        Item::fixedDiscount()
                            ->setAmountIncVat($discount)
                            ->setName($coupon['name'])
                            ->setUnit($this->language->get('unit'))
                        );


        } elseif ($coupon['type'] == 'P') {

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

    private function formatVoucher($svea, $voucher) {
        $voucherAmount =  $this->currency->format($voucher['amount'],'',false,false);
        $svea = $svea
                ->addDiscount(
                    Item::fixedDiscount()
                        ->setAmountIncVat($voucherAmount)
                        ->setName($voucher['code'])
                        ->setDescription($voucher["message"])
                        ->setUnit($this->language->get('unit'))
                    );
        return $svea;
    }

}
?>
