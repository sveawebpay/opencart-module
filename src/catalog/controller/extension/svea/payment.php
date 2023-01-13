<?php

require_once(DIR_APPLICATION . 'controller/extension/payment/svea_common.php');
require_once(DIR_APPLICATION . '../svea/config/configInclude.php');

class ControllerExtensionSveaPayment extends SveaCommon
{
    private $paymentString = "payment_";
    private $moduleString = "module_";
    private $extensionString = "setting/extension";
    private $totalString = "total_";

    public function setVersionStrings()
    {
        if (VERSION < 3.0) {
            $this->moduleString = "";
            $this->paymentString = "";
            $this->extensionString = "extension/extension";
            $this->totalString = "";
        }
    }

    public function index()
    {
        $this->setVersionStrings();
        unset($this->session->data[$this->paymentString . 'svea_last_page']);
        unset($this->session->data[$this->moduleString . 'sco_success_order_id']);

        $module_sco_order_id = isset($this->session->data[$this->moduleString . 'sco_order_id'])
            ? $this->session->data[$this->moduleString . 'sco_order_id']
            : null;

        $config = new OpencartSveaCheckoutConfig($this, 'checkout');

        if ($this->config->get($this->moduleString . 'sco_test_mode')) {
            $config = new OpencartSveaCheckoutConfigTest($this, 'checkout');
        }

        if (isset($this->session->data[$this->moduleString . 'sco_order_id'])) {
            $status_check = \Svea\WebPay\WebPay::checkout($config);
            $status_check->setCheckoutOrderId($module_sco_order_id);

            try {
                $status_check_response = $status_check->getOrder();
                if ($status_check_response['Status'] != 'Created') {
                    unset($this->session->data['order_id']);
                    unset($this->session->data[$this->moduleString . 'sco_order_id']);
                    unset($this->session->data[$this->moduleString . 'sco_cart_hash']);
                    unset($module_sco_order_id);
                }
            } catch (\Exception $e) {
                unset($this->session->data['order_id']);
                unset($this->session->data[$this->moduleString . 'sco_order_id']);
                unset($this->session->data[$this->moduleString . 'sco_cart_hash']);
                unset($module_sco_order_id);
            }
        }

        $forceCreate = (isset($this->request->get['create']) && $this->request->get['create'] === 'true') ? true : false;

        if ($forceCreate === true) {
            unset($this->session->data['order_id']);
            unset($this->session->data[$this->moduleString . 'sco_order_id']);
            unset($this->session->data[$this->moduleString . 'sco_cart_hash']);
            unset($module_sco_order_id);
        }

        $email = isset($this->request->post['email'])
            ? $this->request->post['email']
            : null;

        if (isset($this->session->data['order_id'])) {
            $query_response = $this->db->query("SELECT order_status_id FROM `" . DB_PREFIX . "order` WHERE order_id='" . $this->db->escape($this->session->data['order_id']) . "';")->row;
            if ($query_response['order_status_id'] != 0) {
                unset($this->session->data['order_id']);
                unset($this->session->data[$this->moduleString . 'sco_order_id']);
                unset($this->session->data[$this->moduleString . 'sco_cart_hash']);
            }
        }

        $order_id = (isset($this->session->data['order_id']))
            ? (int)$this->session->data['order_id']
            : null;

        $this->load->language('extension/svea/checkout');

        $data['heading_error'] = $this->language->get('heading_error');
        $data['error_unknown'] = $this->language->get('error_unknown');

        $order_id = $this->addOrder($order_id, $email);

        if ($this->config->get($this->moduleString . 'sco_test_mode')) {
            $order_id = hash('crc32', HTTPS_SERVER) . $order_id;
        }

        if ($this->config->get($this->moduleString . 'sco_currency')) {
            $currency = strtoupper($this->session->data[$this->moduleString . 'sco_currency']);
        } else {
            $currency = strtoupper($this->session->data['currency']);
        }

        $this->load->model('localisation/currency');

        $currency_info = $this->model_localisation_currency->getCurrencyByCode($currency);
        $currency_value = $currency_info['value'];

        $products = $this->cart->getProducts();

        if (empty($products) && empty($this->session->data['vouchers'])) {
            $this->load->language('common/cart');
            header('HTTP/1.1 500 PHP Library Error');
            header('Content-Type: application/json; charset=UTF-8');
            $er = array();
            $er['message'] = $this->language->get('text_empty');

            die(json_encode($er));
        }

        $checkout_order_entry = \Svea\WebPay\WebPay::checkout($config);

        $order_builder = $checkout_order_entry->getCheckoutOrderBuilder()->setPartnerKey('3D5E28FB-EA86-41FE-98CB-60CCAE8E6075');

        $this->setOrderGeneralData($checkout_order_entry, $order_id);

        $order_builder = $this->addOrderRowsToWebServiceOrder($order_builder, $products, $currency_value);

        $this->addPresetValues($order_builder);
        $this->addIdentityFlags($order_builder);
        $this->setMerchantData($order_builder);

        if ($this->config->get($this->moduleString . 'sco_enable_electronic_id_authentication')) {
            $order_builder->setRequireElectronicIdAuthentication(true);
        }

        $add_ons = $this->addTaxRateToAddons();

        $this->addAddonRowsToSveaOrder($order_builder, $add_ons, $currency_value);
        $this->addRoundingRowIfApplicable($order_builder, $this->cart->getTotal(), $add_ons, $currency_value);

        $isScoUpdate = false;
        $isChangedState = $this->isChangedState();

        try {
            if (isset($module_sco_order_id)) {
                $isScoUpdate = true;
                $checkout_order_entry->setCheckoutOrderId($module_sco_order_id);

                if ($isChangedState === true) {
                    $response = $checkout_order_entry->updateOrder();
                } else {
                    $response = $checkout_order_entry->getOrder();
                }
            } else {
                $checkout_order_entry->setClientOrderNumber($order_id);
                $response = $checkout_order_entry->createOrder();
            }

            $this->saveHashOfCurrentState();

            if (is_array($response) === true) {
                $response = lowerArrayKeys($response);
                $this->session->data[$this->moduleString . 'sco_order_id'] = $response['orderid'];

                // Update oc_order_sco.checkout_id
                $this->updateCheckoutRow($order_id, $response);

                // This will used for success page, and for optional second call
                $this->session->data[$this->paymentString . 'svea_last_page'] = 'extension/svea/payment';
                $this->session->data[$this->moduleString . 'sco_success_order_id'] = $response['orderid'];

                // Show Svea checkout snippet
                $data['snippet'] = $response['gui']['snippet'];
            }

            $this->response->setOutput($this->load->view('extension/svea/payment', $data));
        } catch (\Exception $e) {
            header('HTTP/1.1 500 PHP Library Error');
            header('Content-Type: application/json; charset=UTF-8');
            $er = array();
            $er['message'] = $e->getMessage();
            $er['isScoUpdate'] = $isScoUpdate;

            die(json_encode($er));
        }
    }

    private function getHashOfCurrentState()
    {
        $this->setVersionStrings();
        $products = $this->cart->getProducts();
        $session_copy = $this->session->data;
        unset($session_copy[$this->moduleString . 'sco_cart_hash']);
        unset($session_copy[$this->moduleString . 'sco_order_id']);
        unset($session_copy[$this->paymentString . 'svea_last_page']);
        unset($session_copy[$this->moduleString . 'sco_success_order_id']);

        if (isset($this->request->post['sco_newsletter'])) {
            return md5(serialize($session_copy) . serialize($products) . serialize($this->request->post['sco_newsletter']));
        }

        return md5(serialize($session_copy) . serialize($products));
    }

    private function getHashOfOldState()
    {
        $this->setVersionStrings();

        return isset($this->session->data[$this->moduleString . 'sco_cart_hash']) ? $this->session->data[$this->moduleString . 'sco_cart_hash'] : null;
    }

    private function saveHashOfCurrentState()
    {
        $this->setVersionStrings();

        $this->session->data[$this->moduleString . 'sco_cart_hash'] = $this->getHashOfCurrentState();
    }

    private function isChangedState()
    {
        return $this->getHashOfOldState() !== $this->getHashOfCurrentState();
    }

    private function setOrderGeneralData($checkout_order_entry, $order_id)
    {
        $this->setVersionStrings();

        $terms_uri =  $this->url->link('information/information', array('information_id' => $this->config->get('config_checkout_id')));
        $config_terms_uri_secured = $this->config->get($this->moduleString . 'sco_checkout_terms_uri_secured');
        $config_terms_uri = $this->config->get($this->moduleString . 'sco_checkout_terms_uri');

        if (!empty($config_terms_uri)) {
            $terms_uri =  $this->createUrl($config_terms_uri, $config_terms_uri_secured);
        }

        if (!empty($this->session->data['svea_checkout']['currency'])) {
            $currency = strtoupper($this->session->data['svea_checkout']['currency']);
        } else {
            $currency = strtoupper($this->session->data['currency']);
        }

        if (!empty($this->session->data['svea_checkout']['locale'])) {
            $locale = strtolower($this->session->data['svea_checkout']['locale']);
        } else {
            $locale = strtolower($this->session->data['language']);
        }

        if (!empty($this->session->data['svea_checkout']['country_code'])) {
            $country_code = strtoupper($this->session->data['svea_checkout']['country_code']);
        } else {
            $country_code = 'SE';
        }

        $cookie_name = (session_name()) ? session_name() : 'OCSESSID';
        $cookie = isset($_COOKIE[$cookie_name]) ? $_COOKIE[$cookie_name] : null;

        $checkout_order_entry
            ->setCountryCode($country_code)
            ->setCurrency($currency)
            ->setCheckoutUri($this->url->link('extension/svea/checkout'))
            ->setConfirmationUri($this->url->link('extension/svea/success' . '&order_id=' . $order_id . '&hash=' . hash('sha512', $order_id . $cookie)))
            ->setPushUri(str_replace('&amp;', '&', urldecode($this->url->link('extension/svea/push', array($this->paymentString . 'svea_order' => '{checkout.order.uri}')))))
            ->setTermsUri(str_replace('&amp;', '&', (urldecode($terms_uri))))
            ->setLocale($locale);
    }

    private function addOrder($order_id, $email)
    {
        $this->setVersionStrings();
        $this->load->language('extension/svea/checkout');

        // SET VALUES
        $customer_id 		= 0;
        $customer_group_id 	= $this->config->get('config_customer_group_id');
        $firstname 			= null;
        $lastname 			= null;
        $telephone 			= null;
        $fax 				= null;
        $custom_field 		= null;

        // CHECK IF USER IS LOGGED
        if ($this->customer->isLogged()) {

            // LOAD MODEL
            $this->load->model('account/customer');

            // GET CUSTOMER DATA
            $customer_info = $this->model_account_customer->getCustomer($this->customer->getId());

            // SET VALUES
            $customer_id 		= $this->customer->getId();
            $customer_group_id 	= $customer_info['customer_group_id'];
            $firstname 			= $customer_info['firstname'];
            $lastname 			= $customer_info['lastname'];
            $email 				= $customer_info['email'];
            $telephone 			= $customer_info['telephone'];
            $fax 				= $customer_info['fax'];
            $custom_field 		= !empty($customer_info['custom_field']) ? json_decode($customer_info['custom_field'], true) : null;
        }

        $sort_order	= array();
        $totals		= array();
        $taxes		= $this->cart->getTaxes();
        $total		= 0;

        $total_data = array(
            'totals' => &$totals,
            'taxes'  => &$taxes,
            'total'  => &$total
        );

        $this->load->model($this->extensionString);

        if (VERSION < 3.0) {
            $results = $this->model_extension_extension->getExtensions('total');
        } else {
            $results = $this->model_setting_extension->getExtensions('total');
        }

        foreach ($results as $key => $value) {
            $sort_order[$key] = $this->config->get($this->totalString . '' . $value['code'] . '_sort_order');
        }

        array_multisort($sort_order, SORT_ASC, $results);

        foreach ($results as $result) {
            if ($this->config->get($this->totalString . '' . $result['code'] . '_status')) {
                $this->load->model('extension/total/' . $result['code']);

                $this->{'model_extension_total_' . $result['code']}->getTotal($total_data);
            }
        }

        $sort_order = array();

        foreach ($totals as $key => $value) {
            $sort_order[$key] = $value['sort_order'];
        }

        array_multisort($sort_order, SORT_ASC, $totals);

        // LOOP CART
        foreach ($this->cart->getProducts() as $product) {

            // MAKE EMPTY ARRAY
            $option_data = array();

            // LOOP OPTIONS
            foreach ($product['option'] as $option) {

                // ADD OPTION TO ARRAY
                $option_data[] = array(
                    'product_option_id'       => $option['product_option_id'],
                    'product_option_value_id' => $option['product_option_value_id'],
                    'option_id'               => $option['option_id'],
                    'option_value_id'         => $option['option_value_id'],
                    'name'                    => $option['name'],
                    'value'                   => $option['value'],
                    'type'                    => $option['type']
                );
            }

            // ADD PRODUCT TO ARRAY
            $products[] = array(
                'product_id' => $product['product_id'],
                'name'       => $product['name'],
                'model'      => $product['model'],
                'option'     => $option_data,
                'download'   => $product['download'],
                'quantity'   => $product['quantity'],
                'subtract'   => $product['subtract'],
                'price'      => $product['price'],
                'total'      => $product['total'],
                'tax'        => $this->tax->getTax($product['price'], $product['tax_class_id']),
                'reward'     => $product['reward']
            );
        }

        // MAKE EMPTY ARRAY
        $vouchers = array();

        // CHECK IF ANY VOUCHERS IS SET
        if (!empty($this->session->data['vouchers'])) {

            // LOOP VOUCHERS FROM SESSION
            foreach ($this->session->data['vouchers'] as $voucher) {

                // ADD VOUCHER TO ARRAY
                $vouchers[] = array(
                    'description'      => $voucher['description'],
                    'code'             => substr(md5(mt_rand()), 0, 10),
                    'to_name'          => $voucher['to_name'],
                    'to_email'         => $voucher['to_email'],
                    'from_name'        => $voucher['from_name'],
                    'from_email'       => $voucher['from_email'],
                    'voucher_theme_id' => $voucher['voucher_theme_id'],
                    'message'          => $voucher['message'],
                    'amount'           => $voucher['amount']
                );
            }
        }

        $order = array(
            'order_id'					=> $order_id,
            'invoice_prefix' 			=> $this->config->get('config_invoice_prefix'),
            'store_id' 					=> $this->config->get('config_store_id'),
            'store_name' 				=> $this->config->get('config_name'),
            'store_url' 				=> isset($order_data['store_id']) ? $this->config->get('config_url') : HTTP_SERVER,
            'customer_id' 				=> $customer_id,
            'customer_group_id'			=> $customer_group_id,
            'firstname' 				=> $firstname,
            'lastname' 					=> $lastname,
            'email'						=> $email,
            'telephone' 				=> $telephone,
            'fax' 						=> $fax,
            'custom_field'				=> null,
            'payment_firstname' 		=> null,
            'payment_lastname' 			=> null,
            'payment_company' 			=> null,
            'payment_address_1' 		=> null,
            'payment_address_2' 		=> null,
            'payment_city' 				=> null,
            'payment_postcode' 			=> null,
            'payment_country' 			=> null,
            'payment_country_id' 		=> null,
            'payment_zone' 				=> null,
            'payment_zone_id' 			=> null,
            'payment_address_format' 	=> null,
            'payment_custom_field' 		=> null,
            'payment_method' 			=> $this->language->get('text_payment_method'),
            'payment_code'				=> 'sco',
            'shipping_firstname' 		=> null,
            'shipping_lastname' 		=> null,
            'shipping_company' 			=> null,
            'shipping_address_1' 		=> null,
            'shipping_address_2' 		=> null,
            'shipping_city' 			=> null,
            'shipping_postcode' 		=> null,
            'shipping_country' 			=> null,
            'shipping_country_id' 		=> null,
            'shipping_zone' 			=> null,
            'shipping_zone_id' 			=> null,
            'shipping_address_format' 	=> null,
            'shipping_custom_field' 	=> null,
            'shipping_method' 			=> isset($this->session->data['shipping_method']['title']) 	? $this->session->data['shipping_method']['title'] 					: null,
            'shipping_code' 			=> isset($this->session->data['shipping_method']['code'])  	? $this->session->data['shipping_method']['code']  					: null,
            'comment' 					=> isset($this->session->data['comment']) 					? $this->session->data['comment'] 									: null,
            'affiliate_id' 				=> isset($affiliate_id) 									? $affiliate_id 													: null,
            'commission' 				=> isset($commission) 										? $commission 														: null,
            'marketing_id' 				=> isset($marketing_id) 									? $marketing_id 													: null,
            'tracking' 					=> isset($tracking) 										? $tracking 														: null,
            'currency_id' 				=> isset($this->session->data['svea_checkout']['currency']) ? $this->currency->getId($this->session->data['svea_checkout']['currency']) : '0',
            'currency_code' 			=> isset($this->session->data['svea_checkout']['currency']) ? $this->session->data['svea_checkout']['currency'] : 'SEK',
            'currency_value' 			=> isset($this->session->data['svea_checkout']['currency']) ? $this->currency->getValue($this->session->data['svea_checkout']['currency']) : '0',
            'ip' 						=> isset($this->request->server['REMOTE_ADDR']) 			? $this->request->server['REMOTE_ADDR'] 							: null,
            'forwarded_ip' 				=> isset($this->request->server['HTTP_X_FORWARDED_FOR']) 	? $this->request->server['HTTP_X_FORWARDED_FOR'] 					: null,
            'user_agent' 				=> isset($this->request->server['HTTP_USER_AGENT']) 		? $this->request->server['HTTP_USER_AGENT'] 						: null,
            'accept_language' 			=> isset($this->request->server['HTTP_ACCEPT_LANGUAGE']) 	? $this->request->server['HTTP_ACCEPT_LANGUAGE'] 					: null,
            'language_id' 				=> $this->config->get('config_language_id'),
            'vouchers'					=> $vouchers,
            'products'					=> isset($products) ? $products : null,
            'totals'					=> $totals,
            'total' 					=> $total,
        );

        $this->load->model('extension/svea/checkout');

        $order_id = $this->model_extension_svea_checkout->addOrder($order);

        $locale = isset($this->session->data['svea_checkout']['locale']) ? strtolower($this->session->data['svea_checkout']['locale']) : 'sv-se';

        $this->model_extension_svea_checkout->addCheckoutOrder($order_id, $locale);

        $this->session->data['order_id'] = (int)$order_id;

        return (int)$order_id;
    }

    private function updateCheckoutRow($order_id, $response)
    {
        if (!is_array($response)) {
            return;
        }

        $this->load->model('extension/svea/checkout');

        $country = $this->model_extension_svea_checkout->getCountry($response['countrycode']);

        $data = array(
            'order_id'    => $response['clientordernumber'],
            'checkout_id' => $response['orderid'],
            'status'      => isset($response['status']) ? $response['status'] : null,
            'type'        => isset($response['customer']['iscompany']) ? ($response['customer']['iscompany'] ? 'company' : 'person') : 'person',
            'locale'      => isset($response['locale']) ? $response['locale'] : null,
            'currency'    => isset($response['currency']) ? $response['currency'] : null,
            'country'     =>   $country['name'],
        );

        $this->model_extension_svea_checkout->updateCheckoutOrder($data);
    }

    private function addPresetValues($order_builder)
    {
        $email = $this->customer->isLogged() ? $this->customer->getEmail() : null;
        $postcode = isset($this->request->post['postcode']) ? $this->request->post['postcode'] : null;
        $phoneNumber = $this->customer->isLogged() ? $this->customer->getTelephone() : null;
        $isCompany = $this->config->get($this->moduleString . 'sco_force_flow') ? $this->config->get($this->moduleString . 'sco_force_b2b') : null;

        if (!is_null($email)) {
            $presetEmail = \Svea\WebPay\WebPayItem::presetValue()
                ->setTypeName(\Svea\WebPay\Checkout\Model\PresetValue::EMAIL_ADDRESS)
                ->setValue($email)
                ->setIsReadonly(false);

            $order_builder->addPresetValue($presetEmail);
        }

        if (!is_null($postcode)) {
            $presetPostalCode = \Svea\WebPay\WebPayItem::presetValue()
                ->setTypeName(\Svea\WebPay\Checkout\Model\PresetValue::POSTAL_CODE)
                ->setValue($postcode)
                ->setIsReadonly(false);

            $order_builder->addPresetValue($presetPostalCode);
        }

        if (!is_null($phoneNumber)) {
            $presetPhoneNumber = \Svea\WebPay\WebPayItem::presetValue()
                ->setTypeName(\Svea\WebPay\Checkout\Model\PresetValue::PHONE_NUMBER)
                ->setValue($phoneNumber)
                ->setIsReadonly(false);

            $order_builder->addPresetValue($presetPhoneNumber);
        }

        if (!is_null($isCompany)) {
            $presetIsCompany = \Svea\WebPay\WebPayItem::presetValue()
                ->setTypeName(\Svea\WebPay\Checkout\Model\PresetValue::IS_COMPANY)
                ->setValue((bool)$isCompany) // cast to boolean since integration package requires it
                ->setIsReadonly(true);

            $order_builder->addPresetValue($presetIsCompany);
        }
    }

    private function addIdentityFlags($order_builder)
    {
        $hideNotYou        = $this->config->get($this->moduleString . 'sco_iframe_hide_not_you');
        $hideAnonymous     = $this->config->get($this->moduleString . 'sco_iframe_hide_anonymous');
        $hideChangeAddress = $this->config->get($this->moduleString . 'sco_iframe_hide_change_address');

        if (isset($hideNotYou) && $hideNotYou == 1) {
            $order_builder->addIdentityFlag(\Svea\WebPay\Checkout\Model\IdentityFlags::HIDENOTYOU);
        }

        if (isset($hideAnonymous) && $hideAnonymous == 1) {
            $order_builder->addIdentityFlag(\Svea\WebPay\Checkout\Model\IdentityFlags::HIDEANONYMOUS);
        }

        if (isset($hideChangeAddress) && $hideChangeAddress == 1) {
            $order_builder->addIdentityFlag(\Svea\WebPay\Checkout\Model\IdentityFlags::HIDECHANGEADDRESS);
        }
    }

    private function setMerchantData($order_builder)
    {
        if (isset($this->request->post['sco_newsletter']) && $this->request->post['sco_newsletter'] == "true") {
            $order_builder->setMerchantData("{\"newsletter\":\"true\"}");
        } else {
            $order_builder->setMerchantData("{\"newsletter\":\"false\"}");
        }
    }

    private function createUrl($route, $secure)
    {
        $url = ($secure ? 'https://' : 'http://');
        $url .= $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['SCRIPT_NAME']), '/.\\') . '/' . $route;

        return $url;
    }
}
