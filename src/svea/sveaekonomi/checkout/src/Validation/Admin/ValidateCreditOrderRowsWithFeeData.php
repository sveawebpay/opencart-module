<?php

namespace Svea\Checkout\Validation\Admin;

use Svea\Checkout\Validation\ValidationService;

class ValidateCreditOrderRowsWithFeeData extends ValidationService
{
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

		$this->validateRowIds($data);

		if (!empty($data['fee'])) {
			$this->mustNotBeEmptyArray($data['fee'], 'Fee');
		}

		if (isset($data['rowcreditingoptions'])) {
			$this->mustNotBeEmptyArray($data['rowcreditingoptions'], 'Row Crediting Options');
		}
    }

	/**
	 * Validate order row ids
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
}
