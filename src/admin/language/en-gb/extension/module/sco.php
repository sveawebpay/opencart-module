<?php
// Heading
$_['heading_title']				    = 'Svea Checkout';
$_['text_extension']                = 'Extension';

// Misc
$_['text_success']				    = 'Success: You have modified the Svea Checkout module!';
$_['text_edit']					    = 'Edit Svea Checkout';

// Tabs
$_['tab_general']				    = 'General';
$_['tab_authorization']			    = 'Authorization';
$_['tab_checkout_page_settings']    = 'Checkout page-settings';
$_['tab_iframe_settings']           = 'Iframe-settings';

// General
$_['text_module_version']           = 'Module version';
$_['text_module_version_info_new']  = 'There is a new version available. Click to download.';
$_['text_module_version_info']      = 'You have the latest version of the module. Click here to go to the documentation.';
$_['entry_status']				    = 'Status';
$_['entry_status_tooltip']          = 'Enable/Disable Svea Checkout';
$_['text_show_widget_on_product_page'] = 'Show product price widget on product page';
$_['text_show_widget_on_product_page_tooltip'] = 'The lowest price of the campaign available for part payment will be displayed on the product page. Using this option require you to have VQMod installed.';

// Authorization
$_['entry_checkout_default_country']= 'Default checkout country';
$_['entry_checkout_default_country_tooltip']   = 'If a customer selects a country which is not one of the ones below then this checkout will be loaded';
$_['entry_test_mode']			    = 'Test mode';
$_['entry_test_mode_tooltip']       = 'If enabled the test environment will be used instead of the production environment.';

$_['entry_sweden']                  = 'Sweden';
$_['entry_norway']                  = 'Norway';
$_['entry_finland']                 = 'Finland';
$_['entry_denmark']                 = 'Denmark';

$_['entry_stage_environment']       = 'Stage/test credentials:';
$_['entry_prod_environment']        = 'Live/Production credentials:';
$_['entry_checkout_merchant_id']    = 'Merchant Id:';
$_['entry_checkout_secret']		    = 'Secret Word:';

// Checkout page settings
$_['entry_status_checkout']		    = 'Show option to go to default checkout on checkout page';
$_['entry_status_checkout_tooltip'] = 'If enabled, there will be a link on the checkout page which takes the customer to the default Opencart checkout. This can be used if you have more payment methods than SCO';
$_['text_show_voucher_on_checkout']	= 'Show Voucher on Checkout page';
$_['text_show_voucher_on_checkout_tooltip']	= 'If set to \'Yes\', customers will be able to enter vouchers on the checkout page';
$_['text_show_coupons_on_checkout']	= 'Show Coupon on Checkout page';
$_['text_show_coupons_on_checkout_tooltip']	= 'If set to \'Yes\', customers will be able to enter coupons on the checkout page';
$_['text_show_order_comment_on_checkout']   = 'Show Message on Checkout page';
$_['text_show_order_comment_on_checkout_tooltip']   = 'If set to \'Yes\', customers will be able to enter their own messages on their orders on the checkout page';
$_['text_gather_newsletter_consent'] = 'Gather newsletter consent';
$_['text_gather_newsletter_consent_tooltip'] = 'If enabled a checkbox with the text \'Subscribe to newsletter?\' will appear on the checkout page. If the user clicks the box, we will gather their consent in the database which can then be used to import email-addresses into newsletter modules.';
$_['text_download_newsletter_list'] = 'Download newsletter list';
$_['text_newsletter_consent_list'] = 'Newsletter consent list';
$_['text_close'] = 'Close';
$_['text_copy_all_to_clipboard'] = 'Copy all to clipboard';
$_['text_error_fetching_newsletter_consent_list'] = 'Database query returned no result, this might be because no one has subscribed to the newsletter';

// Iframe settings
$_['entry_shop_terms_uri']          = 'Shop terms';
$_['entry_shop_terms_uri_tooltip']  = 'Link to your shops terms & conditions, the link is sent to Svea and displayed at the bottom of the iframe. If the field is empty, the module will fetch the default terms & conditions page.';
$_['text_iframe_hide_not_you']	    = 'Hide "Not you?"';
$_['text_iframe_hide_not_you_tooltip']      = 'If set to \'Yes\' the \'Not you?\' option in the iframe will be hidden.';
$_['text_iframe_hide_anonymous']	= 'Hide anonymous flow';
$_['text_iframe_hide_anonymous_tooltip']    = 'If set to \'Yes\' the anonymous flow in the iframe will be hidden.';
$_['text_iframe_hide_change_address'] = 'Hide "Change address"-option';
$_['text_iframe_hide_change_address_tooltip'] = 'If set to \'Yes\' the customer won\'t be able to change their address in the iframe.';
$_['text_force_flow'] = 'Force B2B or B2C flow';
$_['text_force_flow_tooltip'] = 'If enabled the B2B or B2C flow will be forced. If B2B flow is forced only company customers will be able to finalize purchases and vice-versa.';

// Error
$_['error_permission']			    = 'Warning: You do not have permission to modify the Svea Checkout module!';
$_['error_authorization_data']      = 'Warning: To enable this module you need to add a Checkout merchant id and a Checkout Secret word in the authorization section!';