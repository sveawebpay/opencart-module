<?php

// Translations shared by two or more payment methods

// Text
$_['text_payment']       = 'Betaling';
$_['text_success']       = 'Modifiering av Sveas betalingsmodul var velykket!';

// General
$_['entry_geo_zone']     = 'Geozon:';
$_['entry_status']       = 'Status:';
$_['entry_sort_order']   = 'Sorteringsordning:';
$_['entry_payment_description']   = 'Beskrivelse i kassen:';
$_['entry_shipping_billing']   = 'Shipping same as billing:';
$_['entry_shipping_billing_text']   = 'On get address in checkout we always overwrite the billingaddress, this setting also overwrites shipping address. Important! This should be set to yes if your contract with Svea does not tell otherwise.:';
$_['entry_version_text']           = 'Modulversjon';
$_['entry_version'] = getModuleVersion();
if(getNewVersionAvailable())
{
    $_['entry_version_info']      = 'Det er ny versjon tilgjengelig. Klikk for å laste ned.';
}
else
{
    $_['entry_version_info']      = 'Du har siste versjon av modulen. Gå til dokumetasjon.';
}
$_['entry_module_repo'] = 'https://github.com/sveawebpay/opencart-module/releases';

// Authentication
$_['entry_merchant_id']  = 'Butikk id:';
$_['entry_testmode']     = 'Testmodus:';
$_['entry_sw']           = 'Hemmelig ord:';
$_['entry_username']     = 'Brukernavn:';
$_['entry_password']     = 'Passord:';
$_['entry_clientno']     = 'Klientnr:';

$_['entry_auto_deliver'] = 'Levere automatisk:';
$_['entry_auto_deliver_text'] = 'Om aktivert levereres fakturaen automatiskt ved opprettelse av ordren. Ellers gjøres dette via Sveas admin.';
$_['entry_show_peppol_field'] = 'Vis Peppol-ID-felt for bedriftskunder:';
$_['entry_distribution_type'] = 'Distribusjonsform for faktura (Etter avtale med Svea):';
$_['entry_post'] = 'Post';
$_['entry_email'] = 'Email';
$_['entry_product'] = 'Product Price Widget:';

// Error
$_['error_permission']   = 'Advarsel: Du har ikke tillatelse til å endre Sveas betalingsmodul';