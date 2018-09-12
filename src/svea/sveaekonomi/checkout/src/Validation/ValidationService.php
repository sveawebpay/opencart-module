<?php

namespace Svea\Checkout\Validation;

use Svea\Checkout\Exception\ExceptionCodeList;
use Svea\Checkout\Exception\SveaInputValidationException;

abstract class ValidationService
{
    /**
     * @param mixed $data
     */
    abstract public function validate($data);

    /**
     * @param mixed $data
     * @param string $dataTitle
     * @throws SveaInputValidationException
     */
    protected function mustNotBeEmpty($data, $dataTitle)
    {
        if (empty($data)) {
            throw new SveaInputValidationException(
                "$dataTitle must not be empty!",
                ExceptionCodeList::INPUT_VALIDATION_ERROR
            );
        }
    }

    /**
     * @param array $data
     * @param string $paramKey
     * @param string $paramTitle
     * @throws SveaInputValidationException
     */
    protected function mustBeSet($data, $paramKey, $paramTitle)
    {
        if (!isset($data[$paramKey])) {
            throw new SveaInputValidationException(
                "$paramTitle must be set!",
                ExceptionCodeList::INPUT_VALIDATION_ERROR
            );
        }
    }

    /**
     * @param array  $data
     * @param string $paramTitle
     * @throws SveaInputValidationException
     */
    protected function mustBeString($data, $paramTitle)
    {
        if (!is_string($data) || $data == "") {
            throw new SveaInputValidationException(
                "$paramTitle must be passed as string and can't be empty!",
                ExceptionCodeList::INPUT_VALIDATION_ERROR
            );
        }
    }

    /**
     * @param array  $data
     * @param string $paramTitle
     * @throws SveaInputValidationException
     */
    protected function mustBeBoolean($data, $paramTitle)
    {
        if (!is_bool($data)) {
            throw new SveaInputValidationException(
                "$paramTitle must be passed as a boolean and can't be empty!",
                ExceptionCodeList::INPUT_VALIDATION_ERROR
            );
        }
    }

    /**
     * @param mixed $data
     * @param string $dataTitle
     * @throws SveaInputValidationException
     */
    protected function mustBeInteger($data, $dataTitle)
    {
        $this->mustNotBeEmpty($data, $dataTitle);
        if (!is_int($data)) {
            throw new SveaInputValidationException(
                "$dataTitle must be passed as integer!",
                ExceptionCodeList::INPUT_VALIDATION_ERROR
            );
        }
    }

    /**
     * @param mixed $data
     * @param string $dataTitle
     * @throws SveaInputValidationException
     */
    protected function mustBeArray($data, $dataTitle)
    {
        if (!is_array($data)) {
            throw new SveaInputValidationException(
                "$dataTitle must be passed as array!",
                ExceptionCodeList::INPUT_VALIDATION_ERROR
            );
        }
    }

    /**
     * @param mixed $data
     * @param string $dataTitle
     * @throws SveaInputValidationException
     */
    protected function mustNotBeEmptyArray($data, $dataTitle)
    {
        if (!is_array($data)) {
            throw new SveaInputValidationException(
                "$dataTitle must be passed as array!",
                ExceptionCodeList::INPUT_VALIDATION_ERROR
            );
        }

        if (count($data) < 1) {
            throw new SveaInputValidationException(
                "$dataTitle must not be empty array!",
                ExceptionCodeList::INPUT_VALIDATION_ERROR
            );
        }
    }

    /**
     * @param mixed $data
     * @param integer $minLength
     * @param integer $maxLength
     * @param string $dataTitle
     * @throws SveaInputValidationException
     */
    protected function lengthMustBeBetween($data, $minLength, $maxLength, $dataTitle)
    {
        $size = strlen($data);

        if ($size > $maxLength) {
            throw new SveaInputValidationException(
                "$dataTitle must contain maximum $maxLength characters!",
                ExceptionCodeList::INPUT_VALIDATION_ERROR
            );
        }

        if ($size < $minLength) {
            throw new SveaInputValidationException(
                "$dataTitle must contain minimum $minLength characters!",
                ExceptionCodeList::INPUT_VALIDATION_ERROR
            );
        }
    }
}
