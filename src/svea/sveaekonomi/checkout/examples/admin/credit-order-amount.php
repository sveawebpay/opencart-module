<?php

/**
 * By specifying a credited amount larger than the current credited amount.
 * A credit is being made on the specified delivery.
 * The credited amount cannot be lower than the current credited amount or larger than the delivered amount.
 * This method requires “CanCreditAmount” on the delivery
 *
 *
 * Include Library
 *
 * If you use Composer, include the autoload.php file from vendor folder
 * require_once '../../vendor/autoload.php';
 *
 * If you do not use Composer, include the include.php file from root of the project
 * require_once '../../include.php';
 */
require_once '../../include.php';

/**
 * Unique merchant ID
 * Shared Secret string between Svea and merchant
 * Base Url for SVEA Api. Can be TEST_ADMIN_BASE_URL and PROD_ADMIN_BASE_URL
 */
$checkoutMerchantId = 100002;
$checkoutSecret = "3862e010913d7c44f104ddb4b2881f810b50d5385244571c3327802e241140cc692522c04aa21c942793c8a69a8e55ca7b6131d9ac2a2ae2f4f7c52634fe30d2";
$baseUrl = \Svea\Checkout\Transport\Connector::TEST_ADMIN_BASE_URL;

try {
    /**
     * Create Connector object
     *
     * Exception \Svea\Checkout\Exception\SveaConnectorException will be returned if
     * some of fields $merchantId, $sharedSecret and $baseUrl is missing
     *
     *
     * Credit Order Amount
     *
     * Possible Exceptions are:
     * \Svea\Checkout\Exception\SveaInputValidationException
     * \Svea\Checkout\Exception\SveaApiException
     * \Exception - for any other error
     */
    $conn = \Svea\Checkout\Transport\Connector::init($checkoutMerchantId, $checkoutSecret, $baseUrl);
    $checkoutClient = new \Svea\Checkout\CheckoutAdminClient($conn);

    $data = array(
        "orderId" => 2572095,        // required - Long  filed (Specified Checkout order for cancel amount)
        "deliveryId" => 760583,          // required - Int - Id of order delivery
        "creditedAmount" => 2000,       // required - Int Amount to be credit minor currency,
    );
    $response = $checkoutClient->creditOrderAmount($data);
    print_r($response);
} catch (\Svea\Checkout\Exception\SveaApiException $ex) {
    examplePrintError($ex, 'Api errors');
} catch (\Svea\Checkout\Exception\SveaConnectorException $ex) {
    examplePrintError($ex, 'Conn errors');
} catch (\Svea\Checkout\Exception\SveaInputValidationException $ex) {
    examplePrintError($ex, 'Input data errors');
} catch (Exception $ex) {
    examplePrintError($ex, 'General errors');
}

function examplePrintError(Exception $ex, $errorTitle)
{
    print_r('--------- ' . $errorTitle . ' ---------' . PHP_EOL);
    print_r('Error message -> ' . $ex->getMessage() . PHP_EOL);
    print_r('Error code -> ' . $ex->getCode() . PHP_EOL);
}
