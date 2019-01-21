<?php

require_once(DIR_APPLICATION . '../svea/config/configInclude.php');

class ControllerExtensionModuleSco extends Controller
{
    private $error = array();

    // Use this name as params prefix (Svea checkout)
    private $module_version = '4.5.0';
    
    //backwards compatability
    private $userTokenString = "user_";
    private $linkString = "marketplace/extension";
    private $paymentString ="payment_";
    private $moduleString = "module_";
    private $appendString = "_before";
    private $eventString = "setting/event";
    
    public function setVersionStrings()
    {
        if(VERSION < 3.0)
        {
            $this->userTokenString = "";
            $this->linkString = "extension/extension";
            $this->paymentString = "";
            $this->moduleString = "";
            $this->appendString = "";
            $this->eventString = "extension/event";
        }
    }
    
    public function index()
    {
        $this->setVersionStrings();
        // get language
        $this->load->language('extension/module/sco');
        $data = array();

        // If POST Request - Update module settings
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {

            // save checkout parameters
            $this->load->model('setting/setting');
            $this->model_setting_setting->editSetting($this->moduleString . 'sco', $this->request->post);

            if($this->request->post[$this->moduleString . 'sco_show_widget_on_product_page'] == 1)
            {
                $this->updateCampaigns();
            }

            // success message
            $this->session->data['success'] = $this->language->get('text_success');

            // go back to module list
            $this->response->redirect($this->url->link($this->linkString, $this->userTokenString . 'token=' . $this->session->data[$this->userTokenString . 'token'] . '&type=module', true));
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
        $data['action'] = $this->url->link('extension/module/sco', $this->userTokenString . 'token=' . $this->session->data[$this->userTokenString . 'token'], true);

        // Set cancel url
        $data['cancel'] = $this->url->link('extension/module', $this->userTokenString . 'token=' . $this->session->data[$this->userTokenString . 'token'], true);

        $this->load->model('localisation/country');
        $fields = array(
            'checkout_merchant_id_se' => null,
            'checkout_secret_word_se' => null,
            'checkout_merchant_id_no' => null,
            'checkout_secret_word_no' => null,
            'checkout_merchant_id_fi' => null,
            'checkout_secret_word_fi' => null,
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
            'show_coupons_on_checkout' => '1',
            'show_voucher_on_checkout' => '1',
            'show_order_comment_on_checkout' => '1',
            'show_widget_on_product_page' => '0',
            'checkout_terms_uri' => '',
            'checkout_default_country_id' => '',
        );
        $data['options_on_checkout_page'] = array(
            $this->moduleString . 'sco_show_coupons_on_checkout' => $this->language->get('text_show_coupons_on_checkout'),
            $this->moduleString . 'sco_show_voucher_on_checkout' => $this->language->get('text_show_voucher_on_checkout'),
            $this->moduleString . 'sco_show_order_comment_on_checkout' => $this->language->get('text_show_order_comment_on_checkout'),
        );

        $data['text_yes'] = $this->language->get('text_yes');
        $data['text_no'] = $this->language->get('text_no');

        foreach ($fields as $field => $default) {
            if (isset($this->request->post[$this->moduleString . 'sco_' . $field])) {
                $data[$this->moduleString . 'sco_' . $field] = $this->request->post[$this->moduleString . 'sco_' . $field];
            } elseif ($this->config->has($this->moduleString . 'sco_' . $field)) {
                $data[$this->moduleString . 'sco_' . $field] = $this->config->get($this->moduleString . 'sco_' . $field);
            } else {
                $data[$this->moduleString . 'sco_' . $field] = $default;
            }
        }

        // Set custom events
        $this->load->model('extension/svea/events');

        // Add order statuses
        $this->load->model('localisation/order_status');
        $data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

        // Add countries sweden, norway, finland
        $data['countries'] = array();
        array_push($data['countries'],$this->model_localisation_country->getCountry(203),$this->model_localisation_country->getCountry(160),$this->model_localisation_country->getCountry(72));

        // Load common controllers
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        // set response
        $this->response->setOutput($this->load->view('extension/module/sco', $data));
    }

    /*
     * Validate input data
     * */
    protected function validate()
    {
        $this->setVersionStrings();
        // Validate permission
        if (!$this->user->hasPermission('modify', 'extension/module/sco')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        // Validate authorization data
        $status_field_name = $this->moduleString . 'sco_status';
        $test_mode_field_name = $this->moduleString . 'sco_test_mode';

        $post_fields = $this->request->post;

	    // - if status enabled
        if ($post_fields[$status_field_name] == '1')
        {
            $data = array(
                $this->moduleString . 'sco_checkout_merchant_id_se',
                $this->moduleString . 'sco_checkout_secret_word_se',
                $this->moduleString . 'sco_checkout_merchant_id_no',
                $this->moduleString . 'sco_checkout_secret_word_no',
                $this->moduleString . 'sco_checkout_merchant_id_fi',
                $this->moduleString . 'sco_checkout_secret_word_fi',);

	        // - if test-mode enabled set test credentials
        	if($post_fields[$test_mode_field_name] == '1')
	        {
                $data = array(
                    $this->moduleString . 'sco_checkout_test_merchant_id_se',
                    $this->moduleString . 'sco_checkout_test_secret_word_sweden',
                    $this->moduleString . 'sco_checkout_test_merchant_id_no',
                    $this->moduleString . 'sco_checkout_test_secret_word_norway',
                    $this->moduleString . 'sco_checkout_test_merchant_id_fi',
                    $this->moduleString . 'sco_checkout_test_secret_word_finland',);
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

        return !$this->error;
    }

    /*
     * Add event listener for checkout/checkout, and add custom redirect logic
     * when this is called
     * */
    public function install()
    {
        $this->setVersionStrings();
        // Set custom events
        $this->load->model('extension/svea/events');

        $this->model_extension_svea_events->addSveaCustomEvent(
            $this->moduleString . 'sco_edit_checkout_url' . $this->appendString,
            'catalog/controller/checkout/checkout/before',
            'extension/svea/checkout/redirectToScoPage'
        );

        if(VERSION < 3.0)
        {
            if($this->model_extension_event->getEvent($this->moduleString . 'sco_add_history_order_from_admin' . $this->appendString, "catalog/controller/api/order/history/before", "extension/svea/order/history") == NULL) {
                $this->model_extension_svea_events->addSveaCustomEvent(
                    $this->moduleString . 'sco_add_history_order_from_admin' . $this->appendString,
                    'catalog/controller/api/order/history/before',
                    'extension/svea/order/history'
                );
            }
        }
        else
        {
            if ($this->model_setting_event->getEventByCode($this->moduleString . "sco_add_history_order_from_admin" . $this->appendString) == NULL) {
                $this->model_extension_svea_events->addSveaCustomEvent(
                    $this->moduleString . 'sco_add_history_order_from_admin' . $this->appendString,
                    'catalog/controller/api/order/history/before',
                    'extension/svea/order/history'
                );
            }
        }
    }

    /*
     * Remove event listener for checkout/checkout
     * */
    public function uninstall()
    {
        $this->setVersionStrings();
        $this->load->model('extension/svea/events');
        $this->load->model($this->eventString);

        if(VERSION < 3.0)
        {
            $this->model_extension_event->deleteEvent($this->moduleString . 'sco_edit_checkout_url' . $this->appendString);
            $this->model_extension_event->deleteEvent($this->moduleString . 'sco_add_history_order_from_admin');
            $this->model_extension_event->deleteEvent($this->moduleString . 'sco_edit_order_from_admin' . $this->appendString);
        }
        else
        {
            $this->model_setting_event->deleteEvent($this->moduleString . 'sco_edit_checkout_url' . $this->appendString);
            $this->model_setting_event->deleteEvent($this->moduleString . 'sco_add_history_order_from_admin');
            $this->model_setting_event->deleteEvent($this->moduleString . 'sco_edit_order_from_admin' . $this->appendString);
        }
        $this->model_extension_svea_events->deleteSveaCustomEvents();
    }

    private function setLanguage()
    {
        $this->setVersionStrings();
        $data = array();

        // Set title
        $data['heading_title'] = $this->language->get('heading_title');
        $this->document->setTitle($data['heading_title']);

        $data['text_edit'] = $this->language->get('text_edit');
        $data['text_enabled'] = $this->language->get('text_enabled');
        $data['text_disabled'] = $this->language->get('text_disabled');

        $data['entry_test_mode'] = $this->language->get('entry_test_mode');
        $data['entry_status_checkout'] = $this->language->get('entry_status_checkout');
        $data['entry_status'] = $this->language->get('entry_status');
        $data['entry_order_status'] = $this->language->get('entry_order_status');
        $data['entry_show_options_on_checkout'] = $this->language->get('entry_show_options_on_checkout');

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

        $data['entry_' . $this->moduleString . 'sco_show_widget_on_product_page'] = $this->language->get('text_show_widget_on_product_page');
        $data['entry_' . $this->moduleString . 'sco_show_widget_on_product_page_tooltip'] = $this->language->get('text_show_widget_on_product_page_tooltip');

        $data['version'] = VERSION;

        $module_info_data_url = $url = "https://raw.githubusercontent.com/sveawebpay/opencart-module/master/docs/info.json";
        $json_info = file_get_contents($module_info_data_url);
        $decoded_data = json_decode($json_info);
        $latest_version = $decoded_data->module_version;


        $data['module_version'] = $this->module_version;
        if ($latest_version > $this->module_version) {
            $data['module_repo_url'] = 'https://github.com/sveawebpay/opencart-module/archive/master.zip';
            $data['module_version_info'] = $this->language->get('text_module_version_info_new');
        } else {
            $data['module_repo_url'] = 'https://github.com/sveawebpay/opencart-module/blob/master/README.md';
            $data['module_version_info'] = $this->language->get('text_module_version_info');
        }

        return $data;
    }

    private function setBreadcrumbs()
    {
        $this->setVersionStrings();

        $data = array();

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', $this->userTokenString . 'token=' . $this->session->data[$this->userTokenString . 'token'], true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link($this->linkString, $this->userTokenString . 'token=' . $this->session->data[$this->userTokenString . 'token'] . '&type=module', true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/module/sco', $this->userTokenString . 'token=' . $this->session->data[$this->userTokenString . 'token'], true)
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
    private function updateCampaigns()
    {
        $this->setVersionStrings();

        $this->db->query('CREATE TABLE IF NOT EXISTS `' . DB_PREFIX . $this->moduleString .'sco_campaigns`
                (`id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `campaignCode` VARCHAR( 100 ) NOT NULL,
                `contractLengthInMonths` INT NOT NULL ,
                `description` VARCHAR( 100 ) NOT NULL ,
                `fromAmount` DOUBLE NOT NULL ,
                `initialFee` DOUBLE NOT NULL ,
                `interestRatePercent` INT NOT NULL ,
                `monthlyAnnuityFactor` DOUBLE NOT NULL ,
                `notificationFee` DOUBLE NOT NULL ,
                `numberOfInterestFreeMonths` INT NOT NULL ,
                `numberOfPaymentFreeMonths` INT NOT NULL ,
                `paymentPlanType` VARCHAR( 100 ) NOT NULL ,
                `toAmount` DOUBLE NOT NULL ,
                `timestamp` INT UNSIGNED NOT NULL,
                `countryCode` VARCHAR( 100 ) NOT NULL,
                `productionEnvironment` INT NOT NULL
            )   ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1
            ');

        $this->db->query('TRUNCATE TABLE ' . DB_PREFIX . $this->moduleString .'sco_campaigns');

        $testMode = $this->model_setting_setting->getSettingValue($this->moduleString . 'sco_test_mode');

        $config = ($testMode == "1") ? new OpencartSveaCheckoutConfigTest($this->config) : new OpencartSveaCheckoutConfig($this->config);
        $testString = ($testMode == "1") ? "'" . $this->moduleString . "sco_checkout_test_merchant_id_%'" : "'" . $this->moduleString . "sco_checkout_merchant_id_%'";

        $countriesQuery = $this->db->query("SELECT `key`, `value` FROM " . DB_PREFIX . "setting WHERE `key` LIKE " . $testString . ";");

        foreach ($countriesQuery->rows as $val) {
            if ($val['value'] != "") {
                $request = \Svea\WebPay\WebPay::checkout($config);

                $presetValueIsCompany = \Svea\WebPay\WebPayItem::presetValue()
                    ->setTypeName(\Svea\WebPay\Checkout\Model\PresetValue::IS_COMPANY)
                    ->setValue(false)
                    ->setIsReadonly(true);

                $request->setCountryCode(strtoupper(substr($val['key'], -2)))
                    ->addPresetValue($presetValueIsCompany);

                $response = $request->getAvailablePartPaymentCampaigns();

                if ($response == null) {

                } else {
                    foreach ($response as $responseResultItem) {
                        try {
                            $campaignCode = (isset($responseResultItem['CampaignCode'])) ? $responseResultItem['CampaignCode'] : "";
                            $description = (isset($responseResultItem['Description'])) ? $responseResultItem['Description'] : "";
                            $paymentPlanType = (isset($responseResultItem['PaymentPlanType'])) ? $responseResultItem['PaymentPlanType'] : "";
                            $contractLength = (isset($responseResultItem['ContractLengthInMonths'])) ? $responseResultItem['ContractLengthInMonths'] : "";
                            $monthlyAnnuityFactor = (isset($responseResultItem['MonthlyAnnuityFactor'])) ? $responseResultItem['MonthlyAnnuityFactor'] : "";
                            $initialFee = (isset($responseResultItem['InitialFee'])) ? $responseResultItem['InitialFee'] : "";
                            $notificationFee = (isset($responseResultItem['NotificationFee'])) ? $responseResultItem['NotificationFee'] : "";
                            $interestRatePercentage = (isset($responseResultItem['InterestRatePercent'])) ? $responseResultItem['InterestRatePercent'] : "";
                            $interestFreeMonths = (isset($responseResultItem['NumberOfInterestFreeMonths'])) ? $responseResultItem['NumberOfInterestFreeMonths'] : "";
                            $paymentFreeMonths = (isset($responseResultItem['NumberOfPaymentFreeMonths'])) ? $responseResultItem['NumberOfPaymentFreeMonths'] : "";
                            $fromAmount = (isset($responseResultItem['FromAmount'])) ? $responseResultItem['FromAmount'] : "";
                            $toAmount = (isset($responseResultItem['ToAmount'])) ? $responseResultItem['ToAmount'] : "";

                            try {
                                $this->db->query("INSERT INTO " . DB_PREFIX . $this->moduleString ."sco_campaigns SET
                                    campaignCode = '" . $this->db->escape($campaignCode) . "',
                                    contractLengthInMonths = '" . $this->db->escape($contractLength) . "',
                                    description = '" . $this->db->escape($description) . "',
                                    fromAmount = '" . $this->db->escape($fromAmount) . "',
                                    initialFee = '" . $this->db->escape($initialFee) . "',
                                    interestRatePercent = '" . $this->db->escape($interestRatePercentage) . "',
                                    monthlyAnnuityFactor = '" . $this->db->escape($monthlyAnnuityFactor) . "',
                                    notificationFee = '" . $this->db->escape($notificationFee) . "',
                                    numberOfInterestFreeMonths = '" . $this->db->escape($interestFreeMonths) . "',
                                    numberOfPaymentFreeMonths = '" . $this->db->escape($paymentFreeMonths) . "',
                                    paymentPlanType = '" . $this->db->escape($paymentPlanType) . "',
                                    toAmount = '" . $this->db->escape($toAmount) . "',
                                    timestamp = '" . $this->db->escape(time()) . "',
                                    countryCode = '" . $this->db->escape(strtoupper(substr($val['key'], -2))) . "'");
                            } catch (Exception $e) {
                                $this->log->write($e->getMessage());
                            }
                        } catch (Exception $e) {
                            $this->log->write($e->getMessage());
                        }
                    }
                }
            }
        }
    }
}
