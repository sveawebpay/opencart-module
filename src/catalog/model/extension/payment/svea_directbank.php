<?php

class ModelExtensionPaymentSveadirectbank extends Model
{
    private $paymentString = "payment_";

    public function setVersionStrings()
    {
        if(VERSION < 3.0)
        {
            $this->paymentString = "";
        }
    }
    
    public function getMethod($address, $total)
    {
        $this->setVersionStrings();
        $this->load->language('extension/payment/svea_directbank');
        //$this->data[$this->paymentString . 'svea_username'] = $this->config->get($this->paymentString . 'svea_username');
        if ($this->config->get($this->paymentString . 'svea_directbank_status')) {
            $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$this->config->get($this->paymentString . 'svea_directbank_geo_zone_id') . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");

            if (!$this->config->get($this->paymentString . 'svea_directbank_geo_zone_id')) {
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
                'code' => 'svea_directbank',
                'title' => $this->language->get('text_title') . ' ' . $this->config->get($this->paymentString . 'svea_directbank_payment_description'),
                'terms' => '',
                'sort_order' => $this->config->get($this->paymentString . 'svea_directbank_sort_order')
            );

        }

        return $method_data;
    }
}
