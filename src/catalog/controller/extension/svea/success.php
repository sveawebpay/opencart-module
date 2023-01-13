<?php

require_once(DIR_APPLICATION . 'controller/extension/payment/svea_common.php');
require_once(DIR_APPLICATION . '../svea/config/configInclude.php');

class ControllerExtensionSveaSuccess extends Controller
{
    private $moduleString = "module_";
    private $paymentString = "payment_";
    private $extensionString = "setting/extension";
    private $totalString = "total_";

    public function setVersionStrings()
    {
        if (VERSION < 3.0) {
            $this->paymentString = "";
            $this->moduleString = "";
            $this->extensionString = "extension/extension";
            $this->totalString = "";
        }
    }

    public function index()
    {
        $this->setVersionStrings();

        $module_sco_order_id = null;
        $test_mode = $this->config->get($this->moduleString . 'sco_test_mode');
        $order_id = !empty($_GET['order_id']) ? $_GET['order_id'] : null;
        $hash = !empty($_GET['hash']) ? $_GET['hash'] : null;

        if ($test_mode) {
            $order_id = str_replace(hash('crc32', HTTPS_SERVER), '', $order_id);
        }

        if ($order_id && $hash) {
            $query = $this->db->query("SELECT `checkout_id` FROM `" . DB_PREFIX . "svea_sco_order` WHERE order_id = '" . $this->db->escape((int)$order_id) . "'");
            $module_sco_order_id = (int)$query->row['checkout_id'];
        }

        if ($module_sco_order_id != null) {
            $config = new OpencartSveaCheckoutConfig($this, 'checkout');

            if ($test_mode) {
                $config = new OpencartSveaCheckoutConfigTest($this, 'checkout');
            }

            $checkout_entry = \Svea\WebPay\WebPay::checkout($config);

            if (!empty($this->session->data['svea_checkout']['country_code'])) {
                $checkout_entry->setCountryCode($this->session->data['svea_checkout']['country_code']);
            }

            $checkout_entry->setCheckoutOrderId($module_sco_order_id);

            try {
                $response = $checkout_entry->getOrder();
                $response = lowerArrayKeys($response);
                $hashOnOrder = substr((string)$response['merchantsettings']['confirmationuri'], strpos((string)$response['merchantsettings']['confirmationuri'], "hash=")+5);

                if ($test_mode) {
                    $response['clientordernumber'] = str_replace(hash('crc32', HTTPS_SERVER), '', $response['clientordernumber']);
                }

                if (strtoupper($response['status']) != 'FINAL' || $hashOnOrder != $hash) {
                    $this->response->redirect($this->url->link('extension/svea/checkout'));
                    return;
                }
            } catch (Exception $e) {
                die($e->getMessage());
            }
        } else {
            $this->response->redirect($this->url->link('extension/svea/checkout'));
            return;
        }

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

        unset($this->session->data[$this->moduleString . 'sco_locale']);
        unset($this->session->data[$this->moduleString . 'sco_currency']);
        unset($this->session->data[$this->moduleString . 'sco_order_id']);
        unset($this->session->data[$this->moduleString . 'sco_cart_hash']);
        unset($this->session->data[$this->moduleString . 'sco_email']);
        unset($this->session->data[$this->moduleString . 'sco_postcode']);

        // Clear opencart Cart
        $this->cart->clear();

        $this->load->language('extension/svea/checkout');

        $data['title']         = $this->config->get('config_meta_title');

        $data['base']          = ($this->request->server['HTTPS']) ? $this->config->get('config_ssl') : $this->config->get('config_url');
        $data['description']   = $this->config->get('config_meta_description');
        $data['keywords']      = $this->config->get('config_meta_keyword');
        $data['lang']          = $this->language->get('code');
        $data['direction']     = $this->language->get('direction');
        $data['name']          = $this->config->get('config_name');

        $data['home']          = $this->url->link('common/home');
        $data['text_continue'] = $this->language->get('text_continue');

        if ($this->config->get($this->moduleString . 'sco_create_order_on_success_page') == 1) {
            $this->updateOrders($response);
        }

        $data['button_continue'] = $this->language->get('button_continue');
        $data['continue'] = $this->url->link('common/home');

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home')
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_basket'),
            'href' => $this->url->link('checkout/cart')
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_checkout'),
            'href' => $this->url->link('checkout/checkout', '', true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_success'),
            'href' => $this->url->link('checkout/success')
        );

        $data['snippet'] = (isset($response['gui']['snippet'])) ? $response['gui']['snippet'] : null;

        $data['header'] = $this->load->controller('common/header');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/svea/success', $data));
    }

    private function updateOrders($response)
    {
        $this->updateCheckoutOrder($response);

        $this->updateOrder($response);
    }

    private function updateOrder($response)
    {
        $this->load->model('extension/svea/checkout');
        $this->model_extension_svea_checkout->updateOrder($response['clientordernumber'], $response);
    }

    private function updateCheckoutOrder($response)
    {
        $this->load->model('extension/svea/checkout');

        $country = $this->model_extension_svea_checkout->getCountry($response['countrycode']);

        if (isset($response['merchantdata'])) {
            $merchantData = json_decode($response['merchantdata']);
            if ($merchantData->newsletter == "true") {
                $newsletterConsent = true;
            } else {
                $newsletterConsent = false;
            }
        }

        $data = array(
            'order_id'    => $response['clientordernumber'],
            'checkout_id' => $response['orderid'],
            'status'      => isset($response['status']) ? $response['status'] : null,
            'type'        => isset($response['customer']['iscompany']) ? ($response['customer']['iscompany'] ? 'company' : 'person') : 'person',
            'locale'      => isset($response['locale']) ? $response['locale'] : null,
            'currency'    => isset($response['currency']) ? $response['currency'] : null,
            'country'     => $country['name'],
            'newsletter'  => isset($newsletterConsent) ? $newsletterConsent : 0
        );

        $this->model_extension_svea_checkout->updateCheckoutOrder($data);
        $this->model_extension_svea_checkout->addInvoiceFee($response);
    }
}
