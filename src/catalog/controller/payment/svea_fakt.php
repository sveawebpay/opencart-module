<?php
class ControllerPaymentsveafakt extends Controller {
	protected function index() {
    	$this->data['button_confirm'] = $this->language->get('button_confirm');
		$this->data['button_back'] = $this->language->get('button_back');
        
		$this->data['continue'] = 'index.php?route=checkout/success';

		if ($this->request->get['route'] != 'checkout/guest_step_3') {
			$this->data['back'] = 'index.php?route=checkout/payment';
		} else {
			$this->data['back'] = 'index.php?rout=checkout/guest_step_2';
		}
		
        //Invoice Fee
        $invoiceFee = $this->config->get('svea_invoicefee');
        if ($invoiceFee > 0){
            $this->data['invoiceFee'] = $invoiceFee;
        }
        
		$this->id = 'payment';

		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/svea_fakt.tpl')) {
			$this->template = $this->config->get('config_template') . '/template/payment/svea_fakt.tpl';
		} else {
			$this->template = 'default/template/payment/svea_fakt.tpl';
		}	
		
		$this->render();
	}
    
    private function responseCodes($err){
        $this->load->language('payment/svea_fakt');
        
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
            
        }
    }
	
	public function confirm() {
		$this->load->model('checkout/order');
		$this->load->model('payment/svea_fakt');
        $this->load->model('checkout/coupon');
        
        // Get the products in the cart
		$products = $this->cart->getProducts();
        
        //Get coupons
        if (isset($this->session->data['coupon'])){
            $coupon = $this->model_checkout_coupon->getCoupon($this->session->data['coupon']);           
        }
        
        //Settings and fees'
        $username = $this->config->get('svea_fakt_username');
        $pass = $this->config->get('svea_fakt_password');
        $clientNo = $this->config->get('svea_fakt_clientno');
        $invoiceFee = $this->config->get('svea_invoicefee');
        $testMode = $this->config->get('svea_fakt_testmode');
        $shipping = $this->cart->hasShipping();
        
        //Check if company or private
        $company = ($_GET['company'] == 'true') ? true : false;

        
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
        
        //Invoice Fee
        if ($invoiceFee > 0){
            
            $invoiceTax = 25;
            $invoicePrice = $invoiceFee/1.25;
            
            $clientInvoiceRows[] = Array(
                                  "ClientOrderRowNr" => $n+1,
                                  "Description" => "Faktureringsavg",
                                  "PricePerUnit" => $invoicePrice,
                                  "NrOfUnits" => "1",
                                  "Unit" => "st",
                                  "VatPercent" => $invoiceTax,
                                  "DiscountPercent" => 0
                                );
            
        }
        
        
        //Shipping Fee
        if ($shipping == '1'){
            
            $shipping_info = $this->session->data['shipping_method'];
            $shippingTax = 25;
            
            if ($shipping_info['cost'] > 0){
                $clientInvoiceRows[] = Array(
                                  "ClientOrderRowNr" => $n+2,
                                  "Description" => 'Fraktavgift',
                                  "PricePerUnit" => $shipping_info['cost'],
                                  "NrOfUnits" => "1",
                                  "Unit" => "st",
                                  "VatPercent" => $shippingTax,
                                  "DiscountPercent" => 0
                                );
            }
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
              "Order" => Array(
        		"ClientOrderNr" => $this->session->data['order_id'],
                "CountryCode" => 'SE',
                "SecurityNumber" => $_GET['ssn'],
                "IsCompany" => $company,
                "OrderDate" => date('c'),
        		"AddressSelector" => $_GET['addSel'],
                "PreApprovedCustomerId" => 0
              ),
              
              "InvoiceRows" => array('ClientInvoiceRowInfo' => $clientInvoiceRows)
            );
           
        
        //Put all the data in request tag
        $data['request'] = $request;
        
        //Check if testmode is enabled        
        if ($testMode == '1'){
            $svea_server = "https://webservices.sveaekonomi.se/webpay_test/SveaWebPay.asmx?WSDL";
        }else{
            $svea_server = "https://webservices.sveaekonomi.se/webpay/SveaWebPay.asmx?WSDL";
        }
        
        //print_r($data);
        //die();
        //Call Soap
        $client = new SoapClient( $svea_server );

         //Make soap call to below method using above data
        $svea_req = $client->CreateOrder( $data );   
        $response = $svea_req->CreateOrderResult->RejectionCode;
        
        //If response accepted redirect to thankyou page
        if($response == 'Accepted'){
            
            if ($invoiceFee > 0){
                
                $order_id = $this->session->data['order_id'];
                $invoiceTaxPrice = $invoiceFee - $invoicePrice;
                
                if (floatval(VERSION) >= 1.5){
                                  
                $this->db->query("INSERT INTO `" . DB_PREFIX . "order_product` (order_id,product_id,name,model,price,total,tax,quantity) 
                                  VALUES ('".$order_id."','','Faktureringsavgift','','".$invoicePrice."','".$invoicePrice."','".$invoiceTaxPrice."','1')");
                $this->db->query("UPDATE `" . DB_PREFIX . "order_total` SET value = value+".$invoiceFee.", text = CONCAT(FORMAT(value,0), 'kr')  
                                  WHERE order_id = '" . $order_id . "' AND code = 'total'");
                $this->db->query("UPDATE `" . DB_PREFIX . "order_total` SET value = value+".$invoiceTaxPrice.", text = CONCAT(FORMAT(value,0), 'kr')  
                                  WHERE order_id = '" . $order_id . "' AND code = 'tax'");
                $this->db->query("UPDATE `" . DB_PREFIX . "order_total` SET value = value+".$invoicePrice.", text = CONCAT(FORMAT(value,0), 'kr')  
                                  WHERE order_id = '" . $order_id . "' AND code = 'sub_total'");
                $this->db->query("UPDATE `" . DB_PREFIX . "order` SET total = total+".$invoiceFee." 
                                  WHERE order_id = '" . $order_id . "'");
                }else{
                
                $this->db->query("INSERT INTO `" . DB_PREFIX . "order_product` (order_id,product_id,name,model,price,total,tax,quantity) 
                                  VALUES ('".$order_id."','','Faktureringsavgift','','".$invoicePrice."','".$invoicePrice."','".$invoiceTaxPrice."','1')");
                $this->db->query("UPDATE `" . DB_PREFIX . "order_total` SET value = value+".$invoiceFee.", text = CONCAT(FORMAT(value,0), 'kr')  
                                  WHERE order_id = '" . $order_id . "' AND sort_order = '99'");
                $this->db->query("UPDATE `" . DB_PREFIX . "order_total` SET value = value+".$invoiceTaxPrice.", text = CONCAT(FORMAT(value,0), 'kr')  
                                  WHERE order_id = '" . $order_id . "' AND sort_order = '5'");
                $this->db->query("UPDATE `" . DB_PREFIX . "order_total` SET value = value+".$invoicePrice.", text = CONCAT(FORMAT(value,0), 'kr')  
                                  WHERE order_id = '" . $order_id . "' AND sort_order = '0'");
                $this->db->query("UPDATE `" . DB_PREFIX . "order` SET total = total+".$invoiceFee." 
                                  WHERE order_id = '" . $order_id . "'");
                }
            }
            
            $this->model_checkout_order->confirm($this->session->data['order_id'], $this->config->get('svea_fakt_order_status_id'));
            echo 978;
        }else{
            
            echo "Error: ".$this->responseCodes($response);
        }
	}
    
    public function getAddress(){       
        
        $this->load->model('payment/svea_fakt');
        
        $username = $this->config->get('svea_fakt_username');
        $pass = $this->config->get('svea_fakt_password');
        $clientNo = $this->config->get('svea_fakt_clientno');
        
        $company = ($_GET['company'] == 'true') ? true : false;
        
        
        $request = Array(
              "Auth" => Array(
                "Username" => $username,
                "Password" => $pass,
                "ClientNumber" => $clientNo
               ),
        	  "IsCompany" => $company,
        	  "CountryCode" => "SE",
        	  "SecurityNumber" => $_GET['ssn']
        	);
        
        //Put all the data in request tag
        $data['request'] = $request;

        $svea_server = "";
        $testMode = $this->config->get('svea_fakt_testmode');
        
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
        	echo '  $("#svea_fakt_div").hide();
                    $("#svea_fakt_err").show();
                    $("#svea_fakt_err").append("'.$response->GetAddressesResult->ErrorMessage.'");
                    $("a#checkout").hide();
        		  ';
        }
        elseif(is_array($response->GetAddressesResult->Addresses->CustomerAddress)){
        		foreach ($response->GetAddressesResult->Addresses->CustomerAddress as $key => $info){
        		  
                    $addressline1 = (isset($info->AddressLine1)) ? $info->AddressLine1 : "";
                    $addressline2 = (isset($info->AddressLine2)) ? $info->AddressLine2 : "";
                    
                    $address = ($addressline1 !== "" && $addressline2 !== "") ? $addressline1 . " - " . $addressline2 : $addressline1 . $addressline2;

        			$legelName = (isset($info->LegalName)) ? $info->LegalName : "";
                    $postCode = (isset($info->Postcode)) ? $info->Postcode : "";
                    $city = (isset($info->Postarea)) ? $info->Postarea : "";
                    $addressSelector = (isset($info->AddressSelector)) ? $info->AddressSelector : "";
        			
        			//Send back to user
        			echo '$("#svea_fakt_address").append(\'<option id="adress_'.$key.'" value="'.$addressSelector.'">'.$legelName.', '.$address.', '.$postCode.' '.$city.'</option>\');';
        		}
                
                echo "$(\"#svea_fakt_err\").hide();";
                echo "$(\"#svea_fakt_div\").show();";
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
        		$("#svea_fakt_address").append(\'<option id="adress" value="'.$addressSelector.'">'.$legalName.', '.$address.', '.$postCode.' '.$city.'</option>\');
                $("#svea_fakt_div").show();
                $("#svea_fakt_err").hide();
                $("a#checkout").show();
                ';
        }
        else {
        	echo '  $("#svea_fakt_div").hide();
                    $("#svea_fakt_err").show();
                    $("#svea_fakt_err").append("No address was found.");
                    $("a#checkout").hide();
        		  ';
        }
        
    }
}
?>