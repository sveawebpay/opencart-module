<?php echo $header; ?><?php echo $column_left; ?>
<div id="content">
    <div class="page-header">
        <div class="container-fluid">
            <div class="pull-right">
                <button type="submit" onclick="$('#form').submit();" form="form-svea_fee" data-toggle="tooltip" title="<?php echo $button_save; ?>" class="btn btn-primary"><i class="fa fa-save"></i></button>
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
            <!--<div class="buttons"><a onclick="$('#form').submit();" class="button"><span><?php echo $button_save; ?></span></a><a onclick="location = '<?php echo $cancel; ?>';" class="button"><span><?php echo $button_cancel; ?></span></a></div>-->
            <div class="panel-body">
                <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form-svea_fee" class="form-horizontal">
                    <!-- common to all countries -->
                    <div class="form-group">
                        <label class="col-sm-3 control-label">Version</label>
                        <div class="col-sm-9"><?php echo $svea_version; ?></div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label" for="input-sort-order"><?php echo $entry_sort_order; ?></label>
                        <div class="col-sm-10">
                            <input type="text" name="svea_fee_sort_order" value="<?php echo $svea_fee_sort_order; ?>" placeholder="<?php echo $entry_sort_order; ?>" id="input-sort-order" class="form-control" />
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
                                    <label class="col-sm-3 control-label" for="svea_fee_status_<?php echo $code['lang']; ?>"><?php echo $entry_status; ?></label>
                                    <div class="col-sm-9">
                                        <select name="svea_fee_status_<?php echo $code['lang']; ?>">
                                            <?php if ($code['svea_fee_status']) { ?>
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
                                    <label class="col-sm-3 control-label" for="svea_fee_fee_<?php echo $code['lang']; ?>"><?php echo $entry_fee; ?></label>
                                    <div class="col-sm-9">
                                        <input name="svea_fee_fee_<?php echo $code['lang']; ?>" type="text"
                                               value="<?php echo $code['svea_fee_fee']; ?>" />
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-3 control-label" for="svea_fee_tax_class_<?php echo $code['lang']; ?>"><?php echo $entry_tax_class; ?></label>
                                    <div class="col-sm-9">
                                        <select name="svea_fee_tax_class_<?php echo $code['lang']; ?>">
                                            <option value="0"><?php echo $text_none; ?></option>
                                            <?php foreach ($tax_classes as $tax_class) { ?>
                                            <?php if ($tax_class['tax_class_id'] == $code['svea_fee_tax_class']) { ?>
                                            <option value="<?php echo $tax_class['tax_class_id']; ?>" selected="selected"><?php echo $tax_class['title']; ?></option>
                                            <?php } else { ?>
                                            <option value="<?php echo $tax_class['tax_class_id']; ?>"><?php echo $tax_class['title']; ?></option>
                                            <?php } ?>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="svea_fee_sort_order_<?php echo $code['lang']; ?>" class="col-sm-3 control-label"><?php echo $entry_sort_order; ?></label>
                                    <div class="col-sm-9">
                                        <input type="text" name="svea_fee_sort_order_<?php echo $code['lang']; ?>" value="<?php echo $code['svea_fee_sort_order']; ?>" size="1" />
                                    </div>
                                </div>
                            </div>
                            <?php } ?>
                        </div>
                </form>
            </div>
        </div>
    </div>
    <script type="text/javascript"><!--
        $('#svea_country a:first').tab('show');
        //--></script></div>
<?php echo $footer; ?>
