<?php

namespace Svea\Checkout\Tests\Unit\Validation\Admin;

use Svea\Checkout\Tests\Unit\TestCase;
use Svea\Checkout\Validation\Admin\ValidateDeliverOrderData;

class ValidateDeliverOrderDataTest extends TestCase
{
    /**
     * @var ValidateDeliverOrderData $validateUpdateOrderData
     */
    private $validateDeliverOrder;

    /**
     * @var mixed $inputData
     */
    private $inputData;

    public function setUp()
    {
        parent::setUp();
        $this->validateDeliverOrder = new ValidateDeliverOrderData();

        $this->inputData = array(
            "orderid" => 201,
            'orderrowids' => array(1, 2)
        );
    }

    /**
     * @expectedException \Svea\Checkout\Exception\SveaInputValidationException
     * @expectedExceptionCode Svea\Checkout\Exception\ExceptionCodeList::INPUT_VALIDATION_ERROR
     */
    public function testValidateWithoutOrderId()
    {
        unset($this->inputData['orderid']);
        $this->invokeMethod($this->validateDeliverOrder, 'validate', array($this->inputData));
    }

    /**
     * @expectedException \Svea\Checkout\Exception\SveaInputValidationException
     * @expectedExceptionCode Svea\Checkout\Exception\ExceptionCodeList::INPUT_VALIDATION_ERROR
     */
    public function testValidateWithOrderIdAsString()
    {
        $this->inputData['orderid'] = '204';
        $this->invokeMethod($this->validateDeliverOrder, 'validate', array($this->inputData));
    }

    /**
     * @expectedException \Svea\Checkout\Exception\SveaInputValidationException
     * @expectedExceptionCode Svea\Checkout\Exception\ExceptionCodeList::INPUT_VALIDATION_ERROR
     */
    public function testValidateWithEmptyOrderId()
    {
        $this->inputData['orderid'] = '';
        $this->invokeMethod($this->validateDeliverOrder, 'validate', array($this->inputData));
    }

    /**
     * @expectedException \Svea\Checkout\Exception\SveaInputValidationException
     * @expectedExceptionCode Svea\Checkout\Exception\ExceptionCodeList::INPUT_VALIDATION_ERROR
     */
    public function testValidateWithNullOrderId()
    {
        $this->inputData['orderid'] = null;
        $this->invokeMethod($this->validateDeliverOrder, 'validate', array($this->inputData));
    }

    /**
     * @expectedException \Svea\Checkout\Exception\SveaInputValidationException
     * @expectedExceptionCode Svea\Checkout\Exception\ExceptionCodeList::INPUT_VALIDATION_ERROR
     */
    public function testValidateWithOrderIdAsDecimal()
    {
        $this->inputData['orderid'] = 204.5;
        $this->invokeMethod($this->validateDeliverOrder, 'validate', array($this->inputData));
    }

    /**
     * @expectedException \Svea\Checkout\Exception\SveaInputValidationException
     * @expectedExceptionCode Svea\Checkout\Exception\ExceptionCodeList::INPUT_VALIDATION_ERROR
     */
    public function testValidateWithOrderIdAsIntAndWithoutOrderRowIds()
    {
        unset($this->inputData['orderrowids']);
        $this->invokeMethod($this->validateDeliverOrder, 'validate', array($this->inputData));
    }

    /**
     * @expectedException \Svea\Checkout\Exception\SveaInputValidationException
     * @expectedExceptionCode Svea\Checkout\Exception\ExceptionCodeList::INPUT_VALIDATION_ERROR
     */
    public function testValidateWithOrderIdAsIntAndWithOrderRowIdsAsInt()
    {
        $this->inputData['orderrowids'] = 1;
        $this->invokeMethod($this->validateDeliverOrder, 'validate', array($this->inputData));
    }

    /**
     * Empty array for orderRowIds will be deliver whole Order
     */
    public function testValidateWithOrderIdAsIntAndWithOrderRowIdsAsEmptyArray()
    {
        $this->inputData['orderrowids'] = array();
        $this->invokeMethod($this->validateDeliverOrder, 'validate', array($this->inputData));
    }

    /**
     * @expectedException \Svea\Checkout\Exception\SveaInputValidationException
     * @expectedExceptionCode Svea\Checkout\Exception\ExceptionCodeList::INPUT_VALIDATION_ERROR
     */
    public function testValidateWithOrderIdAsIntAndWithOrderRowIdsAsArrayWithStrings()
    {
        $this->inputData['orderrowids'] = array('1');
        $this->invokeMethod($this->validateDeliverOrder, 'validate', array($this->inputData));
    }

    /**
     * @expectedException \Svea\Checkout\Exception\SveaInputValidationException
     * @expectedExceptionCode Svea\Checkout\Exception\ExceptionCodeList::INPUT_VALIDATION_ERROR
     */
    public function testValidateWithOrderIdAsIntAndWithOrderRowIdsAsArrayWithDecimalValue()
    {
        $this->inputData['orderrowids'] = array(1.5);
        $this->invokeMethod($this->validateDeliverOrder, 'validate', array($this->inputData));
    }

    public function testValidateWithOrderIdAsInteger()
    {
        $this->invokeMethod($this->validateDeliverOrder, 'validate', array($this->inputData));
    }

	/**
     * @expectedException \Svea\Checkout\Exception\SveaInputValidationException
     * @expectedExceptionCode Svea\Checkout\Exception\ExceptionCodeList::INPUT_VALIDATION_ERROR
     */
    public function testValidateWithEmptyRowDeliveryOptions()
    {
        $this->inputData['rowdeliveryoptions'] = array();
        $this->invokeMethod($this->validateDeliverOrder, 'validate', array($this->inputData));
    }

	public function testValidateWithoutRowDeliveryOptions()
    {
        unset($this->inputData['rowdeliveryoptions']);
        $this->invokeMethod($this->validateDeliverOrder, 'validate', array($this->inputData));
    }
}
