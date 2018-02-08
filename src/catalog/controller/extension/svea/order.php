<?php

require_once(DIR_APPLICATION . '../svea/config/configInclude.php');

class ControllerExtensionSveaOrder extends Controller
{
    protected $order_data_from_admin;

    public function history()
    {
        $this->load->model('checkout/order');
        if (isset($this->request->get['order_id'])) {
            $order_id = $this->request->get['order_id'];
        } else {
            $order_id = 0;
        }
        $order_info = $this->model_checkout_order->getOrder($order_id);
        $svea_comment = '';
        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            $json = array();
            $payment_method = $order_info['payment_code'];
            $request_order_status_id = $this->request->post['order_status_id'];
            $status_changed = (int)$order_info['order_status_id'] !== (int)$request_order_status_id;
            //only do something if status is changed and payment is of svea type
            if ($payment_method == 'svea_invoice'
                || $payment_method == 'svea_partpayment'
                || $payment_method == 'svea_directbank'
                || $payment_method == 'svea_card'
            ) {
                //if old orderstatus is cancelled as configured in module,
                // and user trys to change it. do not allow it.
                if ($request_order_status_id == $this->config->get('payment_' .$payment_method . '_canceled_status_id')
                    && $request_order_status_id != $this->config->get('payment_' .$payment_method . '_canceled_status_id')
                ) {
                    $json['error'] = ' Order is closed and status can not be changed.';
                    $this->request->post['order_status_id'] = $order_info['order_status_id'];
                }
                //Testmode
                $testmode_countrycode = $payment_method == 'svea_invoice' || $payment_method == 'svea_partpayment' ? '_' . $order_info['payment_iso_code_2'] : '';
                if ($this->config->get('payment_' . $payment_method . '_testmode' . $testmode_countrycode) !== NULL) {
                    $conf = ($this->config->get('payment_' . $payment_method . '_testmode' . $testmode_countrycode) == "1")
                        ? new OpencartSveaConfigTest($this->config) : new OpencartSveaConfig($this->config);
                }
                //get svea order id
                $svea_order = $this->model_checkout_order->getOrder($this->request->get['order_id']); //deprecated cause don't save to comment on admin action.
                $svea_order_history_comment = $this->db->query("SELECT comment FROM " . DB_PREFIX . "order_history WHERE order_id = " . (int)$this->request->get['order_id']);
                foreach ($svea_order_history_comment->rows as $svea_order_history) {
                    $svea_order_id_exists = strpos($svea_order_history['comment'], 'Svea order id', 0);
                    $svea_transaction_id_exists = strpos($svea_order_history['comment'], 'Svea transactionId', 0);
                    if ($svea_order_id_exists !== false) {
                        preg_match_all('/\d+/', $svea_order_history['comment'], $svea_order_id);
                    }
                    elseif ($svea_transaction_id_exists !== false) {
                        preg_match_all('/\d+/', $svea_order_history['comment'], $svea_order_id);
                    }
                }
                //Cancel
                //if this action is cancel as configured in module,
                //and it's not directbank,
                //and if status wasn't already cancel
                if ($this->config->get('payment_' .$payment_method . '_canceled_status_id') == $request_order_status_id
                    && $payment_method != 'svea_directbank'
                ) {
                    $credit_response = \Svea\WebPay\WebPayAdmin::cancelOrder($conf)
                        ->setOrderId($svea_order_id[0][0])
                        ->setCountryCode($order_info['payment_iso_code_2']);
                    if ($payment_method == 'svea_invoice') {
                        $credit_response = $credit_response->cancelInvoiceOrder();
                    } else if ($payment_method == 'svea_partpayment') {
                        $credit_response = $credit_response->cancelPaymentPlanOrder();
                    } else {
                        $credit_response = $credit_response->cancelCardOrder();
                    }
                    try {
                        $svea_response = $credit_response->doRequest();
                    } catch (Exception $e) {
                        $this->log->write($e->getMessage());
                        $response = $e->getMessage();
                        $json['error'] = 'Svea error: ' . $response . ' Order was not canceled.';
                        $this->request->post['order_status_id'] = $order_info['order_status_id'];
                    }
                    if ($svea_response->accepted == TRUE) {
                        $svea_comment = ' | Order canceled at Svea. ';
                    } else {
                        if($this->syncSveaStatus($svea_order_id[0][0], $order_info['payment_iso_code_2'], $payment_method, $conf) == true)
                        {
                            $svea_comment = 'Status was successfully synced with Svea.';
                        }
                        else
                        {
                            $json['error'] = ' Svea Error: ' . $svea_response->errormessage . '. ResultCode: ' . $svea_response->resultcode;
                        }
                    }
                    //Deliver
                    //if this action is deliver as configured in module,
                    //and it is not directbank
                    //and if status wasn't already deliver
                } elseif ($this->config->get('payment_' . $payment_method . '_deliver_status_id') == $request_order_status_id
                    && $payment_method != 'svea_directbank'
                ) {
                    try {
                        $credit_response = \Svea\WebPay\WebPay::deliverOrder($conf)
                            ->setOrderId($svea_order_id[0][0])
                            ->setOrderDate(date('c'))
                            ->setCountryCode($order_info['payment_iso_code_2']);
                        if ($payment_method == 'svea_invoice') {
                            $svea_response = $credit_response->setInvoiceDistributionType($this->config->get('payment_' . $payment_method . '_distribution_type'))
                                ->deliverInvoiceOrder()
                                ->doRequest();
                        } else if ($payment_method == 'svea_partpayment') {
                            $svea_response = $credit_response->setInvoiceDistributionType('POST')
                                ->deliverPaymentPlanOrder()
                                ->doRequest();
                        } else {
                            $svea_response = $credit_response
                                ->deliverCardOrder()
                                ->doRequest();
                        }
                        if ($svea_response->accepted == TRUE) {
                            if (isset($svea_response->orderType) && $svea_response->orderType == 'Invoice') {
                                $svea_comment = ' | Order delivered at Svea. ' . ' Svea invoiceId: ' . $svea_response->invoiceId;
                            } else if (isset($svea_response->orderType) && $svea_response->orderType == 'PaymentPlan') {
                                $svea_comment = ' | Order delivered at Svea.' . ' Svea contractNumber: ' . $svea_response->contractNumber;
                            } else {
                                $svea_comment = ' | Transaction confirmed at Svea.';
                            }
                        } else {

                            if($this->syncSveaStatus($svea_order_id[0][0], $order_info['payment_iso_code_2'], $payment_method, $conf) == true)
                            {
                                $svea_comment = 'Status was successfully synced with Svea.';
                            }
                            else
                            {
                                $json['error'] = 'Svea Error: ' . $svea_response->errormessage . '. ResultCode: ' . $svea_response->resultcode;
                            }
                        }
                    } catch (Exception $e) {
                        $this->log->write($e->getMessage());
                        $response = $e->getMessage();
                        $json['error'] = 'Svea error: ' . $response . ' Order was not delivered.';
                        $this->request->post['order_status_id'] = $order_info['order_status_id'];
                    }
                    //Credit
                    //if this action is credit as configured in module
                    //and if status wasn't already credit
                } elseif ($this->config->get('payment_' . $payment_method . '_refunded_status_id') == $request_order_status_id) {
                    $svea_query = \Svea\WebPay\WebPayAdmin::queryOrder($conf)
                        ->setOrderId($svea_order_id[0][0])
                        ->setCountryCode($order_info['payment_iso_code_2']);
                    if ($payment_method == 'svea_invoice') {
                        $svea_query = $svea_query->queryInvoiceOrder();
                    } elseif ($payment_method == 'svea_partpayment') {
                        $svea_query = $svea_query->queryPaymentPlanOrder();
                    } else if ($payment_method == 'svea_directbank') {
                        $svea_query = $svea_query->queryDirectBankOrder();
                    } else {
                        $svea_query = $svea_query->queryCardOrder();
                    }
                    try {
                        $svea_query = $svea_query->doRequest();
                    } catch (Exception $e) {
                        $this->log->write($e->getMessage());
                        $response = $e->getMessage();
                        $json['error'] = 'Svea error: ' . $response . ' Order was not credited.';
                        $this->request->post['order_status_id'] = $order_info['order_status_id'];
                    }
                    if ($svea_query->accepted == TRUE) {
                        $row_numbers = array();
                        foreach ($svea_query->numberedOrderRows as $value) {
                            $row_numbers[] = $value->rowNumber;
                        }
                        //get svea invoice id
                        $invoiceId = array();
                        $contractNumber = array();
                        $transactionId = array();
                        foreach ($svea_order_history_comment->rows as $svea_order_history) {
                            $svea_invoice_id_exists = strpos($svea_order_history['comment'], 'Svea invoiceId', 0);
                            $svea_contract_number_exists = strpos($svea_order_history['comment'], 'Svea contractNumber', 0);
                            $svea_transaction_id_exists = strpos($svea_order_history['comment'], 'Svea transactionId', 0);
                            if ($svea_invoice_id_exists !== false) {
                                preg_match_all('/\d+/', $svea_order_history['comment'], $invoiceId);
                            } elseif ($svea_contract_number_exists !== false) {
                                preg_match_all('/\d+/', $svea_order_history['comment'], $contractNumber);
                            } elseif ($svea_transaction_id_exists !== false) {
                                preg_match_all('/\d+/', $svea_order_history['comment'], $transactionId);
                            }
                        }
                        if (sizeof($invoiceId) <= 0 && sizeof($transactionId) <= 0 && sizeof($contractNumber) <= 0) {
                            $json['error'] = 'Svea error: Order was not credited. Transaction or order id not found in comment field. Order must first be delivered/shipped.';
                            $this->request->post['order_status_id'] = $order_info['order_status_id'];
                        } else {
                            $credit_response = \Svea\WebPay\WebPayAdmin::creditOrderRows($conf)
                                ->setCountryCode($order_info['payment_iso_code_2'])
                                ->setRowsToCredit($row_numbers)
                                ->addNumberedOrderRows($svea_query->numberedOrderRows);
                            if ($payment_method == 'svea_invoice') {
                                $credit_response = $credit_response
                                    ->setInvoiceId($invoiceId[0][0])
                                    ->setInvoiceDistributionType($this->config->get('payment_' . $payment_method . '_distribution_type'))
                                    ->creditInvoiceOrderRows();
                            } elseif ($payment_method == 'svea_partpayment') {
                                $credit_response = $credit_response
                                    ->setContractNumber($contractNumber[0][0])
                                    ->creditPaymentPlanOrderRows();
                            } elseif ($payment_method == 'svea_directbank') {
                                $credit_response = $credit_response
                                    ->setOrderId($transactionId[0][0])
                                    ->creditDirectBankOrderRows();
                            } else {
                                $credit_response = $credit_response
                                    ->setOrderId($transactionId[0][0])
                                    ->creditCardOrderRows();
                            }
                            try {
                                $credit_response = $credit_response->doRequest();
                            } catch (Exception $e) {
                                $this->log->write($e->getMessage());
                                $response = $e->getMessage();
                                $json['error'] = 'Svea error: ' . $response . ' Order was not credited.';
                                $this->request->post['order_status_id'] = $order_info['order_status_id'];
                            }
                            if ($credit_response->accepted == TRUE) {
                                if (isset($credit_response->orderType) && $credit_response->orderType == 'Invoice') {
                                    $svea_comment = ' | Order credited at Svea.';
                                } elseif (isset($credit_response->orderType) && $credit_response->orderType == 'PaymentPlan') {
                                    $svea_comment = ' | Payment plan rows cancelled at Svea. ';
                                } else {
                                    $svea_comment = ' | Order credited at Svea.';
                                }
                            } else {
                                if($this->syncSveaStatus($svea_order_id[0][0], $order_info['payment_iso_code_2'], $payment_method, $conf) == true)
                                {
                                    $svea_comment = 'Status successfully synced with Svea.';
                                }
                                else
                                {
                                    $json['error'] = ' Svea Error: ' . $credit_response->errormessage . '. ResultCode: ' . $credit_response->resultcode;
                                }
                            }
                        }
                    }
                } else {
                    $json['error'] = ' Svea Error: Svea order id is missing. ';
                    $this->request->post['order_status_id'] = $order_info['order_status_id'];
                }
                if (isset($this->request->post['comment'])) {
                    $this->request->post['comment'] = $this->request->post['comment'] . $svea_comment;
                } else {
                    $this->request->post['comment'] = '' . $svea_comment;
                }
            } elseif ($payment_method == 'sco' && $status_changed === true) {
                $this->load->model('extension/svea/checkout');
                $order_info = $this->model_checkout_order->getOrder($order_id);
                $module_sco_order = $this->model_extension_svea_checkout->getCheckoutOrder($order_id);
                $order_status_id = $request_order_status_id;
                if ($module_sco_order !== null && isset($module_sco_order['checkout_id'])) {
                    $module_sco_order_id = (int)$module_sco_order['checkout_id'];
                    $country = $this->model_extension_svea_checkout->getCountryCode($module_sco_order['checkout_id']);
                    $config = new OpencartSveaCheckoutConfig($this->config, 'checkout');
                    if ($this->config->get('module_sco_test_mode')) {
                        $config = new OpencartSveaCheckoutConfigTest($this->config, 'checkout');
                    }
                    /**
                     * CHECKOUT
                     * Canceled Order
                     */
                    if ($this->config->get('module_sco_canceled_status_id') === $order_status_id) {
                        try {
                            if ($this->canOrderBeCancelled($module_sco_order_id, $country) === true) {
                                $cancel_response = \Svea\WebPay\WebPayAdmin::cancelOrder($config)
                                    ->setCheckoutOrderId($module_sco_order_id)
                                    ->setCountryCode($country)
                                    ->cancelCheckoutOrder()
                                    ->doRequest();
                                if ($cancel_response === '') {
                                    $svea_comment = ' | Order cancelled at Svea. ';
                                } else {
                                    $json['error'] = "Svea Error: Order wasn't successfully cancelled!";
                                    $this->request->post['order_status_id'] = $order_info['order_status_id'];
                                }
                            } else {
                                $json['error'] = ' Svea Error: Order cannot be cancelled!';
                                $this->request->post['order_status_id'] = $order_info['order_status_id'];
                            }
                        } catch (\Exception $e) {
                            $json['error'] = ' Svea Error: ' . $e->getMessage();
                            $this->request->post['order_status_id'] = $order_info['order_status_id'];
                        }
                    } elseif ($this->config->get('module_sco_credited_status_id') === $order_status_id) {
                        /**
                         * CHECKOUT
                         * Credited (Refunded) Order
                         */
                        try {
                            if ($this->canOrderBeCreditedByDeliveryRows($module_sco_order_id, $country) === true) {
                                $delivery_id = $this->getDeliveryId();
                                $credit_rows = $this->getCreditRows();
                                $credit_response = \Svea\WebPay\WebPayAdmin::creditOrderRows($config)
                                    ->setCheckoutOrderId($module_sco_order_id)
                                    ->setCountryCode($country)
                                    ->setDeliveryId($delivery_id)
                                    ->setRowsToCredit($credit_rows)
                                    ->creditCheckoutOrderRows()
                                    ->doRequest();
                                if (isset($credit_response['HeaderLocation'])) {
                                    $svea_comment = ' | Order credited at Svea. Svea Checkout Order Id: ' . $module_sco_order_id;
                                } else {
                                    $json['error'] = ' Credit Error: Order cannot be Credited!';
                                    $this->request->post['order_status_id'] = $order_info['order_status_id'];
                                }
                            } elseif ($this->canOrderBeCreditedByAmount($module_sco_order_id, $country) === true) {
                                $delivery_id = $this->getDeliveryId();
                                $delivery_credit = $this->getDeliveryAmount();
                                $credit_response = \Svea\WebPay\WebPayAdmin::creditAmount($config)
                                    ->setCheckoutOrderId($module_sco_order_id)
                                    ->setCountryCode($country)
                                    ->setDeliveryId($delivery_id)
                                    ->setAmountIncVat($delivery_credit)
                                    ->creditCheckoutAmount()
                                    ->doRequest();
                                if ($credit_response === '') {
                                    $svea_comment = ' | Order credited at Svea. Svea Checkout Order Id: ' . $module_sco_order_id;
                                } else {
                                    $json['error'] = ' Credit Error: Order cannot be Credited!';
                                    $this->request->post['order_status_id'] = $order_info['order_status_id'];
                                }
                            } else {
                                $json['error'] = ' Credit Error: Order cannot be Credited!';
                                $this->request->post['order_status_id'] = $order_info['order_status_id'];
                            }
                        } catch (Exception $e) {
                            $json['error'] = 'Svea error: ' . $e->getMessage() . ' Order was not credited.';
                            $this->request->post['order_status_id'] = $order_info['order_status_id'];
                        }
                    } elseif ($this->config->get('module_sco_delivered_status_id') === $order_status_id) {
                        /**
                         * CHECKOUT
                         * Delivered (Confirm) Order
                         */
                        try {
                            if ($this->canOrderBeDelivered($module_sco_order_id, $country) === true) {
                                $deliver_response = \Svea\WebPay\WebPayAdmin::deliverOrderRows($config)
                                    ->setCheckoutOrderId($module_sco_order_id)
                                    ->setCountryCode($country)
                                    ->deliverCheckoutOrder()
                                    ->doRequest();
                                if (isset($deliver_response['HeaderLocation'])) {
                                    $svea_comment = ' | Order delivered at Svea. Svea Checkout Order Id: ' . $module_sco_order_id;
                                } else {
                                    $json['error'] = ' Deliver Error: Order cannot be Delivered!';
                                    $this->request->post['order_status_id'] = $order_info['order_status_id'];
                                }
                            } else {
                                $json['error'] = ' Deliver Error: Order cannot be Delivered!';
                                $this->request->post['order_status_id'] = $order_info['order_status_id'];
                            }
                        } catch (Exception $e) {
                            $json['error'] = ' Deliver Error: ' . $e->getMessage();
                            $this->request->post['order_status_id'] = $order_info['order_status_id'];
                        }
                    }
                    $this->addSveaCommentIntoRequest($svea_comment);
                } else {
                    $json['error'] = ' Svea Error: Svea Checkout order not exist.';
                    $this->request->post['order_status_id'] = $order_info['order_status_id'];
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

    public function edit()
    {
        $this->load->model('checkout/order');

        if (isset($this->request->get['order_id'])) {
            $order_id = $this->request->get['order_id'];
        } else {
            $order_id = 0;
        }

        $order_info = $this->model_checkout_order->getOrder($order_id);

        $svea_comment = '';
        if ($this->request->server['REQUEST_METHOD'] == 'POST') {

            $json = array();

            $status_changed = (int)$order_info['order_status_id'] !== (int)$this->request->post['order_status_id'];

            //only do something if status is changed and payment is of svea type
            if ($this->request->post['payment_method'] == 'svea_invoice'
                || $this->request->post['payment_method'] == 'svea_partpayment'
                || $this->request->post['payment_method'] == 'svea_directbank'
                || $this->request->post['payment_method'] == 'svea_card'
            ) {
                //if old orderstatus is cancelled as configured in module,
                // and user trys to change it. do not allow it.
                if ($this->request->post['order_status_id'] == $this->config->get($this->request->post['payment_method'] . '_canceled_status_id')
                    && $this->request->post['order_status_id'] != $this->config->get($this->request->post['payment_method'] . '_canceled_status_id')
                ) {
                    $json['error'] = ' Order is closed and status can not be changed.';
                    $this->request->post['order_status_id'] = $order_info['order_status_id'];
                }
                //Testmode
                $testmode_countrycode = $this->request->post['payment_method'] == 'svea_invoice' || $this->request->post['payment_method'] == 'svea_partpayment' ? '_' . $order_info['payment_iso_code_2'] : '';
                if ($this->config->get($this->request->post['payment_method'] . '_testmode' . $testmode_countrycode) !== NULL) {
                    $conf = ($this->config->get($this->request->post['payment_method'] . '_testmode' . $testmode_countrycode) == "1")
                        ? new OpencartSveaConfigTest($this->config) : new OpencartSveaConfig($this->config);
                }
                //get svea order id
                $svea_order = $this->model_checkout_order->getOrder($this->request->get['order_id']); //deprecated cause don't save to comment on admin action.
                $svea_order_history_comment = $this->db->query("SELECT comment FROM " . DB_PREFIX . "order_history WHERE order_id = " . (int)$this->request->get['order_id']);
//                        var_dump($svea_order_history_comment->num_rows);


                foreach ($svea_order_history_comment->rows as $svea_order_history) {
                    $svea_order_id_exists = strpos($svea_order_history['comment'], 'Svea order id', 0);
                    $svea_transaction_id_exists = strpos($svea_order_history['comment'], 'Svea transactionId', 0);
                    if ($svea_order_id_exists !== false) {
                        preg_match_all('/\d+/', $svea_order_history['comment'], $svea_order_id);
                    } elseif ($svea_transaction_id_exists !== false) {
                        preg_match_all('/\d+/', $svea_order_history['comment'], $svea_order_id);
                    }
                }

                //Cancel
                //if this action is cancel as configured in module,
                //and it's not directbank,
                //and if status wasn't already cancel
                if ($this->config->get($this->request->post['payment_method'] . '_canceled_status_id') == $this->request->post['order_status_id']
                    && $order_info['order_status_id'] != $this->request->post['order_status_id']
                    && $this->request->post['payment_method'] != 'svea_directbank'
                ) {

                    $credit_response = \Svea\WebPay\WebPayAdmin::cancelOrder($conf)
                        ->setOrderId($svea_order_id[0][0])
                        ->setCountryCode($order_info['payment_iso_code_2']);

                    if ($this->request->post['payment_method'] == 'svea_invoice') {
                        $credit_response = $credit_response->cancelInvoiceOrder();
                    } else if ($this->request->post['payment_method'] == 'svea_partpayment') {
                        $credit_response = $credit_response->cancelPaymentPlanOrder();
                    } else {
                        $credit_response = $credit_response->cancelCardOrder();
                    }
                    try {
                        $svea_response = $credit_response->doRequest();
                    } catch (Exception $e) {
                        $this->log->write($e->getMessage());
                        $response = $e->getMessage();
                        $json['error'] = 'Svea error: ' . $response . ' Order was not canceled.';
                        $this->request->post['order_status_id'] = $order_info['order_status_id'];
                    }
                    if ($svea_response->accepted == TRUE) {
                        //save svea invoice no
//                                    $history['order_status_id'] = (int) $this->request->post['order_status_id'];
//                                    $history['notify'] = NULL;
                        $history = 'Order canceled at Svea.';
                        $this->model_checkout_order->addOrderHistory($this->request->get['order_id'], $this->request->post['order_status_id'], $history);
                        $svea_comment = ' | Order canceled at Svea. ';
                    } else {
                        $json['error'] = ' Svea Error: ' . $svea_response->errormessage . '. Resultcode: ' . $svea_response->resultcode;
                        $this->request->post['order_status_id'] = $order_info['order_status_id'];
                    }
                    //Deliver
                    //if this action is deliver as configured in module,
                    //and it is not directbank
                    //and if status wasn't already deliver
                } elseif ($this->config->get($this->request->post['payment_method'] . '_deliver_status_id') == $this->request->post['order_status_id']
                    && $order_info['order_status_id'] != $this->request->post['order_status_id']
                    && $this->request->post['payment_method'] != 'svea_directbank'
                ) {

                    try {
                        $credit_response = \Svea\WebPay\WebPay::deliverOrder($conf)
                            ->setOrderId($svea_order_id[0][0])
                            ->setOrderDate(date('c'))
                            ->setCountryCode($order_info['payment_iso_code_2']);
                        if ($this->request->post['payment_method'] == 'svea_invoice') {
                            $svea_response = $credit_response->setInvoiceDistributionType($this->config->get($this->request->post['payment_method'] . '_distribution_type'))
                                ->deliverInvoiceOrder()
                                ->doRequest();
                        } else if ($this->request->post['payment_method'] == 'svea_partpayment') {
                            $svea_response = $credit_response->setInvoiceDistributionType('POST')
                                ->deliverPaymentPlanOrder()
                                ->doRequest();
                        } else {
                            $svea_response = $credit_response
                                ->deliverCardOrder()
                                ->doRequest();
                        }
                        if ($svea_response->accepted == TRUE) {
                            //save svea invoice no
//                                    $history['order_status_id'] = (int) $this->request->post['order_status_id'];
//                                    $history['notify'] = NULL;
                            //! isset because orderType does'nt exist in HostedService\ConfirmTransactionResponse in package version 2.1.0
                            if (isset($svea_response->orderType) && $svea_response->orderType == 'Invoice') {
                                $history = 'Order delivered at Svea.' . ' Svea invoiceId ' . $svea_response->invoiceId;
                                $this->model_checkout_order->addOrderHistory($this->request->get['order_id'], $this->request->post['order_status_id'], $history);
                                $svea_comment = ' | Order delivered at Svea. '
                                    . ' Svea invoiceId: ' . $svea_response->invoiceId;
                            } else if (isset($svea_response->orderType) && $svea_response->orderType == 'PaymentPlan') {
                                $history = 'Order delivered at Svea.' . ' Svea contractNumber ' . $svea_response->contractNumber;
                                $this->model_checkout_order->addOrderHistory($this->request->get['order_id'], $this->request->post['order_status_id'], $history);
                                $svea_comment = ' | Order delivered at Svea.'
                                    . ' Svea contractNumber: ' . $svea_response->contractNumber;

                            } else {
                                $history = 'Transaction confirmed at Svea.';
                                $this->model_checkout_order->addOrderHistory($this->request->get['order_id'], $this->request->post['order_status_id'], $history);
                                $svea_comment = ' | Transaction confirmed at Svea.';

                            }


                        } else {
                            $json['error'] = ' Svea Error: ' . $svea_response->errormessage . '. Resultcode: ' . $svea_response->resultcode;
                            $this->request->post['order_status_id'] = $order_info['order_status_id'];
                        }

                    } catch (Exception $e) {
                        $this->log->write($e->getMessage());
                        $response = $e->getMessage();
                        $json['error'] = 'Svea error: ' . $response . ' Order was not delivered.';
                        $this->request->post['order_status_id'] = $order_info['order_status_id'];
                    }

                    //Credit
                    //if this action is credit as configured in module
                    //and if status wasn't already credit
                } elseif ($this->config->get($this->request->post['payment_method'] . '_refunded_status_id') == $this->request->post['order_status_id']
                    && $order_info['order_status_id'] != $this->request->post['order_status_id']
                ) {
                    $svea_query = \Svea\WebPay\WebPayAdmin::queryOrder($conf)
                        ->setOrderId($svea_order_id[0][0])
                        ->setCountryCode($order_info['payment_iso_code_2']);
                    if ($this->request->post['payment_method'] == 'svea_invoice') {
                        $svea_query = $svea_query->queryInvoiceOrder();
                    } elseif ($this->request->post['payment_method'] == 'svea_partpayment') {
                        $svea_query = $svea_query->queryPaymentPlanOrder();
                    } else if ($this->request->post['payment_method'] == 'svea_directbank') {
                        $svea_query = $svea_query->queryDirectBankOrder();
                    } else {
                        $svea_query = $svea_query->queryCardOrder();
                    }
                    try {
                        $svea_query = $svea_query->doRequest();
                    } catch (Exception $e) {
                        $this->log->write($e->getMessage());
                        $response = $e->getMessage();
                        $json['error'] = 'Svea error: ' . $response . ' Order was not credited.';
                        $this->request->post['order_status_id'] = $order_info['order_status_id'];
                    }
                    if ($svea_query->accepted == TRUE) {
                        $row_numbers = array();
                        foreach ($svea_query->numberedOrderRows as $value) {
                            $row_numbers[] = $value->rowNumber;

                        }
                        //get svea invoice id
                        $invoiceId = array();
                        $contractNumber = array();
                        $transactionId = array();
                        foreach ($svea_order_history_comment->rows as $svea_order_history) {
                            $svea_invoice_id_exists = strpos($svea_order_history['comment'], 'Svea invoiceId', 0);
                            $svea_contract_number_exists = strpos($svea_order_history['comment'], 'Svea contractNumber', 0);
                            $svea_transaction_id_exists = strpos($svea_order_history['comment'], 'Svea transactionId', 0);
                            if ($svea_invoice_id_exists !== false) {
                                preg_match_all('/\d+/', $svea_order_history['comment'], $invoiceId);
                            } elseif ($svea_contract_number_exists !== false) {
                                preg_match_all('/\d+/', $svea_order_history['comment'], $contractNumber);
                            } elseif ($svea_transaction_id_exists !== false) {
                                preg_match_all('/\d+/', $svea_order_history['comment'], $transactionId);
                            }
                        }


                        if (sizeof($invoiceId) <= 0 && sizeof($transactionId) <= 0 && sizeof($contractNumber) <= 0) {
                            $json['error'] = 'Svea error: Order was not credited. Transaction or order id not found in comment field. Order must first be delivered/shipped.';
                            $this->request->post['order_status_id'] = $order_info['order_status_id'];

                        } else {
                            $credit_response = \Svea\WebPay\WebPayAdmin::creditOrderRows($conf)
                                ->setCountryCode($order_info['payment_iso_code_2'])
                                ->setRowsToCredit($row_numbers)
                                ->addNumberedOrderRows($svea_query->numberedOrderRows);

                            if ($this->request->post['payment_method'] == 'svea_invoice') {
                                $credit_response = $credit_response
                                    ->setInvoiceId($invoiceId[0][0])
                                    ->setInvoiceDistributionType($this->config->get($this->request->post['payment_method'] . '_distribution_type'))
                                    ->creditInvoiceOrderRows();

                            } elseif ($this->request->post['payment_method'] == 'svea_partpayment') {
                                $credit_response = $credit_response
                                    ->setContractNumber($contractNumber[0][0])
                                    ->creditPaymentPlanOrderRows();
                            } elseif ($this->request->post['payment_method'] == 'svea_directbank') {
                                $credit_response = $credit_response
                                    ->setOrderId($transactionId[0][0])
                                    ->creditDirectBankOrderRows();
                            } else {
                                $credit_response = $credit_response
                                    ->setOrderId($transactionId[0][0])
                                    ->creditCardOrderRows();
                            }

                            try {
                                $credit_response = $credit_response->doRequest();
                            } catch (Exception $e) {
                                $this->log->write($e->getMessage());
                                $response = $e->getMessage();
                                $json['error'] = 'Svea error: ' . $response . ' Order was not credited.';
                                $this->request->post['order_status_id'] = $order_info['order_status_id'];
                            }
                            if ($credit_response->accepted == TRUE) {
//                                            $history['order_status_id'] = (int) $this->request->post['order_status_id'];
//                                            $history['notify'] = NULL;
                                if (isset($credit_response->orderType) && $credit_response->orderType == 'Invoice') {
                                    $history = 'Order credited at Svea. Svea creditInvoiceId: ' . $credit_response->creditInvoiceId;
                                    $this->model_checkout_order->addOrderHistory($this->request->get['order_id'], $this->request->post['order_status_id'], $history);
                                    $svea_comment = ' | Order credited at Svea.';
                                } elseif (isset($credit_response->orderType) && $credit_response->orderType == 'PaymentPlan') {
                                    $history = 'Payment plan rows canelled at Svea. ';
                                    $this->model_checkout_order->addOrderHistory($this->request->get['order_id'], $this->request->post['order_status_id'], $history);
                                    $svea_comment = ' | Payment plan rows canelled at Svea. ';
                                } else {
                                    $history = 'Order credited at Svea.';
                                    $this->model_checkout_order->addOrderHistory($this->request->get['order_id'], $this->request->post['order_status_id'], $this->request->post['order_status_id'], $history);
                                    $svea_comment = ' | Order credited at Svea.';
                                }

                            } else {
                                $json['error'] = ' Svea Error: ' . $credit_response->errormessage . '. Resultcode: ' . $credit_response->resultcode;
                                $this->request->post['order_status_id'] = $order_info['order_status_id'];
                            }
                        }


                    }
                } else {
                    $json['error'] = ' Svea Error: Svea order id is missing. ';
                    $this->request->post['order_status_id'] = $order_info['order_status_id'];
                }

                if (isset($this->request->post['comment'])) {
                    $this->request->post['comment'] = $this->request->post['comment'] . $svea_comment;
                } else {
                    $this->request->post['comment'] = '' . $svea_comment;
                }
            } elseif ($this->request->post['payment_method'] == 'sco' && $status_changed === true) {
                $this->load->model('extension/svea/checkout');

                $order_info = $this->model_checkout_order->getOrder($order_id);
                $module_sco_order = $this->model_extension_svea_checkout->getCheckoutOrder($order_id);
                $order_status_id = $this->request->post['order_status_id'];

                if ($module_sco_order !== null && isset($module_sco_order['checkout_id'])) {
                    $module_sco_order_id = (int)$module_sco_order['checkout_id'];
                    $country = $this->model_extension_svea_checkout->getCountryCode($module_sco_order['checkout_id']);
                    $config = new OpencartSveaCheckoutConfig($this->config, 'checkout');
                    if ($this->config->get('module_sco_test_mode')) {
                        $config = new OpencartSveaCheckoutConfigTest($this->config, 'checkout');
                    }

                    /**
                     * CHECKOUT
                     * Canceled Order
                     */
                    if ($this->config->get('module_sco_canceled_status_id') === $order_status_id) {
                        try {
                            if ($this->canOrderBeCancelled($module_sco_order_id, $country) === true) {
                                $cancel_response = \Svea\WebPay\WebPayAdmin::cancelOrder($config)
                                    ->setCheckoutOrderId($module_sco_order_id)
                                    ->setCountryCode($country)
                                    ->cancelCheckoutOrder()
                                    ->doRequest();

                                if ($cancel_response === '') {
                                    $history_comment = 'Order canceled at Svea.';
                                    $this->model_checkout_order->addOrderHistory($order_id, $order_status_id, $history_comment);
                                    $svea_comment = ' | Order canceled at Svea. ';
                                } else {
                                    $json['error'] = ' Svea Error: Order is not successfully Canceled!';
                                    $this->request->post['order_status_id'] = $order_info['order_status_id'];
                                }
                            } else {
                                $json['error'] = ' Svea Error: Order can not be Cancelled!';
                                $this->request->post['order_status_id'] = $order_info['order_status_id'];
                            }
                        } catch (\Exception $e) {
                            $json['error'] = ' Svea Error: ' . $e->getMessage();
                            $this->request->post['order_status_id'] = $order_info['order_status_id'];
                        }
                    } elseif ($this->config->get('module_sco_credited_status_id') === $order_status_id) {
                        /**
                         * CHECKOUT
                         * Credited (Refunded) Order
                         */
                        try {
                            if ($this->canOrderBeCreditedByDeliveryRows($module_sco_order_id, $country) === true) {
                                $delivery_id = $this->getDeliveryId();
                                $credit_rows = $this->getCreditRows();
                                $credit_response = \Svea\WebPay\WebPayAdmin::creditOrderRows($config)
                                    ->setCheckoutOrderId($module_sco_order_id)
                                    ->setCountryCode($country)
                                    ->setDeliveryId($delivery_id)
                                    ->setRowsToCredit($credit_rows)
                                    ->creditCheckoutOrderRows()
                                    ->doRequest();

                                if (isset($credit_response['HeaderLocation'])) {
                                    $history_comment = 'Order credited at Svea.' . ' Svea Checkout Order Id: ' . $module_sco_order_id;
                                    $svea_comment = ' | Order credited at Svea. Svea Checkout Order Id: ' . $module_sco_order_id;
                                    $this->model_checkout_order->addOrderHistory($order_id, $order_status_id, $history_comment);
                                } else {
                                    $json['error'] = ' Credit Error: Order can not be Credited!';
                                    $this->request->post['order_status_id'] = $order_info['order_status_id'];
                                }
                            } elseif ($this->canOrderBeCreditedByAmount($module_sco_order_id, $country) === true) {
                                $delivery_id = $this->getDeliveryId();
                                $delivery_credit = $this->getDeliveryAmount();

                                $credit_response = \Svea\WebPay\WebPayAdmin::creditAmount($config)
                                    ->setCheckoutOrderId($module_sco_order_id)
                                    ->setCountryCode($country)
                                    ->setDeliveryId($delivery_id)
                                    ->setAmountIncVat($delivery_credit)
                                    ->creditCheckoutAmount()
                                    ->doRequest();

                                if ($credit_response === '') {
                                    $history_comment = 'Order credited at Svea.' . ' Svea Checkout Order Id: ' . $module_sco_order_id;
                                    $svea_comment = ' | Order credited at Svea. Svea Checkout Order Id: ' . $module_sco_order_id;
                                    $this->model_checkout_order->addOrderHistory($order_id, $order_status_id, $history_comment);
                                } else {
                                    $json['error'] = ' Credit Error: Order can not be Credited!';
                                    $this->request->post['order_status_id'] = $order_info['order_status_id'];
                                }
                            } else {
                                $json['error'] = ' Credit Error: Order can not be Credited!';
                                $this->request->post['order_status_id'] = $order_info['order_status_id'];
                            }
                        } catch (Exception $e) {
                            $json['error'] = 'Svea error: ' . $e->getMessage() . ' Order was not credited.';
                            $this->request->post['order_status_id'] = $order_info['order_status_id'];
                        }
                    } elseif ($this->config->get('module_sco_delivered_status_id') === $order_status_id) {
                        /**
                         * CHECKOUT
                         * Delivered (Confirm) Order
                         */
                        try {
                            if ($this->canOrderBeDelivered($module_sco_order_id, $country) === true) {
                                $deliver_response = \Svea\WebPay\WebPayAdmin::deliverOrderRows($config)
                                    ->setCheckoutOrderId($module_sco_order_id)
                                    ->setCountryCode($country)
                                    ->deliverCheckoutOrder()
                                    ->doRequest();

                                if (isset($deliver_response['HeaderLocation'])) {
                                    $history_comment = 'Order delivered at Svea.' . ' Svea Checkout Order Id: ' . $module_sco_order_id;
                                    $svea_comment = ' | Order delivered at Svea. Svea Checkout Order Id: ' . $module_sco_order_id;
                                    $this->model_checkout_order->addOrderHistory($order_id, $order_status_id, $history_comment);
                                } else {
                                    $json['error'] = ' Deliver Error: Order can not be Delivered!';
                                    $this->request->post['order_status_id'] = $order_info['order_status_id'];
                                }
                            } else {
                                $json['error'] = ' Deliver Error: Order can not be Delivered!';
                                $this->request->post['order_status_id'] = $order_info['order_status_id'];
                            }
                        } catch (Exception $e) {
                            $json['error'] = ' Deliver Error: ' . $e->getMessage();
                            $this->request->post['order_status_id'] = $order_info['order_status_id'];
                        }
                    }
                    $this->addSveaCommentIntoRequest($svea_comment);

                } else {
                    $json['error'] = ' Svea Error: Svea Checkout order not exist.';
                    $this->request->post['order_status_id'] = $order_info['order_status_id'];
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

    private function getOrderDataFromWebPayAdmin($checkout_order_id, $countryCode)
    {
        $config = $this->getConfiguration();
        $this->order_data_from_admin = \Svea\WebPay\WebPayAdmin::queryOrder($config)
            ->setCheckoutOrderId($checkout_order_id)
            ->setCountryCode($countryCode)
            ->queryCheckoutOrder()
            ->doRequest();

        return $this->order_data_from_admin;
    }

    private function canOrderBeCancelled($checkout_order_id, $countryCode)
    {
        $order_data = $this->getOrderDataFromWebPayAdmin($checkout_order_id, $countryCode);
        if (isset($order_data['Actions'])) {
            $actions = $order_data['Actions'];
            $order_rows = $order_data['OrderRows'];
            $deliveries = $order_data['Deliveries'];
            if (in_array('CanCancelOrder', $actions) === true) {
                if ($order_data['PaymentType'] === 'Card') {
                    return true;
                } else {
                    if (count($order_rows) > 0 && count($deliveries) === 0) {
                        foreach ($order_rows as $order_row) {
                            $row_actions = $order_row['Actions'];
                            if (in_array('CanCancelRow', $row_actions) !== true) {
                                return false;
                            }
                        }
                        return true;
                    }
                }
            }
        }

        return false;
    }

    private function canOrderBeDelivered($checkout_order_id, $countryCode)
    {
        $order_data = $this->getOrderDataFromWebPayAdmin($checkout_order_id, $countryCode);
        if (isset($order_data['Actions'])) {
            $actions = $order_data['Actions'];
            $order_rows = $order_data['OrderRows'];
            if (in_array('CanDeliverOrder', $actions) === true && count($order_rows) > 0) {
                if ($order_data['PaymentType'] !== 'Card' && $order_data['PaymentType'] !== 'PaymentPlan') {
                    foreach ($order_rows as $order_row) {
                        $row_actions = $order_row['Actions'];
                        if (in_array('CanDeliverRow', $row_actions) !== true) {
                            return false;
                        }
                    }
                }
                return true;
            }
        }

        return false;
    }

    private function canOrderBeCreditedByAmount($checkout_order_id, $countryCode)
    {
        return $this->canOrderBeCredited($checkout_order_id, 'CanCreditAmount', $countryCode);
    }

    private function canOrderBeCreditedByDeliveryRows($checkout_order_id, $countryCode)
    {
        return $this->canOrderBeCredited($checkout_order_id, 'CanCreditOrderRows', $countryCode);
    }

    private function canOrderBeCredited($checkout_order_id, $action_type, $countryCode)
    {
        if (empty($action_type)) {
            return false;
        }

        $order_data = $this->getOrderDataFromWebPayAdmin($checkout_order_id, $countryCode);
        $order_rows = $order_data['OrderRows'];
        $deliveries = $order_data['Deliveries'];
        if (count($order_rows) === 0 && count($deliveries) === 1) {
            $delivery = $deliveries[0];
            $delivery_actions = $delivery['Actions'];
            $delivery_rows = $delivery['OrderRows'];
            $delivery_credits = $delivery['Credits'];
            if (in_array($action_type, $delivery_actions) === true && count($delivery_rows) > 0 && empty($delivery_credits)) {
                if ($action_type === 'CanCreditOrderRows') {
                    foreach ($delivery_rows as $delivery_row) {
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

    private function getTaskInfo($location_url)
    {
        $config = $this->getConfiguration();
        return \Svea\WebPay\WebPayAdmin::queryTaskInfo($config)
            ->setTaskUrl($location_url)
            ->getTaskInfo()
            ->doRequest();
    }

    private function getConfiguration()
    {
        $config = new OpencartSveaCheckoutConfig($this->config, 'checkout');
        if ($this->config->get('module_sco_test_mode')) {
            $config = new OpencartSveaCheckoutConfigTest($this->config, 'checkout');
        }

        return $config;
    }

    private function getDeliveryId()
    {
        $order_data = $this->order_data_from_admin;
        $deliveries = $order_data['Deliveries'];
        return $deliveries[0]['Id'];
    }

    private function getDeliveryAmount()
    {
        $order_data = $this->order_data_from_admin;
        $deliveries = $order_data['Deliveries'];
        return $deliveries[0]['DeliveryAmount'];
    }

    private function getCreditRows()
    {
        $row_ids = array();
        $order_data = $this->order_data_from_admin;
        $delivery = $order_data['Deliveries'][0];
        $delivery_rows = $delivery['OrderRows'];

        foreach ($delivery_rows as $delivery_row) {
            $row_ids[] = $delivery_row['OrderRowId'];
        }

        return $row_ids;
    }

    private function addSveaCommentIntoRequest($comment)
    {
        if (isset($this->request->post['comment'])) {
            $this->request->post['comment'] = $this->request->post['comment'] . $comment;
        } else {
            $this->request->post['comment'] = $comment;
        }
    }

    private function syncSveaStatus($orderId, $countryCode, $payment_method, $config)
    {
        $queryOrder = \Svea\WebPay\WebPayAdmin::queryOrder($config)
            ->setOrderId($orderId)
            ->setCountryCode($countryCode);
        if ($payment_method == 'svea_invoice')
        {
            $queryOrder = $queryOrder->queryInvoiceOrder()->doRequest();
        }
        else if ($payment_method == 'svea_partpayment')
        {
            $queryOrder = $queryOrder->queryPaymentPlanOrder()->doRequest();
        }
        else if ($payment_method == 'svea_card')
        {
            $queryOrder = $queryOrder->queryCardOrder()->doRequest();
        }
        else if ($payment_method == 'svea_directbank')
        {
            $queryOrder = $queryOrder->queryDirectBankOrder()->doRequest();
        }
        else
        {
            return false;
        }

        if(isset($queryOrder->orderDeliveryStatus))
        {
            $orderStatus = $queryOrder->orderDeliveryStatus;
        }
        else if(isset($queryOrder->status))
        {
            $orderStatus = $queryOrder->status;
        }
        else if(isset($queryOrder->OrderStatus))
        {
            $orderStatus = $queryOrder->OrderStatus;
        }
        else
        {
            $orderStatus = false;
        }

        if ($orderStatus == "Cancelled" || $orderStatus == "ANNULLED") {
            $this->request->post['order_status_id'] = $this->config->get('payment_' . $payment_method . '_canceled_status_id');
            $this->model_checkout_order->addOrderHistory($this->request->get['order_id'], $this->config->get('payment_' . $payment_method . '_canceled_status_id'), "Unable to update order status, synchronized with Svea instead.");
            return true;
        } else if ($orderStatus == "Delivered" || $orderStatus == "CONFIRMED" || $orderStatus == "SUCCESS") {
            $this->request->post['order_status_id'] = $this->config->get('payment_' . $payment_method . '_deliver_status_id');
            $this->model_checkout_order->addOrderHistory($this->request->get['order_id'], $this->config->get('payment_' . $payment_method . '_deliver_status_id'), "Unable to update order status, synchronized with Svea instead.");
            return true;
        } else if ($orderStatus == "Credited" || $orderStatus == "CREDSUCCESS") {
            $this->request->post['order_status_id'] = $this->config->get('payment_' . $payment_method . '_refunded_status_id');
            $this->model_checkout_order->addOrderHistory($this->request->get['order_id'], $this->config->get('payment_' . $payment_method . '_refunded_status_id'), "Unable to update order status, synchronized with Svea instead.");
            return true;
        } else {
            return false;
        }
    }
}
