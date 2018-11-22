<?php

namespace Svea\Checkout\Validation;

/**
 * Class ValidateGetAvailablePartPaymentCampaignsData
 * @package Svea\Checkout\Validation
 */
class ValidateGetAvailablePartPaymentCampaignsData extends ValidationService
{
    /**
     * @param mixed $data
     */
    public function validate($data)
    {
        $this->mustBeSet($data, 'iscompany', 'IsCompany');
        $this->mustBeBoolean($data['iscompany'], 'IsCompany');
    }
}
