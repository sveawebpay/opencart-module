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
        //Invoice Fee
        $invoiceFee = $this->config->get('svea_invoicefee');
        if ($invoiceFee > 0) {
            $this->data['invoiceFee'] = $invoiceFee;
        }

        //Get the country
        $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
        $this->data['countryCode'] = $order_info['payment_iso_code_2'];



        $this->id = 'payment';

        if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/svea_fakt.tpl')) {
            $this->template = $this->config->get('config_template') . '/template/payment/svea_fakt.tpl';
        } else {
            $this->template = 'default/template/payment/svea_fakt.tpl';
        }

        $this->render();
    }

    private function responseCodes($err) {
        $this->load->language('payment/svea_fakt');

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
        $this->load->model('checkout/order');
        $this->load->model('payment/svea_fakt');
        $this->load->model('checkout/coupon');
        include('svea/svea_soap/SveaConfig.php');


        // Get the products in the cart
        $products = $this->cart->getProducts();
        //Get order information
        $order = $this->model_checkout_order->getOrder($this->session->data['order_id']);

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

        //Settings and fees
        $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
        $countryCode = $order_info['payment_iso_code_2'];
        $username = $this->config->get('svea_fakt_username_' . $countryCode);
        $pass = $this->config->get('svea_fakt_password_' . $countryCode);
        $clientNo = $this->config->get('svea_fakt_clientno_' . $countryCode);

        $invoiceFee = $this->config->get('svea_invoicefee_' . $countryCode);
        $shipping = $this->cart->hasShipping();
        //$invoiceFee = $this->config->get('svea_invoicefee');
        $testMode = $this->config->get('svea_fakt_testmode');
        //get svea_soap class library for WebserviceEu and set testmode
        $con = SveaConfig::getConfig();
        $con->setTestMode($testMode);


        //Check if company or private
        $company = ($_GET['company'] == 'true') ? true : false;
        //-----CreateOrderInformation-----
        $orderInformation = new SveaCreateOrderInformation();
        //Product rows     
        foreach ($products as $product) {
            if (floatval(VERSION) >= 1.5) {
                $tax = ($this->tax->getTax($product['price'], $product['tax_class_id']) / $product['price']) * 100;
            } else {
                $tax = $this->tax->getRate($product['tax_class_id']);
            }

            //-----OrderRow-----
            $orderRow = new SveaOrderRow();
            $orderRow->ArticleNumber = $product['product_id'];
            $orderRow->Description = $product['name'];
            $orderRow->PricePerUnit = $product['price'];
            $orderRow->NumberOfUnits = $product['quantity'];
            $orderRow->Unit = "";
            $orderRow->VatPercent = $tax;
            $orderRow->DiscountPercent = 0;
            $orderInformation->addOrderRow($orderRow);

        }
            //Invoice Fee
            if ($invoiceFee > 0) {
                $invoiceFee_ex = $invoiceFee / (($flatTax / 100) + 1);
                $orderRow = new SveaOrderRow();
                $orderRow->ArticleNumber = "";
                $orderRow->Description = "Svea Invoicefee"; //get language for customer
                $orderRow->PricePerUnit = $invoiceFee_ex;
                $orderRow->NumberOfUnits = $product['quantity'];
                $orderRow->Unit = "";
                $orderRow->VatPercent = $flatTax;
                $orderRow->DiscountPercent = 0;
                $orderInformation->addOrderRow($orderRow);
            }

            //Shipping Fee
            if ($shipping == '1') {
                $shipping_info = $this->session->data['shipping_method'];

                if (floatval(VERSION) >= 1.5) {
                    $shipTax = ($this->tax->getTax($shipping_info["cost"], $shipping_info["tax_class_id"]) / $shipping_info["cost"]) * 100;
                } else {
                    $shipTax = $this->tax->getRate($shipping_info["[tax_class_id"]);
                }
                //-----OrderRow-----
                $orderRow = new SveaOrderRow();
                $orderRow->ArticleNumber = "";
                $orderRow->Description = $shipping_info["title"] . ' ' . $shipping_info["text"]; //get language for customer
                $orderRow->PricePerUnit = $shipping_info["cost"];
                $orderRow->NumberOfUnits = "1";
                $orderRow->Unit = "";
                $orderRow->VatPercent = $shipTax;
                $orderRow->DiscountPercent = 0;
                $orderInformation->addOrderRow($orderRow);
            }
            //Add coupon
            if (isset($coupon)) {

            $totalPrice = $this->cart->getTotal();
//check if right!!
                if ($coupon['type'] == 'F') {
                    $discount = $coupon['discount'];
                } elseif ($coupon['type'] == 'P') {
                    $discount = ($coupon['discount'] / 100) * $totalPrice;
                }
                    $discountAmount = $discount / (($flatTax / 100) + 1);

                    $orderRow = new SveaOrderRow();
                    $orderRow->ArticleNumber = $coupon['code'];
                    $orderRow->Description = $coupon['name'];
                    $orderRow->PricePerUnit = -round($discountAmount, 2);
                    $orderRow->NumberOfUnits = "1";
                    $orderRow->Unit = "";
                    $orderRow->VatPercent = $flatTax;
                    $orderRow->DiscountPercent = 0;
                    $orderInformation->addOrderRow($orderRow);
            }


                //-----Auth-----
            
                $auth = new SveaAuth();
                $auth->Username = $username;
                $auth->Password = $pass;
                $auth->ClientNumber = $clientNo;

                //-----Identity-----
                //Get initials !!//replace with $_get from get address form or something
                /* if ($_GET['sveaInitials'] != "") {
                  $initials = $_GET['sveaInitials'];
                  } else {
                 * 
                 */
                $name_array = explode(' ', $order['payment_firstname']);
                $letter = "";
                foreach ($name_array as $name) {
                    $letter .= substr($name, 0, 1);
                }
                $initials = $letter;
                // }
                //true if company, false if individual
                $identity = new SveaIdentity($company);
                $addresselector = "";
                if ($company) {
                    $identity->CompanyVatNumber = $_GET['company_id'];
                    if ($order["payment_iso_code_2"] == "SE" || $order["payment_iso_code_2"] == "NO" || $order["payment_iso_code_2"] == "DK") {
                        $addresselector = $_GET['addSel'];
                    }
                } else {
                    $identity->FirstName = $order['payment_firstname'];
                    $identity->LastName = $order['payment_lastname'];
                    $identity->Initials = $initials;
                    $identity->BirthDate = $_GET['ssn'];
                }
                 //$identity->BirthDate = $_GET['ssn'];
                //-----CustomerIdentity-----
                $type = ($company == TRUE) ? "CompanyIdentity" : "IndividualIdentity";
                $identityArr[$type] = $identity;

                $customerIdentity = new SveaCustomerIdentity($identityArr);
                $customerIdentity->NationalIdNumber = $_GET['ssn'];
                $customerIdentity->Email = $order['email'];
                $customerIdentity->PhoneNumber = $order['telephone'];
                $customerIdentity->IpAddress = $order['ip'];
                $customerIdentity->FullName = $order['payment_firstname'] . ' ' . $order['payment_lastname'];
                $customerIdentity->Street = $order['payment_address_1'];
                $customerIdentity->CoAddress = $order['payment_address_2'];
                $customerIdentity->ZipCode = $order['payment_postcode'];
                $customerIdentity->HouseNumber = ""; //extract from $order['address_1']?
                $customerIdentity->Locality = $order['payment_city'];
                $customerIdentity->CountryCode = $order['payment_iso_code_2'];
                $customerIdentity->CustomerType = ($company == TRUE) ? "Company" : "Individual";

                //customize to country. If Nordic: unset 'identity', if Eu unset(NatianalIdnumber)
                if ($order['payment_iso_code_2'] == "SE" || $order['payment_iso_code_2'] == "NO" || $order['payment_iso_code_2'] == "FI" || $order['payment_iso_code_2'] == "DK") {
                    unset($customerIdentity->$type);
                } elseif (($order['payment_iso_code_2'] == 'NL' || $order['payment_iso_code_2'] == 'DE') && $order['currency_code'] == 'EUR') {
                    unset($customerIdentity->NationalIdNumber);
                }

                
                $orderInformation->ClientOrderNumber = $order['order_id'] . '-' . time(); //time added to not cause errors when interupting order and ordering again
                $orderInformation->CustomerIdentity = $customerIdentity;
                $orderInformation->AddressSelector = $addresselector;
                $orderInformation->OrderDate = date('c');
                $orderInformation->CustomerReference = "";
                $orderInformation->OrderType = "Invoice";

                //-----Order-----
                $sveaOrder = new SveaOrder();
                $sveaOrder->Auth = $auth;
                $sveaOrder->CreateOrderInformation = $orderInformation;
             
                //make request
                $object = new SveaRequest();
                $object->request = $sveaOrder;
                $request = new SveaDoRequest();
                $svea_req = $request->CreateOrderEu($object);
                $response = $svea_req->CreateOrderEuResult->Accepted;

                //If response accepted redirect to thankyou page
                if ($response == 1) {

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
                    }


                    $this->model_checkout_order->confirm($this->session->data['order_id'], $this->config->get('svea_fakt_order_status_id'));
                    echo 978;
                } else {

                    echo "Error: " . $this->responseCodes($response);
                }
            }

            public function getAddress() {
                include('svea/svea_soap/SveaConfig.php');
                include ('svea/phpintegration/src/Includes.php');
              
                $con = SveaConfig::getConfig();
                $con->setTestMode($this->config->get('svea_fakt_testmode')); //set false if not in testmode

                $this->load->model('payment/svea_fakt');
                $this->load->model('checkout/order');
                $company = ($_GET['company'] == 'true') ? true : false;
                //Get order information
                $order = $this->model_checkout_order->getOrder($this->session->data['order_id']);

                $countryCode = $order['payment_iso_code_2'];
                $username = $this->config->get('svea_fakt_username_' . $countryCode);
                $pass = $this->config->get('svea_fakt_password_' . $countryCode);
                $clientNo = $this->config->get('svea_fakt_clientno_' . $countryCode);
                
                $auth = new SveaAuth();
                $auth->Username = $username;
                $auth->Password = $pass;
                $auth->ClientNumber = $clientNo;

                $address = new SveaAddress();
                $address->Auth = $auth;
                $address->IsCompany = $company;
                $address->CountryCode = $order['payment_iso_code_2'];
                $address->SecurityNumber = $_GET['ssn'];
                
                $object = new SveaRequest();
                $object->request = $address;
                $request = new SveaDoRequest();
                $response = $request->GetAddresses($object);

                if (isset($response->GetAddressesResult->ErrorMessage)) {
                    echo '  $("#svea_fakt_div").hide();
                            $("#svea_fakt_err").show();
                            $("#svea_fakt_err") . append("' . $response->GetAddressesResult->ErrorMessage . '");
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
                        echo '$("#svea_fakt_address").append(\'<option id="adress_' . $key . '" value="' . $addressSelector . '">' . $legelName . ', ' . $address . ', ' . $postCode . ' ' . $city . '</option>\');';
                    }

                    echo "$(\"#svea_fakt_err\").hide();";
                    echo "$(\"#svea_fakt_div\").show();";
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
        		$("#svea_fakt_address").append(\'<option id="adress" value="' . $addressSelector . '">' . $legalName . ', ' . $address . ', ' . $postCode . ' ' . $city . '</option>\');
                        $("#svea_fakt_div").show();
                        $("#svea_fakt_err").hide();
                        $("a#checkout").show();
                ';
                } else {
                    echo '  $("#svea_fakt_div") . hide();
                            $("#svea_fakt_err").show();
                            $("#svea_fakt_err").append("No address was found.");
                            $("a#checkout").hide();
        		  ';
                }
            }

        }
?>
