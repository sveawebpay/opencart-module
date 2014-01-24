# OpenCart - Svea payment module
## Version 2.1.6
* Supports OpenCart 1.4.9+
* Requires PHP 5.3 or higher (namespace support)

This module supports Svea invoice and payment plan payments in Sweden, Finland, Norway, Denmark, Netherlands and Germany, as well as creditcard and direct bank payments from all countries.

The module has been tested with OpenCart and any pre-installed checkout, coupon, voucher and shipping modules, including the Svea invoice fee module. The module has been updated to make use of the latest payment systems at Svea, and builds upon the included Svea php integration package.

**NOTE**: If you are upgrading from the previous version 1.x of this module, please contact Svea support before installing the module, your account settings may require updating.

As always, we strongly recommend that you have a test environment set up, and make a backup of your existing site, database and settings before upgrading.

If you experience technical issues with this module, or if you have feature suggestions, please submit an issue on the Github issue list.

For release history, see [**github release tags**](https://github.com/sveawebpay/opencart-module/releases)

#Installation instructions

##Basic installation example using the Svea Invoice payment method

The following example assumes that you have already downloaded and installed
OpenCart as described in the [OpenCart documentation](http://docs.opencart.com/display/opencart/Installation#Installation-InstallingOpenCart).

This guide covers how to install the SveaWebPay OpenCart module and various payment methods in your OpenCart shop, as well as the various localisation settings you need to make to
ensure that the module works properly.

### Install the OpenCart SveaWebPay module files

* Download or clone the contents of [this repository from github](https://github.com/sveawebpay/opencart-module).
* Copy the contents of the src folder to your OpenCart root folder.
* Make sure to merge the files and folders from the module with the ones in your OpenCart installation (no files should be overwritten).

### Configure the payment module(s) in the OpenCart admin panel
In this example we'll first configure the Svea invoice payment method, instructions for other payment methods follow below.

![Svea payment modules] (https://github.com/sveawebpay/opencart-module/raw/develop/docs/image/Modules.PNG "Svea payment modules")

#### Svea invoice configuration

* Log in to your OpenCart admin panel.
* Browse to _extensions -> payments_ where the various Svea payment methods should appear in the list.
* Click the _install_ link of the payment method you want to install. For now, select install the Svea Invoice payment method.
* Then select _edit_ next to the now installed payment method. You will now see a view with various payment method settings.
* Set _geo zone_ and _sort order_ according to your preferences.
* Set the fields _status_ and _testmode_ to enabled_.
* Also set _order status_ and _auto deliver order_ according to your preferences.
* Fill out the required fields _username, password_ and _client no_. In an production environment, use your Svea account credentials for the desired locales and payment methods. For testing purposes, make sure to use the supplied test account credentials.
* Finally, remember to _save_ your settings.

![Invoice payment settings] (https://github.com/sveawebpay/opencart-module/raw/develop/docs/image/Invoice.PNG "Invoice settings")

#### Next we set up the Svea handling fee (used by Svea Invoice payment method )

* Browse to _extensions -> order totals_.
* Locate _Svea handling fee_ in the list, choose _install_ and then _edit_:
* Set the _order total_ field to a large number, 99999999 is recommended.
* Set the _fee_ to the amount charged.
* Set the corresponding _tax class_.
* Also, set the _status_ field to _enabled_.
* Finally, the _sort order_ field must be set to apply before taxes.

![Invoice fee additional settings] (https://github.com/sveawebpay/opencart-module/raw/develop/docs/image/InvoiceFee.PNG "Invoice fee additional settings")

See also "Localisation and additional OpenCart configuration requirements" below.

### Other payment methods
For the other Svea payment methods (payment plan, card payment and direct bank payment), see below.

#### Svea payment plan configuration

* In OpenCart admin panel, browse to _extensions -> payments_.
* Locate _Svea part payment_ in the list, choose _install_ and then _edit_:
* Set _geo zone_ and _sort order_ according to your preferences.
* Set _testmode_ and _status_ to enabled.
* Also set _order status_ and _auto deliver order_ according to your preferences.
* Fill out the required fields _username, password_ and _client no_. In an production environment, use your Svea account credentials for the desired locales and payment methods. For testing, make sure to use the supplied test account credentials.
* The field _min. amount_ must match the corresponding setting in Svea admin.

#### Svea Card Payment
Module supports one Svea merchant id per Opencart installation.
* In OpenCart admin panel, browse to _extensions -> payments_.
* Locate _Svea card payment_ in the list, choose _install_ and then _edit_:
* Set _order status_ according to your preferences.
* Set _geo zone_ and _sort order_ according to your preferences.
* Set _testmode_ and _status_ to enabled.
* Fill out the required fields _merchant id_ and _secret word_. There are tabs for each _test_ and _prod_. For _prod_, use your Svea account credentials. For _test_, make sure to use the supplied test account credentials.

![Card payment settings] (https://github.com/sveawebpay/opencart-module/raw/develop/docs/image/CardPayment.PNG "Invoice fee additional settings")

#### Svea Direct Payment
Module supports one Svea merchant id per Opencart installation.
* In OpenCart admin panel, browse to _extensions -> payments_.
* Locate _Svea card payment_ in the list, choose _install_ and then _edit_:
* Set _order status_ according to your preferences.
* Set _geo zone_ and _sort order_ according to your preferences.
* Set _testmode_ and _status_ to enabled.
* Fill out the required fields _merchant id_ and _secret word_. There are tabs for each _test_ and _prod_. For _prod_, use your Svea account credentials. For _test_, make sure to use the supplied test account credentials.

##Localisation and additional OpenCart configuration requirements

* The product price must be given in the default currency if multiple currencies are used
* Also, price must be given excluding any taxes
* Currency decimals must be set to two (2) when using Euro currency

* For use in Dutch and German stores the postal code needs to be set to required for customer registrations

* For use with vouchers, the voucher module sort order must be after taxes
* For coupons, the coupon module sort order (in admin, under extensions/order totals) must be set to have a lower sort order than taxes.
* Also, the coupon discount amount (in admin, under sales/coupon) must be specified excluding tax. The coupon tax discount will then be calculated in accordance with OpenCart standard behaviour, and is specified in the order history.
* The recommended order total sort order is: sub-total (lowest), svea invoice fee, shipping, coupon, taxes, store credit, voucher and total.

##Extended functionality

###Auto deliver option

Set up to auto deliver the order. If not set this can be done from Sveas admin panel.

* Browse to _extensions -> payments -> Svea payment method_ (applies only to the Invoice or Payment plan payment methods)
* Set _auto deliver_ to _enabled_
* Set the _order status_
* If you have an agreement with Svea to deliver the invoice as email, you can also set the _distribution type -> Email_


##Troubleshooting and recommendations

Always check that you have set up your settings correctly before posting issues or contacting Svea support. Specifically, the following settings must all be in place for the payment module to work correctly:

### Check your Svea customer credentials

* Your _username, password, client no_ for Invoice and Part Payment are correct.
* Your _secret word_ and _merchant id_ for Card and Direct bank payments are correct and that the test-secret word and production-secret word are in their right places.

### Check correlated OpenCart settings and localisations

* Under _system -> localisation_, the correlating _tax class, tax rate_ (including customer groups), _currency_ and _geo zone_ settings are correct.
* Under _extensions -> order totals_, the sort order et al are correct.
* You are using correct test case credentials when conducting test purchases.

### Specific payment method problems FAQ

#### My card or direct payments don't go through after reinstalling my shop?
Check that you don't attempt to reuse order numbers, they need to be unique. I.e. in the call to

```$form = WebPay::createOrder()-> ... ->setClientOrderNumber("33")-> ... ```

the order number "33" can't have been used in a previous order.

##Important info
The request made from the card and direct payment modules to Sveas systems is made through a redirected form.
The response of the payment is then returned to the module via POST or GET (selectable in the corresponding Svea admin interface).

###When using GET response
Have in mind that a long response string sent via GET could get cut off in some browsers and especially in some servers due to server limitations.
Our recommendation to solve this is to check the PHP configuration of the server and set it to accept at LEAST 512 characters.

###When using POST response
As our servers are using SSL certificates and when using POST to get the response from a payment the users browser propmts the user with a question whether to continue or not, if the receiving site does not have a certificate.
Would the customer then click cancel, the process does not continue.  This does not occur if your server holds a certicifate. To solve this we recommend that you purchase a SSL certificate from your provider.

We can recommend the following certificate providers:
* InfraSec:  infrasec.se
* VeriSign : verisign.com