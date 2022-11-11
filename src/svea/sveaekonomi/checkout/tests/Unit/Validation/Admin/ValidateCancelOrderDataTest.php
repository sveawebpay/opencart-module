<?php

namespace Svea\Checkout\Tests\Unit\Validation\Admin;

use Svea\Checkout\Tests\Unit\TestCase;
use Svea\Checkout\Validation\Admin\ValidateCancelOrderData;

class ValidateCancelOrderDataTest extends TestCase
{
    /**
     * @var ValidateCancelOrderData $validateUpdateOrderData
     */
    private $validateCancelOrder;

    /**
     * @var mixed $inputData
     */
    private $inputData;

    public function setUp()
    {
        parent::setUp();
        $this->validateCancelOrder = new ValidateCancelOrderData();

        $this->inputData = array(
            'orderid' => 204,
            'cancelledamount' => 500
        );
    }

    /**
     * @expectedException \Svea\Checkout\Exception\SveaInputValidationException
     * @expectedExceptionCode Svea\Checkout\Exception\ExceptionCodeList::INPUT_VALIDATION_ERROR
     */
    public function testValidateWithoutOrderId()
    {
        unset($this->inputData['orderid']);
        $this->invokeMethod($this->validateCancelOrder, 'validate', array($this->inputData));
    }

    /**
     * @expectedException \Svea\Checkout\Exception\SveaInputValidationException
     * @expectedExceptionCode Svea\Checkout\Exception\ExceptionCodeList::INPUT_VALIDATION_ERROR
     */
    public function testValidateWithOrderIdAsString()
    {
        $this->inputData['orderid'] = '204';
        $this->invokeMethod($this->validateCancelOrder, 'validate', array($this->inputData));
    }

    /**
     * @expectedException \Svea\Checkout\Exception\SveaInputValidationException
     * @expectedExceptionCode Svea\Checkout\Exception\ExceptionCodeList::INPUT_VALIDATION_ERROR
     */
    public function testValidateWithEmptyOrderId()
    {
        $this->inputData['orderid'] = '';
        $this->invokeMethod($this->validateCancelOrder, 'validate', array($this->inputData));
    }

    /**
     * @expectedException \Svea\Checkout\Exception\SveaInputValidationException
     * @expectedExceptionCode Svea\Checkout\Exception\ExceptionCodeList::INPUT_VALIDATION_ERROR
     */
    public function testValidateWithNullOrderId()
    {
        $this->inputData['orderid'] = null;
        $this->invokeMethod($this->validateCancelOrder, 'validate', array($this->inputData));
    }

    /**
     * @expectedException \Svea\Checkout\Exception\SveaInputValidationException
     * @expectedExceptionCode Svea\Checkout\Exception\ExceptionCodeList::INPUT_VALIDATION_ERROR
     */
    public function testValidateWithOrderIdAsDecimal()
    {
        $this->inputData['orderid'] = 204.5;
        $this->invokeMethod($this->validateCancelOrder, 'validate', array($this->inputData));
    }

    public function testValidateWithOrderIdAsInteger()
    {
        $this->invokeMethod($this->validateCancelOrder, 'validate', array($this->inputData));
    }

    /**
     * @expectedException \Svea\Checkout\Exception\SveaInputValidationException
     * @expectedExceptionCode Svea\Checkout\Exception\ExceptionCodeList::INPUT_VALIDATION_ERROR
     */
    public function testValidateWithAmountAsString()
    {
        $this->inputData['cancelledamount'] = '204';
        $this->validateCancelOrder->setIsCancelAmount(true);

        $this->invokeMethod($this->validateCancelOrder, 'validate', array($this->inputData));
    }

    public function testValidateWithAmountWithoutCancelAmountFlag()
    {
        $this->inputData['cancelledamount'] = 'aaa';

        $this->invokeMethod($this->validateCancelOrder, 'validate', array($this->inputData));
    }

    /**
     * @expectedException \Svea\Checkout\Exception\SveaInputValidationException
     * @expectedExceptionCode Svea\Checkout\Exception\ExceptionCodeList::INPUT_VALIDATION_ERROR
     */
    public function testValidateWithEmptyAmount()
    {
        $this->inputData['cancelledamount'] = '';
        $this->validateCancelOrder->setIsCancelAmount(true);

        $this->invokeMethod($this->validateCancelOrder, 'validate', array($this->inputData));
    }

    /**
     * @expectedException \Svea\Checkout\Exception\SveaInputValidationException
     * @expectedExceptionCode Svea\Checkout\Exception\ExceptionCodeList::INPUT_VALIDATION_ERROR
     */
    public function testValidateWithAmountAsDecimal()
    {
        $this->inputData['cancelledamount'] = 204.5;
        $this->validateCancelOrder->setIsCancelAmount(true);

        $this->invokeMethod($this->validateCancelOrder, 'validate', array($this->inputData));
    }

    /**
     * @expectedException \Svea\Checkout\Exception\SveaInputValidationException
     * @expectedExceptionCode Svea\Checkout\Exception\ExceptionCodeList::INPUT_VALIDATION_ERROR
     */
    public function testValidateCancelAmountWithoutAmount()
    {
        unset($this->inputData['cancelledamount']);
        $this->validateCancelOrder->setIsCancelAmount(true);

        $this->invokeMethod($this->validateCancelOrder, 'validate', array($this->inputData));
    }

    public function testValidateWithAmountAsInteger()
    {
        $this->invokeMethod($this->validateCancelOrder, 'validate', array($this->inputData));
    }
}
