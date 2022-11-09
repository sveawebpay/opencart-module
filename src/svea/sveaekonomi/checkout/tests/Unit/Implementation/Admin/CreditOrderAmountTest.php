<?php

namespace Svea\Checkout\Tests\Unit\Implementation\Admin;

use Svea\Checkout\Implementation\Admin\CreditOrderAmount;
use Svea\Checkout\Model\Request;
use Svea\Checkout\Tests\Unit\TestCase;
use Svea\Checkout\Validation\Admin\ValidateCreditOrderAmountData;

class CreditOrderAmountTest extends TestCase
{
    /**
     * @var CreditOrderAmount $creditOrderAmount
     */
    protected $creditOrderAmount;

    /**
     * @var ValidateCreditOrderAmountData|\PHPUnit_Framework_MockObject_MockObject $validatorMock
     */
    protected $validatorMock;

    public function setUp()
    {
        parent::setUp();

        $this->validatorMock = $this->getMockBuilder('\Svea\Checkout\Validation\Admin\ValidateCreditOrderAmountData')
            ->getMock();
        $this->creditOrderAmount = new CreditOrderAmount($this->connectorMock, $this->validatorMock);
    }

    public function testPrepareData()
    {
        $inputData = array(
            "orderid" => 204,
            "deliveryid" => 1,
            "creditedamount" => 2000,
        );
        $this->creditOrderAmount->prepareData($inputData);

        $requestModel = $this->creditOrderAmount->getRequestModel();
        $requestBodyData = json_decode($requestModel->getBody(), true);

        $this->assertEquals(Request::METHOD_PATCH, $requestModel->getMethod());
        $this->assertEquals($inputData['creditedamount'], $requestBodyData['creditedAmount']);
    }

    public function testInvoke()
    {
        $fakeResponse = 'Test response!!!';
        $this->connectorMock->expects($this->once())
            ->method('sendRequest')
            ->will($this->returnValue($fakeResponse));

        $createOrder = $this->creditOrderAmount;
        $createOrder->setRequestModel($this->requestModel);
        $createOrder->invoke();

        $this->assertEquals($fakeResponse, $createOrder->getResponseHandler());
    }

    public function testValidate()
    {
        $this->validatorMock->expects($this->once())
            ->method('validate');

        $getOrder = $this->creditOrderAmount;

        $getOrder->validateData($this->inputCreateData);
    }
}
