<?php
    /*
    if (isset($_SESSION['error_warning'])){ 
    ?>
        <div class="right" style="color:red"><?php echo $_SESSION['error_warning'] ?></div><br />
       
    <?php unset($_SESSION['error_warning']);    
   }
   */
  ?>
<div class="buttons">
 
<div class="right">
    <?php 
        echo $form_start_tag,
            $input_message,
            $merchant_id,
            $input_mac;
    ?>
    <input id="checkout" class="button" type='submit' name='submit' value='<?php echo $submitMessage ?>' />
    <?php
        echo $form_end_tag;
    ?>
  
   <!-- <a id="checkout" class="button" href="<?php echo $continue; ?>"><span><?php echo $button_confirm; ?></span></a>-->
</div>
</div>