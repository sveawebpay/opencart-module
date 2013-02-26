<?php
class ControllerPaymentsveakort extends Controller {
	protected function index() {
    	$this->data['button_confirm'] = $this->language->get('button_confirm');
		$this->data['button_back'] = $this->language->get('button_back');
        
		

		if ($this->request->get['route'] != 'checkout/guest_step_3') {
			$this->data['back'] = 'index.php?route=checkout/payment';
		} else {
			$this->data['back'] = 'index.php?rout=checkout/guest_step_2';
		}
		
		$this->id = 'payment';

		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/svea_kort.tpl')) {
			$this->template = $this->config->get('config_template') . '/template/payment/svea_kort.tpl';
		} else {
			$this->template = 'default/template/payment/svea_kort.tpl';
		}	
		
        
       $this->data['continue'] = 'index.php?route=payment/svea_kort/redirectSvea';
        
        
		$this->render();
	}
    
    
    public function redirectSvea(){ 
        $this->load->model('checkout/coupon');
             
        //New SHAHO edit
        //Import SVEA files
        include('svea/svea_bv-old/SveaConfig.php');
    
    
        //SVEA config settings
        $config = SveaConfig::getConfig();
        $config->merchantId = $this->config->get('svea_kort_merchant_id'); 
        $config->secret = $this->config->get('svea_kort_sw'); 
        $paymentRequest = new SveaPaymentRequest();
        $order = new SveaOrder();
        $paymentRequest->order = $order;
        
        //Settings and fees'     
        $shipping = $this->cart->hasShipping();
        $totalPrice = 0;
        $totalTax = 0;    
        
        // Get the products in the cart
		$products = $this->cart->getProducts();
        
        //Get coupons
        if (isset($this->session->data['coupon'])){
            $coupon = $this->model_checkout_coupon->getCoupon($this->session->data['coupon']);
        }

        //Product rows   
        foreach($products as $product){

            if (floatval(VERSION) >= 1.5){
                $tax = $this->tax->getTax($product['price'],$product['tax_class_id']);
            }else{
                $tax = ($this->tax->getRate($product['tax_class_id'])/100)*$product['price'];
            }         
            $productPrice = $product['price'] + $tax;

            $orderRow = new SveaOrderRow();
            $orderRow->amount = number_format(round($productPrice,2),2,'','');
            $orderRow->vat = number_format(round($tax,2),2,'','');
            $orderRow->name = urlencode($product['name']);
            $orderRow->quantity = $product['quantity'];
            $orderRow->unit = "st";
            
            //Add the order rows to your order
            $order->addOrderRow($orderRow);
            
            //Update totals
            $totalPrice = $totalPrice + ($productPrice * $product['quantity']);
            $totalTax = $totalTax + ($tax * $product['quantity']);        
            
        }

        
        //Shipping Fee
        if ($shipping == '1'){
            $shipping_info = $this->session->data['shipping_method'];
            if (floatval(VERSION) >= 1.5){
                $shippingTax = $this->tax->getTax($shipping_info['cost'],$shipping_info['tax_class_id']);
            }else{
                $shippingTax = ($this->tax->getRate($shipping_info['tax_class_id'])/100)*$shipping_info['cost'];
            }
            $shippingPrice = $shipping_info['cost'] + $shippingTax;
            
            if ($shipping_info['cost'] > 0){
                
                //Parameters for order rows
                $orderRow = new SveaOrderRow();
                $orderRow->amount = number_format(round($shippingPrice,2),2,'','');
                $orderRow->vat = number_format(round($shippingTax,2),2,'','');
                $orderRow->name = urlencode($shipping_info['title']);
                $orderRow->quantity = '1';
                $orderRow->unit = "st";
                
                //Add the order rows to your order
                $order->addOrderRow($orderRow);
                
                $totalPrice = $totalPrice + $shippingPrice;
                $totalTax = $totalTax + $shippingTax;
   
            }
            
        }
        
        
        //Add coupon
        if (isset($coupon)){

            $totalCartPrice =  $this->cart->getTotal();

            if ($coupon['type'] == 'F') {
                $discount = $coupon['discount'] * 1.25;
            } elseif ($coupon['type'] == 'P') {
                $discount = (($coupon['discount'] / 100) * $totalCartPrice);
            }
            
            $couponTax = $discount * 0.2;

            //Parameters for order rows
            $orderRow = new SveaOrderRow();
            $orderRow->amount = number_format(round(-$discount,2),2,'','');
            $orderRow->vat = number_format(round(-$couponTax,2),2,'','');
            $orderRow->name = $coupon['name'];
            $orderRow->quantity = '1';
            $orderRow->unit = "st";
            
            //Add the order rows to your order
            $order->addOrderRow($orderRow);
            
            //Totals
            $totalPrice -= $discount;                   
            $totalTax -= $couponTax;
        }
        
        //Set base data for the order
        $order->amount = number_format(round($totalPrice,2),2,'','');
        $order->customerRefno = $this->session->data['order_id'].'test2';
        $order->returnUrl = HTTP_SERVER.'index.php?route=payment/svea_kort/responseSvea';
        $order->vat = number_format(round($totalTax,2),2,'','');
        $order->currency = $this->session->data['currency'];
        $order->paymentMethod = 'CARD';
                
        $paymentRequest->createPaymentMessage();
        $request = http_build_query($paymentRequest,'','&');
       
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
        if ($this->config->get('svea_kort_testmode') == '1'){
        	echo $paymentRequest->getPaymentForm(true);
        }else{
        	echo $paymentRequest->getPaymentForm(false);
        }
        
        echo '
            </body></html>
        ';
        
        exit();
        
    }
    
    public function responseSvea(){
        
        $this->load->model('checkout/order');
		$this->load->model('payment/svea_kort');
        
        
        require_once('svea/SveaConfig.php');   
    
        //GETs
        $response = $_REQUEST['response'];
        $mac = $_REQUEST['mac'];
        $merchantid = $_REQUEST['merchantid'];
        $secretWord = $this->config->get('svea_kort_sw');
        
        $resp = new SveaPaymentResponse($response);
        
        
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
        $this->load->language('payment/svea_kort');
        
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