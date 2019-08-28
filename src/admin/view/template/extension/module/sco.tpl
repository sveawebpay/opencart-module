<?php echo $header; ?>
<?php echo $column_left; ?>

<div id="content">

    <div class="page-header">
        <div class="container-fluid">
            <div class="pull-right">
                <button type="submit" form="form-filter" data-toggle="tooltip" title="<?php echo $button_save; ?>"
                        class="btn btn-primary"><i class="fa fa-save"></i></button>
                <a href="<?php echo $cancel; ?>" data-toggle="tooltip" title="<?php echo $button_cancel; ?>"
                   class="btn btn-default"><i class="fa fa-reply"></i></a>
            </div>

            <h1><?php echo $heading_title; ?></h1>

            <ul class="breadcrumb">
                <?php foreach ($breadcrumbs as $breadcrumb) { ?>
                <li><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a></li>
                <?php } ?>
            </ul>
        </div>
    </div>

    <div class="container-fluid">

        <?php if ($error_warning) { ?>
        <div class="alert alert-danger">
            <i class="fa fa-exclamation-circle"></i> <?php echo $error_warning; ?>
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
        <?php } ?>

        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title"><i class="fa fa-pencil"></i> <?php echo $text_edit; ?></h3>
            </div>

            <div class="panel-body">
                <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form-filter"
                      class="form-horizontal">

                    <ul class="nav nav-tabs">
                        <li class="active"><a href="#tab-general" data-toggle="tab"><?php echo $tab_general; ?></a></li>
                        <li><a href="#tab-authorization" data-toggle="tab"><?php echo $tab_authorization; ?></a></li>
                        <li><a href="#tab-checkout-page-settings" data-toggle="tab"><?php echo $tab_checkout_page_settings; ?></a></li>
                        <li><a href="#tab-iframe-settings" data-toggle="tab"><?php echo $tab_iframe_settings; ?></a></li>
                    </ul>

                    <div class="tab-content">

                        <!-- GENERAL -->
                        <div class="tab-pane active" id="tab-general">

                            <div class="form-group">
                                <label class="col-sm-2 control-label" for="input-status">Version</label>
                                <div class="col-sm-10" style="padding-top: 9px;">
                                    <div><?php echo $module_version; ?></div>
                                    <a href="<?php echo $module_repo_url; ?>"><?php echo $module_version_info; ?></a>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-2 control-label" for="input-status"><?php echo $entry_status; ?></label>
                                <div class="col-sm-10">
                                    <select name="sco_status" id="input-status" class="form-control">
                                        <?php if ($sco_status) { ?>
                                        <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                                        <option value="0"><?php echo $text_disabled; ?></option>
                                        <?php } else { ?>
                                        <option value="1"><?php echo $text_enabled; ?></option>
                                        <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-2 control-label" for="input-process-status">
                                    <span data-toggle="tooltip" title="<?php echo $entry_sco_show_widget_on_product_page; ?>"><?php echo $entry_sco_show_widget_on_product_page; ?></span>
                                </label>
                                <div class="col-sm-10">
                                    <label class="radio-inline">
                                        <?php if ($sco_show_widget_on_product_page === '1') { ?>
                                        <input type="radio" name="sco_show_widget_on_product_page" value="1" checked="checked" />
                                        <?php echo $text_yes; ?>
                                        <?php } else { ?>
                                        <input type="radio" name="sco_show_widget_on_product_page" value="1" />
                                        <?php echo $text_yes; ?>
                                        <?php } ?>
                                    </label>
                                    <label class="radio-inline">
                                        <?php if ($sco_show_widget_on_product_page !== '1') { ?>
                                        <input type="radio" name="sco_show_widget_on_product_page" value="0" checked="checked" />
                                        <?php echo $text_no; ?>
                                        <?php } else { ?>
                                        <input type="radio" name="sco_show_widget_on_product_page" value="0" />
                                        <?php echo $text_no; ?>
                                        <?php } ?>
                                    </label>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-2 control-label" for="input-process-hide-svea-comments">
                                    <span data-toggle="tooltip" title="<?php echo $entry_sco_hide_svea_comments_tooltip; ?>"><?php echo $entry_sco_hide_svea_comments; ?></span>
                                </label>
                                <div class="col-sm-10">
                                    <label class="radio-inline">
                                        <?php if ($sco_hide_svea_comments === '1') { ?>
                                        <input type="radio" name="sco_hide_svea_comments" value="1" checked="checked" />
                                        <?php echo $text_yes; ?>
                                        <?php } else { ?>
                                        <input type="radio" name="sco_hide_svea_comments" value="1" />
                                        <?php echo $text_yes; ?>
                                        <?php } ?>
                                    </label>
                                    <label class="radio-inline">
                                        <?php if ($sco_hide_svea_comments !== '1') { ?>
                                        <input type="radio" name="sco_hide_svea_comments" value="0" checked="checked" />
                                        <?php echo $text_no; ?>
                                        <?php } else { ?>
                                        <input type="radio" name="sco_hide_svea_comments" value="0" />
                                        <?php echo $text_no; ?>
                                        <?php } ?>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Authorization -->
                        <div class="tab-pane" id="tab-authorization">
                            <div class="form-group">
                                <label class="col-sm-2 control-label">
                                    <span data-toggle="tooltip" title="<?php echo $entry_checkout_default_country_tooltip; ?>"><?php echo $entry_checkout_default_country; ?></span>
                                </label>
                                <div class="col-sm-10">
                                    <select name="sco_checkout_default_country_id" class="form-control">
                                        <?php foreach ($countries as $country) { ?>
                                        <?php if ($country['country_id'] == $sco_checkout_default_country_id) { ?>
                                        <option value="<?php echo $country['country_id']; ?>" selected="selected"><?php echo $country['name']; ?></option>
                                        <?php } else { ?>
                                        <option value="<?php echo $country['country_id']; ?>"><?php echo $country['name']; ?></option>
                                        <?php } ?>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-2 control-label">
                                    <span data-toggle="tooltip" title="<?php echo $entry_test_mode_tooltip; ?>"><?php echo $entry_test_mode; ?></span>
                                </label>
                                <div class="col-sm-10">
                                    <select name="sco_test_mode" id="input-test_mode" class="form-control">
                                        <?php if ($sco_test_mode) { ?>
                                        <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                                        <option value="0"><?php echo $text_disabled; ?></option>
                                        <?php } else { ?>
                                        <option value="1"><?php echo $text_enabled; ?></option>
                                        <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                            <!-- TABS -->
                            <div class="panel-body">
                                    <ul class="nav nav-tabs">
                                        <li class="active"><a href="#merchant_settings_sweden" data-toggle="tab"><?php echo $entry_sweden ?></a></li>
                                        <li><a href="#merchant_settings_norway" data-toggle="tab"><?php echo $entry_norway ?></a></li>
                                        <li><a href="#merchant_settings_finland" data-toggle="tab"><?php echo $entry_finland ?></a></li>
                                        <li><a href="#merchant_settings_denmark" data-toggle="tab"><?php echo $entry_denmark ?></a></li>
                                    </ul>

                                    <!-- tabs content -->
                                    <div class="tab-content">

                                        <!-- Sweden -->
                                        <div class="tab-pane active" id="merchant_settings_sweden">
                                            <div class="form-group">
                                                <label class="col-sm-2 control-label"><?php echo $entry_stage_environment; ?></label>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-sm-2 control-label" for="input-checkout-test-merchant-id-sweden"><?php echo $entry_checkout_merchant_id; ?></label>
                                                <div class="col-sm-10">
                                                    <input type="text" name="sco_checkout_test_merchant_id_se" id="input-checkout-test-merchant-id-sweden" class="form-control"
                                                           value="<?php echo $sco_checkout_test_merchant_id_se; ?>">
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-sm-2 control-label" for="input-checkout-secret-word-sweden"><?php echo $entry_checkout_secret; ?></label>
                                                <div class="col-sm-10">
                                                    <input type="text" name="sco_checkout_test_secret_word_se" id="input-checkout-test-secret-word-sweden" class="form-control"
                                                           value="<?php echo $sco_checkout_test_secret_word_se; ?>">
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-sm-2 control-label"><?php echo $entry_prod_environment; ?></label>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-sm-2 control-label" for="input-checkout-merchant-id-sweden"><?php echo $entry_checkout_merchant_id; ?></label>
                                                <div class="col-sm-10">
                                                    <input type="text" name="sco_checkout_merchant_id_se" id="input-checkout-merchant-id-sweden" class="form-control"
                                                           value="<?php echo $sco_checkout_merchant_id_se; ?>">
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-sm-2 control-label" for="input-checkout-secret-word-sweden"><?php echo $entry_checkout_secret; ?></label>
                                                <div class="col-sm-10">
                                                    <input type="text" name="sco_checkout_secret_word_se" id="input-checkout-secret-word-sweden" class="form-control"
                                                           value="<?php echo $sco_checkout_secret_word_se; ?>">
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Norway -->
                                        <div class="tab-pane" id="merchant_settings_norway">
                                            <div class="form-group">
                                                <label class="col-sm-2 control-label"><?php echo $entry_stage_environment; ?></label>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-sm-2 control-label" for="input-checkout-test-merchant-id-norway"><?php echo $entry_checkout_merchant_id; ?></label>
                                                <div class="col-sm-10">
                                                    <input type="text" name="sco_checkout_test_merchant_id_no" id="input-checkout-test-merchant-id-norway" class="form-control"
                                                           value="<?php echo $sco_checkout_test_merchant_id_no; ?>">
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-sm-2 control-label" for="input-checkout-secret-word-norway"><?php echo $entry_checkout_secret; ?></label>
                                                <div class="col-sm-10">
                                                    <input type="text" name="sco_checkout_test_secret_word_no" id="input-checkout-test-secret-word-norway" class="form-control"
                                                           value="<?php echo $sco_checkout_test_secret_word_no; ?>">
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-sm-2 control-label"><?php echo $entry_prod_environment; ?></label>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-sm-2 control-label" for="input-checkout-merchant-id-norway"><?php echo $entry_checkout_merchant_id; ?></label>
                                                <div class="col-sm-10">
                                                    <input type="text" name="sco_checkout_merchant_id_no" id="input-checkout-merchant-id-norway" class="form-control"
                                                           value="<?php echo $sco_checkout_merchant_id_no; ?>">
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-sm-2 control-label" for="input-checkout-secret-word-norway"><?php echo $entry_checkout_secret; ?></label>
                                                <div class="col-sm-10">
                                                    <input type="text" name="sco_checkout_secret_word_no" id="input-checkout-secret-word-norway" class="form-control"
                                                           value="<?php echo $sco_checkout_secret_word_no; ?>">
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Finland-->
                                        <div class="tab-pane" id="merchant_settings_finland">
                                            <div class="form-group">
                                                <label class="col-sm-2 control-label"><?php echo $entry_stage_environment; ?></label>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-sm-2 control-label" for="input-checkout-test-merchant-id-finland"><?php echo $entry_checkout_merchant_id; ?></label>
                                                <div class="col-sm-10">
                                                    <input type="text" name="sco_checkout_test_merchant_id_fi" id="input-checkout-test-merchant-id-finland" class="form-control"
                                                           value="<?php echo $sco_checkout_test_merchant_id_fi; ?>">
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-sm-2 control-label" for="input-checkout-secret-word-finland"><?php echo $entry_checkout_secret; ?></label>
                                                <div class="col-sm-10">
                                                    <input type="text" name="sco_checkout_test_secret_word_fi" id="input-checkout-test-secret-word-finland" class="form-control"
                                                           value="<?php echo $sco_checkout_test_secret_word_fi; ?>">
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-sm-2 control-label"><?php echo $entry_prod_environment; ?></label>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-sm-2 control-label" for="input-checkout-merchant-id-finland"><?php echo $entry_checkout_merchant_id; ?></label>
                                                <div class="col-sm-10">
                                                    <input type="text" name="sco_checkout_merchant_id_fi" id="input-checkout-merchant-id-finland" class="form-control"
                                                           value="<?php echo $sco_checkout_merchant_id_fi; ?>">
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-sm-2 control-label" for="input-checkout-secret-word-finland"><?php echo $entry_checkout_secret; ?></label>
                                                <div class="col-sm-10">
                                                    <input type="text" name="sco_checkout_secret_word_fi" id="input-checkout-secret-word-finland" class="form-control"
                                                           value="<?php echo $sco_checkout_secret_word_fi; ?>">
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Denmark -->
                                        <div class="tab-pane" id="merchant_settings_denmark">
                                            <div class="form-group">
                                                <label class="col-sm-2 control-label"><?php echo $entry_stage_environment; ?></label>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-sm-2 control-label" for="input-checkout-test-merchant-id-denmark"><?php echo $entry_checkout_merchant_id; ?></label>
                                                <div class="col-sm-10">
                                                    <input type="text" name="sco_checkout_test_merchant_id_dk" id="input-checkout-test-merchant-id-denmark" class="form-control"
                                                           value="<?php echo $sco_checkout_test_merchant_id_dk; ?>">
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-sm-2 control-label" for="input-checkout-test-secret-word-denmark"><?php echo $entry_checkout_secret; ?></label>
                                                <div class="col-sm-10">
                                                    <input type="text" name="sco_checkout_test_secret_word_dk" id="input-checkout-test-secret-word-denmark" class="form-control"
                                                           value="<?php echo $sco_checkout_test_secret_word_dk; ?>">
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-sm-2 control-label"><?php echo $entry_prod_environment; ?></label>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-sm-2 control-label" for="input-checkout-merchant-id-denmark"><?php echo $entry_checkout_merchant_id; ?></label>
                                                <div class="col-sm-10">
                                                    <input type="text" name="sco_checkout_merchant_id_dk" id="input-checkout-merchant-id-denmark" class="form-control"
                                                           value="<?php echo $sco_checkout_merchant_id_dk; ?>">
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-sm-2 control-label" for="input-checkout-secret-word-denmark"><?php echo $entry_checkout_secret; ?></label>
                                                <div class="col-sm-10">
                                                    <input type="text" name="sco_checkout_secret_word_dk" id="input-checkout-secret-word-denmark" class="form-control"
                                                           value="<?php echo $sco_checkout_secret_word_dk; ?>">
                                                </div>
                                            </div>
                                        </div>
                                    </div><!-- end of tabs content -->
                            </div><!-- END OF TABS -->
                        </div><!-- end of authorization -->

                        <!-- Checkout page settings -->
                        <div class="tab-pane" id="tab-checkout-page-settings">
                            <div class="form-group">
                                <label class="col-sm-2 control-label">
                                    <span data-toggle="tooltip" title="<?php echo $entry_status_checkout_tooltip; ?>"><?php echo $entry_status_checkout; ?></span>
                                </label>
                                <div class="col-sm-10">
                                    <select name="sco_status_checkout" id="input-status_checkout" class="form-control">
                                        <?php if ($sco_status_checkout) { ?>
                                        <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                                        <option value="0"><?php echo $text_disabled; ?></option>
                                        <?php } else { ?>
                                        <option value="1"><?php echo $text_enabled; ?></option>
                                        <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group">
                                <?php foreach ($options_on_checkout_page as $option_name => $option_title) { ?>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label" for="input-process-status">
                                        <span data-toggle="tooltip" title="<?php echo ${$option_name . '_tooltip'}; ?>"><?php echo $option_title; ?></span>
                                    </label>
                                    <div class="col-sm-10">
                                        <label class="radio-inline">
                                            <?php if ($$option_name === '1') { ?>
                                            <input type="radio" name="<?php echo $option_name; ?>" value="1" checked="checked" />
                                            <?php echo $text_yes; ?>
                                            <?php } else { ?>
                                            <input type="radio" name="<?php echo $option_name; ?>" value="1" />
                                            <?php echo $text_yes; ?>
                                            <?php } ?>
                                        </label>
                                        <label class="radio-inline">
                                            <?php if ($$option_name !== '1') { ?>
                                            <input type="radio" name="<?php echo $option_name; ?>" value="0" checked="checked" />
                                            <?php echo $text_no; ?>
                                            <?php } else { ?>
                                            <input type="radio" name="<?php echo $option_name; ?>" value="0" />
                                            <?php echo $text_no; ?>
                                            <?php } ?>
                                        </label>
                                    </div>
                                </div>
                                <?php } ?>
                            </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label" for="input-process-force-flow">
                                <span data-toggle="tooltip" title="<?php echo $entry_sco_gather_newsletter_consent_tooltip; ?>"><?php echo $entry_sco_gather_newsletter_consent; ?></span>
                            </label>
                            <div class="col-sm-10">
                                <label class="radio-inline">
                                    <?php if ($sco_gather_newsletter_consent == '1') { ?>
                                    <input type="radio" name="sco_gather_newsletter_consent" value="1" checked="checked" />
                                    <?php echo $text_yes; ?>
                                    <?php } else { ?>
                                    <input type="radio" name="sco_gather_newsletter_consent" value="1" />
                                    <?php echo $text_yes; ?>
                                    <?php } ?>
                                </label>
                                <label class="radio-inline">
                                    <?php if ($sco_gather_newsletter_consent != '1') { ?>
                                    <input type="radio" name="sco_gather_newsletter_consent" value="0" checked="checked" />
                                    <?php echo $text_no; ?>
                                    <?php } else { ?>
                                    <input type="radio" name="sco_gather_newsletter_consent" value="0" />
                                    <?php echo $text_no; ?>
                                    <?php } ?>
                                </label>
                                <button style="display:none; margin-top:10px; margin-left:10px;"type="button" class="btn btn-primary" id="sco-newsletter-button" data-toggle="modal" data-target="#newsletterModal">
                                    <?php echo $entry_sco_download_newsletter_list ?>
                                </button>
                            </div>
                        </div>
                        </div>
                        <!-- Iframe settings -->
                        <div class="tab-pane" id="tab-iframe-settings">
                            <div class="form-group">
                                <label class="col-sm-2 control-label" for="input-checkout-terms-uri">
                                    <span data-toggle="tooltip" title="<?php echo $entry_shop_terms_uri_tooltip; ?>"><?php echo $entry_shop_terms_uri; ?></span>
                                </label>
                                <div class="col-sm-10">
                                    <div class="input-group">
                                        <span class="input-group-addon"><?php echo $entry_shop_terms_uri_example ?></span>
                                        <input type="text" name="sco_checkout_terms_uri" id="input-checkout-terms-uri" class="form-control"
                                               value="<?php echo $sco_checkout_terms_uri; ?>" placeholder="terms url">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php foreach ($identity_flags as $option_name => $option_title) { ?>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label" for="input-process-status">
                                        <span data-toggle="tooltip" title="<?php echo ${$option_name . '_tooltip'}; ?>"><?php echo $option_title; ?></span>
                                    </label>
                                    <div class="col-sm-10">
                                        <label class="radio-inline">
                                            <?php if ($$option_name === '1') { ?>
                                            <input type="radio" name="<?php echo $option_name; ?>" value="1" checked="checked" />
                                            <?php echo $text_yes; ?>
                                            <?php } else { ?>
                                            <input type="radio" name="<?php echo $option_name; ?>" value="1" />
                                            <?php echo $text_yes; ?>
                                            <?php } ?>
                                        </label>
                                        <label class="radio-inline">
                                            <?php if ($$option_name !== '1') { ?>
                                            <input type="radio" name="<?php echo $option_name; ?>" value="0" checked="checked" />
                                            <?php echo $text_no; ?>
                                            <?php } else { ?>
                                            <input type="radio" name="<?php echo $option_name; ?>" value="0" />
                                            <?php echo $text_no; ?>
                                            <?php } ?>
                                        </label>
                                    </div>
                                </div>
                                <?php } ?>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-2 control-label" for="input-process-force-flow">
                                    <span data-toggle="tooltip" title="<?php echo $entry_sco_force_flow_tooltip; ?>"><?php echo $entry_sco_force_flow; ?></span>
                                </label>
                                <div class="col-sm-10">
                                    <label class="radio-inline">
                                        <?php if ($sco_force_flow == '1') { ?>
                                        <input type="radio" name="sco_force_flow" value="1" checked="checked" />
                                        <?php echo $text_yes; ?>
                                        <?php } else { ?>
                                        <input type="radio" name="sco_force_flow" value="1" />
                                        <?php echo $text_yes; ?>
                                        <?php } ?>
                                    </label>
                                    <label class="radio-inline">
                                        <?php if ($sco_force_flow != '1') { ?>
                                        <input type="radio" name="sco_force_flow" value="0" checked="checked" />
                                        <?php echo $text_no; ?>
                                        <?php } else { ?>
                                        <input type="radio" name="sco_force_flow" value="0" />
                                        <?php echo $text_no; ?>
                                        <?php } ?>
                                    </label>
                                    <br/>
                                    <label class="radio-inline">
                                        <?php if ($sco_force_b2b == '1') { ?>
                                        <input type="radio" name="sco_force_b2b" value="1" checked="checked" />
                                        <?php echo "B2B"; ?>
                                        <?php } else { ?>
                                        <input type="radio" name="sco_force_b2b" value="1" />
                                        <?php echo "B2B"; ?>
                                        <?php } ?>
                                    </label>
                                    <label class="radio-inline">
                                        <?php if ($sco_force_b2b != '1') { ?>
                                        <input type="radio" name="sco_force_b2b" value="0" checked="checked" />
                                        <?php echo "B2C"; ?>
                                        <?php } else { ?>
                                        <input type="radio" name="sco_force_b2b" value="0" />
                                        <?php echo "B2C" ?>
                                        <?php } ?>
                                    </label>
                                </div>
                            </div>
                        </div>
                        </div>
                    </div>
                </form>
            </div>

        </div>

    </div>
</div>
<!-- Newsletter modal -->
<div class="modal fade" id="newsletterModal" tabindex="-1" role="dialog" aria-labelledby="newsletterModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="newsletterModalLabel"><?php echo $entry_sco_newsletter_consent_list; ?></h3>
            </div>
            <div class="modal-body" style="overflow-y:scroll; overflow-x: hidden; max-height:500px;">
                <i class="fa fa-3x fa-spinner fa-spin" id="sco-newsletter-list-loader" aria-hidden="true"></i>
                <span style="display:none;" id="sco-newsletter-list-container"></span>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal"><?php echo $entry_sco_close; ?></button>
                <button type="button" id="sco-copy-to-clipboard" class="btn btn-primary" data-container="body" data-toggle="popover" data-placement="bottom" data-content="Copied to clipboard!" onclick="copyToClipboard()"><?php echo $entry_sco_copy_all_to_clipboard; ?></button>
            </div>
        </div>
    </div>
</div>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        const newsletterButtonEl = $('#sco-newsletter-button');
        const loaderEl = $('#sco-newsletter-list-loader');
        const newsletterListEl = $('#sco-newsletter-list-container');
        const copyToClipboardEl = $('#sco-copy-to-clipboard');

        $('[data-toggle="popover"]').popover();

        newsletterButtonEl.show();

        newsletterButtonEl.on('click', function () {
            $.ajax({
                type: 'GET',
                url: 'index.php?route=extension/module/sco/getNewsletterConsentList&token=<?php echo $token; ?>',
                datatype: 'text',
                success: function (text) {
                    loaderEl.hide();
                    newsletterListEl.show();
                    if(text !== "")
                    {
                        newsletterListEl.html('<pre id="sco-newsletter-list">' + text + '</pre>');
                    }
                    else
                    {
                        let errorMessage = '<?php echo $entry_sco_error_fetching_newsletter_consent_list ?>';
                        newsletterListEl.html('<div class="alert alert-danger" role="alert" style="display: block;">' + errorMessage + '</div>');
                    }
                }
            });
        });
    });
    const copyToClipboard = str => {
        const el = document.createElement('textarea');
        const val = document.createTextNode(document.getElementById('sco-newsletter-list').innerHTML);
        const range = document.createRange();
        el.appendChild(val);
        el.setAttribute('readonly', '');
        el.style.position = 'absolute';
        el.style.left = '-9999px';
        document.body.appendChild(el);
        const selected = document.getSelection().rangeCount > 0 ? document.getSelection().getRangeAt(0) : false;
        range.selectNode(el);
        window.getSelection().addRange(range);
        document.execCommand('copy');
        document.body.removeChild(el);
        if (selected) {
            document.getSelection().removeAllRanges();
            document.getSelection().addRange(selected);
        }
    };
</script>

<?php echo $footer; ?>