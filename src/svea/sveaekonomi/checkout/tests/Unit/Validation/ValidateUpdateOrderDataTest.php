<?php

namespace Svea\Checkout\Tests\Unit\Validation;

use Svea\Checkout\Tests\Unit\TestCase;
use Svea\Checkout\Validation\ValidateUpdateOrderData;

class ValidateUpdateOrderDataTest extends TestCase
{
    /**
     * @var ValidateUpdateOrderData $validateUpdateOrderData
     */
    private $validateUpdateOrderData;

    public function setUp()
    {
        parent::setUp();
        $this->validateUpdateOrderData = new ValidateUpdateOrderData();
    }

    /**
     * @expectedException \Svea\Checkout\Exception\SveaInputValidationException
     * @expectedExceptionCode Svea\Checkout\Exception\ExceptionCodeList::INPUT_VALIDATION_ERROR
     */
    public function testValidateOrderIdWithUnsetField()
    {
        unset($this->inputUpdateData['orderid']);
        $this->invokeMethod($this->validateUpdateOrderData, 'validate', array($this->inputUpdateData));
    }

    /**
     * @expectedException \Svea\Checkout\Exception\SveaInputValidationException
     * @expectedExceptionCode Svea\Checkout\Exception\ExceptionCodeList::INPUT_VALIDATION_ERROR
     */
    public function testValidateOrderIdWithEmptyStringFiled()
    {
        $this->inputUpdateData['orderid'] = '';
        $this->invokeMethod($this->validateUpdateOrderData, 'validate', array($this->inputUpdateData));
    }

    /**
     * @expectedException \Svea\Checkout\Exception\SveaInputValidationException
     * @expectedExceptionCode Svea\Checkout\Exception\ExceptionCodeList::INPUT_VALIDATION_ERROR
     */
    public function testValidateOrderIdWithFalseValueField()
    {
        $this->inputUpdateData['orderid'] = false;
        $this->invokeMethod($this->validateUpdateOrderData, 'validate', array($this->inputUpdateData));
    }

    public function testValidateOrderIdWithZeroValueField()
    {
        $this->inputUpdateData['orderid'] = 1230;
        $this->invokeMethod($this->validateUpdateOrderData, 'validate', array($this->inputUpdateData));
    }

    public function testValidateWithValidData()
    {
        $this->validateUpdateOrderData->validate($this->inputUpdateData);
    }
}
