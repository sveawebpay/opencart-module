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
        /* WIP make dynamic and recalculate $total with right currency
          if ($total < 1000) {
          $this->data['delbet_fail'] = $this->language->get('text_delbet_fail');
          }
         * 
         */


        if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/svea_delbet.tpl')) {
            $this->template = $this->config->get('config_template') . '/template/payment/svea_delbet.tpl';
        } else {
            $this->template = 'default/template/payment/svea_delbet.tpl';
            $this->data['delbet_fail'] = $this->language->get('text_delbet_fail');
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
            }
        }

    public function confirm() {
        $this->load->model('checkout/order');
        $this->load->model('payment/svea_delbet');
        include('svea/svea_soap/SveaConfig.php');
        $this->load->model('checkout/coupon');
    // Get the products in the cart
        $products = $this->cart->getProducts();
        //Get order information
        $order = $this->model_checkout_order->getOrder($this->session->data['order_id']);
           //Get coupons
        if (isset($this->session->data['coupon'])){
            $coupon = $this->model_checkout_coupon->getCoupon($this->session->data['coupon']);
        }
        //flat tax for shop
        $flat_tax_class = $this->config->get('flat_tax_class_id');
        if (floatval(VERSION) >= 1.5) {
            $flatTax = ($this->tax->getTax(100, $flat_tax_class) / 100) * 100;
        } else {
            $flatTax = $this->tax->getRate($flat_tax_class);
        }
       
        //Settings and fees
        $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
        $countryCode = $order_info['payment_iso_code_2'];
        $username = "sverigetest";//$this->config->get('svea_fakt_username_' . $countryCode);
        $pass = "sverigetest";//$this->config->get('svea_fakt_password_' . $countryCode);
        $clientNo = 59999;//$this->config->get('svea_fakt_clientno_' . $countryCode);

        $shipping = $this->cart->hasShipping();
        //$invoiceFee = $this->config->get('svea_invoicefee');
        $testMode = $this->config->get('svea_fakt_testmode');
        //get svea_soap class library for WebserviceEu and set testmode
        $con = SveaConfig::getConfig();
        $con->setTestMode($testMode);
        //-----CreateOrderInformation-----
        $orderInformation = new SveaCreateOrderInformation($_GET['paySel']); //Send in campaigncodes to create instancevariables
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
            $orderRow->PricePerUnit = 5000; //$product['price'];!!!!testing
            $orderRow->NumberOfUnits = $product['quantity'];
            $orderRow->Unit = "";
            $orderRow->VatPercent = $tax;
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
                
                 $totalPrice = $total = $this->cart->getTotal();
           
            if ($coupon['type'] == 'F') {
                $discount = $coupon['discount'];
            } elseif ($coupon['type'] == 'P') {
                $discount = ($coupon['discount'] / 100) * $totalPrice;
            }
            $discountAmount = $discount / (($tax / 100) + 1);
           
            $orderRow = new SveaOrderRow();
            $orderRow->ArticleNumber = $coupon['code'];
            $orderRow->Description = $coupon['name'];
            $orderRow->PricePerUnit = -round($discountAmount, 2);
            $orderRow->NumberOfUnits = "1";
            $orderRow->Unit = "";
            $orderRow->VatPercent = $tax;
            $orderRow->DiscountPercent = 0;

            $orderInformation->addOrderRow($orderRow);
        }
        //-----Auth-----
        $auth = new SveaAuth();
        $auth->Username = $username;
        $auth->Password = $pass;
        $auth->ClientNumber = $clientNo;

        //-----Identity-----
        //make field 
        //Get initials !!//replace with $_get from get address form or something
        /** if ($_GET['sveaInitials'] != "") {
          $initials = $_GET['sveaInitials'];
          }
         *  else {
         */
        $name_array = explode(' ', $order['payment_firstname']);
        $letter = "";
        foreach ($name_array as $name) {
            $letter .= substr($name, 0, 1);
        }
        $initials = $letter;
        // }
        //true if company, false if individual
        $identity = new SveaIdentity();
        $identity->FirstName = $order['payment_firstname'];
        $identity->LastName = $order['payment_lastname'];
        $identity->Initials = $initials;
        $identity->BirthDate = $_GET['ssn'];

        $identityArr["IndividualIdentity"] = $identity;
        $customerIdentity = new SveaCustomerIdentity($identityArr);
        $customerIdentity->NationalIdNumber = $_GET['ssn'];
        $customerIdentity->Email = $order['email'];
        $customerIdentity->PhoneNumber = $order['telephone'];
        $customerIdentity->IpAddress = $order['ip'];
        $customerIdentity->FullName = $order['payment_firstname'] . ' ' . $order['payment_lastname'];
        $customerIdentity->Street = $order['payment_address_1'];
        $customerIdentity->CoAddress = $order['payment_address_2'];
        $customerIdentity->ZipCode = $order['payment_postcode'];
        $customerIdentity->HouseNumber = ""; //extract from $address['address_1']?
        $customerIdentity->Locality = $order['payment_city'];
        $customerIdentity->CountryCode = $order['payment_iso_code_2'];
        $customerIdentity->CustomerType = "Individual";

               //customize to country. If Nordic: unset 'identity', if Eu unset(NatianalIdnumber)
        if ($order['payment_iso_code_2'] == "SE" || $order['payment_iso_code_2'] == "NO" || $order['payment_iso_code_2'] == "FI" || $order['payment_iso_code_2'] == "DK") {
            unset($customerIdentity->IndividualIdentity);
        } elseif (($order['payment_iso_code_2'] == 'NL' || $order['payment_iso_code_2'] == 'DE') && $order['currency_code'] == 'EUR') {
            unset($customerIdentity->NationalIdNumber);
        }
        
        $orderInformation->ClientOrderNumber = $order['order_id'] . '-' . time(); //time added to not cause errors when interupting order and ordering again
        $orderInformation->CustomerIdentity = $customerIdentity;
        $orderInformation->OrderDate = date('c');
        $orderInformation->AddressSelector = ""; //only for company who can not order by paymentplan anyway
        $orderInformation->CustomerReference = "";
        $orderInformation->OrderType = "PaymentPlan";

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
            $this->model_checkout_order->confirm($this->session->data['order_id'], $this->config->get('svea_delbet_order_status_id'));
            echo 978;
        } else {

            echo "Error: " . $this->responseCodes($response);
        }
    }

    /** Only use in nordic countrys and for companys who cant shop here anyway
      public function getAddress() {

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
      if ($testMode == '1') {
      $svea_server = "https://webservices.sveaekonomi.se/webpay_test/SveaWebPay.asmx?WSDL";
      } else {
      $svea_server = "https://webservices.sveaekonomi.se/webpay/SveaWebPay.asmx?WSDL";
      }

      //Call Soap
      $client = new SoapClient($svea_server);

      //Make soap call to below method using above data
      $response = $client->GetAddresses($data);

      if (isset($response->GetAddressesResult->ErrorMessage)) {
      echo '  $("#svea_delbet_fakt").hide();
      $("#svea_delbet_err").show();
      $("#svea_delbet_err").append("' . $response->GetAddressesResult->ErrorMessage . '");
      $("a#checkout").hide();
      ';
      } else if (is_array($response->GetAddressesResult->Addresses->CustomerAddress)) {
      foreach ($response->GetAddressesResult->Addresses->CustomerAddress as $key => $info) {

      $addressline1 = (isset($info->AddressLine1)) ? $info->AddressLine1 : "";
      $addressline2 = (isset($info->AddressLine2)) ? $info->AddressLine2 : "";

      $address = ($addressline1 !== "" && $addressline2 !== "") ? $addressline1 . " - " . $addressline2 : $addressline1 . $addressline2;

      $legelName = (isset($info->LegalName)) ? $info->LegalName : "";
      $postCode = (isset($info->Postcode)) ? $info->Postcode : "";
      $city = (isset($info->Postarea)) ? $info->Postarea : "";
      $addressSelector = (isset($info->AddressSelector)) ? $info->AddressSelector : "";


      //Send back to user
      echo '$("#svea_delbet_address").append(\'<option id="adress_' . $key . '" value="' . $addressSelector . '">' . $legelName . ', ' . $address . ', ' . $postCode . ' ' . $city . '</option>\');';
      }
      echo "$(\"#svea_delbet_tr\").show();";
      echo "$(\"#svea_delbet_address\").show();";
      echo "$(\"#svea_delbet_err\").hide();";
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
      $("#svea_delbet_address").append(\'<option id="adress" value="' . $addressSelector . '">' . $legalName . ', ' . $address . ', ' . $postCode . ' ' . $city . '</option>\');
      $("#svea_delbet_address").show();
      $("#svea_delbet_tr").show();
      $("#svea_delbet_err").hide();
      $("a#checkout").show();
      ';
      } else {
      echo '  $("#svea_delbet_tr").hide();
      $("#svea_delbet_err").show();
      $("#svea_delbet_err").append("No address was found.");
      $("a#checkout").hide();
      ';
      }
      }
     * 
     */
    public function getPaymentOptions() {
        include('svea/svea_soap/SveaConfig.php');
        $this->load->model('checkout/order');
        $testMode = $this->config->get('svea_delbet_testmode');
        //get svea_soap class library for WebserviceEu and set testmode
        $con = SveaConfig::getConfig();
        $con->setTestMode($testMode);
        $order = $this->model_checkout_order->getOrder($this->session->data['order_id']);

        //-----Auth-----
        $auth = new SveaAuth();
        $auth->Username = $this->config->get('svea_username');
        $auth->Password = $this->config->get('svea_password');
        $auth->ClientNumber = $this->config->get('svea_delbet_clientno');

        //make request
        $object = new SveaRequest();
        $object->request = array("Auth" => $auth);
        $request = new SveaDoRequest();
        $svea_req = $request->GetPaymentPlanParamsEu($object);

        /*
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
          foreach ($products as $product) {

          if (floatval(VERSION) >= 1.5) {
          $tax = ($this->tax->getTax($product['price'], $product['tax_class_id']) / $product['price']) * 100;
          } else {
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

          if (isset($clientInvoiceRows)) {
          $clientInvoiceRows[$n] = $rows;
          } else {
          $clientInvoiceRows[] = $rows;
          }

          $n++;
          }

        $data['request'] = $request;
          //Shipping Fee
          if ($shipping == '1') {
                    $address = ($addressline1 !== "" && $addressline2 !== "") ? $addressline1 . " - " . $addressline2 : $addressline1 . $addressline2;
          $shipping_info = $this->session->data['shipping_method'];
          $shippingTax = 25;
                $address = ($addressline1 !== "" && $addressline2 !== "") ? $addressline1 . " - " . $addressline2 : $addressline1 . $addressline2;
          if ($shipping_info['cost'] > 0) {
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
		$this->load->model('payment/svea_delbet');
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
        $shipping = $this->cart->hasShipping();
          //Put all the data in request tag
          $data['request'] = $request;
        }
          //Check if testmode is enabled
          if ($testMode == '1') {
          $svea_server = "https://webservices.sveaekonomi.se/webpay_test/SveaWebPay.asmx?WSDL";
          } else {
          $svea_server = "https://webservices.sveaekonomi.se/webpay/SveaWebPay.asmx?WSDL";
          }
        );
          //Call Soap
          $client = new SoapClient($svea_server);
        $client = new SoapClient( $svea_server );
          //Make soap call to below method using above data
          $svea_req = $client->GetPaymentPlanOptions($data);
         */
        if (!isset($svea_req->GetPaymentPlanParamsEuResult)) {
            $this->ShowErrorMessage();
        } else if (!isset($svea_req->GetPaymentPlanParamsEuResult->CampaignCodes)) {
            $this->ShowErrorMessage($svea_req->GetPaymentPlanParamsEuResult->ResultCode);
        } else {
//WIP 
            $response = $svea_req->GetPaymentPlanParamsEuResult->CampaignCodes;
            if (is_array($response->CampaignCodeInfo)) {
                foreach ($response->CampaignCodeInfo as $key => $ss) {

//transfrom $order['total'] to right currency value
                    if ($order['total'] > $ss->FromAmount || $order['total'] < $ss->ToAmount) {
                        if ($ss->ContractLengthInMonths == 3 && $ss->MonthlyAnnuityFactor == 1) {
                            $description = 'Pay in 3 months'; //ERS?TT MED SP?RKFIL
                        } else {
                            $description = 'Pay in' . $ss->ContractLengthInMonths . 'monts' . //SPR?KFIL OCH VALUTAOMVANDLING
                                    ', (' . ($order['total'] * $ss->MonthlyAnnuityFactor) .
                                    ' ' . $order['currency_code'] . ' /' . 'm?nader' . ')'; //SPR?KFIL och VALUTAOMVANDLING
                        }
                    } /* else {
                      $description = 'Delbetala på ' . $ss->ContractLengthInMonths . ' månader, (' . $ss->MonthlyAnnuity . ' kr/mån)';
                      } */
                    echo '$("#svea_delbet_alt").append("<option id=\"paymentOption' . $key . '\" value=\"' . $ss->CampaignCode . '\">' . $description . '</option>");';
                }

                echo "$(\"#svea_delbet_alt\").show();",
                "$(\"a#checkout\").show();";
            } else {
                $this->ShowErrorMessage();
            }
        }
//WIP
    }
    
    private function ShowErrorMessage($response = null) {
        $message = ($response !== null && isset($response->ErrorMessage)) ? $response->ErrorMessage : "Could not get any partpayment alternatives.";
         echo '$("#svea_delbet_div").hide();
              $("#svea_delbet_alt").hide();
              $("#svea_delbet_err").show();
              $("#svea_delbet_err").append("' . $message . '");
              $("a#checkout").hide();';
    }
}
?>