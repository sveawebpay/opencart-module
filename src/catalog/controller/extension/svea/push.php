<?php

require_once(DIR_APPLICATION . 'controller/extension/payment/svea_common.php');
require_once(DIR_APPLICATION . '../svea/config/configInclude.php');

class ControllerExtensionSveaPush extends Controller
{
    private $paymentString = "payment_";
    private $moduleString = "module_";

    public function setVersionStrings()
    {
        if (VERSION < 3.0) {
            $this->paymentString = "";
            $this->moduleString = "";
        }
    }

    public function index()
    {
        $this->setVersionStrings();

        // Avoid success and callback request to be processed simultaneously
        if ($this->config->get($this->moduleString . 'sco_create_order_on_success_page') == 1) {
            sleep(5);
        }

        $checkout_order_id = isset($this->request->get[$this->paymentString . 'svea_order']) ? trim($this->request->get[$this->paymentString . 'svea_order']) : null;

        if ($checkout_order_id != null) {
            $test_mode = $this->config->get($this->moduleString . 'sco_test_mode');

            $config = new OpencartSveaCheckoutConfig($this, 'checkout');
            if ($test_mode) {
                $config = new OpencartSveaCheckoutConfigTest($this, 'checkout');
            }

            $this->load->model('extension/svea/checkout');

            $checkout_entry = \Svea\WebPay\WebPay::checkout($config);
            $checkout_entry->setCountryCode($this->model_extension_svea_checkout->getCountryCode((int)$checkout_order_id));
            $checkout_entry->setCheckoutOrderId((int)$checkout_order_id);

            // Get Svea Checkout order
            try {
                $response = $checkout_entry->getOrder();
                $response = lowerArrayKeys($response);
            } catch (Exception $e) {
                die($e->getMessage());
            }

            if ($test_mode) {
                $response['clientordernumber'] = str_replace(hash('crc32', HTTPS_SERVER), '', $response['clientordernumber']);
            }

            if ($this->config->get($this->moduleString . 'sco_create_order_on_received_push') == 1) {
                $this->updateOrders($response);
            } else {
                $this->log->write('Svea: Push received for checkoutOrderId ' . $checkout_order_id . ' but create order on push setting was disabled.');
            }

            // Set response header
            header("HTTP/1.1 200 OK");
        } else {
            header("HTTP/1.1 400 BadRequest");
        }

        exit;
    }

    private function updateOrders($response)
    {
        $this->updateOrder($response);

        $this->updateCheckoutOrder($response);
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
