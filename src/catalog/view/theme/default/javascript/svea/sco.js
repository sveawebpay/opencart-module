$(document).ready(function () {
  addAllChange();
  $('[data-toggle="tooltip"]').tooltip();

  var postCodeEl = $('#sco-postcode');
  var shippingEl = $('#sco-shipping');
  var cartEl = $('#sco-cart');
  var couponEl = $('#sco-coupon');
  var voucherEl = $('#sco-voucher');
  var continueBtnStep1 = $('#continue-step-1');
  var continueBtnStep2;
  var postCodeLength = 4;
  var stepOneFormEl = $('.sco-form.step-1');
  var stepTwoFormEl = $('.sco-form.step-2');
  var snippetContainerEl = $('#sco-snippet-section');
  var changePostcodeEl = $('.change-postcode');
  var snippetLoaderEl = $('#sco-snippet-loader');
  snippetLoaderEl.hide();

  setTimeout(function () {
    window.scrollTo(0, 0);
  });


  // Process postcode field
  postCodeEl.allchange(function () {
    if (postCodeEl.val()) {
      var postcode = postCodeEl.val().replace(/\D/g, '');
      if (postcode && postcode.length >= postCodeLength && postcode >= postCodeEl.val()) {
        continueBtnStep1.prop('disabled', false);
      } else {
        continueBtnStep1.prop('disabled', true);
      }
    }
  });

  continueBtnStep1.on('click', function () {
    var postcode = postCodeEl.val().replace(/\D/g, '');
    if (postcode && postcode.length >= postCodeLength && postcode >= postCodeEl.val()) {
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
          $('#sco-details').show("fast", function () {
            stepOneFormEl.addClass('passed');
            postCodeEl.prop('disabled', true);
            changePostcodeEl.show();
            continueBtnStep1.attr('id', 'continue-step-2');
            continueBtnStep2 = $('#continue-step-2');
            continueBtnStep2.show();
          });
        }
      });
    }
  });

  changePostcodeEl.on('click', function () {
    continueBtnStep2.attr('id', 'continue-step-1');
    continueBtnStep1 = $('#continue-step-1');
    postCodeEl.prop('disabled', false);
    changePostcodeEl.hide();
    stepOneFormEl.removeClass('passed');
    $('#sco-details').hide("fast", function() {
      shippingEl.html('');
    });
  });

  $(document).on('click', '#continue-step-2', function () {
    if (continueBtnStep2.hasClass('sco-back-btn')) {
      stepTwoFormEl.removeClass('passed');
      continueBtnStep2.text(continueBtnText);
      continueBtnStep2.removeClass('sco-back-btn');
      continueBtnStep2.addClass('sco-continue-btn');
      snippetContainerEl.html('');
      changePostcodeEl.hide();

      $('html, body').animate({
        scrollTop: stepTwoFormEl.offset().top - 15
      }, 500);

      return;
    }

    stepTwoFormEl.addClass('passed');
    continueBtnStep2.text(backBtnText);
    continueBtnStep2.removeClass('sco-continue-btn');
    continueBtnStep2.addClass('sco-back-btn');
    initialCheckoutPayment();
  });


  $('#sco-form input').on('change', function () {
    var postcode = postCodeEl.val().replace(/\D/g, '');
    if (postcode && postcode.length >= postCodeLength && postcode >= postCodeEl.val()) {
      continueBtnStep1.prop('disabled', false);
    } else {
      continueBtnStep1.prop('disabled', true);
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
      }
    });
  });

  $('#comment').on('blur', function (event) {
    $.ajax({
      type: 'POST',
      url: 'index.php?route=extension/svea/comment',
      data: $('#comment'),
      dataType: 'json',
      success: function (json) {
        if ($('#comment').val() !== '') {
          $('#comment-toggle-btn').addClass('used');
        } else {
          $('#comment-toggle-btn').removeClass('used');
        }
      }
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
          $('#sco-coupon .input-group').after('<div class="alert alert-danger sco-danger" role="alert">' + json['error'] + '</div>');
          $('#sco-coupon-add').addClass('pushed');
          $('#sco-coupon .alert').fadeIn();
          $(".sco-input[name='coupon']").addClass('sco-input-alert');
          $('#sco-coupon .alert').delay(8000).slideUp(function () {
            $(".sco-input[name='coupon']").removeClass('sco-input-alert');
            $('#sco-voucher-add').removeClass('pushed');
          });
        }
        else {
          couponEl.load('index.php?route=extension/svea/coupon');
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
          $('#sco-voucher .input-group').after('<div class="alert alert-danger sco-danger" role="alert">' + json['error'] + '</div>');
          $('#sco-voucher-add').addClass('pushed');
          $('#sco-voucher .alert').fadeIn();
          $(".sco-input[name='voucher']").addClass('sco-input-alert');
          $('#sco-voucher .alert').delay(8000).slideUp(function () {
            $(".sco-input[name='voucher']").removeClass('sco-input-alert');
            $('#sco-voucher-add').removeClass('pushed');
          });
        }
        else {
          voucherEl.load('index.php?route=extension/svea/voucher');
          updateCartInformation();
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
      }
    });
  });

  /* Collapse all opened elements */
  $(document).on('click', '.sco-checkout-extra-options a', function () {
    $('.addon-content .collapse').removeClass('in');
    var addOnIconEls = $('.sco-checkout-extra-options i');
    addOnIconEls.css({
      'opacity': '.5'
    });

    if ($(this).hasClass('collapsed') === false) {
      $(this).children('i').css({
        'opacity': '1'
      });
    } else {
      addOnIconEls.css({
        'opacity': '1'
      });
    }
  });

  postCodeEl.trigger('change');
  cartEl.load('index.php?route=extension/svea/cart');
  couponEl.load('index.php?route=extension/svea/coupon');
  voucherEl.load('index.php?route=extension/svea/voucher');

  /**
   * Create or update checkout order
   */
  function initialCheckoutPayment(forceCreate) {
    var postcode = postCodeEl.val();
    snippetLoaderEl.show();

    var url = 'index.php?route=extension/svea/payment';
    if (forceCreate) {
      url += '&create=true';
    }

    if (postcode.length >= 4) {
      continueBtnStep2.prop('disabled', true);
      $.ajax({
        type: 'POST',
        url: url,
        data: $('#sco-form input'),
        dataType: 'html',
        beforeSend: function () {
        },
        success: function (data) {
          $('.heading-payment').hide();
          snippetContainerEl.html(data);
          snippetLoaderEl.hide();
          continueBtnStep2.prop('disabled', false);

          $('html, body').animate({
            scrollTop: continueBtnStep2.offset().top - 15
          }, 500);
        },
        error: function (data) {
          continueBtnStep2.prop('disabled', false);
          snippetLoaderEl.hide();
          var jsonData = jQuery.parseJSON(data.responseText);

          // If there is error on update force create order again
          if (jsonData['isScoUpdate'] === true) {
            initialCheckoutPayment(true);
          } else {
            snippetContainerEl.html('<div class="alert alert-danger" role="alert" style="display: block;">' + jsonData['message'] + '</div>');
          }
        }
      });
    } else {
      snippetContainerEl.html('');
      snippetLoaderEl.hide();
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