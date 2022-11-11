<?php

namespace Svea\Checkout\Tests\Unit\Implementation\Admin;

use Svea\Checkout\Implementation\Admin\UpdateOrderRow;
use Svea\Checkout\Model\Request;
use Svea\Checkout\Tests\Unit\TestCase;
use Svea\Checkout\Validation\Admin\ValidateUpdateOrderRowData;

class UpdateOrderRowTest extends TestCase
{
    /**
     * @var UpdateOrderRow
     */
    protected $updateOrderRow;

    /**
     * @var ValidateUpdateOrderRowData|\PHPUnit_Framework_MockObject_MockObject $validatorMock
     */
    protected $validatorMock;

    public function setUp()
    {
        parent::setUp();

        $this->validatorMock = $this->getMockBuilder('\Svea\Checkout\Validation\Admin\ValidateUpdateOrderRowData')
            ->getMock();
        $this->updateOrderRow = new UpdateOrderRow($this->connectorMock, $this->validatorMock);
    }

    public function testPrepareData()
    {
        $orderId = 201;
        $orderRowId = 1;

        $inputData = array(
            'orderid' => $orderId,
            'orderrowid' => $orderRowId,
            'orderrow' => array(
                'articlenumber' => '1234'
            )
        );
        $this->updateOrderRow->prepareData($inputData);

        $requestModel = $this->updateOrderRow->getRequestModel();
        $requestBodyData = json_decode($requestModel->getBody(), true);

        $this->assertEquals(Request::METHOD_PATCH, $requestModel->getMethod());
        $this->assertEquals($inputData['orderrow']['articlenumber'], $requestBodyData['articlenumber']);
    }

    public function testInvoke()
    {
        $fakeResponse = 'Test response!!!';
        $this->connectorMock->expects($this->once())
            ->method('sendRequest')
            ->will($this->returnValue($fakeResponse));

        $createOrder = $this->updateOrderRow;
        $createOrder->setRequestModel($this->requestModel);
        $createOrder->invoke();

        $this->assertEquals($fakeResponse, $createOrder->getResponseHandler());
    }

    public function testValidate()
    {
        $this->validatorMock->expects($this->once())
            ->method('validate');

        $getOrder = $this->updateOrderRow;

        $getOrder->validateData($this->inputCreateData);
    }
}
