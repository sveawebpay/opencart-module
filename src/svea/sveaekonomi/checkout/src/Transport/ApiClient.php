<?php

namespace Svea\Checkout\Transport;

use \Exception;
use Svea\Checkout\Exception\ExceptionCodeList;
use Svea\Checkout\Model\Request;
use Svea\Checkout\Transport\Http\HttpRequestInterface;

/**
 * Class ApiClient - Used to make a request to Svea Checkout API
 *
 * @package Svea\Checkout\Transport
 */
class ApiClient
{
    /**
     * Implementation of Http client interface.
     *
     * @var HttpRequestInterface $httpClient
     */
    private $httpClient;

    /**
     * Client constructor.
     *
     * @param HttpRequestInterface $httpClient PHP HTTP client that makes it easy to send HTTP requests
     */
    public function __construct(HttpRequestInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    /**
     * Send request to Svea Checkout API.
     *
     * @param Request $request Request model
     * @throws Exception        When an error is encountered
     * @return ResponseHandler
     */
    public function sendRequest(Request $request)
    {
        $header = array();
        $header[] = 'Content-type: application/json';
        $header[] = 'Authorization: Svea ' . $request->getAuthorizationToken();
        $header[] = 'Timestamp: ' . $request->getTimestamp();
        $header[] = 'Expect:';

        $this->httpClient->init();
        $this->httpClient->setOption(CURLOPT_URL, $request->getApiUrl());
        $this->httpClient->setOption(CURLOPT_HTTPHEADER, $header);
        $this->httpClient->setOption(CURLOPT_RETURNTRANSFER, 1);
        $this->httpClient->setOption(CURLOPT_HEADER, 1);
        $this->httpClient->setOption(CURLOPT_SSL_VERIFYPEER, false);

        if ($request->getMethod() === 'POST') {
            $this->httpClient->setOption(CURLOPT_POST, 1);
            $this->httpClient->setOption(CURLOPT_POSTFIELDS, $request->getBody());
        }

        if ($request->getMethod() === 'PUT') {
            $this->httpClient->setOption(CURLOPT_CUSTOMREQUEST, "PUT");
            $this->httpClient->setOption(CURLOPT_POSTFIELDS, $request->getBody());
        }

        if ($request->getMethod() === 'PATCH') {
            $this->httpClient->setOption(CURLOPT_CUSTOMREQUEST, "PATCH");
            $this->httpClient->setOption(CURLOPT_POSTFIELDS, $request->getBody());
        }

        $httpResponse = $this->httpClient->execute();
        $httpCode = $this->httpClient->getInfo(CURLINFO_HTTP_CODE);

        $httpError = $this->httpClient->getError();
        $errorNumber = $this->httpClient->getErrorNumber();

        $this->httpClient->close();

        if ($errorNumber > 0) {
            throw new Exception(" Curl error " . $errorNumber . " " . $httpError, ExceptionCodeList::COMMUNICATION_ERROR);
        }

        $responseHandler = new ResponseHandler($httpResponse, $httpCode);
        $responseHandler->handleClientResponse();

        return $responseHandler;
    }
}
