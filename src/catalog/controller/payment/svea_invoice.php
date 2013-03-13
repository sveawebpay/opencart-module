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
        
        //Load SVEA includes
        include('svea/Includes.php');
        $svea = WebPay::createOrder();

        
        
        //Get order information
        $order = $this->model_checkout_order->getOrder($this->session->data['order_id']);
        $countryCode = $order['payment_iso_code_2'];
        
        //flat tax for shop
        $flat_tax_class = $this->config->get('flat_tax_class_id');
        if (floatval(VERSION) >= 1.5) {
            $flatTax = ($this->tax->getTax(100, $flat_tax_class) / 100) * 100;
        } else {
            $flatTax = $this->tax->getRate($flat_tax_class);
        }

        //Get coupons
        if (isset($this->session->data['coupon'])) {
            $coupon = $this->model_checkout_coupon->getCoupon($this->session->data['coupon']);
        }


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
        $invoiceFee = $this->config->get('svea_invoicefee_' . $countryCode);
        $invoiceFeeUseTax = $this->config->get('svea_invoicefee_usetax_' . $countryCode);
        
        
        if ($invoiceFee > 0) {
            
            
            $invoiceTax = 0;
            
            if($invoiceFeeUseTax == 1){
                $invoiceTax = (floatval(VERSION) >= 1.5) ? $this->tax->getTax($invoiceFee, $product['tax_class_id']) : $this->tax->getRate($product['tax_class_id']);
                 
                $invoiceFeeExVat  = $this->currency->format($invoiceTax,'',false,false);
                
            }

            
            $invoiceFeeIncVat = $invoiceFeeExVat + $invoiceTax;
            
            $invoiceFee_ex = $invoiceFee / (($flatTax / 100) + 1);
          
            //$orderRow->Description = "Svea Invoicefee"; //get language for customer
            //$orderRow->PricePerUnit = $invoiceFee_ex;
            //$orderRow->NumberOfUnits = $product['quantity'];
            //$orderRow->VatPercent = $flatTax;

        }
        
        $taxes = $this->cart->getTaxes();
       // print_r($invoiceFeeUseTax);
       // print_r($taxes);
        //die();
        
        
        //Shipping Fee
        $shipping = $this->cart->hasShipping();
        if ($shipping == '1') {
            $shipping_info = $this->session->data['shipping_method'];

            if (floatval(VERSION) >= 1.5) {
                $shipTax = ($this->tax->getTax($shipping_info["cost"], $shipping_info["tax_class_id"]) / $shipping_info["cost"]) * 100;
            } else {
                $shipTax = $this->tax->getRate($shipping_info["tax_class_id"]);
            }

            //$orderRow->Description = $shipping_info["title"] . ' ' . $shipping_info["text"]; //get language for customer
            //$orderRow->PricePerUnit = $shipping_info["cost"];
            //$orderRow->VatPercent = $shipTax;
        }
            
            //Add coupon
            if (isset($coupon)) {

            $totalPrice = $this->cart->getTotal();
            
                if ($coupon['type'] == 'F') {
                    $discount = $coupon['discount'];
                } elseif ($coupon['type'] == 'P') {
                    $discount = ($coupon['discount'] / 100) * $totalPrice;
                }
                    $discountAmount = $discount / (($flatTax / 100) + 1);

                   
                    //$orderRow->ArticleNumber = $coupon['code'];
                    //$orderRow->Description = $coupon['name'];
                    //$orderRow->PricePerUnit = -round($discountAmount, 2);
                    //$orderRow->VatPercent = $flatTax;
            }

                 
                $name_array = explode(' ', $order['payment_firstname']);
                $letter = "";
                foreach ($name_array as $name) {
                    $letter .= substr($name, 0, 1);
                }
                
                $initials = $letter;
                
                // }
                //true if company, false if individual
                $addresselector = "";
                if ($company) {
                    $identity->CompanyVatNumber = $_GET['company_id'];
                    if ($order["payment_iso_code_2"] == "SE" || $order["payment_iso_code_2"] == "NO" || $order["payment_iso_code_2"] == "DK") {
                        $addresselector = $_GET['addSel'];
                    }
                } else {
                    //$identity->FirstName = $order['payment_firstname'];
                    //$identity->LastName = $order['payment_lastname'];
                    //$identity->Initials = $initials;
                    //$identity->BirthDate = $_GET['ssn'];
                }
                 //$identity->BirthDate = $_GET['ssn'];
                //-----CustomerIdentity-----
                $type = ($company == TRUE) ? "CompanyIdentity" : "IndividualIdentity";
                //$identityArr[$type] = $identity;

                //$customerIdentity->NationalIdNumber = $_GET['ssn'];
                //$customerIdentity->Email = $order['email'];
                //$customerIdentity->PhoneNumber = $order['telephone'];
                //$customerIdentity->IpAddress = $order['ip'];
                //$customerIdentity->FullName = $order['payment_firstname'] . ' ' . $order['payment_lastname'];
                //$customerIdentity->Street = $order['payment_address_1'];
                //$customerIdentity->CoAddress = $order['payment_address_2'];
                //$customerIdentity->ZipCode = $order['payment_postcode'];
                //$customerIdentity->HouseNumber = ""; //extract from $order['address_1']?
                //$customerIdentity->Locality = $order['payment_city'];
                //$customerIdentity->CountryCode = $order['payment_iso_code_2'];
                //$customerIdentity->CustomerType = ($company == TRUE) ? "Company" : "Individual";

                
                $svea = $svea
                    ->addCustomerDetails(Item::individualCustomer()
                         ->setNationalIdNumber($_GET['ssn']) 
                         //->setName("Sneider","Boasman")
                         //->setInitials("SB")
                         //->setStreetAddress("Gate 42",23)
                         //->setZipCode("1102 HG")
                         //->setBirthDate(1955,03,07)
                         //->setLocality("Barendrecht")
                         );
                
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
                
               // print_r($svea->accepted); 
                //die();
                
                $response = array();

                //If response accepted redirect to thankyou page
                if ($svea->accepted == 1) {
                    
                    /*
                    if ($invoiceFee > 0) {


                        $order_id = $this->session->data['order_id'];
                        $invoiceTaxPrice = $invoiceFee - $invoiceFee_ex;

                        if (floatval(VERSION) >= 1.5) {

                            $this->db->query("INSERT INTO `" . DB_PREFIX . "order_product` (order_id,product_id,name,model,price,total,tax,quantity) 
                                VALUES ('" . $order_id . "','','Faktureringsavgift','','" . $invoiceFee_ex . "','" . $invoiceFee_ex . "','" . $invoiceTaxPrice . "','1')");
                            $this->db->query("UPDATE `" . DB_PREFIX . "order_total` SET value = value+" . $invoiceFee . ", text = CONCAT(FORMAT(value,0), 'kr')  
                                WHERE order_id = '" . $order_id . "' AND code = 'total'");
                            $this->db->query("UPDATE `" . DB_PREFIX . "order_total` SET value = value+" . $invoiceTaxPrice . ", text = CONCAT(FORMAT(value,0), 'kr')  
                                WHERE order_id = '" . $order_id . "' AND code = 'tax'");
                            $this->db->query("UPDATE `" . DB_PREFIX . "order_total` SET value = value+" . $invoiceFee_ex . ", text = CONCAT(FORMAT(value,0), 'kr')  
                                WHERE order_id = '" . $order_id . "' AND code = 'sub_total'");
                            $this->db->query("UPDATE `" . DB_PREFIX . "order` SET total = total+" . $invoiceFee . " 
                                WHERE order_id = '" . $order_id . "'");
                        } else {

                            $this->db->query("INSERT INTO `" . DB_PREFIX . "order_product` (order_id,product_id,name,model,price,total,tax,quantity) 
                                  VALUES ('" . $order_id . "','','Faktureringsavgift','','" . $invoiceFee_ex . "','" . $invoiceFee_ex . "','" . $invoiceTaxPrice . "','1')");
                            $this->db->query("UPDATE `" . DB_PREFIX . "order_total` SET value = value+" . $invoiceFee . ", text = CONCAT(FORMAT(value,0), 'kr')  
                                WHERE order_id = '" . $order_id . "' AND sort_order = '99'");
                            $this->db->query("UPDATE `" . DB_PREFIX . "order_total` SET value = value+" . $invoiceTaxPrice . ", text = CONCAT(FORMAT(value,0), 'kr')  
                                  WHERE order_id = '" . $order_id . "' AND sort_order = '5'");
                            $this->db->query("UPDATE `" . DB_PREFIX . "order_total` SET value = value+" . $invoiceFee_ex . ", text = CONCAT(FORMAT(value,0), 'kr')  
                                  WHERE order_id = '" . $order_id . "' AND sort_order = '0'");
                            $this->db->query("UPDATE `" . DB_PREFIX . "order` SET total = total+" . $invoiceFee . " 
                                WHERE order_id = '" . $order_id . "'");
                        }
                    } */


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
                        $result[] = array("fullName" => $ci->fullName,
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
