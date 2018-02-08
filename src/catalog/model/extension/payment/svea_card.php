<?php

class ModelExtensionPaymentSveacard extends Model
{
    public function getMethod($address, $total)
    {
        $this->load->language('extension/payment/svea_card');

        if ($this->config->get('payment_svea_card_status')) {
            $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$this->config->get('payment_svea_card_geo_zone_id') . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");

            if (!$this->config->get('payment_svea_card_geo_zone_id')) {
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
                'code' => 'svea_card',
                'title' => $this->language->get('text_title') . ' ' . $this->config->get('payment_svea_card_payment_description'),
                'terms' => '',
                'sort_order' => $this->config->get('payment_svea_card_sort_order')
            );

        }


        return $method_data;
    }

    public function getTaxRate($tax_rate_id)
    {
        $query = $this->db->query("SELECT tr.tax_rate_id, tr.name AS name, tr.rate, tr.type, tr.geo_zone_id, gz.name AS geo_zone, tr.date_added, tr.date_modified FROM " . DB_PREFIX . "tax_rate tr LEFT JOIN " . DB_PREFIX . "geo_zone gz ON (tr.geo_zone_id = gz.geo_zone_id) WHERE tr.tax_rate_id = '" . (int)$tax_rate_id . "'");

        return $query->row;
    }

    public function getPaymentTaxRateIdByTaxClass($tax_class)
    {
        $query = $this->db->query("SELECT tax_rate_id FROM " . DB_PREFIX . "tax_rule WHERE tax_class_id = '" . (int)$tax_class . "' ORDER BY priority DESC");

        return $query->rows;
    }
}
