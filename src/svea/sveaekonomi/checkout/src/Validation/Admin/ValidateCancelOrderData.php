<?php

namespace Svea\Checkout\Validation\Admin;

use Svea\Checkout\Validation\ValidationService;

class ValidateCancelOrderData extends ValidationService
{
    /**
     * @var bool $isCancelAmount
     */
    protected $isCancelAmount;

    public function __construct($isCancelAmount = false)
    {
        $this->isCancelAmount = $isCancelAmount;
    }

    /**
     * @param array $data
     */
    public function validate($data)
    {
        $this->mustBeSet($data, 'orderid', 'Order Id');
        $this->mustBeInteger($data['orderid'], 'Order Id');

        if ($this->isCancelAmount === true) {
            $this->mustBeSet($data, 'cancelledamount', 'Cancelled Amount');
            $this->mustBeInteger($data['cancelledamount'], 'Cancelled Amount');
        }
    }

    /**
     * @return boolean
     */
    public function isIsCancelAmount()
    {
        return $this->isCancelAmount;
    }

    /**
     * @param boolean $isCancelAmount
     */
    public function setIsCancelAmount($isCancelAmount)
    {
        $this->isCancelAmount = $isCancelAmount;
    }
}
