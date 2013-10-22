<?php
class ModelPaymentsveainvoice extends Model {
  	public function getMethod($address) {
            $this->load->language('payment/svea_invoice');
            $this->load->model('payment/svea_invoice');

            if ($this->config->get('svea_invoice_status')) {
                    $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$this->config->get('svea_invoice_geo_zone_id') . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");

                if (!$this->config->get('svea_invoice_geo_zone_id')) {
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
                    'id'         => 'svea_invoice',
            'code'         => 'svea_invoice',
                    'title'      => $this->language->get('text_title'),
                            'sort_order' => $this->config->get('svea_invoice_sort_order')
            );
            }


            return $method_data;
  	}
        /**
         * Update shops address so billing address is the same as address recieved from Svea UC
         * @param type $address_id
         * @param type $data
         */
        public function updateAddressField($order_id,$data){
            $query = "UPDATE " . DB_PREFIX .
                              "order SET ";
            $row = "";
            $counter = 0;
            foreach ($data as $key => $value){
                $counter == 0 ? $row = "" : $row .= ",";
                $row .= $this->db->escape($key)." = '".$this->db->escape($value)."'";
                $counter ++;
            }
            $query .= $row;
            $query .=  " WHERE order_id  = '" . (int)$order_id . "'";

            $this->db->query($query);

          }

          public function getCountryIdFromCountryCode($countryCode){
                $query = $this->db->query("SELECT country_id, name FROM " . DB_PREFIX . "country WHERE status = '1' AND iso_code_2 = '$countryCode' ORDER BY name ASC");
                $country = $query->rows;
                return array("country_id" => $country[0]['country_id'], "country_name" => $country[0]['name']);
          }


}
?>