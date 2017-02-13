<?php

class ControllerSveaCheckout extends Controller
{
    public function index()
    {
        $this->load->language('checkout/checkout');
        $this->load->language('svea/checkout');

        $this->load->model('svea/checkout');
        $this->data = array();

        /* Check status - start */
        $status = true;
        $status = (!$this->cart->hasProducts() && empty($this->session->data['vouchers'])) ? false : $status;
        $status = (!$this->cart->hasStock() && !$this->config->get('config_stock_checkout')) ? false : $status;
        if (!$this->config->get('sco_status')) {
            $this->response->redirect($this->url->link('checkout/checkout/index'));
        }

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

        $this->data['status_default_checkout'] = $this->config->get('sco_status_checkout');

        $this->data['entry_email'] = $this->language->get('entry_email');
        $this->data['entry_postcode'] = $this->language->get('entry_postcode');

        // Section titles
        $this->data['heading_order'] = $this->language->get('heading_order');
        $this->data['heading_shipping'] = $this->language->get('heading_shipping');
        $this->data['heading_misc'] = $this->language->get('heading_misc');
        $this->data['heading_cart'] = $this->language->get('heading_cart');
        $this->data['heading_payment'] = $this->language->get('heading_payment');

        $this->data['text_comment'] = $this->language->get('text_comments');
        $this->data['text_tooltip_shipping'] = $this->language->get('text_shipping_method');
        $this->data['text_checkout_into'] = $this->language->get('text_checkout_into');
        $this->data['text_normal_checkout'] = sprintf($this->language->get('text_normal_checkout'), $this->url->link('checkout/checkout/index'));

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

        $this->data['sco_email'] = isset($this->session->data['sco_email']) ? $this->session->data['sco_email'] : null;
        $this->data['sco_postcode'] = isset($this->session->data['sco_postcode']) ? $this->session->data['sco_postcode'] : null;

        $this->data['order_comment'] = '';
        if (isset($this->session->data['order_id'])) {
            $this->load->model('checkout/order');
            $order_details = $this->model_checkout_order->getOrder($this->session->data['order_id']);
            if (isset($order_details['comment'])) {
                $this->data['order_comment'] = $order_details['comment'];
            }
        }

        $this->load->language('checkout/cart');
        $this->data['status_coupon'] = $this->config->get('coupon_status');
        if ($this->data['status_coupon']) {
            $this->data['text_coupon'] = $this->language->get('text_use_coupon');
        }

        $this->data['status_voucher'] = $this->config->get('voucher_status');
        if ($this->data['status_voucher']) {
            $this->data['text_voucher'] = $this->language->get('text_use_voucher');
        }

        $this->children = array(
            'common/header',
            'common/footer'
        );

        $this->template = 'default/template/svea/checkout.tpl';
        $this->response->setOutput($this->render());
    }

    public function redirectToScoPage()
    {
        $this->redirect($this->url->link('svea/checkout'));
    }

}
