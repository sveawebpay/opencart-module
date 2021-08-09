<?php
class ControllerExtensionPaymentSveaCheckout extends Controller {
    private $error = array();

    public function index() {
        $this->load->language('extension/payment/svea_checkout');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('setting/setting');
        $this->load->model('extension/payment/svea_checkout');

        if ($this->request->server['REQUEST_METHOD'] == 'POST' && $this->validate()) {
            $this->model_setting_setting->editSetting('payment_svea_checkout', $this->request->post);

            $this->session->data['success'] = $this->language->get('text_success');

            $this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true));
        }

        $this->upgrade();

        $data['button_save'] = $this->language->get('button_save');
        $data['button_cancel'] = $this->language->get('button_cancel');

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
            'href' => $this->url->link('extension/payment/svea_checkout', 'user_token=' . $this->session->data['user_token'], true)
        );

        $data['action'] = $this->url->link('extension/payment/svea_checkout', 'user_token=' . $this->session->data['user_token'], 'SSL');
        $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', 'SSL');

        $fields = array(
            'default_country'      => null,
            'force_b2b'            => false,
            'test_mode'            => true,
            'status'               => true,

            'live_se_merchant_id'  => null,
            'live_se_secret_key'   => null,
            'test_se_merchant_id'  => null,
            'test_se_secret_key'   => null,
            'live_dk_merchant_id'  => null,
            'live_dk_secret_key'   => null,
            'test_dk_merchant_id'  => null,
            'test_dk_secret_key'   => null,
            'live_no_merchant_id'  => null,
            'live_no_secret_key'   => null,
            'test_no_merchant_id'  => null,
            'test_no_secret_key'   => null,
            'live_fi_merchant_id'  => null,
            'live_fi_secret_key'   => null,
            'test_fi_merchant_id'  => null,
            'test_fi_secret_key'   => null,
            'live_de_merchant_id'  => null,
            'live_de_secret_key'   => null,
            'test_de_merchant_id'  => null,
            'test_de_secret_key'   => null,

            'discount_status'      => false,
            'coupon_status'        => false,
            'comments_status'      => false,
            'newsletter_status'    => false,
            'allow_address_change' => false,
            'allow_guest'          => true,
            'allow_unverified'     => false,

            'widget_total'         => 0.0,
            'widget_status'        => false,

            'accept_status'        => true,
            'callback_status'      => true,
        );

        foreach ($fields as $field => $default) {
            if (isset($this->request->post['payment_svea_checkout_' . $field])) {
                $data['payment_svea_checkout_' . $field] = $this->request->post['payment_svea_checkout_' . $field];
            } elseif ($this->config->has('payment_svea_checkout_' . $field)) {
                $data['payment_svea_checkout_' . $field] = $this->config->get('payment_svea_checkout_' . $field);
            } else {
                $data['payment_svea_checkout_' . $field] = $default;
            }
        }

        $this->load->model('localisation/order_status');

        $data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

        $data['countries'] = [];
        $countries = $this->model_extension_payment_svea_checkout->findCountryByCode(['SE', 'DK', 'NO', 'FI', 'DE']);

        foreach ($countries as $country) {
            $data['countries'][] = [
                'id'   => $country['country_id'],
                'name' => $country['name'],
                'code' => mb_strtolower($country['iso_code_2']),
            ];
        }

        $data['current_version'] = $this->getCurrentVersion();
        $data['latest_version'] = $this->getLatestVersion();
        $data['download_url'] = 'https://github.com/sveawebpay/opencart-module/';
        $data['support_url'] = 'https://github.com/sveawebpay/opencart-module/';

        $data['user_token'] = $this->session->data['user_token'];

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/payment/svea_checkout', $data));
    }

    public function install() {
        $this->load->model('extension/payment/svea_checkout');
        $this->model_extension_payment_svea_checkout->install();
    }

    public function uninstall() {
        $this->load->model('extension/payment/svea_checkout');
        $this->model_extension_payment_svea_checkout->uninstall();
    }

    public function upgrade() {
        $this->load->model('extension/payment/svea_checkout');
        if ($this->model_extension_payment_svea_checkout->upgrade($this->getCurrentVersion())) {
            $this->response->redirect($this->url->link('extension/payment/svea_checkout', 'user_token=' . $this->session->data['user_token'], true));
        }
    }

    protected function validate() {
        if (!$this->user->hasPermission('modify', 'extension/payment/svea_checkout')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        return !$this->error;
    }

    protected function getCurrentVersion() {
        $json = json_decode(file_get_contents(DIR_APPLICATION . '../svea/version.json'), true);

        return $json['version'] ?? 'N/A';
    }

    protected function getLatestVersion() {
        $json = json_decode(file_get_contents('https://raw.githubusercontent.com/sveawebpay/opencart-module/master/src/svea/version.json'), true);

        return $json['version'] ?? 'N/A';
    }
}
