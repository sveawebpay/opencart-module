<?php
// Text
$_['text_title'] = '<img src="admin/view/image/payment/svea_invoice.png" alt="Svea Invoice" title="SVEA Invoice" />';
$_['text_ssn'] = 'Social Securitynumber';

//Error responses
$_['response_CusomterCreditRejected']   = 'Cannot get credit rating information';
$_['response_CustomerOverCreditLimit']   = 'Store or Sveas credit limit overused';
$_['response_CustomerAbuseBlock']   = 'This customer is blocked or has shown strange/unusual behavior';
$_['response_OrderExpired']   = 'The order is too old and can no longer be invoiced against';
$_['response_ClientOverCreditLimit']   = 'The order would cause the client to exceed Sveas credit limit';
$_['response_OrderOverSveaLimit']   = 'The order exceeds the highest order amount permitted at Svea';
$_['response_OrderOverClientLimit']   = 'The order exceeds your highest order amount permitted';
$_['response_CustomerSveaRejected']   = 'The customer has a poor credit history at Svea';
$_['response_CustomerCreditNoSuchEntity']   = 'The customer is not listed with the credit limit supplier';



$_['response_20000'] = "Order closed";
$_['response_20001'] = "Order denied";
$_['response_20002'] = "Something wrong with order";
$_['response_20003'] = "Order expired";
$_['response_20004'] = "Order does not exist";
$_['response_20005'] = "OrderType mismatch";
$_['response_20006'] = "The sum of all order rows cannot be zero or negative";
$_['response_20013'] = "Order is pending";

$_['response_24000'] = "Invoice amount exceeds the authorized amount";

$_['response_30000'] = "The credit report was rejected";
$_['response_30001'] = "The customer is blocked or has shown strange or unusual behavior";
$_['response_30002'] = "Based upon the performed credit check the request was rejected";
$_['response_30003'] = "Customer cannot be found by credit check";

$_['response_40000'] = "No customer found";
$_['response_40001'] = "The provided CountryCode is not supported";
$_['response_40002'] = "Invalid Customer information";
$_['response_40004'] = "Could not find any addresses for this customer";

$_['response_50000'] = "Client is not authorized for this method";
$_['response_error'] = "Error: ";

//Order definitions
$_['text_svea_fee']   = 'SVEA Invoice fee';
$_['unit']            = 'pcs';
?>