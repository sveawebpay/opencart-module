<?php

class ModelPaymentSco extends Model {

	private $name = NULL;

	public function getMethod($address, $total) {

		// SET NAME
		$this->name = basename(__FILE__, '.php');

		// LOAD LANGUAGE
		$this->language->load('payment/' . $this->name);

		// MAKE ARRAY
		$method_data = array();

		// CHECK STATUS
		if ($this->config->get($this->name . '_payment_status')) { return $method_data; }

		// CHECK REQUEST, ONLY FROM ORDER EDIT
		$request = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : NULL;

		// IF STATUS IS OK
		if (strpos($request, 'api/payment')!==false) {

			// ADD METHOD
			$method_data = array(
				'code'       => $this->name,
				'title'      => $this->language->get('text_title'),
				'terms'      => '',
				'sort_order' => $this->config->get($this->name . '_payment_sort_order'),
			);

		}

		// RETURN METHOD
		return $method_data;

	}

}
?>
