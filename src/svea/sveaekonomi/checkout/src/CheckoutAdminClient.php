<?php

namespace Svea\Checkout;

use Svea\Checkout\Implementation\ImplementationInterface;
use Svea\Checkout\Transport\Connector;
use Svea\Checkout\Implementation\Admin\ImplementationAdminFactory;
use Svea\Checkout\Transport\ResponseHandler;

/**
 * Class CheckoutAdminClient
 *
 * @package Svea\Checkout
 * @author  Svea
 */
class CheckoutAdminClient
{
    /**
     * Transport connector used to make HTTP request to Svea Checkout API.
     *
     * @var Connector
     */
    private $connector;

    /**
     * CheckoutAdminClient constructor.
     *
     * @param Connector $connector
     */
    public function __construct(Connector $connector)
    {
        $this->connector = $connector;
    }

    /**
     * Get Svea Checkout order information.
     *
     * @param int $data
     *
     * @return mixed
     */
    public function getOrder($data)
    {
        return $this->executeAction(ImplementationAdminFactory::returnGetOrderClass($this->connector), $data);
    }

    /**
     * Get Svea Checkout task information.
     *
     * @param int $data
     *
     * @return mixed
     */
    public function getTask($data)
    {
        return $this->executeAction(ImplementationAdminFactory::returnGetTaskClass($this->connector), $data);
    }

    /**
     * Deliver Svea Checkout order.
     *
     * @param mixed $data
     * @return mixed
     */
    public function deliverOrder($data)
    {
        return $this->executeAction(ImplementationAdminFactory::returnDeliverOrderClass($this->connector), $data);
    }

    /**
     * Cancel Svea Checkout order.
     *
     * @param mixed $data
     * @return mixed
     */
    public function cancelOrder($data)
    {
        return $this->executeAction(ImplementationAdminFactory::returnCancelOrderClass($this->connector), $data);
    }

    /**
     * Cancel Svea Checkout order amount.
     *
     * @param mixed $data
     * @return mixed
     */
    public function cancelOrderAmount($data)
    {
        return $this->executeAction(ImplementationAdminFactory::returnCancelOrderClass($this->connector, true), $data);
    }

    /**
     * Cancel Checkout order row.
     *
     * @param int $data
     * @return mixed
     */
    public function cancelOrderRow($data)
    {
        return $this->executeAction(ImplementationAdminFactory::returnCancelOrderRowClass($this->connector), $data);
    }

    /**
     * Credit order rows for Svea Checkout order.
     *
     * @param int $data
     * @return mixed
     */
    public function creditOrderRows($data)
    {
        return $this->executeAction(ImplementationAdminFactory::returnCreditOrderRowsClass($this->connector), $data);
    }

    /**
     * Credit new order row.
     *
     * @param int $data
     * @return mixed
     */
    public function creditNewOrderRow($data)
    {
        return $this->executeAction(
            ImplementationAdminFactory::returnCreditOrderRowsClass($this->connector, true),
            $data
        );
    }

    /**
     * Credit amount for Svea Checkout order.
     *
     * @param int $data
     * @return mixed
     */
    public function creditOrderAmount($data)
    {
        return $this->executeAction(ImplementationAdminFactory::returnCreditOrderAmountClass($this->connector), $data);
    }

    /**
     * Add Order Row.
     *
     * @param int $data
     * @return mixed
     */
    public function addOrderRow($data)
    {
        return $this->executeAction(ImplementationAdminFactory::returnAddOrderRowClass($this->connector), $data);
    }

    /**
     * Update Order Row.
     *
     * @param int $data
     * @return mixed
     */
    public function updateOrderRow($data)
    {
        return $this->executeAction(ImplementationAdminFactory::returnUpdateOrderRowClass($this->connector), $data);
    }

	/**
     * Replace Order Rows.
     *
     * @param int $data
     * @return mixed
     */
    public function replaceOrderRows($data)
    {
        return $this->executeAction(ImplementationAdminFactory::returnReplaceOrderRowsClass($this->connector), $data);
    }

	/**
     * Credit Order Rows with fee.
     *
     * @param int $data
     * @return mixed
     */
    public function creditOrderRowsWithFee($data)
    {
        return $this->executeAction(ImplementationAdminFactory::returnCreditOrderRowsWithFeeClass($this->connector), $data);
    }

    /**
     * @param ImplementationInterface $actionObject
     * @param mixed $inputData
     * @return array
     */
    private function executeAction($actionObject, $inputData)
    {
        $actionObject->execute($inputData);

        /**
         * @var ResponseHandler $responseHandler
         */
        $responseHandler = $actionObject->getResponseHandler();

        return $responseHandler->getResponse();
    }
}
