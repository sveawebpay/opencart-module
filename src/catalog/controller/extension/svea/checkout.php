<?php

class ControllerExtensionSveaCheckout extends Controller
{
    private $moduleString = "module_";
    private $totalString = "total_";

    public function setVersionStrings()
    {
        if(VERSION < 3.0)
        {
            $this->moduleString = "";
            $this->totalString = "";
        }
    }
    
    private function setCheckoutCountry($country)
    {
        $this->setVersionStrings();
        
        if($country == 203)
        {
            $this->session->data[$this->moduleString . 'sco_locale'] = "sv-se";
            $this->session->data[$this->moduleString . 'sco_currency'] = "SEK";
            $this->session->data['currency'] = 'SEK';
        }
        else if($country == 160)
        {
            $this->session->data[$this->moduleString . 'sco_locale'] = "nn-no";
            $this->session->data[$this->moduleString . 'sco_currency'] = "NOK";
            $this->session->data['currency'] = 'NOK';
        }
        else if($country == 72)
        {
            $this->session->data[$this->moduleString . 'sco_locale'] = "fi-fi";
            $this->session->data[$this->moduleString . 'sco_currency'] = "EUR";
            $this->session->data['currency'] = 'EUR';
        }
        else {

        }
        $this->load->model('localisation/country');
        $countryCode = $this->model_localisation_country->getCountry($country);
        $this->session->data[$this->moduleString . 'sco_country'] = $countryCode['iso_code_2'];
        $this->session->data[$this->moduleString . 'sco_country_id'] = $country;
    }

    public function getCheckoutCountry()
    {
        $this->setVersionStrings();

        if($this->request->cookie['language'] == "sv-se")
        {
            return 203;
        }
        else if($this->request->cookie['language'] == "nn-no")
        {
            return 160;
        }
        else if($this->request->cookie['language'] == "fi-fi")
        {
            return 72;
        }
        else
        {
            return $this->config->get($this->moduleString . 'sco_checkout_default_country_id');
        }
    }
    public function index()
    {
        $this->setVersionStrings();
        
        $this->load->language('extension/svea/checkout');
        $this->load->model('extension/svea/checkout');

        /* Check status - start */
        $status = true;
        $status = (!$this->cart->hasProducts() && empty($this->session->data['vouchers'])) ? false : $status;
        $status = (!$this->cart->hasStock() && !$this->config->get('config_stock_checkout')) ? false : $status;
        if (!$this->config->get($this->moduleString . 'sco_status')) {
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

        $this->setCheckoutCountry($this->getCheckoutCountry());

        $data['status_default_checkout'] = $this->config->get($this->moduleString . 'sco_status_checkout');

        $data['entry_email'] = $this->language->get('entry_email');
        $data['entry_postcode'] = $this->language->get('entry_postcode');

        // Section titles
        $data['heading_order'] = $this->language->get('heading_order');
        $data['heading_shipping'] = $this->language->get('heading_shipping');
        $data['heading_misc'] = $this->language->get('heading_misc');
        $data['heading_payment'] = $this->language->get('heading_payment');

        $data['text_checkout_into'] = $this->language->get('text_checkout_into');
        $data['text_normal_checkout'] = sprintf($this->language->get('text_normal_checkout'), $this->url->link('checkout/checkout/index'));

//        $this->session->data[$this->moduleString . 'sco_email'] = '';
//        $this->session->data[$this->moduleString . 'sco_postcode'] = '';

        if ($this->customer->isLogged()) {
            if ($this->customer->getEmail()) {
                $this->session->data[$this->moduleString . 'sco_email'] = $this->customer->getEmail();
            }
            if ($this->customer->getAddressId()) {
                $this->session->data[$this->moduleString . 'sco_postcode'] = $this->model_extension_svea_checkout->getPostcode($this->customer->getAddressId());
            }
        }

        $data[$this->moduleString . 'sco_email'] = isset($this->session->data[$this->moduleString . 'sco_email']) ? $this->session->data[$this->moduleString . 'sco_email'] : null;
        $data[$this->moduleString . 'sco_postcode'] = isset($this->session->data[$this->moduleString . 'sco_postcode']) ? $this->session->data[$this->moduleString . 'sco_postcode'] : null;

        $data[$this->moduleString . 'sco_show_coupons'] = $this->config->get($this->moduleString . 'sco_show_coupons_on_checkout');
        $data[$this->moduleString . 'sco_show_voucher'] = $this->config->get($this->moduleString . 'sco_show_voucher_on_checkout');
        $data[$this->moduleString . 'sco_show_comment'] = $this->config->get($this->moduleString . 'sco_show_order_comment_on_checkout');

        $data['order_comment'] = '';
        if (isset($this->session->data['order_id'])) {
            $this->load->model('checkout/order');
            $order_details = $this->model_checkout_order->getOrder($this->session->data['order_id']);
            if (isset($order_details['comment'])) {
                $data['order_comment'] = $order_details['comment'];
            }
        }

        $data['text_comment'] = $this->language->get('text_comment');
        $data['status_coupon'] = $this->config->get($this->totalString . 'coupon_status');
        $data['text_coupon'] = $this->language->get('text_coupon');

        $data['coupon_icon_title'] = $this->language->get('item_coupon');
        $data['voucher_icon_title'] = $this->language->get('item_voucher');
        $data['comment_icon_title'] = $this->language->get('item_comment');
        $data['text_change_postcode'] = $this->language->get('text_change_postcode');
        $data['button_continue'] = $this->language->get('button_continue');
        $data['button_back'] = $this->language->get('button_back');

        $data['status_voucher'] = $this->config->get($this->totalString . 'voucher_status');
        $data['text_voucher'] = $this->language->get('text_voucher');

        $data['header'] = $this->load->controller('common/header');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/svea/checkout', $data));
    }

    public function redirectToScoPage()
    {
        $this->response->redirect($this->url->link('extension/svea/checkout'));
    }

}
