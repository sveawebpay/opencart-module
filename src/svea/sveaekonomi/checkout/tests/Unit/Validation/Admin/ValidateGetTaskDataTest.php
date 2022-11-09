<?php

namespace Svea\Checkout\Tests\Unit\Validation\Admin;

use Svea\Checkout\Tests\Unit\TestCase;
use Svea\Checkout\Validation\Admin\ValidateGetTaskData;

class ValidateGetTaskDataTest extends TestCase
{
    /**
     * @expectedException \Svea\Checkout\Exception\SveaInputValidationException
     * @expectedExceptionCode Svea\Checkout\Exception\ExceptionCodeList::INPUT_VALIDATION_ERROR
     */
    public function testValidateGetTaskWithoutLocationUrl()
    {
        $data = array();
        $validateGetOrder = new ValidateGetTaskData();
        $validateGetOrder->validate($data);
    }

    /**
     * @expectedException \Svea\Checkout\Exception\SveaInputValidationException
     * @expectedExceptionCode Svea\Checkout\Exception\ExceptionCodeList::INPUT_VALIDATION_ERROR
     */
    public function testValidateGetTaskWithEmptyLocationUrl()
    {
        $data = array(
            'locationUrl' => ''
        );
        $validateGetOrder = new ValidateGetTaskData();
        $validateGetOrder->validate($data);
    }

    /**
     * @expectedException \Svea\Checkout\Exception\SveaInputValidationException
     * @expectedExceptionCode Svea\Checkout\Exception\ExceptionCodeList::INPUT_VALIDATION_ERROR
     */
    public function testValidateGetTaskWithInvalidLocationUrl()
    {
        $data = array(
            'locationUrl' => 'dev.svea.com'
        );
        $validateGetOrder = new ValidateGetTaskData();
        $validateGetOrder->validate($data);
    }

    /**
     * @expectedException \Svea\Checkout\Exception\SveaInputValidationException
     * @expectedExceptionCode Svea\Checkout\Exception\ExceptionCodeList::INPUT_VALIDATION_ERROR
     */
    public function testValidateGetTaskWithNullLocationUrl()
    {
        $data = array(
            'locationUrl' => null
        );
        $validateGetOrder = new ValidateGetTaskData();
        $validateGetOrder->validate($data);
    }

    public function testValidateGetTaskWithValidLocationUrl()
    {
        $data = array(
            'locationurl' => 'http://webpaypaymentadminapi.test.svea.com/api/v1/queue/1'
        );
        $validateGetOrder = new ValidateGetTaskData();
        $validateGetOrder->validate($data);
    }
}
