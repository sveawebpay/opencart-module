<?php
class ModelPaymentsveapartpayment extends Model {
    public function getMethod($address,$total) {
        $this->load->language('payment/svea_partpayment');
        $countryCode = $address['iso_code_2'];

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
                                'code'         => 'svea_partpayment',
                                'title'      => $this->language->get('text_title') . ' ' . $this->config->get('svea_partpayment_payment_description'),
                                'terms'      => '',
                                'sort_order' => $this->config->get('svea_partpayment_sort_order')
                                );
    	}

    return $method_data;
    }

    public function getPaymentPlanParams($countryCode) {
        $table_name = DB_PREFIX ."svea_params_table";

        $query = "SELECT `campaignCode`,`description`,`paymentPlanType`,`contractLengthInMonths`,
                `monthlyAnnuityFactor`,`initialFee`, `notificationFee`,`interestRatePercent`,
                `numberOfInterestFreeMonths`,`numberOfPaymentFreeMonths`,`fromAmount`,`toAmount`
                FROM `".$table_name."`
                WHERE `timestamp`=(SELECT MAX(timestamp) FROM `".$table_name."` WHERE `countryCode` = '".$countryCode."' )
                AND `countryCode` = '".$countryCode."'
                ORDER BY `monthlyAnnuityFactor` ASC";
        $query = $this->db->query($query);

        if($query->num_rows) {
            $rows = array();
            foreach ($query->rows as $row) {
                $rows[] = (object)$row;

            }
            $svea['campaignCodes'] = $rows;
            return (object)$svea;
        }
    }

    public function getProductPriceMode(){
        return $this->config->get('svea_partpayment_product_price');
    }
}
?>