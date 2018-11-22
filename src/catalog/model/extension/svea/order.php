<?php

class ModelExtensionSveaOrder extends Model
{
    private $paymentString ="payment_";
    private $moduleString = "module_";

    public function deliverOrder($config, $paymentMethod, $sveaOrderId, $countryCode)
    {
        $this->setVersionStrings();
        $status = $this->queryOrderStatus($config, $paymentMethod, $sveaOrderId, $countryCode);

        if ($status == "DELIVERED" || $paymentMethod == "svea_directbank") {
            $this->request->post['comment'] = $this->request->post['comment'] . " Svea: Order has already been delivered at Svea, no request was sent to the server.";
            return;
        }

        try {
            $response = \Svea\WebPay\WebPay::deliverOrder($config)
                ->setOrderId($sveaOrderId)
                ->setTransactionId($sveaOrderId)
                ->setOrderDate(date('c'))
                ->setCountryCode($countryCode);

            if ($paymentMethod == "svea_invoice") {
                $response = $response->setInvoiceDistributionType($this->config->get($this->paymentString . $paymentMethod . '_distribution_type'))
                    ->deliverInvoiceOrder()
                    ->doRequest();
            } else if ($paymentMethod == "svea_partpayment") {
                $response = $response->setInvoiceDistributionType("POST")
                    ->deliverPaymentPlanOrder()
                    ->doRequest();
            } else if ($paymentMethod == "svea_card") {
                $response = $response->deliverCardOrder()
                    ->doRequest();
            } else if ($paymentMethod == "sco") {
                $response = \Svea\WebPay\WebPayAdmin::deliverOrderRows($config)
                    ->setCheckoutOrderId($sveaOrderId)
                    ->setCountryCode($countryCode)
                    ->deliverCheckoutOrder()
                    ->doRequest();

                if (isset($response['HeaderLocation'])) {
                    $response = new \StdClass();
                    $response->accepted = 1;
                }
            }

            if ($response->accepted == 1) {
                if ($paymentMethod == "svea_invoice") {
                    $this->request->post['comment'] = $this->request->post['comment'] . " Svea: Order was delivered. " . "Svea invoiceId " . $response->invoiceId;
                } else if ($paymentMethod == "svea_partpayment") {
                    $this->request->post['comment'] = $this->request->post['comment'] . " Svea: Order was delivered. " . "Svea contractNumber: " . $response->contractNumber;
                } else {
                    $this->request->post['comment'] = $this->request->post['comment'] . " Svea: Order was delivered.";
                }
            } else {
                if ($this->request->post['override'] == 1) {
                    if(isset($response->errormessage)) {
                        $this->request->post['comment'] = $this->request->post['comment'] . " Svea: Request wasn't accepted by Svea. Reason: " . $response->errormessage . " However it was overridden.";
                    }
                    else
                    {
                        $this->request->post['comment'] = $this->request->post['comment'] . " Svea: Request wasn't accepted by Svea. However it was overridden.";
                    }
                } else {
                    $json['error'] = "Svea: Request wasn't accepted by Svea. Reason: " . $response->errormessage . " Override the status if you still want to change it.";
                }
            }
        } catch (Exception $e) {
            if ($this->request->post['override'] == 1) {
                $this->request->post['comment'] = $this->request->post['comment'] . " Svea: Request wasn't accepted by Svea. Reason: " . $e->getMessage() . ". However it was overridden.";
            } else {
                $json['error'] = "Svea: An error occurred. Error: " . $e->getMessage() . ". Override the status if you still want to change it.";
            }
        }
        if(isset($json['error']))
        {
            return $json['error'];
        }
    }

    public function creditOrder($config, $paymentMethod, $sveaOrderId, $countryCode, $opencartOrderId)
    {
        $this->setVersionStrings();
        $status = $this->queryOrderStatus($config, $paymentMethod, $sveaOrderId, $countryCode);

        if ($status == "CANCELLED" || $status == "CREDITED" || $status == "ERROR") {
            $this->request->post['comment'] = $this->request->post['comment'] . " Svea: Order is already credited or cancelled at Svea, no request was sent to the server.";
        } else if ($status == "ERROR") {
            $json['error'] = "Svea: An error occurred. Status was not recognized. No request was sent.";
        }

        if ($status == "CREATED") {
            try {
                $response = \Svea\WebPay\WebPayAdmin::cancelOrder($config)
                    ->setCountryCode($countryCode)
                    ->setOrderId($sveaOrderId)
                    ->setTransactionId($sveaOrderId)
                    ->setCheckoutOrderId($sveaOrderId);

                if ($paymentMethod == "svea_invoice") {
                    $response = $response->cancelInvoiceOrder()
                        ->doRequest();
                } else if ($paymentMethod == "svea_partpayment") {
                    $response = $response->cancelPaymentPlanOrder()
                        ->doRequest();
                } else if ($paymentMethod == "svea_card") {
                    $response = $response->cancelCardOrder()
                        ->doRequest();
                } else if ($paymentMethod == "sco") {
                    $response = $response->cancelCheckoutOrder()
                        ->doRequest();

                    if ($response == "") {
                        $response = new \StdClass();
                        $response->accepted = 1;
                    }
                }

                if ($response->accepted == 1) {
                    $this->request->post['comment'] = $this->request->post['comment'] . " Svea: Order was cancelled at Svea.";
                } else {
                    if ($this->request->post['override'] == 1) {
                        $this->request->post['comment'] = $this->request->post['comment'] . " Svea: Request wasn't accepted by Svea. Reason: " . $response->errormessage . " However it was overridden.";
                    } else {
                        if(isset($response->errormessage)) {
                            $json['error'] = "Svea: Request wasn't accepted by Svea. Reason: " . $response->errormessage . " Override the status if you still want to change it.";
                        }
                        else {
                            $json['error'] = "Svea: Request wasn't accepted by Svea. Override the status if you still want to change it.";
                        }
                    }
                }

            } catch (Exception $e) {
                if ($this->request->post['override'] == 1) {
                    $this->request->post['comment'] = $this->request->post['comment'] . " Svea: Request wasn't accepted by Svea. Reason: " . $e->getMessage() . ". However it was overridden.";
                } else {
                    $json['error'] = "Svea: An error occurred. Error: " . $e->getMessage() . ". Override the status if you still want to change it.";
                }
            }
        } else if ($status == "DELIVERED") {
            try {
                $response = \Svea\WebPay\WebPayAdmin::creditOrderRows($config)
                    ->setCountryCode($countryCode)
                    ->setOrderId($sveaOrderId)
                    ->setTransactionId($sveaOrderId)
                    ->setRowsToCredit($this->getRowsToDeliver($config, $paymentMethod, $sveaOrderId, $countryCode));

                if ($paymentMethod == "svea_invoice") {
                    $response = $response->setInvoiceId($this->getDeliverId($paymentMethod, $opencartOrderId, $countryCode))
                        ->setInvoiceDistributionType($this->config->get($this->paymentString . 'svea_invoice_distribution_type'))
                        ->creditInvoiceOrderRows()
                        ->doRequest();
                } else if ($paymentMethod == "svea_partpayment") {
                    $response = $response->setContractNumber($this->getDeliverId($paymentMethod, $opencartOrderId, $countryCode))
                        ->creditPaymentPlanOrderRows()
                        ->doRequest();
                } else if ($paymentMethod == "svea_card") {
                    $response = $response->addNumberedOrderRows($this->getNumberedRows($config, $paymentMethod, $sveaOrderId, $countryCode))
                        ->creditCardOrderRows()
                        ->doRequest();
                } else if ($paymentMethod == "svea_directbank") {
                    $response = $response->addNumberedOrderRows($this->getNumberedRows($config, $paymentMethod, $sveaOrderId, $countryCode))
                        ->creditDirectBankOrderRows()
                        ->doRequest();
                } else if ($paymentMethod == "sco") {
                    if ($this->canOrderBeCreditedByDeliveryRows($sveaOrderId, $countryCode)) {
                        $response = $response->setCheckoutOrderId($sveaOrderId)
                            ->setDeliveryId($this->getDeliverId($paymentMethod, $opencartOrderId, $countryCode))
                            ->creditCheckoutOrderRows()
                            ->doRequest();

                        if (isset($response['HeaderLocation'])) {
                            $response = new \StdClass();
                            $response->accepted = 1;
                        }


                    } else if ($this->canOrderBeCreditedByAmount($sveaOrderId, $countryCode)) {
                        $response = \Svea\WebPay\WebPayAdmin::creditAmount($config)
                            ->setCheckoutOrderId($sveaOrderId)
                            ->setCountryCode($countryCode)
                            ->setDeliveryId($this->getDeliverId($paymentMethod, $opencartOrderId, $countryCode))
                            ->setAmountIncVat($this->getDeliveryAmount($sveaOrderId, $countryCode))
                            ->creditCheckoutAmount()
                            ->doRequest();

                        if ($response === "") {
                            $response = new \StdClass();
                            $response->accepted = 1;
                        }
                    } else {
                        $response = new \StdClass();
                        $response->accepted = 0;
                    }
                }

                if ($response->accepted == 1) {
                    $this->request->post['comment'] = $this->request->post['comment'] . " Svea: Order was credited.";
                }
                else
                {
                    if ($this->request->post['override'] == 1) {
                        if(isset($response->errormessage)) {
                            $this->request->post['comment'] = $this->request->post['comment'] . " Svea: Request wasn't accepted by Svea. Reason: " . $response->errormessage . " However it was overridden.";
                        }
                        else
                        {
                            $this->request->post['comment'] = $this->request->post['comment'] . " Svea: Request wasn't accepted by Svea. However it was overridden.";
                        }
                    } else {
                        if(isset($response->errormessage)) {
                            $json['error'] = "Svea: Request wasn't accepted by Svea. Reason: " . $response->errormessage . " Override the status if you still want to change it.";
                        }
                        else {
                            $json['error'] = "Svea: Request wasn't accepted by Svea. Override the status if you still want to change it.";
                        }
                    }
                }
            } catch (Exception $e) {
                if ($this->request->post['override'] == 1) {
                    $this->request->post['comment'] = $this->request->post['comment'] . " Svea: Request wasn't accepted by Svea. Reason: " . $e->getMessage() . ". However it was overridden.";
                } else {
                    $json['error'] = "Svea: An error occurred. Error: " . $e->getMessage() . ". Override the status if you still want to change it.";
                }
            }
        }
        if(isset($json['error']))
        {
            return $json['error'];
        }
    }

    public function canOrderBeCreditedByAmount($checkoutOrderId, $countryCode)
    {
        return $this->canOrderBeCredited($checkoutOrderId, 'CanCreditAmount', $countryCode);
    }

    public function canOrderBeCreditedByDeliveryRows($checkoutOrderId, $countryCode)
    {
        return $this->canOrderBeCredited($checkoutOrderId, 'CanCreditOrderRows', $countryCode);
    }

    private function canOrderBeCredited($checkoutOrderId, $actionType, $countryCode)
    {
        if (empty($actionType)) {
            return false;
        }

        $orderData = $this->getScoOrderData($checkoutOrderId, $countryCode);
        $orderRows = $orderData['OrderRows'];
        $deliveries = $orderData['Deliveries'];
        if (count($orderRows) === 0 && count($deliveries) === 1) {
            $delivery = $deliveries[0];
            $deliveryActions = $delivery['Actions'];
            $deliveryRows = $delivery['OrderRows'];
            $deliveryCredits = $delivery['Credits'];
            if (in_array($actionType, $deliveryActions) === true && count($deliveryRows) > 0 && empty($deliveryCredits)) {
                if ($actionType === 'CanCreditOrderRows') {
                    foreach ($deliveryRows as $delivery_row) {
                        $row_actions = $delivery_row['Actions'];
                        if (in_array('CanCreditRow', $row_actions) !== true) {
                            return false;
                        }
                    }
                }
                return true;
            }
        }

        return false;
    }

    public function getRowsToDeliver($config, $paymentMethod, $sveaOrderId, $countryCode)
    {
        $response = \Svea\WebPay\WebPayAdmin::queryOrder($config)
            ->setOrderId($sveaOrderId)
            ->setTransactionId($sveaOrderId)
            ->setCheckoutOrderId($sveaOrderId)
            ->setCountryCode($countryCode);

        if ($paymentMethod == "svea_invoice") {
            $response = $response->queryInvoiceOrder()
                ->doRequest();
        } else if ($paymentMethod == "svea_partpayment") {
            $response = $response->queryPaymentPlanOrder()
                ->doRequest();
        } else if ($paymentMethod == "svea_card") {
            $response = $response->queryCardOrder()
                ->doRequest();
        } else if ($paymentMethod == "svea_directbank") {
            $response = $response->queryDirectBankOrder()
                ->doRequest();
        } else {
            $response = $this->getScoOrderData($sveaOrderId, $countryCode);

            if (isset($response['Id'])) {
                $rows = array();
                $delivery = $response['Deliveries'][0];
                $deliveryRows = $delivery['OrderRows'];

                foreach ($deliveryRows as $deliveryRow) {
                    $rows[] = $deliveryRow['OrderRowId'];
                }

                return $rows;
            }
        }

        if ($response->accepted == 1) {
            $rowNumbers = array();
            foreach ($response->numberedOrderRows as $value) {
                $rowNumbers[] = $value->rowNumber;
            }
            return $rowNumbers;
        } else {
            $json['error'] = "Svea: An error occurred when fetching order rows.";
        }
    }

    public function getDeliveryAmount($checkoutOrderId, $countryCode)
    {
        $orderData = $this->getScoOrderData($checkoutOrderId, $countryCode);
        $deliveries = $orderData['Deliveries'];
        return $deliveries[0]['DeliveryAmount'];
    }

    public function queryOrderStatus($config, $paymentMethod, $sveaOrderId, $countryCode)
    {
        $response = \Svea\WebPay\WebPayAdmin::queryOrder($config)
            ->setOrderId($sveaOrderId)
            ->setTransactionId($sveaOrderId)
            ->setCheckoutOrderId($sveaOrderId)
            ->setCountryCode($countryCode);

        if ($paymentMethod == "svea_invoice") {
            $response = $response->queryInvoiceOrder()
                ->doRequest();

            $status = strtoupper($response->orderDeliveryStatus);
        } else if ($paymentMethod == "svea_partpayment") {
            $response = $response->queryPaymentPlanOrder()
                ->doRequest();

            $status = strtoupper($response->orderDeliveryStatus);
        } else if ($paymentMethod == "svea_card") {
            $response = $response->queryCardOrder()
                ->doRequest();

            $status = strtoupper($response->status);

        } else if ($paymentMethod == "svea_directbank") {
            $response = $response->queryDirectBankOrder()
                ->doRequest();

            $status = strtoupper($response->status);
        } else {
            $response = $response->setCheckoutOrderId($sveaOrderId)
                ->queryCheckoutOrder()
                ->doRequest();

            $status = strtoupper($response['OrderStatus']);
            if($status == "DELIVERED")
            {
                foreach($response['Actions'] as $action)
                {
                    if($action == "CanCancelOrder")
                    {
                        $status = "OPEN";
                    }
                    else if($action == "CanCancelAmount")
                    {
                        $status = "OPEN";
                    }
                }
            }
        }

        if ($status == "CANCELLED" || $status == "ANNULLED") {
            return "CANCELLED";
        } else if ($status == "DELIVERED" || $status == "SUCCESS") {
            return "DELIVERED";
        } else if ($status == "CREATED" || $status == "AUTHORIZED" || $status == "OPEN" || $status == "CONFIRMED") {
            return "CREATED";
        } else if ($status == "CREDITED" || $status == "CREDSUCCESS") {
            return "CREDITED";
        } else {
            return "ERROR";
        }
    }

    public function getActionFromStatus($status)
    {
        /*
         * nop = No operation, we don't want to communicate with Svea if the function returns this
         */
        foreach ($this->config->get('config_complete_status') as $deliverGroupStatus) {
            if ($deliverGroupStatus == $status) {
                return "deliver";
            }
        }

        foreach ($this->config->get('config_processing_status') as $processingGroupStatus) {
            if ($processingGroupStatus == $status) {
                return "nop";
            }
        }

        if ($this->config->get('config_order_status_id') == $status || $this->config->get('config_fraud_status_id') == $status) {
            return "nop";
        }

        return "credit"; // If we haven't returned in any previous statement we assume that the user wants to credit this order
    }

    public function getSveaOrderId($paymentMethod, $opencartOrderId)
    {
        if ($paymentMethod == 'sco') {
            $this->load->model('extension/svea/checkout');
            $scoOrder = $this->model_extension_svea_checkout->getCheckoutOrder($opencartOrderId);
            if ($scoOrder != null || isset($scoOrder['checkout_id'])) {
                return (int)$scoOrder['checkout_id'];
            } else {
                return 0; // We couldn't find the checkoutOrderId even though the payment code was sco
            }
        } else {
            $orderHistoryComments = $this->db->query("SELECT comment FROM " . DB_PREFIX . "order_history WHERE order_id = " . (int)$this->request->get['order_id']);
            $sveaOrderId = 0;
            foreach ($orderHistoryComments->rows as $orderHistoryComment) {
                $sveaOrderIdExists = strpos($orderHistoryComment['comment'], 'Svea order id', 0);
                $sveaTransactionIdExists = strpos($orderHistoryComment['comment'], 'Svea transactionId', 0);
                if ($sveaOrderIdExists !== false) {
                    preg_match_all('/\d+/', $orderHistoryComment['comment'], $sveaOrderId);
                } elseif ($sveaTransactionIdExists !== false) {
                    preg_match_all('/\d+/', $orderHistoryComment['comment'], $sveaOrderId);
                }
            }
            if ($sveaOrderId != null) {
                return $sveaOrderId[0][0];
            } else {
                return $sveaOrderId;
            }
        }
    }

    public function getDeliverId($paymentMethod, $opencartOrderId, $countryCode)
    {
        if ($paymentMethod == "sco") {
            $checkoutOrderId = $this->getSveaOrderId($paymentMethod, $opencartOrderId);
            $order = $this->getScoOrderData($checkoutOrderId, $countryCode);
            $deliveries = $order['Deliveries'];
            return $deliveries[0]['Id'];
        } else {
            $commentHistoryArray = $this->db->query("SELECT comment FROM " . DB_PREFIX . "order_history WHERE order_id = " . (int)$opencartOrderId);
            foreach ($commentHistoryArray->rows as $comment) {
                if (strpos($comment['comment'], 'Svea invoiceId', 0) == true || strpos($comment['comment'], 'Svea contractNumber', 0) == true || strpos($comment['comment'], 'Svea transactionId', 0) == true) {
                    preg_match_all('/\d+/', $comment['comment'], $deliverId);
                }
            }
            if(isset($deliverId[0][0]))
            {
                return $deliverId[0][0];
            }
            else
            {
                return 0;
            }
        }
    }

    public function getScoOrderData($checkoutOrderId, $countryCode)
    {
        if (isset($this->scoOrderData)) {
            return $this->scoOrderData;
        }
        $config = $this->getConfiguration("sco", $countryCode);
        $this->scoOrderData = \Svea\WebPay\WebPayAdmin::queryOrder($config)
            ->setCheckoutOrderId($checkoutOrderId)
            ->setCountryCode($countryCode)
            ->queryCheckoutOrder()
            ->doRequest();

        return $this->scoOrderData;
    }

    public function getConfiguration($paymentMethod, $countryCode)
    {
        $this->setVersionStrings();

        if ($paymentMethod == "sco")
        {
            $config = ($this->config->get($this->moduleString . 'sco_test_mode') == '1') ? new OpencartSveaCheckoutConfigTest($this->config, 'checkout') : new OpencartSveaCheckoutConfig($this->config, 'checkout');
        } else {
            $configCountryCode = $paymentMethod == "svea_invoice" || $paymentMethod == "svea_partpayment" ? '_' . $countryCode : '';

            if ($this->config->get($this->paymentString . $paymentMethod . "_testmode" . $configCountryCode) !== NULL) {
                $config = ($this->config->get($this->paymentString . $paymentMethod . "_testmode" . $configCountryCode) == "1") ? new OpencartSveaConfigTest($this->config) : new OpencartSveaConfig($this->config);
            }
        }
        return $config;
    }

    public function setVersionStrings()
    {
        if(VERSION < 3.0)
        {
            $this->paymentString = "";
            $this->moduleString = "";
        }
    }

    public function getNumberedRows($config, $paymentMethod, $transactionId, $countryCode)
    {
        try {
            $response = \Svea\WebPay\WebPayAdmin::queryOrder($config)
                ->setTransactionId($transactionId)
                ->setCountryCode($countryCode);

            if ($paymentMethod == "svea_card") {
                $response = $response->queryCardOrder()
                    ->doRequest();
            } else if ($paymentMethod == "svea_directbank") {
                $response = $response->queryDirectBankOrder()
                    ->doRequest();
            }
            if($response->accepted == 1)
            {
                return $response->numberedOrderRows;
            }
            else
            {
                $this->log->write("getNumberedRows(): Could not fetch transaction.");
            }
        }
        catch (Exception $e)
        {
            $this->log->write($e->getMessage());
        }
    }
}