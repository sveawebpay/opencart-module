<?php


namespace Svea\Checkout\Validation\Admin;

use Svea\Checkout\Validation\ValidationService;

class ValidateCreditOrderAmountData extends ValidationService
{
    /**
     * @param mixed $data
     */
    public function validate($data)
    {
        $this->mustBeSet($data, 'orderid', 'Order Id');
        $this->mustBeInteger($data['orderid'], 'Order Id');

        $this->mustBeSet($data, 'deliveryid', 'Delivery Id');
        $this->mustBeInteger($data['deliveryid'], 'Delivery Id');

        $this->mustBeSet($data, 'creditedamount', 'Credit Amount');
        $this->mustBeInteger($data['creditedamount'], 'Credit Amount');
    }
}
