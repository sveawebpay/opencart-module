<div class="buttons">

    <div class="right">
        
        Privatperson eller företag: <select id="svea_invoice_company" name="svea_invoice_company">
            <option value="true">Företag</option>
            <option value="false" selected="selected">Privat</option>
        </select><br /><br />
        
        <?php if($countryCode == "SE" || $countryCode == "DK" || $countryCode == "NO"){?>
        Personnr: <input type="text" id="ssn" name="ssn" /><br /><br />
        <a id="getSSN" class="button"><span>Hämta personnr</span></a>
        <?php }?>
    </div>
    <br />
    
    <div id="svea_invoice_err" class="right" style="color:red; clear:both; "></div><br />
    
    
    <?php if($countryCode == "SE" || $countryCode == "DK" || $countryCode == "NO"){ ?>
    <div id="svea_invoice_div" class="right" style="clear:both;">
        <br />
        Faktureringsadress:
        <select name="svea_invoice_address" id="svea_invoice_address">
        </select>
        
    <?php }else{    
        
        //Days, to 31
        $days = "";
        $zero = "";
        for($d = 1; $d <= 31; $d++){
            
            $val = $d;
            if($d < 10)
                $val = "$d";
                
            $days .= "<option value='$val'>$d</option>";
        }
        $birthDay = "<select name='birthDay' id='birthDay'>$days</select>";
        
        
        //Months to 12
        $months = "";
        for($m = 1; $m <= 12; $m++){
            $val = $m;
            if($m < 10)
                $val = "$m";
            
            $months .= "<option value='$val'>$m</option>";
        }
        $birthMonth = "<select name='birthMonth' id='birthMonth'>$months</select>";
        
        //Years from 1913 to 1996
        $years = '';
        for($y = 1913; $y <= 1996; $y++){    
            $years .= "<option value='$y'>$y</option>";
        }
        $birthYear = '<select name="birthYear" id="birthYear">'.$years.'</select>';
        ?>
        
        <div class="right" style="clear:both;">
         <span id="sveaBirthDateCont">Birthdate: <?php  echo "$birthDay $birthMonth $birthYear"; ?><br /><br />
         Initials: <input type="text" id="initials" name="initials" />
         </span>
         <span id="sveaVatNoCont">VATno: <input type="text" id="vatno" name="vatno" /><br /><br /></span>

         <?php } ?>
     </div>
     
    <div class="right" style="clear:both;"><br /><br /><a id="checkout" class="button"><span><?php echo $button_confirm; ?></span></a></div><br />
    
    
    <br />

</div>
<style>
#SveaAddressDiv{margin:10px 0;}
</style>
<script type="text/javascript"><!--

$('#svea_invoice_div').hide();
$('#sveaVatNoCont').hide();

$("#svea_invoice_company").change(function(){
    
    if ($(this).val() == "true"){
        $('#sveaVatNoCont').show();
        $('#sveaBirthDateCont').hide();
    }else{
        $('#sveaVatNoCont').hide();
        $('#sveaBirthDateCont').show();
    }
    
});

//Loader
var sveaLoading = '<img src="catalog/view/theme/default/image/loading.gif" id="sveaLoading" />';

$('a#checkout').click(function() {
    
    //Show loader
    $(this).parent().after().append(sveaLoading);
    
    var company = $("#svea_invoice_company").val();
    var ssnNo = $('#ssn').val();
    var adressSelector = $('#svea_invoice_address').val();
    var Initials = $("#initials").val();
    var birthDay = $("#birthDay").val();
    var birthMonth = $("#birthMonth").val();
    var birthYear = $("#birthYear").val();
    var vatNo = $('#vatno').val();
    
	$.ajax({ 
		type: 'GET',
        data: {ssn: ssnNo, company: company, addSel: adressSelector, initials: Initials, birthDay: birthDay, birthMonth: birthMonth, birthYear: birthYear, vatno: vatNo },
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


<?php if($countryCode == "SE"){?>
$("a#checkout").hide();
<?php }?>




//Get address
$('#getSSN').click(function() {
   
    //Show loader
    $(this).parent().after().append(sveaLoading);
   
    var company = $("#svea_invoice_company").val();
    var ssnNo = $('#ssn').val();
    $("#svea_invoice_err").empty();
    $("#svea_invoice_address").empty();
    $("#svea_invoice_div").hide();
    
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
                            $("#svea_invoice_address").append('<option value="'+value.addressSelector+'">'+value.fullName+' '+value.street+' '+value.zipCode+' '+value.locality+'</option>');
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
