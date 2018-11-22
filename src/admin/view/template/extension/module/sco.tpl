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
                                <label class="col-sm-2 control-label" for="input-test_mode"><?php echo $entry_test_mode; ?></label>
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

                            <div class="form-group">
                                <label class="col-sm-2 control-label" for="input-status_checkout"><?php echo $entry_status_checkout; ?></label>
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
                                    <label class="col-sm-2 control-label" for="input-process-status"><?php echo $option_title; ?></label>

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
                                <label class="col-sm-2 control-label" for="input-checkout-terms-uri"><?php echo $entry_shop_terms_uri; ?></label>
                                <div class="col-sm-10">
                                    <div class="input-group">
                                        <span class="input-group-addon"><?php echo $entry_shop_terms_uri_example ?></span>
                                        <input type="text" name="sco_checkout_terms_uri" id="input-checkout-terms-uri" class="form-control"
                                               value="<?php echo $sco_checkout_terms_uri; ?>" placeholder="terms url">
                                    </div>
                                </div>
                            </div>
                        </div>


                        <!-- Authorization -->
                        <div class="tab-pane" id="tab-authorization">
                            <div class="form-group">
                                <label class="col-sm-2 control-label">
                                    <span data-toggle="tooltip" title="<?php echo $entry_checkout_default_country_text; ?>"><?php echo $entry_checkout_default_country; ?></span>
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
                            <!-- TABS -->
                            <div class="panel-body">
                                    <ul class="nav nav-tabs">
                                        <li class="active"><a href="#test_merchant_settings" data-toggle="tab"><?php echo $tab_authorization_test ?></a></li>
                                        <li><a href="#prod_merchant_settings" data-toggle="tab"><?php echo $tab_authorization_prod ?></a></li>
                                    </ul>

                                    <!-- tabs content -->
                                    <div class="tab-content">

                                        <!-- Test Authorization -->
                                        <div class="tab-pane active" id="test_merchant_settings">

                                        <div class="form-group">
                                            <label class="col-sm-2 control-label" for="input-checkout-test-merchant-id-sweden"><?php echo $entry_checkout_merchant_id . ' | ' . $entry_sweden; ?></label>
                                            <div class="col-sm-10">
                                                <input type="text" name="sco_checkout_test_merchant_id_se" id="input-checkout-test-merchant-id-sweden" class="form-control"
                                                       value="<?php echo $sco_checkout_test_merchant_id_se; ?>">
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-sm-2 control-label" for="input-checkout-secret-word-sweden"><?php echo $entry_checkout_secret . ' | ' . $entry_sweden;; ?></label>
                                            <div class="col-sm-10">
                                                <input type="text" name="sco_checkout_test_secret_word_se" id="input-checkout-test-secret-word-sweden" class="form-control"
                                                       value="<?php echo $sco_checkout_test_secret_word_se; ?>">
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label class="col-sm-2 control-label" for="input-checkout-test-merchant-id-norway"><?php echo $entry_checkout_merchant_id . ' | ' . $entry_norway; ?></label>
                                            <div class="col-sm-10">
                                                <input type="text" name="sco_checkout_test_merchant_id_no" id="input-checkout-test-merchant-id-norway" class="form-control"
                                                       value="<?php echo $sco_checkout_test_merchant_id_no; ?>">
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-sm-2 control-label" for="input-checkout-secret-word-norway"><?php echo $entry_checkout_secret . ' | ' . $entry_norway; ?></label>
                                            <div class="col-sm-10">
                                                <input type="text" name="sco_checkout_test_secret_word_no" id="input-checkout-test-secret-word-norway" class="form-control"
                                                       value="<?php echo $sco_checkout_test_secret_word_no; ?>">
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label class="col-sm-2 control-label" for="input-checkout-test-merchant-id-finland"><?php echo $entry_checkout_merchant_id . ' | ' . $entry_finland; ?></label>
                                            <div class="col-sm-10">
                                                <input type="text" name="sco_checkout_test_merchant_id_fi" id="input-checkout-test-merchant-id-finland" class="form-control"
                                                       value="<?php echo $sco_checkout_test_merchant_id_fi; ?>">
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-sm-2 control-label" for="input-checkout-secret-word-finland"><?php echo $entry_checkout_secret . ' | ' . $entry_finland; ?></label>
                                            <div class="col-sm-10">
                                                <input type="text" name="sco_checkout_test_secret_word_fi" id="input-checkout-test-secret-word-finland" class="form-control"
                                                       value="<?php echo $sco_checkout_test_secret_word_fi; ?>">
                                            </div>
                                        </div>
                                        </div>

                                        <!-- Prod Authorization -->
                                        <div class="tab-pane" id="prod_merchant_settings">
                                            <div class="form-group">
                                                <label class="col-sm-2 control-label" for="input-checkout-merchant-id-sweden"><?php echo $entry_checkout_merchant_id . ' | ' . $entry_sweden; ?></label>
                                                <div class="col-sm-10">
                                                    <input type="text" name="sco_checkout_merchant_id_se" id="input-checkout-merchant-id-sweden" class="form-control"
                                                           value="<?php echo $sco_checkout_merchant_id_se; ?>">
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-sm-2 control-label" for="input-checkout-secret-word-sweden"><?php echo $entry_checkout_secret . ' | ' . $entry_sweden;; ?></label>
                                                <div class="col-sm-10">
                                                    <input type="text" name="sco_checkout_secret_word_se" id="input-checkout-secret-word-sweden" class="form-control"
                                                           value="<?php echo $sco_checkout_secret_word_se; ?>">
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <label class="col-sm-2 control-label" for="input-checkout-merchant-id-norway"><?php echo $entry_checkout_merchant_id . ' | ' . $entry_norway; ?></label>
                                                <div class="col-sm-10">
                                                    <input type="text" name="sco_checkout_merchant_id_no" id="input-checkout-merchant-id-norway" class="form-control"
                                                           value="<?php echo $sco_checkout_merchant_id_no; ?>">
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-sm-2 control-label" for="input-checkout-secret-word-norway"><?php echo $entry_checkout_secret . ' | ' . $entry_norway; ?></label>
                                                <div class="col-sm-10">
                                                    <input type="text" name="sco_checkout_secret_word_no" id="input-checkout-secret-word-norway" class="form-control"
                                                           value="<?php echo $sco_checkout_secret_word_no; ?>">
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <label class="col-sm-2 control-label" for="input-checkout-merchant-id-finland"><?php echo $entry_checkout_merchant_id . ' | ' . $entry_finland; ?></label>
                                                <div class="col-sm-10">
                                                    <input type="text" name="sco_checkout_merchant_id_fi" id="input-checkout-merchant-id-finland" class="form-control"
                                                           value="<?php echo $sco_checkout_merchant_id_fi; ?>">
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-sm-2 control-label" for="input-checkout-secret-word-finland"><?php echo $entry_checkout_secret . ' | ' . $entry_finland; ?></label>
                                                <div class="col-sm-10">
                                                    <input type="text" name="sco_checkout_secret_word_fi" id="input-checkout-secret-word-finland" class="form-control"
                                                           value="<?php echo $sco_checkout_secret_word_fi; ?>">
                                                </div>
                                            </div>
                                        </div>

                                    </div><!-- end of tabs content -->

                            </div><!-- END OF TABS -->

                        </div><!-- end of authorization -->
                    </div>
                </form>
            </div>

        </div>

    </div>
</div>

<?php echo $footer; ?>