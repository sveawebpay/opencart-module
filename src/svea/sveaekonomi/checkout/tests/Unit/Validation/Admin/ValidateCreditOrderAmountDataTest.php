<?php

namespace Svea\Checkout\Tests\Unit\Validation\Admin;

use Svea\Checkout\Tests\Unit\TestCase;
use Svea\Checkout\Validation\Admin\ValidateCreditOrderAmountData;

class ValidateCreditOrderAmountDataTest extends TestCase
{
    /**
     * @var ValidateCreditOrderAmountData $validateUpdateOrderData
     */
    private $validateCreditOrderAmount;

    /**
     * @var mixed $inputData
     */
    private $inputData;

    public function setUp()
    {
        parent::setUp();
        $this->validateCreditOrderAmount = new ValidateCreditOrderAmountData();

        $this->inputData = array(
            "orderid"    => 204,        // required - Long  filed (Specified Checkout order for cancel amount)
            "deliveryid" => 1,          // required - Int - Id of order delivery
            "creditedamount"     => 2000,       // required - Int
        );
    }

    /**
     * @expectedException \Svea\Checkout\Exception\SveaInputValidationException
     * @expectedExceptionCode Svea\Checkout\Exception\ExceptionCodeList::INPUT_VALIDATION_ERROR
     */
    public function testValidateWithoutOrderId()
    {
        unset($this->inputData['orderid']);
        $this->invokeMethod($this->validateCreditOrderAmount, 'validate', array($this->inputData));
    }

    /**
     * @expectedException \Svea\Checkout\Exception\SveaInputValidationException
     * @expectedExceptionCode Svea\Checkout\Exception\ExceptionCodeList::INPUT_VALIDATION_ERROR
     */
    public function testValidateWithOrderIdAsString()
    {
        $this->inputData['orderid'] = '204';
        $this->invokeMethod($this->validateCreditOrderAmount, 'validate', array($this->inputData));
    }

    /**
     * @expectedException \Svea\Checkout\Exception\SveaInputValidationException
     * @expectedExceptionCode Svea\Checkout\Exception\ExceptionCodeList::INPUT_VALIDATION_ERROR
     */
    public function testValidateWithEmptyOrderId()
    {
        $this->inputData['orderid'] = '';
        $this->invokeMethod($this->validateCreditOrderAmount, 'validate', array($this->inputData));
    }

    /**
     * @expectedException \Svea\Checkout\Exception\SveaInputValidationException
     * @expectedExceptionCode Svea\Checkout\Exception\ExceptionCodeList::INPUT_VALIDATION_ERROR
     */
    public function testValidateWithNullOrderId()
    {
        $this->inputData['orderid'] = null;
        $this->invokeMethod($this->validateCreditOrderAmount, 'validate', array($this->inputData));
    }

    /**
     * @expectedException \Svea\Checkout\Exception\SveaInputValidationException
     * @expectedExceptionCode Svea\Checkout\Exception\ExceptionCodeList::INPUT_VALIDATION_ERROR
     */
    public function testValidateWithOrderIdAsDecimal()
    {
        $this->inputData['orderid'] = 204.5;
        $this->invokeMethod($this->validateCreditOrderAmount, 'validate', array($this->inputData));
    }

    public function testValidateWithOrderIdAsInteger()
    {
        $this->invokeMethod($this->validateCreditOrderAmount, 'validate', array($this->inputData));
    }

    /**
     * @expectedException \Svea\Checkout\Exception\SveaInputValidationException
     * @expectedExceptionCode Svea\Checkout\Exception\ExceptionCodeList::INPUT_VALIDATION_ERROR
     */
    public function testValidateWithoutDeliveryId()
    {
        unset($this->inputData['deliveryid']);
        $this->invokeMethod($this->validateCreditOrderAmount, 'validate', array($this->inputData));
    }

    /**
     * @expectedException \Svea\Checkout\Exception\SveaInputValidationException
     * @expectedExceptionCode Svea\Checkout\Exception\ExceptionCodeList::INPUT_VALIDATION_ERROR
     */
    public function testValidateWithDeliveryIdAsString()
    {
        $this->inputData['deliveryid'] = '204';
        $this->invokeMethod($this->validateCreditOrderAmount, 'validate', array($this->inputData));
    }

    /**
     * @expectedException \Svea\Checkout\Exception\SveaInputValidationException
     * @expectedExceptionCode Svea\Checkout\Exception\ExceptionCodeList::INPUT_VALIDATION_ERROR
     */
    public function testValidateWithEmptyDeliveryId()
    {
        $this->inputData['deliveryid'] = '';
        $this->invokeMethod($this->validateCreditOrderAmount, 'validate', array($this->inputData));
    }

    /**
     * @expectedException \Svea\Checkout\Exception\SveaInputValidationException
     * @expectedExceptionCode Svea\Checkout\Exception\ExceptionCodeList::INPUT_VALIDATION_ERROR
     */
    public function testValidateWithNullDeliveryId()
    {
        $this->inputData['deliveryid'] = null;
        $this->invokeMethod($this->validateCreditOrderAmount, 'validate', array($this->inputData));
    }

    /**
     * @expectedException \Svea\Checkout\Exception\SveaInputValidationException
     * @expectedExceptionCode Svea\Checkout\Exception\ExceptionCodeList::INPUT_VALIDATION_ERROR
     */
    public function testValidateWithDeliveryIdAsDecimal()
    {
        $this->inputData['deliveryid'] = 204.5;
        $this->invokeMethod($this->validateCreditOrderAmount, 'validate', array($this->inputData));
    }

    public function testValidateWithDeliveryIdAsInteger()
    {
        $this->invokeMethod($this->validateCreditOrderAmount, 'validate', array($this->inputData));
    }

    /**
     * @expectedException \Svea\Checkout\Exception\SveaInputValidationException
     * @expectedExceptionCode Svea\Checkout\Exception\ExceptionCodeList::INPUT_VALIDATION_ERROR
     */
    public function testValidateWithoutAmount()
    {
        unset($this->inputData['creditedamount']);
        $this->invokeMethod($this->validateCreditOrderAmount, 'validate', array($this->inputData));
    }

    /**
     * @expectedException \Svea\Checkout\Exception\SveaInputValidationException
     * @expectedExceptionCode Svea\Checkout\Exception\ExceptionCodeList::INPUT_VALIDATION_ERROR
     */
    public function testValidateWithAmountAsString()
    {
        $this->inputData['creditedamount'] = '204';
        $this->invokeMethod($this->validateCreditOrderAmount, 'validate', array($this->inputData));
    }

    /**
     * @expectedException \Svea\Checkout\Exception\SveaInputValidationException
     * @expectedExceptionCode Svea\Checkout\Exception\ExceptionCodeList::INPUT_VALIDATION_ERROR
     */
    public function testValidateWithEmptyAmount()
    {
        $this->inputData['creditedamount'] = '';
        $this->invokeMethod($this->validateCreditOrderAmount, 'validate', array($this->inputData));
    }

    /**
     * @expectedException \Svea\Checkout\Exception\SveaInputValidationException
     * @expectedExceptionCode Svea\Checkout\Exception\ExceptionCodeList::INPUT_VALIDATION_ERROR
     */
    public function testValidateWithNullAmount()
    {
        $this->inputData['creditedamount'] = null;
        $this->invokeMethod($this->validateCreditOrderAmount, 'validate', array($this->inputData));
    }

    /**
     * @expectedException \Svea\Checkout\Exception\SveaInputValidationException
     * @expectedExceptionCode Svea\Checkout\Exception\ExceptionCodeList::INPUT_VALIDATION_ERROR
     */
    public function testValidateWithAmountAsDecimal()
    {
        $this->inputData['creditedamount'] = 204.5;
        $this->invokeMethod($this->validateCreditOrderAmount, 'validate', array($this->inputData));
    }

    public function testValidateWithAmountAsInteger()
    {
        $this->invokeMethod($this->validateCreditOrderAmount, 'validate', array($this->inputData));
    }
}
