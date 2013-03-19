<?php
class ControllerPaymentsveadirectbank extends Controller {
	protected function index() {
            
        //set template
            
    	$this->data['button_confirm'] = $this->language->get('button_confirm');
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
		
        
       $this->data['continue'] = 'index.php?route=payment/svea_directbank/redirectSvea';
        
        
		//$this->render();
	//}
    
    
    //public function redirectSvea(){ 
        $this->load->model('checkout/coupon');
        $this->load->model('checkout/order');
        $this->load->model('payment/svea_directbank');
        $this->load->model('localisation/currency');
        $this->load->language('payment/svea_directbank');
        include('svea/Includes.php');
        $svea = WebPay::createOrder();        
          //Get order information
        $order = $this->model_checkout_order->getOrder($this->session->data['order_id']);
         //Product rows         
        $products = $this->cart->getProducts(); 

        //Product rows   
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
           //Testmode
        if($this->config->get('svea_invoice_testmode') == 1){
              $svea = $svea->setTestmode();
        } 
     
         $form = $svea 
                ->setCountryCode($order['payment_iso_code_2'])
                ->setCurrency($this->session->data['currency'])
                ->setClientOrderNumber($this->session->data['order_id'].  rand(1, 100000))//remove rand after developing
                ->setOrderDate(date('c'))
                ->usePayPageDirectBankOnly()
                    ->setCancelUrl(HTTP_SERVER.'index.php?route=payment/svea_directbank/responseSvea')
                    ->setMerchantIdBasedAuthorization($this->config->get('svea_directbank_merchant_id'),$this->config->get('svea_directbank_sw'))
                    ->setReturnUrl(HTTP_SERVER.'index.php?route=payment/svea_directbank/responseSvea')
                    ->setPayPageLanguage($payPageLanguage)
                    ->getPaymentForm();
        //print form with hidden buttons
        $fields = $form->htmlFormFieldsAsArray;
        $this->data['form_start_tag'] = $fields['form_start_tag'];
        $this->data['merchant_id'] = $fields['input_merchantId'];
        $this->data['input_message'] = $fields['input_message'];
        $this->data['input_mac'] = $fields['input_mac'];
        // $this->data['noscript_p_tag'] = $fields['noscript_p_tag'];
        $this->data['input_submit'] = $fields['input_submit'];        
        $this->data['form_end_tag'] = $fields['form_end_tag'];
        $this->data['submitMessage'] = $this->language->get('button_confirm');
        $this->render();
    }
    
    public function responseSvea(){
          $this->load->model('checkout/order');
        $this->load->model('payment/svea_directbank');  
        $this->load->language('payment/svea_directbank');
        include('svea/Includes.php'); 
             
       //$resp = new SveaPaymentResponse($response);
        $resp = new SveaResponse($_REQUEST,$this->config->get('svea_directbank_sw'));            
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
        /**
        print_r($resp);
        $d['order_id'] = $resp->customerRefno;
        
        
        if($resp->validateMac($mac,$secretWord) == true){
            if ($resp->statuscode == '0'){
                $this->model_checkout_order->confirm($this->session->data['order_id'], $this->config->get('svea_kort_order_status_id'));
            
                header("Location: index.php?route=checkout/success");
                flush();
            }else{
                $this->session->data['error_warning'] = $this->responseCodes($resp->statuscode);
                $this->renderFailure($resp->statuscode);
            }
        }else{
            $this->renderFailure("Could not validate mac");
        }
        
    }
         * 
         */
    
    
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
        $err = strstr($err, "(", TRUE);
        $this->load->language('payment/svea_invoice');
        
        $definition = $this->language->get("response_$err");
        
        if (preg_match("/^response/", $definition))
             $definition = $this->language->get("response_error"). " $msg";
      
        return $definition;
    }
    
}
?>