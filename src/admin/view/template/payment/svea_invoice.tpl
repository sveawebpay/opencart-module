<?php echo $header; ?>
<?php if ($error_warning) { ?>
<div class="warning"><?php echo $error_warning; ?></div>
<?php } ?>

<div class="box">
    <div class="left"></div>
    <div class="right"></div>
    <div class="heading">
        <h1 style=""><?php echo $heading_title; ?></h1>
        <div class="buttons"><a onclick="$('#form').submit();" class="button"><span><?php echo $button_save; ?></span></a><a onclick="location = '<?php echo $cancel; ?>';" class="button"><span><?php echo $button_cancel; ?></span></a></div>
    </div>
    <div class="content">
        <!--general settings -->
        <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form">
            <table class="form">
                <tbody>
                    <tr>
                        <td>Version</td>
                        <td>2.4.2</td>
                    </tr>
                    <tr>
                        <td><?php echo $entry_geo_zone; ?></td>
                        <td><select name="svea_invoice_geo_zone_id">
                                <option value="0"><?php echo $text_all_zones; ?></option>
                                <?php foreach ($geo_zones as $geo_zone) { ?>
                                <?php if ($geo_zone['geo_zone_id'] == $svea_invoice_geo_zone_id) { ?>
                                <option value="<?php echo $geo_zone['geo_zone_id']; ?>" selected="selected"><?php echo $geo_zone['name']; ?></option>
                                <?php } else { ?>
                                <option value="<?php echo $geo_zone['geo_zone_id']; ?>"><?php echo $geo_zone['name']; ?></option>
                                <?php } ?>
                                <?php } ?>
                            </select></td>
                    </tr>
                    <tr>
                        <td><?php echo $entry_status; ?></td>
                        <td><select name="svea_invoice_status">
                                <?php if ($svea_invoice_status) { ?>
                                <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                                <option value="0"><?php echo $text_disabled; ?></option>
                                <?php } else { ?>
                                <option value="1"><?php echo $text_enabled; ?></option>
                                <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                                <?php } ?>
                            </select></td>
                    </tr>
                    <tr>
                        <td><?php echo $entry_sort_order; ?></td>
                        <td><input type="text" name="svea_invoice_sort_order" value="<?php echo $svea_invoice_sort_order; ?>" size="1" /></td>
                    </tr>
                    <tr>
                        <td><?php echo $entry_order_status; ?><span class="help"><?php echo $entry_order_status_text ?></span></td>
                        <td><select name="svea_invoice_order_status_id">
                                <?php foreach ($order_statuses as $order_status) { ?>
                                <?php if ($order_status['order_status_id'] == $svea_invoice_order_status_id) { ?>
                                <option value="<?php echo $order_status['order_status_id']; ?>" selected="selected"><?php echo $order_status['name']; ?></option>
                                <?php } else { ?>
                                <option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
                                <?php } ?>
                                <?php } ?>
                            </select></td>
                    </tr>
                    <tr>
                        <td><?php echo $entry_auto_deliver; ?><span class="help"><?php echo $entry_auto_deliver_text ?></span></td>
                        <td>
                            <select name="svea_invoice_auto_deliver">
                                <option value="0" <?php if($svea_invoice_auto_deliver == '0'){ echo 'selected="selected"';}?> ><?php echo $text_disabled; ?></option>
                                <option value="1" <?php if($svea_invoice_auto_deliver == '1'){ echo 'selected="selected"';}?> ><?php echo $text_enabled; ?></option>
                            </select>
                            <span class="help"><?php echo $entry_order_status; ?></span>
                            <select name="svea_invoice_auto_deliver_status_id">
                                <?php foreach ($order_statuses as $deliver_status) { ?>
                                <?php if ($deliver_status['order_status_id'] == $svea_invoice_auto_deliver_status_id) { ?>
                                <option value="<?php echo $deliver_status['order_status_id']; ?>" selected="selected"><?php echo $deliver_status['name']; ?></option>
                                <?php } else { ?>
                                <option value="<?php echo $deliver_status['order_status_id']; ?>"><?php echo $deliver_status['name']; ?></option>
                                <?php } ?>
                                <?php } ?>
                            </select>
                            <span class="help"><?php echo $entry_distribution_type; ?></span>
                            <select name="svea_invoice_distribution_type">
                                <option value="Post" <?php if($svea_invoice_distribution_type == 'Post'){ echo 'selected="selected"';}?> ><?php echo $entry_post; ?></option>
                                <option value="Email" <?php if($svea_invoice_distribution_type == 'Email'){ echo 'selected="selected"';}?> ><?php echo $entry_email; ?></option>
                            </select>
                        </td>
                    </tr>
                     <tr>
                        <td><?php echo $entry_product; ?><span class="help"><?php echo $entry_product_text ?></span></td>
                        <td>
                            <?php if ($svea_invoice_product_price) { ?>
                            <input type="radio" name="svea_invoice_product_price" value="1" checked="checked" />
                            <?php echo $entry_yes; ?>
                            <input type="radio" name="svea_invoice_product_price" value="0" />
                            <?php echo $entry_no; ?>
                            <?php } else { ?>
                            <input type="radio" name="svea_invoice_product_price" value="1" />
                            <?php echo $entry_yes; ?>
                            <input type="radio" name="svea_invoice_product_price" value="0" checked="checked" />
                            <?php echo $entry_no; ?>
                            <?php } ?>
                            <span class="help"><?php echo $entry_min_amount; ?></span>
                            <input name="svea_invoice_product_price_min" type="text"
                                           value="<?php echo $svea_invoice_product_price_min; ?>" />
                        </td>
                    </tr>
                </tbody>
            </table>
            <!-- Countrycode specific -->
            <div id="tab-invoice" style="display: inline;">
                <?php if($version >= 1.5){ ?>
                <div id="vtabs" class="vtabs">
                    <?php foreach ($credentials as $code){ ?>
                    <a href="#tab-invoice_<?php echo $code['lang'] ?>"><?php echo $code['lang'] ?></a>
                    <?php } ?>
                </div>
                <?php } ?>
                <?php foreach($credentials as $code){ ?>
                <div id="tab-invoice_<?php echo $code['lang'] ?>" class="vtabs-content">
                    <?php if($version < 1.5){ ?>
                    <h3><?php echo $code['lang']; ?></h3>
                    <?php } ?>
                    <table class="form">
                        <tbody>
                            <tr>
                                <td><?php echo $entry_testmode; ?></td>
                                <td>
                                    <select name="<?php echo $code['name_testmode']; ?>">
                                        <option value="1" <?php if($code['value_testmode'] == '1'){ echo 'selected="selected"';}?> ><?php echo $text_enabled; ?></option>
                                        <option value="0" <?php if($code['value_testmode'] == '0'){ echo 'selected="selected"';}?> ><?php echo $text_disabled; ?></option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td><?php echo $entry_username; ?></td>
                                <td>
                                    <input name="<?php echo $code['name_username']; ?>" type="text"
                                           value="<?php echo $code['value_username']; ?>" />
                                </td>
                            </tr>
                            <tr>
                                <td><?php echo $entry_password; ?></td>
                                <td>
                                    <input name="<?php echo $code['name_password']; ?>" type="password"
                                           value="<?php echo $code['value_password']; ?>" />
                                </td>
                            </tr>
                            <tr>
                                <td><?php echo $entry_clientno; ?></td>
                                <td>
                                    <input name="<?php echo $code['name_clientno']; ?>" type="text"
                                           value="<?php echo $code['value_clientno']; ?>" />
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <?php } ?>
            </div>
        </form>
    </div>
</div>
<div style="height:100px"></div>
<script type="text/javascript"><!--
    $('#tab-invoice a').tabs();
//--></script>
<?php echo $footer; ?>