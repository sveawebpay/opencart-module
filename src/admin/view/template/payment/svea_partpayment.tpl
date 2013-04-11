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
      <table class="form">
          <tbody>
            <tr>
                <td>Version</td>
                <td>2.0.0</td>
            </tr>
            <tr>
              <td><?php echo $entry_order_status; ?></td>
              <td><select name="svea_partpayment_order_status_id">
                  <?php foreach ($order_statuses as $order_status) { ?>
                  <?php if ($order_status['order_status_id'] == $svea_partpayment_order_status_id) { ?>
                  <option value="<?php echo $order_status['order_status_id']; ?>" selected="selected"><?php echo $order_status['name']; ?></option>
                  <?php } else { ?>
                  <option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
                  <?php } ?>
                  <?php } ?>
                </select></td>
            </tr>
            <tr>
              <td><?php echo $entry_geo_zone; ?></td>
              <td><select name="svea_partpayment_geo_zone_id">
                  <option value="0"><?php echo $text_all_zones; ?></option>
                  <?php foreach ($geo_zones as $geo_zone) { ?>
                  <?php if ($geo_zone['geo_zone_id'] == $svea_partpayment_geo_zone_id) { ?>
                  <option value="<?php echo $geo_zone['geo_zone_id']; ?>" selected="selected"><?php echo $geo_zone['name']; ?></option>
                  <?php } else { ?>
                  <option value="<?php echo $geo_zone['geo_zone_id']; ?>"><?php echo $geo_zone['name']; ?></option>
                  <?php } ?>
                  <?php } ?>
                </select></td>
            </tr>
            <tr>
              <td><?php echo $entry_status; ?></td>
              <td><select name="svea_partpayment_status">
                  <?php if ($svea_partpayment_status) { ?>
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
              <td><input type="text" name="svea_partpayment_sort_order" value="<?php echo $svea_partpayment_sort_order; ?>" size="1" /></td>
            </tr>
            <tr>
              <td><?php echo $entry_testmode; ?>:</td>
              <td>
                <select name="svea_partpayment_testmode">
                    <option value="1" <?php if($svea_partpayment_testmode == '1'){ echo 'selected="selected"';}?> ><?php echo $text_enabled; ?></option>
                    <option value="0" <?php if($svea_partpayment_testmode == '0'){ echo 'selected="selected"';}?> ><?php echo $text_disabled; ?></option>
                </select>
                </td>
            </tr>
          </tbody>
      </table>
         <!-- Countrycode specific -->
        <div id="tab-partpayment" style="display: inline;">
            <div id="vtabs" class="vtabs">
                <?php foreach ($credentials as $code){ ?>
                    <a href="#tab-partpayment_<?php echo $code['lang'] ?>"><?php echo $code['lang'] ?></a>
                <?php } ?>
            </div>
        <?php foreach($credentials as $code){ ?>
            <div id="tab-partpayment_<?php echo $code['lang'] ?>" class="vtabs-content">
                <table class="form">
                    <tbody>

                        <tr>
                            <td><?php echo $entry_username; ?>:</td>
                            <td>
                                <input name="<?php echo $code['name_username']; ?>" type="text"
                                       value="<?php echo $code['value_username']; ?>" />
                            </td>
                        </tr>
                        <tr>
                            <td><?php echo $entry_password; ?>:</td>
                            <td>
                                <input name="<?php echo $code['name_password']; ?>" type="password"
                                       value="<?php echo $code['value_password']; ?>" />
                            </td>
                        </tr>
                        <tr>
                            <td><?php echo $entry_clientno; ?>:</td>
                            <td>
                                <input name="<?php echo $code['name_clientno']; ?>" type="text"
                                       value="<?php echo $code['value_clientno']; ?>" />
                            </td>
                        </tr>
                        <tr>
                            <td><?php echo $entry_min_amount; ?>:</td>
                            <td>
                                <input name="<?php $code['min_amount_name']; ?>" type="text" value="<?php echo $code['min_amount_value']; ?>" />
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
$('#tab-partpayment a').tabs();

//--></script>
<?php echo $footer; ?>
        <!--

        <tr>
            <td><?php echo $entry_sweden; ?></td>
            <td></td>
        </tr>
        <tr>
            <td><?php echo $entry_username; ?>:</td>
            <td>
                <input name="svea_partpayment_username_SE" type="text" value="<?php echo $svea_partpayment_username_SE; ?>" />
            </td>
        </tr>
        <tr>
            <td><?php echo $entry_password; ?>:</td>
            <td>
                <input name="svea_partpayment_password_SE" type="password" value="<?php echo $svea_partpayment_password_SE; ?>" />
            </td>
        </tr>
        <tr>
            <td><?php echo $entry_clientno; ?>:</td>
            <td>
                <input name="svea_partpayment_clientno_SE" type="text" value="<?php echo $svea_partpayment_clientno_SE; ?>" />
            </td>
        </tr>
        <tr>
            <td><?php echo $entry_min_amount; ?>:</td>
            <td>
                <input name="svea_partpayment_min_amount_SE" type="text" value="<?php echo $svea_partpayment_min_amount_SE; ?>" />
            </td>
        </tr>



        <tr>
            <td><?php echo $entry_finland; ?></td>
            <td></td>
        </tr>
        <tr>
            <td><?php echo $entry_username; ?>:</td>
            <td>
                <input name="svea_partpayment_username_FI" type="text" value="<?php echo $svea_partpayment_username_FI; ?>" />
            </td>
        </tr>
        <tr>
            <td><?php echo $entry_password; ?>:</td>
            <td>
                <input name="svea_partpayment_password_FI" type="password" value="<?php echo $svea_partpayment_password_FI; ?>" />
            </td>
        </tr>
        <tr>
            <td><?php echo $entry_clientno; ?>:</td>
            <td>
                <input name="svea_partpayment_clientno_FI" type="text" value="<?php echo $svea_partpayment_clientno_FI; ?>" />
            </td>
        </tr>
        <tr>
            <td><?php echo $entry_min_amount; ?>:</td>
            <td>
                <input name="svea_partpayment_min_amount_FI" type="text" value="<?php echo $svea_partpayment_min_amount_FI; ?>" />
            </td>
        </tr>



        <tr>
            <td><?php echo $entry_denmark; ?></td>
            <td></td>
        </tr>
        <tr>
            <td><?php echo $entry_username; ?>:</td>
            <td>
                <input name="svea_partpayment_username_DK" type="text" value="<?php echo $svea_partpayment_username_DK; ?>" />
            </td>
        </tr>
        <tr>
            <td><?php echo $entry_password; ?>:</td>
            <td>
                <input name="svea_partpayment_password_DK" type="password" value="<?php echo $svea_partpayment_password_DK; ?>" />
            </td>
        </tr>
        <tr>
            <td><?php echo $entry_clientno; ?>:</td>
            <td>
                <input name="svea_partpayment_clientno_DK" type="text" value="<?php echo $svea_partpayment_clientno_DK; ?>" />
            </td>
        </tr>
        <tr>
            <td><?php echo $entry_min_amount; ?>:</td>
            <td>
                <input name="svea_partpayment_min_amount_DK" type="text" value="<?php echo $svea_partpayment_min_amount_DK; ?>" />
            </td>
        </tr>



        <tr>
            <td><?php echo $entry_norway; ?></td>
            <td></td>
        </tr>
        <tr>
            <td><?php echo $entry_username; ?>:</td>
            <td>
                <input name="svea_partpayment_username_NO" type="text" value="<?php echo $svea_partpayment_username_NO; ?>" />
            </td>
        </tr>
        <tr>
            <td><?php echo $entry_password; ?>:</td>
            <td>
                <input name="svea_partpayment_password_NO" type="password" value="<?php echo $svea_partpayment_password_NO; ?>" />
            </td>
        </tr>
        <tr>
            <td><?php echo $entry_clientno; ?>:</td>
            <td>
                <input name="svea_partpayment_clientno_NO" type="text" value="<?php echo $svea_partpayment_clientno_NO; ?>" />
            </td>
        </tr>
        <tr>
            <td><?php echo $entry_min_amount; ?>:</td>
            <td>
                <input name="svea_partpayment_min_amount_NO" type="text" value="<?php echo $svea_partpayment_min_amount_NO; ?>" />
            </td>
        </tr>



        <tr>
            <td><?php echo $entry_netherlands; ?></td>
            <td></td>
        </tr>
        <tr>
            <td><?php echo $entry_username; ?>:</td>
            <td>
                <input name="svea_partpayment_username_NL" type="text" value="<?php echo $svea_partpayment_username_NL; ?>" />
            </td>
        </tr>
        <tr>
            <td><?php echo $entry_password; ?>:</td>
            <td>
                <input name="svea_partpayment_password_NL" type="password" value="<?php echo $svea_partpayment_password_NL; ?>" />
            </td>
        </tr>
        <tr>
            <td><?php echo $entry_clientno; ?>:</td>
            <td>
                <input name="svea_partpayment_clientno_NL" type="text" value="<?php echo $svea_partpayment_clientno_NL; ?>" />
            </td>
        </tr>
        <tr>
            <td><?php echo $entry_min_amount; ?>:</td>
            <td>
                <input name="svea_partpayment_min_amount_NL" type="text" value="<?php echo $svea_partpayment_min_amount_NL; ?>" />
            </td>
        </tr>



        <tr>
            <td><?php echo $entry_germany; ?></td>
            <td></td>
        </tr>
        <tr>
            <td><?php echo $entry_username; ?>:</td>
            <td>
                <input name="svea_partpayment_username_DE" type="text" value="<?php echo $svea_partpayment_username_DE; ?>" />
            </td>
        </tr>
        <tr>
            <td><?php echo $entry_password; ?>:</td>
            <td>
                <input name="svea_partpayment_password_DE" type="password" value="<?php echo $svea_partpayment_password_DE; ?>" />
            </td>
        </tr>
        <tr>
            <td><?php echo $entry_clientno; ?>:</td>
            <td>
                <input name="svea_partpayment_clientno_DE" type="text" value="<?php echo $svea_partpayment_clientno_DE; ?>" />
            </td>
        </tr>
        <tr>
            <td><?php echo $entry_min_amount; ?>:</td>
            <td>
                <input name="svea_partpayment_min_amount_DE" type="text" value="<?php echo $svea_partpayment_min_amount_DE; ?>" />
            </td>
        </tr>

      </table>
    </form>
  </div>
  <div style="height:100px"></div>
</div>
<?php echo $footer; ?>
-->