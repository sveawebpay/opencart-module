<?php

require_once(DIR_APPLICATION . '../svea/config/configInclude.php');

class ControllerExtensionPaymentSveaInvoice extends Controller
{
    private $error = array();

    //backwards compatability
    private $userTokenString = "user_";
    private $linkString = "marketplace/extension";
    private $paymentString ="payment_";
    private $moduleString = "module_";
    private $appendString = "_before";

    public function index()
    {
        $this->load->language('extension/payment/svea_invoice');
        $this->load->language('extension/payment/svea_shared');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('setting/setting');

        $this->setVersionStrings();

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && ($this->validate())) {
            $this->model_setting_setting->editSetting($this->paymentString . 'svea_invoice', $this->request->post);

            $this->session->data['success'] = $this->language->get('text_success');

            $this->response->redirect($this->url->link($this->linkString, $this->userTokenString . 'token=' . $this->session->data[$this->userTokenString . 'token'] . '&type=payment', true));
        }

        $data['entry_version_text']          = $this->language->get('entry_version_text');
        $data['entry_version']               = $this->language->get('entry_version');
        $data['entry_version_info']          = $this->language->get('entry_version_info');
        $data['entry_module_repo']           = $this->language->get('entry_module_repo');

        $data['heading_title']               = $this->language->get('heading_title');

        $data['text_enabled']                = $this->language->get('text_enabled');
        $data['text_disabled']               = $this->language->get('text_disabled');
        $data['text_all_zones']              = $this->language->get('text_all_zones');

        $data['entry_shipping_billing']      = $this->language->get('entry_shipping_billing');
        $data['entry_shipping_billing_text'] = $this->language->get('entry_shipping_billing_text');
        $data['entry_geo_zone']              = $this->language->get('entry_geo_zone');
        $data['entry_status']                = $this->language->get('entry_status');
        $data['entry_sort_order']            = $this->language->get('entry_sort_order');
        $data['entry_payment_description']   = $this->language->get('entry_payment_description');
        $data['button_save']                 = $this->language->get('button_save');
        $data['button_cancel']               = $this->language->get('button_cancel');
        $data['tab_general']                 = $this->language->get('tab_general');
        //Credentials
        $data['entry_username']              = $this->language->get('entry_username');
        $data['entry_password']              = $this->language->get('entry_password');
        $data['entry_clientno']              = $this->language->get('entry_clientno');
        $data['entry_product']               = $this->language->get('entry_product');
        $data['entry_product_text']          = $this->language->get('entry_product_text');

        $data['entry_sweden']                = $this->language->get('entry_sweden');
        $data['entry_finland']               = $this->language->get('entry_finland');
        $data['entry_denmark']               = $this->language->get('entry_denmark');
        $data['entry_norway']                = $this->language->get('entry_norway');
        $data['entry_germany']               = $this->language->get('entry_germany');
        $data['entry_netherlands']           = $this->language->get('entry_netherlands');

        $data['entry_testmode']              = $this->language->get('entry_testmode');
        $data['entry_auto_deliver']          = $this->language->get('entry_auto_deliver');
        $data['entry_auto_deliver_text']     = $this->language->get('entry_auto_deliver_text');
        $data['entry_show_peppol_field']     = $this->language->get('entry_show_peppol_field');
        $data['entry_distribution_type']     = $this->language->get('entry_distribution_type');
        $data['entry_post']                  = $this->language->get('entry_post');
        $data['entry_email']                 = $this->language->get('entry_email');
        $data['entry_yes']                   = $this->language->get('entry_yes');
        $data['entry_no']                    = $this->language->get('entry_no');
        $data['entry_min_amount']            = $this->language->get('entry_min_amount');

        $data['entry_hide_svea_comments']         = $this->language->get('entry_hide_svea_comments');
        $data['entry_hide_svea_comments_tooltip'] = $this->language->get('entry_hide_svea_comments_tooltip');

        // Order statuses
        $data['entry_deliver_status']               = $this->language->get('entry_deliver_status');
        $data['entry_deliver_status_tooltip']       = $this->language->get('entry_deliver_status_tooltip');
        $data['entry_cancel_credit_status']         = $this->language->get('entry_cancel_credit_status');
        $data['entry_cancel_credit_status_tooltip'] = $this->language->get('entry_cancel_credit_status_tooltip');

        $data['version'] = floatval(VERSION);

        $cred = array();
        $cred[] = array("lang" => "SE", "value_username" => $this->config->get($this->paymentString . 'svea_invoice_username_SE'), "name_username" => $this->paymentString . 'svea_invoice_username_SE', "value_password" => $this->config->get($this->paymentString . 'svea_invoice_password_SE'), "name_password" => $this->paymentString . 'svea_invoice_password_SE', "value_clientno" => $this->config->get($this->paymentString . 'svea_invoice_clientno_SE'), "name_clientno" => $this->paymentString . 'svea_invoice_clientno_SE', "value_testmode" => $this->config->get($this->paymentString . 'svea_invoice_testmode_SE'), "name_testmode" => $this->paymentString . 'svea_invoice_testmode_SE');
        $cred[] = array("lang" => "NO", "value_username" => $this->config->get($this->paymentString . 'svea_invoice_username_NO'), "name_username" => $this->paymentString . 'svea_invoice_username_NO', "value_password" => $this->config->get($this->paymentString . 'svea_invoice_password_NO'), "name_password" => $this->paymentString . 'svea_invoice_password_NO', "value_clientno" => $this->config->get($this->paymentString . 'svea_invoice_clientno_NO'), "name_clientno" => $this->paymentString . 'svea_invoice_clientno_NO', "value_testmode" => $this->config->get($this->paymentString . 'svea_invoice_testmode_NO'), "name_testmode" => $this->paymentString . 'svea_invoice_testmode_NO');
        $cred[] = array("lang" => "FI", "value_username" => $this->config->get($this->paymentString . 'svea_invoice_username_FI'), "name_username" => $this->paymentString . 'svea_invoice_username_FI', "value_password" => $this->config->get($this->paymentString . 'svea_invoice_password_FI'), "name_password" => $this->paymentString . 'svea_invoice_password_FI', "value_clientno" => $this->config->get($this->paymentString . 'svea_invoice_clientno_FI'), "name_clientno" => $this->paymentString . 'svea_invoice_clientno_FI', "value_testmode" => $this->config->get($this->paymentString . 'svea_invoice_testmode_FI'), "name_testmode" => $this->paymentString . 'svea_invoice_testmode_FI');
        $cred[] = array("lang" => "DK", "value_username" => $this->config->get($this->paymentString . 'svea_invoice_username_DK'), "name_username" => $this->paymentString . 'svea_invoice_username_DK', "value_password" => $this->config->get($this->paymentString . 'svea_invoice_password_DK'), "name_password" => $this->paymentString . 'svea_invoice_password_DK', "value_clientno" => $this->config->get($this->paymentString . 'svea_invoice_clientno_DK'), "name_clientno" => $this->paymentString . 'svea_invoice_clientno_DK', "value_testmode" => $this->config->get($this->paymentString . 'svea_invoice_testmode_DK'), "name_testmode" => $this->paymentString . 'svea_invoice_testmode_DK');
        $cred[] = array("lang" => "NL", "value_username" => $this->config->get($this->paymentString . 'svea_invoice_username_NL'), "name_username" => $this->paymentString . 'svea_invoice_username_NL', "value_password" => $this->config->get($this->paymentString . 'svea_invoice_password_NL'), "name_password" => $this->paymentString . 'svea_invoice_password_NL', "value_clientno" => $this->config->get($this->paymentString . 'svea_invoice_clientno_NL'), "name_clientno" => $this->paymentString . 'svea_invoice_clientno_NL', "value_testmode" => $this->config->get($this->paymentString . 'svea_invoice_testmode_NL'), "name_testmode" => $this->paymentString . 'svea_invoice_testmode_NL');
        $cred[] = array("lang" => "DE", "value_username" => $this->config->get($this->paymentString . 'svea_invoice_username_DE'), "name_username" => $this->paymentString . 'svea_invoice_username_DE', "value_password" => $this->config->get($this->paymentString . 'svea_invoice_password_DE'), "name_password" => $this->paymentString . 'svea_invoice_password_DE', "value_clientno" => $this->config->get($this->paymentString . 'svea_invoice_clientno_DE'), "name_clientno" => $this->paymentString . 'svea_invoice_clientno_DE', "value_testmode" => $this->config->get($this->paymentString . 'svea_invoice_testmode_DE'), "name_testmode" => $this->paymentString . 'svea_invoice_testmode_DE');

        $data['credentials'] = $cred;

        $data[$this->paymentString . 'svea_invoice_sort_order'] = $this->config->get($this->paymentString . 'svea_invoice_sort_order');
        $data[$this->paymentString . 'svea_invoice_auto_deliver'] = $this->config->get($this->paymentString . 'svea_invoice_auto_deliver');

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', $this->userTokenString . 'token=' . $this->session->data[$this->userTokenString . 'token'], true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link($this->linkString, $this->userTokenString . 'token=' . $this->session->data[$this->userTokenString . 'token'] . '&type=payment', true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/payment/svea_invoice', $this->userTokenString . 'token=' . $this->session->data[$this->userTokenString . 'token'], true)
        );

        $data['action'] = $this->url->link('extension/payment/svea_invoice', $this->userTokenString . 'token=' . $this->session->data[$this->userTokenString . 'token'], true);
        $data['cancel'] = $this->url->link($this->linkString, $this->userTokenString . 'token=' . $this->session->data[$this->userTokenString . 'token'] . '&type=payment', true);

        // Invoice distribution type
        if (isset($this->request->post[$this->paymentString . 'svea_invoice_distribution_type'])) {
            $data[$this->paymentString . 'svea_invoice_distribution_type'] = $this->request->post[$this->paymentString . 'svea_invoice_distribution_type'];
        } else {
            $data[$this->paymentString . 'svea_invoice_distribution_type'] = $this->config->get($this->paymentString . 'svea_invoice_distribution_type');
        }

        // Shipping billing
        if (isset($this->request->post[$this->paymentString . 'svea_invoice_shipping_billing'])) {
            $data[$this->paymentString . 'svea_invoice_shipping_billing'] = $this->request->post[$this->paymentString . 'svea_invoice_shipping_billing'];
        } else {
            $data[$this->paymentString . 'svea_invoice_shipping_billing'] = $this->config->get($this->paymentString . 'svea_invoice_shipping_billing');
        }

        // Show price on product
        if (isset($this->request->post[$this->paymentString . 'svea_invoice_product_price'])) {
            $data[$this->paymentString . 'svea_invoice_product_price'] = $this->request->post[$this->paymentString . 'svea_invoice_product_price'];
        } else {
            $data[$this->paymentString . 'svea_invoice_product_price'] = $this->config->get($this->paymentString . 'svea_invoice_product_price');
        }

        // Show peppol field
        if (isset($this->request->post[$this->paymentString . 'svea_invoice_peppol'])) {
            $data[$this->paymentString . 'svea_invoice_peppol'] = $this->request->post[$this->paymentString . 'svea_invoice_peppol'];
        } else {
            $data[$this->paymentString . 'svea_invoice_peppol'] = $this->config->get($this->paymentString . 'svea_invoice_peppol');
        }

        // Geo zone
        if (isset($this->request->post[$this->paymentString . 'svea_invoice_geo_zone_id'])) {
            $data[$this->paymentString . 'svea_invoice_geo_zone_id'] = $this->request->post[$this->paymentString . 'svea_invoice_geo_zone_id'];
        } else {
            $data[$this->paymentString . 'svea_invoice_geo_zone_id'] = $this->config->get($this->paymentString . 'svea_invoice_geo_zone_id');
        }

        $this->load->model('localisation/geo_zone');

        $data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

        // Invoice status
        if (isset($this->request->post[$this->paymentString . 'svea_invoice_status'])) {
            $data[$this->paymentString . 'svea_invoice_status'] = $this->request->post[$this->paymentString . 'svea_invoice_status'];
        } else {
            $data[$this->paymentString . 'svea_invoice_status'] = $this->config->get($this->paymentString . 'svea_invoice_status');
        }

        // Sort order
        if (isset($this->request->post[$this->paymentString . 'svea_invoice_sort_order'])) {
            $data[$this->paymentString . 'svea_invoice_sort_order'] = $this->request->post[$this->paymentString . 'svea_invoice_sort_order'];
        } else {
            $data[$this->paymentString . 'svea_invoice_sort_order'] = $this->config->get($this->paymentString . 'svea_invoice_sort_order');
        }

        // Payment info
        if (isset($this->request->post[$this->paymentString . 'svea_invoice_payment_description'])) {
            $data[$this->paymentString . 'svea_invoice_payment_description'] = $this->request->post[$this->paymentString . 'svea_invoice_payment_description'];
        } else {
            $data[$this->paymentString . 'svea_invoice_payment_description'] = $this->config->get($this->paymentString . 'svea_invoice_payment_description');
        }

        // Auto deliver
        if (isset($this->request->post[$this->paymentString . 'svea_invoice_auto_deliver'])) {
            $data[$this->paymentString . 'svea_invoice_auto_deliver'] = $this->request->post[$this->paymentString . 'svea_invoice_auto_deliver'];
        } else {
            $data[$this->paymentString . 'svea_invoice_auto_deliver'] = $this->config->get($this->paymentString . 'svea_invoice_auto_deliver');
        }

        // Distribution type
        if (isset($this->request->post[$this->paymentString . 'svea_invoice_distribution_type'])) {
            $data[$this->paymentString . 'svea_invoice_distribution_type'] = $this->request->post[$this->paymentString . 'svea_invoice_distribution_type'];
        } else {
            $data[$this->paymentString . 'svea_invoice_distribution_type'] = $this->config->get($this->paymentString . 'svea_invoice_distribution_type');
        }

        // Hide svea comments
        if (isset($this->request->post[$this->paymentString . 'svea_invoice_hide_svea_comments'])) {
            $data[$this->paymentString . 'svea_invoice_hide_svea_comments'] = $this->request->post[$this->paymentString . 'svea_invoice_hide_svea_comments'];
        } else {
            $data[$this->paymentString . 'svea_invoice_hide_svea_comments'] = $this->config->get($this->paymentString . 'svea_invoice_hide_svea_comments');
        }

        if (isset($this->request->post[$this->paymentString . 'svea_invoice_deliver_status'])) {
            $data[$this->paymentString . 'svea_invoice_deliver_status'] = $this->request->post[$this->paymentString . 'svea_invoice_deliver_status'];
        } else {
            $data[$this->paymentString . 'svea_invoice_deliver_status'] = $this->config->get($this->paymentString . 'svea_invoice_deliver_status');
        }

        if (isset($this->request->post[$this->paymentString . 'svea_invoice_cancel_credit_status'])) {
            $data[$this->paymentString . 'svea_invoice_cancel_credit_status'] = $this->request->post[$this->paymentString . 'svea_invoice_cancel_credit_status'];
        } else {
            $data[$this->paymentString . 'svea_invoice_cancel_credit_status'] = $this->config->get($this->paymentString . 'svea_invoice_cancel_credit_status');
        }

        if ($data[$this->paymentString . 'svea_invoice_deliver_status'] == null) {
            $data[$this->paymentString . 'svea_invoice_deliver_status'] = array();
        }

        if ($data[$this->paymentString . 'svea_invoice_cancel_credit_status'] == null) {
            $data[$this->paymentString . 'svea_invoice_cancel_credit_status'] = array();
        }

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $data['version'] = VERSION;

        // Add order statuses
        $this->load->model('localisation/order_status');
        $data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

        $this->response->setOutput($this->load->view('extension/payment/svea_invoice', $data));
    }

    private function validate()
    {
        $this->setVersionStrings();

        if (!$this->user->hasPermission('modify', 'extension/payment/svea_invoice')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        // Order status list validation
        if (!isset($this->request->post[$this->paymentString . 'svea_invoice_deliver_status']) || count($this->request->post[$this->paymentString . 'svea_invoice_deliver_status']) == 0) {
            $this->error['warning'] = $this->language->get('error_validation_deliver_status_empty');
        }

        if (!isset($this->request->post[$this->paymentString . 'svea_invoice_cancel_credit_status']) || count($this->request->post[$this->paymentString . 'svea_invoice_cancel_credit_status']) == 0) {
            $this->error['warning'] = $this->language->get('error_validation_cancel_credit_status_empty');
        }

        $sharedStatuses = array();

        if (isset($this->request->post[$this->paymentString . 'svea_invoice_deliver_status']) && isset($this->request->post[$this->paymentString . 'svea_invoice_cancel_credit_status'])) {
            foreach ($this->request->post[$this->paymentString . 'svea_invoice_deliver_status'] as $deliverStatus) {
                foreach ($this->request->post[$this->paymentString . 'svea_invoice_cancel_credit_status'] as $key => $cancelCreditStatus) {
                    if ($deliverStatus == $cancelCreditStatus) {
                        array_push($sharedStatuses, $cancelCreditStatus);
                        break;
                    }
                }
            }
        }

        if (count($sharedStatuses) != 0) {
            $this->load->model('localisation/order_status');
            $sharedStatusesString = "";

            foreach ($sharedStatuses as $sharedStatus) {
                $sharedStatusesString = $sharedStatusesString . $this->model_localisation_order_status->getOrderStatus($sharedStatus)['name'] . ", ";
            }

            $sharedStatusesString = substr($sharedStatusesString, 0, strlen($sharedStatusesString)-2);
            $this->error['warning'] = $this->language->get('error_validation_shared_status') . $sharedStatusesString;
        }

        if (!$this->error) {
            return true;
        } else {
            return false;
        }
    }

    public function install()
    {
        $this->load->model('setting/setting');

        $this->setVersionStrings();

        $this->model_setting_setting->editSetting($this->paymentString . 'svea_invoice', array($this->paymentString . 'svea_invoice_status' => 1));

        if (VERSION < 3.0) {
            if ($this->model_extension_event->getEvent($this->moduleString . "sco_add_history_order_from_admin" . $this->appendString, "catalog/controller/api/order/history/before", "extension/svea/order/history") == null) {
                $this->model_extension_event->addEvent($this->moduleString . "sco_add_history_order_from_admin" . $this->appendString, "catalog/controller/api/order/history/before", "extension/svea/order/history");
            }
        } else {
            if ($this->model_setting_event->getEventByCode($this->moduleString . "sco_add_history_order_from_admin" . $this->appendString) == null) {
                $this->model_setting_event->addEvent($this->moduleString . "sco_add_history_order_from_admin" . $this->appendString, "catalog/controller/api/order/history/before", "extension/svea/order/history");
            }
        }
    }

    public function uninstall()
    {
        $this->load->model('setting/setting');
        $this->setVersionStrings();
        $this->model_setting_setting->editSetting($this->paymentString . 'svea_invoice', array($this->paymentString . 'svea_invoice_status' => 0));
    }

    public function setVersionStrings()
    {
        if (VERSION < 3.0) {
            $this->userTokenString = "";
            $this->linkString = "extension/extension";
            $this->paymentString = "";
            $this->moduleString = "";
            $this->appendString = "";
        }
    }
}
