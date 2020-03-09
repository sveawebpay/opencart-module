<?php

require_once(DIR_APPLICATION . '../svea/config/configInclude.php');

class ControllerExtensionModuleSco extends Controller
{
    private $error = array();

    //backwards compatibility
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
        $this->load->language('extension/payment/svea_shared');
        $data = array();

        // If POST Request - Update module settings
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {

            // save checkout parameters
            $this->load->model('setting/setting');
            $this->model_setting_setting->editSetting($this->moduleString . 'sco', $this->request->post);

            $this->load->model('extension/svea/upgrade');
            $this->model_extension_svea_upgrade->upgradeDatabase('sco');

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
        if(VERSION < 3.0)
        {
            $data['token'] = $this->session->data['token'];
        }
        else
        {
            $data['user_token'] = $this->session->data['user_token'];
        }

        $this->load->model('localisation/country');
        $fields = array(
            'checkout_merchant_id_se' => null,
            'checkout_secret_word_se' => null,
            'checkout_merchant_id_no' => null,
            'checkout_secret_word_no' => null,
            'checkout_merchant_id_fi' => null,
            'checkout_secret_word_fi' => null,
            'checkout_merchant_id_dk' => null,
            'checkout_secret_word_dk' => null,
            'checkout_merchant_id_de' => null,
            'checkout_secret_word_de' => null,
            'checkout_test_merchant_id_se' => null,
            'checkout_test_secret_word_se' => null,
            'checkout_test_merchant_id_no' => null,
            'checkout_test_secret_word_no' => null,
            'checkout_test_merchant_id_fi' => null,
            'checkout_test_secret_word_fi' => null,
            'checkout_test_merchant_id_dk' => null,
            'checkout_test_secret_word_dk' => null,
            'checkout_test_merchant_id_de' => null,
            'checkout_test_secret_word_de' => null,
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
            'iframe_hide_not_you' => '0',
            'iframe_hide_anonymous' => '0',
            'iframe_hide_change_address' => '0',
            'force_flow' => '0',
            'force_b2b' => '0',
            'gather_newsletter_consent' => '0',
            'hide_svea_comments' => '0',
            'create_order_on_success_page' => '1',
            'create_order_on_received_push' => '1',
            'enable_electronic_id_authentication' => '0'
        );
        $data['options_on_checkout_page'] = array(
            $this->moduleString . 'sco_show_coupons_on_checkout' => $this->language->get('text_show_coupons_on_checkout'),
            $this->moduleString . 'sco_show_voucher_on_checkout' => $this->language->get('text_show_voucher_on_checkout'),
            $this->moduleString . 'sco_show_order_comment_on_checkout' => $this->language->get('text_show_order_comment_on_checkout')
        );

        $data['identity_flags'] = array(
            $this->moduleString . 'sco_iframe_hide_not_you' => $this->language->get('text_iframe_hide_not_you'),
            $this->moduleString . 'sco_iframe_hide_anonymous' => $this->language->get('text_iframe_hide_anonymous'),
            $this->moduleString . 'sco_iframe_hide_change_address' => $this->language->get('text_iframe_hide_change_address')
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

        // Add countries Sweden, Norway, Finland, Denmark and Germany
        $data['countries'] = array();
        array_push($data['countries'],$this->model_localisation_country->getCountry(203),$this->model_localisation_country->getCountry(160),$this->model_localisation_country->getCountry(72),$this->model_localisation_country->getCountry(57),$this->model_localisation_country->getCountry(81));

        // Load common controllers
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $data['file_link'] = $this->url->link('extension/module/sco', $this->userTokenString . 'token=' . $this->session->data[$this->userTokenString . 'token'], true);

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

        return !$this->error;
    }

    public function getNewsletterConsentList()
    {
        if (!$this->user->hasPermission('modify', 'extension/module/sco')) {
            http_response_code(401);
            return;
        }

        $this->load->model('extension/svea/newsletter');

        $list = $this->model_extension_svea_newsletter->getUsersConsentingToNewsletter();

        $formattedString = null;

        foreach($list as $key)
        {
            $formattedString = $formattedString . $key['email'] . "\n";
        }

        //$this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput($formattedString);
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

        // Heading
        $data['heading_title'] = $this->language->get('heading_title');
        $this->document->setTitle($data['heading_title']);

        // Misc
        $data['text_edit'] = $this->language->get('text_edit');
        $data['text_enabled'] = $this->language->get('text_enabled');
        $data['text_disabled'] = $this->language->get('text_disabled');
        $data['button_save'] = $this->language->get('button_save');
        $data['button_cancel'] = $this->language->get('button_cancel');

        // Tabs
        $data['tab_general'] = $this->language->get('tab_general');
        $data['tab_authorization'] = $this->language->get('tab_authorization');
        $data['tab_checkout_page_settings'] = $this->language->get('tab_checkout_page_settings');
        $data['tab_iframe_settings'] = $this->language->get('tab_iframe_settings');
        $data['tab_debug_settings'] = $this->language->get('tab_debug_settings');

        // General
        $data['version'] = VERSION;
        $data['entry_version_text'] = $this->language->get('entry_version_text');
        $data['entry_version'] = $this->language->get('entry_version');
        $data['entry_version_info'] = $this->language->get('entry_version_info');
        $data['entry_module_repo'] = $this->language->get('entry_module_repo');
        $data['entry_status'] = $this->language->get('entry_status');
        $data['entry_status_tooltip'] = $this->language->get('entry_status_tooltip');
        $data['entry_' . $this->moduleString . 'sco_show_widget_on_product_page'] = $this->language->get('text_show_widget_on_product_page');
        $data['entry_' . $this->moduleString . 'sco_show_widget_on_product_page_tooltip'] = $this->language->get('text_show_widget_on_product_page_tooltip');
        $data['entry_' . $this->moduleString . 'sco_hide_svea_comments'] = $this->language->get('text_hide_svea_comments');
        $data['entry_' . $this->moduleString . 'sco_hide_svea_comments_tooltip'] = $this->language->get('text_hide_svea_comments_tooltip');

        // Authorization
        $data['entry_checkout_default_country'] = $this->language->get('entry_checkout_default_country');
        $data['entry_checkout_default_country_tooltip'] = $this->language->get('entry_checkout_default_country_tooltip');
        $data['entry_test_mode'] = $this->language->get('entry_test_mode');
        $data['entry_test_mode_tooltip'] = $this->language->get('entry_test_mode_tooltip');

        $data['entry_sweden'] = $this->language->get('entry_sweden');
        $data['entry_norway'] = $this->language->get('entry_norway');
        $data['entry_finland'] = $this->language->get('entry_finland');
        $data['entry_denmark'] = $this->language->get('entry_denmark');
        $data['entry_germany'] = $this->language->get('entry_germany');

        $data['entry_stage_environment'] = $this->language->get('entry_stage_environment');
        $data['entry_prod_environment'] = $this->language->get('entry_prod_environment');
        $data['entry_checkout_merchant_id'] = $this->language->get('entry_checkout_merchant_id');
        $data['entry_checkout_secret'] = $this->language->get('entry_checkout_secret');

        // Checkout page settings
        $data['entry_status_checkout'] = $this->language->get('entry_status_checkout');
        $data['entry_status_checkout_tooltip'] = $this->language->get('entry_status_checkout_tooltip');
        //Options on checkout page on line 109
        $data[$this->moduleString . 'sco_show_coupons_on_checkout_tooltip'] = $this->language->get('text_show_coupons_on_checkout_tooltip');
        $data[$this->moduleString . 'sco_show_voucher_on_checkout_tooltip'] = $this->language->get('text_show_voucher_on_checkout_tooltip');
        $data[$this->moduleString . 'sco_show_order_comment_on_checkout_tooltip'] = $this->language->get('text_show_order_comment_on_checkout_tooltip');
        $data['entry_' . $this->moduleString . 'sco_gather_newsletter_consent'] = $this->language->get('text_gather_newsletter_consent');
        $data['entry_' . $this->moduleString . 'sco_gather_newsletter_consent_tooltip'] = $this->language->get('text_gather_newsletter_consent_tooltip');
        $data['entry_' . $this->moduleString . 'sco_download_newsletter_list'] = $this->language->get('text_download_newsletter_list');
        $data['entry_' . $this->moduleString . 'sco_newsletter_consent_list'] = $this->language->get('text_newsletter_consent_list');
        $data['entry_' . $this->moduleString . 'sco_close'] = $this->language->get('text_close');
        $data['entry_' . $this->moduleString . 'sco_copy_all_to_clipboard'] = $this->language->get('text_copy_all_to_clipboard');
        $data['entry_' . $this->moduleString . 'sco_error_fetching_newsletter_consent_list'] = $this->language->get('text_error_fetching_newsletter_consent_list');

        // Iframe settings
        $data['entry_shop_terms_uri'] = $this->language->get('entry_shop_terms_uri');
        $data['entry_shop_terms_uri_tooltip'] = $this->language->get('entry_shop_terms_uri_tooltip');
        $data['entry_shop_terms_uri_example'] = $_SERVER['HTTP_HOST'] . str_replace("admin", "", rtrim(dirname($_SERVER['SCRIPT_NAME']), '/.\\'));
        //Identity flags on line 115
        $data[$this->moduleString . 'sco_iframe_hide_not_you_tooltip'] = $this->language->get('text_iframe_hide_not_you_tooltip');
        $data[$this->moduleString . 'sco_iframe_hide_anonymous_tooltip'] = $this->language->get('text_iframe_hide_anonymous_tooltip');
        $data[$this->moduleString . 'sco_iframe_hide_change_address_tooltip'] = $this->language->get('text_iframe_hide_change_address_tooltip');
        $data['entry_' . $this->moduleString . 'sco_force_flow'] = $this->language->get('text_force_flow');
        $data['entry_' . $this->moduleString . 'sco_force_flow_tooltip'] = $this->language->get('text_force_flow_tooltip');
        $data['entry_' . $this->moduleString . 'sco_require_electronic_id_authentication'] = $this->language->get('text_require_electronic_id_authentication');
        $data['entry_' . $this->moduleString . 'sco_require_electronic_id_authentication_tooltip'] = $this->language->get('text_require_electronic_id_authentication_tooltip');

        // Debug settings
        $data['text_debug_warning']                                 = $this->language->get('text_debug_warning');
        $data['text_debug_create_order_on_success_page']            = $this->language->get('text_debug_create_order_on_success_page');
        $data['text_debug_create_order_on_success_page_tooltip']    = $this->language->get('text_debug_create_order_on_success_page_tooltip');
        $data['text_debug_create_order_on_received_push']           = $this->language->get('text_debug_create_order_on_received_push');
        $data['text_debug_create_order_on_received_push_tooltip']   = $this->language->get('text_debug_create_order_on_received_push_tooltip');
        $data['text_debug_simulate_push']                           = $this->language->get('text_debug_simulate_push');
        $data['text_debug_simulate_push_tooltip']                   = $this->language->get('text_debug_simulate_push_tooltip');
        $data['text_debug_simulate_push_button']                    = $this->language->get('text_debug_simulate_push_button');
        $data['text_debug_simulate_push_sent']                      = $this->language->get('text_debug_simulate_push_sent');
        $data['text_debug_simulate_push_error']                     = $this->language->get('text_debug_simulate_push_error');

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
        $this->load->model('extension/svea/campaigns');

        $result = $this->model_extension_svea_campaigns->checkIfOrderScoTableExists();

        if (!$result->num_rows)
        {
            $this->model_extension_svea_campaigns->createOrderScoTable();
        }
    }

    private function updateCampaigns()
    {
        $this->setVersionStrings();

        $this->load->model('extension/svea/campaigns');

        //Create table for SCO campaigns if it doesn't exist
        $this->model_extension_svea_campaigns->createScoCampaignsTableIfNotExist();
        //Truncate table every time the campaigns are updated
        $this->model_extension_svea_campaigns->truncateScoCampaignsTable();

        $testMode = $this->config->get($this->moduleString . 'sco_test_mode');

        $config = ($testMode == "1") ? new OpencartSveaCheckoutConfigTest($this) : new OpencartSveaCheckoutConfig($this);
        $testString = ($testMode == "1") ? "'" . $this->moduleString . "sco_checkout_test_merchant_id_%'" : "'" . $this->moduleString . "sco_checkout_merchant_id_%'";

        $countries = $this->model_extension_svea_campaigns->fetchScoCountries($testString);

        foreach ($countries->rows as $country)
        {
            if ($country['value'] != "")
            {
                $request = \Svea\WebPay\WebPay::checkout($config);

                $presetValueIsCompany = \Svea\WebPay\WebPayItem::presetValue()
                    ->setTypeName(\Svea\WebPay\Checkout\Model\PresetValue::IS_COMPANY)
                    ->setValue(false)
                    ->setIsReadonly(true);

                $request->setCountryCode(strtoupper(substr($country['key'], -2)))
                    ->addPresetValue($presetValueIsCompany);

                try
                {
                    $response = $request->getAvailablePartPaymentCampaigns();
                }
                catch (Exception $e)
                {
                    $this->log->write("Unable to fetch campaigns for countryCode '" . substr($country['key'], -2) . "' Reason: " . $e->getMessage());
                }

                if ($response != null)
                {
                    $this->model_extension_svea_campaigns->insertScoCampaignsToTable($response, $country);
                }
            }
        }
    }
}
