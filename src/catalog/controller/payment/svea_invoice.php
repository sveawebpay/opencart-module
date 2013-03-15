<?php

class ControllerPaymentsveainvoice extends Controller {

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
        if ($invoiceFee > 0) {
            $this->data['invoiceFee'] = $invoiceFee;            
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

    private function responseCodes($err) {
        $this->load->language('payment/svea_invoice');

        switch ($err) {

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
        }
    }

    public function confirm() {
        
        //Load models
        $this->load->model('checkout/order');
        $this->load->model('payment/svea_invoice');
        $this->load->model('checkout/coupon');
        $this->load->model('checkout/voucher');
        
        //Load SVEA includes
        include('svea/Includes.php');
        $svea = WebPay::createOrder();

        
        
        //Get order information
        $order = $this->model_checkout_order->getOrder($this->session->data['order_id']);
        $countryCode = $order['payment_iso_code_2'];

        //Check if company or private
        $company = ($_GET['company'] == 'true') ? true : false;
        
        
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
        
        
        //Invoice Fee
        if ($this->config->get('svea_fee_status') == 1) {
            
            $invoiceFee = $this->currency->format($this->config->get('svea_fee_fee'),'',false,false);
            $invoiceFeeTaxId = $this->config->get('svea_fee_tax_class_id');
            
            $invoiceTax = 0;
            
            if($invoiceFeeTaxId > 0)
                $invoiceTax = (floatval(VERSION) >= 1.5) ? $this->tax->getTax($invoiceFee, $invoiceFeeTaxId) : $this->tax->getRate($invoiceFeeTaxId);

            $invoiceFeeExVat  = $invoiceFee;
            $invoiceFeeIncVat = $invoiceFeeExVat + $invoiceTax;

                   
            $svea = $svea
                    ->addFee(
                        Item::invoiceFee()
                            ->setAmountExVat($invoiceFeeExVat)
                            ->setAmountIncVat($invoiceFeeIncVat)
                            ->setName($this->language->get('text_svea_fee'))
                            ->setUnit($this->language->get('pcs'))
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
                            ->setUnit($this->language->get('pcs'))
                        );

        }
        

        
        preg_match_all('!\d+!',$order['payment_address_1'],$houseNoArr);
        $houseNo = $houseNoArr[0][0];
        
        preg_match_all('!\w+!',$order['payment_address_1'],$streetArr);
        $street = $streetArr[0][0];
        

        

        //Set order detials if company or private
        if ($company == TRUE){
            
            //$ssn = (isset($_GET['ssn'])) ? $_GET['ssn'] : null;
            
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
            
            $item = $item->setInitials($_GET['initials'])
                         ->setBirthDate($_GET['birthYear'], $_GET['birthMonth'], $_GET['birthDay']);        
            
            }
            
            
            $svea = $svea->addCustomerDetails($item);

            }   
            
            
            //Testmode
            if($this->config->get('svea_invoice_testmode') == 1)
                $svea = $svea->setTestmode();
     
            $svea = $svea 
                      ->setCountryCode($countryCode)
                      ->setCurrency($this->session->data['currency'])
                      ->setClientOrderNumber($this->session->data['order_id'])
                      ->setOrderDate(date('c'))
                      ->useInvoicePayment()
                        ->setPasswordBasedAuthorization($this->config->get('svea_invoice_username_' . $countryCode),$this->config->get('svea_invoice_password_' . $countryCode),$this->config->get('svea_invoice_clientno_' . $countryCode))
                      ->doRequest();

            
            $response = array();

            //If response accepted redirect to thankyou page
            if ($svea->accepted == 1) {
                


                $this->model_checkout_order->confirm($this->session->data['order_id'], $this->config->get('svea_invoice_order_status_id'));
                
                $response = array("success" => true);
            } else {
                
                $response = array("error" => $svea->errormessage);
                //$this->responseCodes($response)
            }
            
            echo json_encode($response);

        }
            
            public function getAddress() {
                include('svea/Includes.php');
              
                $this->load->model('payment/svea_invoice');
                $this->load->model('checkout/order');
                
                $order = $this->model_checkout_order->getOrder($this->session->data['order_id']);
                $testMode = $this->config->get('svea_invoice_testmode'); //set false if not in testmode
                $countryCode = $order['payment_iso_code_2'];
                           
                
                $svea = WebPay::getAddresses()
                    ->setPasswordBasedAuthorization($this->config->get('svea_invoice_username_' . $countryCode), $this->config->get('svea_invoice_password_' . $countryCode), $this->config->get('svea_invoice_clientno_' . $countryCode)) 
                    ->setOrderTypeInvoice()                                              
                    ->setCountryCode($countryCode);

                //Testmode
                if($this->config->get('svea_invoice_testmode') == 1)
                    $svea = $svea->setTestmode();
                    
                //Testmode
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
                //print_r($svea);
                /*
                die();
                
                if (isset($response->GetAddressesResult->ErrorMessage)) {
                    echo '  $("#svea_invoice_div").hide();
                            $("#svea_invoice_err").show();
                            $("#svea_invoice_err") . append("' . $response->GetAddressesResult->ErrorMessage . '");
                            $("a#checkout") . hide();
                        ';
                } elseif (is_array($response->GetAddressesResult->Addresses->CustomerAddress)) {
                    foreach ($response->GetAddressesResult->Addresses->CustomerAddress as $key => $info) {

                        $addressline1 = (isset($info->AddressLine1)) ? $info->AddressLine1 : "";
                        $addressline2 = (isset($info->AddressLine2)) ? $info->AddressLine2 : "";
                        $address = ($addressline1 !== "" && $addressline2 !== "") ? $addressline1 . " - " . $addressline2 : $addressline1 . $addressline2;
                        
                        $legelName = (isset($info->LegalName)) ? $info->LegalName : "";
                        $postCode = (isset($info->Postcode)) ? $info->Postcode : "";
                        $city = (isset($info->Postarea)) ? $info->Postarea : "";
                        $addressSelector = (isset($info->AddressSelector)) ? $info->AddressSelector : "";

                        //Send back to user
                        echo '$("#svea_invoice_address").append(\'<option id="adress_' . $key . '" value="' . $addressSelector . '">' . $legelName . ', ' . $address . ', ' . $postCode . ' ' . $city . '</option>\');';
                    }

                    echo "$(\"#svea_invoice_err\").hide();";
                    echo "$(\"#svea_invoice_div\").show();";
                    echo "$(\"a#checkout\").show();";
                } else if (isset($response->GetAddressesResult->Addresses->CustomerAddress)) {
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
        		$("#svea_invoice_address").append(\'<option id="adress" value="' . $addressSelector . '">' . $legalName . ', ' . $address . ', ' . $postCode . ' ' . $city . '</option>\');
                        $("#svea_invoice_div").show();
                        $("#svea_invoice_err").hide();
                        $("a#checkout").show();
                ';
                } else {
                    echo '  $("#svea_invoice_div") . hide();
                            $("#svea_invoice_err").show();
                            $("#svea_invoice_err").append("No address was found.");
                            $("a#checkout").hide();
        		  ';
                }
                
                */
            }

        }
?>
