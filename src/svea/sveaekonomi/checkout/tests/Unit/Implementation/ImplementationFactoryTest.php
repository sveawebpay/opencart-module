<?php

namespace Svea\Checkout\Tests\Unit\Implementation;

use Svea\Checkout\Implementation\ImplementationFactory;
use Svea\Checkout\Tests\Unit\TestCase;

class ImplementationFactoryTest extends TestCase
{
    public function testReturnCreateOrderClass()
    {
        $co = ImplementationFactory::returnCreateOrderClass($this->connectorMock);
        $this->assertInstanceOf('\Svea\Checkout\Implementation\CreateOrder', $co);
    }

    public function testReturnGetOrderClass()
    {
        $co = ImplementationFactory::returnGetOrderClass($this->connectorMock);
        $this->assertInstanceOf('\Svea\Checkout\Implementation\GetOrder', $co);
    }

    public function testReturnUpdateOrderClass()
    {
        $co = ImplementationFactory::returnUpdateOrderClass($this->connectorMock);
        $this->assertInstanceOf('\Svea\Checkout\Implementation\UpdateOrder', $co);
    }

    public function testReturnGetAvailablePartPaymentCampaigns()
    {
        $co = ImplementationFactory::returnGetAvailablePartPaymentCampaignsClass($this->connectorMock);
        $this->assertInstanceOf('\Svea\Checkout\Implementation\GetAvailablePartPaymentCampaigns', $co);
    }
}
