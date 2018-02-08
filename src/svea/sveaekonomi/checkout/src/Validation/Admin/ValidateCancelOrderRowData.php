<?php

namespace Svea\Checkout\Validation\Admin;

use Svea\Checkout\Validation\ValidationService;

class ValidateCancelOrderRowData extends ValidationService
{
    /**
     * @param array $data
     */
    public function validate($data)
    {
        $this->mustBeSet($data, 'orderid', 'Order Id');
        $this->mustBeInteger($data['orderid'], 'Order Id');

        $this->mustBeSet($data, 'orderrowid', 'Order Row Id');
        $this->mustBeInteger($data['orderrowid'], 'Order Row Id');
    }
}
