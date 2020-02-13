<?php

require_once(DIR_APPLICATION . '../svea/config/configInclude.php');

class ControllerExtensionPaymentSveacard extends Controller
{
    private $error = array();

    private $userTokenString = "user_";
    private $linkString = "marketplace/extension";
    private $paymentString ="payment_";
    private $moduleString = "module_";
    private $appendString = "_before";

    public function index()
    {
        $this->setVersionStrings();
        $this->load->language('extension/payment/svea_card');
        $this->load->language('extension/payment/svea_shared');
        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('setting/setting');
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && ($this->validate())) {

            //Remove whitespace  from input, outcommented cause do not filter correct
            $inputArray = array();
            foreach ($this->request->post as $k => $i) {
                $inputArray[$k] = $i;//($k == $this->paymentString . 'svea_card_sw_test' || $this->paymentString . 'svea_card_sw_prod') ? str_replace(" ","",$i) : $i;
            }

            //Save all settings
            $this->model_setting_setting->editSetting($this->paymentString . 'svea_card', $inputArray);

            $this->session->data['success'] = $this->language->get('text_success');

            $this->response->redirect($this->url->link($this->linkString, $this->userTokenString . 'token=' . $this->session->data[$this->userTokenString . 'token'] . '&type=payment', true));
        }

        $data['entry_version_text'] = $this->language->get('entry_version_text');
        $data['entry_version'] = $this->language->get('entry_version');
        $data['entry_version_info'] = $this->language->get('entry_version_info');
        $data['entry_module_repo'] = $this->language->get('entry_module_repo');

        $data['heading_title'] = $this->language->get('heading_title');
        $data['text_enabled'] = $this->language->get('text_enabled');
        $data['text_disabled'] = $this->language->get('text_disabled');
        $data['text_all_zones'] = $this->language->get('text_all_zones');
        $data['entry_geo_zone'] = $this->language->get('entry_geo_zone');
        $data['entry_status'] = $this->language->get('entry_status');
        $data['entry_sort_order'] = $this->language->get('entry_sort_order');
        $data['entry_payment_description'] = $this->language->get('entry_payment_description');
        $data['entry_card_logos'] = $this->language->get('entry_card_logos');

        $data['button_save'] = $this->language->get('button_save');
        $data['button_cancel'] = $this->language->get('button_cancel');

        $data['tab_general'] = $this->language->get('tab_general');

        //Credentials
        $data['entry_test'] = $this->language->get('entry_test');
        $data['entry_prod'] = $this->language->get('entry_prod');

        $data['entry_auto_deliver'] = $this->language->get('entry_auto_deliver');
        $data['entry_auto_deliver_description'] = $this->language->get('entry_auto_deliver_description');

        $data['entry_hide_svea_comments'] = $this->language->get('entry_hide_svea_comments');
        $data['entry_hide_svea_comments_tooltip'] = $this->language->get('entry_hide_svea_comments_tooltip');

        //Definitions lang
        $data['entry_testmode'] = $this->language->get('entry_testmode');
        $data['entry_merchant_id'] = $this->language->get('entry_merchant_id');
        $data['entry_sw'] = $this->language->get('entry_sw');
        $data['entry_merchant_id'] = $this->language->get('entry_merchant_id');
        $data['entry_sw'] = $this->language->get('entry_sw');

        //Definitions settings
        $data[$this->paymentString . 'svea_card_sort_order'] = $this->config->get($this->paymentString . 'svea_card_sort_order');
        $data[$this->paymentString . 'svea_card_testmode'] = $this->config->get($this->paymentString . 'svea_card_testmode');

        $data['version'] = floatval(VERSION);

        $data['value_merchant_test'] = $this->config->get($this->paymentString . 'svea_card_merchant_id_test');
        $data['value_sw_test'] = $this->config->get($this->paymentString . 'svea_card_sw_test');
        $data['value_merchant_prod'] = $this->config->get($this->paymentString . 'svea_card_merchant_id_prod');
        $data['value_sw_prod'] = $this->config->get($this->paymentString . 'svea_card_sw_prod');

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        if (isset($this->request->post[$this->paymentString . 'svea_card_logos'])) {
            $data[$this->paymentString . 'svea_card_logos'] = $this->request->post[$this->paymentString . 'svea_card_logos'];
        } elseif ($this->config->get($this->paymentString . 'svea_card_logos')) {
            $data[$this->paymentString . 'svea_card_logos'] = $this->config->get($this->paymentString . 'svea_card_logos');
        } else {
            $data[$this->paymentString . 'svea_card_logos'] = array();
        }
        $data['card_logos'] = array(
            'MASTERCARD.png' => 'view/image/payment/svea_direct/MASTERCARD.png',
            'VISA.png' => 'view/image/payment/svea_direct/VISA.png'
        );

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
            'href' => $this->url->link('extension/payment/svea_card', $this->userTokenString . 'token=' . $this->session->data[$this->userTokenString . 'token'], true)
        );

        $data['action'] = $this->url->link('extension/payment/svea_card', $this->userTokenString . 'token=' . $this->session->data[$this->userTokenString . 'token'], true);
        $data['cancel'] = $this->url->link($this->linkString, $this->userTokenString . 'token=' . $this->session->data[$this->userTokenString . 'token'] . '&type=payment', true);

        if (isset($this->request->post[$this->paymentString . 'svea_card_geo_zone_id'])) {
            $data[$this->paymentString . 'svea_card_geo_zone_id'] = $this->request->post[$this->paymentString . 'svea_card_geo_zone_id'];
        } else {
            $data[$this->paymentString . 'svea_card_geo_zone_id'] = $this->config->get($this->paymentString . 'svea_card_geo_zone_id');
        }

        $this->load->model('localisation/geo_zone');

        $data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

        if (isset($this->request->post[$this->paymentString . 'svea_card_status'])) {
            $data[$this->paymentString . 'svea_card_status'] = $this->request->post[$this->paymentString . 'svea_card_status'];
        } else {
            $data[$this->paymentString . 'svea_card_status'] = $this->config->get($this->paymentString . 'svea_card_status');
        }

        if (isset($this->request->post[$this->paymentString . 'svea_card_sort_order'])) {
            $data[$this->paymentString . 'svea_card_sort_order'] = $this->request->post[$this->paymentString . 'svea_card_sort_order'];
        } else {
            $data[$this->paymentString . 'svea_card_sort_order'] = $this->config->get($this->paymentString . 'svea_card_sort_order');
        }
        //payment info
        if (isset($this->request->post[$this->paymentString . 'svea_card_payment_description'])) {
            $data[$this->paymentString . 'svea_card_payment_description'] = $this->request->post[$this->paymentString . 'svea_card_payment_description'];
        } else {
            $data[$this->paymentString . 'svea_card_payment_description'] = $this->config->get($this->paymentString . 'svea_card_payment_description');
        }

        if (isset($this->request->post[$this->paymentString . 'svea_card_testmode'])) {
            $data[$this->paymentString . 'svea_card_testmode'] = $this->request->post[$this->paymentString . 'svea_card_testmode'];
        } else {
            $data[$this->paymentString . 'svea_card_testmode'] = $this->config->get($this->paymentString . 'svea_card_testmode');
        }

        if (isset($this->request->post[$this->paymentString . 'svea_card_auto_deliver'])) {
            $data[$this->paymentString . 'svea_card_auto_deliver'] = $this->request->post[$this->paymentString . 'svea_card_auto_deliver'];
        } else {
            $data[$this->paymentString . 'svea_card_auto_deliver'] = $this->config->get($this->paymentString . 'svea_card_auto_deliver');
        }

        if (isset($this->request->post[$this->paymentString . 'svea_card_hide_svea_comments'])) {
            $data[$this->paymentString . 'svea_card_hide_svea_comments'] = $this->request->post[$this->paymentString . 'svea_card_hide_svea_comments'];
        } else {
            $data[$this->paymentString . 'svea_card_hide_svea_comments'] = $this->config->get($this->paymentString . 'svea_card_hide_svea_comments');
        }

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $data['version'] = VERSION;

        $this->response->setOutput($this->load->view('extension/payment/svea_card', $data));
    }

    private function validate()
    {
        if (!$this->user->hasPermission('modify', 'extension/payment/svea_card')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if (!$this->error) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    public function install()
    {
        $this->setVersionStrings();
        $this->load->model('setting/setting');
        $this->model_setting_setting->editSetting($this->paymentString . 'svea_card', array($this->paymentString . 'svea_card_status' => 1));
        if (VERSION < 3.0) {
            if ($this->model_extension_event->getEvent($this->moduleString . "sco_add_history_order_from_admin" . $this->appendString, "catalog/controller/api/order/history/before", "extension/svea/order/history") == NULL) {
                $this->model_extension_event->addEvent($this->moduleString . "sco_add_history_order_from_admin" . $this->appendString, "catalog/controller/api/order/history/before", "extension/svea/order/history");
            }
        } else {
            if ($this->model_setting_event->getEventByCode($this->moduleString . "sco_add_history_order_from_admin" . $this->appendString) == NULL) {
                $this->model_setting_event->addEvent($this->moduleString . "sco_add_history_order_from_admin" . $this->appendString, "catalog/controller/api/order/history/before", "extension/svea/order/history");
            }
        }
    }

    public function uninstall()
    {
        $this->load->model('setting/setting');
        $this->model_setting_setting->editSetting($this->paymentString . 'svea_card', array($this->paymentString . 'svea_card_status' => 0));
    }

    public function setVersionStrings()
    {
        if(VERSION < 3.0)
        {
            $this->userTokenString = "";
            $this->linkString = "extension/extension";
            $this->paymentString = "";
            $this->moduleString = "";
            $this->appendString = "";
        }
    }
}
