$(document).ready(function () {
    addAllChange();
    $('[data-toggle="tooltip"]').tooltip();

    var postCodeEl = $('#sco-postcode');
    var shippingEl = $('#sco-shipping');
    var cartEl = $('#sco-cart');
    var couponEl = $('#sco-coupon');
    var voucherEl = $('#sco-voucher');
    var postCodeLength = 5;

    // Process postcode field
    postCodeEl.allchange(function () {
        if (postCodeEl.val()) {
            var postcode = postCodeEl.val().replace(/\s+/g, '');
            if (postcode.length === postCodeLength) {
                postCodeEl.blur();
            }
        }
    });

    $('#sco-form input').on('change', function () {
        var email = $('#sco-email').val();
        var postcode = postCodeEl.val().replace(/\s+/g, '');

        if (isEmail(email) && postcode.length === postCodeLength) {
            $.ajax({
                type: 'POST',
                url: 'index.php?route=extension/svea/shipping',
                data: $('#sco-form input'),
                dataType: 'json',
                success: function (json) {
                    var html = '';

                    for (var i = 0; i < json['methods'].length; i++) {
                        html += '<option value="' + json['methods'][i]['id'] + '"';
                        if (json['methods'][i]['selected'] == 1) {
                            html += ' selected="selected"'
                        }
                        html += '>' + json['methods'][i]['name'] + '</option>';
                    }

                    shippingEl.html(html);
                    $('#sco-details').slideDown();
                    shippingEl.trigger('change');
                }
            });
        }
    });

    // When change shipping, payment functionality calls again
    shippingEl.on('change', function (event) {
        event.preventDefault();

        $.ajax({
            type: 'POST',
            url: 'index.php?route=extension/svea/shipping/save',
            data: shippingEl,
            dataType: 'json',
            success: function () {
                updateCartInformation();
                initialCheckoutPayment();
            }
        });
    });

    $('#comment').on('blur', function (event) {
        $.ajax({
            type: 'POST',
            url: 'index.php?route=extension/svea/comment',
            data: $('#comment'),
            dataType: 'json',
            success: function (json) {}
        });
    });

    $(document).on('click', '#sco-coupon-add', function () {
        $.ajax({
            url: 'index.php?route=extension/svea/coupon/add',
            type: 'post',
            data: $('input[name="coupon"]'),
            dataType: 'json',
            beforeSend: function () {
                $('.alert').remove();
            },
            success: function (json) {
                if (json['error']) {
                    $('#sco-coupon .input-group').after('<div class="alert alert-danger" role="alert">' + json['error'] + '</div>');
                    $('#sco-coupon .alert').fadeIn();
                    $('#sco-coupon .alert').delay(8000).slideUp();
                }
                else {
                    couponEl.load('index.php?route=extension/svea/coupon');
                    initialCheckoutPayment();
                    updateCartInformation();
                }
            }
        });
    });

    $(document).on('click', '#sco-coupon-remove', function () {
        $.ajax({
            url: 'index.php?route=extension/svea/coupon/remove',
            type: 'post',
            dataType: 'json',
            beforeSend: function () {
                $('.alert').remove();
            },
            success: function () {
                couponEl.load('index.php?route=extension/svea/coupon');
                updateCartInformation();
                initialCheckoutPayment();
            }
        });
    });

    $(document).on('click', '#sco-voucher-add', function () {
        $.ajax({
            url: 'index.php?route=extension/svea/voucher/add',
            type: 'post',
            data: $('input[name="voucher"]'),
            dataType: 'json',
            beforeSend: function () {
                $('.alert').remove();
            },
            success: function (json) {
                if (json['error']) {
                    $('#sco-voucher .input-group').after('<div class="alert alert-danger" role="alert">' + json['error'] + '</div>');
                    $('#sco-voucher .alert').fadeIn();
                    $('#sco-voucher .alert').delay(8000).slideUp();
                }
                else {
                    voucherEl.load('index.php?route=extension/svea/voucher');
                    updateCartInformation();
                    initialCheckoutPayment();
                }
            }
        });
    });

    $(document).on('click', '#sco-voucher-remove', function () {
        $.ajax({
            url: 'index.php?route=extension/svea/voucher/remove',
            type: 'post',
            dataType: 'json',
            beforeSend: function () {
                $('.alert').remove();
            },
            success: function (json) {
                voucherEl.load('index.php?route=extension/svea/voucher');
                updateCartInformation();
                initialCheckoutPayment();
            }
        });
    });

    postCodeEl.trigger('change');
    cartEl.load('index.php?route=extension/svea/cart');
    couponEl.load('index.php?route=extension/svea/coupon');
    voucherEl.load('index.php?route=extension/svea/voucher');

    /**
     * Create or update checkout order
     */
    function initialCheckoutPayment(forceCreate) {
        var email = $('#sco-email').val();
        var postcode = postCodeEl.val();

        var url = 'index.php?route=extension/svea/payment';
        if (forceCreate) {
            url += '&create=true';
        }

        if (isEmail(email) && postcode.length > 4) {
            $.ajax({
                type: 'POST',
                url: url,
                data: $('#sco-form input'),
                dataType: 'html',
                beforeSend: function () {
                    $('#sco-email').attr('readonly', true);
                    $('#sco-postcode').attr('readonly', true);
                },
                success: function (data) {
                    $('.heading-payment').hide();
                    $("#sco-snippet-section").html(data);
                },
                error: function (data) {
                    $('#sco-email').attr('readonly', false);
                    $('#sco-postcode').attr('readonly', false);
                    var jsonData = jQuery.parseJSON(data.responseText);

                    // If there is error on update force create order again
                    if (jsonData['isScoUpdate'] === true) {
                        initialCheckoutPayment(true);
                    } else {
                        $("#sco-snippet-section").html('<div class="alert alert-danger" role="alert" style="display: block;">' + jsonData['message'] + '</div>');
                    }
                }
            });
        } else {
            $("#sco-snippet-section").html('');
        }
    }

    /**
     * Update cart information
     */
    function updateCartInformation() {
        cartEl.load('index.php?route=extension/svea/cart');
    }

    function isEmail(email) {
        var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
        return regex.test(email);
    }

    function addAllChange() {
        $.fn.allchange = function (callback) {
            var self = this;
            var last = "";
            var infunc = function () {
                var text = $(self).val();
                if (text != last) {
                    last = text;
                    callback();
                }
                setTimeout(infunc, 100);
            };
            setTimeout(infunc, 100);
        };
    }
});