<?php

namespace Svea\Checkout\Tests\Unit\Implementation\Admin;

use Svea\Checkout\Implementation\Admin\CreditOrderRowsWithFee;
use Svea\Checkout\Model\Request;
use Svea\Checkout\Tests\Unit\TestCase;

class CreditOrderRowsWithFeeTest extends TestCase
{
    /**
     * @var CreditOrderRowsWithFee
     */
    protected $creditOrderRowsWithFee;

    /**
     * @var ValidateCreditOrderRowsWithFee|\PHPUnit_Framework_MockObject_MockObject $validatorMock
     */
    protected $validatorMock;

	/**
	 * Setup the test
	 *
	 * @return void
	 */
    public function setUp()
    {
        parent::setUp();

        $this->validatorMock = $this->getMockBuilder('\Svea\Checkout\Validation\Admin\ValidateCreditOrderRowsWithFeeData')
            ->getMock();
        $this->creditOrderRowsWithFee = new CreditOrderRowsWithFee($this->connectorMock, $this->validatorMock);
    }

	/**
	 * Test the prepare data method
	 *
	 * @return void
	 */
    public function testPrepareData()
    {
        $inputData = array(
            'orderid' => 201,
            'deliveryid' => 1,
            'orderrowids' => array(1, 2),
			'fee' => array(
				'articlenumber' => '123456',
				'name' => 'Tomatoes',
				'quantity' => 10,
				'unitprice' => 600,
				'discountpercent' => 1000,
				'vatpercent' => 2500
			),
			'rowcreditingoptions' => array(
				array(
					'orderrowid' => 1,
					'quantity' => 1,
				)
			)
        );
        $this->creditOrderRowsWithFee->prepareData($inputData);

        $requestModel = $this->creditOrderRowsWithFee->getRequestModel();
        $requestBodyData = json_decode($requestModel->getBody(), true);

        $this->assertEquals(Request::METHOD_POST, $requestModel->getMethod());
        $this->assertEquals($inputData['orderrowids'], $requestBodyData['orderRowIds']);
		$this->assertEquals($inputData['fee'], $requestBodyData['fee']);
		$this->assertEquals($inputData['rowcreditingoptions'], $requestBodyData['rowCreditingOptions']);
    }

	/**
	 * Test the invoke method
	 *
	 * @return void
	 */
    public function testInvoke()
    {
        $fakeResponse = 'Test response!!!';
        $this->connectorMock->expects($this->once())
            ->method('sendRequest')
            ->will($this->returnValue($fakeResponse));

        $createOrder = $this->creditOrderRowsWithFee;
        $createOrder->setRequestModel($this->requestModel);
        $createOrder->invoke();

        $this->assertEquals($fakeResponse, $createOrder->getResponseHandler());
    }

	/**
	 * Test the validate method
	 *
	 * @return void
	 */
    public function testValidate()
    {
        $this->validatorMock->expects($this->once())
            ->method('validate');

        $getOrder = $this->creditOrderRowsWithFee;

        $getOrder->validateData($this->inputCreateData);
    }
}
