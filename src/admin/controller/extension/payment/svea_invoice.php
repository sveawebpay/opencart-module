<?php

class ControllerExtensionPaymentSveaInvoice extends Controller
{
    protected $svea_version = '4.3.3';
    private $error = array();

    public function index()
    {
        $this->load->language('extension/payment/svea_invoice');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('setting/setting');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && ($this->validate())) {
            $this->model_setting_setting->editSetting('payment_svea_invoice', $this->request->post);

            $this->session->data['success'] = $this->language->get('text_success');

            $this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true));
        }

        $data['payment_svea_version_text'] = $this->getSveaVersion();
        $data['payment_svea_version'] = $this->svea_version;

        $data['heading_title'] = $this->language->get('heading_title');

        $data['text_enabled'] = $this->language->get('text_enabled');
        $data['text_disabled'] = $this->language->get('text_disabled');
        $data['text_all_zones'] = $this->language->get('text_all_zones');

        $data['entry_shipping_billing'] = $this->language->get('entry_shipping_billing');
        $data['entry_shipping_billing_text'] = $this->language->get('entry_shipping_billing_text');
        $data['entry_geo_zone'] = $this->language->get('entry_geo_zone');
        $data['entry_status'] = $this->language->get('entry_status');
        $data['entry_sort_order'] = $this->language->get('entry_sort_order');
        $data['entry_payment_description'] = $this->language->get('entry_payment_description');
        $data['button_save'] = $this->language->get('button_save');
        $data['button_cancel'] = $this->language->get('button_cancel');
        $data['tab_general'] = $this->language->get('tab_general');
        //Credentials
        $data['entry_username'] = $this->language->get('entry_username');
        $data['entry_password'] = $this->language->get('entry_password');
        $data['entry_clientno'] = $this->language->get('entry_clientno');
        $data['entry_product'] = $this->language->get('entry_product');
        $data['entry_product_text'] = $this->language->get('entry_product_text');

        $data['entry_sweden'] = $this->language->get('entry_sweden');
        $data['entry_finland'] = $this->language->get('entry_finland');
        $data['entry_denmark'] = $this->language->get('entry_denmark');
        $data['entry_norway'] = $this->language->get('entry_norway');
        $data['entry_germany'] = $this->language->get('entry_germany');
        $data['entry_netherlands'] = $this->language->get('entry_netherlands');

        $data['entry_testmode'] = $this->language->get('entry_testmode');
        $data['entry_auto_deliver'] = $this->language->get('entry_auto_deliver');
        $data['entry_auto_deliver_text'] = $this->language->get('entry_auto_deliver_text');
        $data['entry_distribution_type'] = $this->language->get('entry_distribution_type');
        $data['entry_post'] = $this->language->get('entry_post');
        $data['entry_email'] = $this->language->get('entry_email');
        $data['entry_yes'] = $this->language->get('entry_yes');
        $data['entry_no'] = $this->language->get('entry_no');
        $data['entry_min_amount'] = $this->language->get('entry_min_amount');

        $data['version'] = floatval(VERSION);

        $cred = array();
        $cred[] = array("lang" => "SE", "value_username" => $this->config->get('payment_svea_invoice_username_SE'), "name_username" => 'payment_svea_invoice_username_SE', "value_password" => $this->config->get('payment_svea_invoice_password_SE'), "name_password" => 'payment_svea_invoice_password_SE', "value_clientno" => $this->config->get('payment_svea_invoice_clientno_SE'), "name_clientno" => 'payment_svea_invoice_clientno_SE', "value_testmode" => $this->config->get('payment_svea_invoice_testmode_SE'), "name_testmode" => 'payment_svea_invoice_testmode_SE');
        $cred[] = array("lang" => "NO", "value_username" => $this->config->get('payment_svea_invoice_username_NO'), "name_username" => 'payment_svea_invoice_username_NO', "value_password" => $this->config->get('payment_svea_invoice_password_NO'), "name_password" => 'payment_svea_invoice_password_NO', "value_clientno" => $this->config->get('payment_svea_invoice_clientno_NO'), "name_clientno" => 'payment_svea_invoice_clientno_NO', "value_testmode" => $this->config->get('payment_svea_invoice_testmode_NO'), "name_testmode" => 'payment_svea_invoice_testmode_NO');
        $cred[] = array("lang" => "FI", "value_username" => $this->config->get('payment_svea_invoice_username_FI'), "name_username" => 'payment_svea_invoice_username_FI', "value_password" => $this->config->get('payment_svea_invoice_password_FI'), "name_password" => 'payment_svea_invoice_password_FI', "value_clientno" => $this->config->get('payment_svea_invoice_clientno_FI'), "name_clientno" => 'payment_svea_invoice_clientno_FI', "value_testmode" => $this->config->get('payment_svea_invoice_testmode_FI'), "name_testmode" => 'payment_svea_invoice_testmode_FI');
        $cred[] = array("lang" => "DK", "value_username" => $this->config->get('payment_svea_invoice_username_DK'), "name_username" => 'payment_svea_invoice_username_DK', "value_password" => $this->config->get('payment_svea_invoice_password_DK'), "name_password" => 'payment_svea_invoice_password_DK', "value_clientno" => $this->config->get('payment_svea_invoice_clientno_DK'), "name_clientno" => 'payment_svea_invoice_clientno_DK', "value_testmode" => $this->config->get('payment_svea_invoice_testmode_DK'), "name_testmode" => 'payment_svea_invoice_testmode_DK');
        $cred[] = array("lang" => "NL", "value_username" => $this->config->get('payment_svea_invoice_username_NL'), "name_username" => 'payment_svea_invoice_username_NL', "value_password" => $this->config->get('payment_svea_invoice_password_NL'), "name_password" => 'payment_svea_invoice_password_NL', "value_clientno" => $this->config->get('payment_svea_invoice_clientno_NL'), "name_clientno" => 'payment_svea_invoice_clientno_NL', "value_testmode" => $this->config->get('payment_svea_invoice_testmode_NL'), "name_testmode" => 'payment_svea_invoice_testmode_NL');
        $cred[] = array("lang" => "DE", "value_username" => $this->config->get('payment_svea_invoice_username_DE'), "name_username" => 'payment_svea_invoice_username_DE', "value_password" => $this->config->get('payment_svea_invoice_password_DE'), "name_password" => 'payment_svea_invoice_password_DE', "value_clientno" => $this->config->get('payment_svea_invoice_clientno_DE'), "name_clientno" => 'payment_svea_invoice_clientno_DE', "value_testmode" => $this->config->get('payment_svea_invoice_testmode_DE'), "name_testmode" => 'payment_svea_invoice_testmode_DE');

        $data['credentials'] = $cred;


        $data['payment_svea_invoice_sort_order'] = $this->config->get('payment_svea_invoice_sort_order');

        $data['payment_svea_invoice_auto_deliver'] = $this->config->get('payment_svea_invoice_auto_deliver');


        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

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
            'href' => $this->url->link('extension/payment/svea_invoice', 'user_token=' . $this->session->data['user_token'], true)
        );


        $data['action'] = $this->url->link('extension/payment/svea_invoice', 'user_token=' . $this->session->data['user_token'], true);
        $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true);

        //invoice distribution type
        if (isset($this->request->post['payment_svea_invoice_distribution_type'])) {
            $data['payment_svea_invoice_distribution_type'] = $this->request->post['payment_svea_invoice_distribution_type'];
        } else {
            $data['payment_svea_invoice_distribution_type'] = $this->config->get('payment_svea_invoice_distribution_type');
        }

        //shipping billing
        if (isset($this->request->post['payment_svea_invoice_shipping_billing'])) {
            $data['payment_svea_invoice_shipping_billing'] = $this->request->post['payment_svea_invoice_shipping_billing'];
        } else {
            $data['payment_svea_invoice_shipping_billing'] = $this->config->get('payment_svea_invoice_shipping_billing');
        }
        //show price on product
        /*if (isset($this->request->post['payment_svea_invoice_product_price'])) {
            $data['payment_svea_invoice_product_price'] = $this->request->post['payment_svea_invoice_product_price'];
        } else {
            $data['payment_svea_invoice_product_price'] = $this->config->get('payment_svea_invoice_product_price');
        }*/
        //geozone
        if (isset($this->request->post['payment_svea_invoice_geo_zone_id'])) {
            $data['payment_svea_invoice_geo_zone_id'] = $this->request->post['payment_svea_invoice_geo_zone_id'];
        } else {
            $data['payment_svea_invoice_geo_zone_id'] = $this->config->get('payment_svea_invoice_geo_zone_id');
        }


        $this->load->model('localisation/geo_zone');

        $data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

        //invoice status
        if (isset($this->request->post['payment_svea_invoice_status'])) {
            $data['payment_svea_invoice_status'] = $this->request->post['payment_svea_invoice_status'];
        } else {
            $data['payment_svea_invoice_status'] = $this->config->get('payment_svea_invoice_status');
        }

        //sort order
        if (isset($this->request->post['payment_svea_invoice_sort_order'])) {
            $data['payment_svea_invoice_sort_order'] = $this->request->post['payment_svea_invoice_sort_order'];
        } else {
            $data['payment_svea_invoice_sort_order'] = $this->config->get('payment_svea_invoice_sort_order');
        }
        //payment info
        if (isset($this->request->post['payment_svea_invoice_payment_description'])) {
            $data['payment_svea_invoice_payment_description'] = $this->request->post['payment_svea_invoice_payment_description'];
        } else {
            $data['payment_svea_invoice_payment_description'] = $this->config->get('payment_svea_invoice_payment_description');
        }

        //auto deliver
        if (isset($this->request->post['payment_svea_invoice_auto_deliver'])) {
            $data['payment_svea_invoice_auto_deliver'] = $this->request->post['payment_svea_invoice_auto_deliver'];
        } else {
            $data['payment_svea_invoice_auto_deliver'] = $this->config->get('payment_svea_invoice_auto_deliver');
        }

        //Distribution type
        if (isset($this->request->post['payment_svea_invoice_distribution_type'])) {
            $data['payment_svea_invoice_distribution_type'] = $this->request->post['payment_svea_invoice_distribution_type'];
        } else {
            $data['payment_svea_invoice_distribution_type'] = $this->config->get('payment_svea_invoice_distribution_type');
        }

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/payment/svea_invoice', $data));
    }

    private function validate()
    {
        if (!$this->user->hasPermission('modify', 'extension/payment/svea_invoice')) {
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
        $this->model_setting_setting->editSetting('payment_svea_invoice', array('payment_svea_invoice_status' => 1));
    }

    public function uninstall()
    {
        $this->load->model('setting/setting');
        $this->model_setting_setting->editSetting('payment_svea_invoice', array('payment_svea_invoice_status' => 0));
    }


}
