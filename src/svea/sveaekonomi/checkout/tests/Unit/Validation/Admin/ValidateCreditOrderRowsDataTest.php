<?php

namespace Svea\Checkout\Tests\Unit\Validation\Admin;

use Svea\Checkout\Tests\Unit\TestCase;
use Svea\Checkout\Validation\Admin\ValidateCreditOrderRowsData;

class ValidateCreditOrderRowsDataTest extends TestCase
{
    /**
     * @var ValidateCreditOrderRowsData $validateUpdateOrderData
     */
    private $validateCreditOrderRow;

    /**
     * @var mixed $inputData
     */
    private $inputData;

    public function setUp()
    {
        parent::setUp();
        $this->validateCreditOrderRow = new ValidateCreditOrderRowsData();

        $this->inputData = array(
            "orderid" => 201,
            "deliveryid" => 1,
            "orderrowids" => array(3),
            "newcreditrow" => array(
                'unitPrice' => 5000
            )
        );
    }

    /**
     * @expectedException \Svea\Checkout\Exception\SveaInputValidationException
     * @expectedExceptionCode Svea\Checkout\Exception\ExceptionCodeList::INPUT_VALIDATION_ERROR
     */
    public function testValidateWithoutOrderId()
    {
        unset($this->inputData['orderid']);
        $this->invokeMethod($this->validateCreditOrderRow, 'validate', array($this->inputData));
    }

    /**
     * @expectedException \Svea\Checkout\Exception\SveaInputValidationException
     * @expectedExceptionCode Svea\Checkout\Exception\ExceptionCodeList::INPUT_VALIDATION_ERROR
     */
    public function testValidateWithOrderIdAsString()
    {
        $this->inputData['orderid'] = '204';
        $this->invokeMethod($this->validateCreditOrderRow, 'validate', array($this->inputData));
    }

    /**
     * @expectedException \Svea\Checkout\Exception\SveaInputValidationException
     * @expectedExceptionCode Svea\Checkout\Exception\ExceptionCodeList::INPUT_VALIDATION_ERROR
     */
    public function testValidateWithEmptyOrderId()
    {
        $this->inputData['orderid'] = '';
        $this->invokeMethod($this->validateCreditOrderRow, 'validate', array($this->inputData));
    }

    /**
     * @expectedException \Svea\Checkout\Exception\SveaInputValidationException
     * @expectedExceptionCode Svea\Checkout\Exception\ExceptionCodeList::INPUT_VALIDATION_ERROR
     */
    public function testValidateWithNullOrderId()
    {
        $this->inputData['orderid'] = null;
        $this->invokeMethod($this->validateCreditOrderRow, 'validate', array($this->inputData));
    }

    /**
     * @expectedException \Svea\Checkout\Exception\SveaInputValidationException
     * @expectedExceptionCode Svea\Checkout\Exception\ExceptionCodeList::INPUT_VALIDATION_ERROR
     */
    public function testValidateWithOrderIdAsDecimal()
    {
        $this->inputData['orderid'] = 204.5;
        $this->invokeMethod($this->validateCreditOrderRow, 'validate', array($this->inputData));
    }

    public function testValidateValidCreditOrderRowIds()
    {
        unset($this->inputData['newcreditrow']);
        $this->validateCreditOrderRow->setIsNewCreditRow(false);
        $this->invokeMethod($this->validateCreditOrderRow, 'validate', array($this->inputData));
    }

    public function testValidateNewCreditRowWithoutOrderRowIds()
    {
        unset($this->inputData['orderrowids']);
        $this->validateCreditOrderRow->setIsNewCreditRow(true);
        $this->invokeMethod($this->validateCreditOrderRow, 'validate', array($this->inputData));
    }

    /**
     * @expectedException \Svea\Checkout\Exception\SveaInputValidationException
     * @expectedExceptionCode Svea\Checkout\Exception\ExceptionCodeList::INPUT_VALIDATION_ERROR
     */
    public function testValidateWithoutDeliveryId()
    {
        unset($this->inputData['deliveryid']);
        $this->invokeMethod($this->validateCreditOrderRow, 'validate', array($this->inputData));
    }

    /**
     * @expectedException \Svea\Checkout\Exception\SveaInputValidationException
     * @expectedExceptionCode Svea\Checkout\Exception\ExceptionCodeList::INPUT_VALIDATION_ERROR
     */
    public function testValidateWithDeliveryIdAsString()
    {
        $this->inputData['deliveryid'] = '204';
        $this->invokeMethod($this->validateCreditOrderRow, 'validate', array($this->inputData));
    }

    /**
     * @expectedException \Svea\Checkout\Exception\SveaInputValidationException
     * @expectedExceptionCode Svea\Checkout\Exception\ExceptionCodeList::INPUT_VALIDATION_ERROR
     */
    public function testValidateWithEmptyDeliveryId()
    {
        $this->inputData['deliveryid'] = '';
        $this->invokeMethod($this->validateCreditOrderRow, 'validate', array($this->inputData));
    }

    /**
     * @expectedException \Svea\Checkout\Exception\SveaInputValidationException
     * @expectedExceptionCode Svea\Checkout\Exception\ExceptionCodeList::INPUT_VALIDATION_ERROR
     */
    public function testValidateWithNullDeliveryId()
    {
        $this->inputData['deliveryid'] = null;
        $this->invokeMethod($this->validateCreditOrderRow, 'validate', array($this->inputData));
    }

    /**
     * @expectedException \Svea\Checkout\Exception\SveaInputValidationException
     * @expectedExceptionCode Svea\Checkout\Exception\ExceptionCodeList::INPUT_VALIDATION_ERROR
     */
    public function testValidateWithDeliveryIdAsDecimal()
    {
        $this->inputData['deliveryid'] = 204.5;
        $this->invokeMethod($this->validateCreditOrderRow, 'validate', array($this->inputData));
    }

    /**
     * @expectedException \Svea\Checkout\Exception\SveaInputValidationException
     * @expectedExceptionCode Svea\Checkout\Exception\ExceptionCodeList::INPUT_VALIDATION_ERROR
     */
    public function testValidateCreditOrderRowIdsWithOrderRowIdsAsString()
    {
        $this->inputData['orderrowids'] = '204';
        unset($this->inputData['newcreditrow']);
        $this->validateCreditOrderRow->setIsNewCreditRow(false);
        $this->invokeMethod($this->validateCreditOrderRow, 'validate', array($this->inputData));
    }

    /**
     * @expectedException \Svea\Checkout\Exception\SveaInputValidationException
     * @expectedExceptionCode Svea\Checkout\Exception\ExceptionCodeList::INPUT_VALIDATION_ERROR
     */
    public function testValidateCreditOrderRowIdsWithEmptyOrderRowIds()
    {
        $this->inputData['orderrowids'] = '';
        unset($this->inputData['newcreditrow']);
        $this->validateCreditOrderRow->setIsNewCreditRow(false);
        $this->invokeMethod($this->validateCreditOrderRow, 'validate', array($this->inputData));
    }

    public function testValidateNewCreditRowWithValidData()
    {
        unset($this->inputData['orderrowids']);
        $this->validateCreditOrderRow->setIsNewCreditRow(true);
        $this->invokeMethod($this->validateCreditOrderRow, 'validate', array($this->inputData));
    }

    /**
     * @expectedException \Svea\Checkout\Exception\SveaInputValidationException
     * @expectedExceptionCode Svea\Checkout\Exception\ExceptionCodeList::INPUT_VALIDATION_ERROR
     */
    public function testValidateCreditOrderRowIdsWithOrderRowIdsAsDecimal()
    {
        $this->inputData['orderrowids'] = 204.5;
        unset($this->inputData['newcreditrow']);
        $this->validateCreditOrderRow->setIsNewCreditRow(false);
        $this->invokeMethod($this->validateCreditOrderRow, 'validate', array($this->inputData));
    }

    /**
     * @expectedException \Svea\Checkout\Exception\SveaInputValidationException
     * @expectedExceptionCode Svea\Checkout\Exception\ExceptionCodeList::INPUT_VALIDATION_ERROR
     */
    public function testValidateCreditOrderRowIdsWithOrderRowIdAsEmptyArray()
    {
        $this->inputData['orderrowids'] = array();
        unset($this->inputData['newcreditrow']);
        $this->validateCreditOrderRow->setIsNewCreditRow(false);
        $this->invokeMethod($this->validateCreditOrderRow, 'validate', array($this->inputData));
    }

    /**
     * @expectedException \Svea\Checkout\Exception\SveaInputValidationException
     * @expectedExceptionCode Svea\Checkout\Exception\ExceptionCodeList::INPUT_VALIDATION_ERROR
     */
    public function testValidateCreditOrderRowIdsWithOrderRowIdAsString()
    {
        $this->inputData['orderrowids'] = array('1');
        unset($this->inputData['newcreditrow']);
        $this->validateCreditOrderRow->setIsNewCreditRow(false);
        $this->invokeMethod($this->validateCreditOrderRow, 'validate', array($this->inputData));
    }

    /**
     * @expectedException \Svea\Checkout\Exception\SveaInputValidationException
     * @expectedExceptionCode Svea\Checkout\Exception\ExceptionCodeList::INPUT_VALIDATION_ERROR
     */
    public function testValidateCreditOrderRowIdsWithOrderRowIdAsDecimal()
    {
        $this->inputData['orderrowids'] = array(1.1);
        unset($this->inputData['newcreditrow']);
        $this->validateCreditOrderRow->setIsNewCreditRow(false);
        $this->invokeMethod($this->validateCreditOrderRow, 'validate', array($this->inputData));
    }

    /**
     * @expectedException \Svea\Checkout\Exception\SveaInputValidationException
     * @expectedExceptionCode Svea\Checkout\Exception\ExceptionCodeList::INPUT_VALIDATION_ERROR
     */
    public function testValidateCreditOrderRowIdsWithEmptyOrderRowId()
    {
        $this->inputData['orderrowids'] = array('');
        unset($this->inputData['newcreditrow']);
        $this->validateCreditOrderRow->setIsNewCreditRow(false);
        $this->invokeMethod($this->validateCreditOrderRow, 'validate', array($this->inputData));
    }

    /**
     * @expectedException \Svea\Checkout\Exception\SveaInputValidationException
     * @expectedExceptionCode Svea\Checkout\Exception\ExceptionCodeList::INPUT_VALIDATION_ERROR
     */
    public function testValidateWithoutOrderRowIdsAndWithoutNewCreditRow()
    {
        unset($this->inputData['orderrowids']);
        unset($this->inputData['newcreditrow']);
        $this->invokeMethod($this->validateCreditOrderRow, 'validate', array($this->inputData));
    }

    /**
     * @expectedException \Svea\Checkout\Exception\SveaInputValidationException
     * @expectedExceptionCode Svea\Checkout\Exception\ExceptionCodeList::INPUT_VALIDATION_ERROR
     */
    public function testValidateNewCreditRowWithoutOrderRowIdsAndEmptyNewCreditRow()
    {
        unset($this->inputData['orderrowids']);
        $this->inputData['newcreditrow'] = array();
        $this->validateCreditOrderRow->setIsNewCreditRow(true);
        $this->invokeMethod($this->validateCreditOrderRow, 'validate', array($this->inputData));
    }

	/**
     * @expectedException \Svea\Checkout\Exception\SveaInputValidationException
     * @expectedExceptionCode Svea\Checkout\Exception\ExceptionCodeList::INPUT_VALIDATION_ERROR
     */
    public function testValidateWithEmptyRowCreditingOptions()
    {
        $this->inputData['rowcreditingoptions'] = array();
        $this->invokeMethod($this->validateCreditOrderRow, 'validate', array($this->inputData));
    }
	
    public function testValidateWithoutRowCreditingOptions()
    {
        unset($this->inputData['rowcreditingoptions']);
        $this->invokeMethod($this->validateCreditOrderRow, 'validate', array($this->inputData));
    }
}
