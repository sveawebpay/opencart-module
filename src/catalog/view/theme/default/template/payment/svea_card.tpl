<div class="buttons">
 
<div class="right">
    <div><p><?php echo $logo; ?></p></div>
    
    <?php 
        echo $form_start_tag,
            $input_message,
            $merchant_id,
            $input_mac;
    ?>
    
    
    
    <?php if(floatval(VERSION) >= 1.5){?>
    <input id="checkout" class="button" type='submit' name='submit' value='<?php echo $submitMessage ?>' />
    <?php }else{ ?>
  
    <a id="checkout" class="button" onclick="document.paymentForm.submit()"><span><?php echo $button_confirm; ?></span></a>
    <?php }?>
   <?php
        echo $form_end_tag;
    ?>
</div>
</div>