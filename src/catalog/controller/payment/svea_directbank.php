<?php
class ControllerPaymentsveadirectbank extends Controller {
	protected function index() {

        //set template

    	//$this->data['button_confirm'] = $this->language->get('button_confirm');
    	$this->data['button_continue'] = $this->language->get('button_continue');
        $this->data['button_back'] = $this->language->get('button_back');

        if ($this->request->get['route'] != 'checkout/guest_step_3') {
                $this->data['back'] = 'index.php?route=checkout/payment';
        } else {
                $this->data['back'] = 'index.php?rout=checkout/guest_step_2';
        }
        $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
        $this->data['countryCode'] = $order_info['payment_iso_code_2'];
        $this->id = 'payment';

        if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/svea_directbank.tpl')) {
                $this->template = $this->config->get('config_template') . '/template/payment/svea_directbank.tpl';
        } else {
                $this->template = 'default/template/payment/svea_directbank.tpl';
        }

        $this->data['logo'] = "<img src='admin/view/image/payment/".$this->getLogo($order_info['payment_iso_code_2'])."/svea_directbank.png'>";
        $this->data['svea_banks_base'] = "admin/view/image/payment/svea_direct/";

        /*
         **get my methods, present page
         */
        $this->load->language('payment/svea_directbank');
        include(DIR_APPLICATION.'../svea/Includes.php');

       //Testmode
        $conf = ($this->config->get('svea_directbank_testmode') == 1) ? (new OpencartSveaConfigTest($this->config)) : new OpencartSveaConfig($this->config);
        $svea = WebPay::getPaymentMethods($conf);
        $this->data['sveaMethods'] = $svea
            ->setContryCode($order_info['payment_iso_code_2'])
            ->doRequest();

        $this->data['continue'] = 'index.php?route=payment/svea_directbank/redirectSvea';

        $this->render();

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
         //Product rows
        $products = $this->cart->getProducts();

        //Product rows
        foreach($products as $product){
             $productPriceExVat  = $product['price'] * $order['currency_value'];
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
                        ->setArticleNumber($product['product_id'])
                        ->setDescription($product['model'])
                    );

        }

        //Shipping Fee
        if ( $this->cart->hasShipping() == 1){
            $shipping_info = $this->session->data['shipping_method'];
            $shippingExVat = $shipping_info["cost"];
            $shippingIncVat = 0;
            if (floatval(VERSION) >= 1.5){
                $shippingTax = $this->tax->getTax($shippingExVat, $shipping_info["tax_class_id"]);
                $shippingIncVat = $shippingExVat + $shippingTax;
            }else{
                $taxRate = $this->tax->getRate($shipping_info['tax_class_id']);
                $shippingIncVat = (($taxRate / 100) +1) * $shippingExVat;
            }

            if ($shipping_info['cost'] > 0){
                $svea = $svea
                        ->addFee(Item::shippingFee()
                            ->setAmountExVat(floatval($shippingExVat * $order['currency_value']))
                            ->setAmountIncVat(floatval($shippingIncVat * $order['currency_value']))
                            ->setName($shipping_info['title'])
                            ->setDescription($shipping_info['text'])
                            ->setUnit($this->language->get('unit'))
                            );
            }

        }
        //Add coupon
        if (isset($this->session->data['coupon'])){
        $coupon = $this->model_checkout_coupon->getCoupon($this->session->data['coupon']);

        $totalPrice = $this->cart->getTotal();

        if ($coupon['discount'] > 0 && $coupon['type'] == 'F') {
            $discount = $coupon['discount'];
            $svea = $svea
                    ->addDiscount(
                        Item::fixedDiscount()
                            ->setAmountIncVat(floatval($discount * $order['currency_value']))
                            ->setName($coupon['name'])
                            ->setUnit($this->language->get('unit'))
                        );
        } elseif ($coupon['discount'] > 0 && $coupon['type'] == 'P') {
            $svea = $svea
                    ->addDiscount(
                        Item::relativeDiscount()
                            ->setDiscountPercent(floatval($coupon['discount']))
                            ->setName($coupon['name'])
                            ->setUnit($this->language->get('unit'))
                        );
                }
        }
           //Get vouchers
        if (isset($this->session->data['voucher'])) {
            $voucher = $this->model_checkout_voucher->getVoucher($this->session->data['voucher']);

            $totalPrice = $this->cart->getTotal();

            $voucherAmount = $voucher['amount'];
            $svea = $svea
                    ->addDiscount(
                        Item::fixedDiscount()
                            ->setVatPercent(0)//No vat on voucher. Concidered a debt.
                            ->setAmountIncVat(floatval($voucherAmount * $order['currency_value']))
                            ->setName($voucher['code'])
                            ->setDescription($voucher["message"])
                            ->setUnit($this->language->get('unit'))
                        );


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
         $server_url = $this->config->get('config_url');
         try{
              $form = $svea
                ->setCountryCode($order['payment_iso_code_2'])
                ->setCurrency($this->session->data['currency'])
                ->setClientOrderNumber($this->session->data['order_id'])//remove rand after developing
                ->setOrderDate(date('c'))
                ->usePaymentMethod($_POST['svea_directbank_payment_method'])
                    ->setCancelUrl($server_url.'index.php?route=payment/svea_directbank/responseSvea')
                    ->setReturnUrl($server_url.'index.php?route=payment/svea_directbank/responseSvea')
                    ->setCardPageLanguage($payPageLanguage)
                    ->getPaymentForm();
         }  catch (Exception $e){
            $this->log->write($e->getMessage());
             echo '<div class="attention">Logged Svea Error</div>';
            exit();
         }
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

    public function responseSvea(){
        $this->load->model('checkout/order');
        $this->load->model('payment/svea_directbank');
        $this->load->language('payment/svea_directbank');
        include(DIR_APPLICATION.'../svea/Includes.php');

        //Get the country
        $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
        $countryCode = $order_info['payment_iso_code_2'];

        //Testmode
        $conf = ($this->config->get('svea_directbank_testmode') == 1) ? (new OpencartSveaConfigTest($this->config)) : new OpencartSveaConfig($this->config);

        $resp = new SveaResponse($_REQUEST, $countryCode, $conf);

        $this->session->data['order_id'] = $resp->response->clientOrderNumber;

        if($resp->response->resultcode != '0'){
            if ($resp->response->accepted == '1'){
                $this->model_checkout_order->confirm($this->session->data['order_id'], $this->config->get('svea_directbank_order_status_id'));

                header("Location: index.php?route=checkout/success");
                flush();
            }else{
                $this->session->data['error_warning'] = $this->responseCodes($resp->response->resultcode, $resp->response->errormessage);
                $this->renderFailure($resp->response);
            }
        }else{
            $this->renderFailure($resp->response);
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

}
?>