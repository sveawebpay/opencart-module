<?php

class ControllerSveaCheckout extends Controller
{
    public function index()
    {
//        $this->load->language('checkout/checkout');
        $this->load->language('svea/checkout');

        $this->load->model('svea/checkout');
        $this->load->model('extension/extension');

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
        $this->session->data['sco_country_id'] = '203'; // Hard coded Sweden country

        $data['status_default_checkout'] = $this->config->get('sco_status_checkout');

        $data['entry_email'] = $this->language->get('entry_email');
        $data['entry_postcode'] = $this->language->get('entry_postcode');

        // Section titles
        $data['heading_order'] = $this->language->get('heading_order');
        $data['heading_shipping'] = $this->language->get('heading_shipping');
        $data['heading_misc'] = $this->language->get('heading_misc');

        $data['heading_payment'] = $this->language->get('heading_payment');

//        $data['text_tooltip_shipping'] = $this->language->get('text_shipping_method');
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

        $data['sco_show_coupons'] = $this->config->get('sco_show_coupons_on_checkout');
        $data['sco_show_voucher'] = $this->config->get('sco_show_voucher_on_checkout');
        $data['sco_show_comment'] = $this->config->get('sco_show_order_comment_on_checkout');

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

        $data['coupon_icon_title'] = $this->language->get('item_coupon');
        $data['voucher_icon_title'] = $this->language->get('item_voucher');
        $data['comment_icon_title'] = $this->language->get('item_comment');
        $data['text_change_postcode'] = $this->language->get('text_change_postcode');
        $data['button_continue'] = $this->language->get('button_continue');
        $data['button_back'] = $this->language->get('button_back');

        $data['text_comment'] = $this->language->get('text_comment');
        $data['status_coupon'] = $this->config->get('coupon_status');
        $data['text_coupon'] = $this->language->get('text_coupon');

        $data['status_voucher'] = $this->config->get('voucher_status');
        $data['text_voucher'] = $this->language->get('text_voucher');

        $data['header'] = $this->load->controller('common/header');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('svea/checkout', $data));
    }

    public function redirectToScoPage()
    {
        $this->response->redirect($this->url->link('svea/checkout'));
    }

}
