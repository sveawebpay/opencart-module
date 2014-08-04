<?php

class ModelTotalSveafee extends Model {

    /**
     * getTotal is triggered to get our contribution to the cart order_total and globals total & taxes
     *
     * @param type $total_data
     * @param type $total
     * @param type $taxes
     * @return type
     */
    public function getTotal(&$total_data, &$total, &$taxes) {

        // svea_fee applicable?
        if( ($this->cart->getSubTotal() > 0) && // only checks for lower limit
            isset($this->session->data['payment_method']['code']) && ($this->session->data['payment_method']['code'] == 'svea_invoice') )
        {
                // get country from session data
                $this->load->model('localisation/country');
                $country_info = $this->model_localisation_country->getCountry($this->session->data['payment_country_id']);

                // get svea_fee config settings for country
                $svea_fee_fee = $this->config->get('svea_fee_fee'."_".$country_info['iso_code_2']);
                $svea_fee_sort_order = $this->config->get('svea_fee_sort_order'."_".$country_info['iso_code_2']);
                $svea_fee_tax_class_id = $this->config->get('svea_fee_tax_class'."_".$country_info['iso_code_2']);
                $svea_fee_status = $this->config->get('svea_fee_status'."_".$country_info['iso_code_2']);

                // svea_fee disabled?
                if ( $svea_fee_status == false) {
                    return;
                }

            $this->load->language('total/svea_fee');

            // add our svea_fee total to the rest of the totals
            $total_data[] = array(
                'code' => 'svea_fee',
                'title' => $this->language->get('text_svea_fee')." (".$country_info['iso_code_2'].")",
                'text' => $this->currency->format($svea_fee_fee),
                'value' => $svea_fee_fee,
                'sort_order' => $svea_fee_sort_order
            );

            // calculate tax, add tax and fee to globals total, taxes
            if (isset($svea_fee_tax_class_id)) {

                if (floatval(VERSION) >= 1.5)
                {
                    $tax_rates = $this->tax->getRates($svea_fee_fee, $svea_fee_tax_class_id);

                    foreach ($tax_rates as $tax_rate) {
                        if (!isset($taxes[$tax_rate['tax_rate_id']])) {
                            $taxes[$tax_rate['tax_rate_id']] = $tax_rate['amount'];
                        } else {
                            $taxes[$tax_rate['tax_rate_id']] += $tax_rate['amount'];
                        }
                    }

                    $total += $svea_fee_fee;
                }
                else // OpenCart <1.5
                {
                    $tax_rates = $this->tax->getRate($svea_fee_tax_class_id);

                    $fee = $svea_fee_fee;
                    $tax = (($tax_rates / 100) * $fee );

                    if (!isset($taxes[$svea_fee_tax_class_id])) {
                        $taxes[$svea_fee_tax_class_id] = $tax;
                    } else {
                        $taxes[$svea_fee_tax_class_id] += $tax;
                    }

                    $total += $fee + $tax;
                }
            }
        }
    }
}
?>