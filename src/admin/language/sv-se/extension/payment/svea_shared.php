<?php

// Translations shared by two or more payment methods

// Text
$_['text_payment']       = 'Betalning';
$_['text_success']       = 'Modifiering av Sveas betalmodul lyckades!';

// General
$_['entry_geo_zone']     = 'Geozon:';
$_['entry_status']       = 'Status:';
$_['entry_sort_order']   = 'Sorteringsordning:';
$_['entry_payment_description']   = 'Beskrivning i kassan:';
$_['entry_shipping_billing']   = 'Frakt samma som fakturering:';
$_['entry_shipping_billing_text']   = 'När vi hämtar adress i kassan skriver vi alltid över fakturaadressen. Den här inställningen skriver över även fraktadressen. Viktigt! Ställ in på Ja om det inte står något annat i ert kontrakt med Svea:';
$_['entry_version_text']           = 'Modulversion';
$_['entry_version'] = getModuleVersion();
if(getNewVersionAvailable())
{
    $_['entry_version_info']      = 'En ny version finns tillgänglig. Klicka för nedladdning.';
}
else
{
    $_['entry_version_info']      = 'Du har den senaste versionen. Klicka för att gå till dokumentationen';
}
$_['entry_module_repo'] = 'https://github.com/sveawebpay/opencart-module/';

// Authentication
$_['entry_merchant_id']  = 'Butiks id:';
$_['entry_testmode']     = 'Testläge:';
$_['entry_sw']           = 'Hemligt ord:';
$_['entry_username']     = 'Användarnamn:';
$_['entry_password']     = 'Lösenord:';
$_['entry_clientno']     = 'Klientnr:';

$_['entry_yes']               = 'Ja';
$_['entry_no']                = 'Nej';
$_['entry_distribution_type'] = 'Distributionsform för faktura (Enligt överenskommelse med Svea):';
$_['entry_post'] = 'Post';
$_['entry_email'] = 'Email';
$_['entry_product'] = 'Product Price Widget:';
$_['entry_show_peppol_field'] = 'Visa Peppol-ID fält för företagskunder i kassan:';
$_['entry_auto_deliver'] = 'Leverera automatiskt:';
$_['entry_auto_deliver_text'] = 'Om aktiverad levereras fakturan automatiskt vid skapandet av ordern. Leverans av fakturan görs från Sveas admin.';

// Order statuses
$_['entry_deliver_status']                              = 'Statusar för att leverera ordrar';
$_['entry_deliver_status_tooltip']                      = 'Setting the order status of a order to one of the order statuses in the list will make the module send a deliver order request to Svea. If the previous status was a deliver order status, no request will be sent.';
$_['entry_deliver_status_tooltip']                      = 'Genom att sätta en av de statusar valda i listan så kommer modulen att skicka ett leveransanrop till Svea. Om förgående status också var en leveransstatus så kommer inget anrop skickas till Svea.';
$_['entry_cancel_credit_status']                        = 'Statusar för att makulera/kreditera ordrar';
$_['entry_cancel_credit_status_tooltip']                = 'Genom att sätta en av de statusar valda i listan så kommer modulen att skicka ett makulering/krediteringsanrop(beroende på nuvarande status på ordern) till Svea. Om förgående status också var en makulering/krediteringsstatus så kommer inget anrop skickas till Svea.';

// Error
$_['error_permission']   = 'Fel: Du har inte tillåtelse att ändra Sveas betalmodul.';
$_['error_validation_shared_status']            = 'Fel: Listan av leveransstatusar kan inte ha samma statusar som makulering/krediteringsstatus listan. Vänligen ta bort följande statusar från någon av listorna: ';
$_['error_validation_deliver_status_empty']     = 'Fel: Listan av leveransstatusar får inte vara tom!';
$_['error_validation_cancel_credit_status_empty']     = 'Fel: Listan av makulering/krediteringsstatusar får inte vara tom!';