<?php

/**
 * Creates a new order and returns the checkout to the web shop.
 *
 *
 * Include Library
 *
 * If you use Composer, include the autoload.php file from vendor folder
 * require_once '../vendor/autoload.php';
 *
 * If you do not use Composer, include the include.php file from root of the project
 * require_once '../include.php';
 */
require_once '../include.php';

/**
 * Unique merchant ID
 * Shared Secret string between Svea and merchant
 * Base Url for SVEA Api. Can be TEST_BASE_URL and PROD_BASE_URL
 */

$checkoutMerchantId = 100002;
$checkoutSecret = "3862e010913d7c44f104ddb4b2881f810b50d5385244571c3327802e241140cc692522c04aa21c942793c8a69a8e55ca7b6131d9ac2a2ae2f4f7c52634fe30d2";
$baseUrl = \Svea\Checkout\Transport\Connector::TEST_BASE_URL;


/**
 * Initialize creating the order and receive the response data
 * Possible Exceptions are:
 * - \Svea\Checkout\Exception\SveaInputValidationException - if some of fields is missing
 * - \Svea\Checkout\Exception\SveaApiException - is there is some problem with api connection or
 * some error occurred with data validation on API side
 * - \Exception - for any other error
 */
try {
    /**
     * Create Connector object
     *
     * Exception \Svea\Checkout\Exception\SveaConnectorException will be returned if
     * some of fields $merchantId, $sharedSecret and $baseUrl is missing
     *
     *
     * Create Order
     *
     * Possible Exceptions are:
     * \Svea\Checkout\Exception\SveaInputValidationException - if $orderId is missing
     * \Svea\Checkout\Exception\SveaApiException - is there is some problem with api connection or
     *      some error occurred with data validation on API side
     * \Exception - for any other error
     */
    $conn = \Svea\Checkout\Transport\Connector::init($checkoutMerchantId, $checkoutSecret, $baseUrl);
    $checkoutClient = new \Svea\Checkout\CheckoutClient($conn);

    /**
     * Example of creating the order and getting the response data
     */
    $data = array(
        "countryCode" => "SE",
        "currency" => "SEK",
        "locale" => "sv-SE",
        "clientOrderNumber" => rand(10000,30000000),
        "merchantData" => "Test string from merchant",
        "cart" => array(
            "items" => array(
                array(
                    "articleNumber" => "11",
                    "name" => "aa",
                    "quantity" => 200,
                    "unitPrice" => 12300,
                    "vatPercent" => 2500,
                    "unit" => "st",
                    "temporaryReference" => "1",
                    "merchantData" => "Size: S"
                ),
                array(
                    "articleNumber" => "22",
                    "name" => "bb",
                    "quantity" => 300,
                    "unitPrice" => 25000,
                    "vatPercent" => 2500,
                    "unit" => "pcs",
                    "temporaryReference" => "2",
                    "merchantData" => null
                )
            )
        ),
        "presetValues" => array(
            array(
                "typeName" => "emailAddress",
                "value" => "test@yourdomain.se",
                "isReadonly" => false
            ),
            array(
                "typeName" => "postalCode",
                "value" => "99999",
                "isReadonly" => false
            )
        ),
        "merchantSettings" => array(
            "termsUri" => "http://localhost:51898/terms",
            "checkoutUri" => "http://localhost:51925/",
            "confirmationUri" => "http://localhost:51925/checkout/confirm",
            "pushUri" => "https://localhost:51925/push.php?svea_order_id={checkout.order.uri}",
        )
    );

    $response = $checkoutClient->create($data);

    /*
     * Format of returned response array
     *
     * Response:
     *  - MerchantSettings
     *      - TermsUri
     *      - CheckoutUri
     *      - ConfirmationUri
     *      - PushUri
     *  - Cart
     *      - Items [..] / list of items
     *          - ArticleNumber
     *          - Name
     *          - Quantity
     *          - UnitPrice 
     *          - VatPercent
     *          - Unit
     *          - TemporaryReference
     *  - Customer
     *  - ShippingAddress
     *  - BillingAddress
     *  - Gui
     *      - Layout
     *      - Snippet
     *  - Locale
     *  - Currency
     *  - CountryCode
     *  - PresetValues
     *  - OrderId
     *  - Status
     */

    $orderId = $response['OrderId'];
    $guiSnippet = $response['Gui']['Snippet'];
    $orderStatus = $response['Status'];

    echo '<pre>' . print_r($response, true) . '</pre>';

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
