<?php

namespace Svea\Checkout\Tests\Unit\Validation\Admin;

use Svea\Checkout\Tests\Unit\TestCase;
use Svea\Checkout\Validation\Admin\ValidateCancelOrderRowData;

class ValidateCancelOrderRowDataTest extends TestCase
{
    /**
     * @var ValidateCancelOrderRowData $validateUpdateOrderData
     */
    private $validateCancelOrderRow;

    /**
     * @var mixed $inputData
     */
    private $inputData;

    public function setUp()
    {
        parent::setUp();
        $this->validateCancelOrderRow = new ValidateCancelOrderRowData();

        $this->inputData = array(
            'orderid' => 201,
            'orderrowid' => 1,
        );
    }

    /**
     * @expectedException \Svea\Checkout\Exception\SveaInputValidationException
     * @expectedExceptionCode Svea\Checkout\Exception\ExceptionCodeList::INPUT_VALIDATION_ERROR
     */
    public function testValidateWithoutOrderId()
    {
        unset($this->inputData['orderid']);
        $this->invokeMethod($this->validateCancelOrderRow, 'validate', array($this->inputData));
    }

    /**
     * @expectedException \Svea\Checkout\Exception\SveaInputValidationException
     * @expectedExceptionCode Svea\Checkout\Exception\ExceptionCodeList::INPUT_VALIDATION_ERROR
     */
    public function testValidateWithOrderIdAsString()
    {
        $this->inputData['orderid'] = '204';
        $this->invokeMethod($this->validateCancelOrderRow, 'validate', array($this->inputData));
    }

    /**
     * @expectedException \Svea\Checkout\Exception\SveaInputValidationException
     * @expectedExceptionCode Svea\Checkout\Exception\ExceptionCodeList::INPUT_VALIDATION_ERROR
     */
    public function testValidateWithEmptyOrderId()
    {
        $this->inputData['orderid'] = '';
        $this->invokeMethod($this->validateCancelOrderRow, 'validate', array($this->inputData));
    }

    /**
     * @expectedException \Svea\Checkout\Exception\SveaInputValidationException
     * @expectedExceptionCode Svea\Checkout\Exception\ExceptionCodeList::INPUT_VALIDATION_ERROR
     */
    public function testValidateWithNullOrderId()
    {
        $this->inputData['orderid'] = null;
        $this->invokeMethod($this->validateCancelOrderRow, 'validate', array($this->inputData));
    }

    /**
     * @expectedException \Svea\Checkout\Exception\SveaInputValidationException
     * @expectedExceptionCode Svea\Checkout\Exception\ExceptionCodeList::INPUT_VALIDATION_ERROR
     */
    public function testValidateWithOrderIdAsDecimal()
    {
        $this->inputData['orderid'] = 204.5;
        $this->invokeMethod($this->validateCancelOrderRow, 'validate', array($this->inputData));
    }

    public function testValidateWithOrderIdAsInteger()
    {
        $this->invokeMethod($this->validateCancelOrderRow, 'validate', array($this->inputData));
    }

    /**
     * @expectedException \Svea\Checkout\Exception\SveaInputValidationException
     * @expectedExceptionCode Svea\Checkout\Exception\ExceptionCodeList::INPUT_VALIDATION_ERROR
     */
    public function testValidateWithoutOrderRowId()
    {
        unset($this->inputData['orderrowid']);
        $this->invokeMethod($this->validateCancelOrderRow, 'validate', array($this->inputData));
    }

    /**
     * @expectedException \Svea\Checkout\Exception\SveaInputValidationException
     * @expectedExceptionCode Svea\Checkout\Exception\ExceptionCodeList::INPUT_VALIDATION_ERROR
     */
    public function testValidateWithOrderRowIdAsString()
    {
        $this->inputData['orderrowid'] = '204';
        $this->invokeMethod($this->validateCancelOrderRow, 'validate', array($this->inputData));
    }

    /**
     * @expectedException \Svea\Checkout\Exception\SveaInputValidationException
     * @expectedExceptionCode Svea\Checkout\Exception\ExceptionCodeList::INPUT_VALIDATION_ERROR
     */
    public function testValidateWithEmptyOrderRowId()
    {
        $this->inputData['orderrowid'] = '';
        $this->invokeMethod($this->validateCancelOrderRow, 'validate', array($this->inputData));
    }

    /**
     * @expectedException \Svea\Checkout\Exception\SveaInputValidationException
     * @expectedExceptionCode Svea\Checkout\Exception\ExceptionCodeList::INPUT_VALIDATION_ERROR
     */
    public function testValidateWithNullOrderRowId()
    {
        $this->inputData['orderrowid'] = null;
        $this->invokeMethod($this->validateCancelOrderRow, 'validate', array($this->inputData));
    }

    /**
     * @expectedException \Svea\Checkout\Exception\SveaInputValidationException
     * @expectedExceptionCode Svea\Checkout\Exception\ExceptionCodeList::INPUT_VALIDATION_ERROR
     */
    public function testValidateWithOrderRowIdAsDecimal()
    {
        $this->inputData['orderrowid'] = 204.5;
        $this->invokeMethod($this->validateCancelOrderRow, 'validate', array($this->inputData));
    }

    public function testValidateWithOrderRowIdAsInteger()
    {
        $this->invokeMethod($this->validateCancelOrderRow, 'validate', array($this->inputData));
    }
}
