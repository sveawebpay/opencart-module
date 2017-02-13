<?php

class ControllerSveaVoucher extends Controller
{

    /**
     * Show voucher template
     */
    public function index()
    {
        $this->load->language('checkout/cart');

        $this->data = array();
        $this->data['text_voucher_code'] = $this->language->get('text_voucher');
        $this->data['voucher'] = null;

        if ((isset($this->session->data['voucher'])) && (!empty($this->session->data['voucher']))) {
            $this->load->model('checkout/voucher');
            $this->data['voucher'] = $this->model_checkout_voucher->getVoucher($this->session->data['voucher']);
        }

        $this->template = 'default/template/svea/voucher.tpl';

        $this->response->setOutput($this->render());
    }

    /**
     * Remove voucher from order
     */
    public function remove()
    {
        $json = array();

        $this->session->data['voucher'] = null;

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    /**
     * Add voucher from order
     */
    public function add()
    {
        $json = array();

        $this->load->language('checkout/cart');
        $this->load->model('checkout/voucher');

        $voucher = (isset($this->request->post['voucher'])) ? trim($this->request->post['voucher']) : null;

        $result = $this->model_checkout_voucher->getVoucher($voucher);

        if (empty($voucher)) {
            $json['error'] = $this->language->get('error_voucher');
            unset($this->session->data['voucher']);
        } elseif ($result) {
            $json['success'] = $this->language->get('text_voucher');
            $this->session->data['voucher'] = $this->request->post['voucher'];
        } else {
            $json['error'] = $this->language->get('error_voucher');
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

}
