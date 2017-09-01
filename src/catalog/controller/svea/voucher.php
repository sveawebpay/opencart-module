<?php

class ControllerSveaVoucher extends Controller {

    /**
     * Show voucher template
     */
	public function index() {
		$this->load->language('svea/checkout');

        $data['text_voucher_code'] = $this->language->get('text_voucher_code');
        $data['item_voucher'] = $this->language->get('item_voucher');
        $data['voucher'] = NULL;

		if ( (isset($this->session->data['voucher'])) AND (!empty($this->session->data['voucher'])) ) {
            $this->load->model('checkout/voucher');
			$data['voucher'] = $this->model_checkout_voucher->getVoucher($this->session->data['voucher']);
		}

		$this->response->setOutput($this->load->view('default/template/svea/voucher.tpl', $data));
	}

    /**
     * Remove voucher from order
     */
	public function remove() {
		$json = array();

		$this->session->data['voucher'] = NULL;

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

    /**
     * Add voucher from order
     */
	public function add() {
		$json = array();

		$this->load->language('svea/checkout');
        $this->load->model('checkout/voucher');

		$voucher = (isset($this->request->post['voucher'])) ? trim($this->request->post['voucher']) : NULL;

		$result = $this->model_checkout_voucher->getVoucher($voucher);

		if (empty($voucher)) {
            $json['error'] = $this->language->get('error_no_voucher');
			unset($this->session->data['voucher']);
		} elseif ($result) {
            $json['success'] = $this->language->get('success_add_voucher');
			$this->session->data['voucher'] = $this->request->post['voucher'];
		} else {
            $json['error'] = $this->language->get('error_unknown_voucher');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

}
