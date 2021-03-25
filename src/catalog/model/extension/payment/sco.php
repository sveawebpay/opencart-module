<?php

class ModelExtensionPaymentSco extends Model
{
    private $moduleString = "module_";

    public function setVersionStrings()
    {
        if (VERSION < 3.0) {
            $this->moduleString = "";
        }
    }

    public function getMethod($address, $total)
    {
        $this->setVersionStrings();

        $this->language->load('extension/payment/sco');

        $method_data = array();

        if ($this->config->get($this->moduleString . 'sco_payment_status')) {
            return $method_data;
        }

        $request = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : null;

        if (strpos($request, 'api/payment')!==false) {
            $method_data = array(
                'code'       => 'sco',
                'title'      => $this->language->get('text_title'),
                'terms'      => '',
                'sort_order' => $this->config->get($this->moduleString . 'sco_payment_sort_order'),
            );
        }

        return $method_data;
    }
}
