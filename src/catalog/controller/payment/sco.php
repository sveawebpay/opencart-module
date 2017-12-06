<?php
//
//class ControllerPaymentSco extends Controller {
//
//	private $name = NULL;
//
//	public function index() {
//
//		return false;
//
//	}
//
//	public function confirm() {
//
//		// SET NAME
//		$this->name = basename(__FILE__, '.php');
//
//		// CHECK IF METHOD IS CORRECT
//		if ($this->session->data['payment_method']['code'] == $this->name) {
//
//			// LOAD MODEL
//			$this->load->model('checkout/order');
//
//			// CONFIRM ORDER
//			$this->model_checkout_order->addOrderHistory($this->session->data['order_id'], $this->config->get($this->name . '_order_status_id'));
//		}
//
//	}
//
//}
