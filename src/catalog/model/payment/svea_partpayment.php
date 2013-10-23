<?php
class ModelPaymentsveapartpayment extends Model {
  	public function getMethod($address,$total = null) {
		$this->load->language('payment/svea_partpayment');
		$this->load->model('payment/svea_partpayment');

        //Get country
        $countryId = (floatval(VERSION) >= 1.5) ? $this->session->data['payment_country_id'] : $this->session->data['country_id'];
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "country WHERE country_id=$countryId");
        $countryCode = $query->row["iso_code_2"];

        if($total == null)
            $total = $this->cart->getTotal();

        $total = $this->currency->format($total,'',false,false);


		if ($this->config->get('svea_partpayment_status')) {
			$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$this->config->get('svea_partpayment_geo_zone_id') . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");

      		if ($this->config->get("svea_partpayment_min_amount_$countryCode") > $total) {
        		$status = FALSE;
      		} elseif (!$this->config->get('svea_partpayment_geo_zone_id')) {
			    $status = TRUE;
            } elseif ($query->num_rows) {
        		$status = TRUE;
      		} else {
        		$status = FALSE;
      		}

		} else {
			$status = FALSE;
		}

		$method_data = array();

		if ($status) {
      		$method_data = array(
        		'id'         => 'svea_partpayment',
                'code'       => 'svea_partpayment',
        		'title'      => $this->language->get('text_title'),
				'sort_order' => $this->config->get('svea_partpayment_sort_order')
      		);
    	}


        return $method_data;
  	}
}
?>