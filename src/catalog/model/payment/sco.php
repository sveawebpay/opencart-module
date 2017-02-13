<?php

class ModelPaymentSco extends Model
{

    private $name = null;

    public function getMethod($address, $total)
    {
        $this->name = basename(__FILE__, '.php');
        $this->language->load('payment/' . $this->name);

        $method_data = array();

        // Show this payment type only from edit order form in admin section
        // Hide this payment method from classic payment method on default checkout page
        $request = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : null;

        // If request come from admin
        if (strpos($request, 'checkout/manual') !== false) {
            $method_data = array(
                'code' => $this->name,
                'title' => $this->language->get('text_title'),
                'terms' => '',
                'sort_order' => $this->config->get($this->name . '_payment_sort_order'),
            );
        }

        return $method_data;
    }

}

?>
