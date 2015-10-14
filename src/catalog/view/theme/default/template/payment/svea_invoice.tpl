<div class="container-fluid">
    <div><p><?php echo $logo; ?></p></div>
    <?php
    if($countryCode == "SE" || $countryCode == "DK" || $countryCode == "NO" || $countryCode == "FI" || $countryCode == "NL" || $countryCode == "DE") {
        ?>
        <br />
        <select id="svea_invoice_company" name="svea_invoice_company">
            <option value="true"><?php echo $text_company; ?></option>
            <option value="false" selected="selected"><?php echo $text_private; ?></option>
        </select><br /><br />

        <?php
        if($countryCode == "SE" || $countryCode == "DK" || $countryCode == "NO" || $countryCode == "FI"){ ?>
        <label for="ssn" id="svea_private_text"><?php echo $text_ssn; ?></label>
            <label for="ssn" id="svea_business_text" style="display:none;"><?php echo $text_vat_no; ?>:</label>
            <br />
            <input type="text" id="ssn" name="ssn" /><span style="color: red">*</span>
        <?php
        } ?>

    <?php
    }?>
    <div id="svea_invoice_err" style="color:red; margin-bottom:10px"></div>
</div>

<?php
if($countryCode == "SE" || $countryCode == "DK" || $countryCode == "NO") { ?>
    <div class="buttons">
        <div class="pull-right">
            <a id="getSSN" class="btn btn-primary"><span><?php echo $text_get_address; ?></span></a>
        </div>
    </div>
<?php
} ?>

    <div class="content" id="svea_invoice_div">
<?php
if($countryCode == "SE" || $countryCode == "DK" || $countryCode == "FI" || $countryCode == "NO" ){ ?>
<div id="svea_invoice_address_div">
    <label for="svea_invoice_address">
        <?php
            echo $text_invoice_address;
            if($svea_invoice_shipping_billing == '1'){
                   echo ' / '.$text_shipping_address;
            }

        ?>:</label><br />
    <select name="svea_invoice_address" id="svea_invoice_address"></select>
</div>
<?php
} ?>

    <?php
    if($countryCode == "DE" || $countryCode == "NL") {

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
        ?>

        <span id="sveaBirthDateCont"><?php echo $text_birthdate; ?>: <?php  echo "$birthDay $birthMonth $birthYear"; ?><br /><br />
            <?php
            if($countryCode == "NL"){ ?>
                <?php echo $text_initials; ?>: <input type="text" id="initials" name="initials" />
            <?php
            } ?>
        </span>
        <span id="sveaVatNoCont"><?php echo $text_vat_no; ?>: <input type="text" id="vatno" name="vatno" /><br /><br /></span>
    <?php
    } ?>
</div>
<div class="container-fluid" id="svea_customerreference_div" style="display:none;">
    <label for="customerreference">translate me</label><br />
    <input type="text" id="customerreference" name="customerreference" />
</div>
<div class="buttons">
    <div class="pull-right">
        <a id="checkout" class="btn btn-primary"><span><?php echo $button_confirm; ?></span></a>
    </div>
</div>

<style>
    #SveaAddressDiv{margin:10px 0;}
</style>

<script type="text/javascript"><!--
<?php
if($countryCode == "SE" || $countryCode == "DK"){//|| $countryCode == "NO"){ ?>
    $("a#checkout").hide();
<?php
} ?>
<?php
//If norway, hide getAddress for selected private
if($countryCode == "NO"){ ?>
    $("#getSSN").hide();

<?php
} ?>
$('#sveaVatNoCont').hide();
$('#svea_invoice_address_div').hide();

//Selection of business or private
$("#svea_invoice_company").change(function(){

    if ($(this).val() == "true"){
        $('#sveaVatNoCont').show();
        $('#sveaBirthDateCont').hide();

        $('#svea_private_text').hide();
        $('#svea_business_text').show();
        $('#svea_customerreference_div').show();

        //if norway show get address
            $('#getSSN').show();
        //$('#svea_invoice_div').show();
       // $('#svea_invoice_div').show();

        //if norway hide confirm
         <?php
        if( $countryCode == "NO"){ ?>
            $("a#checkout").hide();
        <?php } ?>

    } else {
        $('#sveaVatNoCont').hide();
        $('#sveaBirthDateCont').show();

        $('#svea_private_text').show();
        $('#svea_business_text').hide();
        $('#svea_customerreference_div').show();

         //if norway hide get address
         <?php
        if( $countryCode == "NO"){ ?>
            $("#getSSN").hide();
            $("a#checkout").show();
            $("#svea_invoice_div").hide();
        <?php }else{ ?>
            $('#getSSN').show();
        <?php } ?>
        //$('#svea_invoice_div').hide();
    }
});

//Loader
var sveaLoading = '<img src="catalog/view/theme/default/image/loading.gif" id="sveaLoading" />';
var runningCheckout = false;
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
    var adressSelector = $('#svea_invoice_address').val();
    var Initials = $("#initials").val();
    var birthDay = $("#birthDay").val();
    var birthMonth = $("#birthMonth").val();
    var birthYear = $("#birthYear").val();
    var vatNo = $('#vatno').val();
    var company = $("#svea_invoice_company").val();
    //validate empty field
    if(ssnNo == ''){
        $("#svea_invoice_err").empty().addClass("attention").show().append('<br>*Required');
        $('#sveaLoading').remove();
        runningCheckout = false;
        return false;
    }

    $.ajax({
        type: 'get',
        dataType: 'json',
        data: {
            ssn: ssnNo,
            company: company,
            addSel: adressSelector,
            initials: Initials,
            birthDay: birthDay,
            birthMonth: birthMonth,
            birthYear: birthYear,
            vatno: vatNo
        },
        url: 'index.php?route=payment/svea_invoice/confirm',
        success: function(data) {
                if(data.success){
                    location = '<?php echo $continue; ?>'; // runningCheckout stays in effect until opencart finishes its redirect
                }
                else{
                    $("#svea_invoice_err").empty().addClass("attention").show().append('<br>'+data.error);

                    // remove runningCheckout so that we can retry the payment
                    $('#sveaLoading').remove();
                    runningCheckout = false;
                }
        }
    });
});

//Get address
var runningGetSSN = false;
$('#getSSN').click(function() {
    if(runningGetSSN){
        return false;
    }
    runningGetSSN = true;

    //Show loader
    $(this).parent().after().append(sveaLoading);

    var company = $("#svea_invoice_company").val();
    var ssnNo = $('#ssn').val();

    $("#svea_invoice_err").empty();
    $("#svea_invoice_address").empty();
    $("#svea_invoice_div").hide();

    if(ssnNo == ''){
        $("#svea_invoice_err").empty().addClass("attention").show().append('<br>*Required');
        $('#sveaLoading').remove();
        runningGetSSN = false;
    }
    else{
    	$.ajax({
            type: 'post',
            dataType: 'json',
            url: 'index.php?route=payment/svea_invoice/getAddress',
            data: {
                ssn: ssnNo,
                company: company
            },
            success: function(data) {
                //on error
                if (data.error){

                    $("#svea_invoice_err").empty().addClass("attention").show().append('<br>'+data.error);

                }
                else{
                    if (company == "true"){
                        $("#SveaAddressDiv").empty();
                        $.each(data,function(key,value){
                            $("#svea_invoice_address").append('<option value="'+value.addressSelector+'">'+value.fullName+' '+value.street+' '+value.zipCode+' '+value.locality+'</option>');
                       });

                        $("#svea_invoice_address_div").show();

                    }else{
                        $("#svea_invoice_address_div").hide();
                        $("#SveaAddressDiv").remove();
                        $("#svea_invoice_div").append('<div id="SveaAddressDiv"><strong>'+data[0].fullName+'</strong><br> '+data[0].street+' <br>'+data[0].zipCode+' '+data[0].locality+'</div>');
                    }

                    $("#svea_invoice_div").show();
                    $("#svea_invoice_err").hide();
                    $("a#checkout").show();

                }

                $('#sveaLoading').remove();
                runningGetSSN = false;
    		}
    	});
    }
});
//--></script>