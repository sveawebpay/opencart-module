<?php

namespace Svea\Checkout\Tests\Unit\Validation;

use Svea\Checkout\Tests\Unit\TestCase;
use Svea\Checkout\Validation\ValidateGetOrderData;

class ValidateGetOrderDataTest extends TestCase
{
    public function testValidateWithOrderIdAsIntegerInArray()
    {
        $orderId = array(
            'orderid' => 1234
        );
        $validateGetOrder = new ValidateGetOrderData();
        $validateGetOrder->validate($orderId);
    }

    /**
     * @expectedException \Svea\Checkout\Exception\SveaInputValidationException
     * @expectedExceptionCode Svea\Checkout\Exception\ExceptionCodeList::INPUT_VALIDATION_ERROR
     */
    public function testValidateWithOrderIdAsInteger()
    {
        $orderId = 1234;
        $validateGetOrder = new ValidateGetOrderData();
        $validateGetOrder->validate($orderId);
    }

    /**
     * @expectedException \Svea\Checkout\Exception\SveaInputValidationException
     * @expectedExceptionCode Svea\Checkout\Exception\ExceptionCodeList::INPUT_VALIDATION_ERROR
     */
    public function testValidateWithoutOrderId()
    {
        $orderId = array();
        $validateGetOrder = new ValidateGetOrderData();
        $validateGetOrder->validate($orderId);
    }

    /**
     * @expectedException \Svea\Checkout\Exception\SveaInputValidationException
     * @expectedExceptionCode Svea\Checkout\Exception\ExceptionCodeList::INPUT_VALIDATION_ERROR
     */
    public function testValidateWithOrderIdIntAsString()
    {
        $orderId = '1234';
        $validateGetOrder = new ValidateGetOrderData();
        $validateGetOrder->validate($orderId);
    }

    /**
     * @expectedException \Svea\Checkout\Exception\SveaInputValidationException
     * @expectedExceptionCode Svea\Checkout\Exception\ExceptionCodeList::INPUT_VALIDATION_ERROR
     */
    public function testValidateWithOrderIdString()
    {
        $orderId = 'svea';
        $validateGetOrder = new ValidateGetOrderData();
        $validateGetOrder->validate($orderId);
    }

    /**
     * @expectedException \Svea\Checkout\Exception\SveaInputValidationException
     * @expectedExceptionCode Svea\Checkout\Exception\ExceptionCodeList::INPUT_VALIDATION_ERROR
     */
    public function testValidateWithEmptyString()
    {
        $orderId = '';
        $validateGetOrder = new ValidateGetOrderData();
        $validateGetOrder->validate($orderId);
    }

    /**
     * @expectedException \Svea\Checkout\Exception\SveaInputValidationException
     * @expectedExceptionCode Svea\Checkout\Exception\ExceptionCodeList::INPUT_VALIDATION_ERROR
     */
    public function testValidateWithOrderIdAsStringInArray()
    {
        $data = array(
            'orderid' => '1234'
        );
        $validateGetOrder = new ValidateGetOrderData();
        $validateGetOrder->validate($data);
    }

    /**
     * @expectedException \Svea\Checkout\Exception\SveaInputValidationException
     * @expectedExceptionCode Svea\Checkout\Exception\ExceptionCodeList::INPUT_VALIDATION_ERROR
     */
    public function testValidateWithEmptyStringInArray()
    {
        $data = array(
            'orderid' => ''
        );
        $validateGetOrder = new ValidateGetOrderData();
        $validateGetOrder->validate($data);
    }
}
