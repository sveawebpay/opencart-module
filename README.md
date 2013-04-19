# Opencart - Svea WebPay payment module 
##Work in Progress! Development branch.
###Do not copy this branch. The latest version is on the master branch.


#Installation instructions

##Basic installation example using SVEA Invoice payment

* Copy the contents of the src folder to your opencart root folder
* Log in to your admin panel
* Browse to _extensions -> payments_ where SVEAs payment methods should appear in the list
* Click the _install_ link of the payment method you want to install, for this example we will install SVEA Invoice
* Select _edit_ next to the installed payment method
* Fill out the required fields for the desired countries
 * _Username, Password_ and _client no_ for _Invoice_ and _Part Payment_
 * _Secret word_ and _merchant id_ for _Card_ and _Direct bank payment_
* Set _testmode -> enabled_ and _status -> enabled_
* Set  _order status_ to the desired status e.g _Complete_
* Click _Save_

Finished!

##Requirements and Additional installation info

* For use in Dutch and German stores the postal code needs to be set to required for customer registrations
* For use with coupons, the coupon module sort order must be: 
 * After taxes, if fixed amount
 * Before taxes, if percentage
* For use with voucher, the voucher module sort order must be after taxes
* Currency decimals must be set to 2 when using Euro currency


##Extended functionality

###Set up auto deliver
* Browse to _extensions -> payments -> Svea payment method (only Invoice or Part Payment)_
* Set the _auto deliver_ to _enabled_
* Set the _order status_
* If you have an agreement with Svea emailing of invoices/part payment agreements, you can also set the _distribution type -> Email_

###Set up Svea handling fee (for SVEA Invoice)

####Installation and use
* Browse to _extensions -> order totals_
* Click the _install_ button next to _Svea handling fee_
* Select _edit_ next to _Svea handling fee_
* Set the _Order total, Fee, tax class and sort order_
* Set the _status_ to _enabled_

####Additional info and requirements
* The handling fee sort order must be set to before taxes
* The price must be set in the standard currency if mulitple currencies are used
* The price must be set excluding taxes
* Order total must be set to a high number, 99999999 is recommended

##Recommendations
Always check that you have set up your settings correctly before posting issues or contacting Svea support

Please verify that:
* Your _username, password, client no_ for Invoice and Part Payment are correct
* Your _secret word_ and _merchant id_ for Card and Direct bank payments are correct and that the test-secret word and production-secret word are in their right places
* Your tax, currency and geo settings are correct
* You are using correct test case credentials when conducting test purchases
