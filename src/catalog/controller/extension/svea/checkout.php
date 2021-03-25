<?php

class ControllerExtensionSveaCheckout extends Controller
{
    private $moduleString = "module_";
    private $totalString = "total_";

    public function setVersionStrings()
    {
        if (VERSION < 3.0) {
            $this->moduleString = "";
            $this->totalString = "";
        }
    }

    private function setCheckoutCountry($country)
    {
        $this->setVersionStrings();

        if ($country == 203) {
            $this->session->data[$this->moduleString . 'sco_locale'] = "sv-se";
            $this->session->data[$this->moduleString . 'sco_currency'] = "SEK";
            $this->session->data['currency'] = 'SEK';
        } elseif ($country == 160) {
            $this->session->data[$this->moduleString . 'sco_locale'] = "nn-no";
            $this->session->data[$this->moduleString . 'sco_currency'] = "NOK";
            $this->session->data['currency'] = 'NOK';
        } elseif ($country == 72) {
            $this->session->data[$this->moduleString . 'sco_locale'] = "fi-fi";
            $this->session->data[$this->moduleString . 'sco_currency'] = "EUR";
            $this->session->data['currency'] = 'EUR';
        } elseif ($country == 57) {
            $this->session->data[$this->moduleString . 'sco_locale'] = "da-dk";
            $this->session->data[$this->moduleString . 'sco_currency'] = "DKK";
            $this->session->data['currency'] = 'DKK';
        } elseif ($country == 81) {
            $this->session->data[$this->moduleString . 'sco_locale'] = "de-de";
            $this->session->data[$this->moduleString . 'sco_currency'] = "EUR";
            $this->session->data['currency'] = 'EUR';
        }

        $this->load->model('localisation/country');

        $countryCode = $this->model_localisation_country->getCountry($country);

        $this->session->data[$this->moduleString . 'sco_country'] = $countryCode['iso_code_2'];
        $this->session->data[$this->moduleString . 'sco_country_id'] = $country;
    }

    public function getCheckoutCountry()
    {
        $this->setVersionStrings();

        if ($this->request->cookie['language'] == "sv-se") {
            return 203;
        } elseif ($this->request->cookie['language'] == "nn-no") {
            return 160;
        } elseif ($this->request->cookie['language'] == "fi-fi") {
            return 72;
        } elseif ($this->request->cookie['language'] == "da-dk") {
            return 57;
        } elseif ($this->request->cookie['language'] == "de-de") {
            return 81;
        } else {
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
        $data['status_test_mode'] = $this->config->get($this->moduleString . 'sco_test_mode');

        $data['entry_email'] = $this->language->get('entry_email');
        $data['entry_postcode'] = $this->language->get('entry_postcode');

        // Section titles
        $data['heading_order'] = $this->language->get('heading_order');
        $data['heading_shipping'] = $this->language->get('heading_shipping');
        $data['heading_misc'] = $this->language->get('heading_misc');
        $data['heading_payment'] = $this->language->get('heading_payment');

        $data['text_subscribe_to_newsletter'] = $this->language->get('text_sco_subscribe_to_newsletter');
        $data['text_normal_checkout'] = sprintf($this->language->get('text_normal_checkout'), $this->url->link('checkout/checkout/index'));

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
        $data[$this->moduleString . 'sco_gather_newsletter_consent'] = $this->config->get($this->moduleString . 'sco_gather_newsletter_consent');

        $data['order_comment'] = '';

        if (isset($this->session->data['order_id'])) {
            $this->load->model('checkout/order');
            $order_details = $this->model_checkout_order->getOrder($this->session->data['order_id']);

            if (isset($order_details['comment'])) {
                $data['order_comment'] = $order_details['comment'];
            }
        }

        $data['text_test_mode']       = $this->language->get('text_test_mode');
        $data['text_comment']         = $this->language->get('text_comment');
        $data['status_coupon']        = $this->config->get($this->totalString . 'coupon_status');
        $data['text_coupon']          = $this->language->get('text_coupon');

        $data['coupon_icon_title']    = $this->language->get('item_coupon');
        $data['voucher_icon_title']   = $this->language->get('item_voucher');
        $data['comment_icon_title']   = $this->language->get('item_comment');
        $data['text_change_postcode'] = $this->language->get('text_change_postcode');
        $data['button_continue']      = $this->language->get('button_continue');
        $data['button_back']          = $this->language->get('button_back');

        $data['status_voucher']       = $this->config->get($this->totalString . 'voucher_status');
        $data['text_voucher']         = $this->language->get('text_voucher');
        
        $this->document->addScript($this->getThemeDependentResourceSrc('javascript/svea/sco.js'));
        $this->document->addStyle($this->getThemeDependentResourceSrc('stylesheet/svea/sco.css'));

        $data['footer']               = $this->load->controller('common/footer');
        $data['header']               = $this->load->controller('common/header');

        $this->response->setOutput($this->load->view('extension/svea/checkout', $data));
    }
    
    protected function getThemeDirName()
    {
        if ((VERSION >= 3.0 && $this->config->get('config_theme') == 'default') || (VERSION < 3.0 && $this->config->get('config_theme') == 'theme_default')) {
			$theme_dir = $this->config->get('theme_default_directory');
		} else {
			$theme_dir = $this->config->get('config_theme');
		}
        return $theme_dir;
    }
    
    protected function getThemeDependentResourceSrc($resource)
    {
        $theme_dir = $this->getThemeDirName();
        $filename = DIR_TEMPLATE . $theme_dir . '/' . $resource;
        if ( !file_exists($filename) ) {
            $theme_dir = 'default'; // expect resource to be always existing in the default theme dir
            $filename = DIR_TEMPLATE . $theme_dir . '/' . $resource; 
        }
        return 'catalog/view/theme/' . $theme_dir . '/' . $resource . '?' . filemtime($filename);
    }

    public function redirectToScoPage()
    {
        $this->response->redirect($this->url->link('extension/svea/checkout'));
    }
}
