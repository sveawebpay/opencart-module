<?php

namespace Svea\Checkout\Tests\Unit\Implementation\Admin;

use Svea\Checkout\Implementation\Admin\DeliverOrder;
use Svea\Checkout\Model\Request;
use Svea\Checkout\Tests\Unit\TestCase;
use Svea\Checkout\Validation\Admin\ValidateDeliverOrderData;

class DeliverOrderTest extends TestCase
{
    /**
     * @var DeliverOrder
     */
    protected $deliverOrder;

    /**
     * @var ValidateDeliverOrderData|\PHPUnit_Framework_MockObject_MockObject $validatorMock
     */
    protected $validatorMock;

    public function setUp()
    {
        parent::setUp();

        $this->validatorMock = $this->getMockBuilder('\Svea\Checkout\Validation\Admin\ValidateDeliverOrderData')
            ->getMock();
        $this->deliverOrder = new DeliverOrder($this->connectorMock, $this->validatorMock);
    }

    public function testPrepareData()
    {
        $inputData = array(
            'orderid' => 201,
            'orderrowids' => array(1, 2)
        );
        $this->deliverOrder->prepareData($inputData);

        $requestModel = $this->deliverOrder->getRequestModel();
        $requestBodyData = json_decode($requestModel->getBody(), true);

        $this->assertEquals(Request::METHOD_POST, $requestModel->getMethod());
        $this->assertEquals($inputData['orderrowids'], $requestBodyData['orderRowIds']);
    }

    public function testInvoke()
    {
        $fakeResponse = 'Test response!!!';
        $this->connectorMock->expects($this->once())
            ->method('sendRequest')
            ->will($this->returnValue($fakeResponse));

        $createOrder = $this->deliverOrder;
        $createOrder->setRequestModel($this->requestModel);
        $createOrder->invoke();

        $this->assertEquals($fakeResponse, $createOrder->getResponseHandler());
    }

    public function testValidate()
    {
        $this->validatorMock->expects($this->once())
            ->method('validate');

        $getOrder = $this->deliverOrder;

        $getOrder->validateData($this->inputCreateData);
    }
}
