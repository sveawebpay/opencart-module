<div class="container-fluid">
    <div><?php echo $logo; ?></div>

    <div class="container-fluid" id="svea_partpaymentalt_tr" style="clear:both; margin-top:15px; display:inline-block;">
        <?php echo $text_payment_options; ?>:<br />
        <?php

        if( empty( $paymentOptions ) )     // catch error fetching payment plans
        {
            printf( "<div id=\"svea_partpayment_render_err\" style=\"color:red; clear:both; margin-top:15px;\">%s</div>", $response_no_campaign_on_amount);
        }
        else    // present payment plans
        {
            if( key_exists("error", $paymentOptions)){
                printf( "<div id=\"svea_partpayment_render_err\" style=\"color:red; clear:both; margin-top:15px;\">%s</div>",  $paymentOptions['error'] );
            }else{
                $flag = true;
                printf("<div class=\"form-group payment-campaigns\">");
                foreach( $paymentOptions as $p )
                {
                    if($p['paymentPlanType'] == "InterestAndAmortizationFree")
                    {
                        $p['monthlyAmountToPay'] = "";
                    }
                    else
                    {
                        $p['monthlyAmountToPay'] = ": " . $p['monthlyAmountToPay'];
                    }
                    printf(   "<div class=\"col-sm-9 payment-campaign-option\"><input name=\"svea_partpayment_alt\" type=\"radio\" value=\"%s\" %s > %s </input>",
                                    $p['campaignCode'], $flag ? 'checked' : '', $p['description'] . $p['monthlyAmountToPay'] );
                    ?><ul id="<?php echo $p['campaignCode'] ?>" class="campaign-item" style="">
                        <li><?php echo $p['notificationFee'] ?></li>
                        <li><?php echo $p['initialFee'] ?></li>
                        <li><?php echo $p['interestRatePercent'] ?></li>
                        <li><?php echo $p['effectiveInterestRate'] ?></li>
                        <li><?php echo $p['contractLengthInMonths'] ?></li>
                        <?php
                         if($p['numberOfPaymentFreeMonths'] != "0")
                         { ?>
                        <li><?php echo $p['numberOfPaymentFreeMonths'] ?></li>
                        <?php }
                         if($p['numberOfInterestFreeMonths'] != "0")
                         { ?>
                        <li><?php echo $p['numberOfInterestFreeMonths'] ?></li>
                        <?php } ?>
                        <li><?php echo $p['totalAmountToPay'] ?></li>
                    </ul>
                </div> <?php
                    $flag = false;
                }
                 ?></div>
                <script>
                    $(document).ready(function(){
                        $('.payment-campaigns input[type="radio"]').click(function(){
                            var campaign = $(this).val();
                            $("ul.campaign-item").hide();
                            $("#"+campaign).show();
                        });
                        $( '.payment-campaign-option input[type="radio"]:first').click();
                    });
                </script> <?php
            }
        } ?>
    </div>
    <br />
    <div style="display:inline-block;">
        <p> <?php echo $termsOfService1 ?><a href="<?php echo $termsLink ?>"> <?php echo $companyName . $termsOfService2 ?> </a> <?php echo $termsOfService3 ?></p>
    </div>
    <?php
    if($countryCode == "SE" || $countryCode == "DK" || $countryCode == "NO" || $countryCode == "FI" || $countryCode == "NL" || $countryCode == "DE")
    { ?>

        <?php // get SSN
        if( $countryCode == "SE" || $countryCode == "DK" || $countryCode == "NO" || $countryCode == "FI")
        { ?>
        <br />
        <div style="margin-left:15px;">
             <?php echo $text_ssn; ?>:
            <br />
            <input type="text" id="ssn" name="ssn" /><span style="color: red">*</span><br /><br />
        </div>
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

            if($countryCode == "NL" || $countryCode == "DE"){ ?>
                <span id="sveaBirthDateCont">
                    <?php echo $text_birthdate; ?>:
                    <?php echo "$birthDay $birthMonth $birthYear"; ?><br /><br />
                    <?php   if($countryCode == "NL"){
                    echo $text_initials; ?>:
                    <input type="text" id="initials" name="initials" />
                    <?php } ?>
               </span>
            <?php
            } ?>
        <?php
        } ?>
    <?php
    } ?>

    <div id="svea_partpayment_err"  style="color:red; clear:both; margin-top:15px;"></div>

<?php // show getAddress button for private persons in SE, DK
if( $countryCode == "SE" || $countryCode == "DK" )
{ ?>
    <div class="buttons">
        <div class="pull-right">
            <a id="getPlan" class="btn btn-primary"><span><?php echo $text_get_address; ?></span></a>
        </div>
    </div>

    <script type="text/javascript"><!--
        $("a#checkout").hide();
    //--></script>
<?php
} ?>

<div class="content" id="svea_partpayment_tr" style="clear:both; margin-top:15px;display:inline-block;padding-left:15px;">
    <?php echo $text_invoice_address;
        if($svea_partpayment_shipping_billing == '1'){
             echo ' / '.$text_shipping_address;
        }
    ?>:<br />
    <div id="svea_partpayment_address"></div>
</div>

<br />

<div class="buttons">
    <div class="pull-right">
        <a id="checkout"  class="btn btn-primary"><span><?php echo $button_confirm; ?></span></a>
   </div>
</div>


<!-- Modal -->
<div id="svea_error_sending_mail" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Error sending mail</h4>
      </div>
      <div class="modal-body">
        <p>There was a problem sending you the email with the terms for payment plans</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>


<script type="text/javascript"><!--

//Loader
var sveaLoading = '<img src="catalog/view/theme/default/image/loading.gif" id="sveaLoading" />';
var runningCheckout = false;
//$("a#checkout").hide();
$('#svea_partpayment_tr').hide();

$('a#checkout').click(function(event) {

    // we don't accept multiple confirmations of one order
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

    //validate empty field
    if(ssnNo == ''){
        $("#svea_partpayment_err").empty().addClass("attention").show().append('<br><div style="margin-left:15px"/;>* <?php echo $text_required ?></div>');
        $('#sveaLoading').remove();
        runningCheckout = false;
        return false;
    }
    $.ajax({
            type: 'get',
            dataType: 'json',
            data: { ssn: ssnNo,
                    paySel: paymentSelector,
                    initials: Initials,
                    birthDay: birthDay,
                    birthMonth: birthMonth,
                    birthYear: birthYear
                },
            url: 'index.php?route=extension/payment/svea_partpayment/confirm',
            success: function(data) {


                if(data.success){
                    location = '<?php echo $continue; ?>'; // runningCheckout stays in effect until opencart finishes its redirect
                }
                else{
                    $("#svea_partpayment_err").empty().addClass("attention").show().append('<br>* '+data.error);

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
        $("#svea_partpayment_err").empty().addClass("attention").show().append('<br><div style="margin-left:15px"/;>* <?php echo $text_required ?></div>');
        $('#sveaLoading').remove();
        runningGetPlan = false;
    }
    else{
        $.ajax({
            type: 'post',
            dataType: 'json',
            url: 'index.php?route=extension/payment/svea_partpayment/getAddressAndPaymentOptions',
            data: {
                ssn: ssnNo},
            success: function(data) {

                    if(data.addresses.error){
                        $("#svea_partpayment_err").empty().addClass("attention").show().append('<br>'+data.addresses.error);
                    }
                    else if(data.paymentOptions.error){
                        $("#svea_partpayment_err").empty().addClass("attention").show().append('<br>'+data.paymentOptions.error);
                    }
                    else{

                        if (data.addresses.length > 0){
                            $("#svea_partpayment_address").empty().append('<strong>'+data.addresses[0].fullName+'</strong><br>'+data.addresses[0].street+'<br>'+data.addresses[0].zipCode+' '+data.addresses[0].locality);
                            $("#svea_partpayment_tr").show();
                        }
                        //$.each(data.paymentOptions,function(key,value){
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
<script>
$(document).ready(function () {
if( $('#collapse-checkout-confirm').html().length > 0) {
    var country = $('#input-payment-country option:selected').text();
    /// only execute for finland
    if (country === 'Finland') {
        var email = $('#input-payment-email').val();
        fireEmail(email);
    }
  }
  function fireEmail(email) {
      
      $.ajax("index.php?route=extension/svea/email/index", {
        method: 'POST',
        data: { email: email },
        statusCode: {
          201: function (response) {
            $('a#checkout').removeClass('disabled');
          },
          401: function (response) {
            $('a#checkout').addClass('disabled');
            $("#svea_error_sending_mail").modal();
            
          }
        },
        success: function () {
          
        }
       });
    }
});
</script>
