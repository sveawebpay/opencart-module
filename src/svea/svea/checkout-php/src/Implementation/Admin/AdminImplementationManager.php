<?php

namespace Svea\Checkout\Implementation\Admin;

use Exception;
use Svea\Checkout\Transport\Connector;
use Svea\Checkout\Validation\ValidationService;
use Svea\Checkout\Implementation\ImplementationManager;

abstract class AdminImplementationManager extends ImplementationManager
{
    public function __construct(Connector $connector, ValidationService $validationService)
    {
        parent::__construct($connector, $validationService);
    }

    /**
     * Return Api url string
     *
     * @param $data mixed
     * @return string
     * @throws Exception
     */
    protected function prepareUrl($data)
    {
        if (!is_array($data)) {
            $data = array($data);
        }

        if (!isset($this->apiUrl)) {
            throw new Exception("Api Url must be set.");
        }

        $url = vsprintf($this->apiUrl, $data);

        return $this->connector->getBaseApiUrl() . $url;
    }
}
