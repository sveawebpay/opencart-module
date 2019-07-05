<?php echo $header; ?>
<link href="catalog/view/theme/default/stylesheet/svea/sco.css" rel="stylesheet"/>
<script>
  var continueBtnText = "<?php echo $button_continue; ?>";
  var backBtnText = "<?php echo $button_back; ?>";
</script>
<script src="catalog/view/theme/default/javascript/svea/sco.js"></script>
<div class="container" id="container">

    <section class="checkout-page-container">
        <!-- Checkout main content - start -->
        <div class="sco-main-content">

            <!-- If default checkout option enable link to classic opencart checkout page - start -->
            <?php if ($status_default_checkout) { ?>
            <section class="allow-classic-checkout">
                <div class="row">
                    <div class="col-xs-12" style="width: 100%;">
                        <p><?php echo $text_normal_checkout; ?></p>
                    </div>
                </div>
            </section>
            <?php } ?>
            <!-- If default checkout option enable link to classic opencart checkout page - end -->

            <div class="row">
                <div class="col-sm-12 sco-form step-1">
                    <div class="cover-layer"></div>
                    <!-- Place for order cart - start -->
                    <div id="sco-cart"></div>
                    <!-- Place for order cart - end -->




                    <!-- Miscellaneous section (coupons, vouchers, comment ...) - start -->
                    <!--<h3><?php echo $heading_misc; ?></h3>-->

                    <div class="action-icons">
                        <!-- coupon - start -->
                        <?php if ($status_coupon && $sco_show_coupons === '1') { ?>
                        <div class="sco-checkout-extra-options" id="coupon-toggle-btn">
                            <a data-toggle="collapse" href="#sco-coupon" aria-expanded="false"aria-controls="sco-coupon">
                                <i class="fa fa-ticket"></i>
                                <span>
                                    <?php echo $coupon_icon_title; ?>
                                    <i class="check-icon fa fa-check-circle"></i>
                                </span>
                            </a>
                        </div>
                        <?php } ?>
                        <!-- coupon - end -->

                        <!-- voucher - start -->
                        <?php if ($status_voucher && $sco_show_voucher === '1') { ?>
                        <div class="sco-checkout-extra-options" id="voucher-toggle-btn">
                            <a data-toggle="collapse" href="#sco-voucher" aria-expanded="false" aria-controls="sco-voucher">
                                <i class="fa fa-credit-card"></i>
                                <span>
                                    <?php echo $voucher_icon_title; ?>
                                    <i class="check-icon fa fa-check-circle"></i>
                                </span>
                            </a>
                        </div>
                        <?php } ?>
                        <!-- voucher - end -->

                        <!-- order comment - start -->
                        <?php if ($sco_show_comment === '1') { ?>
                        <div class="sco-checkout-extra-options" id="comment-toggle-btn">
                            <a data-toggle="collapse" href="#sco-comment" aria-expanded="false" aria-controls="sco-comment">
                                <i class="fa fa-comment"></i>
                                <span>
                                    <?php echo $comment_icon_title; ?>
                                    <i class="check-icon fa fa-check-circle"></i>
                                </span>
                            </a>
                        </div>
                        <?php } ?>
                        <!-- order comment - end -->
                    </div>

                    <div class="addon-content">
                        <div class="collapse" id="sco-coupon">
                            <div class="input-group">
                                <input type="text" class="form-control" name="coupon" id="coupon"/>
                                <span class="input-group-btn">
                                    <button class="btn btn-primary" type="button" id="coupon-button">
                                        <i class="glyphicon glyphicon-plus"></i>
                                    </button>
                                </span>
                            </div>
                        </div>

                        <div class="collapse" id="sco-voucher">
                            <div class="input-group">
                                <input type="text" class="form-control" name="voucher" id="voucher"/>
                                <span class="input-group-btn">
                                    <button class="btn btn-primary" type="button" id="voucher-button">
                                        <i class="glyphicon glyphicon-plus"></i>
                                    </button>
                                </span>
                            </div>
                        </div>

                        <div class="collapse" id="sco-comment">
                            <div class="input-group">
                                <textarea class="form-control" name="comment" id="comment"><?php echo $order_comment; ?></textarea>
                                <span class="input-group-btn">
                                    <button class="btn sco-primary-btn" id="sco-comment-add" type="button"><i class="glyphicon glyphicon-plus"></i></button>
                                </span>
                            </div>
                            <?php if (!empty($order_comment)) { ?>
                            <script>
                              $('#comment-toggle-btn').addClass('used');
                            </script>
                            <?php } ?>
                        </div>
                    </div>
                    <?php if($sco_gather_newsletter_consent == 1) { ?>
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="sco-newsletter" name="sco-newsletter">
                        <label class="sco-newsletter" for="sco-newsletter"><?php echo $text_subscribe_to_newsletter; ?></label>
                    </div>
                    <?php } ?>
                </div>
                <div class="col-sm-12 sco-form step-2">
                    <div class="cover-layer"></div>
                    <h3><?php echo $heading_shipping; ?></h3>

                    <!-- Postcode form fields - start -->
                    <section id="sco-form">
                        <div class="form-group">
                            <div class="addon">
                                <i class="fa fa-map-marker" aria-hidden="true"></i>
                                <input type="text" name="postcode" id="sco-postcode"
                                       value="<?php echo $sco_postcode; ?>" class="form-control"
                                       placeholder="<?php echo $entry_postcode; ?>"/>
                                <span class="change-postcode"><?php echo $text_change_postcode; ?></span>
                            </div>
                        </div>
                    </section>
                    <!-- Postcode form fields - end -->

                    <div class="shipping-methods" id="sco-details">
                        <div class="cover-layer"></div>
                        <!-- Shipping section - start -->
                        <select name="shipping" id="sco-shipping" class="form-control"></select>
                        <!-- Shipping section - end -->
                    </div>
                </div>



                <div class="col-sm-12">
                    <button disabled type="button" id="continue-step-1" class="sco-continue-btn btn btn-primary btn-block sco-primary-btn">
                        <?php echo $button_continue; ?>
                    </button>
                </div>

                <!-- PLace holder for Svea checkout i-frame - start -->
                <div class="col-sm-12 sco-snippet">
                    <div class="text-center" style="margin-top: 10px;">
                        <i class="fa fa-spinner" id="sco-snippet-loader" aria-hidden="true"></i>
                    </div>
                    <div id="sco-snippet-section"></div>
                </div>
                <!-- PLace holder for Svea checkout i-frame - end -->

            </div>
        </div>
        <!-- Checkout main content - end -->
    </section>

</div>

<?php echo $footer; ?>