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
                    <td>2.5.0</td>
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
         <?php if($version >= 1.5){ ?>
         <div class="htabs" id="htabs" >
             <a href="#tab-card_test" style="display: inline">Test</a>
             <a href="#tab-card_prod" style="display: inline">Prod</a>
        </div>
         <?php } ?>
        <!-- Countrycode and testmode specific -->
        <!--Test -->
        <?php
        if($version < 1.5){
            echo '<h2>Test</h2>';
        }
        ?>
        <div id="tab-card_test" style="display: inline;">
             <table class="form">
                 <tbody>
                     <tr>
                         <td><?php echo $entry_merchant_id; ?>:</td>
                         <td>
                             <input name="svea_card_merchant_id_test" type="text"
                                    value="<?php echo $value_merchant_test; ?>" />
                         </td>
                     </tr>
                     <tr>
                         <td><?php echo $entry_sw; ?>:</td>
                         <td>
                             <input name="svea_card_sw_test" type="text"
                                    value="<?php echo $value_sw_test; ?>" />
                         </td>
                     </tr>
                 </tbody>
             </table>
         </div>
<!--Prod -->
        <?php
        if($version < 1.5){
            echo '<h2>Prod</h2>';
        }
        ?>
        <div id="tab-card_prod" style="display: inline;">
            <table class="form">
                <tbody>
                    <tr>
                        <td><?php echo $entry_merchant_id; ?>:</td>
                        <td>
                            <input name="svea_card_merchant_id_prod" type="text"
                                   value="<?php echo $value_merchant_prod; ?>" />
                        </td>
                    </tr>
                    <tr>
                        <td><?php echo $entry_sw; ?>:</td>
                        <td>
                            <input name="svea_card_sw_prod" type="text"
                                   value="<?php echo $value_sw_prod; ?>" />
                        </td>
                    </tr>
                </tbody>
            </table>
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