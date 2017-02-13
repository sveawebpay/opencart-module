<?php

require_once(DIR_APPLICATION . 'controller/payment/svea_common.php');
require_once(DIR_APPLICATION . '../svea/config/configInclude.php');

class ControllerSveaSuccess extends Controller
{

    public function index()
    {
        $sco_order_id = isset($this->session->data['sco_order_id']) ? $this->session->data['sco_order_id'] : null;

        unset($this->session->data['shipping_method']);
        unset($this->session->data['shipping_methods']);
        unset($this->session->data['payment_method']);
        unset($this->session->data['payment_methods']);
        unset($this->session->data['guest']);
        unset($this->session->data['comment']);
        unset($this->session->data['order_id']);
        unset($this->session->data['coupon']);
        unset($this->session->data['reward']);
        unset($this->session->data['voucher']);
        unset($this->session->data['vouchers']);
        unset($this->session->data['totals']);

        unset($this->session->data['sco_locale']);
        unset($this->session->data['sco_currency']);
        unset($this->session->data['order_id']);
        unset($this->session->data['sco_order_id']);
        unset($this->session->data['sco_cart_hash']);
        unset($this->session->data['sco_email']);
        unset($this->session->data['sco_postcode']);

        // Clear opencart Cart
        $this->cart->clear();

        $this->load->language('checkout/success');
        $this->load->language('svea/checkout');
        $this->load->model('setting/extension');

        $this->data = array();
        $this->data['title'] = $this->config->get('config_meta_title');

        $this->data['base'] = (isset($this->request->server['HTTPS']) && $this->request->server['HTTPS']) ? $this->config->get('config_ssl') : $this->config->get('config_url');
        $this->data['description'] = $this->config->get('config_meta_description');
        $this->data['keywords'] = $this->config->get('config_meta_keyword');
        $this->data['lang'] = $this->language->get('code');
        $this->data['direction'] = $this->language->get('direction');
        $this->data['name'] = $this->config->get('config_name');

        $this->data['home'] = $this->url->link('common/home');
        $this->data['text_continue'] = $this->language->get('text_continue');

        if (isset($this->session->data['svea_last_page']) && $this->session->data['svea_last_page'] === 'svea/success') {
            unset($this->session->data['svea_last_page']);
            $sco_order_id = $this->session->data['sco_success_order_id'];
        } else if (isset($this->session->data['svea_last_page']) && $this->session->data['svea_last_page'] !== 'svea/success') {
            $this->session->data['svea_last_page'] = 'svea/success';
        } else {
            $this->response->redirect($this->url->link('checkout/success'));
            return;
        }

        $test_mode = $this->config->get('sco_test_mode');
        $config = new OpencartSveaCheckoutConfig($this->config, 'checkout');
        if ($test_mode) {
            $config = new OpencartSveaCheckoutConfigTest($this->config, 'checkout');
        }

        $checkout_entry = \Svea\WebPay\WebPay::checkout($config);
        $checkout_entry->setCheckoutOrderId($sco_order_id);

        // Get Svea Checkout order
        try {
            $response = $checkout_entry->getOrder();
            $response = lowerArrayKeys($response);
        } catch (Exception $e) {
            die($e->getMessage());
        }

        $this->updateOrders($response);

        $this->data['button_continue'] = $this->language->get('button_continue');
        $this->data['continue'] = $this->url->link('common/home');

        $this->data['breadcrumbs'] = array();

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home'),
            'separator' => false
        );

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_basket'),
            'href' => $this->url->link('checkout/cart'),
            'separator' => $this->language->get('text_separator')
        );

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_checkout'),
            'href' => $this->url->link('checkout/checkout', '', true),
            'separator' => $this->language->get('text_separator')
        );

        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_success'),
            'href' => $this->url->link('checkout/success'),
            'separator' => $this->language->get('text_separator')
        );

        $this->data['snippet'] = (isset($response['gui']['snippet'])) ? $response['gui']['snippet'] : NULL;

        $this->children = array(
            'common/header',
            'common/footer'
        );

        $this->template = 'default/template/svea/success.tpl';
        $this->response->setOutput($this->render());
    }

    private function updateOrders($response)
    {
        // - update order
        $this->updateOrder($response);

        // - update sco order
        $this->updateCheckoutOrder($response);
    }

    private function updateOrder($response)
    {
        $this->load->model('svea/checkout');
        $this->model_svea_checkout->updateOrder($response['clientordernumber'], $response);
    }

    private function updateCheckoutOrder($response)
    {
        $this->load->model('svea/checkout');

        $country = $this->model_svea_checkout->getCountry($response['countrycode']);

        $date_of_birth = null;
        if (isset($response['customer']['dateofbirth']) && !empty($response['customer']['dateofbirth'])) {
            $date_of_birth = $response['customer']['dateofbirth'];
        }

        $data = array(
            'order_id' => $response['clientordernumber'],
            'checkout_id' => $response['orderid'],
            'status' => isset($response['status']) ? $response['status'] : null,

            'type' => isset($response['customer']['iscompany']) ? ($response['customer']['iscompany'] ? 'company' : 'person') : 'person',
            'gender' => isset($response['customer']['ismale']) ? ($response['customer']['ismale'] ? 'male' : 'female') : null,
            'date_of_birth' => $date_of_birth,

            'locale' => isset($response['locale']) ? $response['locale'] : null,
            'currency' => isset($response['currency']) ? $response['currency'] : null,
            'country' => $country['name'],
        );

        $this->model_svea_checkout->updateCheckoutOrder($data);
    }
}
