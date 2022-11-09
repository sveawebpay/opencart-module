<?php

/**
 * Returns a list of available B2C/B2B part payment campaigns
 * which can be used to display example prices on product pages
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
 * Create connector for given
 * Merchant Id - unique merchant ID
 * Shared Secret - Shared Secret string between Svea and merchant
 * Base Url for SVEA Api. Can be STAGE_BASE_URL and PROD_BASE_URL
 */
$checkoutMerchantId = 100002;
$checkoutSecret = "3862e010913d7c44f104ddb4b2881f810b50d5385244571c3327802e241140cc692522c04aa21c942793c8a69a8e55ca7b6131d9ac2a2ae2f4f7c52634fe30d2";
$baseUrl = \Svea\Checkout\Transport\Connector::TEST_BASE_URL;

try {
    /**
     * Create Connector object
     *
     * Exception \Svea\Checkout\Exception\SveaConnectorException will be returned if
     * some of fields $merchantId, $sharedSecret and $baseUrl is missing
     *
     *
     * Get available part payment campaigns
     *
     * Initialize the request
     *  Possible Exceptions are:
     *  \Svea\Checkout\Exception\SveaInputValidationException - if "IsCompany" is missing or not boolean
     *  \Svea\Checkout\Exception\SveaApiException - is there is some problem with api connection or
     *      some error occurred with data validation on API side
     *  \Exception - for any other error
     */
    $conn = \Svea\Checkout\Transport\Connector::init($checkoutMerchantId, $checkoutSecret, $baseUrl);
    $checkoutClient = new \Svea\Checkout\CheckoutClient($conn);

    $data = array(
        'IsCompany' => false
    );
    $response = $checkoutClient->getAvailablePartPaymentCampaigns($data);
    echo "<pre>" . print_r($response, true) . "</pre>";

    /*
     * Format of returned response array
     *
     * Response:
     *  Array of Campaigns - [..]
     *      Array of CampaignCodeInfo - [..]
     *          - CampaignCode
     *          - ContractLengthInMonths
     *          - Description
     *          - FromAmount
     *          - InitialFee
     *          - InterestRatePercent
     *          - MonthlyAnnuityFactor
     *          - NotificationFee
     *          - NumberOfInterestFreeMonths
     *          - NumberOfPaymentFreeMonths
     *          - PaymentPlanType
     *          - ToAmount
     * */

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
