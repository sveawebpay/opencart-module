# PHP Checkout library for Svea Checkout

## Index
* [1. Setup](#1-setup)
* [2. Create a Connector](#2-create-a-connector)
* [3. Create order](#3-create-order)
* [4. Get order](#4-get-order)
* [5. Update order](#5-update-order)
* [6. Response](#6-response)
* [7. Additional requests](#7-additional-requests)
* [8. Data structures](#8-data-structures)
* [9. HttpStatusCodes](#9-httpstatuscodes)
* [10. Administrate orders](#10-administrate-orders)

## Introduction
The checkout offers a complete solution with a variety of payment methods. The payment methods that are currently available in the checkout is invoice, payment plan, account credit, card payments and payment by bank.


The checkout supports both B2C and B2B payments, fast customer identification and caches customers behaviour. 
For administration of orders, you can either implement it in your own project, or use our new admin interface.

The library provides entry points to integrate the checkout into your platform and to administrate checkout orders.

### 1. Setup

#### 1.1 Install with [**Composer**](https://getcomposer.org/)

In command line
```bash

    Composer require sveaekonomi/checkout

```

or add this part to your composer.json

```json
    {
        "require": {
            "sveaekonomi/checkout": "dev-master"
        }
    }
```
and run command ` composer update ` in the console

#### 1.2 Install without composer
You can also download and unzip the project and upload it to your server.

### 2. Create a Connector
You use a connector object as parameter when creating a CheckoutClient request.
Parameters for creating Connector are: checkoutMerchantId, checkoutSecret and base Api url.

```php
// include the library
include 'vendor/autoload.php';

// without composer
require_once 'include.php';

$checkoutMerchantId = '100001';
$checkoutSecret = 'checkoutSecret';
//set endpoint url. Eg. test or prod
$baseUrl = \Svea\Checkout\Transport\Connector::TEST_BASE_URL;

$connector = \Svea\Checkout\Transport\Connector::init($checkoutMerchantId, $checkoutSecret, $baseUrl);
```

### 3. Create Order
Create a new order with the given merchant and cart, where the cart contains the order rows.
Returns the order information and the Gui needed to display the iframe Svea checkout.

[See full Create order example](https://github.com/sveawebpay/php-checkout/blob/master/examples/create-order.php)

#### 3.1 Order data

Sample order data
```php
// Example of data for creating order
$data = array(
    "CountryCode" => "SE",
    "Currency" => "SEK",
    "Locale" => "sv-SE",
    "Cart" => array(
        "items" => array(
            array(
                "ArticleNumber" => "123456789",
                "Name" => "Car",
                "Quantity" => 200,
                "UnitPrice" => 12300,
                "DiscountPercent" => 1000,
                "VatPercent" => 2500,
                'TemporaryReference' => '230'
            ),
            array(
                "ArticleNumber" => "987654321",
                "Name" => "Fork",
                "Quantity" => 300,
                "UnitPrice" => 15800,
                "DiscountPercent" => 2000,
                "VatPercent" => 2500,
                'TemporaryReference' => '231'
            ),
            array(
                "Type" => "shipping_fee",
                "ArticleNumber" => "SHIPPING",
                "Name" => "Shipping fee",
                "Quantity" => 100,
                "UnitPrice" => 4900,
                "VatPercent" => 2500
            )
        )
    ),
    "MerchantSettings" => array(
        "TermsUri" => "http://localhost:51898/terms",
        "CheckoutUri" => "http://localhost:51925/",
        "ConfirmationUri" => "http://localhost:51925/checkout/confirm",
        "pushUri" => "https://localhost:51925/push.php?svea_order_id={checkout.order.uri}"
    )
);
```

#### 3.2 Create the Order
Create a CheckoutClient object with the [*Connector*](#2-create-a-connector) as parameter.
The checkoutClient object is an entry point to use library.

```php
// include the library
include 'vendor/autoload.php'

// without composer
require_once 'include.php';

...

$checkoutClient = new \Svea\Checkout\CheckoutClient($connector);

$response = $checkoutClient->create($data);
```

### 4. Get Order
Get an existing order. Returns the order information and the Gui needed to display the iframe for Svea checkout.

[See full Get order example](https://github.com/sveawebpay/php-checkout/blob/master/examples/get-order.php)

| Parameters IN                | Required  | Type      | Description  |
|------------------------------|-----------|-----------|--------------|
| Id                           |	*      | Long      | Checkoutorderid of the specified order. |

| Parameters OUT               | Type      | Description  |
|------------------------------|-----------|--------------|
| Data                         | Data      | An object containing all information needed to return a checkout to the merchant, see (#6-response) |

#### 4.1 Get the Order
Create a CheckoutClient object with the [*Connector*](#2-create-a-connector) as parameter.
The checkoutClient object is an entry point to use library.

```php
// include the library
include 'vendor/autoload.php'

// without composer
require_once 'include.php';

...

$data = array(
        'orderId' => 51721
    );

$checkoutClient = new \Svea\Checkout\CheckoutClient($connector);

$response = $checkoutClient->get($data);
```

### 5. Update Order
Update an existing order. Returns the order information and the updated Gui needed to display the iframe for Svea checkout.

Updating an order is only possible while the CheckoutOrderStatus is "Created", see [*CheckoutOrderStatus*](#78-checkoutorderstatus).

[See full Update order example](https://github.com/sveawebpay/php-checkout/blob/master/examples/update-order.php)

| Parameters IN  as URI-parameters: | Required   | Type      | Description  |
|-----------------------------------|------------|-----------|--------------|
| Id                                |	*        | Long      | Checkoutorderid of the specified order. |

| Parameters IN as Content:     | Required   | Type      | Description  |
|-------------------------------|------------|-----------|--------------|
| Cart                          |	         | Cart      | A cart-object containing the [*OrderRows*](#73-orderrow) |

Sample order data
```php
// Example of data for creating order
$data = array(
    "Id" => 9,
    "Cart" => array(
        "Items" => array(
            array(
                "ArticleNumber" => "123456789",
                "Name" => "Dator",
                "Quantity" => 200,
                "UnitPrice" => 12300,
                "DiscountPercent" => 1000,
                "VatPercent" => 2500.
                "TemporaryReference" => "230"
            ),
            array(
                "Type" => "shipping_fee",
                "ArticleNumber" => "SHIPPING",
                "Name" => "Shipping Fee Updated",
                "Quantity" => 100,
                "UnitPrice" => 4900,
                "VatPercent" => 2500,
                "TemporaryReference" => "231"
            )
        )
    )
);
```

#### 5.1 Update the Order
Create a CheckoutClient object with the [*Connector*](#2-create-a-connector) as parameter.
The checkoutClient object is an entry point to use library.

```php
// include the library
include 'vendor/autoload.php'

// without composer
require_once 'include.php';

...

$checkoutClient = new \Svea\Checkout\CheckoutClient($connector);

$response = $checkoutClient->update($data);
```

### 6. Response
The create method will return an array with the response data. The response contains information about the Cart,
merchantSettings, Customer and the Gui for the checkout.

| Parameters OUT                | Type                 | Description |
|-------------------------------|----------------------|-------------|
| MerchantSettings              | MerchantSettings     | Specific merchant URIs, see [*Merchant settings*](#71-merchantsettings) |
| Cart                          | Cart                 | A cart-object containing the [*OrderRows*](#73-orderrow) |
| Gui                           | Gui                  | See [*Gui*](#75-gui) |
| Customer                      | Customer             | Identified [*Customer*](#76-customer) of the order. |
| ShippingAddress               | Address              | Shipping [*Address*](#77-address) of identified customer. |
| BillingAddress                | Address              | Billing [*Address*](#77-address) of identified customer. |
| Locale                        | String               | The current locale of the checkout, i.e. sv-SE etc. |
| Currency                      | String               | The current currency as defined by ISO 4217, i.e. SEK, NOK etc. |
| CountryCode                   | String               | Defined by two-letter ISO 3166-1 alpha-2, i.e. SE, DE, FI etc.  |
| ClientOrderNumber             | String               | A string with maximum of 32 characters that identifies the order in the merchant’s systems |
| PresetValues                  | List of PresetValues | [*Preset values*](#74-presetvalue) chosen by the merchant to be prefilled and eventually locked for changing by the customer. |
| OrderId                       | Long                 | CheckoutOrderId of the order. |
| Status                        | The current state of the order, see [*CheckoutOrderStatus*](#78-checkoutorderstatus) below. |
| EmailAddress                  | String               | The customer’s email address |
| PhoneNumber                   | String               | The customer’s phone number |
| PaymentType                   | String               | The final payment method for the order. Will only have a value when the order is locked, otherwise null. See [*PaymentType*](#710-paymenttype)|


Sample response
```
Array
(
    [MerchantSettings] => Array
        (
            [TermsUri] => http://localhost:51898/terms
            [CheckoutUri] => http://localhost:51925/
            [ConfirmationUri] => http://localhost:51925/checkout/confirm
            [PushUri] => https://localhost:51925/push.php?svea_order={checkout.order.uri}
        )

    [Cart] => Array
        (
            [Items] => Array
                (
                    [0] => Array
                        (
                            [ArticleNumber] => 123456789
                            [Name] => Dator
                            [Quantity] => 200
                            [UnitPrice] => 12300
                            [DiscountPercent] => 1000
                            [VatPercent] => 2500
                            [Unit] =>
                            [TemporaryReference] => "230"
                        )
                    [1] => Array
                        (
                            [ArticleNumber] => SHIPPING
                            [Name] => Shipping Fee Updated
                            [Quantity] => 100
                            [UnitPrice] => 4900
                            [DiscountPercent] => 0
                            [VatPercent] => 2500
                            [Unit] =>
                            [TemporaryReference] => "231"
                        )
                )
         )
    [Customer] =>
    [ShippingAddress] =>
    [BillingAddress] =>
    [Gui] => Array
        (
            [Layout] => desktop
            [Snippet] =>
        )
    [Locale] => sv-SE
    [Currency] =>
    [CountryCode] =>
    [PresetValues] =>
    [ClientOrderNumber] => '78691'
    [OrderId] => 9
    [EmailAddress] => 'integration@svea.com'
    [PhoneNumber] => '1234567'
    [Status] => Created
)
```

The checkout Gui contains the Snippet and the Layout. The Snippet contains the Html and JavaScript that you implement on your
page where you want to display the iframe for Svea checkout. The Layout is a String defining the orientation of the customers screen.

```php
echo $response['Gui']['Snippet']
```
### 7. Additional requests

#### 7.1 GetAvailablePartPaymentCampaigns

[See example](https://github.com/sveawebpay/php-checkout/blob/master/examples/get-available-part-payment-campaigns.php)

GetAvailablePartPaymentCampaigns can be used to fetch the details of all the campaigns that are available on the merchant

The information can be used to for example display information about how much it will cost to pay for a certain product or products on the actual product page.

Example Request:
```php

$checkoutMerchantId = 100002;
$checkoutSecret = "3862e010913d7c44f104ddb4b2881f810b50d5385244571c3327802e241140cc692522c04aa21c942793c8a69a8e55ca7b6131d9ac2a2ae2f4f7c52634fe30d2";
$baseUrl = \Svea\Checkout\Transport\Connector::TEST_BASE_URL;

$conn = \Svea\Checkout\Transport\Connector::init($checkoutMerchantId, $checkoutSecret, $baseUrl);
$checkoutClient = new \Svea\Checkout\CheckoutClient($conn);

$data = array(
    'IsCompany' => false
);
$response = $checkoutClient->getAvailablePartPaymentCampaigns($data);
echo "<pre>" . print_r($response, true) . "</pre>";
```

Running the code above will return an array with [8.11 CampaignCodeInfo](#811-campaigncodeinfo)

Example response when decoded:
```php
Array
(
    [0] => Array
        (
            [CampaignCode] => 213060
            [ContractLengthInMonths] => 3
            [Description] => Köp nu betala om 3 månader (räntefritt)
            [FromAmount] => 1000
            [InitialFee] => 100
            [InterestRatePercent] => 0
            [MonthlyAnnuityFactor] => 1
            [NotificationFee] => 29
            [NumberOfInterestFreeMonths] => 3
            [NumberOfPaymentFreeMonths] => 3
            [PaymentPlanType] => 2
            [ToAmount] => 50000
        )

    [1] => Array
        (
                    [CampaignCode] => 410012
                    [ContractLengthInMonths] => 12
                    [Description] => Dela upp betalningen på 12 månader
                    [FromAmount] => 100
                    [InitialFee] => 0
                    [InterestRatePercent] => 19.9
                    [MonthlyAnnuityFactor] => 0.092586652785396
                    [NotificationFee] => 29
                    [NumberOfInterestFreeMonths] => 0
                    [NumberOfPaymentFreeMonths] => 0
                    [PaymentPlanType] => 0
                    [ToAmount] => 30000
        )
)
```

The information should be stored in a database for fast access.

##### Calculation formulas

Calculating price per month:
```php
ProductPrice * MonthlyAnnuityFactor + NotificationFee
```

Using the second campaign with a product price of 150kr in the example above will result in:
150 * 0.092586652785396 + 29 = 42.8879979kr round upwards to closest whole number -> 43kr



Calculating total amount to pay:
```php
InitialFee + (ProductPrice * MonthlyAnnuityFactor + NotificationFee) * ContractLengthInMonths
```

Using the second campaign with a product price of 150kr in the example above will result in:
0 + (150 * 0.092586652785396 + 29 ) * 12 = 514.655975 round upwards to closest whole numer -> 515kr

!!! NOTE !!!
If you are a finnish merchant you have to display ALL the values described [here](https://www.kkv.fi/sv/beslut-och-publikationer/publikationer/konsumentrombudsmannens-riktlinjer/enligt-substans/tillhandahallande-av-konsumentkrediter/#luottolinjausSVE5.1)

### 8. Data structures

The latest data structures are always available at [Checkout API](https://www.svea.com/globalassets/sweden/foretag/betallosningar/e-handel/moduler-integration/merchant-checkout-api.pdf) and [Payment Admin API](https://www.svea.com/globalassets/sweden/foretag/betallosningar/e-handel/moduler-integration/paymentadminexternalapi_.pdf) meanwhile the data below can be outdated.

#### 8.1 MerchantSettings

| Parameters IN                | Required  | Type      | Description  | Limits  |
|------------------------------|-----------|-----------|--------------|---------|
| TermsUri                     |	*      | string    | URI to a page with webshop specific terms. | 1-500 characters, must be a valid Url |
| CheckoutUri                  |	*      | string    | URI to the page in the webshop that loads the Checkout.  | 1-500 characters, must be a valid Url |
| ConfirmationUri              |	*      | string    | URI to the page in the webshop displaying specific information to a customer after the order has been confirmed. | 1-500 characters, must be a valid Url |
| PushUri                      |	*      | string    | URl to a location that is expecting callbacks from the Checkout when an order is confirmed. Uri must have the {checkout.order.uri} placeholder.  | 1-500 characters, must be a valid Url |
| CheckoutValidationCallBackUri|           | string    | An optional URl to a location that is expecting callbacks from the Checkout to validate order’s stock status, and also the possibility to update checkout with an updated ClientOrderNumber. Uri may have a {checkout.order.uri} placeholder which will be replaced with the checkoutorderid. Please refer below CheckoutValidationCallbackResponse to see the expected response. | 1-500 characters, must be a valid Url |
| ActivePartPaymentCampaigns   |           | List<integer> | List of valid CampaignIDs. If used then list of available part payment campaign options will be filtered through the chosen list. | Must be a list of valid CampaignIDs |
| PromotedPartPaymentCampaign  |           | integer   | Valid CampaignID. If used then the chosen campaign will be shown as the first payment method in all payment method lists. | Must be valid CampaignID |

#### 8.2 Items

| Parameters IN                | Required  | Type                                 | Description         |
|------------------------------|-----------|--------------------------------------|---------------------|
| Items                        |	*      | List of [*OrderRows*](#83-orderrow)  | See structure below |

#### 8.3 OrderRow

| Parameters IN                | Required   | Type      | Description  | Limits  |
|------------------------------|------------|-----------|--------------|---------|
| ArticleNumber                |	        | String    | Articlenumber as a string, can contain letters and numbers. | Maximum 1000 characters |
| Name                         |	*       | String    | Article name | 1-40 characters |
| Quantity                     |	*       | Integer       | Set as basis point (1/100) e.g  2 = 200      | 1-9 digits. Minor currency |
| UnitPrice                    |	*       | Integer       | Set as basis point (1/100) e.g. 25.00 = 2500 | 1-13 digits, can be negative. Minor currency |
| DiscountPercent              |	        | Integer       | The discountpercent of the product. | 0-100 |
| VatPercent                   |	*       | Integer       | The VAT percentage of the current product. | Valid vat percentage for that country. Minor currency.  |
| Unit                         |            | String        | The unit type, e.g., “st”, “pc”, “kg” etc. | 0-4 characters|
| TemporaryReference           |            | String        | Can be used when creating or updating an order. The returned rows will have their corresponding temporaryreference as they were given in the indata. It will not be stored and will not be returned in GetOrder.  | |

#### 8.4 PresetValue

| Parameters IN             | Required  | Type          | Description  |
|---------------------------|-----------|---------------|--------------|
| TypeName                  |	*       | String        | Name of the field you want to set (see list below).  |
| Value                     |	*       | String        | See limits below. |
| IsReadOnly                |	*       | Boolean       | Should the preset value be locked for editing, set readonly to true. Usable if you only let your registered users use the checkout. |

**List of presetvalue typenames**

| Parameter                 | Type          | Description  | Limits       |
|---------------------------|---------------|--------------|--------------|
| NationalId                | String        |              | Company specific validation |
| EmailAddress              | String        |              | Max 50 characters, will be validated as an email address |
| PhoneNumber               | String        |              | 1-18 digits, can include “+”, “-“s and space |
| PostalCode                | String        |              | Company specific validation |
| IsCompany                 | Boolean       | Required if nationalid is set | |

#### 8.5 Gui

| Parameters OUT               | Required   | Type      | Description  |
|------------------------------|------------|-----------|--------------|
| Layout                       |	*       | String    | Defines the orientation of the device, either “desktop” or “portrait”.  |
| Snippet                      |	*       | String    | HTML-snippet including javascript to populate the iFrame. |

#### 8.6 Customer

| Parameters OUT               | Required   | Type      | Description  |
|------------------------------|------------|-----------|--------------|
| NationalId                   |	*       | String    | Personal- or organizationnumber. |
| IsCompany                    |	*       | Boolean   | True if nationalId is organisationnumber, false if nationalid is personalnumber.   |
| IsMale                       |	        | Boolean  | Indicating if the customer is male or not. |
| DateOfBirth                  |	        | Nullable datetime | Required only for DE and NL or if NationalId is not set for any reason. |

#### 8.7 Address

| Parameters OUT               | Type      | Description  |
|------------------------------|-----------|--------------|
| FullName                     | String    | Company: name of the company. Individual: first name(s), middle name(s) and last name(s). |
| FirstName                    | String    | First name(s).  |
| LastName                     | String    | Last name(s).   |
| StreetAddress                | String    | Street address.  |
| CoAddress                    | String    | Co address.  |
| PostalCode                   | String    | Postal code.  |
| City                         | String    | City.  |
| CountryCode                  | String    | Defined by two-letter ISO 3166-1 alpha-2, i.e. SE, DE, FI etc.|

#### 8.8 CheckoutOrderStatus

The order can only be considered “ready to send to customer” when the CheckoutOrderStatus is Final. No other status can guarantee payment.

| Parameters OUT               | Description  |
|------------------------------|--------------|
| Cancelled                    | The order has been cancelled due to inactivity. |
| Created                      | The order has been created.  |
| Confirmed                    | The order has been confirmed using card payment and is waiting to be paid by the customer.   |
| PaymentGuaranteed            | The order has been confirmed using a credit option; invoice, paymentplan or accountcredit. |
| WaitingToBeSent              | The order is finished and is waiting to be sent to WebPay’s subsystems for further handling. |
| Final                        | The order is completed in the checkout and managed by WebPay’s subsystems.|

#### 8.9 Locale
| Parameter | Description     |
|-----------|-----------------|
| sv-SE     | Swedish locale. |
| nn-NO     | Norwegian locale. |
| nb-NO     | Norwegian locale. |
| fi-FI     | Finnish locale. |



#### 8.10 PaymentType
| Parameter   | Description     |
|-------------|-----------------|
| Null        | The customer haven’t confirmed the order or can still change paymentType. |
| INVOICE     | Invoice |
| PAYMENTPLAN |	The customer chose a payment plan |
| SVEACARDPAY	      | The customer paid the order with card |
| DirectBank(varies)  |	The customer paid the order with direct bank e.g. Nordea, SEB. See below for all available parameters |
| ACCOUNTCREDIT	  | The customer chose to use their account credit. |

| Parameter         | Description     |
|-------------------|-----------------|
|BANKAXESS	        | BankAxess, Norway |
|DBAKTIAFI	        | Aktia, Finland |
|DBALANDSBANKENFI	| Ålandsbanken, Finland |
|DBDANSKEBANKSE	    | Danske bank, Sweden |
|DBNORDEAFI	        | Nordea, Finland |
|DBNORDEASE	        | Nordea, Sweden |
|DBPOHJOLAFI	    | OP-Pohjola, Finland |
|DBSAMPOFI	        | Sampo, Finland |
|DBSEBSE	        | SEB, Individuals, Sweden |
|DBSEBFTGSE	        | SEB, companies, Sweden |
|DBSHBSE	        | Handelsbanken, Sweden |
|DBSPANKKIFI	    | S-Pankki, Finland |
|DBSWEDBANKSE	    | Swedbank, Sweden |
|DBTAPIOLAFI	    | Tapiola, Finland |
|TRUSTLY            | Trustly |

#### 8.11 CampaignCodeInfo
| Parameter                 | Type      | Description |
|---------------------------|-----------|-------------|
| CampaignCode              | Integer   | CampaignId  |
| ContractLengthInMonths    | Integer   | Contract length in months |
| Description               | String    | Campaign description |
| FromAmount                | Decimal   | Minimum amount (major currency) |
| ToAmount                  | Decimal   | Maximum amount (major currency) |
| InitialFee                | Decimal   | Initial fee (major currency) |
| InterestRatePercent       | Decimal   | Interest rate in percent (e.g. 40 = 40%) |
| MonthlyAnnuityFactor      | Decimal   | Monthly annuity factor |
| NotificationFee           | Decimal   | Notification fee (major currency) |
| NumberOfInterestFreeMonths| Integer   | Number of interest free months |
| NumberOfPaymentFreeMonths | Integer   | Number of payment free months |
| PaymentPlanType           | Integer   | Type of campaign |

### 9.0 HttpStatusCodes
| Parameter | Type          | Description |
|-----------|---------------|-------------|
| 200       | Success       | Request was successful. |
| 201       | Created       | The order was created successfully. The request has been fulfilled, resulting in the creation of a new resource. |
| 202       | Accepted      | Request has been accepted and is in progress. |
| 204       | No content    | The server successfully processed the request and is not returning any content. |
| 302       | Found         | The order was found. |
| 303       | See Other     | Task is complete, Location URI in header. |
| 400       | Bad Request   | The input data was invalid. Validation error. |
| 401       | Unauthorized  | The request did not contain correct authorization. | 
| 403       | Forbidden     | The request did not contain correct authorization. | 
| 404       | Not Found     | No order with the requested ID was found. | 

If the returned ResultCode is not present in the above tables please contact SveaWebPay for further information.

## 10. Administrate orders

[See full examples](examples/admin)

### Errors
If any action is unsuccessful or there is any other error, library will throw exception

**Possible Exceptions**
\Svea\Checkout\Exception\SveaInputValidationException - If any of the input fields is invalid or missing.
\Svea\Checkout\Exception\SveaApiException - If there is some problem with API connection or some error occurred with data validation on the API side.
\Svea\Checkout\Exception\SveaConnectorException - will be returned if some of fields merchantId, sharedSecret or baseUrl is missing.
\Exception - For any other error

### 10.1 Get order
This method is used to get the entire order with all its relevant information. Including its deliveries, rows, credits and addresses.

#### Parameters

| Parameters IN                 | Required   | Type      | Description  |
|-------------------------------|------------|-----------|--------------|
| orderId                       |	*        | int      | Checkout order id of the specified order. |

#### Response

| Parameters OUT                | Type      | Description  |
|-------------------------------|-----------|--------------|
| info about the order          | array     | An array containing all the order details. |



### 10.2 Get task
A task will explain the status of a previously performed operation. When finished it will point towards the new resource with the Location.
#### Parameters

| Parameters IN                 | Required   | Type      | Description  |
|-------------------------------|------------|-----------|--------------|
| locationUrl                   |	*        | string    | Key **HeaderLocation** in response array from accepted admin requests. |

#### Response

| Parameters OUT                 |Type      | Description  |
|-------------------------------|-----------|--------------|
| Task                          | Task      | And object containing details regarding a queued task |

### 10.3 Deliver order
Creates a delivery on a checkout order. Assuming the order got the **CanDeliverOrder** action.

The deliver call should contain a list of all order row ids that should be delivered.
If a complete delivery of all rows should be made the list should either contain all order row ids or be empty.
However if a subset of all active order rows are specified a partial delivery will be made. Partial delivery can only be made if the order has the 
**CanDeliverOrderPartially** action and each OrderRow must have action **CanDeliverRow**.

| Parameters IN                 | Required   | Type      | Description  |
|-------------------------------|------------|-----------|--------------|
| orderId                       |	*        | int       | Checkout order id of the specified order. |
| orderRowIds                   |	*        | array     | array of *orderRowIds* To deliver whole order just send orderRowIds as empty array |

#### Response

| Parameters OUT                |Type       | Description  |
|-------------------------------|-----------|--------------|
| HeaderLocation                | string    | URI to the created task. (Absolute URL) |

### 10.4 Cancel Order
Cancel an order before it has been delivered. Assuming the order has the action **CanCancelOrder**.

| Parameters IN                 | Required   | Type      | Description  |
|-------------------------------|------------|-----------|--------------|
| orderId                       |	*        | int      | Checkout order id of the specified order. |


#### Response

If the order is successfully cancelled, Response is empty. 

### 10.5 Cancel order amount
By specifying a higher amount than the current order cancelled amount then the order cancelled amount will increase, 
assuming the order has the action **CanCancelOrderAmount**. The delta between the new *CancelledAmount* and the former *CancelledAmount* will be cancelled.

The new *CancelledAmount* cannot be equal to or lower than the current *CancelledAmount* or more than *OrderAmount* on the order.

| Parameters IN                 | Required   | Type      | Description  |
|-------------------------------|------------|-----------|--------------|
| orderId                       |	*        | int       | Checkout order id of the specified order. |
| cancelledAmount               |	*        | int(1-13) | 1-13 digits, only positive. Minor currency. |


#### Response

If order amount is successfully cancelled, Response is empty.

### 10.6 Cancel order row
Changes the status of an order row to *Cancelled*, assuming the order has the action **CanCancelOrderRow** and the OrderRow has the action **CanCancelRow**. 

| Parameters IN                 | Required   | Type     | Description  |
|-------------------------------|------------|----------|--------------|
| orderId                       |	*        | int      | Checkout order id of the specified order. |
| orderRowId                    |	*        | int      | Id of the specified row|


#### Response

If order row is successfully cancelled, Response is empty.

### 10.7 Credit order rows
Creates a new credit on the specified delivery with specified order rows. Assuming the delivery has action **CanCreditOrderRows** and the specified order rows also has action **CanCreditRow**

| Parameters IN                 | Required   | Type     | Description  |
|-------------------------------|------------|----------|--------------|
| orderId                       |	*        | int      | Checkout order id of the specified order. |
| deliveryId                    |	*        | int      | Id of the specified delivery row |
| orderRowIds                   |	*        | array    | Id of the specified row |


#### Response

| Parameters OUT                |Type       | Description  |
|-------------------------------|-----------|--------------|
| HeaderLocation                | string    | URI to the created task. (Absolute URL) |

On the returned URL can be checked status of the task.

### 10.8 Credit new order row
By specifying a new credit row, a new credit row will be created on the delivery, assuming the delivery has action **CanCreditNewRow**.

| Parameters IN                 | Required   | Type     | Description  |
|-------------------------------|------------|----------|--------------|
| orderId                       |	*        | int      | Checkout order id of the specified order. |
| deliveryId                    |	*        | int      | Id of the specified delivery row. |
| newCreditOrderRow             |	*        | array    | The new credit row. |

#### Response

| Parameters OUT                |Type       | Description  |
|-------------------------------|-----------|--------------|
| HeaderLocation                | string    | URI to the created task. (Absolute URL) |

On the returned URL can be checked status of the task.

### 10.9 Credit amount
By specifying a credited amount larger than the current credited amount. A credit is being made on the specified delivery. The credited amount cannot be lower than the current credited amount or larger than the delivered amount.

This method requires **CanCreditAmount** on the delivery.

| Parameters IN                 | Required   | Type      | Description  |
|-------------------------------|------------|-----------|--------------|
| orderId                       |	*        | int      | Checkout order id of the specified order. |
| deliveryId                    |	*        | int      | Id of the specified delivery row. |
| creditedAmount                |	*        | int(1-13)| 1-13 digits, only positive. Minor currency. |

#### Response

If order amount is successfully credited, Response is empty.

### 10.10 Add order row
This method is used to add order rows to an order, assuming the order has the action **CanAddOrderRow**. 
If the new order amount will exceed the current order amount, a credit check will be performed.

| Parameters IN                 | Required   | Type     | Description  |
|-------------------------------|------------|----------|--------------|
| orderId                       |	*        | int      | Checkout order id of the specified order. |
| orderRow                      |	*        | array    | Order Row data. |

#### Response

| Parameters OUT                |Type       | Description  |
|-------------------------------|-----------|--------------|
| HeaderLocation                | string    | URI to the created task. (Absolute URL) |
| OrderRowId                    | int       | The row id of the newly created Order Row |

On the returned URL (HeaderLocation) can be checked status of the task.

### 10.11 Update order row
This method is used to update an order row, assuming the order has action "CanUpdateOrderRow" and the order row has the action **CanUpdateRow**. 
The method will update all fields set in the payload, if a field is not set the row will keep the current value.
If the new order amount will exceed the current order amount, a credit check will be performed.

| Parameters IN                 | Required   | Type     | Description  |
|-------------------------------|------------|----------|--------------|
| orderId                       |	*        | int      | Checkout order id of the specified order. |
| orderRowId                    |	*        | int      | Id of the specified row. |
| orderRow                      |	*        | array    | Use only those fields that need to be updated. |

#### Response

If order row is successfully updated, Response is empty.


### 10.12 Data objects

#### 10.12.1 Order
| Parameter             |   Type        | Description                                               |
|-----------------------|---------------|-----------------------------------------------------------|
| Id                    |   int	        | Checkoutorderid of the order|
| Currency	            |  string	    | The current currency as defined by ISO 4217, i.e. SEK, NOK etc.|
| MerchantOrderId	    |  string	    | A string with maximum of 32 characters that identifies the order in the merchant’s systems.| 
| OrderStatus           |  string       | The current state of the order, see list of possible OrderStatus below.| 
| EmailAddress          |  string       | The customer’s email address|
| PhoneNumber           |  string       | The customer’s phone number| 
| PaymentType           | string        | The final payment method for the order. Will only have a value when the order is locked, otherwise null. See list of possible PaymentType below.|
| CreationDate          | DateTime      | Date and time when the order was created|
| NationalId            | string        | Personal- or organizationnumber.|
| IsCompany             | boolean       | True if nationalid is organisationnumber, false if nationalid is personalnumber.|  
| OrderAmount           | int           | The total amount on the order. Minor unit|
| CancelledAmount       | int           | The total cancelled amount on the order. Minor uit|
| ShippingAddress       | Address       | Shipping address of identified customer.|   
| BillingAddress        | Address       | Billing address of identified customer.|
| OrderRows             | List of OrderRow | |
| Deliveries            | List of Delivery | |
| Actions               | List of String | A list of actions possible on the order.|

#### 10.12.2 Delivery

| Parameter             |   Type        | Description                                               |
|-----------------------|---------------|-----------------------------------------------------------|
| Id                    | int           |	Delivery id                                             |
| CreationDate          | DateTime      | Date and time when the order was created|
| InvoiceId             | int           | Invoice identification number, is only set if the payment method is invoice|
| DeliveryAmount        | int           | The total amount on the delivery. Minor unit|
| CreditedAmount        | int           | The total credited amount on the delivery. Minor unit|
| Credits               | List of Credit| |
| OrderRows             | List of OrderRow | | 
| Actions               | List of string | A list of actions possible on the delivery.|

#### 10.12.3 Credit

| Parameter             |   Type        | Description                                               |
|-----------------------|---------------|-----------------------------------------------------------|
| Amount	            | Long          |	Credited amount. Minor currency.|
| OrderRows             | List of OrderRow | |
| Actions               | List of String | A list of actions possible on the credit.|

#### 10.12.4 Task

| Parameter             |   Type        | Description                                               |
|-----------------------|---------------|-----------------------------------------------------------|
| Id	                | Long          |	Identifier for the task |
| Status                | String        | Status of the task |

#### 10.12.5 Order Row

|Parameter	            |R  | RO | Type     | Description                   |	Limits|
|-----------------------|---|----|----------|-------------------------------|-------------------|
| OrderRowId            |   | *  |int      | Order row id from underlying system, unique on order. | Not possible to set through API, only get.|
| ArticleNumber         |   |    |string    | Articlenumber as a string, can contain letters and numbers. | Maximum 256 characters.   |
| Name                  | * |    |string    | Article name. | 1-40 characters. |
| Quantity              | * |    |int      | Quantity of the product. | 1-9 digits. Minor unit.|
| UnitPrice             | * |    |int      | Price of the product including VAT. | 1-13 digits, can be negative. Minor currency.|
| DiscountPercent       |   |    |int      | The discountpercent of the product. | 0-9900. Minor unit| 
| VatPercent            | * |    |int      | The VAT percentage of the current product. | Valid vat percentage for that country . Minor unit.0-10000|
| Unit                  |   |    |string    | The unit type, e.g., “st”, “pc”, “kg” etc.  | 0-4 characters. |
| IsCancelled           |   | *  |boolean  | Determines if the row is cancelled. | Not possible to set through API, only get.|
| Actions               |   | *  |List of string | A list of actions possible on the order row. See list of OrderRow actions below. | Not possible to set through API, only get.|

#### 10.12.6 Address

| Parameter             |   Type        | Description                                               |
|-----------------------|---------------|-----------------------------------------------------------|
| FullName              | 	string      | 	Company: name of the company. Individual: first, middle and last name(s)  |
| StreetAddress         | 	string      | 	Street address |
| CoAddress             | 	string      | 	Co address |
| PostalCode            | 	string      | 	Postal code |
| City                  | 	string      | 	City |
| CountryCode           | 	string      | 	2-letter ISO country code |

#### 10.12.7 Order Status

| Parameter             |  Description                                               |
|-----------------------|------------------------------------------------------------|
| Open                  | The order is open and active. This includes partially delivered orders |
| Delivered             | The order is fully delivered |
| Cancelled             | The order is fully cancelled |
| Failed                | The payment for this order has failed |

#### 10.12.8 Order actions

| Parameter                 |  Description                                               |
|---------------------------|------------------------------------------------------------|
| CanDeliverOrder           ||
| CanDeliverOrderPartially  ||
| CanCancelOrder            ||
| CanCancelOrderRow         ||
| CanCancelOrderAmount      ||
| CanAddOrderRow            ||
| CanUpdateOrderRow         ||

#### 10.12.9 Delivery actions

| Parameter             |  Description                                               |
|-----------------------|------------------------------------------------------------|
| CanCreditNewRow       ||
| CanCreditOrderRows    ||
| CanCreditAmount       ||		

#### 10.12.10 Order Row actions

| Parameter             |  Description                                               |
|-----------------------|------------------------------------------------------------|
| CanDeliverRow         ||	
| CanCancelRow          ||
| CanCreditRow          ||	
| CanUpdateRow          ||