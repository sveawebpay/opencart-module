<?php
// Heading
$_['heading_title']				    = 'Svea Checkout';
$_['text_extension']                = 'Extension';

// Misc
$_['text_success']				    = 'Modifiering av Svea Checkoutmodulen lyckades!';
$_['text_edit']					    = 'Redigera Svea Checkout.';

// Tabs
$_['tab_general']				    = 'Övrigt';
$_['tab_authorization']			    = 'Auktorisation';
$_['tab_checkout_page_settings']    = 'Checkout-sidinställningar';
$_['tab_iframe_settings']           = 'Iframe-inställningar';

// General
$_['entry_status']				    = 'Status';
$_['entry_status_tooltip']		    = 'Aktivera/Inaktivera Svea Checkout';
$_['text_show_widget_on_product_page'] = 'Visa delbetalningswidget';
$_['text_show_widget_on_product_page_tooltip'] = 'Visar ut det lägsta delbetalningspriset för delbetalningskampanjer som passar produktpriset. Om du vill använda denna funktion måste du ha VQMod installerat.';

// Authorization
$_['entry_checkout_default_country']= 'Standard checkout-land';
$_['entry_checkout_default_country_tooltip']    = 'Om en kund väljer ett land som inte är en av nedan så kommer denna checkout laddas';
$_['entry_test_mode']			    = 'Testläge';
$_['entry_test_mode_tooltip']	    = 'Om denna inställning är aktiverad så kommer testmiljön användas istället för produktionsmiljön';

$_['entry_sweden']                  = 'Sverige';
$_['entry_norway']                  = 'Norge';
$_['entry_finland']                 = 'Finland';
$_['entry_denmark']                 = 'Danmark';

$_['entry_stage_environment']       = 'Testmiljö';
$_['entry_prod_environment']        = 'Produktionsmiljö';
$_['entry_checkout_merchant_id']    = 'Checkout Merchant Id';
$_['entry_checkout_secret']		    = 'Checkout Secret word';

// Checkout page settings
$_['entry_status_checkout']		    = 'Visa alternativ för att gå till standardkassan på checkoutsidan';
$_['entry_status_checkout_tooltip']	= 'Om denna inställning är aktiverad så kommer det att finnas en länk till Opencarts-standardkassa på checkoutsidan, använd denna funktion om du har fler betalmetoder än SCO';
$_['text_show_voucher_on_checkout']	= 'Visa Presentkort på checkoutsidan';
$_['text_show_voucher_on_checkout_tooltip']	= 'Om satt till \'Ja\' så kommer kunder att kunna lägga till presentkort på sina ordrar';
$_['text_show_coupons_on_checkout']	= 'Visa Rabattkod på checkoutsidan';
$_['text_show_voucher_on_checkout_tooltip']	= 'Om satt till \'Ja\' så kommer kunder att kunna lägga till rabattkoder på sina ordrar';
$_['text_show_order_comment_on_checkout']   = 'Visa Meddelande på checkoutsidan';
$_['text_show_order_comment_on_checkout_tooltip']	= 'Om satt till \'Ja\' så kommer kunder att kunna lägga till kommentarer på sina ordrar';

// Iframe settings
$_['entry_shop_terms_uri']          = 'Butikens köpvillkor';
$_['entry_shop_terms_uri_tooltip']  = 'Länk till dina butiksvillkor, länken skickas till Svea och visas i botten av iframen. Om fältet är tomt så kommer modulen att försöka hämta butiksvillkoren automatiskt';
$_['text_iframe_hide_not_you']	    = 'Göm "Inte du?"';
$_['text_iframe_hide_not_you_tooltip']      = 'Om denna är satt till \'Ja\' så kommer \'Inte du?\'-alternativet i iframen att döljas.';
$_['text_iframe_hide_anonymous']	= 'Hide anonymous flow';
$_['text_iframe_hide_anonymous_tooltip']    = 'Om denna är satt till \'Ja\' så kommer anonymaflödet i iframen att döljas.';
$_['text_iframe_hide_change_address'] = 'Hide "Change address"-option';
$_['text_iframe_hide_change_address_tooltip'] = 'Om denna är satt till \'Ja\' så kommer kunder inte kunna byta adress i iframen.';

// Error
$_['error_permission']			    = 'Varning: Du har inte rättigheter att redigera Svea checkoutmodulen.';
$_['error_authorization_data']      = 'Varning! För att aktivera den här modulen behöver du fylla i ett Checkout merchant id och ett Checkout Secret word i på fliken för identifiering.';