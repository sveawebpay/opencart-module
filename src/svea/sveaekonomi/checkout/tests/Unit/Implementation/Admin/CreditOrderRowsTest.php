<?php

namespace Svea\Checkout\Tests\Unit\Implementation\Admin;

use Svea\Checkout\Implementation\Admin\CreditOrderRows;
use Svea\Checkout\Model\Request;
use Svea\Checkout\Tests\Unit\TestCase;
use Svea\Checkout\Validation\Admin\ValidateCreditOrderRowsData;

class CreditOrderRowsTest extends TestCase
{
    /**
     * @var CreditOrderRows
     */
    protected $creditOrderRows;

    /**
     * @var ValidateCreditOrderRowsData|\PHPUnit_Framework_MockObject_MockObject $validatorMock
     */
    protected $validatorMock;

    public function setUp()
    {
        parent::setUp();

        $this->validatorMock = $this->getMockBuilder('\Svea\Checkout\Validation\Admin\ValidateCreditOrderRowsData')
            ->getMock();
        $this->creditOrderRows = new CreditOrderRows($this->connectorMock, $this->validatorMock);
    }

    public function testPrepareData()
    {
        $inputData = array(
            'orderid' => 201,
            'deliveryid' => 1,
            'orderrowids' => array(1, 2)
        );
        $this->creditOrderRows->prepareData($inputData);

        $requestModel = $this->creditOrderRows->getRequestModel();
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

        $createOrder = $this->creditOrderRows;
        $createOrder->setRequestModel($this->requestModel);
        $createOrder->invoke();

        $this->assertEquals($fakeResponse, $createOrder->getResponseHandler());
    }

    public function testValidate()
    {
        $this->validatorMock->expects($this->once())
            ->method('validate');

        $getOrder = $this->creditOrderRows;

        $getOrder->validateData($this->inputCreateData);
    }
}
