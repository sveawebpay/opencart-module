<?php
class ControllerPaymentsveadelbet extends Controller {
	protected function index() {
	   $this->load->language('payment/svea_delbet');
    	$this->data['button_confirm'] = $this->language->get('button_confirm');
		$this->data['button_back'] = $this->language->get('button_back');
        
		$this->data['continue'] = 'index.php?route=checkout/success';

		if ($this->request->get['route'] != 'checkout/guest_step_3') {
			$this->data['back'] = 'index.php?route=checkout/payment';
		} else {
			$this->data['back'] = 'index.php?rout=checkout/guest_step_2';
		}
		
		$this->id = 'payment';
        $shippingCost = ($this->cart->hasShipping() == '1') ? $this->session->data['shipping_method']['cost'] : 0;
        $total = $this->cart->getTotal() + $shippingCost;

        if ($total < 1000){
            $this->data['delbet_fail'] = $this->language->get('text_delbet_fail');
        }
        
        
		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/svea_delbet.tpl')) {
			$this->template = $this->config->get('config_template') . '/template/payment/svea_delbet.tpl';
		} else {
			$this->template = 'default/template/payment/svea_delbet.tpl';
		}	
		
		$this->render();
	}
    
    private function responseCodes($err){
        $this->load->language('payment/svea_delbet');
        
        switch ($err){
            case "CusomterCreditRejected" :
                return $this->language->get('response_CusomterCreditRejected');
                break;
            case "CustomerOverCreditLimit" :
                return $this->language->get('response_CustomerOverCreditLimit');
                break;
            case "CustomerAbuseBlock" :
                return $this->language->get('response_CustomerAbuseBlock');
                break;
            case "OrderExpired" :
                return $this->language->get('response_OrderExpired');
                break;
            case "ClientOverCreditLimit" :
                return $this->language->get('response_ClientOverCreditLimit');
                break;
            case "OrderOverSveaLimit" :
                return $this->language->get('response_OrderOverSveaLimit');
                break;
            case "OrderOverClientLimit" :
                return $this->language->get('response_OrderOverClientLimit');
                break;
            case "CustomerSveaRejected" :
                return $this->language->get('response_CustomerSveaRejected');
                break;
            case "CustomerCreditNoSuchEntity" :
                return $this->language->get('response_CustomerCreditNoSuchEntity');
                break;
            default:
                return "Could not create partpaymentplan, it could be that the totalamount is to high or to low.";
                break;
            
        }
    }
	
	public function confirm() {
		$this->load->model('checkout/order');
		$this->load->model('payment/svea_delbet');
        $this->load->model('checkout/coupon');

        // Get the products in the cart
		$products = $this->cart->getProducts();
        
        //Get coupons
        if (isset($this->session->data['coupon'])){
            $coupon = $this->model_checkout_coupon->getCoupon($this->session->data['coupon']);
        }

        //Settings and fees'
        $username = $this->config->get('svea_username');
        $pass = $this->config->get('svea_password');
        $clientNo = $this->config->get('svea_delbet_clientno');
        $testMode = $this->config->get('svea_delbet_testmode');
        $shipping = $this->cart->hasShipping();

        
        //Product rows
        $n = 0;       
        foreach($products as $product){
            
            if (floatval(VERSION) >= 1.5){
                $tax = ($this->tax->getTax($product['price'],$product['tax_class_id'])/$product['price'])*100;
            }else{
                $tax = $this->tax->getRate($product['tax_class_id']);
            }         
            
            $productPrice = $product['price'];

            
            $rows = Array(
                      "ClientOrderRowNr" => $n,
                      "Description" => $product['name'],
                      "PricePerUnit" => $productPrice,
                      "NrOfUnits" => $product['quantity'],
                      "Unit" => "st",
                      "VatPercent" => $tax,
                      "DiscountPercent" => 0
                    );
            
            if (isset($clientInvoiceRows)){
                $clientInvoiceRows[$n] = $rows;
            }else{
                $clientInvoiceRows[] = $rows;
            }
            
            $n++;
        }
        
        //Shipping Fee
        if ($shipping == '1'){
            
            $shipping_info = $this->session->data['shipping_method'];
            $shippingTax = 25;
            
            if ($shipping_info['cost'] > 0){
                $clientInvoiceRows[] = Array(
                                  "ClientOrderRowNr" => $n,
                                  "Description" => 'Fraktavgift',
                                  "PricePerUnit" => $shipping_info['cost'],
                                  "NrOfUnits" => "1",
                                  "Unit" => "st",
                                  "VatPercent" => $shippingTax,
                                  "DiscountPercent" => 0
                                );
            }
            $n++;
        }
        
        //Add coupon
        if (isset($coupon)){
            
            $totalPrice = $total = $this->cart->getTotal();
            
            if ($coupon['type'] == 'F') {
                $discount = $coupon['discount'];
            } elseif ($coupon['type'] == 'P') {
                $discount = ($coupon['discount'] / 100) * $totalPrice;
            }
            
            $discountAmount = $discount;
            
            $clientInvoiceRows[] = Array(
                                  "ClientOrderRowNr" => $n+3,
                                  "Description" => $coupon['name'],
                                  "PricePerUnit" => -$discountAmount,
                                  "NrOfUnits" => "1",
                                  "Unit" => "st",
                                  "VatPercent" => 25,
                                  "DiscountPercent" => 0
                                );
            
        }
       
        
        //The createOrder Data
        $request = Array(
              "Auth" => Array(
                "Username" => $username,
                "Password" => $pass,
                "ClientNumber" => $clientNo
               ),
              'Amount' => 0,
            	'PayPlan' => Array(
            		'SendAutomaticGiropaymentForm' => false,
                    'ClientPaymentPlanNr' => $this->session->data['order_id'],
            		'CampainCode' => $_GET['paySel'],
            		'CountryCode' => 'SE',
                    "AddressSelector" => $_GET['addSel'],
            		'SecurityNumber' => $_GET['ssn'],
            		'IsCompany' => false
            ),
            "InvoiceRows" => array('ClientInvoiceRowInfo' => $clientInvoiceRows)
        );   
        
        //Put all the data in request tag
        $data['request'] = $request;
        
        $svea_server = "";
        
        //Check if testmode is enabled        
        if ($testMode == '1'){
            $svea_server = "https://webservices.sveaekonomi.se/webpay_test/SveaWebPay.asmx?WSDL";
        }else{
            $svea_server = "https://webservices.sveaekonomi.se/webpay/SveaWebPay.asmx?WSDL";
        }
        
        //Call Soap
        $client = new SoapClient( $svea_server );
        
         //Make soap call to below method using above data
        $svea_req = $client->CreatePaymentPlan( $data );   
        $response = $svea_req->CreatePaymentPlanResult->RejectionCode;

        //If response accepted redirect to thankyou page
        if($response == 'Accepted'){
            $this->model_checkout_order->confirm($this->session->data['order_id'], $this->config->get('svea_delbet_order_status_id'));
            echo 978;
        }else{
            
            echo "Error: ".$this->responseCodes($response);
        }
	}
    
    public function getAddress(){       
        
        $this->load->model('payment/svea_delbet');
        
        $username = $this->config->get('svea_username');
        $pass = $this->config->get('svea_password');
        $clientNo = $this->config->get('svea_delbet_clientno');
        $testMode = $this->config->get('svea_delbet_testmode');
        
        
        $request = Array(
              "Auth" => Array(
                "Username" => $username,
                "Password" => $pass,
                "ClientNumber" => $clientNo
               ),
        	  "IsCompany" => false,
        	  "CountryCode" => "SE",
        	  "SecurityNumber" => $_GET['ssn']
        	);
        
        //Put all the data in request tag
        $data['request'] = $request;

        //Check if testmode is enabled        
        if ($testMode == '1'){
            $svea_server = "https://webservices.sveaekonomi.se/webpay_test/SveaWebPay.asmx?WSDL";
        }else{
            $svea_server = "https://webservices.sveaekonomi.se/webpay/SveaWebPay.asmx?WSDL";
        }       
        
        //Call Soap
        $client = new SoapClient( $svea_server );
        
         //Make soap call to below method using above data
        $response = $client->GetAddresses( $data ); 
        
        if (isset($response->GetAddressesResult->ErrorMessage)){
    	echo '  $("#svea_delbet_fakt").hide();
                $("#svea_delbet_err").show();
                $("#svea_delbet_err").append("'.$response->GetAddressesResult->ErrorMessage.'");
                $("a#checkout").hide();
    		  ';
        } else if (is_array($response->GetAddressesResult->Addresses->CustomerAddress)){
        		foreach ($response->GetAddressesResult->Addresses->CustomerAddress as $key => $info){
        
                    $addressline1 = (isset($info->AddressLine1)) ? $info->AddressLine1 : "";
                    $addressline2 = (isset($info->AddressLine2)) ? $info->AddressLine2 : "";
                    
                    $address = ($addressline1 !== "" && $addressline2 !== "") ? $addressline1 . " - " . $addressline2 : $addressline1 . $addressline2;

        			$legelName = (isset($info->LegalName)) ? $info->LegalName : "";
                    $postCode = (isset($info->Postcode)) ? $info->Postcode : "";
                    $city = (isset($info->Postarea)) ? $info->Postarea : "";
                    $addressSelector = (isset($info->AddressSelector)) ? $info->AddressSelector : "";
        			
        			
        			//Send back to user
        			echo '$("#svea_delbet_address").append(\'<option id="adress_'.$key.'" value="'.$addressSelector.'">'.$legelName.', '.$address.', '.$postCode.' '.$city.'</option>\');';
                    
        		}
                echo "$(\"#svea_delbet_tr\").show();";
                echo "$(\"#svea_delbet_address\").show();";
                echo "$(\"#svea_delbet_err\").hide();";
                echo "$(\"a#checkout\").show();";
                
        } else if(isset($response->GetAddressesResult->Addresses->CustomerAddress)) {
            
                $customerAddress = $response->GetAddressesResult->Addresses->CustomerAddress;
                
                $addressline1 = (isset($customerAddress->AddressLine1)) ? $customerAddress->AddressLine1 : "";
                $addressline2 = (isset($customerAddress->AddressLine2)) ? $customerAddress->AddressLine2 : "";
                
                $address = ($addressline1 !== "" && $addressline2 !== "") ? $addressline1 . " - " . $addressline2 : $addressline1 . $addressline2;

        		$legalName = (isset($customerAddress->LegalName)) ? $customerAddress->LegalName : "";
                $postCode = (isset($customerAddress->Postcode)) ? $customerAddress->Postcode : "";
                $city = (isset($customerAddress->Postarea)) ? $customerAddress->Postarea : "";
                $addressSelector = (isset($customerAddress->AddressSelector)) ? $customerAddress->AddressSelector : "";
        		
        		//Send back to user
        		echo '
        		$("#svea_delbet_address").append(\'<option id="adress" value="'.$addressSelector.'">' . $legalName . ', '.$address.', '.$postCode.' '.$city.'</option>\');
                $("#svea_delbet_address").show();
                $("#svea_delbet_tr").show();
                $("#svea_delbet_err").hide();
                $("a#checkout").show();
                ';	
         
        }
        
        else {
        	echo '  $("#svea_delbet_tr").hide();
                    $("#svea_delbet_err").show();
                    $("#svea_delbet_err").append("No address was found.");
                    $("a#checkout").hide();
        		  ';
        }
    }
    
    
    public function getPaymentOptions(){
        $this->load->model('checkout/order');
		$this->load->model('payment/svea_delbet');

        // Get the products in the cart
		$products = $this->cart->getProducts();
        
        //Settings and fees'
        $username = $this->config->get('svea_username');
        $pass = $this->config->get('svea_password');
        $clientNo = $this->config->get('svea_delbet_clientno');
        $testMode = $this->config->get('svea_delbet_testmode');
        $shipping = $this->cart->hasShipping();

        //Product rows
        $n = 0;       
        foreach($products as $product){
 
            if (floatval(VERSION) >= 1.5){
                $tax = ($this->tax->getTax($product['price'],$product['tax_class_id'])/$product['price'])*100;
            }else{
                $tax = $this->tax->getRate($product['tax_class_id']);
            }
            $productPrice = $product['price'];
            
            $rows = Array(
                      "ClientOrderRowNr" => $n,
                      "Description" => $product['name'],
                      "PricePerUnit" => $productPrice,
                      "NrOfUnits" => $product['quantity'],
                      "Unit" => "st",
                      "VatPercent" => $tax,
                      "DiscountPercent" => 0
                    );
            
            if (isset($clientInvoiceRows)){
                $clientInvoiceRows[$n] = $rows;
            }else{
                $clientInvoiceRows[] = $rows;
            }
            
            $n++;
        }
        
        
        //Shipping Fee
        if ($shipping == '1'){
            
            $shipping_info = $this->session->data['shipping_method'];
            $shippingTax = 25;
            
            if ($shipping_info['cost'] > 0){
                $clientInvoiceRows[] = Array(
                                  "ClientOrderRowNr" => $n,
                                  "Description" => 'Fraktavgift',
                                  "PricePerUnit" => $shipping_info['cost'],
                                  "NrOfUnits" => "1",
                                  "Unit" => "st",
                                  "VatPercent" => $shippingTax,
                                  "DiscountPercent" => 0
                                );
            }
            $n++;
        }

        //The createOrder Data
       $request = Array(
              "Auth" => Array(
                "Username" => $username,
                "Password" => $pass,
                "ClientNumber" => $clientNo
               ),
              "Amount" => 0,
              "InvoiceRows" => array('ClientInvoiceRowInfo' => $clientInvoiceRows)
        );

        //Put all the data in request tag
        $data['request'] = $request;
        
        //Check if testmode is enabled        
        if ($testMode == '1'){
            $svea_server = "https://webservices.sveaekonomi.se/webpay_test/SveaWebPay.asmx?WSDL";
        } else {
            $svea_server = "https://webservices.sveaekonomi.se/webpay/SveaWebPay.asmx?WSDL";
        }
        
        //Call Soap
        $client = new SoapClient( $svea_server );

         //Make soap call to below method using above data
        $svea_req = $client->GetPaymentPlanOptions( $data); 

        if(!isset($svea_req->GetPaymentPlanOptionsResult)) {
            $this->ShowErrorMessage();
        }
        
        else if(!isset($svea_req->GetPaymentPlanOptionsResult->PaymentPlanOptions)) {
            $this->ShowErrorMessage($svea_req->GetPaymentPlanOptionsResult);
        }
        
        else {
            
            $response = $svea_req->GetPaymentPlanOptionsResult->PaymentPlanOptions;    
            if(is_array($response->PaymentPlanOption)) {
                foreach ($response->PaymentPlanOption as $key => $ss){
                	
                	if ($ss->ContractLengthInMonths == 3){
                		$description = 'Betala om 3 månader';
                	}else{
                		$description = 'Delbetala på '.$ss->ContractLengthInMonths.' månader, ('.$ss->MonthlyAnnuity.' kr/mån)';
                	}
                	echo '$("#svea_delbet_alt").append("<option id=\"paymentOption'.$key.'\" value=\"'.$ss->CampainCode.'\">'.$description.'</option>");';
                }
                
                echo "$(\"#svea_delbet_alt\").show();";
            }
            else {
                $this->ShowErrorMessage();
            }
        }
        
    }
    
    
    private function ShowErrorMessage($response = null) {
        $message = ($response !== null && isset($response->ErrorMessage)) ? $response->ErrorMessage : "Could not get any partpayment alternatives.";
        echo '$("#svea_delbet_div").hide();
              $("#svea_delbet_alt").hide();
              $("#svea_delbet_err").show();
              $("#svea_delbet_err").append("'.$message.'");
              $("a#checkout").hide();';
    }
    
}
?>