<?php echo $header; ?><?php echo $column_left; ?>
<div id="content">
    <div class="page-header">
      <div class="container-fluid">
        <div class="pull-right">
          <button type="submit" onclick="$('#form').submit();" form="form-sveadirectbank" data-toggle="tooltip" title="<?php echo $button_save; ?>" class="btn btn-primary"><i class="fa fa-save"></i></button>
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
        <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form-sveadirectbank" class="form-horizontal">
            <div class="form-group">
                <label class="col-sm-2 control-label" for="input-status"><?php echo $entry_version_text; ?></label>
                <div class="col-sm-10" style="padding-top: 9px;">
                    <div><?php echo $entry_version; ?></div>
                    <a href="<?php echo $entry_module_repo; ?>"><?php echo $entry_version_info; ?></a>
                </div>
            </div>
              <div class="form-group">
                    <label for="svea_directbank_testmode" class="col-sm-2 control-label"><?php echo $entry_testmode; ?></label>
                    <div class="col-sm-9">
                        <select class="form-control" name="svea_directbank_testmode">
                            <option value="1" <?php if($svea_directbank_testmode == '1'){ echo 'selected="selected"';}?> ><?php echo $text_enabled; ?></option>
                            <option value="0" <?php if($svea_directbank_testmode == '0'){ echo 'selected="selected"';}?> ><?php echo $text_disabled; ?></option>
                        </select>
                    </div>
                </div>
              <div class="form-group">
                    <label for="svea_geo_zone_id" class="col-sm-2 control-label"><?php echo $entry_geo_zone; ?></label>
                    <div class="col-sm-9">
                         <select class="form-control" name="svea_geo_zone_id">
                            <option value="0"><?php echo $text_all_zones; ?></option>
                            <?php foreach ($geo_zones as $geo_zone) { ?>
                                <?php if ($geo_zone['geo_zone_id'] == $svea_directbank_geo_zone_id) { ?>
                            <option value="<?php echo $geo_zone['geo_zone_id']; ?>" selected="selected"><?php echo $geo_zone['name']; ?></option>
                                <?php } else { ?>
                            <option value="<?php echo $geo_zone['geo_zone_id']; ?>"><?php echo $geo_zone['name']; ?></option>
                                <?php } ?>
                            <?php } ?>
                      </select>
                    </div>
                </div>
              <div class="form-group">
                    <label for="svea_directbank_status" class="col-sm-2 control-label"><?php echo $entry_status; ?></label>
                    <div class="col-sm-9">
                        <select class="form-control" name="svea_directbank_status">
                            <?php if ($svea_directbank_status) { ?>
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
                    <label for="svea_directbank_sort_order" class="col-sm-2 control-label"><?php echo $entry_sort_order; ?></label>
                    <div class="col-sm-9">
                       <input class="form-control" type="text" name="svea_directbank_sort_order" value="<?php echo $svea_directbank_sort_order; ?>" size="1" />
                    </div>
                </div>
              <div class="form-group">
                    <label for="svea_directbank_payment_description" class="col-sm-2 control-label"><?php echo $entry_payment_description; ?></label>
                    <div class="col-sm-9">
                      <textarea class="form-control" rows="2" cols="30" name="svea_directbank_payment_description"><?php echo $svea_directbank_payment_description; ?></textarea>
                    </div>
                </div>
            <div class="form-group">
                <label for="svea_directbank_hide_svea_comments" class="col-sm-2 control-label">
                    <span data-toggle="tooltip" title="<?php echo $entry_hide_svea_comments_tooltip; ?>"><?php echo $entry_hide_svea_comments; ?></span>
                </label>
                <div class="col-sm-9">
                    <select name="svea_directbank_hide_svea_comments"  class="form-control">
                        <?php if ($svea_directbank_hide_svea_comments) { ?>
                        <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                        <option value="0"><?php echo $text_disabled; ?></option>
                        <?php } else { ?>
                        <option value="1"><?php echo $text_enabled; ?></option>
                        <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                        <?php } ?>
                    </select>
                </div>
            </div>
        <!-- Mode specific -->
        <div class="tab-content">
            <ul class="nav nav-tabs" id="svea_merchant_db">
                <li><a href="#tab-directbanktest" data-toggle="tab">Test</a></li>
                <li><a href="#tab-directbankprod" data-toggle="tab">Prod</a></li>
            </ul>
            <div class="tab-content">
                  <!--Test -->
                <div class="tab-pane" id="tab-directbanktest">
                     <div class="form-group">
                        <label class="col-sm-2 control-label" for="svea_directbank_merchant_id_test"><?php echo $entry_merchant_id; ?></label>
                        <div class="col-sm-9">
                            <input class="form-control" name="svea_directbank_merchant_id_test" type="text"
                                    value="<?php echo $value_merchant_test; ?>" />
                        </div>
                    </div>
                     <div class="form-group">
                        <label class="col-sm-2 control-label" for="svea_directbank_sw_test"><?php echo $entry_sw; ?></label>
                        <div class="col-sm-9">
                            <input class="form-control" name="svea_directbank_sw_test" type="text"
                                    value="<?php echo $value_sw_test; ?>" />
                        </div>
                    </div>
                </div>
                <!--Prod -->
                <div class="tab-pane" id="tab-directbankprod">
                     <div class="form-group">
                        <label class="col-sm-2 control-label" for="svea_directbank_merchant_id_prod"><?php echo $entry_merchant_id; ?></label>
                        <div class="col-sm-9">
                            <input class="form-control" name="svea_directbank_merchant_id_prod" type="text"
                                    value="<?php echo $value_merchant_prod; ?>" />
                        </div>
                    </div>
                     <div class="form-group">
                        <label class="col-sm-2 control-label" for="svea_directbank_sw_prod"><?php echo $entry_sw; ?></label>
                        <div class="col-sm-9">
                            <input class="form-control" name="svea_directbank_sw_prod" type="text"
                                    value="<?php echo $value_sw_prod; ?>" />
                        </div>
                    </div>
                </div>
            </div>
        </div>


      </form>
    </div>
    </div><!-- panel-default -->
  </div><!-- container-fluid -->
<div style="height:100px"></div>
  <script type="text/javascript"><!--
$('#svea_merchant_db a:first').tab('show');
//--></script>
<?php echo $footer; ?>