# Index <a name="index"></a>
 
* [I. Information](#i-information)
* [1. Installation & configuration](#i1)
    * [1.1 General](#i1-1)
    * [1.2 Svea Checkout](#i1-2)
    * [1.3 Standalone payment methods](#i1-3)
    * [1.4 Svea Invoice Fee](#i1-4)
    * [1.5 Additional information](#i1-5)
* [2. Miscellaneous](#i2)
    * [2.1 Administrating orders](#i2-1)
    * [2.2 Troubleshooting and recommendations](#i2-2)
    * [2.3 Product Price Widget](#i2-3)
    * [2.4 Important information](#i2-4)

# I. Information

## OpenCart - Svea Checkout and Svea payment modules

* Supports OpenCart 2.3.0.2 - 3.1.0.0_a1 (older versions can be found in the branches)
* Requires PHP 5.3 or higher (namespace support)

Features:

* Svea Checkout
* Standalone payment modules for invoices, payment plans, card and direct bank payments in Sweden, Finland, Norway, Denmark, Netherlands and Germany.
* Includes integrated admin functionality that corresponds with Svea Ekonomi's servers, current functions are delivering, confirming, canceling and crediting orders
* Product price widget for lowest monthly cost on products where applicable

If you are experiencing technical issues with this module, or if you have a feature suggestion, please submit an issue on the GitHub issue page.

For release history, see [**github release tags**](https://github.com/sveawebpay/opencart-module/releases)

# 1. Installation <a name="i1"></a>
Before installing, we recommend that you set up a test environment and make a backup of your existing site, database and settings.

The following examples assumes that you have already downloaded and installed OpenCart, if you are upgrading from a previous version of the module disable and uninstall the module from the OpenCart backend before installing and delete the vqcache folder if you're using VQMod.

## 1.1 General <a name="i1-1"></a>
* Download the latest version of the module from repository
* Extract the contents of the folder "src/" into your OpenCart root directory, the folders _admin_, _catalog_, _svea_ should merge with the existing folders
* If you are going to use Svea Checkout see [1.2 Checkout](#i1-2) otherwise follow [1.3 Standalone payment methods](#i1-3)

## 1.2 Svea Checkout <a name="i1-2"></a>
* Navigate to the Extensions/Modules page and find Svea Checkout
* Install Svea Checkout
* Select the options you which to use and fill out your credentials that are give to you by Svea Ekonomi.

Now you're all set! You have now configured all the required settings for the checkout and can now proceed with using the checkout!

## 1.3 Standalone payment methods <a name="i1-3"></a>
* Navigate to the Extensions/Payment page and find the payment methods that you wish to use
* The available standalone payment methods are: Svea Invoice, Svea Payment Plan, Svea Card payments, Svea Directbank payments.
* Install & configure the payment modules

After configuring the payment modules the payment methods will show up on your checkout page.

## 1.4 Svea Invoice Fee <a name="i1-4"></a>
If you want to use an invoice fee for the checkout you have to send a request to [support-webpay@sveaekonomi.se](mailto:support-webpay@sveaekonomi.se) with the amount you want B2C customers to pay and the amount you want B2B customers to pay. The integration team will then configure your checkout merchant in Svea's database.

As soon as the integration team has configured your merchant in the database, the prices will appear automatically on orders where customers select invoice, the team will inform you when the change has been made.

If you are using the regular the standalone Svea Invoice payment method, follow the steps below to set an invoice fee.

* Navigate to Extensions/Order Totals in your OpenCart admin
* Find _Svea Invoice Fee_ in the list and install it
* For each country which you will accept invoice payments from, select the corresponding tab and fill all the fields
* Select a value for the _sort order_ e.g. 4 or 5, preferably a value lower than the sort order for taxes otherwise there will be no tax on the invoice fee

## 1.5 Additional information <a name="i1-5"></a>

### Order Total module sort order
* For use with vouchers, the voucher module sort order must be after taxes.
* For coupons, the coupon module sort order (in admin, under extensions/order totals) must be set to have a lower sort order than taxes.
* Also, the coupon discount amount (in admin, under sales/coupon) must be specified excluding tax. The coupon tax discount will then be calculated in accordance with OpenCart standard behaviour, and is specified in the order history.
* The recommended order total sort order is: sub-total (lowest), Svea invoice fee, shipping, coupon, taxes, store credit, voucher and total.

### Hide Svea comments 
It's possible to hide the comments added by the module by enabling "Hide Svea comments" on the administration page of the payment method. This will however not hide messages that contain critical information, for example invoiceIds or contractNumbers.

# 2. Miscellaneous <a name="i2"></a>

## 2.1 Administrating orders <a name="i2-1"></a>
**Important!** The Svea order id information saved in the Comment field must not be changed for the action to work. You may add to the information, but not change or remove it.

To deliver an order just set the order status in the order history to one of the statuses that's configured in System->Settings->Store->Option->Complete Order Status

To credit or cancel an order just select any other status that's not selected in the above setting.

**Important! You have to change your fraud status to something else than the statuses in "Processing order statues", "Complete order statuses" and "Pending order status" otherwise the actions might not be sent to Svea.**

Actions available:

| Method        | Deliver order | Cancel order  |   Credit order    | Auto Deliver order  |
|---------------|:-------------:|:-------------:|:-----------------:|:-------------------:|
| Invoice       |   *           |   *           |   *               |   *                 |
| Payment plan  |   *           |   *           |   *               |   *                 |
| Card          |   *           |   *           |   *               |   *                 |
| Direct bank   |   N/A         |   N/A         |   *               |   N/A               |

## 2.2 Troubleshooting and recommendations <a name="i2-2"></a>
Always check that your settings are properly configured before posting issues or contacting our support, go through these steps first:

### Check your Svea customer credentials

* Your _username, password, client no_ for Invoice and Part Payment are correct and doesn't contain any whitespaces.
* Your _secret word_ and _merchant id_ for Card and Direct bank payments are correct and that the test-secret word and production-secret word are in their correct fields and doesn't contain any whitespaces.
* Your _secret word_ and _merchant id_ for Svea Checkout are correct and that the test-secret word and production-secret word are in their correct fields and doesn't contain any whitespaces.

### Check correlated OpenCart settings and localisations

* Under _system -> localisation_, the correlating _tax class, tax rate_ (including customer groups), _currency_ and _geo zone_ settings are correct.
* Under _extensions -> order totals_, the sort order et al are correct.

### Specific payment method problems FAQ

#### The invoice fee shows doesn't seem to be included in the OpenCart order total, though it is present in the Svea invoice?
Ensure that you have specified a valid tax class for the country in question in the Svea invoice fee module.

#### My Svea Checkout, card or direct payments don't go through after reinstalling my shop?
Check that you don't attempt to reuse order numbers, if you're reusing old order numbers you won't be able to complete any transaction. To fix this, change the order number series in the opencart order table in your database.

#### My payment plan method is not displaying any campaigns, what can I do?
First make sure that you have a cart filled with items of a value that fits in one of the campaigns, if that doesn't solve it follow these steps:
* Navigate to Extensions/Payments
* Find Svea payment plan and press Edit
* Scroll down to the bottom find the _Min. amount_ box
Is the amount too high? In that case, lower it and press Save

Pressing save will send a request to our servers which will update all your campaigns.

#### My checkout page shows "Thank you for your purchase" instead of the checkout
If you see the "Thank you for your purchase" instead of the checkout iframe, the order number that OpenCart sends to Sveas server is already used with the merchantId that you have.

To fix this you have to go in to your database and to the table oc_order and find the latest order. Change the order_id on that order to a much higher number, this will almost always ensure that the range isn't used.


## 2.3 Product Price Widget <a name="i2-3"></a>

Enabling the product price widget on either Svea Checkout, Svea Invoice or Svea Part payment will result in a "price box" appearing on the product page.

Only applicable in Sweden, Norway, Finland.

Note! Requires [vQmod](https://github.com/vqmod).

Example:

![Product price widget](docs/image/Widget.png "Product price widget")

## 2.4 Important information <a name="i2-4"></a>

The request made from the card and direct payment modules to Svea's systems are made through a redirected form.
The response of the payment is then returned to the module via POST or GET (selectable in the corresponding Svea admin interface).

#### When using GET response
Keep in mind that a long response string sent via GET could get cut off in some browsers and especially in some servers due to server limitations.
Our recommendation to solve this is to check the PHP configuration of the server and set it to accept at LEAST 1024 characters.

#### When using POST response
As our servers are using SSL-certificates and when using POST to get the response from a payment the users browser prompts the user with a question whether to continue or not, if the receiving site does not have a certificate.
Would the customer then click cancel, the process does not continue.  This does not occur if your server holds a certificate. To solve this we recommend that you purchase a SSL-certificate from your hosting provider or get one for free through Let's Encrypt.