<?php

class ControllerSveaCoupon extends Controller
{
    public function index()
    {
        $this->load->language('checkout/cart');

        $this->data = array();
        $this->data['text_coupon_code'] = $this->language->get('text_coupon');
        $this->data['coupon'] = null;

        if ((isset($this->session->data['coupon'])) && (!empty($this->session->data['coupon']))) {
            $this->load->model('checkout/coupon');
            $this->data['coupon'] = $this->model_checkout_coupon->getCoupon($this->session->data['coupon']);
        }

        $this->template = 'default/template/svea/coupon.tpl';

        $this->response->setOutput($this->render());
    }

    public function remove()
    {
        $json = array();

        $this->session->data['coupon'] = null;

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function add()
    {
        $json = array();

        $this->load->language('checkout/cart');
        $this->load->model('checkout/coupon');

        $coupon = (isset($this->request->post['coupon'])) ? trim($this->request->post['coupon']) : null;
        $result = $this->model_checkout_coupon->getCoupon($coupon);

        if (empty($coupon)) {
            $json['error'] = $this->language->get('error_coupon');
            unset($this->session->data['coupon']);
        } elseif ($result) {
            $json['success'] = $this->language->get('text_coupon');
            $this->session->data['coupon'] = $this->request->post['coupon'];
        } else {
            $json['error'] = $this->language->get('error_coupon');
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
}