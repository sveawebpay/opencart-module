<?php

namespace Svea\Checkout\Tests\Unit\Implementation\Admin;

use Svea\Checkout\Implementation\Admin\GetTask;
use Svea\Checkout\Model\Request;
use Svea\Checkout\Tests\Unit\TestCase;
use Svea\Checkout\Validation\Admin\ValidateGetTaskData;

class GetTaskTest extends TestCase
{
    /**
     * @var ValidateGetTaskData|\PHPUnit_Framework_MockObject_MockObject $validatorMock
     */
    protected $validatorMock;

    /**
     * @var GetTask
     */
    protected $getTask;

    public function setUp()
    {
        parent::setUp();

        $this->validatorMock = $this->getMockBuilder('\Svea\Checkout\Validation\Admin\ValidateGetTaskData')->getMock();
        $this->getTask = new GetTask($this->connectorMock, $this->validatorMock);
    }

    public function testPrepareData()
    {
        $locationUrl = 'http://webpaypaymentadminapi.test.svea.com/api/v1/queue/1';
        $data = array('locationurl' => $locationUrl);
        $this->getTask->prepareData($data);

        $requestModel = $this->getTask->getRequestModel();
        $requestModel->getApiUrl();
        $this->assertInstanceOf('\Svea\Checkout\Model\Request', $requestModel);
        $this->assertEquals(Request::METHOD_GET, $requestModel->getMethod());
        $this->assertEquals($locationUrl, $requestModel->getApiUrl());
    }

    public function testInvoke()
    {
        $fakeResponse = 'Test response!!!';
        $this->connectorMock->expects($this->once())
            ->method('sendRequest')
            ->will($this->returnValue(($fakeResponse)));

        $this->requestModel->setGetMethod();
        $this->requestModel->setBody(null);
        $this->getTask->setRequestModel($this->requestModel);

        $this->getTask->invoke();

        $this->assertEquals($fakeResponse, $this->getTask->getResponseHandler());
    }

    public function testValidate()
    {
        $data = array('locationurl' => 'http://webpaypaymentadminapi.test.svea.com/api/v1/queue/1');
        $this->validatorMock->expects($this->once())
            ->method('validate');

        $this->getTask->validateData($data);
    }
}
