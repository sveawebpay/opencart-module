<?php

namespace Svea\Checkout\Exception;

class ExceptionCodeList
{
    const COMMUNICATION_ERROR = 10000;
    const MISSING_MERCHANT_ID = 20001;
    const MISSING_SHARED_SECRET = 20002;
    const MISSING_API_BASE_URL = 20003;
    const INCORRECT_API_BASE_URL = 20004;
    const INPUT_VALIDATION_ERROR = 30000;
    const UNKNOWN_CODE_MESSAGE = 'Unknown code error';

    /**
     * Return Message for given exception code
     *
     * @param  $exceptionCode
     * @return string
     */
    public static function getErrorMessage($exceptionCode)
    {
        $exceptionCode = intval($exceptionCode);

        $exceptionMessageList = array(
            self::COMMUNICATION_ERROR => 'Api Client Error',
            self::MISSING_MERCHANT_ID => 'Missing Merchant Id',
            self::MISSING_SHARED_SECRET => 'Missing Shared Secret',
            self::MISSING_API_BASE_URL => 'Missing API Base URL',
            self::INCORRECT_API_BASE_URL => 'Incorrect API Base URL',
            self::INPUT_VALIDATION_ERROR => 'Input Validation Error'
        );

        if (isset($exceptionMessageList[$exceptionCode])) {
            return $exceptionMessageList[$exceptionCode];
        }

        return self::UNKNOWN_CODE_MESSAGE;
    }
}
