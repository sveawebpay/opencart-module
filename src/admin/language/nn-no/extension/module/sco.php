<?php
// Heading
$_['heading_title']				    = 'Svea Checkout';
$_['text_extension']                = 'Utvidelse';

// Misc
$_['text_success']				    = 'Suksess: Du har endret Svea Checkout modulen!';
$_['text_edit']					    = 'Endre Svea Checkout';

// Tabs
$_['tab_general']				    = 'Vanlig';
$_['tab_authorization']			    = 'Autorisasjon';
$_['tab_checkout_page_settings']    = 'Checkout sideinnstillinger';
$_['tab_iframe_settings']           = 'Iframe-innstillinger';

// General
$_['text_module_version']           = 'Modulversjon';
$_['text_module_version_info_new']  = 'Det er ny versjon tilgjengelig. Klikk for å laste ned.';
$_['text_module_version_info']      = 'Du har siste versjon av modulen. Gå til dokumetasjon.';
$_['entry_status']				    = 'Status';
$_['entry_status_tooltip']		    = 'Aktiver / deaktiver Svea Checkout';
$_['text_show_widget_on_product_page'] = 'Vis produktpris widget';
$_['text_show_widget_on_product_page_tooltip'] = 'Den laveste prisen for kampanjen som er tilgjengelig for delbetaling, vises på produktsiden. Ved bruk av dette alternativet må du ha VQMod installert.';

// Authorization
$_['entry_checkout_default_country']= 'Standard checkout land';
$_['entry_checkout_default_country_text']   = 'Hvis en kunde velger et land som ikke er vist nedenfor, vil denne checkouten bli lastet opp';
$_['entry_test_mode']			    = 'Test modus';
$_['entry_test_mode_tooltip']       = 'Hvis enabled benyttes testmiljø og IKKE produksjonsmiljø';

$_['entry_sweden']                  = 'Sverige';
$_['entry_norway']                  = 'Norge';
$_['entry_finland']                 = 'Finland';
$_['entry_denmark']                 = 'Danmark';

$_['entry_stage_environment']       = 'Stage/Testmiljø';
$_['entry_prod_environment']        = 'Produksjonsmiljø';
$_['entry_checkout_merchant_id']    = 'Checkout Merchant ID';
$_['entry_checkout_secret']		    = 'Checkout hemmelig ord';

// Checkout page settings
$_['entry_status_checkout']		    = 'Vis valg for å gå til standard checkout på checkout siden.';
$_['entry_status_checkout_tooltip'] = 'Hvis Enabled vil linken til standard opencart checkout være synlig. Dette kan benyttes om man har flere betalingsmetoder enn SCO';
$_['text_show_voucher_on_checkout']	= 'Vis gavekupong på Checkout siden';
$_['text_show_voucher_on_checkout_tooltip']	= 'Hvis ‘ja’, vil kunder ha mulighet til å bruke rabattkupong i kassen ';
$_['text_show_coupons_on_checkout']	= 'Vis kupong på Checkout siden';
$_['text_show_coupons_on_checkout_tooltip']	= 'Hvis ‘ja’, vil kunder ha mulighet til å bruke verdikupong i kassen';
$_['text_show_order_comment_on_checkout']   = 'Vis medling på Checkout siden';
$_['text_show_order_comment_on_checkout_tooltip']   = 'Hvis ‘ja’, vil kunder ha mulighet til å legge til notat på ordren i kassen';

// Iframe settings
$_['entry_shop_terms_uri']          = 'Butikkens vilkår';
$_['entry_shop_terms_uri_tooltip']  = 'Link til butikkens kjøpsvilkår. Svea viser linken i kassens ifram. Hvis feltet er tomt, vil modulen hente standard kjøpsvilkår';
$_['text_iframe_hide_not_you']	    = 'Skjul “ikke deg?”';
$_['text_iframe_hide_not_you_tooltip']      = 'Hvis ‘ja’, vil ‘ikke deg’ valget i vår være skjult';
$_['text_iframe_hide_anonymous']	= 'Skjul handle uten fødselsnummer';
$_['text_iframe_hide_anonymous_tooltip']    = 'Hvis ‘ja’ vil handle uten fødselsnummer være skjult';
$_['text_iframe_hide_change_address'] = 'Skjul “endre adresse” valget';
$_['text_iframe_hide_change_address_tooltip'] = 'Hvis ‘ja’, vil kundene ikke ha mulighet til å endre adressen';

// Error
$_['error_permission']			    = 'Warning: Warning: Du har ikke rettighet til å endre Svea Checkout modulen!';
$_['error_authorization_data']      = 'Warning: For å aktivere modulen må du legge til Checkout merchant ID og Checkout hemmelig ord i autorisasjons sekjsonen!';