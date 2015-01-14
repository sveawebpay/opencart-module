<?php echo $header; ?>
<div id="content">

    <div class="breadcrumb">
        <?php foreach ($breadcrumbs as $breadcrumb) { ?>
        <?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
        <?php } ?>
    </div>

    <?php if ($error_warning) { ?>
    <div class="warning"><?php echo $error_warning; ?></div>
    <?php } ?>
    <div class="box">
        <div class="heading">
            <h1><img src="view/image/total.png" alt="" /> <?php echo $heading_title; ?></h1>
            <div class="buttons"><a onclick="$('#form').submit();" class="button"><span><?php echo $button_save; ?></span></a><a onclick="location = '<?php echo $cancel; ?>';" class="button"><span><?php echo $button_cancel; ?></span></a></div>
        </div>
        <div class="content">
            <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form">

                <!-- common to all countries -->
                <table class="form"><tbody>
                    <tr>
                        <td>Version</td>
                        <td>2.6.8</td>
                    </tr>
                </tbody></table>

                <!-- Countrycode specific -->
                <div id="tab-svea_fee" style="display: inline;">
                    <?php if($version >= 1.5){ ?>
                    <div id="vtabs" class="vtabs">
                        <?php foreach ($credentials as $code){ ?>
                            <a href="#tab-svea_fee_<?php echo $code['lang'] ?>"><?php echo $code['lang'] ?></a>
                        <?php } ?>
                    </div>
                    <?php } ?>
                    <?php foreach($credentials as $code){ ?>
                        <div id="tab-svea_fee_<?php echo $code['lang'] ?>" class="vtabs-content">
                            <?php if($version < 1.5){ ?>
                            <h3><?php echo $code['lang']; ?></h3>
                            <?php } ?>
                            <table class="form"><tbody>

                                <tr>
                                    <td><?php echo $entry_status; ?></td>
                                    <td><select name="svea_fee_status_<?php echo $code['lang']; ?>">
                                        <?php if ($code['svea_fee_status']) { ?>
                                            <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                                            <option value="0"><?php echo $text_disabled; ?></option>
                                        <?php } else { ?>
                                            <option value="1"><?php echo $text_enabled; ?></option>
                                            <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                                        <?php } ?>
                                    </select></td>
                                </tr>

                                <tr>
                                    <td><?php echo $entry_fee; ?></td>
                                    <td><input type="text" name="svea_fee_fee_<?php echo $code['lang']; ?>" value="<?php echo $code['svea_fee_fee']; ?>" /></td>
                                </tr>

                                <tr>
                                    <td><?php echo $entry_tax_class; ?></td>
                                    <td><select name="svea_fee_tax_class_<?php echo $code['lang']; ?>">
                                        <option value="0"><?php echo $text_none; ?></option>
                                        <?php foreach ($tax_classes as $tax_class) { ?>
                                            <?php if ($tax_class['tax_class_id'] == $code['svea_fee_tax_class']) { ?>
                                                <option value="<?php echo $tax_class['tax_class_id']; ?>" selected="selected"><?php echo $tax_class['title']; ?></option>
                                            <?php } else { ?>
                                                <option value="<?php echo $tax_class['tax_class_id']; ?>"><?php echo $tax_class['title']; ?></option>
                                            <?php } ?>
                                        <?php } ?>
                                    </select></td>
                                </tr>

                                <tr>
                                    <td><?php echo $entry_sort_order; ?></td>
                                    <td><input type="text" name="svea_fee_sort_order_<?php echo $code['lang']; ?>" value="<?php echo $code['svea_fee_sort_order']; ?>" size="1" /></td>
                                </tr>
                            </tbody></table> <!-- .form -->
                        </div> <!-- #tab-svea_fee_$code['lang'] -->
                    <?php } ?> <!-- $credentials as $code -->
                </div> <!-- #tab-svea_fee -->
            </form> <!-- #form -->
        </div> <!-- .content -->
    </div> <!-- .box -->
</div> <!-- #content -->
<div style="height:100px"></div>
<script type="text/javascript"><!--
    $('#tab-svea_fee a').tabs();
//--></script>
<?php echo $footer; ?>