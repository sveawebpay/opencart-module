<div class="buttons">
        <form id="sveaForm" action="<?php echo $continue; ?>" method="post">
            <div><p><?php echo $logo; ?></p></div>
            <table class="radio">
            <?php
            $i = 0;
            foreach ($sveaMethods as $sveaM){
                if(substr($sveaM, 0, 2) == "DB"){
                    echo ' <tr class="highlight">';
                    $checked = $i == 0 ? "checked" : "";

                    echo '
                                <td>
                                    <input '.$checked. ' id="svea_'.$sveaM.'" type="radio" value="'.$sveaM.'" name="svea_directbank_payment_method">
                                </td>
                                <td><img src="'.$svea_banks_base.$sveaM.'.png" ></td>
                           '
                        ;
                        $i++;
                    echo '</tr>';
                }
            }

            ?>
            </table>

                <div class="right">
            <?php if(floatval(VERSION) >= 1.5){?>
                <input id="checkout_choose" class="button" type='submit' name='submit' value='<?php echo $button_continue; ?>' />
                <?php }else{ ?>
                    <a id="checkout_choose" class="button" onclick="$('#sveaForm').submit()"><span><?php echo $button_continue; ?></span></a>
            <?php }?>
                </div>
        </form>
 