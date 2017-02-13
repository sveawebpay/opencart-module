<?php

class ModelExtensionPaymentSco extends Model {
	public function getMethod($address, $total) {
		$this->language->load('extension/payment/sco');

		$method_data = array();
		if ($this->config->get('sco_payment_status')) { return $method_data; }

		$request = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : NULL;
		if (strpos($request, 'api/payment')!==false) {
			$method_data = array(
				'code'       => 'sco',
				'title'      => $this->language->get('text_title'),
				'terms'      => '',
				'sort_order' => $this->config->get('sco_payment_sort_order'),
			);
		}

		return $method_data;
	}
}
