<?php

namespace Svea\Checkout\Tests\Unit\Implementation\Admin;

use Svea\Checkout\Implementation\Admin\CancelOrderRow;
use Svea\Checkout\Model\Request;
use Svea\Checkout\Tests\Unit\TestCase;
use Svea\Checkout\Validation\Admin\ValidateCancelOrderRowData;

class CancelOrderRowTest extends TestCase
{
    /**
     * @var CancelOrderRow
     */
    protected $cancelOrderRow;

    /**
     * @var ValidateCancelOrderRowData|\PHPUnit_Framework_MockObject_MockObject $validatorMock
     */
    protected $validatorMock;

    public function setUp()
    {
        parent::setUp();

        $this->validatorMock = $this->getMockBuilder('\Svea\Checkout\Validation\Admin\ValidateCancelOrderRowData')
            ->getMock();
        $this->cancelOrderRow = new CancelOrderRow($this->connectorMock, $this->validatorMock);
    }

    public function testPrepareData()
    {
        $inputData = array(
            'orderid' => 201,
            'orderrowid' => 1
        );
        $this->cancelOrderRow->prepareData($inputData);

        $requestModel = $this->cancelOrderRow->getRequestModel();
        $requestBodyData = json_decode($requestModel->getBody(), true);

        $this->assertEquals(Request::METHOD_PATCH, $requestModel->getMethod());
        $this->assertEquals(true, $requestBodyData['isCancelled']);
    }

    public function testInvoke()
    {
        $fakeResponse = 'Test response!!!';
        $this->connectorMock->expects($this->once())
            ->method('sendRequest')
            ->will($this->returnValue($fakeResponse));

        $createOrder = $this->cancelOrderRow;
        $createOrder->setRequestModel($this->requestModel);
        $createOrder->invoke();

        $this->assertEquals($fakeResponse, $createOrder->getResponseHandler());
    }

    public function testValidate()
    {
        $this->validatorMock->expects($this->once())
            ->method('validate');

        $getOrder = $this->cancelOrderRow;

        $getOrder->validateData($this->inputCreateData);
    }
}
