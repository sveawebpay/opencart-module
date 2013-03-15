<?php
class ControllerPaymentsveacard extends Controller {
    protected function index() {
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
        
        $this->data['continue'] = 'index.php?route=payment/svea_card/redirectSvea';        
        
        $this->render();
    }    
    
    public function redirectSvea(){ 
        $this->load->model('checkout/coupon');
        $this->load->model('checkout/order');
        $this->load->model('payment/svea_card');
        $this->load->model('localisation/currency');
        $this->load->language('payment/svea_card');
        include('svea/Includes.php');
        $svea = WebPay::createOrder();
        
          //Get order information
        $order = $this->model_checkout_order->getOrder($this->session->data['order_id']);
    
       
    /**
        //SVEA config settings
        $config = SveaConfig::getConfig();
        $config->merchantId = $this->config->get('svea_card_merchant_id'); 
        $config->secret = $this->config->get('svea_card_sw'); 
        $paymentRequest = new SveaPaymentRequest();
        $order = new SveaOrder();
        $paymentRequest->order = $order;
        
     * 
     */       
       //Product rows         
        $products = $this->cart->getProducts();  
        foreach($products as $product){
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
                        ->setUnit($this->language->get('unit'))
                        ->setArticleNumber($product['product_id'])
                        ->setDescription($product['model'])   
                    );
                  
        }

        
        //Shipping Fee
        if ( $this->cart->hasShipping() == 1){
            $shipping_info = $this->session->data['shipping_method'];            
            $shippingCost = $this->currency->format($shipping_info["cost"],'',false,false);            
            $shippingTax = (floatval(VERSION) >= 1.5) ? $this->tax->getTax($shippingCost, $shipping_info["tax_class_id"]) : $this->tax->getRate($shipping_info["tax_class_id"]) ;

            if ($shipping_info['cost'] > 0){
                $svea
                ->addFee(Item::shippingFee()              
                    ->setAmountExVat($shippingCost)
                    ->setAmountIncVat( $shippingCost + $shippingTax)
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
        }
       
        //Get vouchers
        if (isset($this->session->data['voucher'])) {
            $voucher = $this->model_checkout_voucher->getVoucher($this->session->data['voucher']);

            $totalPrice = $this->cart->getTotal();
            
            $voucherAmount =  $this->currency->format($voucher['amount'],'',false,false);
            
            $svea = $svea
                    ->addDiscount(
                        Item::fixedDiscount()                
                            ->setAmountIncVat($voucherAmount)
                            ->setName($voucher['code'])
                            ->setDescription($voucher["message"])
                            ->setUnit($this->language->get('unit'))
                        );

        }
       
        //testmode
          //Testmode
        if($this->config->get('svea_invoice_testmode') == 1)
                $svea = $svea->setTestmode();
         $svea = $svea 
                ->setCountryCode($order['payment_iso_code_2'])
                ->setCurrency($this->session->data['currency'])
                ->setClientOrderNumber($this->session->data['order_id'])
                ->setOrderDate(date('c'))
                ->usePayPageCardOnly()
                    ->setCancelUrl(HTTP_SERVER.'index.php?route=payment/svea_card/responseSvea')
                    //->setMerchantIdBasedAuthorization($this->config->get('svea_card_merchant_id'), $this->config->get('svea_card_sw'))
                    ->setReturnUrl(HTTP_SERVER.'index.php?route=payment/svea_card/responseSvea')
                    ->getPaymentForm();
      /**
        //Set base data for the order
        $order->amount = number_format(round($totalPrice,2),2,'','');
        $order->customerRefno = $this->session->data['order_id'].'test2';
        $order->returnUrl = HTTP_SERVER.'index.php?route=payment/svea_card/responseSvea';
        $order->vat = number_format(round($totalTax,2),2,'','');
        $order->currency = $this->session->data['currency'];
        $order->paymentMethod = 'CARD';
                
        $paymentRequest->createPaymentMessage();
        $request = http_build_query($paymentRequest,'','&');
       
       * 
       */
      print_r($svea->completeHtmlFormWithSubmitButton);
      /**
      die();
        echo '<html><head>
                <script type="text/javascript">
                    function doPost(){
                            document.forms[0].submit();
                        }
                </script>
                </head>
                <body onload="doPost()">
                ';
        //Check for testmode
        if ($this->config->get('svea_card_testmode') == '1'){
        	echo $paymentRequest->getPaymentForm(true);
        }else{
        	echo $paymentRequest->getPaymentForm(false);
        }
        
        echo '
            </body></html>
        ';
        
        exit();
        **/
    }
    
    public function responseSvea(){
       
        $this->load->model('checkout/order');
        $this->load->model('payment/svea_card');
        
        include('svea/src/Includes.php'); //NEW LINE
       //require_once('svea/SveaConfig.php');   
      
        //GETs
        $response = $_REQUEST['response'];
        $mac = $_REQUEST['mac'];
        $merchantid = $_REQUEST['merchantid'];
        $secretWord = $this->config->get('svea_card_sw');
       
       //$resp = new SveaPaymentResponse($response);
        $resp = new SveaResponse($_REQUEST,"8a9cece566e808da63c6f07ff415ff9e127909d000d259aba24daa2fed6d9e3f8b0b62e8ad1fa91c7d7cd6fc3352deaae66cdb533123edf127ad7d1f4c77e7a3"); //NEW LINE
      
          var_dump($resp);
        /**
        $d['order_id'] = $resp->customerRefno;
        
        if($resp->validateMac($mac,$secretWord) == true){
            if ($resp->statuscode == '0'){
                $this->model_checkout_order->confirm($this->session->data['order_id'], $this->config->get('svea_card_order_status_id'));
            
                header("Location: index.php?route=checkout/success");
                flush();
            }else{
                $this->session->data['error_warning'] = $this->responseCodes($resp->statuscode);
                $this->renderFailure($resp->statuscode);
            }
        }else{
            $this->renderFailure("Could not validate mac");
        }
         * 
         * 
         */
    }
    
    private function renderFailure($rejection)
    {
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
        		
        $this->data['text_message'] = "Dessvärre misslyckades betalningen.<br />Med anledningen: <br /><br />".$this->responseCodes($rejection)."<br /><br /><br />";
        $this->data['heading_title'] = "Betalning misslyckades";
        $this->data['footer'] = "";
                                
        $this->data['button_continue'] = $this->language->get('button_continue');
		$this->data['button_back'] = $this->language->get('button_back');
            
        $this->data['continue'] = 'index.php?route=checkout/cart';              
		$this->response->setOutput($this->render(TRUE), $this->config->get('config_compression'));
    }
    
    private function responseCodes($err){
        $this->load->language('payment/svea_card');
        
        switch ($err){
            case "100" :
                return $this->language->get('response_100');
                break;
            case "105" :
                return $this->language->get('response_105');
                break;
            case "106" :
                return $this->language->get('response_106');
                break;
            case "107" :
                return $this->language->get('response_107');
                break;
            case "108" :
                return $this->language->get('response_108');
                break;
            case "109" :
                return $this->language->get('response_109');
                break;
            case "114" :
                return $this->language->get('response_114');
                break;
            case "121" :
                return $this->language->get('response_121');
                break;
            case "122" :
                return $this->language->get('response_122');
                break;
            case "123" :
                return $this->language->get('response_123');
                break;
            case "127" :
                return $this->language->get('response_127');
                break;
            case "129" :
                return $this->language->get('response_129');
                break;
        }
    }
    
}
?>