<?php

class ControllerSveaCheckout extends Controller
{
    public function index()
    {
        $this->load->language('checkout/checkout');
        $this->load->language('svea/checkout');

        $this->load->model('svea/checkout');
        $this->load->model('extension/extension');

        /* Check status - start */
        $status = true;
        $status = (!$this->cart->hasProducts() && empty($this->session->data['vouchers'])) ? false : $status;
        $status = (!$this->cart->hasStock() && !$this->config->get('config_stock_checkout')) ? false : $status;
        $status = (!$this->config->get('sco_status')) ? false : $status;

        $products = $this->cart->getProducts();

        foreach ($products as $product) {
            $status = ($product['minimum'] > $product['quantity']) ? false : $status;
        }

        if (!$status) {
            $this->response->redirect($this->url->link('checkout/cart'));
        }
        /* Check status - end */

        $this->session->data['sco_locale'] = 'sv-se';
        $this->session->data['sco_currency'] = 'SEK';
        $this->session->data['currency'] = 'SEK';
        $this->session->data['sco_country'] = 'SE';

        $data['status_default_checkout'] = $this->config->get('sco_status_checkout');

        $data['entry_email'] = $this->language->get('entry_email');
        $data['entry_postcode'] = $this->language->get('entry_postcode');

        // Section titles
        $data['heading_order'] = $this->language->get('heading_order');
        $data['heading_shipping'] = $this->language->get('heading_shipping');
        $data['heading_misc'] = $this->language->get('heading_misc');
        $data['heading_cart'] = $this->language->get('heading_cart');
        $data['heading_payment'] = $this->language->get('heading_payment');

        $data['text_comment'] = $this->language->get('text_comments');
        $data['text_tooltip_shipping'] = $this->language->get('text_shipping_method');
        $data['text_checkout_into'] = $this->language->get('text_checkout_into');
        $data['text_normal_checkout'] = sprintf($this->language->get('text_normal_checkout'), $this->url->link('checkout/checkout/index'));

//        $this->session->data['sco_email'] = '';
//        $this->session->data['sco_postcode'] = '';

        if ($this->customer->isLogged()) {
            if ($this->customer->getEmail()) {
                $this->session->data['sco_email'] = $this->customer->getEmail();
            }
            if ($this->customer->getAddressId()) {
                $this->session->data['sco_postcode'] = $this->model_svea_checkout->getPostcode($this->customer->getAddressId());
            }
        }

        $data['sco_email'] = isset($this->session->data['sco_email']) ? $this->session->data['sco_email'] : null;
        $data['sco_postcode'] = isset($this->session->data['sco_postcode']) ? $this->session->data['sco_postcode'] : null;

        $data['order_comment'] = '';
        if (isset($this->session->data['order_id'])) {
            $this->load->model('checkout/order');
            $order_details = $this->model_checkout_order->getOrder($this->session->data['order_id']);
            if (isset($order_details['comment'])) {
                $data['order_comment'] = $order_details['comment'];
            }
        }

        $data['status_coupon'] = $this->config->get('coupon_status');
        if ($data['status_coupon']) {
            $this->load->language('total/coupon');
            $data['text_coupon'] = $this->language->get('heading_title');
        }

        $data['status_voucher'] = $this->config->get('voucher_status');
        if ($data['status_voucher']) {
            $this->load->language('total/voucher');
            $data['text_voucher'] = $this->language->get('heading_title');
        }

        $data['header'] = $this->load->controller('common/header');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('svea/checkout', $data));
    }

    public function redirectToScoPage()
    {
        $this->response->redirect($this->url->link('svea/checkout'));
    }

}
