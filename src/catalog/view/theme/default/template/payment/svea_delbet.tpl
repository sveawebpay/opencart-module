<div class="buttons">
    <div class="right">
        <?php if (isset($delbet_fail)):
            echo $delbet_fail;
        else: ?>
            Personnr: <input type="text" id="ssn" name="ssn" maxlength="10" /><a id="getPlan" class="button"><span>Payment Options</span></a>
        <?php endif; ?>
    </div>
    
    <br />
    <div id="svea_delbet_err" class="right" style="color:red; clear:both; margin-top:15px;">
    </div>
    
    <div class="right" id="svea_delbet_tr" style="clear:both; margin-top:15px;">
        Faktureringsadress:
        <select name="svea_delbet_address" id="svea_delbet_address">
        </select>
    </div>
    
    <br />
    <div class="right" id="svea_delbetalt_tr" style="clear:both; margin-top:15px;">
        Betalningsalternativ:
        <select name="svea_delbet_alt" id="svea_delbet_alt">
        </select>
    </div>
    <br />
    <div class="right" style="clear:both; margin-top:15px;">
        <a id="checkout"  class="button"><span><?php echo $button_confirm; ?></span></a>
    </div>
  
</div>
<script type="text/javascript"><!--
$('a#checkout').click(function() {
    
    var ssnNo = $('#ssn').val();
    //var adressSelector = $('#svea_delbet_address').val();
    var paymentSelector = $('#svea_delbet_alt').val();
	$.ajax({ 
		type: 'GET',
        data: {ssn: ssnNo, paySel: paymentSelector},// addSel: adressSelector,
		url: 'index.php?route=payment/svea_delbet/confirm',
		success: function(data) {
            if (data == 978){
                location = '<?php echo $continue; ?>';
            }else{
                alert(data);
            }          
		}		
	});
});

$("a#checkout").hide();
$('#svea_delbet_tr').hide();
$('#svea_delbetalt_tr').hide();

$('#getPlan').click(function() {
   
    var ssnNo = $('#ssn').val();
    $("#svea_delbet_err").empty();
    $("#svea_delbet_address").empty();
    $("#svea_delbet_alt").empty();
    
    if(ssnNo == ''){
        alert('Vänligen fyll i personnr');
    }else{
        getPaymentOptions();
        /*
    	$.ajax({ 
    		type: 'GET',
    		url: 'index.php?route=payment/svea_delbet/getAddress',
            data: {ssn: ssnNo},
    		success: function(msg) {    			
                eval(msg);                
    		}		
    	});
        */
    }
});

function getPaymentOptions(){
    
    $.ajax({ 
    		type: 'GET',
    		url: 'index.php?route=payment/svea_delbet/getPaymentOptions',
            data: {pay:1},
    		success: function(msg) {
                eval(msg);
                $('#svea_delbetalt_tr').show();
    		}		
    	});
}
//--></script>
