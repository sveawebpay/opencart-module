<?php

class ModelExtensionSveaWidget extends Model
{
    public function is_widget_active()
    {
        if (!$this->config->get('module_sco_show_widget_on_product_page')) {
            return false;
        }

        return true;
    }

    public function get_price($product_info)
    {
        return !empty($product_info['special'])
            ? $product_info['special']
            : $product_info['price'];
    }

    public function calculate_montly_cost($product_info)
    {
        $this->load->model('localisation/country');
        $this->load->language('extension/svea/checkout');

        $price = $this->get_price($product_info);
        $price = $this->tax->calculate($price, $product_info['tax_class_id'], $this->config->get('config_tax'));

        if (empty($price)) {
            return null;
        }

        $country_id = $this->get_checkout_country();
        $country = $this->model_localisation_country->getCountry($country_id);

        $campaigns = $this->get_campaigns_by_country($country);

        if (empty($campaigns)) {
            return null;
        }

        foreach ($campaigns as $campaign) {
            if (empty($monthly_prices) || ($price >= $campaign['fromAmount'] && $price <= $campaign['toAmount'] && $campaign['paymentPlanType'] == 0)) {
                $start_fee = $campaign['initialFee'];
                $annuity = $campaign['monthlyAnnuityFactor'];
                $invoice_fee = $campaign['notificationFee'];
                $months = $campaign['contractLengthInMonths'];

                $monthly_prices[] = ($start_fee + (ceil($price * $annuity) + $invoice_fee) * $months) / $months;
            }
        }

        $data['monthly_cost'] = min($monthly_prices);
        $data['monthly_cost'] = $this->currency->format($data['monthly_cost'], $this->session->data['currency']);

        return $this->load->view('extension/svea/widget', $data);
    }

    public function get_checkout_country()
    {
        switch ($this->request->cookie['language'] ?? null) {
            case 'sv-se':
                return 203;

            case 'nn-no':
                return 160;

            case 'fi-fi':
                return 72;

            case 'da-dk':
                return 57;

            case 'de-de':
                return 81;

            default:
                return $this->config->get('module_sco_checkout_default_country_id');
        }
    }

    public function get_campaigns_by_country($country)
    {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "svea_sco_campaigns`
            WHERE `timestamp` =
            (SELECT MAX(timestamp) FROM `" . DB_PREFIX . "svea_sco_campaigns` WHERE `countryCode` = '" . $country['iso_code_2'] . "')
            AND `countryCode` = '" . $country['iso_code_2'] . "'
            ORDER BY `monthlyAnnuityFactor` ASC");

        return $query->rows;
    }
}
