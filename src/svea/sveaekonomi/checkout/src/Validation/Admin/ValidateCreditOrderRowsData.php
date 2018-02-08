<?php

namespace Svea\Checkout\Validation\Admin;

use Svea\Checkout\Validation\ValidationService;

class ValidateCreditOrderRowsData extends ValidationService
{
    /**
     * @var bool $isNewCreditRow
     */
    protected $isNewCreditRow;

    public function __construct($isNewCreditRow = false)
    {
        $this->isNewCreditRow = $isNewCreditRow;
    }

    /**
     * @param array $data
     */
    public function validate($data)
    {
        $this->mustBeSet($data, 'orderid', 'Order Id');
        $this->mustBeInteger($data['orderid'], 'Order Id');

        $this->mustBeSet($data, 'deliveryid', 'Delivery Id');
        $this->mustBeInteger($data['deliveryid'], 'Delivery Id');

        if ($this->isNewCreditRow === true) {
            $this->validateNewCreditRow($data);
        } else {
            $this->validateRowIds($data);
        }
    }

    private function validateNewCreditRow($data)
    {
        $this->mustBeSet($data, 'newcreditrow', 'Credit Row');
        $this->mustNotBeEmptyArray($data['newcreditrow'], 'Credit Row');
    }

    private function validateRowIds($data)
    {
        $this->mustBeSet($data, 'orderrowids', 'Order Row Ids');
        $this->mustNotBeEmptyArray($data['orderrowids'], 'Order Row Ids');

        foreach ($data['orderrowids'] as $orderRowId) {
            $this->mustBeInteger($orderRowId, 'Order Row Id');
        }
    }

    /**
     * @return boolean
     */
    public function isIsNewCreditRow()
    {
        return $this->isNewCreditRow;
    }

    /**
     * @param boolean $isNewCreditRow
     */
    public function setIsNewCreditRow($isNewCreditRow)
    {
        $this->isNewCreditRow = $isNewCreditRow;
    }
}
