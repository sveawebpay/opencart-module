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
          <td><select name="svea_delbet_order_status_id">
              <?php foreach ($order_statuses as $order_status) { ?>
              <?php if ($order_status['order_status_id'] == $svea_delbet_order_status_id) { ?>
              <option value="<?php echo $order_status['order_status_id']; ?>" selected="selected"><?php echo $order_status['name']; ?></option>
              <?php } else { ?>
              <option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
              <?php } ?>
              <?php } ?>
            </select></td>
        </tr>
        <tr>
          <td><?php echo $entry_geo_zone; ?></td>
          <td><select name="svea_delbet_geo_zone_id">
              <option value="0"><?php echo $text_all_zones; ?></option>
              <?php foreach ($geo_zones as $geo_zone) { ?>
              <?php if ($geo_zone['geo_zone_id'] == $svea_delbet_geo_zone_id) { ?>
              <option value="<?php echo $geo_zone['geo_zone_id']; ?>" selected="selected"><?php echo $geo_zone['name']; ?></option>
              <?php } else { ?>
              <option value="<?php echo $geo_zone['geo_zone_id']; ?>"><?php echo $geo_zone['name']; ?></option>
              <?php } ?>
              <?php } ?>
            </select></td>
        </tr>
        <tr>
          <td><?php echo $entry_status; ?></td>
          <td><select name="svea_delbet_status">
              <?php if ($svea_delbet_status) { ?>
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
          <td><input type="text" name="svea_delbet_sort_order" value="<?php echo $svea_delbet_sort_order; ?>" size="1" /></td>
        </tr>
		<tr>
          <td><?php echo $entry_testmode; ?>:</td>
          <td><select name="svea_delbet_testmode">
				<option value="1" <?php if($svea_delbet_testmode == '1'){ echo 'selected="selected"';}?> ><?php echo $text_enabled; ?></option>
				<option value="0" <?php if($svea_delbet_testmode == '0'){ echo 'selected="selected"';}?> ><?php echo $text_disabled; ?></option>
				</select>
		  </td>
        </tr>
        <tr>
            <td><?php echo $entry_username; ?>:</td>
            <td>
                <input name="svea_username" type="text" value="<?php echo $svea_username; ?>" />
            </td>
        </tr>
        <tr>
            <td><?php echo $entry_password; ?>:</td>
            <td>
                <input name="svea_password" type="password" value="<?php echo $svea_password; ?>" />
            </td>
        </tr>
        <tr>
            <td><?php echo $entry_clientno; ?>:</td>
            <td>
                <input name="svea_delbet_clientno" type="text" value="<?php echo $svea_delbet_clientno; ?>" />
            </td>
        </tr>

      </table>
    </form>
  </div>
</div>
<?php echo $footer; ?>