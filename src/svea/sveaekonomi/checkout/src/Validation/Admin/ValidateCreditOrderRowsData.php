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
	 * Validate the provided data
	 * 
     * @param array $data
	 * 
	 * @return void
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

			if (isset($data['rowcreditingoptions'])) {
				$this->mustNotBeEmptyArray($data['rowcreditingoptions'], 'Row Crediting Options');
			}
        }
    }

	/**
	 * Validate new credit row
	 *
	 * @param array $data
	 * 
	 * @return void
	 */
    private function validateNewCreditRow($data)
    {
        $this->mustBeSet($data, 'newcreditrow', 'Credit Row');
        $this->mustNotBeEmptyArray($data['newcreditrow'], 'Credit Row');
    }

	/**
	 * Validate row ids
	 *
	 * @param array $data
	 * 
	 * @return void
	 */
    private function validateRowIds($data)
    {
        $this->mustBeSet($data, 'orderrowids', 'Order Row Ids');
        $this->mustNotBeEmptyArray($data['orderrowids'], 'Order Row Ids');

        foreach ($data['orderrowids'] as $orderRowId) {
            $this->mustBeInteger($orderRowId, 'Order Row Id');
        }
    }

    /**
	 * Check if this request contains a new credit row
	 * 
     * @return boolean
     */
    public function isIsNewCreditRow()
    {
        return $this->isNewCreditRow;
    }

    /**
	 * Set if this request contains a new credit row
	 * 
     * @param boolean $isNewCreditRow
	 * 
	 * @return void
     */
    public function setIsNewCreditRow($isNewCreditRow)
    {
        $this->isNewCreditRow = $isNewCreditRow;
    }
}
