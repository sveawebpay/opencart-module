<?php

/**
 * Svea Connection library for Svea Checkout
 *
 *
 * Copyright 2018 Svea Ekonomi AB
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @category  Payment
 * @package   Svea_Checkout
 * @author    Svea Ekonomi AB <support-webpay@sveaekonomi.se>
 * @copyright 2018 Svea Ekonomi AB
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache license v2.0
 */
namespace Svea\Checkout;

use Svea\Checkout\Implementation\ImplementationInterface;
use Svea\Checkout\Transport\Connector;
use Svea\Checkout\Implementation\ImplementationFactory;

/**
 * Class CheckoutClient
 *
 * @package Svea\Checkout
 * @author  Svea
 */
class CheckoutClient
{
    /**
     * Transport connector used to make HTTP request to Svea Checkout API.
     *
     * @var Connector
     */
    private $connector;

    /**
     * CheckoutClient constructor.
     *
     * @param Connector $connector
     */
    public function __construct(Connector $connector)
    {
        $this->connector = $connector;
    }

    /**
     * Create new Svea Checkout order.
     *
     * @param array $data
     * @return mixed
     */
    public function create(array $data)
    {
        return $this->executeAction(ImplementationFactory::returnCreateOrderClass($this->connector), $data);
    }

    /**
     * Update existing Svea Checkout order.
     *
     * @param array $data
     * @return mixed
     */
    public function update(array $data)
    {
        return $this->executeAction(ImplementationFactory::returnUpdateOrderClass($this->connector), $data);
    }

    /**
     * Return Svea Checkout order data.
     *
     * @param array $data
     * @return mixed
     */
    public function get($data)
    {
        return $this->executeAction(ImplementationFactory::returnGetOrderClass($this->connector), $data);
    }

    /**
     * Returns available part payment
     *
     * @param array $data
     * @return mixed
     */
    public function getAvailablePartPaymentCampaigns($data)
    {
        return $this->executeAction(ImplementationFactory::returnGetAvailablePartPaymentCampaignsClass($this->connector), $data);
    }

    /**
     * @param ImplementationInterface $actionObject
     * @param array $inputData
     * @return array
     */
    private function executeAction($actionObject, $inputData)
    {
        $actionObject->execute($inputData);

        $responseHandler = $actionObject->getResponseHandler();

        return $responseHandler->getResponse();
    }
}
