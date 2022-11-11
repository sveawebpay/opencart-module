<?php

namespace Svea\Checkout\Tests\Unit\Transport;

use Svea\Checkout\Tests\Unit\TestCase;
use Svea\Checkout\Transport\ResponseHandler;

class ResponseHandlerTest extends TestCase
{
    /**
     * @expectedException \Svea\Checkout\Exception\SveaApiException
     * @expectedExceptionCode 401
     */
    public function testHandleClientResponseExceptionThrown()
    {
        $body = '';
        $content = 'HTTP/1.1 401 Unauthorized' . "\r\n\r\n" . $body;
        $httpCode = 401;

        $responseHandler = new ResponseHandler($content, $httpCode);
        $responseHandler->handleClientResponse();
    }

    public function testHandleClientResponseWithSuccessfulResponse()
    {
        $body = '{"test":"Order create successful"}';
        $content = 'HTTP/1.1 201 Created' . "\r\n\r\n" . $body;
        $httpCode = 201;

        $responseHandler = new ResponseHandler($content, $httpCode);
        $responseHandler->handleClientResponse();

        $this->assertEquals(json_decode($body, true), $responseHandler->getContent());
    }

    /**
     * @expectedException \Svea\Checkout\Exception\SveaApiException
     * @expectedExceptionMessage Bad request message
     */
    public function testHandleClientResponseWithErrorMessageFromApiResponse()
    {
        $body = '';
        $content = 'HTTP/1.1 400 Bad request' . "\r\n";
        $content .= 'ErrorMessage: Bad request message';
        $content .= "\r\n\r\n" . $body;
        $httpCode = 400;

        $responseHandler = new ResponseHandler($content, $httpCode);
        $responseHandler->handleClientResponse();
    }

    /**
     * @expectedException \Svea\Checkout\Exception\SveaApiException
     * @expectedExceptionMessage HTTP/1.1 401 Unauthorized
     */
    public function testHandleClientResponseWithGeneralResponseMessageFromApiResponse()
    {
        $body = '';
        $content = 'HTTP/1.1 401 Unauthorized' . "\r\n\r\n" . $body;
        $httpCode = 401;

        $responseHandler = new ResponseHandler($content, $httpCode);
        $responseHandler->handleClientResponse();
    }

    public function testGetContent()
    {
        $body = '{"test":"Order create successful"}';
        $content = 'HTTP/1.1 201 Created' . "\r\n\r\n" . $body;
        $httpCode = 201;

        $responseHandler = new ResponseHandler($content, $httpCode);

        $this->assertEquals(json_decode($body, true), $responseHandler->getContent());
    }

    public function testSetHeader()
    {
        $body = '';
        $header = 'HTTP/1.1 201 Created' . "\r\n";
        $header .= 'Cache-Control: no-cache' . "\r\n";
        $header .= 'Pragma: no-cache' . "\r\n";
        $header .= 'Content-Length: 3469' . "\r\n";
        $header .= 'Content-Type: application/json; charset=utf-8' . "\r\n";
        $header .= 'Expires: -1' . "\r\n";
        $header .= 'Server: Microsoft-IIS/8.5' . "\r\n";
        $header .= 'X-AspNet-Version: 4.0.30319' . "\r\n";
        $header .= 'X-Powered-By: ASP.NET' . "\r\n";
        $header .= 'Date: Wed, 27 Apr 2016 09:42:19 GMT';

        $content = $header . "\r\n\r\n" . $body;
        $httpCode = 201;

        $responseHandler = new ResponseHandler($content, $httpCode);
        $responseHandler->setHeader();

        $res = array
        (
            'http_code' => 'HTTP/1.1 201 Created',
            'Cache-Control' => 'no-cache',
            'Pragma' => 'no-cache',
            'Content-Length' => '3469',
            'Content-Type' => 'application/json; charset=utf-8',
            'Expires' => '-1',
            'Server' => 'Microsoft-IIS/8.5',
            'X-AspNet-Version' => '4.0.30319',
            'X-Powered-By' => 'ASP.NET',
            'Date' => 'Wed, 27 Apr 2016 09:42:19 GMT'
        );


        $this->assertEquals($res, $responseHandler->getHeader());
    }

    public function testSetBody()
    {
        $body = '{"test":"Order create successful"}';
        $content = 'HTTP/1.1 201 Created' . "\r\n\r\n" . $body;
        $httpCode = 200;

        $responseHandler = new ResponseHandler($content, $httpCode);
        $responseHandler->setBody();

        $this->assertEquals($body, $responseHandler->getBody());
    }

    public function testGetResponseWith204NoContentStatusResponse()
    {
        $body = '{"test":"Order credited successful"}';
        $content = 'HTTP/1.1 204 Created' . "\r\n\r\n" . $body;
        $httpCode = 204;

        $responseHandler = new ResponseHandler($content, $httpCode);

        $this->assertEquals('', $responseHandler->getResponse());
    }

    public function testGetResponseWith201CreatedStatusResponse()
    {
        $body = '{"test":"Order credited successful"}';
        $content = 'HTTP/1.1 201 Created' . "\r\n\r\n" . $body;
        $httpCode = 201;

        $responseHandler = new ResponseHandler($content, $httpCode);

        $this->assertEquals(json_decode($body, true), $responseHandler->getResponse());
    }

    public function testGetResponseWith202AcceptedStatusResponseWithBodyContent()
    {
        $body = '{"test":"Order credited successful"}';
        $locationUrl = 'http://svea.com';
        $content = 'HTTP/1.1 202 Accepted' . "\r\nLocation: ". $locationUrl . "\r\n\r\n" . $body;
        $httpCode = 202;

        $responseHandler = new ResponseHandler($content, $httpCode);

        $expectedValue = array(
            'HeaderLocation' => $locationUrl
        );
        $expectedValue = array_merge($expectedValue, json_decode($body, true));

        $this->assertEquals($expectedValue, $responseHandler->getResponse());
    }

    public function testGetResponseWith202AcceptedStatusResponseWithoutBodyContent()
    {
        $body = '';
        $locationUrl = 'http://svea.com';
        $content = 'HTTP/1.1 202 Accepted' . "\r\nLocation: ". $locationUrl . "\r\n\r\n" . $body;
        $httpCode = 202;

        $responseHandler = new ResponseHandler($content, $httpCode);

        $expectedValue = array(
            'HeaderLocation' => $locationUrl
        );

        $this->assertEquals($expectedValue, $responseHandler->getResponse());
    }
}
