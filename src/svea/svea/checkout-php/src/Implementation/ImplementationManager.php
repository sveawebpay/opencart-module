<?php

namespace Svea\Checkout\Implementation;

use Svea\Checkout\Transport\Connector;
use Svea\Checkout\Transport\ResponseHandler;
use Svea\Checkout\Validation\ValidationService;

/**
 * Class ImplementationManager
 * @package Svea\Checkout\Implementation
 */
abstract class ImplementationManager implements ImplementationInterface
{
    /**
     * Transport connector used to make HTTP request to Svea Checkout API.
     *
     * @var Connector $connector
     */
    protected $connector;

    /**
     * API response content - json
     *
     * @var ResponseHandler $responseHandler
     */
    protected $responseHandler;

    /**
     * @var ValidationService
     */
    protected $validator;

    /**
     * @param Connector $connector Used for authentication and connect with Svea Checkout API
     * @param ValidationService $validationService
     */
    public function __construct(Connector $connector, ValidationService $validationService)
    {
        $this->connector = $connector;
        $this->validator = $validationService;
    }

    /**
     * Template pattern for all implementations.
     * These are necessary steps for all implementations.
     *
     * @param array $data Input data to Svea Checkout Library
     */
    public function execute($data)
    {
        $data = FormatInputData::formatArrayKeysToLower($data);
        $this->validateData($data);
        $this->prepareData($data);
        $this->invoke();
    }

    /**
     * Return API response content
     *
     * @return mixed
     */
    public function getResponseHandler()
    {
        return $this->responseHandler;
    }

    /**
     * Input data validation
     * @param array $data Input data to Svea Checkout Library
     */
    abstract public function validateData($data);

    /**
     * Prepare body data for Api call
     * @param array $data
     */
    abstract public function prepareData($data);

    /**
     * Invoke Api call
     */
    abstract public function invoke();
}
