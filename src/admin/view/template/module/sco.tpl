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
                                <label class="col-sm-2 control-label" for="input-status">Module version</label>
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
                                <label class="col-sm-2 control-label" for="input-checkout-secret-word"><?php echo $entry_shop_terms_uri; ?></label>
                                <div class="col-sm-10">
                                    <div class="input-group">
                                        <span class="input-group-addon"><?php echo $entry_shop_terms_uri_example ?></span>
                                        <input type="text" name="sco_checkout_terms_uri" id="input-checkout-terms-uri" class="form-control"
                                               value="<?php echo $sco_checkout_terms_uri; ?>" placeholder="terms url">
                                    </div>
                                </div>
                            </div>


                            <!-- order statuses - start -->
                            <div class="form-group">
                                <h5 class="col-sm-2 control-label">Svea <?php echo $entry_order_status; ?></h5>
                                <h5 class="col-sm-10 control-label" style="text-align: left"><?php echo $entry_oc_order_status ?></h5>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-2 control-label"><?php echo $pending_status_order; ?></label>
                                <div class="col-sm-10">
                                    <select name="sco_pending_status_id" class="form-control">
                                        <?php foreach ($order_statuses as $order_status) { ?>
                                        <?php if ($order_status['order_status_id'] == $sco_pending_status_id) { ?>
                                        <option value="<?php echo $order_status['order_status_id']; ?>" selected="selected"><?php echo $order_status['name']; ?></option>
                                        <?php } else { ?>
                                        <option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
                                        <?php } ?>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-2 control-label"><?php echo $failed_status_order; ?></label>
                                <div class="col-sm-10">
                                    <select name="sco_failed_status_id" class="form-control">
                                        <?php foreach ($order_statuses as $order_status) { ?>
                                        <?php if ($order_status['order_status_id'] == $sco_failed_status_id) { ?>
                                        <option value="<?php echo $order_status['order_status_id']; ?>" selected="selected"><?php echo $order_status['name']; ?></option>
                                        <?php } else { ?>
                                        <option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
                                        <?php } ?>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-2 control-label">
                                    <span data-toggle="tooltip" title="<?php echo $entry_status_delivered_text; ?>"><?php echo $entry_status_delivered; ?></span>
                                </label>
                                <div class="col-sm-10">
                                    <select name="sco_delivered_status_id" class="form-control">
                                        <?php foreach ($order_statuses as $order_status) { ?>
                                        <?php if ($order_status['order_status_id'] == $sco_delivered_status_id) { ?>
                                        <option value="<?php echo $order_status['order_status_id']; ?>" selected="selected"><?php echo $order_status['name']; ?></option>
                                        <?php } else { ?>
                                        <option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
                                        <?php } ?>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-2 control-label">
                                    <span data-toggle="tooltip" title="<?php echo $entry_status_canceled_text; ?>"><?php echo $entry_status_canceled; ?></span>
                                </label>
                                <div class="col-sm-10">
                                    <select name="sco_canceled_status_id" class="form-control">
                                        <?php foreach ($order_statuses as $order_status) { ?>
                                        <?php if ($order_status['order_status_id'] == $sco_canceled_status_id) { ?>
                                        <option value="<?php echo $order_status['order_status_id']; ?>" selected="selected"><?php echo $order_status['name']; ?></option>
                                        <?php } else { ?>
                                        <option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
                                        <?php } ?>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-2 control-label">
                                    <span data-toggle="tooltip" title="<?php echo $entry_status_refunded_text; ?>"><?php echo $entry_status_refunded; ?></span>
                                </label>
                                <div class="col-sm-10">
                                    <select name="sco_credited_status_id" class="form-control">
                                        <?php foreach ($order_statuses as $order_status) { ?>
                                        <?php if ($order_status['order_status_id'] == $sco_credited_status_id) { ?>
                                        <option value="<?php echo $order_status['order_status_id']; ?>" selected="selected"><?php echo $order_status['name']; ?></option>
                                        <?php } else { ?>
                                        <option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
                                        <?php } ?>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                            <!-- order statuses - end -->

                        </div>


                        <!-- Authorization -->
                        <div class="tab-pane" id="tab-authorization">
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
                                                <label class="col-sm-2 control-label" for="input-checkout-test-merchant-id"><?php echo $entry_checkout_merchant_id; ?></label>
                                                <div class="col-sm-10">
                                                    <input type="text" name="sco_checkout_test_merchant_id" id="input-checkout-test-merchant-id" class="form-control"
                                                           value="<?php echo $sco_checkout_test_merchant_id; ?>">
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-sm-2 control-label" for="input-checkout-secret-word"><?php echo $entry_checkout_secret; ?></label>
                                                <div class="col-sm-10">
                                                    <input type="text" name="sco_checkout_test_secret_word" id="input-checkout-test-secret-word" class="form-control"
                                                           value="<?php echo $sco_checkout_test_secret_word; ?>">
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Prod Authorization -->
                                        <div class="tab-pane" id="prod_merchant_settings">
                                            <div class="form-group">
                                                <label class="col-sm-2 control-label" for="input-checkout-merchant-id"><?php echo $entry_checkout_merchant_id; ?></label>
                                                <div class="col-sm-10">
                                                    <input type="text" name="sco_checkout_merchant_id" id="input-checkout-merchant-id" class="form-control"
                                                           value="<?php echo $sco_checkout_merchant_id; ?>">
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-sm-2 control-label" for="input-checkout-secret-word"><?php echo $entry_checkout_secret; ?></label>
                                                <div class="col-sm-10">
                                                    <input type="text" name="sco_checkout_secret_word" id="input-checkout-secret-word" class="form-control"
                                                           value="<?php echo $sco_checkout_secret_word; ?>">
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