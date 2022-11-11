<?php

namespace Svea\Checkout\Validation\Admin;

use Svea\Checkout\Validation\ValidationService;

class ValidateDeliverOrderData extends ValidationService
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

        $this->mustBeSet($data, 'orderrowids', 'Order Id');
        $this->mustBeArray($data['orderrowids'], 'Order Row Ids');

        if (count($data['orderrowids']) > 0) {
            foreach ($data['orderrowids'] as $orderRowId) {
                $this->mustBeInteger($orderRowId, 'Order Row Id');
            }
        }

		if (isset($data['rowdeliveryoptions'])) {
			$this->mustNotBeEmptyArray($data['rowdeliveryoptions'], 'Row Delivery Options');
		}
    }
}
