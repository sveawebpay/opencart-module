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

    public function getDefaultCountry($country_id = null)
    {
        switch ($country_id) {
            case 203:
                return [
                    'country_code' => 'SE',
                    'country_id'   => 203,
                    'locale'       => 'sv-se',
                    'currency'     => 'SEK',
                ];
            case 160:
                return [
                    'country_code' => 'NO',
                    'country_id'   => 160,
                    'locale'       => 'nn-no',
                    'currency'     => 'NOK',
                ];
            case 72:
                return [
                    'country_code' => 'FI',
                    'country_id'   => 72,
                    'locale'       => 'fi-fi',
                    'currency'     => 'EUR',
                ];
            case 57:
                return [
                    'country_code' => 'DK',
                    'country_id'   => 57,
                    'locale'       => 'da-dk',
                    'currency'     => 'DKK',
                ];
            case 81:
                return [
                    'country_code' => 'DE',
                    'country_id'   => 81,
                    'locale'       => 'de-de',
                    'currency'     => 'EUR',
                ];
            default:
                if (is_numeric($this->config->get('module_sco_checkout_default_country_id'))) {
                    $default = $this->getDefaultCountry($this->config->get('module_sco_checkout_default_country_id'));
                }

                $parts = explode('-', $this->session->data['language']);
                $country_code = !empty($parts[1]) ? $parts[1] : 'SE';

                return [
                    'country_code' => $country_code,
                    'country_id'   => !empty($default['country_id']) ? $default['country_id'] : $this->config->get('module_sco_checkout_default_country_id'),
                    'locale'       => !empty($default['locale']) ? $default['locale'] : $this->session->data['language'],
                    'currency'     => !empty($default['currency']) ? $default['currency'] : $this->session->data['currency'],
                ];
        }
    }

    public function getCheckoutCountry()
    {
        switch ($this->session->data['language']) {
            case 'sv-se':
                $settings = $this->getDefaultCountry(203);
                break;
            case 'nn-no':
                $settings = $this->getDefaultCountry(160);
                break;
            case 'fi-fi':
                $settings = $this->getDefaultCountry(72);
                break;
            case 'da-dk':
                $settings = $this->getDefaultCountry(57);
                break;
            case 'de-de':
                $settings = $this->getDefaultCountry(81);
                break;
            default:
                $settings = $this->getDefaultCountry();
                break;
        }

        $this->session->data['svea_checkout']['country_code'] = $settings['country_code'];
        $this->session->data['svea_checkout']['country_id'] = $settings['country_id'];
        $this->session->data['svea_checkout']['locale'] = $settings['locale'];
        $this->session->data['svea_checkout']['currency'] = $settings['currency'];
        $this->session->data['currency'] = $settings['currency'];
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

        $this->getCheckoutCountry();

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

        $data['footer']               = $this->load->controller('common/footer');
        $data['header']               = $this->load->controller('common/header');

        $this->response->setOutput($this->load->view('extension/svea/checkout', $data));
    }

    public function redirectToScoPage()
    {
        $this->response->redirect($this->url->link('extension/svea/checkout'));
    }
}
