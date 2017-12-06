<?php

require_once(DIR_APPLICATION . '../svea/config/configInclude.php');

class ControllerSveaOrder extends Controller
{
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
                    if ($svea_order_id_exists !== false) {
                        preg_match_all('/\d+/', $svea_order_history['comment'], $svea_order_id);
                    }
                }

                //Cancel
                //if this action is cancel as configured in module,
                //and it´s not directbank,
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
            } else if ($this->request->post['payment_method'] == 'sco') {
                $this->load->model('svea/checkout');

                $order_info = $this->model_checkout_order->getOrder($order_id);
                $sco_order = $this->model_svea_checkout->getCheckoutOrder($order_id);
                $order_status_id = $this->request->post['order_status_id'];

                if ($sco_order !== null && isset($sco_order['checkout_id'])) {
                    $sco_order_id = (int)$sco_order['checkout_id'];
                    $country_code = isset($this->session->data['sco_country']) ? strtoupper($this->session->data['sco_country']) : 'SE';
                    $config = new OpencartSveaCheckoutConfig($this->config, 'checkout');
                    if ($this->config->get('sco_test_mode')) {
                        $config = new OpencartSveaCheckoutConfigTest($this->config, 'checkout');
                    }


                    if ($this->config->get('sco_canceled_status_id') === $order_status_id) { /* Canceled Order */
                        try {
                            $cancel_response = \Svea\WebPay\WebPayAdmin::cancelOrder($config)
                                ->setCheckoutOrderId($sco_order_id)
                                ->setCountryCode($country_code)
                                ->cancelCheckoutOrder()
                                ->doRequest();

                            if ($cancel_response->accepted == true) {
                                $history_comment = 'Order canceled at Svea.';
                                $this->model_checkout_order->addOrderHistory($order_id, $order_status_id, $history_comment);
                                $svea_comment = ' | Order canceled at Svea. ';
                            } else {
                                $json['error'] = ' Svea Error: ' . $cancel_response->errormessage . '. Resultcode: ' . $cancel_response->resultcode;
                                $this->request->post['order_status_id'] = $order_info['order_status_id'];
                            }
                        } catch (\Exception $e) {
                            $json['error'] = ' Svea Error: ' . $e->getMessage();
                            $this->request->post['order_status_id'] = $order_info['order_status_id'];
                        }
                    } else if ($this->config->get('sco_credited_status_id') === $order_status_id) { /* Credited (Refunded) Order */
                        try {
                            $svea_query = \Svea\WebPay\WebPayAdmin::queryOrder($config)
                                ->setCheckoutOrderId($sco_order_id)
                                ->setCountryCode($country_code)
                                ->queryCheckoutOrder()
                                ->doRequest();

                            if ($svea_query->accepted == true) {

                                $ids = $this->extractOrderIds();
                                $invoiceId = $ids['invoiceId'];
                                $contractNumber = $ids['contractNumber'];


                                $row_numbers = array();
                                foreach ($svea_query->numberedOrderRows as $value) {
                                    $row_numbers[] = $value->rowNumber;
                                }

                                $credit_response = \Svea\WebPay\WebPayAdmin::creditOrderRows($config)
                                    ->setCheckoutOrderId($sco_order_id)
                                    ->setCountryCode($country_code)
                                    ->setRowsToCredit($row_numbers)
                                    ->addNumberedOrderRows($svea_query->numberedOrderRows)
                                    ->creditCheckoutOrderRows($invoiceId, $contractNumber)
                                    ->doRequest();

                                if ($credit_response->accepted == true) {
                                    if (isset($credit_response->orderType) && $credit_response->orderType == 'Invoice') {
                                        $history_comment = 'Order credited at Svea. Svea creditInvoiceId: ' . $credit_response->creditInvoiceId;
                                        $svea_comment = ' | Order credited at Svea.';
                                    } elseif (isset($credit_response->orderType) && $credit_response->orderType == 'PaymentPlan') {
                                        $history_comment = 'Payment plan rows canelled at Svea. ';
                                        $svea_comment = ' | Payment plan rows credited at Svea. ';
                                    } else {
                                        $history_comment = 'Order credited at Svea.';
                                        $svea_comment = ' | Order credited at Svea.';
                                    }
                                    $this->model_checkout_order->addOrderHistory($order_id, $order_status_id, $history_comment);
                                } else {
                                    $json['error'] = ' Svea Error: ' . $credit_response->errormessage . '. Resultcode: ' . $credit_response->resultcode;
                                    $this->request->post['order_status_id'] = $order_info['order_status_id'];
                                }
                            }
                        } catch (Exception $e) {
                            $json['error'] = 'Svea error: ' . $e->getMessage() . ' Order was not credited.';
                            $this->request->post['order_status_id'] = $order_info['order_status_id'];
                        }
                    } else if ($this->config->get('sco_delivered_status_id') === $order_status_id) { /* Delivered (Confirm) Order */
                        try {
                            $deliver_response = \Svea\WebPay\WebPay::deliverOrder($config)
                                ->setCheckoutOrderId($sco_order_id)
                                ->setOrderDate(date('c'))
                                ->setCountryCode($country_code)
                                ->deliverCheckoutOrder()
                                ->doRequest();

                            if ($deliver_response->accepted == true) {
                                if (isset($deliver_response->orderType) && $deliver_response->orderType == 'Invoice') {
                                    $history_comment = 'Order delivered at Svea.' . ' Svea invoiceId ' . $deliver_response->invoiceId;
                                    $svea_comment = ' | Order delivered at Svea.  Svea invoiceId: ' . $deliver_response->invoiceId;
                                } else if (isset($deliver_response->orderType) && $deliver_response->orderType == 'PaymentPlan') {
                                    $history_comment = 'Order delivered at Svea.' . ' Svea contractNumber ' . $deliver_response->contractNumber;
                                    $svea_comment = ' | Order delivered at Svea. Svea contractNumber: ' . $deliver_response->contractNumber;
                                } else {
                                    $history_comment = 'Transaction confirmed at Svea.';
                                    $svea_comment = ' | Transaction confirmed at Svea.';
                                }

                                $this->model_checkout_order->addOrderHistory($order_id, $order_status_id, $history_comment);
                            } else {
                                $json['error'] = ' Svea Error: ' . $deliver_response->errormessage . '. Resultcode: ' . $deliver_response->resultcode;
                                $this->request->post['order_status_id'] = $order_info['order_status_id'];
                            }
                        } catch (Exception $e) {
                            $json['error'] = ' Svea Error: ' . $e->getMessage();
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

    private function extractOrderIds()
    {
        $svea_order_history_comment = $this->db->query("SELECT comment FROM " . DB_PREFIX . "order_history WHERE order_id = " . (int)$this->request->get['order_id']);

        $invoiceId = array();
        $contractNumber = array();
        foreach ($svea_order_history_comment->rows as $svea_order_history) {
            $svea_invoice_id_exists = strpos($svea_order_history['comment'], 'Svea invoiceId', 0);
            $svea_contract_number_exists = strpos($svea_order_history['comment'], 'Svea contractNumber', 0);
            if ($svea_invoice_id_exists !== false) {
                preg_match_all('/\d+/', $svea_order_history['comment'], $invoiceId);
            } elseif ($svea_contract_number_exists !== false) {
                preg_match_all('/\d+/', $svea_order_history['comment'], $contractNumber);
            }
        }

        $cn = null;
        if (isset($contractNumber) && isset($contractNumber[0]) && isset($contractNumber[0][0])) {
            $cn = $contractNumber[0][0];
        }

        $ii = null;
        if (isset($invoiceId) && isset($invoiceId[0]) && isset($invoiceId[0][0])) {
            $ii = $invoiceId[0][0];
        }

        return array(
            'invoiceId' => $ii,
            'contractNumber' => $cn
        );
    }

    private function addSveaCommentIntoRequest($comment)
    {
        if (isset($this->request->post['comment'])) {
            $this->request->post['comment'] = $this->request->post['comment'] . $comment;
        } else {
            $this->request->post['comment'] = $comment;
        }
    }
}