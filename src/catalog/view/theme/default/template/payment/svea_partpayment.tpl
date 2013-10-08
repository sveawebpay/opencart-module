<div class="buttons">
    <div class="right">

        <div><p><?php echo $logo; ?></p></div>

        <?php if($countryCode == "SE" || $countryCode == "DK" || $countryCode == "NO" || $countryCode == "FI"){ ?>
        <?php echo $this->language->get('text_ssn')?>: <input type="text" id="ssn" name="ssn" /><br /><br />
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
            $birthYear = "<select name='birthYear' id='birthYear'>$years</select>";

        ?>
           <span id="sveaBirthDateCont"><?php echo $this->language->get('text_birthdate')?>: <?php echo "$birthDay $birthMonth $birthYear"; ?><br /><br />
        <?php if($countryCode == "NL"){ ?>
           <?php echo $this->language->get('text_initials')?>: <input type="text" id="initials" name="initials" />
        <?php }?>
           </span>
        <?php }?>
        <br /><br /><a id="getPlan" class="button"><span><?php echo $this->language->get('text_get_payment_options')?></span></a>
    </div>

    <br />
    <div id="svea_partpayment_err" class="right" style="color:red; clear:both; margin-top:15px;">
    </div>

    <div class="right" id="svea_partpayment_tr" style="clear:both; margin-top:15px;display:inline-block;">
        <?php echo $this->language->get('text_invoice_address')?>:<br />
        <div id="svea_partpayment_address"></div>
    </div>

    <br />
    <div class="right" id="svea_partpaymentalt_tr" style="clear:both; margin-top:15px;display:inline-block;">
        <?php echo $this->language->get('text_payment_options')?>:<br />
        <select name="svea_partpayment_alt" id="svea_partpayment_alt">
        </select>
    </div>
    <br />

    <div class="right" style="clear:both; margin-top:15px;display:inline-block;">
        <a id="checkout"  class="button"><span><?php echo $button_confirm; ?></span></a>
    </div>

</div>
<script type="text/javascript"><!--


//Loader
var sveaLoading = '<img src="catalog/view/theme/default/image/loading.gif" id="sveaLoading" />';
var runningCheckout = false;
$("a#checkout").hide();
$('#svea_partpayment_tr').hide();
$('#svea_partpaymentalt_tr').hide();

$('a#checkout').click(function() {
     if(runningCheckout){
       return false;
    }
    runningCheckout = true;
    //Show loader
    $(this).parent().after().append(sveaLoading);

    var ssnNo = $('#ssn').val();
    var paymentSelector = $('#svea_partpayment_alt').val();
    var Initials = $("#initials").val();
    var birthDay = $("#birthDay").val();
    var birthMonth = $("#birthMonth").val();
    var birthYear = $("#birthYear").val();

	$.ajax({
		type: 'GET',
                data: {ssn: ssnNo, paySel: paymentSelector,initials: Initials, birthDay: birthDay, birthMonth: birthMonth, birthYear: birthYear},
		url: 'index.php?route=payment/svea_partpayment/confirm',
		success: function(data) {

                    var json = JSON.parse(data);

                    if(json.success){
                        location = '<?php echo $continue; ?>';
                    }else{
                    $("#svea_partpayment_err").empty().show().append('<br>'+json.error);
                    }

                    $('#sveaLoading').remove();
                    runningCheckout = false;
		}
	});
});


var runningGetPlan = false;
$('#getPlan').click(function() {
    if(runningGetPlan){
       return false;
    }
    runningGetPlan = true;
    //Show loader
    $(this).parent().after().append(sveaLoading);
    var ssnNo = $('#ssn').val();
    $("#svea_partpayment_err").empty();
    $("#svea_partpayment_address").empty();
    $("#svea_partpayment_alt").empty();


    if(ssnNo == ''){
        alert('Vänligen fyll i personnr');
    }else{


    	$.ajax({
    		type: 'GET',
    		url: 'index.php?route=payment/svea_partpayment/getAddressAndPaymentOptions',
            data: {ssn: ssnNo},
    		success: function(msg) {
                var json = JSON.parse(msg);

                if(json.addresses.error){
                $("#svea_partpayment_err").empty().show().append('<br>'+json.addresses.error);
                }else if(json.paymentOptions.error){
                    $("#svea_partpayment_err").empty().show().append('<br>'+json.paymentOptions.error);
                }else{

                    if (json.addresses.length > 0){
                        $("#svea_partpayment_address").empty().append('<strong>'+json.addresses[0].fullName+'</strong><br>'+json.addresses[0].street+'<br>'+json.addresses[0].zipCode+' '+json.addresses[0].locality);
                        $("#svea_partpayment_tr").show();
                    }

                    $.each(json.paymentOptions,function(key,value){
                         $("#svea_partpayment_alt").append('<option value="'+value.campaignCode+'">'+value.description+' ('+value.price_per_month+')</option>');
                    });

                    $("#svea_partpaymentalt_tr").show();

                    $("#svea_partpayment_err").hide();
                    $("a#checkout").show();
                }

                $('#sveaLoading').remove();
                 runningGetPlan = false;
    		}
    	});

    }
});

//--></script>