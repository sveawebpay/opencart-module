<div class="buttons">

    <div class="right">
        Personnr: <input type="text" id="ssn" name="ssn" maxlength="10" /><br /><br />
        Privatperson eller företag: <select id="svea_invoice_company" name="svea_invoice_company">
            <option value="true">Företag</option>
            <option value="false" selected="selected">Privat</option>
        </select><br /><br />
        <?php if($countryCode == "SE" || $countryCode == "DK" || $countryCode == "NO"){?>
        <a id="getSSN" class="button"><span>Hämta personnr</span></a>
        <?php } ?>
    </div>
    <br />
    
    <div id="svea_invoice_err" class="right" style="color:red; clear:both; "></div><br />
    
    <div id="svea_invoice_div" class="right" style="clear:both;">
        <br />
        Faktureringsadress:
        <select name="svea_invoice_address" id="svea_invoice_address">
        </select>
        <br /><br />
    </div>
    <div class="right" style="clear:both;"><a id="checkout" class="button"><span><?php echo $button_confirm; ?></span></a></div><br />
    
    
    <br />

</div>
<style>
#SveaAddressDiv{margin:10px 0;}
</style>
<script type="text/javascript"><!--


//Loader
var sveaLoading = '<img src="catalog/view/theme/default/image/loading.gif" id="sveaLoading" />';

$('a#checkout').click(function() {
    
    //Show loader
    $(this).parent().after().append(sveaLoading);
    
    var company = $("#svea_invoice_company").val();
    var ssnNo = $('#ssn').val();
    var adressSelector = $('#svea_invoice_address').val();
    
	$.ajax({ 
		type: 'GET',
        data: {ssn: ssnNo, company: company, addSel: adressSelector},
		url: 'index.php?route=payment/svea_invoice/confirm',
		success: function(data) {
            
            var json = JSON.parse(data);
            
            if(json.success){
                location = '<?php echo $continue; ?>';
            }else{
                alert(json.error);
            }
            
            $('#sveaLoading').remove();         
		}		
	});
});
<?php if (isset($invoiceFee)): ?>
$('div.checkout-product table tfoot').append('<tr><td class="price" colspan="4"><b>Invoice fee:</b></td><td class="total"><?php echo $invoiceFee;?> kr</td></tr>');
<?php endif; ?>

<?php if($countryCode == "SE"){?>
$("a#checkout").hide();
<?php }?>

$('#svea_invoice_div').hide();


//Get address
$('#getSSN').click(function() {
   
    //Show loader
    $(this).parent().after().append(sveaLoading);
   
    var company = $("#svea_invoice_company").val();
    var ssnNo = $('#ssn').val();
    $("#svea_invoice_err").empty();
    $("#svea_invoice_address").empty();
    $("#svea_invoice_div").empty();
    
    if(ssnNo == ''){
        alert('Vänligen fyll i personnr');
    }else{

    	$.ajax({ 
    		type: 'GET',
    		url: 'index.php?route=payment/svea_invoice/getAddress',
            data: {ssn: ssnNo, company: company},
    		success: function(msg) {
                var json = JSON.parse(msg);
                
                //on error
                if (json.error){
                    
                    $("#svea_invoice_err").show().append('<br>'+json.error);
                    
                }else{
                    
                    if (json.length > 1){
                        $.each(json,function(key,value){
                            $("#svea_invoice_address").append('<option id="adress" value="'+value.addressSelector+'">'+value.fullName+' '+value.street+' '+value.zipCode+' '+value.locality+'</option>');
                        });
                        
                        $("#svea_invoice_address").show();
                        
                    }else{
                        $("#svea_invoice_address").hide();
                        $("#svea_invoice_div").append('<div id="SveaAddressDiv">Faktureringsadress:<br><strong>'+json[0].fullName+'</strong><br> '+json[0].street+' <br>'+json[0].zipCode+' '+json[0].locality+'</div>');
                    }

                    $("#svea_invoice_div").show();
                    $("#svea_invoice_err").hide();
                    $("a#checkout").show();
                    
                    
                }
                    
                $('#sveaLoading').remove();    
                
    		}		
    	});
    }
});
//--></script>
