<?php

require_once(DIR_APPLICATION . 'controller/payment/svea_common.php');
require_once(DIR_APPLICATION . '../svea/config/configInclude.php');

class ControllerSveaPayment extends SveaCommon
{
    public function index()
    {
        unset( $this->session->data['svea_last_page'] );
        unset( $this->session->data['sco_success_order_id'] );

        $forceCreate = isset($this->request->get['create']) && $this->request->get['create'] === 'true';
        if ($forceCreate === true) {
            unset($this->session->data['order_id']);
            unset($this->session->data['sco_order_id']);
            unset($this->session->data['sco_cart_hash']);
        }

        $email = isset($this->request->post['email']) ? $this->request->post['email'] : null;
        $order_id = (isset($this->session->data['order_id'])) ? (int)$this->session->data['order_id'] : null;

        $this->load->language('svea/checkout');
        $this->load->model('extension/extension');

        $data['heading_error'] = $this->language->get('heading_error');
        $data['error_unknown'] = $this->language->get('error_unknown');

        $order_id = $this->addOrder($order_id, $email);

        $currency = strtoupper($this->session->data['sco_currency']);

        $this->load->model('localisation/currency');
        $currency_info = $this->model_localisation_currency->getCurrencyByCode($currency);
        $currency_value = $currency_info['value'];

        $products = $this->cart->getProducts();

        $config = new OpencartSveaCheckoutConfig($this->config, 'checkout');
        if ($this->config->get('sco_test_mode')) {
            $config = new OpencartSveaCheckoutConfigTest($this->config, 'checkout');
        }

        $checkout_order_entry = \Svea\WebPay\WebPay::checkout($config);

        $order_builder = $checkout_order_entry->getCheckoutOrderBuilder();

        $this->setOrderGeneralData($checkout_order_entry);

        $order_builder = $this->addOrderRowsToWebServiceOrder($order_builder, $products, $currency_value);

        $this->addPresetValues($order_builder);

        $add_ons = $this->addTaxRateToAddons();
        $this->addAddonRowsToSveaOrder($order_builder, $add_ons, $currency_value);

        $sco_order_id = isset($this->session->data['sco_order_id']) ? $this->session->data['sco_order_id'] : null;
        $isScoUpdate = false;
        $isChangedState = $this->isChangedState();

        try {
            if ($sco_order_id) {
                $isScoUpdate = true;
                $checkout_order_entry->setCheckoutOrderId($sco_order_id);

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
                $this->session->data['sco_order_id'] = $response['orderid'];

                // - update oc_order_sco.checkout_id
                $this->updateCheckoutRow($order_id, $response);

                // - this will used for success page, and for optional second call
                $this->session->data['svea_last_page'] = 'svea/payment';
                $this->session->data['sco_success_order_id'] = $response['orderid'];

                // Show Svea checkout snippet
                $data['snippet'] = $response['gui']['snippet'];
            }

            $this->response->setOutput($this->load->view('default/template/svea/payment.tpl', $data));

        } catch (\Exception $e) {
            header('HTTP/1.1 500 PHP Library Error');
            header('Content-Type: application/json; charset=UTF-8');
            $er = array();
            $er['message'] = $e->getMessage();
            $er['isScoUpdate'] = $isScoUpdate; // this is update request flag
            die(json_encode($er));
        }

    }

    private function getHashOfCurrentState()
    {
        $products = $this->cart->getProducts();
        $session_copy = $this->session->data;
        unset($session_copy['sco_cart_hash']);
        unset($session_copy['sco_order_id']);
        unset($session_copy['svea_last_page']);
        unset($session_copy['sco_success_order_id']);

        return md5(serialize($session_copy) . serialize($products));
    }

    private function getHashOfOldState()
    {
        return isset($this->session->data['sco_cart_hash']) ? $this->session->data['sco_cart_hash'] : null;
    }

    private function saveHashOfCurrentState()
    {
        $this->session->data['sco_cart_hash'] = $this->getHashOfCurrentState();
    }

    private function isChangedState()
    {
        return $this->getHashOfOldState() !== $this->getHashOfCurrentState();
    }

    private function setOrderGeneralData($checkout_order_entry)
    {
        $terms_uri =  $this->url->link('information/information', array('information_id' => $this->config->get('config_checkout_id')));
        $config_terms_uri_secured = $this->config->get('sco_checkout_terms_uri_secured');
        $config_terms_uri = $this->config->get('sco_checkout_terms_uri');
        if ($config_terms_uri != "") {
            $terms_uri =  $this->createUrl($config_terms_uri, $config_terms_uri_secured);
        }

        $checkout_order_entry
            ->setCountryCode($this->session->data['sco_country'])// customer country, we recommend basing this on the customer billing address
            ->setCurrency($this->session->data['sco_currency'])
            ->setCheckoutUri($this->url->link('svea/checkout'))
            ->setConfirmationUri($this->url->link('svea/success'))
            ->setPushUri(str_replace('&amp;', '&', urldecode($this->url->link('svea/push', array('svea_order' => '{checkout.order.uri}')))))
            ->setTermsUri(str_replace('&amp;', '&',(urldecode($terms_uri))))
            ->setLocale($this->session->data['sco_locale']);
    }

    private function addOrder($order_id, $email)
    {
        $this->load->language('svea/checkout');

        // SET VALUES
        $customer_id 		= 0;
        $customer_group_id 	= $this->config->get('config_customer_group_id');
        $firstname 			= null;
        $lastname 			= null;
        $telephone 			= null;
        $fax 				= null;
        $custom_field 		= null;
        $products           = array();

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
            $custom_field 		= unserialize($customer_info['custom_field']);
        }

        $sort_order	= array();
        $total_data	= array();
        $taxes		= $this->cart->getTaxes();
        $total		= 0;

        $this->load->model('extension/extension');

        $results = $this->model_extension_extension->getExtensions('total');

        foreach ($results as $key => $value) {
            $sort_order[$key] = $this->config->get($value['code'] . '_sort_order');
        }

        array_multisort($sort_order, SORT_ASC, $results);

        foreach ($results as $result) {
            if ($this->config->get($result['code'] . '_status')) {
                $this->load->model('total/' . $result['code']);
                $this->{'model_total_' . $result['code']}->getTotal($total_data, $total, $taxes);
            }
        }

        $sort_order = array();

        foreach ($total_data as $key => $value) {
            $sort_order[$key] = $value['sort_order'];
        }

        array_multisort($sort_order, SORT_ASC, $total_data);


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

        }// - end foreach products


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
            'custom_field'				=> NULL,
            'payment_firstname' 		=> NULL,
            'payment_lastname' 			=> NULL,
            'payment_company' 			=> NULL,
            'payment_address_1' 		=> NULL,
            'payment_address_2' 		=> NULL,
            'payment_city' 				=> NULL,
            'payment_postcode' 			=> NULL,
            'payment_country' 			=> NULL,
            'payment_country_id' 		=> NULL,
            'payment_zone' 				=> NULL,
            'payment_zone_id' 			=> NULL,
            'payment_address_format' 	=> NULL,
            'payment_custom_field' 		=> NULL,
            'payment_method' 			=> $this->language->get('text_payment_method'),
            'payment_code'				=> 'sco',
            'shipping_firstname' 		=> NULL,
            'shipping_lastname' 		=> NULL,
            'shipping_company' 			=> NULL,
            'shipping_address_1' 		=> NULL,
            'shipping_address_2' 		=> NULL,
            'shipping_city' 			=> NULL,
            'shipping_postcode' 		=> NULL,
            'shipping_country' 			=> NULL,
            'shipping_country_id' 		=> NULL,
            'shipping_zone' 			=> NULL,
            'shipping_zone_id' 			=> NULL,
            'shipping_address_format' 	=> NULL,
            'shipping_custom_field' 	=> NULL,
            'shipping_method' 			=> isset($this->session->data['shipping_method']['title']) 	? $this->session->data['shipping_method']['title'] 					: NULL,
            'shipping_code' 			=> isset($this->session->data['shipping_method']['code'])  	? $this->session->data['shipping_method']['code']  					: NULL,
            'comment' 					=> isset($this->session->data['comment']) 					? $this->session->data['comment'] 									: NULL,
            'affiliate_id' 				=> isset($affiliate_id) 									? $affiliate_id 													: NULL,
            'commission' 				=> isset($commission) 										? $commission 														: NULL,
            'marketing_id' 				=> isset($marketing_id) 									? $marketing_id 													: NULL,
            'tracking' 					=> isset($tracking) 										? $tracking 														: NULL,
            'currency_id' 				=> isset($this->session->data['sco_currency']) 				? $this->currency->getId($this->session->data['sco_currency']) 		: '0',
            'currency_code' 			=> isset($this->session->data['sco_currency']) 				? $this->session->data['sco_currency'] 								: 'SEK',
            'currency_value' 			=> isset($this->session->data['sco_currency']) 				? $this->currency->getValue($this->session->data['sco_currency'])	: '0',
            'ip' 						=> isset($this->request->server['REMOTE_ADDR']) 			? $this->request->server['REMOTE_ADDR'] 							: NULL,
            'forwarded_ip' 				=> isset($this->request->server['HTTP_X_FORWARDED_FOR']) 	? $this->request->server['HTTP_X_FORWARDED_FOR'] 					: NULL,
            'user_agent' 				=> isset($this->request->server['HTTP_USER_AGENT']) 		? $this->request->server['HTTP_USER_AGENT'] 						: NULL,
            'accept_language' 			=> isset($this->request->server['HTTP_ACCEPT_LANGUAGE']) 	? $this->request->server['HTTP_ACCEPT_LANGUAGE'] 					: NULL,
            'language_id' 				=> $this->config->get('config_language_id'),
            'vouchers'					=> $vouchers,
            'products'					=> $products,
            'totals'					=> $total_data,
            'total' 					=> $total,
        );

        $this->load->model('svea/checkout');

        // - get order id
        $order_id = $this->model_svea_checkout->addOrder($order);

        /*
         * Read from session
         * */
        $locale = isset($this->session->data['sco_locale']) ? strtolower($this->session->data['sco_locale']) : 'sv-se'; 

        $this->model_svea_checkout->addCheckoutOrder($order_id, $locale);

        $this->session->data['order_id'] = (int)$order_id;

        return (int)$order_id;
    }

 
    private function updateCheckoutRow($order_id, $response)
    {
        if (!is_array($response)) {
            return;
        }

        $this->load->model('svea/checkout');

        $country = $this->model_svea_checkout->getCountry($response['countrycode']);

        $data = array(
            'order_id' => $response['clientordernumber'],
            'checkout_id' => $response['orderid'],
            'status' => isset( $response['status'] ) ? $response['status'] : null,

            'type'   => isset( $response['customer']['iscompany'] ) ? ( $response['customer']['iscompany'] ? 'company' : 'person') : 'person',
            'gender' => isset( $response['customer']['ismale'] ) ? ( $response['customer']['ismale'] ? 'male' : 'female' ) : null,
            'date_of_birth' => isset( $response['customer']['iscompany'] ) ? $response['customer']['dateofbirth'] : null,

            'locale' => isset( $response['locale'] ) ? $response['locale'] : null,
            'currency' => isset( $response['currency'] ) ? $response['currency'] : null,
            'country' =>   $country['name'],
        );

        $this->model_svea_checkout->updateCheckoutOrder($data);
    }


    private function addPresetValues($order_builder)
    {
        $email = isset($this->request->post['email']) ? $this->request->post['email'] : null;
        $postcode = isset($this->request->post['postcode']) ? $this->request->post['postcode'] : null;
        $phone_number = $this->customer->isLogged() ? $this->customer->getTelephone() : null;

        if(!is_null($email)){
            $presetEmail = \Svea\WebPay\WebPayItem::presetValue()
                ->setTypeName(\Svea\WebPay\Checkout\Model\PresetValue::EMAIL_ADDRESS)
                ->setValue($email)
                ->setIsReadonly(false);

            $order_builder->addPresetValue($presetEmail);
        }

        if(!is_null($postcode)){
            $presetPostalCode = \Svea\WebPay\WebPayItem::presetValue()
                ->setTypeName(\Svea\WebPay\Checkout\Model\PresetValue::POSTAL_CODE)
                ->setValue($postcode)
                ->setIsReadonly(false);

            $order_builder->addPresetValue($presetPostalCode);
        }

        if(!is_null($phone_number)){
            $presetPhoneNumber = \Svea\WebPay\WebPayItem::presetValue()
                ->setTypeName(\Svea\WebPay\Checkout\Model\PresetValue::PHONE_NUMBER)
                ->setValue($phone_number)
                ->setIsReadonly(false);

            $order_builder->addPresetValue($presetPhoneNumber);
        }

    }

    private function createUrl($route, $secure) {
        $url = ($secure ? 'https://' : 'http://');
        $url .= $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['SCRIPT_NAME']), '/.\\') . '/' . $route;
        return $url;
    }

}
