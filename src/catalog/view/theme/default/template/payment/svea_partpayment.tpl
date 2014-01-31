<div class="content">
    <div><?php echo $logo; ?></div>
    
    <div class="content" id="svea_partpaymentalt_tr" style="clear:both; margin-top:15px;display:inline-block;">
        <?php echo $this->language->get('text_payment_options') ?>:<br />
        <?php 
        $flag = true;
        foreach( $paymentOptions as $p )
        { 
            printf(   "<div><input name=\"svea_partpayment_alt\" type=\"radio\" value=\"%s\" %s > %s </input></div>", 
                            $p['campaignCode'], $flag ? 'checked' : '', $p['description'].': '.$p['price_per_month'] );
            $flag = false;
        } ?>
    </div>
    <br />
   
    <?php 
    if($countryCode == "SE" || $countryCode == "DK" || $countryCode == "NO" || $countryCode == "FI" || $countryCode == "NL" || $countryCode == "DE")
    { ?>
        
        <?php // get SSN
        if( $countryCode == "SE" || $countryCode == "DK" || $countryCode == "NO" || $countryCode == "FI")
        { ?>
            <?php echo $this->language->get('text_ssn') ?>: 
            <input type="text" id="ssn" name="ssn" /><br /><br />
        <?php 
        }
        elseif( $countryCode == "NL" || $countryCode == "DE" )
        {
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

            //Years from 1913 to date('Y')
            $years = '';
            for($y = 1913; $y <= date('Y'); $y++){
                $selected = "";
                if( $y == (date('Y')-30) )      // selected is backdated 30 years
                    $selected = "selected";

                $years .= "<option value='$y' $selected>$y</option>";
            }           
            $birthYear = "<select name='birthYear' id='birthYear'>$years</select>";
            
            if($countryCode == "NL"){ ?>
                <span id="sveaBirthDateCont">
                    <?php echo $this->language->get('text_birthdate')?>: 
                    <?php echo "$birthDay $birthMonth $birthYear"; ?><br /><br />
                    <?php echo $this->language->get('text_initials') ?>: 
                    <input type="text" id="initials" name="initials" />
               </span>
            <?php 
            } ?>
        <?php 
        } ?>
    <?php 
    } ?>

    <div id="svea_partpayment_err"  style="color:red; clear:both; margin-top:15px;"></div>
</div>

<div class="buttons">
    <div class="right">
        <a id="getPlan" class="button"><span><?php echo $this->language->get('text_get_address') ?></span></a>
    </div>
</div>

<div class="content" id="svea_partpayment_tr" style="clear:both; margin-top:15px;display:inline-block;">
    <?php echo $this->language->get('text_invoice_address') ?>:<br />
    <div id="svea_partpayment_address"></div>
</div>

<br />

<div class="buttons">
    <div class="right">
        <a id="checkout"  class="button"><span><?php echo $button_confirm; ?></span></a>
   </div>
</div>

<script type="text/javascript"><!--

//Loader
var sveaLoading = '<img src="catalog/view/theme/default/image/loading.gif" id="sveaLoading" />';
var runningCheckout = false;
$("a#checkout").hide();
$('#svea_partpayment_tr').hide();

$('a#checkout').click(function(event) {

    // we don't accept multiple confirmations of one onder
    if(runningCheckout){
        event.preventDefault();
        return false;
    }
    runningCheckout = true;
    
    //Show loader
    $(this).parent().after().append(sveaLoading);

    var ssnNo = $('#ssn').val();
    var paymentSelector = $("input:radio[name=svea_partpayment_alt]:checked").val();
    var Initials = $("#initials").val();
    var birthDay = $("#birthDay").val();
    var birthMonth = $("#birthMonth").val();
    var birthYear = $("#birthYear").val();

    $.ajax({
            type: 'GET',
            data: { ssn: ssnNo,
                    paySel: paymentSelector,
                    initials: Initials,
                    birthDay: birthDay,
                    birthMonth: birthMonth,
                    birthYear: birthYear
                },
            url: 'index.php?route=payment/svea_partpayment/confirm',
            success: function(data) {

                var json = JSON.parse(data);

                if(json.success){
                    location = '<?php echo $continue; ?>'; // runningCheckout stays in effect until opencart finishes its redirect
                }
                else{
                    $("#svea_partpayment_err").empty().addClass("attention").show().append('<br>'+json.error);

                    // remove runningCheckout so that we can retry the payment
                    $('#sveaLoading').remove();
                    runningCheckout = false;
                }
            }
    });
});

// Jan'14: getPlan is only used for getAddresses call, we disregard the payment plans, as we show available payment plans when the template is rendered. 
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
    //$("#svea_partpayment_alt").empty();       // we no longer touch the payment plans from this function


    if(ssnNo == ''){
        $("#svea_partpayment_err").empty().addClass("attention").show().append('<br>*Required');
        $('#sveaLoading').remove();
        runningGetPlan = false;
    }
    else{
    	$.ajax({
            type: 'GET',
            url: 'index.php?route=payment/svea_partpayment/getAddressAndPaymentOptions',
            data: {
                ssn: ssnNo},
    		success: function(msg) {
                    var json = JSON.parse(msg);

                    if(json.addresses.error){
                        $("#svea_partpayment_err").empty().addClass("attention").show().append('<br>'+json.addresses.error);
                    }
                    else if(json.paymentOptions.error){
                        $("#svea_partpayment_err").empty().addClass("attention").show().append('<br>'+json.paymentOptions.error);
                    }
                    else{

                        if (json.addresses.length > 0){
                            $("#svea_partpayment_address").empty().append('<strong>'+json.addresses[0].fullName+'</strong><br>'+json.addresses[0].street+'<br>'+json.addresses[0].zipCode+' '+json.addresses[0].locality);
                            $("#svea_partpayment_tr").show();
                        }
                        //$.each(json.paymentOptions,function(key,value){
                        //    $("#svea_partpayment_alt").append('<option value="'+value.campaignCode+'">'+value.description+' ('+value.price_per_month+')</option>');
                        //});
                        //$("#svea_partpaymentalt_tr").show();

                        $("#svea_partpayment_err").hide();
                        $("a#checkout").show();
                    }

                    $('#sveaLoading').remove();
                    runningGetPlan = false;
    		}
            }
        );
    }
});
//--></script>