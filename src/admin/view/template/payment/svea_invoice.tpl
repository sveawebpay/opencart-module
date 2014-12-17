<?php echo $header; ?><?php echo $column_left; ?>
<div id="content">
    <div class="page-header">
      <div class="container-fluid">
        <div class="pull-right">
          <button type="submit" onclick="$('#form').submit();" form="form-sveainvoice" data-toggle="tooltip" title="<?php echo $button_save; ?>" class="btn btn-primary"><i class="fa fa-save"></i></button>
          <a href="<?php echo $cancel; ?>" data-toggle="tooltip" title="<?php echo $button_cancel; ?>" class="btn btn-default"><i class="fa fa-reply"></i></a></div>
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
    <div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> <?php echo $error_warning; ?>
        <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
    <?php } ?>
    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title"><i class="fa fa-pencil"></i> </h3>
        </div>
    <div class="panel-body">
        <!--general settings -->
        <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form-sveainvoice" class="form-horizontal">
                <div class="form-group">
                    <div class="col-sm-3 control-label">Version</div>
                    <div class="col-sm-9">3.0.4</div>
                </div>
                <div class="form-group">
                    <label for="svea_invoice_geo_zone_id" class="col-sm-3 control-label"><?php echo $entry_geo_zone; ?></label>
                    <div class="col-sm-9">
                        <select name="svea_invoice_geo_zone_id">
                            <option value="0"><?php echo $text_all_zones; ?></option>
                            <?php foreach ($geo_zones as $geo_zone) { ?>
                            <?php if ($geo_zone['geo_zone_id'] == $svea_invoice_geo_zone_id) { ?>
                            <option value="<?php echo $geo_zone['geo_zone_id']; ?>" selected="selected"><?php echo $geo_zone['name']; ?></option>
                            <?php } else { ?>
                            <option value="<?php echo $geo_zone['geo_zone_id']; ?>"><?php echo $geo_zone['name']; ?></option>
                            <?php } ?>
                            <?php } ?>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label for="svea_invoice_status" class="col-sm-3 control-label"><?php echo $entry_status; ?></label>
                    <div class="col-sm-9">
                        <select name="svea_invoice_status">
                            <?php if ($svea_invoice_status) { ?>
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
                    <label for="svea_invoice_sort_order" class="col-sm-3 control-label"><?php echo $entry_sort_order; ?></label>
                    <div class="col-sm-9">
                       <input type="text" name="svea_invoice_sort_order" value="<?php echo $svea_invoice_sort_order; ?>" size="1" />
                    </div>
                </div>
                <div class="form-group">
                    <label for="svea_invoice_payment_description" class="col-sm-3 control-label"><?php echo $entry_payment_description; ?></label>
                    <div class="col-sm-9">
                     <textarea rows="2" cols="30" name="svea_invoice_payment_description"><?php echo $svea_invoice_payment_description; ?></textarea>
                    </div>
                </div>
                    <!-- order statuses -->
                <div class="form-group">
                    <h5 class="col-sm-3 control-label"><?php echo $entry_order_status; ?></h5>
                    <div class="col-sm-9"></div>
                </div>
                <div class="form-group">
                    <label for="svea_invoice_order_status_id" class="col-sm-3 control-label">
                       <?php echo $entry_status_order; ?>
                    </label>
                     <div class="col-sm-9">
                          <select name="svea_invoice_order_status_id">
                            <?php foreach ($order_statuses as $order_status) { ?>
                            <?php if ($order_status['order_status_id'] == $svea_invoice_order_status_id) { ?>
                            <option value="<?php echo $order_status['order_status_id']; ?>" selected="selected"><?php echo $order_status['name']; ?></option>
                            <?php } else { ?>
                            <option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
                            <?php } ?>
                            <?php } ?>
                        </select>
                     </div>
                </div>
              <div class="form-group">
                  <label for="svea_invoice_deliver_status_id" class="col-sm-3 control-label">
                        <span data-toggle="tooltip" title="<?php echo $entry_status_delivered_text; ?>"><?php echo $entry_status_delivered; ?></span>
                    </label>
                     <div class="col-sm-9">
                        <select name="svea_invoice_deliver_status_id">
                            <?php foreach ($order_statuses as $deliver_status) { ?>
                            <?php if ($deliver_status['order_status_id'] == $svea_invoice_deliver_status_id) { ?>
                            <option value="<?php echo $deliver_status['order_status_id']; ?>" selected="selected"><?php echo $deliver_status['name']; ?></option>
                            <?php } else { ?>
                            <option value="<?php echo $deliver_status['order_status_id']; ?>"><?php echo $deliver_status['name']; ?></option>
                            <?php } ?>
                            <?php } ?>
                        </select>
                     </div>
                </div>
              <div class="form-group">
                  <label for="svea_invoice_canceled_status_id" class="col-sm-3 control-label">
                        <span data-toggle="tooltip" title="<?php echo $entry_status_canceled_text; ?>"><?php echo $entry_status_canceled; ?></span>
                    </label>
                     <div class="col-sm-9">
                         <select name="svea_invoice_canceled_status_id">
                            <?php foreach ($order_statuses as $order_status) {
                                if ($order_status['order_status_id'] == $svea_invoice_canceled_status_id) { ?>
                             <option value="<?php echo $order_status['order_status_id']; ?>" selected="selected"><?php echo $order_status['name']; ?></option>
                                 <?php } else { ?>
                             <option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
                                  <?php } ?>
                             <?php } ?>
                        </select>
                     </div>
                </div>
              <div class="form-group">
                    <label for="svea_invoice_refunded_status_id" class="col-sm-3 control-label">
                        <span data-toggle="tooltip" title="<?php echo $entry_status_refunded_text; ?>"><?php echo $entry_status_refunded; ?></span>
                    </label>
                     <div class="col-sm-9">
                        <select name="svea_invoice_refunded_status_id">
                            <?php foreach ($order_statuses as $order_status) {
                                if ($order_status['order_status_id'] == $svea_invoice_refunded_status_id) { ?>
                             <option value="<?php echo $order_status['order_status_id']; ?>" selected="selected"><?php echo $order_status['name']; ?></option>
                                 <?php } else { ?>
                             <option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
                                  <?php } ?>
                             <?php } ?>
                        </select>
                     </div>
                </div>
                    <!--shipping billing-->
                    <div class="form-group">
                        <label for="svea_invoice_shipping_billing" class="col-sm-3 control-label">
                            <span data-toggle="tooltip" title="<?php echo $entry_shipping_billing_text; ?>"><?php echo $entry_shipping_billing; ?></span>
                        </label>
                         <div class="col-sm-9">
                           <?php if ($svea_invoice_shipping_billing === "0") { ?>
                            <input type="radio" name="svea_invoice_shipping_billing" value="1" />
                            <?php echo $entry_yes; ?>
                            <input type="radio" name="svea_invoice_shipping_billing" value="0" checked="checked" />
                            <?php echo $entry_no; ?>
                            <?php } else { ?>
                            <input type="radio" name="svea_invoice_shipping_billing" value="1" checked="checked" />
                            <?php echo $entry_yes; ?>
                            <input type="radio" name="svea_invoice_shipping_billing" value="0" />
                            <?php echo $entry_no; ?>
                            <?php } ?>
                         </div>
                    </div>
                    <!-- autodeliver -->
                    <div class="form-group">
                        <label for="svea_invoice_auto_deliver" class="col-sm-3 control-label">
                            <span data-toggle="tooltip" title="<?php echo $entry_auto_deliver_text; ?>"><?php echo $entry_auto_deliver; ?></span>
                        </label>
                         <div class="col-sm-9">
                            <select name="svea_invoice_auto_deliver">
                                <option value="0" <?php if($svea_invoice_auto_deliver == '0'){ echo 'selected="selected"';}?> ><?php echo $text_disabled; ?></option>
                                <option value="1" <?php if($svea_invoice_auto_deliver == '1'){ echo 'selected="selected"';}?> ><?php echo $text_enabled; ?></option>
                            </select>
                         </div>
                    </div>
                    <div class="form-group">
                        <label for="entry_distribution_type" class="col-sm-3 control-label">
                            <?php echo $entry_distribution_type; ?>
                        </label>
                        <div class="col-sm-9">
                            <select name="svea_invoice_distribution_type">
                                <option value="Post" <?php if($svea_invoice_distribution_type == 'Post'){ echo 'selected="selected"';}?> ><?php echo $entry_post; ?></option>
                                <option value="Email" <?php if($svea_invoice_distribution_type == 'Email'){ echo 'selected="selected"';}?> ><?php echo $entry_email; ?></option>
                            </select>
                         </div>
                    </div>
                    <div class="form-group">
                        <label for="svea_invoice_product_price" class="col-sm-3 control-label">
                            <span data-toggle="tooltip" title="<?php echo $entry_product_text; ?>"><?php echo $entry_product; ?></span>
                        </label>
                        <div class="col-sm-9">
                        <div class="col-sm-4">
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
                         </div>
                        <div class="col-sm-4">
                              <?php echo $entry_min_amount; ?>
                            <input name="svea_invoice_product_price_min" type="text"
                            value="<?php echo $svea_invoice_product_price_min; ?>" />
                        </div>
                    </div>
                </div>
            <!-- Countrycode specific -->
            <div class="tab-content" id="tab-invoice" >
                <ul class="nav nav-tabs" id="svea_country">
                    <?php foreach ($credentials as $code) { ?>
                    <li><a href="#tab-<?php echo $code['lang'] ?>" data-toggle="tab"><?php echo $code['lang']; ?></a></li>
                    <?php } ?>
                </ul>
                <div class="tab-content">
                <?php foreach($credentials as $code){ ?>
                    <div class="tab-pane" id="tab-<?php echo $code['lang']; ?>">
                        <div class="form-group">
                            <label class="col-sm-3 control-label" for="<?php echo $code['name_testmode']; ?>"><?php echo $entry_testmode; ?></label>
                            <div class="col-sm-9">
                                <select name="<?php echo $code['name_testmode']; ?>">
                                    <option value="1" <?php if($code['value_testmode'] == '1'){ echo 'selected="selected"';}?> ><?php echo $text_enabled; ?></option>
                                    <option value="0" <?php if($code['value_testmode'] == '0'){ echo 'selected="selected"';}?> ><?php echo $text_disabled; ?></option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label" for="<?php echo $code['name_username']; ?>"><?php echo $entry_username; ?></label>
                            <div class="col-sm-9">
                                  <input name="<?php echo $code['name_username']; ?>" type="text"
                                           value="<?php echo $code['value_username']; ?>" />
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label" for="<?php echo $code['name_password']; ?>"><?php echo $entry_password; ?></label>
                            <div class="col-sm-9">
                                 <input name="<?php echo $code['name_password']; ?>" type="text"
                                           value="<?php echo $code['value_password']; ?>" />
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label" for="<?php echo $code['name_clientno']; ?>"><?php echo $entry_clientno; ?></label>
                            <div class="col-sm-9">
                                 <input name="<?php echo $code['name_clientno']; ?>" type="text"
                                           value="<?php echo $code['value_clientno']; ?>" />
                            </div>
                        </div>
                    </div>
                <?php } ?>
                </div>
            </div>
        </form>
    </div>
    </div><!-- panel-default -->
  </div><!-- container-fluid -->
<div style="height:100px"></div>
  <script type="text/javascript"><!--
$('#svea_country a:first').tab('show');
//--></script>
<?php echo $footer; ?>