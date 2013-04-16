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
                <td>2.0.0</td>
            </tr>
            <tr>
              <td><?php echo $entry_order_status; ?></td>
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
                <td><?php echo $entry_testmode; ?>:</td>
                <td>
                    <select name="svea_invoice_testmode">
                        <option value="1" <?php if($svea_invoice_testmode == '1'){ echo 'selected="selected"';}?> ><?php echo $text_enabled; ?></option>
                        <option value="0" <?php if($svea_invoice_testmode == '0'){ echo 'selected="selected"';}?> ><?php echo $text_disabled; ?></option>
                    </select>
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
$('#tab-invoice a').tabs();

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
                <input name="svea_invoice_username_SE" type="text" value="<?php echo $svea_invoice_username_SE; ?>" />
            </td>
        </tr>
        <tr>
            <td><?php echo $entry_password; ?>:</td>
            <td>
                <input name="svea_invoice_password_SE" type="password" value="<?php echo $svea_invoice_password_SE; ?>" />
            </td>
        </tr>
        <tr>
            <td><?php echo $entry_clientno; ?>:</td>
            <td>
                <input name="svea_invoice_clientno_SE" type="text" value="<?php echo $svea_invoice_clientno_SE; ?>" />
            </td>
        </tr>

        <tr>
            <td><?php echo $entry_finland; ?></td>
            <td></td>
        </tr>
        <tr>
            <td><?php echo $entry_username; ?>:</td>
            <td>
                <input name="svea_invoice_username_FI" type="text" value="<?php echo $svea_invoice_username_FI; ?>" />
            </td>
        </tr>
        <tr>
            <td><?php echo $entry_password; ?>:</td>
            <td>
                <input name="svea_invoice_password_FI" type="password" value="<?php echo $svea_invoice_password_FI; ?>" />
            </td>
        </tr>
        <tr>
            <td><?php echo $entry_clientno; ?>:</td>
            <td>
                <input name="svea_invoice_clientno_FI" type="text" value="<?php echo $svea_invoice_clientno_FI; ?>" />
            </td>
        </tr>



        <tr>
            <td><?php echo $entry_denmark; ?></td>
            <td></td>
        </tr>
        <tr>
            <td><?php echo $entry_username; ?>:</td>
            <td>
                <input name="svea_invoice_username_DK" type="text" value="<?php echo $svea_invoice_username_DK; ?>" />
            </td>
        </tr>
        <tr>
            <td><?php echo $entry_password; ?>:</td>
            <td>
                <input name="svea_invoice_password_DK" type="password" value="<?php echo $svea_invoice_password_DK; ?>" />
            </td>
        </tr>
        <tr>
            <td><?php echo $entry_clientno; ?>:</td>
            <td>
                <input name="svea_invoice_clientno_DK" type="text" value="<?php echo $svea_invoice_clientno_DK; ?>" />
            </td>
        </tr>



        <tr>
            <td><?php echo $entry_norway; ?></td>
            <td></td>
        </tr>
        <tr>
            <td><?php echo $entry_username; ?>:</td>
            <td>
                <input name="svea_invoice_username_NO" type="text" value="<?php echo $svea_invoice_username_NO; ?>" />
            </td>
        </tr>
        <tr>
            <td><?php echo $entry_password; ?>:</td>
            <td>
                <input name="svea_invoice_password_NO" type="password" value="<?php echo $svea_invoice_password_NO; ?>" />
            </td>
        </tr>
        <tr>
            <td><?php echo $entry_clientno; ?>:</td>
            <td>
                <input name="svea_invoice_clientno_NO" type="text" value="<?php echo $svea_invoice_clientno_NO; ?>" />
            </td>
        </tr>



        <tr>
            <td><?php echo $entry_netherlands; ?></td>
            <td></td>
        </tr>
        <tr>
            <td><?php echo $entry_username; ?>:</td>
            <td>
                <input name="svea_invoice_username_NL" type="text" value="<?php echo $svea_invoice_username_NL; ?>" />
            </td>
        </tr>
        <tr>
            <td><?php echo $entry_password; ?>:</td>
            <td>
                <input name="svea_invoice_password_NL" type="password" value="<?php echo $svea_invoice_password_NL; ?>" />
            </td>
        </tr>
        <tr>
            <td><?php echo $entry_clientno; ?>:</td>
            <td>
                <input name="svea_invoice_clientno_NL" type="text" value="<?php echo $svea_invoice_clientno_NL; ?>" />
            </td>
        </tr>



        <tr>
            <td><?php echo $entry_germany; ?></td>
            <td></td>
        </tr>
        <tr>
            <td><?php echo $entry_username; ?>:</td>
            <td>
                <input name="svea_invoice_username_DE" type="text" value="<?php echo $svea_invoice_username_DE; ?>" />
            </td>
        </tr>
        <tr>
            <td><?php echo $entry_password; ?>:</td>
            <td>
                <input name="svea_invoice_password_DE" type="password" value="<?php echo $svea_invoice_password_DE; ?>" />
            </td>
        </tr>
        <tr>
            <td><?php echo $entry_clientno; ?>:</td>
            <td>
                <input name="svea_invoice_clientno_DE" type="text" value="<?php echo $svea_invoice_clientno_DE; ?>" />
            </td>
        </tr>

      </table>

    </form>
  </div>
  <div style="height:100px"></div>
</div>
<?php echo $footer; ?>
        -->