<?php
// Heading
$_['heading_title']				    = 'Svea Checkout';
$_['text_extension']                = 'Extension';

// Misc
$_['text_success']				    = 'Onnistui!: Olet muokannut Svea Checkout modulia!';
$_['text_edit']					    = 'Muokkaa Svea Checkoutia';

// Tabs
$_['tab_general']				    = 'Yleiset';
$_['tab_authorization']			    = 'Valtuutus';
$_['tab_checkout_page_settings']    = 'Checkout sivun asetukset';
$_['tab_iframe_settings']           = 'Iframe asetukset';

// General
$_['entry_status']				    = 'Status';
$_['entry_status_tooltip']          = 'Kytke päälle/Kytke pois päältä Svea Checkout';
$_['text_show_widget_on_product_page'] = 'Näytä tuotteen hinta -widget';
$_['text_show_widget_on_product_page_tooltip'] = 'Alin mahdollinen hinta erämaksukampanjalla näytetään tuote-sivulla. Tämä edellyttää että VQMod on asennettu.';

// Authorization
$_['entry_checkout_default_country']= 'Checkoutin oletus maa';
$_['entry_checkout_default_country_text']   = 'Jos asiakas valitsee maan jota ei ole mainittu alla, tämä Checkout esitetään';
$_['entry_test_mode']			    = 'Testiympäristö';
$_['entry_test_mode_tooltip']	    = 'Jos tuotantoympäristö on kytketty pois päältä, käytetään testiympäristö';

$_['entry_sweden']                  = 'Ruotsi';
$_['entry_norway']                  = 'Norja';
$_['entry_finland']                 = 'Suomi';
$_['entry_denmark']                 = 'Tanska';
$_['entry_germany']                 = 'Saksa';

$_['entry_stage_environment']       = 'Testiympäristö';
$_['entry_prod_environment']        = 'Tuotantoympäristö';
$_['entry_checkout_merchant_id']    = 'Kauppiastunnus';
$_['entry_checkout_secret']		    = 'Salasana';

// Checkout page settings
$_['entry_status_checkout']		    = 'Näytä mahdollisuus siirtyä oletusmaksutapoihin maksusivulla.';
$_['entry_status_checkout_tooltip'] = 'Jos kytketty pois päältä, checkout-sivulle tulee linkki, joka vie asiakkaan Opencart Checkoutiin. Tätä voidaan käyttää, jos haluat enemmän maksutapoja SCO:n lisäksi.';
$_['text_show_voucher_on_checkout']	= 'Näytä Voucher Checkout-sivulla';
$_['text_show_voucher_on_checkout_tooltip']	= 'Jos merkitty “kyllä”, asiakas pystyy syöttämään alennuksia Checkout-sivulla.';
$_['text_show_coupons_on_checkout']	= 'Näytä Coupon Checkout-sivulla';
$_['text_show_coupons_on_checkout_tooltip']	= 'Jos merkitty “kyllä”, asiakas pystyy syöttämään kuponkeja Checkout-sivulla.';
$_['text_show_order_comment_on_checkout']   = 'Näytä viesti Checkout-sivulla';
$_['text_show_order_comment_on_checkout_tooltip']   = 'Jos merkitty “kyllä”, asiakkaat pystyvät kirjoittamaan omia viestejä tilauksien yhtyeteen Checkout-sivulla.';

// Iframe settings
$_['entry_shop_terms_uri']          = 'Kaupan ehdot';
$_['entry_shop_terms_uri_tooltip']  = 'Linkki kauppojesi ehtoihin ja edellytyksiin, linkki lähetetään Svealle ja löytyy iframen alareunasta. Jos kohta on tyhjä, moduuli hakee oletusehdot sekä -tilan.';
$_['text_iframe_hide_not_you']	    = 'Piilota ”Etkö ole tämä henkilö?”';
$_['text_iframe_hide_not_you_tooltip']      = 'Jos “Etkö ole tämä henkikö?” kohtaan on laitettu kyllä, valinta piilotetaan iframesta';
$_['text_iframe_hide_anonymous']	= 'Piilota tuntematon virta.';
$_['text_iframe_hide_anonymous_tooltip']    = 'Jos “kyllä”, niin tuntematon virta piilotetaan.';
$_['text_iframe_hide_change_address'] = 'Piilota “muuta osoitetta”-valinta';
$_['text_iframe_hide_change_address_tooltip'] = 'Jos “kyllä”, asiakas ei kykene muuttamaan osoitetta iframessa.';

// Error
$_['error_permission']			    = 'Varoitus: Sinulla ei ole valtuuksia muokata Svea Checkout moduulia!';
$_['error_authorization_data']      = 'Varoitus: Sinun täytyy antaa kauppiastunnus ja salasana muokataksesi tätä moduulia!';
