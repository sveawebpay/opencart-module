<?php

namespace Svea\Checkout\Tests\Unit\Implementation;

use Svea\Checkout\Implementation\UpdateOrder;
use Svea\Checkout\Model\Request;
use Svea\Checkout\Tests\Unit\TestCase;
use Svea\Checkout\Transport\ResponseHandler;
use Svea\Checkout\Validation\ValidateUpdateOrderData;

class UpdateOrderTest extends TestCase
{
    /**
     * @var UpdateOrder
     */
    protected $updateOrder;

    /**
     * @var ValidateUpdateOrderData|\PHPUnit_Framework_MockObject_MockObject $validatorMock
     */
    protected $validatorMock;

    public function setUp()
    {
        parent::setUp();

        $this->validatorMock = $this->getMockBuilder('\Svea\Checkout\Validation\ValidateUpdateOrderData')->getMock();
        $this->updateOrder = new UpdateOrder($this->connectorMock, $this->validatorMock);
    }

    public function testPrepareData()
    {
        $this->connectorMock->expects($this->once())
            ->method('getBaseApiUrl');

        $this->updateOrder->prepareData($this->inputUpdateData);

        $requestModel = $this->updateOrder->getRequestModel();
        $requestBodyData = json_decode($requestModel->getBody(), true);

        $items = $requestBodyData['cart']['items'];

        $this->assertEquals(Request::METHOD_PUT, $requestModel->getMethod());

        $expectedItems = $this->inputUpdateData['cart']['items'];
        $this->assertEquals($expectedItems[0]['articlenumber'], $items[0]['articlenumber']);
        $this->assertEquals($expectedItems[0]['quantity'], $items[0]['quantity']);
    }

    public function testInvoke()
    {
        $fakeResponse = 'Test response!!!';
        $this->connectorMock->expects($this->once())
            ->method('sendRequest')
            ->will($this->returnValue($fakeResponse));

        $updateOrder = $this->updateOrder;
        $updateOrder->setRequestModel($this->requestModel);
        $updateOrder->invoke();

        $this->assertEquals($fakeResponse, $updateOrder->getResponseHandler());
    }

    public function testValidate()
    {
        $this->validatorMock->expects($this->once())
            ->method('validate');

        $getOrder = $this->updateOrder;

        $getOrder->validateData($this->inputCreateData);
    }
}
