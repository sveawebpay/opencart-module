<?php
// Heading
$_['heading_title']           = 'Svea Faktura';

// Text
$_['text_payment']            = 'Betaling';
$_['text_success']            = 'Modificering af Sveas kortbetalingsmodul lykkedes!';
$_['text_svea_invoice']       = '<img src="view/image/payment/danish/svea_invoice.png" alt="Svea Invoice" title="SVEA Invoice" />';

// Entry
$_['entry_order_status']      = 'Ordrerstatus:';
$_['entry_status_order'] = 'Oprettet:';
$_['entry_status_canceled'] = 'Aflyst/Annulleret:';
$_['entry_status_canceled_text'] = 'Kan tilføres på ordren inden levering/godkend.';
$_['entry_status_delivered'] = 'Leveret:';
$_['entry_status_delivered_text'] = 'Leverer en oprettet ordre.';
$_['entry_status_refunded'] = 'Krediteret:';
$_['entry_status_refunded_text'] = 'Ordren skal være leveret/indløst inden den krediteres.';

$_['entry_geo_zone']          = 'Geo Zone:';
$_['entry_status']            = 'Status:';
$_['entry_sort_order']        = 'Sorteringsorden:';

$_['entry_shipping_billing']   = 'Shipping same as billing:';
$_['entry_shipping_billing_text']   = 'On get address in checkout we always overwrite the billingaddress, this setting also overwrites shipping address. Important! This should be set to yes if your contract with Svea does not tell otherwise.:';

$_['entry_min_amount']   	= 'Produktens min.pris:';

$_['entry_username']          = 'Anvendernavn:';
$_['entry_password']          = 'Password:';
$_['entry_clientno']          = 'Klient Nr:';

$_['entry_yes']               = 'ja';
$_['entry_no']                = 'nej';
$_['entry_testmode']          = 'Testmode:';

$_['entry_auto_deliver']      = 'Levere automatisk:';
$_['entry_auto_deliver_text'] = 'Ved aktivering leveres fakturaen automatisk når ordren afgives. I modsat fald gøres dette via Sveas admin.';
$_['entry_distribution_type'] = 'Distributionsform for faktura (efter aftale med Svea):';
$_['entry_post'] = 'Post';
$_['entry_email'] = 'Email';
$_['entry_product_text'] = 'Mindste beløb til at betale. Se på produktsiden';
$_['entry_product'] = 'Product Price Widget:';

// Error
$_['error_permission']        = 'Advarsel: Du har ikke tilladelse til at modificere Svea Fakturas betalingsmodul!';
?>