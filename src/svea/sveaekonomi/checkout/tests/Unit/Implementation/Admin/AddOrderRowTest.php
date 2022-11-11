<?php

namespace Svea\Checkout\Tests\Unit\Implementation\Admin;

use Svea\Checkout\Implementation\Admin\AddOrderRow;
use Svea\Checkout\Model\Request;
use Svea\Checkout\Tests\Unit\TestCase;
use Svea\Checkout\Validation\Admin\ValidateAddOrderRowData;

class AddOrderRowTest extends TestCase
{
    /**
     * @var AddOrderRow
     */
    protected $addOrderRow;

    /**
     * @var ValidateAddOrderRowData|\PHPUnit_Framework_MockObject_MockObject $validatorMock
     */
    protected $validatorMock;

    public function setUp()
    {
        parent::setUp();

        $this->validatorMock = $this->getMockBuilder('\Svea\Checkout\Validation\Admin\ValidateAddOrderRowData')
            ->getMock();
        $this->addOrderRow = new AddOrderRow($this->connectorMock, $this->validatorMock);
    }

    public function testPrepareData()
    {
        $orderId = 201;

        $inputData = array(
            'orderid' => $orderId,
            'orderrow' => array(
                "articlenumber" => "prod-01",
                "name" => "someProd",
                "quantity" => 300,
                "unitprice" => 5000,
                "vatpercent" => 0,
                "unit" => "pc"
            )
        );
        $this->addOrderRow->prepareData($inputData);

        $requestModel = $this->addOrderRow->getRequestModel();
        $requestBodyData = json_decode($requestModel->getBody(), true);

        $this->assertEquals(Request::METHOD_POST, $requestModel->getMethod());
        $this->assertEquals($inputData['orderrow']['articlenumber'], $requestBodyData['articlenumber']);
        $this->assertEquals($inputData['orderrow']['name'], $requestBodyData['name']);
        $this->assertEquals($inputData['orderrow']['quantity'], $requestBodyData['quantity']);
        $this->assertEquals($inputData['orderrow']['unitprice'], $requestBodyData['unitprice']);
        $this->assertEquals($inputData['orderrow']['vatpercent'], $requestBodyData['vatpercent']);
        $this->assertEquals($inputData['orderrow']['unit'], $requestBodyData['unit']);
    }

    public function testInvoke()
    {
        $fakeResponse = 'Test response!!!';
        $this->connectorMock->expects($this->once())
            ->method('sendRequest')
            ->will($this->returnValue($fakeResponse));

        $createOrder = $this->addOrderRow;
        $createOrder->setRequestModel($this->requestModel);
        $createOrder->invoke();

        $this->assertEquals($fakeResponse, $createOrder->getResponseHandler());
    }

    public function testValidate()
    {
        $this->validatorMock->expects($this->once())
            ->method('validate');

        $getOrder = $this->addOrderRow;

        $getOrder->validateData($this->inputCreateData);
    }
}
