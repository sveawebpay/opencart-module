<?php
// Text
$_['text_title']               = 'SveaWebPay Partpayment';
$_['text_paymentplan']        = 'Partpayment';
$_['text_ssn']                 = 'Social Security No';
$_['text_get_payment_options'] = 'Get payment options';
$_['text_invoice_address']     = 'Invoice address';
$_['text_shipping_address']    = 'Shipping address';
$_['text_birthdate']           = 'Birth date';
$_['text_vat_no']              = 'VAT no';
$_['text_initials']            = 'Initials';
$_['text_payment_options']     = 'Payment options';
$_['text_get_address']        = 'Get address';
$_['text_from']        			= 'From';

//Error responses
$_['response_20000'] = "Order closed";
$_['response_20001'] = "Order denied";
$_['response_20002'] = "Something wrong with order";
$_['response_20003'] = "Order expired";
$_['response_20004'] = "Order does not exist";
$_['response_20005'] = "OrderType mismatch";
$_['response_20006'] = "The sum of all order rows cannot be zero or negative";
$_['response_20013'] = "Order is pending";

$_['response_27000'] = "The provided campaigncode-amount combination does not match any campaign code attached to this client";
$_['response_27001'] = "Can not deliver order since the specified pdf template is missing. Contact SveaWebPay´s support";
$_['response_27002'] = "Can not partial deliver a PaymentPlan";
$_['response_27003'] = "Can not mix CampaignCode with a fixed Monthly Amount.";
$_['response_27004'] = "Can not find a suitable CampaignCode for the Monthly Amount";

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

$_['unit']           = 'pcs';
$_['month']          = 'month';
$_['initial_fee']    = 'initial fee will be added';
?>