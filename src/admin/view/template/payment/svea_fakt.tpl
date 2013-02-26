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
        <tr>
            <td>Version</td>
            <td>1.3</td>
        </tr>
        <tr>
          <td><?php echo $entry_order_status; ?></td>
          <td><select name="svea_fakt_order_status_id">
              <?php foreach ($order_statuses as $order_status) { ?>
              <?php if ($order_status['order_status_id'] == $svea_fakt_order_status_id) { ?>
              <option value="<?php echo $order_status['order_status_id']; ?>" selected="selected"><?php echo $order_status['name']; ?></option>
              <?php } else { ?>
              <option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
              <?php } ?>
              <?php } ?>
            </select></td>
        </tr>
        <tr>
          <td><?php echo $entry_geo_zone; ?></td>
          <td><select name="svea_fakt_geo_zone_id">
              <option value="0"><?php echo $text_all_zones; ?></option>
              <?php foreach ($geo_zones as $geo_zone) { ?>
              <?php if ($geo_zone['geo_zone_id'] == $svea_fakt_geo_zone_id) { ?>
              <option value="<?php echo $geo_zone['geo_zone_id']; ?>" selected="selected"><?php echo $geo_zone['name']; ?></option>
              <?php } else { ?>
              <option value="<?php echo $geo_zone['geo_zone_id']; ?>"><?php echo $geo_zone['name']; ?></option>
              <?php } ?>
              <?php } ?>
            </select></td>
        </tr>
        <tr>
          <td><?php echo $entry_status; ?></td>
          <td><select name="svea_fakt_status">
              <?php if ($svea_fakt_status) { ?>
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
          <td><input type="text" name="svea_fakt_sort_order" value="<?php echo $svea_fakt_sort_order; ?>" size="1" /></td>
        </tr>
		<tr>
          <td><?php echo $entry_testmode; ?>:</td>
          <td><select name="svea_fakt_testmode">
				<option value="1" <?php if($svea_fakt_testmode == '1'){ echo 'selected="selected"';}?> ><?php echo $text_enabled; ?></option>
				<option value="0" <?php if($svea_fakt_testmode == '0'){ echo 'selected="selected"';}?> ><?php echo $text_disabled; ?></option>
				</select>
		  </td>
        </tr>
        
        <tr>
            <td><?php echo $entry_sweden; ?></td>
            <td></td>
        </tr>
        <tr>
            <td><?php echo $entry_username_SE; ?>:</td>
            <td>
                <input name="svea_fakt_username_SE" type="text" value="<?php echo $svea_fakt_username_SE; ?>" />
            </td>
        </tr>
        <tr>
            <td><?php echo $entry_password_SE; ?>:</td>
            <td>
                <input name="svea_fakt_password_SE" type="password" value="<?php echo $svea_fakt_password_SE; ?>" />
            </td>
        </tr>
        <tr>
            <td><?php echo $entry_clientno_SE; ?>:</td>
            <td>
                <input name="svea_fakt_clientno_SE" type="text" value="<?php echo $svea_fakt_clientno_SE; ?>" />
            </td>
        </tr>
        <tr>
            <td><?php echo $entry_invoicefee_SE; ?>:</td>
            <td>
                <input name="svea_invoicefee_SE" type="text" value="<?php echo $svea_invoicefee_SE; ?>" /> kr
            </td>
        </tr>
        
        
        <tr>
            <td><?php echo $entry_netherlands; ?></td>
            <td></td>
        </tr>
        <tr>
            <td><?php echo $entry_username_NL; ?>:</td>
            <td>
                <input name="svea_fakt_username_NL" type="text" value="<?php echo $svea_fakt_username_NL; ?>" />
            </td>
        </tr>
        <tr>
            <td><?php echo $entry_password_NL; ?>:</td>
            <td>
                <input name="svea_fakt_password_NL" type="password" value="<?php echo $svea_fakt_password_NL; ?>" />
            </td>
        </tr>
        <tr>
            <td><?php echo $entry_clientno_NL; ?>:</td>
            <td>
                <input name="svea_fakt_clientno_NL" type="text" value="<?php echo $svea_fakt_clientno_NL; ?>" />
            </td>
        </tr>
        <tr>
            <td><?php echo $entry_invoicefee_NL; ?>:</td>
            <td>
                <input name="svea_invoicefee_NL" type="text" value="<?php echo $svea_invoicefee_NL; ?>" /> Euro
            </td>
        </tr>
      </table>
    </form>
  </div>
</div>
<?php echo $footer; ?>