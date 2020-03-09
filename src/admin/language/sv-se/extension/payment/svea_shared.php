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

// Error
$_['error_permission']   = 'Varning: Du har inte tillåtelse att ändra Sveas betalmodul.';