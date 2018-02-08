<?php

namespace Svea\Checkout\Validation\Admin;

use Svea\Checkout\Validation\ValidationService;

class ValidateGetOrderData extends ValidationService
{
    /**
     * @param mixed $data
     */
    public function validate($data)
    {
        $this->mustBeSet($data, 'orderid', 'Order Id');
        $this->mustBeInteger($data['orderid'], 'Order Id');
    }
}
