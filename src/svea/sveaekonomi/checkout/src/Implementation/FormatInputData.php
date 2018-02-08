<?php

namespace Svea\Checkout\Implementation;

use Svea\Checkout\Exception\ExceptionCodeList;
use Svea\Checkout\Exception\SveaInputValidationException;

final class FormatInputData
{
    public static function formatArrayKeysToLower($data)
    {
        if (!is_array($data)) {
            throw new SveaInputValidationException(
                "Input data must be array!",
                ExceptionCodeList::INPUT_VALIDATION_ERROR
            );
        }

        return self::lowerArrayKeys($data);
    }

    private static function lowerArrayKeys(array $input)
    {
        $return = array();

        foreach ($input as $key => $value) {
            $key = strtolower($key);

            if (is_array($value)) {
                $value = self::lowerArrayKeys($value);
            }

            $return[$key] = $value;
        }

        return $return;
    }
}
