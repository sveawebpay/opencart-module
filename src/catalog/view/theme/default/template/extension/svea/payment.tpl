<?php if ($snippet) { ?>
    <div id="sco-snippet-wrapper"><?php echo $snippet; ?></div>
<?php } else { ?>
    <p><b><?php echo $heading_error; ?></b><br/><?php echo $error_unknown; ?></p>
<?php } ?>