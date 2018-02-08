<?php

namespace Svea\Checkout\Implementation;

use Svea\Checkout\Transport\ResponseHandler;

/**
 * Interface ImplementationInterface
 * @package Svea\Checkout\Implementation
 */
interface ImplementationInterface
{
    /**
     * Template pattern for all implementations
     * @param array $data
     */
    public function execute($data);

    /**
     * @return ResponseHandler
     */
    public function getResponseHandler();
}
