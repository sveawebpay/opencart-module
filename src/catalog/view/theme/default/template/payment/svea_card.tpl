 <div class="content">

            <div><span><?php echo $logo; ?></span></div>
            <div><span><?php echo $cardLogos; ?></span></div>

            <?php
                echo $form_start_tag,
                    $input_message,
                    $merchant_id,
                    $input_mac;
            ?>



    <div class="buttons">
       <div class="pull-right">
            <input id="checkout" class="btn btn-primary" type='submit' name='submit' value='<?php echo $submitMessage ?>' />
       </div>

       <?php
            echo $form_end_tag;
        ?>
    </div>
</div>
