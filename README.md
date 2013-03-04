# Opencart - Svea WebPay payment module installation guide

Copy all files in the src folder to the root directory for your installation. 
The folders should merge with the folders of the same name.
If you use a customized theme it could be so that he files copied to:
*/catalog/view/default/template/payment* also needs to be copied to 
*/catalog/view/YOUR_THEME/template/payment*

1. Log in the shop administration page
2. Choose *Extensions->Payments*
3. Install the payment methods you wish to use for Svea WebPay by clicking *install*.
4. Choose *Edit* for installed payment methods.
5. Enter your *username*, *password*, *clientnumber* and *invoice fee*
	* For card or directbank payments you should also enter *merchantId* and *Secret word*
6. You can also set *test mode* here.
7. Set status *enabled* and *order status* to *complete*.

###You are now ready to start using Svea WebPay payment module!
	
##Important info
The request made from this module to SVEAs systems is made through a redirected form. 
The response of the payment is then sent back to the module via POST or GET (selectable in our admin).

###When using GET
Have in mind that a long response string sent via GET could get cut off in some browsers and especially in some servers due to server limitations. 
Our recommendation to solve this is to check the PHP configuration of the server and set it to accept at LEAST 512 characters.


###When using POST
As our servers are using SSL certificates and when using POST to get the response from a payment the users browser propmts the user with a question whether to continue or not, if the receiving site does not have a certificate.
Would the customer then click cancel, the process does not continue.  This does not occur if your server holds a certicifate. To solve this we recommend that you purchase a SSL certificate from your provider.

We can recommend the following certificate providers:
* InfraSec:  infrasec.se
* VeriSign : verisign.com
