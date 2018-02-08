<?php

class ControllerExtensionPaymentSveacard extends Controller
{
    protected $svea_version = '4.2.0';
    private $error = array();

    public function index()
    {
        $this->load->language('extension/payment/svea_card');
        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('setting/setting');
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && ($this->validate())) {

            //Remove whitespace  from input, outcommented cause do not filter correct
            $inputArray = array();
            foreach ($this->request->post as $k => $i) {
                $inputArray[$k] = $i;//($k == 'payment_svea_card_sw_test' || 'payment_svea_card_sw_prod') ? str_replace(" ","",$i) : $i;
            }

            //Save all settings
            $this->model_setting_setting->editSetting('payment_svea_card', $inputArray);

            $this->session->data['success'] = $this->language->get('text_success');

            $this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true));
        }

        $data['payment_svea_version_text'] = $this->getSveaVersion();
        $data['payment_svea_version'] = $this->svea_version;
        $data['heading_title'] = $this->language->get('heading_title');
        $data['text_enabled'] = $this->language->get('text_enabled');
        $data['text_disabled'] = $this->language->get('text_disabled');
        $data['text_all_zones'] = $this->language->get('text_all_zones');

        $data['entry_order_status'] = $this->language->get('entry_order_status');
        $data['entry_status_order'] = $this->language->get('entry_status_order');
        $data['entry_status_canceled'] = $this->language->get('entry_status_canceled');
        $data['entry_status_canceled_text'] = $this->language->get('entry_status_canceled_text');
        $data['entry_status_confirmed'] = $this->language->get('entry_status_confirmed');
        $data['entry_status_confirmed_text'] = $this->language->get('entry_status_confirmed_text');
        $data['entry_status_refunded'] = $this->language->get('entry_status_refunded');
        $data['entry_status_refunded_text'] = $this->language->get('entry_status_refunded_text');
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

        //Definitions lang
        $data['entry_testmode'] = $this->language->get('entry_testmode');
        $data['entry_merchant_id'] = $this->language->get('entry_merchant_id');
        $data['entry_sw'] = $this->language->get('entry_sw');
        $data['entry_merchant_id'] = $this->language->get('entry_merchant_id');
        $data['entry_sw'] = $this->language->get('entry_sw');

        //Definitions settings
        $data['payment_svea_card_sort_order'] = $this->config->get('payment_svea_card_sort_order');
        $data['payment_svea_card_testmode'] = $this->config->get('payment_svea_card_testmode');

        $data['version'] = floatval(VERSION);

        $data['value_merchant_test'] = $this->config->get('payment_svea_card_merchant_id_test');
        $data['value_sw_test'] = $this->config->get('payment_svea_card_sw_test');
        $data['value_merchant_prod'] = $this->config->get('payment_svea_card_merchant_id_prod');
        $data['value_sw_prod'] = $this->config->get('payment_svea_card_sw_prod');

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        if (isset($this->request->post['payment_svea_card_logos'])) {
            $data['payment_svea_card_logos'] = $this->request->post['payment_svea_card_logos'];
        } elseif ($this->config->get('payment_svea_card_logos')) {
            $data['payment_svea_card_logos'] = $this->config->get('payment_svea_card_logos');
        } else {
            $data['payment_svea_card_logos'] = array();
        }
        $data['card_logos'] = array(
            'MASTERCARD.png' => 'view/image/payment/svea_direct/MASTERCARD.png',
            'VISA.png' => 'view/image/payment/svea_direct/VISA.png'
        );

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/payment/svea_card', 'user_token=' . $this->session->data['user_token'], true)
        );

        $data['action'] = $this->url->link('extension/payment/svea_card', 'user_token=' . $this->session->data['user_token'], true);
        $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true);

        //statuses
        $this->load->model('localisation/order_status');
        $data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();
        if (isset($this->request->post['payment_svea_card_order_status_id'])) {
            $data['payment_svea_card_order_status_id'] = $this->request->post['payment_svea_card_order_status_id'];
        } else {
            $data['payment_svea_card_order_status_id'] = $this->config->get('payment_svea_card_order_status_id');
        }
        if (isset($this->request->post['payment_svea_card_canceled_status_id'])) {
            $data['payment_svea_card_canceled_status_id'] = $this->request->post['payment_svea_card_canceled_status_id'];
        } else {
            $data['payment_svea_card_canceled_status_id'] = $this->config->get('payment_svea_card_canceled_status_id');
        }
        if (isset($this->request->post['payment_svea_card_deliver_status_id'])) {
            $data['payment_svea_card_deliver_status_id'] = $this->request->post['payment_svea_card_deliver_status_id'];
        } else {
            $data['payment_svea_card_deliver_status_id'] = $this->config->get('payment_svea_card_deliver_status_id');
        }
        if (isset($this->request->post['payment_svea_card_refunded_status_id'])) {
            $data['payment_svea_card_refunded_status_id'] = $this->request->post['payment_svea_card_refunded_status_id'];
        } else {
            $data['payment_svea_card_refunded_status_id'] = $this->config->get('payment_svea_card_refunded_status_id');
        }

        if (isset($this->request->post['payment_svea_card_geo_zone_id'])) {
            $data['payment_svea_card_geo_zone_id'] = $this->request->post['payment_svea_card_geo_zone_id'];
        } else {
            $data['payment_svea_card_geo_zone_id'] = $this->config->get('payment_svea_card_geo_zone_id');
        }

        $this->load->model('localisation/geo_zone');

        $data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

        if (isset($this->request->post['payment_svea_card_status'])) {
            $data['payment_svea_card_status'] = $this->request->post['payment_svea_card_status'];
        } else {
            $data['payment_svea_card_status'] = $this->config->get('payment_svea_card_status');
        }

        if (isset($this->request->post['payment_svea_card_sort_order'])) {
            $data['payment_svea_card_sort_order'] = $this->request->post['payment_svea_card_sort_order'];
        } else {
            $data['payment_svea_card_sort_order'] = $this->config->get('payment_svea_card_sort_order');
        }
        //payment info
        if (isset($this->request->post['payment_svea_card_payment_description'])) {
            $data['payment_svea_card_payment_description'] = $this->request->post['payment_svea_card_payment_description'];
        } else {
            $data['payment_svea_card_payment_description'] = $this->config->get('payment_svea_card_payment_description');
        }

        if (isset($this->request->post['payment_svea_card_testmode'])) {
            $data['payment_svea_card_testmode'] = $this->request->post['payment_svea_card_testmode'];
        } else {
            $data['payment_svea_card_testmode'] = $this->config->get('payment_svea_card_testmode');
        }

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

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

    protected function getSveaVersion()
    {
        $update_url = "https://github.com/sveawebpay/opencart-module/archive/master.zip";
        $docs_url = "https://github.com/sveawebpay/opencart-module/releases";
        $url = "https://raw.githubusercontent.com/sveawebpay/opencart-module/master/docs/info.json";
        $json = file_get_contents($url);
        $data = json_decode($json);

        if ($data->module_version > $this->svea_version) {
            return "You have the latest " . $this->svea_version . " version.";
        } else {
            return $this->svea_version . '<br />
                There is a new version available.<br />
                <a href="' . $docs_url . '" title="Go to release notes on github">View version details</a> or <br />
                <a title="Download zip" href="' . $update_url . '"><img width="67" src="view/image/download.png"></a>';

        }

    }

    public function install()
    {
        $this->load->model('setting/setting');
        $this->model_setting_setting->editSetting('payment_svea_card', array('payment_svea_card_status' => 1));
    }

    public function uninstall()
    {
        $this->load->model('setting/setting');
        $this->model_setting_setting->editSetting('payment_svea_card', array('payment_svea_card_status' => 0));
    }
}
