<div class="buttons">

    <div class="right">
        Personnr: <input type="text" id="ssn" name="ssn" maxlength="10" /><br />
        Privatperson eller företag: <select id="svea_fakt_company" name="svea_fakt_company">
            <option value="true">Företag</option>
            <option value="false" selected="selected">Privat</option>
        </select><br /><br />
        <?php if($countryCode == "SE"){?>
        <a id="getSSN" class="button"><span>Hämta personnr</span></a>
        <?php } ?>
    </div>
    <br />
    
    <div id="svea_fakt_err" class="right" style="color:red; clear:both; "></div><br />
    
    <div id="svea_fakt_div" class="right" style="clear:both;">
        Faktureringsadress:
        <select name="svea_fakt_address" id="svea_fakt_address">
        </select>
        <br /><br />
    </div>
    <div class="right" style="clear:both;"><a id="checkout" class="button"><span><?php echo $button_confirm; ?></span></a></div><br />
    
    
    <br />

</div>
<script type="text/javascript"><!--
$('a#checkout').click(function() {
    
    var company = $("#svea_fakt_company").val();
    var ssnNo = $('#ssn').val();
    var adressSelector = $('#svea_fakt_address').val();
    
	$.ajax({ 
		type: 'GET',
        data: {ssn: ssnNo, company: company, addSel: adressSelector},
		url: 'index.php?route=payment/svea_fakt/confirm',
		success: function(data) {
            if (data == 978){
                location = '<?php echo $continue; ?>';
            }else{
                alert(data);
            }          
		}		
	});
});
<?php if (isset($invoiceFee)): ?>
$('div.checkout-product table tfoot').append('<tr><td></td><td></td><td></td><td>Tillägg faktura:</td><td><?php echo $invoiceFee;?> kr</td></tr>');
<?php endif; ?>

<?php if($countryCode == "SE"){?>
$("a#checkout").hide();
<?php }?>

$('#svea_fakt_div').hide();

$('#getSSN').click(function() {
   
    var company = $("#svea_fakt_company").val();
    var ssnNo = $('#ssn').val();
    $("#svea_fakt_err").empty().append(' ');
    $("#svea_fakt_address").empty();
    
    if(ssnNo == ''){
        alert('Vänligen fyll i personnr');
    }else{

    	$.ajax({ 
    		type: 'GET',
    		url: 'index.php?route=payment/svea_fakt/getAddress',
            data: {ssn: ssnNo, company: company},
    		success: function(msg) {
                eval(msg);
    		}		
    	});
    }
});
//--></script>
