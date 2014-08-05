<?php
// Heading
$_['heading_title']           = 'Svea Invoice';

// Text
$_['text_payment']            = 'Payment';
$_['text_success']            = 'Success: You have modified Svea Invoice payment module!';
$_['text_svea_invoice']       = '<img src="view/image/payment/english/svea_invoice.png" alt="Svea Invoice" title="SVEA Invoice" />';

// Entry
$_['entry_order_status'] = 'Orderstatus:';
$_['entry_status_order'] = 'Created:';
$_['entry_status_canceled'] = 'Cancelled/Annulled:';
$_['entry_status_canceled_text'] = 'Applies on orders not yet delivered/confirmed.';
$_['entry_status_delivered'] = 'Delivered:';
$_['entry_status_delivered_text'] = 'Delivers a created order.';
$_['entry_status_refunded'] = 'Credited:';
$_['entry_status_refunded_text'] = 'Applies on orders delivered/captured.';


$_['entry_order_status']      = 'Order Status:';
$_['entry_order_status_text'] = 'Orderstatus for created order but not delivered. Deliver the invoice from Svea admin.';
$_['entry_geo_zone']          = 'Geo Zone:';
$_['entry_status']            = 'Status:';
$_['entry_sort_order']        = 'Sort Order:';

$_['entry_shipping_billing']   = 'Shipping same as billing:';
$_['entry_shipping_billing_text']   = 'On get address in checkout we always overwrite the billingaddress, this setting also overwrites shipping address. Important! This should be set to yes if your contract with Svea does not tell otherwise.:';

$_['entry_username']          = 'Username:';
$_['entry_password']          = 'Password:';
$_['entry_clientno']          = 'Client No:';
$_['entry_min_amount']   = 'Product´s min.price:';

$_['entry_yes']               = 'yes';
$_['entry_no']                = 'no';
$_['entry_testmode']          = 'Testmode:';

$_['entry_auto_deliver']      = 'Auto deliver order:';
$_['entry_auto_deliver_text'] = 'If enabled the invoice will automatically be delivered when creating an order. If disabled, deliver the invoice from Svea admin.';
$_['entry_distribution_type'] = 'Invoice distribution type (As agreed with Svea):';
$_['entry_post'] = 'Post';
$_['entry_email'] = 'Email';
$_['entry_product_text'] = 'Minimum amount to pay. Show on product display';
$_['entry_product'] = 'Product Price Widget:';

// Error
$_['error_permission']        = 'Warning: You do not have permission to modify Svea Invoice payment module!';
?>