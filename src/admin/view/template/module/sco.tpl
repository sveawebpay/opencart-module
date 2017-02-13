<?php echo $header; ?>

<div id="content">

    <div class="breadcrumb">
        <?php foreach ($breadcrumbs as $breadcrumb) { ?>
        <?php echo $breadcrumb['separator']; ?><a
                href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
        <?php } ?>
    </div>

    <?php if ($error_warning) { ?>
    <div class="warning"><?php echo $error_warning; ?></div>
    <?php } ?>

    <div class="box">
        <div class="heading">
            <h1><img src="view/image/module.png" alt=""/> <?php echo $heading_title; ?></h1>
            <div class="buttons">
                <a onclick="$('#form').submit();" class="button"><?php echo $button_save; ?></a>
                <a href="<?php echo $cancel; ?>" class="button"><?php echo $button_cancel; ?></a>
            </div>
        </div>

        <div class="content">

            <div id="tabs" class="htabs">
                <a href="#tab-general"><?php echo $tab_general; ?></a>
                <a href="#tab-authorization"><?php echo $tab_authorization; ?></a>
            </div>

            <form action="<?php echo $action; ?>" method="post" id="form">

                <!-- GENERAL -->
                <div id="tab-general">
                    <table class="form">
                        <tr>
                            <td>Module version</td>
                            <td>
                                <div><?php echo $module_version; ?></div>
                                <a href="<?php echo $module_repo_url; ?>"><?php echo $module_version_info; ?></a>
                            </td>
                        </tr>
                        <tr>
                            <td><?php echo $entry_status; ?></td>
                            <td>
                                <select name="sco_status" id="input-status" class="form-control">
                                    <?php if ($sco_status) { ?>
                                    <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                                    <option value="0"><?php echo $text_disabled; ?></option>
                                    <?php } else { ?>
                                    <option value="1"><?php echo $text_enabled; ?></option>
                                    <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                                    <?php } ?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td><?php echo $entry_test_mode; ?></td>
                            <td>
                                <select name="sco_test_mode" id="input-test_mode" class="form-control">
                                    <?php if ($sco_test_mode) { ?>
                                    <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                                    <option value="0"><?php echo $text_disabled; ?></option>
                                    <?php } else { ?>
                                    <option value="1"><?php echo $text_enabled; ?></option>
                                    <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                                    <?php } ?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td><?php echo $entry_status_checkout; ?></td>
                            <td>
                                <select name="sco_status_checkout" id="input-status_checkout" class="form-control">
                                    <?php if ($sco_status_checkout) { ?>
                                    <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                                    <option value="0"><?php echo $text_disabled; ?></option>
                                    <?php } else { ?>
                                    <option value="1"><?php echo $text_enabled; ?></option>
                                    <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                                    <?php } ?>
                                </select>
                            </td>
                        </tr>


                        <!-- order statuses - start -->
                        <tr>
                            <td><h3>Svea <?php echo $entry_order_status; ?></h3></td>
                            <td><h3><?php echo $entry_oc_order_status ?></h3></td>
                        </tr>
                        <tr>
                            <td><?php echo $pending_status_order; ?></td>
                            <td>
                                <select name="sco_pending_status_id" class="form-control">
                                    <?php foreach ($order_statuses as $order_status) { ?>
                                    <?php if ($order_status['order_status_id'] == $sco_pending_status_id) { ?>
                                    <option value="<?php echo $order_status['order_status_id']; ?>"
                                            selected="selected"><?php echo $order_status['name']; ?></option>
                                    <?php } else { ?>
                                    <option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
                                    <?php } ?>
                                    <?php } ?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td><?php echo $failed_status_order; ?></td>
                            <td>
                                <select name="sco_failed_status_id" class="form-control">
                                    <?php foreach ($order_statuses as $order_status) { ?>
                                    <?php if ($order_status['order_status_id'] == $sco_failed_status_id) { ?>
                                    <option value="<?php echo $order_status['order_status_id']; ?>"
                                            selected="selected"><?php echo $order_status['name']; ?></option>
                                    <?php } else { ?>
                                    <option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
                                    <?php } ?>
                                    <?php } ?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td><?php echo $entry_status_delivered; ?></td>
                            <td>
                                <select name="sco_delivered_status_id" class="form-control">
                                    <?php foreach ($order_statuses as $order_status) { ?>
                                    <?php if ($order_status['order_status_id'] == $sco_delivered_status_id) { ?>
                                    <option value="<?php echo $order_status['order_status_id']; ?>"
                                            selected="selected"><?php echo $order_status['name']; ?></option>
                                    <?php } else { ?>
                                    <option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
                                    <?php } ?>
                                    <?php } ?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td><?php echo $entry_status_canceled; ?></td>
                            <td>
                                <select name="sco_canceled_status_id" class="form-control">
                                    <?php foreach ($order_statuses as $order_status) { ?>
                                    <?php if ($order_status['order_status_id'] == $sco_canceled_status_id) { ?>
                                    <option value="<?php echo $order_status['order_status_id']; ?>"
                                            selected="selected"><?php echo $order_status['name']; ?></option>
                                    <?php } else { ?>
                                    <option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
                                    <?php } ?>
                                    <?php } ?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td><?php echo $entry_status_refunded; ?></td>
                            <td>
                                <select name="sco_credited_status_id" class="form-control">
                                    <?php foreach ($order_statuses as $order_status) { ?>
                                    <?php if ($order_status['order_status_id'] == $sco_credited_status_id) { ?>
                                    <option value="<?php echo $order_status['order_status_id']; ?>"
                                            selected="selected"><?php echo $order_status['name']; ?></option>
                                    <?php } else { ?>
                                    <option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
                                    <?php } ?>
                                    <?php } ?>
                                </select>
                            </td>
                        </tr>
                    </table>
                    <!-- order statuses - end -->

                </div>


                <!-- Authorization -->
                <div id="tab-authorization">
                    <!-- Mode specific -->
                    <div class="htabs" id="htabs" >
                        <a href="#tab-card_test" style="display: inline">Test</a>
                        <a href="#tab-card_prod" style="display: inline">Prod</a>
                    </div>
                    <!--Test -->
                    <div id="tab-card_test" style="display: inline;">
                        <table class="form"><tr>
                                <td><?php echo $entry_checkout_merchant_id; ?></td>
                                <td>
                                    <input type="text" name="sco_checkout_test_merchant_id" id="input-checkout-test-merchant-id" class="form-control"
                                           value="<?php echo $sco_checkout_test_merchant_id; ?>">
                                </td>
                            </tr>
                            <tr>
                                <td><?php echo $entry_checkout_secret; ?></td>
                                <td>
                                    <input type="text" name="sco_checkout_test_secret_word" id="input-checkout-test-secret-word" class="form-control"
                                           value="<?php echo $sco_checkout_test_secret_word; ?>">
                                </td>
                            </tr>
                        </table>
                    </div>
                    <!--Prod -->
                    <div id="tab-card_prod" style="display: inline;">
                        <table class="form"><tr>
                                <td><?php echo $entry_checkout_merchant_id; ?></td>
                                <td>
                                    <input type="text" name="sco_checkout_merchant_id" id="input-checkout-merchant-id" class="form-control"
                                           value="<?php echo $sco_checkout_merchant_id; ?>">
                                </td>
                            </tr>
                            <tr>
                                <td><?php echo $entry_checkout_secret; ?></td>
                                <td>
                                    <input type="text" name="sco_checkout_secret_word" id="input-checkout-secret-word" class="form-control"
                                           value="<?php echo $sco_checkout_secret_word; ?>">
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>

            </form>
        </div>

    </div>

</div>

<script type="text/javascript">
    $('#tabs a').tabs();
    $('#htabs a').tabs();
</script>

<?php echo $footer; ?>