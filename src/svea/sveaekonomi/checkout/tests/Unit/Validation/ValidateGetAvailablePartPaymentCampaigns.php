<?php

namespace Svea\Checkout\Tests\Unit\Validation;

use Svea\Checkout\Tests\Unit\TestCase;
use Svea\Checkout\Validation\ValidateGetAvailablePartPaymentCampaignsData;

class ValidateGetAvailablePartPaymentCampaigns extends TestCase
{
    /**
     * @var ValidateGetAvailablePartPaymentCampaignsData $validateGetAvailablePartPaymentCampaignsData
     */
    private $validateGetAvailablePartPaymentCampaignsData;

    public function setUp()
    {
        parent::setUp();
        $this->validateGetAvailablePartPaymentCampaignsData = new ValidateGetAvailablePartPaymentCampaignsData();
    }

    /**
     * @expectedException \Svea\Checkout\Exception\SveaInputValidationException
     * @expectedExceptionCode Svea\Checkout\Exception\ExceptionCodeList::INPUT_VALIDATION_ERROR
     */
    public function testValidateWithEmptyArray()
    {
        unset($this->inputGetAvailablePartPaymentCampaignsData);
        $this->invokeMethod($this->validateGetAvailablePartPaymentCampaignsData, 'validate', array($this->inputUpdateData));
    }

    /**
     * @expectedException \Svea\Checkout\Exception\SveaInputValidationException
     * @expectedExceptionCode Svea\Checkout\Exception\ExceptionCodeList::INPUT_VALIDATION_ERROR
     */
    public function testValidateWithNonBooleanType()
    {
        $this->inputGetAvailablePartPaymentCampaignsData['iscompany'] = "true";
        $this->invokeMethod($this->validateGetAvailablePartPaymentCampaignsData, 'validate', array($this->inputUpdateData));
    }

    public function testValidateWithValidData()
    {
        $this->validateGetAvailablePartPaymentCampaignsData->validate($this->inputGetAvailablePartPaymentCampaignsData);
    }
}
