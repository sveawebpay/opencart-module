# PHP Checkout library for Svea Checkout

## Index
* [1. Setup](#1-setup)
* [2. General information](#2-general-information)
* [3. Create order](#3-create-order)
* [4. Get order](#4-get-order)
* [5. Update order](#5-update-order)
* [6. Response](#6-response)
* [7. Additional requests](#7-additional-requests)
* [8. Data structures](#8-data-structures)
* [9. HttpStatusCodes](#9-httpstatuscodes)
* [10. Order administration](#10-order-administration)
* [11. Javascript API](#11-javascript-api)
## Introduction
The checkout offers a complete solution with a variety of payment methods. The payment methods that are currently available in the checkout are invoice, payment plan, account credit, card payments and payment by bank.


The checkout supports both B2C and B2B payments, fast customer identification and caches customers behaviour.


This library provides entrypoints to integrate the checkout into your platform and to administrate checkout orders.

### Test credentials

You can find credentials that can be used in the stage environment without signing an contract with Svea Ekonomi [here](https://www.svea.com/globalassets/sweden/foretag/betallosningar/e-handel/integrationspaket-logos-and-doc.-integration-test-instructions-webpay/test-instructions-webpay-partners-.pdf)

The example files also contain merchant credentials which can be used in the stage environment.

### 1. Setup

#### 1.1 Installing with [**Composer**](https://getcomposer.org/)

Execute the following line in your command line interface:
```bash

    composer require sveaekonomi/checkout

```

or add the following to your composer.json:

```json
    {
        "require": {
            "sveaekonomi/checkout": "dev-master"
        }
    }
```
and run command ` composer update ` in your CLI

#### 1.2 Install without composer
You can also download the library and upload it onto your server.

### 2. General information

#### 2.1 Creating a Connector
You have to use a connector object as parameter when creating a CheckoutClient or a CheckoutAdminClient-object which is used to create API requests.

The connector defines what credentials should be used and which environment should be used.


Parameters for creating Connector are: checkoutMerchantId, checkoutSecret and base API url(environment).

```php
// include the library
include 'vendor/autoload.php';

// include library without composer, include.php is in the library root
require_once 'include.php';

$checkoutMerchantId = '100001';
$checkoutSecret = 'checkoutSecret';
//set endpoint url. Eg. test or prod
$baseUrl = \Svea\Checkout\Transport\Connector::TEST_BASE_URL;

$connector = \Svea\Checkout\Transport\Connector::init($checkoutMerchantId, $checkoutSecret, $baseUrl);
```
#### 2.2 CheckoutClient
The CheckoutClient class contains four methods which can be used to:
* [Create order](#3-create-order) - Creates a Svea Checkout order
* [Update order](#5-update-order) - Updates an existing Svea Checkout order
* [Get order](#4-get-order) - Gets order data from an existing order
* [Get payment plan campaigns](#71-getavailablepartpaymentcampaigns) - Fetches campaigns which can for example be used to display price per month on product page

To ensure that a snippet always is displayed to the end-user, we recommend using the following flow in your platform:

![Recommended flow of checkout](docs/image/flow.png?raw=true)

#### 2.3 CheckoutAdminClient
The CheckoutAdminClient class contains methods which are used to administrate orders which have been creating using the CheckoutClient class.

Orders can only be administrated if [CheckoutOrderStatus](#88-checkoutorderstatus) is "FINAL", any other status indicates that the order has not been finalized by the end-customer.

In order to perform an action on an order, the order needs to have an [action](#10128-order-actions). You'll have to get the order using ["Get Order"](#101-get-order) and check which [actions](#10128-order-actions) are available and then use a corresponding method.

For example, if you want to credit an order you first have to use [Get order](#101-get-order). Let's say that the action returned is "CanCreditAmount", then you'll have to use [Credit amount](#109-credit-amount) to credit the order.

Some actions may not be available on certain order types, but a good integration doesn't check order type but rather which actions are available for use.

The available methods are:
* [Get order](#101-get-order) - Returns order data from Payment Admin, contains other information than the method in CheckoutClient
* [Get task](#102-get-task) - Returns the status of a previously performed operation
* [Deliver order](#103-deliver-order) - Creates a delivery on a checkout order(sends invoice to end-customer etc.)
* [Cancel order](#104-cancel-order) - Cancels an order
* [Cancel order amount](#105-cancel-order-amount) - Removes a specified amount from an order
* [Cancel order row](#106-cancel-order-row) - Removes order rows from an order
* [Credit order rows](#107-credit-order-rows) - Credits order rows on a delivered order
* [Credit new order row](#108-credit-new-order-row) - Creates a new order row with a credited amount
* [Credit amount](#109-credit-amount) - Credits a specified amount
* [Add order row](#1010-add-order-row) - Adds an order row to the order
* [Update order row](#1011-update-order-row) - Updates an existing order row


### 3. Create order
To create a new order, you'll need to instantiate an object of \Svea\Checkout\CheckoutClient and pass a [*Connector*](#2-create-a-connector) as an parameter.

You can then use the method "create" in the object, pass the data below into the method. See example below.

The response will contain all order data along with a snippet which contains the iframe which needs to be rendered to the end-user.

| Parameters IN   | Required | Type | Description                                       |
|-----------------|----------|------|---------------------------------------------------|
|MerchantSettings | *        |[*MerchantSettings*](#81-merchantsettings) |The merchants settings for the order              |
|Cart             | *        |Cart  |A cart-object containing the [*OrderRows*](#83-orderrow)            |
|Locale           | *        |String|The current locale of the checkout, i.e. sv-SE etc. Does not change the actual language in the GUI|
|Currency         | *        |String|The current currency as defined by ISO 4217, i.e. SEK, NOK etc. Currently fixed to merchant, only SEK for swedish merchants, etc |
|CountryCode      | *        |String|Defined by two-letter ISO 3166-1 alpha-2, i.e. SE, NO, FI etc. Setting this parameter to anything but the country which the merchant is configured for will trigger the "International flow" which is in english and only supports card payments |
|ClientOrderNumber| *        |String|A string with maximum of 32 characters identifying the order in the merchant’s system|
|PresetValues     |          |Array of [*Preset values*](#84-presetvalue) |Array of [*Preset values*](#84-presetvalue) chosen by the merchant to be pre-filled in the iframe |
|IdentityFlags    |          |Array of [*IdentityFlags*](#812-identityflags) | Array of [*IdentityFlags*](#812-identityflags) used to hide certain features of the iframe |
|PartnerKey       |          | Guid | Optional, provided by Svea on request. Used to create statistics.
|MerchantData     |          | String | Metadata visible in the checkout API, returned when order is fetched through the API. |
  
| Parameters OUT | Type | Description |
|----------------|------|-------------|
|Data            | Data | An object containing all of the order-data, see structure [here](#6-response). 

#### Create order example:

```php
// include the library
include 'vendor/autoload.php'

// without composer
require_once 'include.php';

$data = array(
        "countryCode" => "SE",
        "currency" => "SEK",
        "locale" => "sv-SE",
        "clientOrderNumber" => rand(10000,30000000),
        "merchantData" => "Test string from merchant",
        "cart" => array(
            "items" => array(
                array(
                    "articleNumber" => "1234567",
                    "name" => "Yellow rubber duck",
                    "quantity" => 200,
                    "unitPrice" => 12300,
                    "discountPercent" => 1000,
                    "vatPercent" => 2500,
                    "unit" => "st",
                    "temporaryReference" => "1",
                    "merchantData" => "Size: S"
                ),
                array(
                    "articleNumber" => "987654321",
                    "name" => "Blue rubber duck",
                    "quantity" => 500,
                    "unitPrice" => 25000,
                    "discountPercent" => 1000,
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
            "termsUri" => "http://yourshop.se/terms/",
            "checkoutUri" => "http://yourshop.se/checkout/",
            "confirmationUri" => "http://yourshop.se/checkout/confirm/",
            "pushUri" => "https://yourshop.se/push.php?checkout_order_id={checkout.order.uri}",
        )
    );

$checkoutClient = new \Svea\Checkout\CheckoutClient($connector);

$response = $checkoutClient->create($data);
```

[See full example](https://github.com/sveawebpay/php-checkout/blob/master/examples/create-order.php)

### 4. Get Order
To fetch an existing order, you'll need to instantiate an object of \Svea\Checkout\CheckoutClient and pass a [*Connector*](#2-create-a-connector) as an parameter.

You can then use the method "get" in the object, pass the data below into the method. See example below.

The response contains the order information and the along with the GUI which can be used to render the iframe once again.

| Parameters IN                | Required  | Type      | Description  |
|------------------------------|-----------|-----------|--------------|
| orderId                           |	*      | Long      | Checkoutorderid of the specified order |

| Parameters OUT               | Type      | Description  |
|------------------------------|-----------|--------------|
| Data                         | Data      | An object containing all of the order-data, see structure [here](#6-response) |


```php
// include the library
include 'vendor/autoload.php'

// without composer
require_once 'include.php';

$data = array(
        'orderId' => 51721
    );

$checkoutClient = new \Svea\Checkout\CheckoutClient($connector);

$response = $checkoutClient->get($data);
```

[See full example](https://github.com/sveawebpay/php-checkout/blob/master/examples/get-order.php)



### 5. Update Order
To update an existing order, you'll need to instantiate an object of \Svea\Checkout\CheckoutClient and pass a [*Connector*](#2-create-a-connector) as an parameter.

The method returns the order information and the updated Gui needed to display the iframe for Svea Checkout. The previously displayed iframe should be replaced by the iframe in the response received when updating the order unless using the Javascript API.

Updating an order is only possible while the CheckoutOrderStatus is "Created", see [*CheckoutOrderStatus*](#78-checkoutorderstatus).

This method can be combined with the Javascript API, if the iframe is disabled using the JS API and the order is updated while the it's disabled the iframe will be updated once it's enabled again. This removes the requirement of replacing the iframe once the order is updated.

| Parameters IN:     | Required   | Type      | Description  |
|-------------------------------|------------|-----------|--------------|
| OrderId                       |	*        | Long      | Checkoutorderid of the specified order.
| Cart                          |	 *       | Cart      | A cart-object containing the [*OrderRows*](#73-orderrow) |
| MerchantData                  |            | String    | Can be used to store data, the data is not displayed anywhere but in the API |

```php
// include the library
include 'vendor/autoload.php'

// without composer
require_once 'include.php';

...

$checkoutClient = new \Svea\Checkout\CheckoutClient($connector);

$data = array(
        "orderId" => 251147,
        "merchantData" => "test",
        "cart" => array(
            "items" => array(
                array(
                    "articleNumber" => "123456",
                    "name" => "Yellow rubber duck",
                    "quantity" => 200,
                    "unitPrice" => 66600,
                    "discountPercent" => 1000,
                    "vatPercent" => 2500,
                    "temporaryReference" => "230",
                    "merchantData" => "Size: M"
                ),
                array(
                    "type" => "shipping_fee",
                    "articleNumber" => "658475",
                    "name" => "Shipping Fee Updated",
                    "quantity" => 100,
                    "unitPrice" => 4900,
                    "vatPercent" => 2500,
                    "temporaryReference" => "231",
                    "merchantData" => null
                )
            )
        )
    );

$response = $checkoutClient->update($data);
```

[See full example](https://github.com/sveawebpay/php-checkout/blob/master/examples/update-order.php)

### 6. Response
The response contains information about the order such as Cart, Status, PaymentType and much more.

| Parameters OUT                | Type                 | Description |
|-------------------------------|----------------------|-------------|
| MerchantSettings              | [*Merchant settings*](#81-merchantsettings)     | Specific merchant URIs |
| Cart                          | Cart                 | A cart-object containing the [*OrderRows*](#83-orderrow) |
| Gui                           | [*Gui*](#85-gui)     | Contains iframe and layout information  |
| Customer                      | [*Customer*](#86-customer)             | Identified [*Customer*](#86-customer) of the order. |
| ShippingAddress               | [*Address*](#87-address)              | Shipping [*Address*](#87-address) of identified customer. |
| BillingAddress                | [*Address*](#87-address)              | Billing [*Address*](#87-address) of identified customer. Returned empty if same as ShippingAddress. |
| Locale                        | String               | The current locale of the checkout, i.e. sv-SE etc. Does not override language in iframe |
| Currency                      | String               | The current currency as defined by ISO 4217, i.e. SEK, NOK etc. Merchant specific, swedish merchants uses SEK etc.|
| CountryCode                   | String               | Defined by two-letter ISO 3166-1 alpha-2, i.e. SE, NO, FI etc.  |
| ClientOrderNumber             | String               | A string with maximum of 32 characters that identifies the order in the merchant’s systems |
| PresetValues                  | Array of [*Preset values*](#84-presetvalue) | [*Preset values*](#84-presetvalue) chosen by the merchant to be pre-filled in the iframe |
| OrderId                       | Long                 | CheckoutOrderId of the order |
| Status                        | [*CheckoutOrderStatus*](#88-checkoutorderstatus) |The current status of the order. |
| EmailAddress                  | String               | The customer’s email address |
| PhoneNumber                   | String               | The customer’s phone number |
| MerchantData                  | String               | Can be used to store data, the data is not displayed anywhere but in the API |
| SveaWillBuyOrder              | Boolean              | Only applicable if merchant uses the "no-risk flow", used to determine if Svea buys the invoice or not | 
| IdentityFlags                 | Array of [*IdentityFlags*](#812-identityflags) | Settings which disables certain features in the iframe. See [*IdentityFlags*](#) |
| PaymentType                   | String               | The final payment method for the order. Will only have a value when the order is finalized, otherwise null. See [*PaymentType*](#810-paymenttype)|
| CustomerReference             | String               | B2B Customer reference |

Sample response
```php
Array
(
    [MerchantSettings] => Array
        (
            [CheckoutValidationCallBackUri] => 
            [PushUri] => https://yourdomain.se/push.php?svea_order_id={checkout.order.uri}
            [TermsUri] => http://yourdomain.se/terms
            [CheckoutUri] => http://yourdomain.se/checkout/
            [ConfirmationUri] => http://yourdomain.se/checkout/confirm
            [ActivePartPaymentCampaigns] => Array
                (
                )

            [PromotedPartPaymentCampaign] => 0
        )

    [Cart] => Array
        (
            [Items] => Array
                (
                    [0] => Array
                        (
                            [ArticleNumber] => 1234567
                            [Name] => Yellow rubber duck
                            [Quantity] => 200
                            [UnitPrice] => 66600
                            [DiscountPercent] => 1000
                            [VatPercent] => 2500
                            [Unit] => 
                            [TemporaryReference] => 
                            [RowNumber] => 1
                            [MerchantData] => Size: M
                        )

                    [1] => Array
                        (
                            [ArticleNumber] => 987654321
                            [Name] => Blue rubber duck
                            [Quantity] => 500
                            [UnitPrice] => 25000
                            [DiscountPercent] => 1000
                            [VatPercent] => 2500
                            [Unit] => pcs
                            [TemporaryReference] => 
                            [RowNumber] => 2
                            [MerchantData] => 
                        )
                        
                    [2] => Array
                        (
                            [ArticleNumber] => 6eaceaec-fffc-41ad-8095-c21de609bcfd
                            [Name] => InvoiceFee
                            [Quantity] => 100
                            [UnitPrice] => 2900
                            [DiscountPercent] => 0
                            [VatPercent] => 2500
                            [Unit] => st
                            [TemporaryReference] => 
                            [RowNumber] => 3
                            [MerchantData] => 
                        )
                )
        )
        
    [Customer] => Array
        (
            [Id] => 626
            [NationalId] => 194605092222
            [CountryCode] => SE
            [IsCompany] => 
        )

    [ShippingAddress] => Array
        (
            [FullName] => Persson, Tess T
            [FirstName] => Tess T
            [LastName] => Persson
            [StreetAddress] => Testgatan 1
            [CoAddress] => c/o Eriksson, Erik
            [PostalCode] => 99999
            [City] => Stan
            [CountryCode] => SE
            [IsGeneric] => 
            [AddressLines] => Array
                (
                )

        )

    [BillingAddress] => Array
        (
            [FullName] => Persson, Tess T
            [FirstName] => Tess T
            [LastName] => Persson
            [StreetAddress] => Testgatan 1
            [CoAddress] => c/o Eriksson, Erik
            [PostalCode] => 99999
            [City] => Stan
            [CountryCode] => SE
            [IsGeneric] => 
            [AddressLines] => Array
                (
                )

        )


    [Gui] => Array
            (
                [Layout] => desktop
                [Snippet] => <iframe src=\"\"></iframe>
            )
    [Locale] => sv-SE
    [Currency] => SEK
    [CountryCode] => SE
    [PresetValues] => 
    [ClientOrderNumber] => 8828014
    [OrderId] => 251147
    [EmailAddress] => test@yourdomain.se
    [PhoneNumber] => 12312313
    [PaymentType] => INVOICE
    [Status] => Final
    [CustomerReference] => 
    [SveaWillBuyOrder] => 1
    [IdentityFlags] => 
    [MerchantData] => test
)
```

The checkout GUI contains the Snippet and the Layout. The Snippet contains the Html and JavaScript that you implement on your
page where you want to display the iframe for Svea checkout. The Layout is a String defining the orientation of the customers screen.

```php
echo $response['Gui']['Snippet']
```
### 7. Additional requests

#### 7.1 GetAvailablePartPaymentCampaigns

GetAvailablePartPaymentCampaigns can be used to fetch the details of all the campaigns that are available on the merchant

The information can be used to for example display information about how much it will cost to pay for a certain product or products on the actual product page.

[See example](https://github.com/sveawebpay/php-checkout/blob/master/examples/get-available-part-payment-campaigns.php)


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

Executing the code above will return an array with [8.11 CampaignCodeInfo](#811-campaigncodeinfo)

Example response:
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

The information should be stored in a database for fast access instead of sending requests on demand.

##### Calculation formulas

Calculating price per month:
```php
(InitialFee + (ceil(ProductPrice * MonthlyAnnuityFactor) + NotificationFee) * ContractLengthInMonths) / ContractLengthInMonths
```

Using the second campaign with a product price of 1500kr in the example above will result in:
(0 + (ceil(1500 * 0.092586652785396) + 29 ) * 12) / 12 = (0 + (139 + 29) * 12 ) / 12 = 168kr



Calculating total amount to pay:
```php
InitialFee + (ProductPrice * MonthlyAnnuityFactor + NotificationFee) * ContractLengthInMonths
```

Using the second campaign with a product price of 150kr in the example above will result in:
0 + (150 * 0.092586652785396 + 29 ) * 12 = 514.655975 round upwards to closest whole number -> 515kr

### !!! NOTE !!!
If you are a finnish merchant you have to display ALL the values described [here](https://www.kkv.fi/sv/beslut-och-publikationer/publikationer/konsumentrombudsmannens-riktlinjer/enligt-substans/tillhandahallande-av-konsumentkrediter/#luottolinjausSVE5.1) to be compliant with finnish laws.

### 8. Data structures

#### 8.1 MerchantSettings

| Parameters                | Required  | Type      | Description  | Limits  |
|------------------------------|-----------|-----------|--------------|---------|
| TermsUri                     |	*      | string    | URI to a page which contains terms of the webshop. | 1-500 characters, must be a valid Url |
| CheckoutUri                  |	*      | string    | URI to the page in the webshop that loads the Checkout.  | 1-500 characters, must be a valid Url |
| ConfirmationUri              |	*      | string    | URI to the page in the webshop displaying specific information to a customer after the order has been confirmed. | 1-500 characters, must be a valid Url |
| PushUri                      |	*      | string    | URI to a location that is expecting callbacks when CheckoutOrderStatus is changed. Uri should use the {checkout.order.uri} placeholder.  | 1-500 characters, must be a valid Url |
| CheckoutValidationCallBackUri|           | string    | An optional URl to a location that is expecting callbacks from the Checkout to validate order’s stock status, and also the possibility to update checkout with an updated ClientOrderNumber. Uri may have a {checkout.order.uri} placeholder which will be replaced with the CheckoutOrderId. Please refer below [*CheckoutValidationCallbackResponse*](#813-checkoutvalidationcallbackresponse) to see the expected response. | 1-500 characters, must be a valid Url |
| ActivePartPaymentCampaigns   |           | Array of CampaignCode | Array of valid CampaignCodes. If used then list of available part payment campaign options will be filtered through the chosen list. | Must be an array of valid CampaignCode |
| PromotedPartPaymentCampaign  |           | integer   | Valid CampaignID. If used then the chosen campaign will be shown as the first payment method in all payment method lists. | Must be valid CampaignID |

#### 8.2 Items

| Parameters                | Required  | Type                                 | Description         |
|------------------------------|-----------|--------------------------------------|---------------------|
| Items                        |	*      | List of [*OrderRows*](#83-orderrow)  | See structure below |

#### 8.3 OrderRow

| Parameters                | Required   | Type      | Description  | Limits  |
|------------------------------|------------|-----------|--------------|---------|
| ArticleNumber                |	        | String    | Articlenumber as a string, can contain letters and numbers. | Maximum 1000 characters |
| Name                         |	*       | String    | Article name | 1-40 characters |
| Quantity                     |	*       | Integer       | Set as basis point (1/100) e.g  2 = 200      | 1-9 digits. Minor currency |
| UnitPrice                    |	*       | Integer       | Set as basis point (1/100) e.g. 25.00 = 2500 | 1-13 digits, can be negative. Minor currency |
| DiscountPercent              |	        | Integer       | The discountpercent of the product. | 0-100 |
| VatPercent                   |	*       | Integer       | The VAT percentage of the current product. | Valid vat percentage for that country. Minor currency.  |
| Unit                         |            | String        | The unit type, e.g., “st”, “pc”, “kg” etc. | 0-4 characters|
| TemporaryReference           |            | String        | Can be used when creating or updating an order. The returned rows will have their corresponding temporaryreference as they were given in the indata. It will not be stored and will not be returned in GetOrder.  | |
| MerchantData                 |            | String        | Can be used to store data, the data is not displayed anywhere but in the API

#### 8.4 PresetValue

| Parameters             | Required  | Type          | Description  |
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
| IsCompany                 | Boolean       |              | Required if nationalid is set |

#### 8.5 Gui

| Parameters               |  Type      | Description  |
|------------------------------|------------|--------------|
| Layout                       | String     | Defines the orientation of the device, either “desktop” or “portrait”.  |
| Snippet                      | String     | HTML-snippet including javascript to populate the iFrame. |

#### 8.6 Customer

| Parameters               | Type      | Description  |
|------------------------------|-----------|--------------|
| NationalId                   | String    | Personal- or organizationnumber. |
| IsCompany                    | Boolean   | True if nationalId is organisationnumber, false if nationalid is personalnumber.   |
| CountryCode                  | String    |  Defined by two-letter ISO 3166-1 alpha-2, i.e. SE, DE, FI etc.|
| Id                           | Integer   | Customer-specific id |

#### 8.7 Address

| Parameters                | Type      | Description  |
|------------------------------|-----------|--------------|
| FullName                     | String    | Company: name of the company. Individual: first name(s), middle name(s) and last name(s). |
| FirstName                    | String    | First name(s).  |
| LastName                     | String    | Last name(s).   |
| StreetAddress                | String    | Street address.  |
| CoAddress                    | String    | Co address.  |
| PostalCode                   | String    | Postal code.  |
| City                         | String    | City.  |
| CountryCode                  | String    | Defined by two-letter ISO 3166-1 alpha-2, i.e. SE, DE, FI etc.|
| IsGeneric                    | Boolean   | True if international flow is used |
| AddressLines                 | Array of strings | Null unless international flow is used

#### 8.8 CheckoutOrderStatus

The order can only be considered “ready to send to customer” when the CheckoutOrderStatus is Final. No other status can guarantee payment.

| Parameters OUT               | Description  |
|------------------------------|--------------|
| Cancelled                    | The order has been cancelled due to inactivity (default is 48h, can be changed per merchant if requested) |
| Created                      | The order has been created  |
| Final                        | The order is completed in the checkout and managed by WebPay’s subsystems. The order can now be administrated using either the library or browsing to the admin user interface|

#### 8.9 Locale
| Parameter | Description     |
|-----------|-----------------|
| sv-SE     | Swedish locale. |
| nn-NO     | Norwegian locale. |
| nb-NO     | Norwegian locale. |
| fi-FI     | Finnish locale. |
| da-DK     | Danish locale. |
| de-DE     | German locale. |


#### 8.10 PaymentType
| Parameter   | Description     |
|-------------|-----------------|
| Null        | The customer hasn't confirmed the order. |
| INVOICE     | Invoice (Svea buys the invoice) |
| ADMININVOICE | Invoice (Svea only administrates the invoice, not enabled by default) |
| PAYMENTPLAN |	The customer chose a payment plan |
| SVEACARDPAY	      | The customer paid the order with card |
| Directbank(varies)  |	The customer paid the order with direct bank e.g. Nordea, SEB. See below for all available parameters |
| ACCOUNTCREDIT	  | The customer chose to use their account credit. |
| LEASINGUNAPPROVED | Leasing (Manual approve process by Sveas leasing department, check Store pay admin page) |
| LEASINGAPPROVED | Leasing (Automatically approved leasing contract)

Directbanks:

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

#### 8.12 IdentityFlags
| Parameter                 | Type      | Description |
|---------------------------|-----------|-------------|
| HideNotYou              | Boolean   | Hides "Not you?"-button in iframe  |
| HideChangeAddress       | Boolean   | Hides "Change address"-button in iframe |
| HideAnonymous           | Boolean   | Hides anonymous flow, forcing users to identify with their nationalId to perform a purchase |

#### 8.13 CheckoutValidationCallbackResponse
If a CheckoutValidationCallbackUri is set on an order when it's created, Svea will send a HTTP GET request to the specified URI when a customer clicks on "Confirm Order".

The response should have HTTP status 200, indicating a successful request. The response should contain the required parameters below. Encode the response in JSON before responding.

| Parameter         | Required | Type    | Description |
|-------------------|----------|---------|-------------|
| Valid             | *        | Boolean | Should be set to true if Svea should accept the order |
| ClientOrderNumber |          | String  | Max 32 characters. Set if you want the ClientOrderNumber to be updated. |

### 9. HttpStatusCodes
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

If the returned ResultCode is not present in the above tables please contact Svea Ekonomi for further information.

## 10. Order administration

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
| Order          | array     | An array containing all the order details. See [10.12 Data objects](#1012-data-objects) |



### 10.2 Get task
A task will explain the status of a previously performed operation. When finished it will point towards the new resource with the Location.
#### Parameters

| Parameters IN                 | Required   | Type      | Description  |
|-------------------------------|------------|-----------|--------------|
| locationUrl                   |	*        | string    | Key **HeaderLocation** in response array from accepted admin requests. |

#### Response

| Parameters OUT                 |Type      | Description  |
|-------------------------------|-----------|--------------|
| Task                          | [Task](#10124-task)      | An object containing details regarding a queued task |

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
| newCreditOrderRow             |	*        | array    | [Order Row](#10125-order-row) |

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
| orderRow                      |	*        | array    | [Order Row](#10125-order-row) |

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
| orderRow                      |	*        | array    | Use only those fields that need to be updated. [Order Row](#10125-order-row) |

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
| BillingAddress        | Address       | Billing address of identified customer. Returned empty if same as ShippingAddress. |
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

## 11. Javascript API

(Please note that the API is still considered a work in progress and might see significant changes.)

### API entry point

window.scoApi is the root object for the API and contains all the operations available.

### Listening for API readiness

The checkout raises an event when ready, which can be used to safely access the API.

*Example:*
```javascript
document.addEventListener("checkoutReady", function() {
    window.scoApi... // Your code here
});
```

### Available operations

#### observeEvent(propertyString, handlerFunction) => function

Observes the client data for changes, calling the supplied function when a change is detected.

Returns a function that can be called to stop observing the specified property.

The following properties are currently supported:

```javascript
"identity.isCompany"
"identity.email"
"identity.phoneNumber"
"identity.companyName"
"identity.firstName"
"identity.lastName"
"identity.streetAddress"
"identity.coAddress"
"identity.postalCode"
"identity.city"
"identity.addressLines"
```

*Example:*
```javascript
// Observe the city property
var unsubscribe = window.scoApi.observeEvent("identity.city", function (data) { 
    console.log("City changed to %s.", data.value); 
});

// Stop observing
unsubscribe();
```

#### setCheckoutEnabled(value) => void

Pass a false-ish value to disable the checkout. While disabled, the merchant can safely perform updates to the cart. When finished, call setCheckoutEnabled(true) to re-enable the checkout and make it reflect the changes made.