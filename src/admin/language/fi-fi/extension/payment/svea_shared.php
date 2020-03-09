<?php

// Translations shared by two or more payment methods

// Text
$_['text_payment']       = 'Maksu';
$_['text_success']       = 'Muutokset Svean moduliin onnistuivat!';

// General
$_['entry_geo_zone']          = 'Maa:';
$_['entry_status']            = 'Tila:';
$_['entry_sort_order']        = 'Lajittelujärjestys:';
$_['entry_payment_description']   = 'Kuvaus kassalle:';
$_['entry_shipping_billing']   = 'Shipping same as billing:';
$_['entry_shipping_billing_text']   = 'On get address in checkout we always overwrite the billingaddress, this setting also overwrites shipping address. Important! This should be set to yes if your contract with Svea does not tell otherwise.:';
$_['entry_version_text']           = 'Moduuli versio';
$_['entry_version']           = getModuleVersion();
if(getNewVersionAvailable())
{
    $_['entry_version_info']      = 'Uusi versio on saatavilla. Lataa se tästä.';
}
else
{
    $_['entry_version_info']      = 'Käytössäsi on viimeisin versio. Siirry dokumentaatioon.';
}
$_['entry_module_repo'] = 'https://github.com/sveawebpay/opencart-module/';

// Authentication
$_['entry_merchant_id']  = 'Kaupan id';
$_['entry_testmode']     = 'Testitila';
$_['entry_sw']           = 'Salasana';
$_['entry_username']          = 'Käyttäjätunnus:';
$_['entry_password']          = 'Salasana:';
$_['entry_clientno']          = 'Asiakasnro:';

$_['entry_min_amount']   = 'Tuotteen vähimmäishinta';
$_['entry_auto_deliver'] = 'Automaattinen toimitus:';

$_['entry_yes']               = 'Kyllä';
$_['entry_no']                = 'Ei';

$_['entry_product'] = 'Product Price Widget:';
$_['entry_post'] = 'Posti';
$_['entry_email'] = 'Sähköposti';
$_['entry_distribution_type'] = 'Laskun jakelutapa (Svean kanssa sovitun mukaan):';

// Error
$_['error_permission']   = 'Varoitus: Sinulla ei ole oikeuksia muuttaa Svea!';