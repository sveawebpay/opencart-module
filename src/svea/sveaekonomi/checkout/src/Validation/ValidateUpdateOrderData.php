<?php

namespace Svea\Checkout\Validation;

/**
 * Class ValidateUpdateOrderData
 * @package Svea\Checkout\Validation
 */
class ValidateUpdateOrderData extends ValidationService
{
    /**
     * @param array $data
     */
    public function validate($data)
    {
        $this->mustBeSet($data, 'orderid', 'Order Id');
        $this->mustBeInteger($data['orderid'], 'Order Id');
    }
}
