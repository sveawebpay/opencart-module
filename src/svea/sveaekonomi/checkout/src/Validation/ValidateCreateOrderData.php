<?php

namespace Svea\Checkout\Validation;

use Svea\Checkout\Exception\ExceptionCodeList;
use Svea\Checkout\Exception\SveaInputValidationException;

/**
 * Class ValidateCreateOrderData
 * @package Svea\Checkout\Validation
 */
class ValidateCreateOrderData extends ValidationService
{
    /**
     * @param array $data
     * @throws SveaInputValidationException If data is invalid
     */
    public function validate($data)
    {
        $this->validateGeneralData($data);
        $this->validateMerchant($data);
        $this->validateOrderCart($data);
        $this->validateClientOrderNumber($data);
        $this->validateMerchantData($data);
    }

    /**
     * @param array $data
     * @throws SveaInputValidationException
     */
    private function validateMerchantData($data)
    {
        $fieldTitle = "merchantData";

        if(isset($data['merchantData']))
        {
            $this->lengthMustBeBetween($data['merchantData'], 0, 6000, $fieldTitle);
        }
        foreach($data['cart']['items'] as $item)
        {
            if(isset($item['merchantData']))
            {
                $this->lengthMustBeBetween($item['merchantData'], 0, 255, $fieldTitle);
            }
        }
    }
    /**
     * @param array $data
     * @throws SveaInputValidationException
     */
    private function validateGeneralData($data)
    {
        $this->mustBeArray($data, 'Order data');

        $requiredFields = array('merchantsettings', 'cart', 'locale', 'currency', 'countrycode');
        foreach ($requiredFields as $field) {
            $this->mustBeSet($data, $field, $field);
            $this->mustNotBeEmpty($data[$field], $field);
        }
    }

    /**
     * @param array $data
     * @throws SveaInputValidationException
     */
    private function validateMerchant($data)
    {
        $this->mustBeSet($data, 'merchantsettings', 'Merchant settings');
        $this->mustBeArray($data['merchantsettings'], 'Merchant settings');

        $merchantData = $data['merchantsettings'];
        $requiredFields = array('termsuri', 'checkouturi', 'confirmationuri', 'pushuri');

        foreach ($requiredFields as $field) {
            $this->mustBeSet($merchantData, $field, 'Merchant settings' . $field);
            $this->mustNotBeEmpty($merchantData[$field], 'Merchant settings' . $field);
        }
    }

    /**
     * @param array $data
     * @throws SveaInputValidationException
     */
    private function validateOrderCart($data)
    {
        $this->mustBeSet($data, 'cart', 'Order Cart');
        $this->mustBeArray($data['cart'], 'Order Cart');

        $this->mustBeSet($data['cart'], 'items', 'Order Items');
        $this->mustBeArray($data['cart']['items'], 'Order Items');
    }

    /**
     * @param array $data
     * @throws SveaInputValidationException
     */
    private function validateClientOrderNumber($data)
    {
        $fieldKey = 'clientordernumber';
        $fieldTitle = 'Client Order number';
        $this->mustBeSet($data, $fieldKey, $fieldTitle);
        $this->mustNotBeEmpty($data[$fieldKey], $fieldTitle);
        $this->lengthMustBeBetween($data[$fieldKey], 1, 32, $fieldTitle);
    }
}
