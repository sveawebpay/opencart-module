<?php

class ControllerModuleSco extends Controller
{
    private $error = array();

    // Use this name as params prefix (Svea checkout)
    private $name = 'sco';
    private $module_version = '4.1.0';

    public function index()
    {
        // get language
        $this->load->language('module/' . $this->name);
        $data = array();

        // If POST Request - Update module settings
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {

            // save checkout parameters
            $this->load->model('setting/setting');
            $this->model_setting_setting->editSetting($this->name, $this->request->post);

            // success message
            $this->session->data['success'] = $this->language->get('text_success');

            $status_field_name = $this->name . '_status';
            $old_status = $this->config->get('sco_status');
            if ($this->request->post[$status_field_name] == '1' && empty($old_status)) {
                $this->disableOldPaymentTypes();
            }

            // go back to module list
            $this->response->redirect($this->url->link('extension/module', 'token=' . $this->session->data['token'], 'SSL'));
        }

        $this->setCheckoutDBTable();

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        $data = array_merge($data, $this->setLanguage());
        $data = array_merge($data, $this->setBreadcrumbs());

        // Set action url
        $data['action'] = $this->url->link('module/' . $this->name, 'token=' . $this->session->data['token'], 'SSL');

        // Set cancel url
        $data['cancel'] = $this->url->link('extension/module', 'token=' . $this->session->data['token'], 'SSL');

        $this->load->model('localisation/country');
        $fields = array(
            'checkout_merchant_id' => null,
            'checkout_secret_word' => null,
            'checkout_merchant_id_se' => null,
            'checkout_secret_word_se' => null,
            'checkout_merchant_id_no' => null,
            'checkout_secret_word_no' => null,
            'checkout_merchant_id_fi' => null,
            'checkout_secret_word_fi' => null,
            'checkout_test_merchant_id' => null,
            'checkout_test_secret_word' => null,
            'checkout_test_merchant_id_se' => null,
            'checkout_test_secret_word_se' => null,
            'checkout_test_merchant_id_no' => null,
            'checkout_test_secret_word_no' => null,
            'checkout_test_merchant_id_fi' => null,
            'checkout_test_secret_word_fi' => null,
            'status' => '0',
            'test_mode' => '1',
            'status_checkout' => '0',
            'product_option' => '0',
            'failed_status_id' => '10',
            'pending_status_id' => '1',
            'delivered_status_id' => '15',
            'canceled_status_id' => '7',
            'credited_status_id' => '11',
            'show_coupons_on_checkout' => '1',
            'show_voucher_on_checkout' => '1',
            'show_order_comment_on_checkout' => '1',
            'checkout_terms_uri' => '',
            'checkout_default_country_id' => ''
        );

        $data['options_on_checkout_page'] = array(
            'sco_show_coupons_on_checkout' => $this->language->get('text_show_coupons_on_checkout'),
            'sco_show_voucher_on_checkout' => $this->language->get('text_show_voucher_on_checkout'),
            'sco_show_order_comment_on_checkout' => $this->language->get('text_show_order_comment_on_checkout')
        );

        $data['text_yes'] = $this->language->get('text_yes');
        $data['text_no'] = $this->language->get('text_no');

        foreach ($fields as $field => $default) {
            if (isset($this->request->post[$this->name . '_' . $field])) {
                $data[$this->name . '_' . $field] = $this->request->post[$this->name . '_' . $field];
            } elseif ($this->config->has($this->name . '_' . $field)) {
                $data[$this->name . '_' . $field] = $this->config->get($this->name . '_' . $field);
            } else {
                $data[$this->name . '_' . $field] = $default;
            }
        }

        // Add order statuses
        $this->load->model('localisation/order_status');
        $data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

        // Load common controllers
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $data['countries'] = array();
        array_push($data['countries'],$this->model_localisation_country->getCountry(203),$this->model_localisation_country->getCountry(160),$this->model_localisation_country->getCountry(72));
        // set response
        $this->response->setOutput($this->load->view('module/sco.tpl', $data));
    }

    /*
     * Validate input data
     * */
    protected function validate()
    {
        // Validate permission
        if (!$this->user->hasPermission('modify', 'module/' . $this->name)) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

	    // Validate authorization data
	    $status_field_name = $this->name . '_status';
	    $test_mode_field_name = $this->name . '_test_mode';

	    $post_fields = $this->request->post;

	    // - if status enabled
        if ($post_fields[$status_field_name] == '1')
        {
            $data = array(
                'sco_checkout_merchant_id_se',
                'sco_checkout_secret_word_se',
                'sco_checkout_merchant_id_no',
                'sco_checkout_secret_word_no',
                'sco_checkout_merchant_id_fi',
                'sco_checkout_secret_word_fi',);

            // - if test-mode enabled set test credentials
            if($post_fields[$test_mode_field_name] == '1')
            {
                $data = array(
                    'sco_checkout_test_merchant_id_se',
                    'sco_checkout_test_secret_word_se',
                    'sco_checkout_test_merchant_id_no',
                    'sco_checkout_test_secret_word_no',
                    'sco_checkout_test_merchant_id_fi',
                    'sco_checkout_test_secret_word_fi',);
            }

            // - check values
            $empty_field_count = 0;
            for($i = 0; $i < count($data); $i++)
            {
                if(empty($post_fields[current($data)]))
                {
                    $empty_field_count++;
                }
                next($data);
            }
            if($empty_field_count == 6)
            {
                $this->error['warning'] = $this->language->get('error_authorization_data');
            }
        }

        $this->error = $this->validateStatuses();

        return !$this->error;
    }

    private function validateStatuses()
    {
        $post_fields = $this->request->post;

        $pending_status_field = $this->name . '_pending_status_id';
        $failed_status_field = $this->name . '_failed_status_id';
        $delivered_status_field = $this->name . '_delivered_status_id';
        $canceled_status_field = $this->name . '_canceled_status_id';
        $credited_status_field = $this->name . '_credited_status_id';

        $statuses = array(
            $post_fields[$pending_status_field],
            $post_fields[$failed_status_field],
            $post_fields[$delivered_status_field],
            $post_fields[$canceled_status_field],
            $post_fields[$credited_status_field],
        );

        foreach (array_count_values($statuses) as $val => $c) {
            if ($c > 1) {
                $this->error['warning'] = $this->language->get('error_duplicate_statuses');
            }
        }

        return $this->error;
    }

    /**
     * Disable all old Svea payment types
     */
    private function disableOldPaymentTypes()
    {
        $svea_payment_statuses = array(
            'svea_directbank' => 'svea_directbank_status',
            'svea_card' => 'svea_card_status',
            'svea_partpayment' => 'svea_partpayment_status',
            'svea_invoice' => 'svea_invoice_status'
        );

        $store_id = 0;
        foreach ($svea_payment_statuses as $code => $value) {
            $this->db->query("UPDATE " . DB_PREFIX . "setting SET
                `value` = '0'
            WHERE   `code` = '" . $this->db->escape($code) . "' AND
                    `key` = '" . $this->db->escape($value) . "' AND
                    store_id = '" . (int)$store_id . "'");
        }
    }

    private function setLanguage()
    {
        $data = array();

        // Set title
        $data['heading_title'] = $this->language->get('heading_title');
        $this->document->setTitle($data['heading_title']);

        $data['text_edit'] = $this->language->get('text_edit');
        $data['text_enabled'] = $this->language->get('text_enabled');
        $data['text_disabled'] = $this->language->get('text_disabled');

        $data['entry_test_mode'] = $this->language->get('entry_test_mode');
        $data['created_status_order'] = $this->language->get('created_status_order');
        $data['pending_status_order'] = $this->language->get('pending_status_order');
        $data['failed_status_order'] = $this->language->get('failed_status_order');
        $data['entry_status_checkout'] = $this->language->get('entry_status_checkout');
        $data['entry_status'] = $this->language->get('entry_status');
        $data['entry_order_status'] = $this->language->get('entry_order_status');
        $data['entry_oc_order_status'] = $this->language->get('entry_oc_order_status');
        $data['entry_show_options_on_checkout'] = $this->language->get('entry_show_options_on_checkout');

        $data['entry_status_refunded'] = $this->language->get('entry_status_refunded');
        $data['entry_status_refunded_text'] = $this->language->get('entry_status_refunded_text');
        $data['entry_status_canceled'] = $this->language->get('entry_status_canceled');
        $data['entry_status_canceled_text'] = $this->language->get('entry_status_canceled_text');
        $data['entry_status_delivered'] = $this->language->get('entry_status_delivered');
        $data['entry_status_delivered_text'] = $this->language->get('entry_status_delivered_text');
        $data['entry_shop_terms_uri'] = $this->language->get('entry_shop_terms_uri');
        $data['entry_shop_terms_uri_example'] = $_SERVER['HTTP_HOST'] . str_replace("admin", "", rtrim(dirname($_SERVER['SCRIPT_NAME']), '/.\\'));

        $data['entry_checkout_merchant_id'] = $this->language->get('entry_checkout_merchant_id');
        $data['entry_checkout_secret'] = $this->language->get('entry_checkout_secret');

        $data['entry_product_option'] = $this->language->get('entry_product_option');

        $data['tab_general'] = $this->language->get('tab_general');
        $data['tab_authorization'] = $this->language->get('tab_authorization');
        $data['entry_checkout_default_country'] = $this->language->get('entry_checkout_default_country');
        $data['entry_checkout_default_country_text'] = $this->language->get('entry_checkout_default_country_text');
        $data['tab_authorization_test'] = $this->language->get('tab_authorization_test');
	    $data['tab_authorization_prod'] = $this->language->get('tab_authorization_prod');
        $data['entry_sweden'] = $this->language->get('entry_sweden');
        $data['entry_norway'] = $this->language->get('entry_norway');
        $data['entry_finland'] = $this->language->get('entry_finland');

        $data['button_save'] = $this->language->get('button_save');
        $data['button_cancel'] = $this->language->get('button_cancel');

        $module_info_data_url = $url = "https://raw.githubusercontent.com/sveawebpay/opencart-module/oc-2-0/docs/info.json";
        $json_info = file_get_contents($module_info_data_url);
        $decoded_data = json_decode($json_info);
        $latest_version = $decoded_data->module_version;


        $data['module_version'] = $this->module_version;
        if ($latest_version > $this->module_version) {
            $data['module_repo_url'] = 'https://github.com/sveawebpay/opencart-module/archive/oc-2-0.zip';
            $data['module_version_info'] = $this->language->get('text_module_version_info_new');
        } else {
            $data['module_repo_url'] = 'https://github.com/sveawebpay/opencart-module/blob/oc-2-0/README.md';
            $data['module_version_info'] = $this->language->get('text_module_version_info');
        }

        return $data;
    }

    private function setBreadcrumbs()
    {
        $data = array();

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL')
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_module'),
            'href' => $this->url->link('extension/module', 'token=' . $this->session->data['token'], 'SSL')
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('module/' . $this->name, 'token=' . $this->session->data['token'], 'SSL')
        );

        return $data;
    }


    private function setCheckoutDBTable()
    {
        $result = $this->db->query("SHOW TABLES LIKE '" . DB_PREFIX . "order_sco'");

        if (!$result->num_rows) {

            $this->db->query("CREATE TABLE `" . DB_PREFIX . "order_sco` (
                                `order_id`				int(11) unsigned NOT NULL AUTO_INCREMENT,
                                `checkout_id`           int(11) unsigned DEFAULT NULL, 
                                `gender`                varchar(20) DEFAULT NULL,
                                `date_of_birth`         varchar(20) DEFAULT NULL,
                                `locale` 				varchar(10) DEFAULT NULL,
                                `country` 				varchar(8) DEFAULT NULL,
                                `currency` 				varchar(4) DEFAULT NULL, 
                                `status` 				varchar(30) DEFAULT NULL,
                                `type` 					varchar(20) DEFAULT NULL, 
                                `notes` 	        	text DEFAULT NULL,  
            					`date_added` 			datetime DEFAULT NULL, 
            					`date_modified` 		datetime DEFAULT NULL,
                              PRIMARY KEY (`order_id`)
                            ) ENGINE=MyISAM DEFAULT CHARSET=utf8; ");
        }
    }

}
