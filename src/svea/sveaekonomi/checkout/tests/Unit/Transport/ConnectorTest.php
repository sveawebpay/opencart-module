<?php

namespace Svea\Checkout\Tests\Unit\Transport;

use \Exception;
use Svea\Checkout\Tests\Unit\TestCase;
use Svea\Checkout\Transport\Connector;
use Svea\Checkout\Exception\SveaApiException;

class ConnectorTest extends TestCase
{
    public function testCreateMatchesDataGiven()
    {
        $connector = new Connector(
            $this->apiClientMock,
            $this->merchantId,
            $this->sharedSecret,
            $this->apiUrl
        );

        $this->assertInstanceOf('\Svea\Checkout\Transport\ApiClient', $connector->getApiClient());
        $this->assertEquals($this->merchantId, $connector->getMerchantId());
        $this->assertEquals($this->sharedSecret, $connector->getSharedSecret());
        $this->assertEquals($this->apiUrl, $connector->getBaseApiUrl());
    }

    /**
     * @expectedException \Svea\Checkout\Exception\SveaConnectorException
     * @expectedExceptionCode \Svea\Checkout\Exception\ExceptionCodeList::MISSING_MERCHANT_ID
     */
    public function testValidateMerchantIdWithInvalidMerchantId()
    {
        $this->merchantId = '';
        $connector = new Connector($this->apiClientMock, $this->merchantId, $this->sharedSecret, $this->apiUrl);

        $this->invokeMethod($connector, 'validateMerchantId');
    }

    public function testValidateMerchantIdWithValidMerchantId()
    {
        $this->merchantId = '123';
        $connector = new Connector($this->apiClientMock, $this->merchantId, $this->sharedSecret, $this->apiUrl);

        $this->invokeMethod($connector, 'validateMerchantId');
    }

    /**
     * @expectedException \Svea\Checkout\Exception\SveaConnectorException
     * @expectedExceptionCode \Svea\Checkout\Exception\ExceptionCodeList::MISSING_SHARED_SECRET
     */
    public function testValidateSharedSecretWithInvalidSharedSecret()
    {
        $this->sharedSecret = '';
        $connector = new Connector($this->apiClientMock, $this->merchantId, $this->sharedSecret, $this->apiUrl);

        $this->invokeMethod($connector, 'validateSharedSecret');
    }

    public function testValidateSharedSecretWithValidSharedSecret()
    {
        $this->sharedSecret = 'sharedSecret';
        $connector = new Connector($this->apiClientMock, $this->merchantId, $this->sharedSecret, $this->apiUrl);

        $this->invokeMethod($connector, 'validateSharedSecret');
    }

    /**
     * @expectedException \Svea\Checkout\Exception\SveaConnectorException
     * @expectedExceptionCode \Svea\Checkout\Exception\ExceptionCodeList::MISSING_API_BASE_URL
     */
    public function testValidateBaseApiUrlWithoutApiUrl()
    {
        $this->apiUrl = '';
        $connector = new Connector($this->apiClientMock, $this->merchantId, $this->sharedSecret, $this->apiUrl);

        $this->invokeMethod($connector, 'validateBaseApiUrl');
    }

    /**
     * @expectedException \Svea\Checkout\Exception\SveaConnectorException
     * @expectedExceptionCode \Svea\Checkout\Exception\ExceptionCodeList::INCORRECT_API_BASE_URL
     */
    public function testValidateBaseApiUrlWithBadApiUrl()
    {
        $this->apiUrl = 'http://invalid.url.svea.com';
        $connector = new Connector($this->apiClientMock, $this->merchantId, $this->sharedSecret, $this->apiUrl);

        $this->invokeMethod($connector, 'validateBaseApiUrl');
    }

    public function testValidateBaseApiUrlWithValidApiUrl()
    {
        $this->apiUrl = Connector::TEST_BASE_URL;
        $connector = new Connector($this->apiClientMock, $this->merchantId, $this->sharedSecret, $this->apiUrl);

        $this->invokeMethod($connector, 'validateBaseApiUrl');
    }

    public function testSendRequestAndReceiveSuccessfulResponse()
    {
        $responseContent = $this->apiResponse;
        $httpCode = 201;
        $responseHandler = $this->getMockBuilder('\Svea\Checkout\Transport\ResponseHandler')
            ->setConstructorArgs(array($responseContent, $httpCode))
            ->getMock();

        $responseHandler->expects($this->once())
            ->method('getContent')
            ->will($this->returnValue($responseContent));

        $this->apiClientMock->expects($this->once())
            ->method('sendRequest')
            ->will($this->returnValue($responseHandler));

        $connector = new Connector($this->apiClientMock, $this->merchantId, $this->sharedSecret, $this->apiUrl);
        $response = $connector->sendRequest($this->requestModel)->getContent();

        $this->assertEquals($responseContent, $response);
    }

    /**
     * @expectedException \Svea\Checkout\Exception\SveaApiException
     * @expectedExceptionMessage The input data was bad
     */
    public function testSendRequestSveaApiExceptionThrown()
    {
        $sveaApiException = new SveaApiException('The input data was bad', 1000);

        $this->apiClientMock->expects($this->once())
            ->method('sendRequest')
            ->with($this->identicalTo($this->requestModel))
            ->will($this->throwException($sveaApiException));

        $connector = new Connector($this->apiClientMock, $this->merchantId, $this->sharedSecret, $this->apiUrl);
        $connector->sendRequest($this->requestModel);
    }

    /**
     * @expectedException \Svea\Checkout\Exception\SveaApiException
     * @expectedExceptionMessage API communication error
     */
    public function testSendRequestGeneralExceptionThrown()
    {
        $ex = new Exception('General error');

        $this->apiClientMock->expects($this->once())
            ->method('sendRequest')
            ->with($this->identicalTo($this->requestModel))
            ->will($this->throwException($ex));

        $connector = new Connector($this->apiClientMock, $this->merchantId, $this->sharedSecret, $this->apiUrl);
        $connector->sendRequest($this->requestModel);
    }

    public function testInit()
    {
        $connector = Connector::init($this->merchantId, $this->sharedSecret, $this->apiUrl);

        $this->assertInstanceOf('\Svea\Checkout\Transport\ApiClient', $connector->getApiClient());
        $this->assertEquals($this->merchantId, $connector->getMerchantId());
        $this->assertEquals($this->sharedSecret, $connector->getSharedSecret());
        $this->assertEquals($this->apiUrl, $connector->getBaseApiUrl());
    }

    public function testCreateAuthorizationToken()
    {
        $this->markTestSkipped('Skip Create Authorization Token because timestamp');
        $expectedAuthToken = 'MTIzNDU2OmE0NGI0ZmZkY2U3NGI1M2UzZjk3NzM5YjQwYmJlY2VmMmQxMjlmOTQ5M2FjNGIyZTQ';
        $expectedAuthToken .= '3ODU0ZTVkYjAxYTBlZGU1ZjI5OTc2ZmE2ZjE1NmYwYTM3YmE4ZmVm';
        $expectedAuthToken .= 'MzE2MWI0MzEyNzNiZWI1ZDQ0ODFhNTZmM2I0YTk1OTI0OTI0YjAw';

        $connector = new Connector($this->apiClientMock, $this->merchantId, $this->sharedSecret, $this->apiUrl);
        $this->invokeMethod($connector, 'createAuthorizationToken', array($this->requestModel));

        $this->assertEquals($expectedAuthToken, $this->requestModel->getAuthorizationToken());
    }
}
