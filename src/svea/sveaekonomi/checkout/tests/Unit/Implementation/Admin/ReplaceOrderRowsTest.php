<?php

namespace Svea\Checkout\Tests\Unit\Implementation\Admin;

use Svea\Checkout\Implementation\Admin\ReplaceOrderRows;
use Svea\Checkout\Model\Request;
use Svea\Checkout\Tests\Unit\TestCase;
use Svea\Checkout\Validation\Admin\ValidateReplaceOrderRowsData;

class ReplaceOrderRowTest extends TestCase
{
    /**
     * @var ReplaceOrderRows
     */
    protected $replaceOrderRows;

    /**
     * @var ValidateReplaceOrderRowsData|\PHPUnit_Framework_MockObject_MockObject $validatorMock
     */
    protected $validatorMock;

    public function setUp()
    {
        parent::setUp();

        $this->validatorMock = $this->getMockBuilder('\Svea\Checkout\Validation\Admin\ValidateReplaceOrderRowsData')
            ->getMock();
        $this->replaceOrderRows = new ReplaceOrderRows($this->connectorMock, $this->validatorMock);
    }

    public function testPrepareData()
    {
        $orderId = 201;

        $inputData = array(
            'orderid' => $orderId,
            'orderrows' => array(
				array(
					"articlenumber" => "prod-01",
					"name" => "someProd",
					"quantity" => 300,
					"unitprice" => 5000,
					"vatpercent" => 0,
					"unit" => "pc"
				),
				array(
					"articlenumber" => "prod-02",
					"name" => "someProd 2",
					"quantity" => 500,
					"unitprice" => 2000,
					"vatpercent" => 0,
					"unit" => "st"
				)
            )
        );
        $this->replaceOrderRows->prepareData($inputData);

        $requestModel = $this->replaceOrderRows->getRequestModel();
        $requestBodyData = json_decode($requestModel->getBody(), true);

        $this->assertEquals(Request::METHOD_PUT, $requestModel->getMethod());

		foreach ($inputData['orderrows'] as $key => $orderRow) {
			$this->assertEquals($orderRow['articlenumber'], $requestBodyData['orderRows'][$key]['articlenumber']);
			$this->assertEquals($orderRow['name'], $requestBodyData['orderRows'][$key]['name']);
			$this->assertEquals($orderRow['quantity'], $requestBodyData['orderRows'][$key]['quantity']);
			$this->assertEquals($orderRow['unitprice'], $requestBodyData['orderRows'][$key]['unitprice']);
			$this->assertEquals($orderRow['vatpercent'], $requestBodyData['orderRows'][$key]['vatpercent']);
			$this->assertEquals($orderRow['unit'], $requestBodyData['orderRows'][$key]['unit']);
		}
    }

    public function testInvoke()
    {
        $fakeResponse = 'Test response!!!';
        $this->connectorMock->expects($this->once())
            ->method('sendRequest')
            ->will($this->returnValue($fakeResponse));

        $createOrder = $this->replaceOrderRows;
        $createOrder->setRequestModel($this->requestModel);
        $createOrder->invoke();

        $this->assertEquals($fakeResponse, $createOrder->getResponseHandler());
    }

    public function testValidate()
    {
        $this->validatorMock->expects($this->once())
            ->method('validate');

        $getOrder = $this->replaceOrderRows;

        $getOrder->validateData($this->inputCreateData);
    }
}
