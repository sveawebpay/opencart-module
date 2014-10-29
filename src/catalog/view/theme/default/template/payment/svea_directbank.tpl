<div class="buttons">
        <form id="sveaForm" action="<?php echo $continue; ?>" method="post">
            <div><p><?php echo $logo; ?></p></div>
            <table class="radio">
            <?php
            $i = 0;
            foreach ($sveaMethods as $sveaM){
                if(substr($sveaM, 0, 2) == "DB"){
                    echo ' <div class="form-group">';
                    $checked = $i == 0 ? "checked" : "";

                    echo '

                                <div class="col-sm-9">
                                 <input '.$checked. ' id="svea_'.$sveaM.'" type="radio" value="'.$sveaM.'" name="svea_directbank_payment_method">
                                 <img src="'.$svea_banks_base.$sveaM.'.png" >
                           
                           '
                        ;
                        $i++;
                    echo '</div>';
                }
            }

            ?>
            </table>

            <div class="buttons">
                <div class="pull-right">
                    <input id="checkout_choose" class="btn btn-primary" type='submit' name='submit' value='<?php echo $button_continue; ?>' />
                </div>
            </div>
        </form>
