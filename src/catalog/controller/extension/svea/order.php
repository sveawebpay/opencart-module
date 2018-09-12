<?php

require_once(DIR_APPLICATION . '../svea/config/configInclude.php');

class ControllerExtensionSveaOrder extends Controller
{
    public function history()
    {
        $this->load->language('api/order');
        if (!isset($this->session->data['api_id'])) {
            $json['error'] = $this->language->get('error_permission');
        }
        else
        {
            $keys = array(
                'order_status_id',
                'notify',
                'override',
                'comment'
            );

            foreach ($keys as $key) {
                if (!isset($this->request->post[$key])) {
                    $this->request->post[$key] = '';
                }
            }

            if (isset($this->request->get['order_id']))
            {
                $orderId = $this->request->get['order_id'];
            }
            else
            {
                $orderId = 0;
            }
            $this->load->model('checkout/order');
            $orderInfo = $this->model_checkout_order->getOrder($orderId);

            if (strpos($orderInfo['payment_code'], "svea_") !== false || strpos($orderInfo['payment_code'], "sco") !== false)
            {
                $this->load->model('extension/svea/order');
                $sveaOrderId = $this->model_extension_svea_order->getSveaOrderId($orderInfo['payment_code'], $orderInfo['order_id']);
                $action = $this->model_extension_svea_order->getActionFromStatus($this->request->post['order_status_id']);
                $config = $this->model_extension_svea_order->getConfiguration($orderInfo['payment_code'], $orderInfo['payment_iso_code_2']);
                if(isset($config))
                {
                    if ($action == "deliver") {
                        $json['error'] = $this->model_extension_svea_order->deliverOrder($config, $orderInfo['payment_code'], $sveaOrderId, $orderInfo['payment_iso_code_2']);
                    } else if ($action == "credit") {
                        $json['error'] = $this->model_extension_svea_order->creditOrder($config, $orderInfo['payment_code'], $sveaOrderId, $orderInfo['payment_iso_code_2'], $orderInfo['order_id']);
                    } else {

                    }
                    if(isset($json['error'])) {
                        $this->log->write("Svea module error: " . $json['error'] . " Order Id at Svea: " . $sveaOrderId);
                    }
                }
                else
                {
                    $json['error'] = "Svea: Error fetching configuration";
                }
            }
        }
        if (isset($json['error']) && $json['error']) {
            if (isset($this->request->server['HTTP_ORIGIN'])) {
                $this->response->addHeader('Access-Control-Allow-Origin: ' . $this->request->server['HTTP_ORIGIN']);
                $this->response->addHeader('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
                $this->response->addHeader('Access-Control-Max-Age: 1000');
                $this->response->addHeader('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
            }

            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($json));
            return $json;
        }
    }
}