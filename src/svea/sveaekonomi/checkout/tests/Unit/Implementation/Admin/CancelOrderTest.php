<?php

namespace Svea\Checkout\Tests\Unit\Implementation\Admin;

use Svea\Checkout\Implementation\Admin\CancelOrder;
use Svea\Checkout\Model\Request;
use Svea\Checkout\Tests\Unit\TestCase;
use Svea\Checkout\Validation\Admin\ValidateCancelOrderData;

class CancelOrderTest extends TestCase
{
    /**
     * @var CancelOrder
     */
    protected $cancelOrder;

    /**
     * @var ValidateCancelOrderData|\PHPUnit_Framework_MockObject_MockObject $validatorMock
     */
    protected $validatorMock;

    public function setUp()
    {
        parent::setUp();

        $this->validatorMock = $this->getMockBuilder('\Svea\Checkout\Validation\Admin\ValidateCancelOrderData')
            ->getMock();
        $this->cancelOrder = new CancelOrder($this->connectorMock, $this->validatorMock);
    }

    public function testPrepareDataCancelOrder()
    {
        $inputData = array(
            'orderid' => 1,
            'cancelledamount' => 15000
        );

        $this->cancelOrder->prepareData($inputData);

        $requestModel = $this->cancelOrder->getRequestModel();
        $requestBodyData = json_decode($requestModel->getBody(), true);

        $this->assertEquals(Request::METHOD_PATCH, $requestModel->getMethod());
        $this->assertArrayNotHasKey('cancelledamount', $requestBodyData);
    }

    public function testPrepareDataCancelOrderAmount()
    {
        $inputData = array(
            'orderid' => 1,
            'cancelledamount' => 15000
        );

        $this->cancelOrder->setIsCancelAmount(true);
        $this->cancelOrder->prepareData($inputData);

        $requestModel = $this->cancelOrder->getRequestModel();
        $requestBodyData = json_decode($requestModel->getBody(), true);

        $this->assertEquals(Request::METHOD_PATCH, $requestModel->getMethod());
        $this->assertEquals($inputData['cancelledamount'], $requestBodyData['cancelledAmount']);
    }

    public function testInvoke()
    {
        $fakeResponse = 'Test response!!!';
        $this->connectorMock->expects($this->once())
            ->method('sendRequest')
            ->will($this->returnValue($fakeResponse));

        $createOrder = $this->cancelOrder;
        $createOrder->setRequestModel($this->requestModel);
        $createOrder->invoke();

        $this->assertEquals($fakeResponse, $createOrder->getResponseHandler());
    }

    public function testValidate()
    {
        $this->validatorMock->expects($this->once())
            ->method('validate');

        $getOrder = $this->cancelOrder;

        $getOrder->validateData($this->inputCreateData);
    }
}
