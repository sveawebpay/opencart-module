<?php

namespace Svea\Checkout\Tests\Unit\Implementation;

use Svea\Checkout\Implementation\GetAvailablePartPaymentCampaigns;
use Svea\Checkout\Implementation\GetOrder;
use Svea\Checkout\Model\Request;
use Svea\Checkout\Tests\Unit\TestCase;
use Svea\Checkout\Validation\ValidateGetOrderData;

class GetAvailablePartPaymentCampaignsTest extends TestCase
{
    /**
     * @var ValidateGetOrderData|\PHPUnit_Framework_MockObject_MockObject $validatorMock
     */
    protected $validatorMock;

    /**
     * @var GetAvailablePartPaymentCampaigns
     */
    protected $getAvailablePartPaymentCampaigns;

    public function setUp()
    {
        parent::setUp();

        $this->validatorMock = $this->getMockBuilder('\Svea\Checkout\Validation\ValidateGetAvailablePartPaymentCampaignsData')->getMock();
        $this->getAvailablePartPaymentCampaigns = new GetAvailablePartPaymentCampaigns($this->connectorMock, $this->validatorMock);
    }

    public function testPrepareData()
    {
        $this->connectorMock->expects($this->once())
            ->method('getBaseApiUrl');

        $data = array('iscompany' => true);
        $this->getAvailablePartPaymentCampaigns->prepareData($data);

        $requestModel = $this->getAvailablePartPaymentCampaigns->getRequestModel();

        $this->assertInstanceOf('\Svea\Checkout\Model\Request', $requestModel);
        $this->assertEquals(Request::METHOD_GET, $requestModel->getMethod());
    }

    public function testInvoke()
    {
        $fakeResponse = 'Test response!!!';
        $this->connectorMock->expects($this->once())
            ->method('sendRequest')
            ->will($this->returnValue(($fakeResponse)));

        $this->requestModel->setGetMethod();
        $this->requestModel->setBody(null);
        $this->getAvailablePartPaymentCampaigns->setRequestModel($this->requestModel);

        $this->getAvailablePartPaymentCampaigns->invoke();

        $this->assertEquals($fakeResponse, $this->getAvailablePartPaymentCampaigns->getResponseHandler());
    }

    public function testValidate()
    {
        $data = array('iscompany' => false);
        $this->validatorMock->expects($this->once())
            ->method('validate');

        $this->getAvailablePartPaymentCampaigns->validateData($data);
    }
}
