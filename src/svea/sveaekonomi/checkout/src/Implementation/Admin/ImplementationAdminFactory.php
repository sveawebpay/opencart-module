<?php

namespace Svea\Checkout\Implementation\Admin;

use Svea\Checkout\Transport\Connector;
use Svea\Checkout\Validation\Admin\ValidateGetOrderData;
use Svea\Checkout\Validation\Admin\ValidateAddOrderRowData;
use Svea\Checkout\Validation\Admin\ValidateCancelOrderData;
use Svea\Checkout\Validation\Admin\ValidateGetDataFromLink;
use Svea\Checkout\Validation\Admin\ValidateDeliverOrderData;
use Svea\Checkout\Validation\Admin\ValidateGetTaskData;
use Svea\Checkout\Validation\Admin\ValidateUpdateOrderRowData;
use Svea\Checkout\Validation\Admin\ValidateCancelOrderRowData;
use Svea\Checkout\Validation\Admin\ValidateCreditOrderRowsData;
use Svea\Checkout\Validation\Admin\ValidateGetOrderCreditsData;
use Svea\Checkout\Validation\Admin\ValidateGetOrderDeliveryData;
use Svea\Checkout\Validation\Admin\ValidateCreditOrderAmountData;
use Svea\Checkout\Validation\Admin\ValidateGetOrderAddressesData;

class ImplementationAdminFactory
{
    /**
     * @param Connector $connector
     * @return DeliverOrder
     */
    public static function returnDeliverOrderClass(Connector $connector)
    {
        return new DeliverOrder($connector, new ValidateDeliverOrderData());
    }

    /**
     * @param Connector $connector
     * @return GetOrder
     */
    public static function returnGetOrderClass(Connector $connector)
    {
        return new GetOrder($connector, new ValidateGetOrderData());
    }

    /**
     * @param Connector $connector
     * @return GetTask
     */
    public static function returnGetTaskClass(Connector $connector)
    {
        return new GetTask($connector, new ValidateGetTaskData());
    }

    /**
     * @param Connector $connector
     * @param bool $isCancelAmount
     * @return CancelOrder
     */
    public static function returnCancelOrderClass(Connector $connector, $isCancelAmount = false)
    {
        return new CancelOrder($connector, new ValidateCancelOrderData($isCancelAmount), $isCancelAmount);
    }

    /**
     * @param Connector $connector
     * @return CancelOrderRow
     */
    public static function returnCancelOrderRowClass(Connector $connector)
    {
        return new CancelOrderRow($connector, new ValidateCancelOrderRowData());
    }

    /**
     * @param Connector $connector
     * @return CreditOrderAmount
     */
    public static function returnCreditOrderAmountClass(Connector $connector)
    {
        return new CreditOrderAmount($connector, new ValidateCreditOrderAmountData());
    }

    /**
     * @param Connector $connector
     * @param bool $isNewCreditRow
     * @return CreditOrderRows
     */
    public static function returnCreditOrderRowsClass(Connector $connector, $isNewCreditRow = false)
    {
        return new CreditOrderRows($connector, new ValidateCreditOrderRowsData($isNewCreditRow), $isNewCreditRow);
    }

    /**
     * @param Connector $connector
     * @return AddOrderRow
     */
    public static function returnAddOrderRowClass(Connector $connector)
    {
        return new AddOrderRow($connector, new ValidateAddOrderRowData());
    }

    /**
     * @param Connector $connector
     * @return UpdateOrderRow
     */
    public static function returnUpdateOrderRowClass(Connector $connector)
    {
        return new UpdateOrderRow($connector, new ValidateUpdateOrderRowData());
    }
}
