<?php

require_once(DIR_APPLICATION . '../svea/config/configInclude.php');

class ControllerExtensionSveaSyncOrder extends Controller
{
    private $moduleString = "module_";
    private $paymentString = "payment_";
    protected $current_order_id;

    public function setVersionStrings()
    {
        if(VERSION < 3.0)
        {
            $this->paymentString = "";
            $this->moduleString = "";
        }
    }

    public function index()
    {
        $this->setVersionStrings();
        
        $this->load->language('extension/svea/sync_order');
        $json = array();

        $validation_result = $this->validateRequest();
        if ($validation_result !== true) {
            $json['error'] = $validation_result;
        } else {
            $this->current_order_id = (int)$this->request->post['orderId'];
            try {
                $checkout_order = $this->getOrderDataFromApi();
                if ($checkout_order) {
                    $order_status_id = $this->updateOcOrderData($checkout_order);
                    if ($order_status_id) {
                        $json['success'] = $this->language->get('text_success');
                        $json['order_status_id'] = $order_status_id;
                    } else {
                        $json['error'] = $this->language->get('error_update_order');
                    }
                } else {
                    $json['error'] = $this->language->get('error_update_order');
                }
            } catch (\Exception $e) {
                $json['error'] = ' Svea Error: ' . $e->getMessage();
            }
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    private function validateRequest()
    {
        if ($this->request->server['REQUEST_METHOD'] !== 'POST') {
            return $this->language->get('error_permission');
        }

        if (!isset($this->request->post['orderId'])) {
            return $this->language->get('error_order_id_missing');
        }

        return true;
    }

    private function getOrderDataFromApi()
    {
        $checkout_order = null;
        $checkout_order_id = $this->getSveaCheckoutOrderId($this->current_order_id);

        if (!empty($checkout_order_id)) {
            $config = $this->getConfiguration();
            $checkout_order = \Svea\WebPay\WebPayAdmin::queryOrder($config)
                ->setCheckoutOrderId($checkout_order_id)
                ->queryCheckoutOrder()
                ->doRequest();

            $checkout_order = lowerArrayKeys($checkout_order);
        }

        return $checkout_order;
    }

    private function getConfiguration()
    {
        $this->setVersionStrings();
        $config = new OpencartSveaCheckoutConfig($this->config, 'checkout');
        if ($this->config->get($this->moduleString . 'sco_test_mode')) {
            $config = new OpencartSveaCheckoutConfigTest($this->config, 'checkout');
        }

        return $config;
    }

    private function getSveaCheckoutOrderId($oc_order_id)
    {
        $checkout_order_id = null;
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_sco WHERE order_id = " . $oc_order_id);

        foreach ($query->rows as $order) {
            $checkout_order_id = (int)$order['checkout_id'];
        }

        return $checkout_order_id;
    }

    private function updateOcOrderData($checkout_order)
    {
        // - update order
//        $this->updateOCOrder($checkout_order);

        // - update oc order status
        $oc_order_status_id = $this->updateOrderStatus($checkout_order);

        return $oc_order_status_id;
    }

    private function updateOCOrder($response)
    {
        $this->load->model('extension/svea/checkout');
        return $this->model_svea_checkout->updateOrder($this->current_order_id, $response);
    }

    private function updateOrderStatus($checkout_order)
    {
        $oc_order_status_id = $this->getOrderStatusId($checkout_order);

        if ($oc_order_status_id !== null) {
            $query = "UPDATE `" . DB_PREFIX . "order` SET order_status_id = '" . $oc_order_status_id . "',";
            $query .= " date_modified = NOW() WHERE order_id = '" . (int)$this->current_order_id . "' LIMIT 1 ";
            $this->db->query($query);
        }

        return $oc_order_status_id;
    }

    private function getOrderStatusId($checkout_order)
    {
        $this->setVersionStrings();
        $oc_order_status_id = null;
        $module_sco_status = $checkout_order['orderstatus'];

        switch ($module_sco_status) {
            case 'Open':
                /**
                 * Default order status when an order is processed. (Settings from shop)
                 */
                $oc_order_status_id = $this->config->get('config_order_status_id');
                break;
            case 'Delivered':
                if ($this->isCredited($checkout_order) === true) {
                    $oc_order_status_id = $this->config->get($this->moduleString . 'sco_credited_status_id');
                } else {
                    $oc_order_status_id = $this->config->get($this->moduleString . 'sco_delivered_status_id');
                }
                break;
            case 'Cancelled':
                $oc_order_status_id = $this->config->get($this->moduleString . 'sco_canceled_status_id');
                break;
            case 'Pending':
                $oc_order_status_id = $this->config->get($this->moduleString . 'sco_pending_status_id');
                break;
            case 'Denied':
                $oc_order_status_id = $this->config->get($this->moduleString . 'sco_failed_status_id');
                break;
        }

        return $oc_order_status_id;
    }

    private function isCredited($checkout_order)
    {
        $isCredited = true;
        $deliveries = $checkout_order['deliveries'];
        foreach ($deliveries as $delivery) {
            if ($delivery['deliveryamount'] !== $delivery['creditedamount']) {
                $isCredited = false;
            }
        }

        if (count($checkout_order['orderrows']) > 0) {
            $isCredited = false;
        }

        return $isCredited;
    }
}
