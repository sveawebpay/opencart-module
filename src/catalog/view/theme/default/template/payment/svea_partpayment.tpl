<div class="buttons">
    <div class="right">
        <?php if (isset($partpayment_fail)):
            echo $partpayment_fail;
        else: ?>
            Personnr: <input type="text" id="ssn" name="ssn" maxlength="10" /><a id="getPlan" class="button"><span>Payment Options</span></a>
        <?php endif; ?>
    </div>
    
    <br />
    <div id="svea_partpayment_err" class="right" style="color:red; clear:both; margin-top:15px;">
    </div>
    
    <div class="right" id="svea_partpayment_tr" style="clear:both; margin-top:15px;">
        Faktureringsadress:
        <select name="svea_partpayment_address" id="svea_partpayment_address">
        </select>
    </div>
    
    <br />
    <div class="right" id="svea_partpaymentalt_tr" style="clear:both; margin-top:15px;">
        Betalningsalternativ:
        <select name="svea_partpayment_alt" id="svea_partpayment_alt">
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
    //var adressSelector = $('#svea_partpayment_address').val();
    var paymentSelector = $('#svea_partpayment_alt').val();
	$.ajax({ 
		type: 'GET',
        data: {ssn: ssnNo, paySel: paymentSelector},// addSel: adressSelector,
		url: 'index.php?route=payment/svea_partpayment/confirm',
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
$('#svea_partpayment_tr').hide();
$('#svea_partpaymentalt_tr').hide();

$('#getPlan').click(function() {
   
    var ssnNo = $('#ssn').val();
    $("#svea_partpayment_err").empty();
    $("#svea_partpayment_address").empty();
    $("#svea_partpayment_alt").empty();
    
    if(ssnNo == ''){
        alert('Vänligen fyll i personnr');
    }else{
        getPaymentOptions();
        /*
    	$.ajax({ 
    		type: 'GET',
    		url: 'index.php?route=payment/svea_partpayment/getAddress',
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
    		url: 'index.php?route=payment/svea_partpayment/getPaymentOptions',
            data: {pay:1},
    		success: function(msg) {
                eval(msg);
                $('#svea_partpaymentalt_tr').show();
    		}		
    	});
}
//--></script>
