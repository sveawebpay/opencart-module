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
    <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form">
        <!--general settings -->
        <table class="form">
            <tbody>
                <tr>
                    <td>Version</td>
                    <td>2.0.0</td>
                </tr>
                <tr>
                    <td><?php echo $entry_order_status; ?></td>
                    <td><select name="svea_card_order_status_id">
                        <?php foreach ($order_statuses as $order_status) { ?>
                            <?php if ($order_status['order_status_id'] == $svea_card_order_status_id) { ?>
                            <option value="<?php echo $order_status['order_status_id']; ?>" selected="selected"><?php echo $order_status['name']; ?></option>
                            <?php } else { ?>
                            <option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
                            <?php }
                        } ?>
                      </select></td>
                </tr>
                <tr>
                    <td><?php echo $entry_testmode; ?>:</td>
                    <td><select name="svea_card_testmode">
                          <option value="1" <?php if($svea_card_testmode == '1'){ echo 'selected="selected"';}?> ><?php echo $text_enabled; ?></option>
                          <option value="0" <?php if($svea_card_testmode == '0'){ echo 'selected="selected"';}?> ><?php echo $text_disabled; ?></option>
                          </select>
                    </td>
                </tr>
                <tr>
                    <td><?php echo $entry_geo_zone; ?></td>
                    <td>
                        <select name="svea_geo_zone_id">
                            <option value="0"><?php echo $text_all_zones; ?></option>
                            <?php foreach ($geo_zones as $geo_zone) { ?>
                                <?php if ($geo_zone['geo_zone_id'] == $svea_card_geo_zone_id) { ?>
                            <option value="<?php echo $geo_zone['geo_zone_id']; ?>" selected="selected"><?php echo $geo_zone['name']; ?></option>
                                <?php } else { ?>
                            <option value="<?php echo $geo_zone['geo_zone_id']; ?>"><?php echo $geo_zone['name']; ?></option>
                                <?php } ?>
                            <?php } ?>
                      </select>
                    </td>
                </tr>
                <tr>
                    <td><?php echo $entry_status; ?></td>
                    <td>
                        <select name="svea_card_status">
                            <?php if ($svea_card_status) { ?>
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
                    <td><?php echo $entry_sort_order; ?></td>
                    <td><input type="text" name="svea_card_sort_order" value="<?php echo $svea_card_sort_order; ?>" size="1" /></td>
                </tr>
            </tbody>
        </table>
        <!-- Mode specific -->
         <div class="htabs" id="htabs" >
             <a href="#tab-card_test" style="display: inline">test</a>
             <a href="#tab-card_prod" style="display: inline">Prod</a>
        </div>
        <!-- Countrycode and testmode specific -->
        <!--Test -->
        <div id="tab-card_test" style="display: inline;">
            <div id="vtabs" class="vtabs">
                <?php foreach ($test as $code){ ?>
                    <a href="#tab-card_test_<?php echo $code['lang'] ?>"><?php echo $code['lang'] ?></a>
                <?php } ?>
            </div>
        <?php foreach($test as $code){ ?>
            <div id="tab-card_test_<?php echo $code['lang'] ?>" class="vtabs-content">
                <table class="form">
                    <tbody>

                        <tr>
                            <td><?php echo $entry_merchant_id; ?>:</td>
                            <td>
                                <input name="<?php echo $code['name_merchant']; ?>" type="text"
                                       value="<?php echo $code['value_merchant']; ?>" />
                            </td>
                        </tr>
                        <tr>
                            <td><?php echo $entry_sw; ?>:</td>
                            <td>
                                <input name="<?php echo $code['name_sw']; ?>" type="password"
                                       value="<?php echo $code['value_sw']; ?>" />
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        <?php } ?>
        </div>
<!--Prod -->
     <div id="tab-card_prod" style="display: inline;">
            <div id="vtabs" class="vtabs">
                <?php foreach ($prod as $code){ ?>
                    <a href="#tab-card_prod_<?php echo $code['lang'] ?>"><?php echo $code['lang'] ?></a>
                <?php } ?>
            </div>


        <?php foreach($prod as $code){ ?>
            <div id="tab-card_prod_<?php echo $code['lang'] ?>" class="vtabs-content">
                <table class="form">
                    <tbody>

                        <tr>
                            <td><?php echo $entry_merchant_id; ?>:</td>
                            <td>
                                <input name="<?php echo $code['name_merchant']; ?>" type="text"
                                       value="<?php echo $code['value_merchant']; ?>" />
                            </td>
                        </tr>
                        <tr>
                            <td><?php echo $entry_sw; ?>:</td>
                            <td>
                                <input name="<?php echo $code['name_sw']; ?>" type="password"
                                       value="<?php echo $code['value_sw']; ?>" />
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        <?php } ?>
        </div>
    </div>
  </form>
    </div>
    <div style="height:100px"></div>
</div>
<script type="text/javascript"><!--
$('#tab-card_test a').tabs();
$('#tab-card_prod a').tabs();
$('#htabs a').tabs();

//--></script>
<?php echo $footer; ?>