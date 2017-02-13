<?php

class ControllerSveaCoupon extends Controller
{
    public function index()
    {
        $this->load->language('total/coupon');
        $this->load->language('svea/checkout');

        $data['text_coupon_code'] = $this->language->get('text_success');
        $data['coupon'] = NULL;

        if ((isset($this->session->data['coupon'])) && (!empty($this->session->data['coupon']))) {
            $this->load->model('total/coupon');
            $data['coupon'] = $this->model_total_coupon->getCoupon($this->session->data['coupon']);
        }

        $this->response->setOutput($this->load->view('default/template/svea/coupon.tpl', $data));
    }

    public function remove()
    {
        $json = array();

        $this->session->data['coupon'] = NULL;

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function add()
    {
        $json = array();

        $this->load->language('total/coupon');
        $this->load->language('svea/checkout');
        $this->load->model('total/coupon');

        $coupon = (isset($this->request->post['coupon'])) ? trim($this->request->post['coupon']) : NULL;
        $result = $this->model_total_coupon->getCoupon($coupon);

        if (empty($coupon)) {
            $json['error'] = $this->language->get('error_empty');
            unset($this->session->data['coupon']);
        } elseif ($result) {
            $json['success'] = $this->language->get('text_success');
            $this->session->data['coupon'] = $this->request->post['coupon'];
        } else {
            $json['error'] = $this->language->get('error_coupon');
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
}