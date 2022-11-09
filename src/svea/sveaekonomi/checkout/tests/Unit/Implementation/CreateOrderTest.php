<?php

namespace Svea\Checkout\Tests\Unit\Implementation;

use Svea\Checkout\Implementation\CreateOrder;
use Svea\Checkout\Tests\Unit\TestCase;
use Svea\Checkout\Validation\ValidateCreateOrderData;
use Svea\Checkout\Model\Request;

class CreateOrderTest extends TestCase
{
    /**
     * @var CreateOrder
     */
    protected $createOrder;

    /**
     * @var ValidateCreateOrderData|\PHPUnit_Framework_MockObject_MockObject $validatorMock
     */
    protected $validatorMock;

    public function setUp()
    {
        parent::setUp();

        $this->validatorMock = $this->getMockBuilder('\Svea\Checkout\Validation\ValidateCreateOrderData')->getMock();
        $this->createOrder = new CreateOrder($this->connectorMock, $this->validatorMock);
    }

    public function testPrepareData()
    {
        $this->connectorMock->expects($this->once())
            ->method('getBaseApiUrl');

        $this->createOrder->prepareData($this->inputCreateData);

        $requestModel = $this->createOrder->getRequestModel();
        $requestBodyData = json_decode($requestModel->getBody(), true);

        $this->assertEquals(Request::METHOD_POST, $requestModel->getMethod());
        $this->assertEquals($this->inputCreateData['locale'], $requestBodyData['locale']);
        $this->assertEquals($this->inputCreateData['countrycode'], $requestBodyData['countrycode']);
        $this->assertEquals($this->inputCreateData['currency'], $requestBodyData['currency']);

        $items = $requestBodyData['cart']['items'];

        $expectedItems = $requestBodyData['cart']['items'];
        $this->assertEquals($expectedItems[0]['articlenumber'], $items[0]['articlenumber']);
        $this->assertEquals($expectedItems[0]['quantity'], $items[0]['quantity']);

        $merchantSettings = $requestBodyData['merchantsettings'];
        $expectedMerchantSettings = $this->inputCreateData['merchantsettings'];
        $this->assertEquals($expectedMerchantSettings['termsuri'], $merchantSettings['termsuri']);
        $this->assertEquals($expectedMerchantSettings['checkouturi'], $merchantSettings['checkouturi']);
        $this->assertEquals($expectedMerchantSettings['confirmationuri'], $merchantSettings['confirmationuri']);
        $this->assertEquals($expectedMerchantSettings['pushuri'], $merchantSettings['pushuri']);
    }

    public function testInvoke()
    {
        $fakeResponse = 'Test response!!!';
        $this->connectorMock->expects($this->once())
            ->method('sendRequest')
            ->will($this->returnValue($fakeResponse));

        $createOrder = $this->createOrder;
        $createOrder->setRequestModel($this->requestModel);
        $createOrder->invoke();

        $this->assertEquals($fakeResponse, $createOrder->getResponseHandler());
    }

    public function testValidate()
    {
        $this->validatorMock->expects($this->once())
            ->method('validate');

        $getOrder = $this->createOrder;

        $getOrder->validateData($this->inputCreateData);
    }
}
