<?php

namespace Svea\Checkout\Validation\Admin;

use Svea\Checkout\Exception\ExceptionCodeList;
use Svea\Checkout\Exception\SveaInputValidationException;
use Svea\Checkout\Validation\ValidationService;

class ValidateGetTaskData extends ValidationService
{
    /**
     * @param mixed $data
     * @throws SveaInputValidationException
     */
    public function validate($data)
    {
        $this->mustBeSet($data, 'locationurl', 'Location URL');

        $locationUrl = $data['locationurl'];
        if (filter_var($locationUrl, FILTER_VALIDATE_URL) === false) {
            throw new SveaInputValidationException(
                "Location URL must be Valid URL!",
                ExceptionCodeList::INPUT_VALIDATION_ERROR
            );
        }
    }
}
