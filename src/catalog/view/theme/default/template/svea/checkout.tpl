<?php echo $header; ?>
<link href="catalog/view/theme/default/stylesheet/svea/sco.css" rel="stylesheet">
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
                        <p><i class="glyphicon glyphicon-refresh"></i> <?php echo $text_normal_checkout; ?></p>
                    </div>
                </div>
            </section>
            <?php } ?>
            <!-- If default checkout option enable link to classic opencart checkout page - end -->

            <div class="row">
                <div class="col-sm-7">
                    <section class="sco-left-column">

                        <h2><?php echo $heading_order; ?></h2>

                        <!-- Email and postcode form fields - start -->
                        <section id="sco-form">
                            <div class="form-group">
                                <div class="addon">
                                    <i class="glyphicon glyphicon-envelope"></i>
                                    <input type="text" name="email" id="sco-email" value="<?php echo $sco_email; ?>"
                                           class="form-control" placeholder="<?php echo $entry_email; ?>"/>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="addon">
                                    <i class="glyphicon glyphicon-map-marker"></i>
                                    <input type="text" name="postcode" id="sco-postcode"
                                           value="<?php echo $sco_postcode; ?>" class="form-control"
                                           placeholder="<?php echo $entry_postcode; ?>"/>
                                </div>
                            </div>
                        </section>
                        <!-- Email and postcode form fields - end -->

                        <div id="sco-details">

                            <!-- Shipping section - start -->
                            <h3>
                                <?php echo $heading_shipping; ?>
                                <i class="glyphicon glyphicon-question-sign" data-toggle="tooltip"
                                   data-placement="right"
                                   title="<?php echo $text_tooltip_shipping; ?>">
                                </i>
                            </h3>
                            <select name="shipping" id="sco-shipping" class="form-control"></select>
                            <!-- Shipping section - end -->

                            <!-- Miscellaneous section (coupons, vouchers, comment ...) - start -->
                            <h3><?php echo $heading_misc; ?></h3>
                            <ul class="list-group">

                                <!-- coupon - start -->
                                <?php if ($status_coupon) { ?>
                                <li class="list-group-item">
                                    <a data-toggle="collapse" href="#sco-coupon" aria-expanded="false"
                                       aria-controls="sco-coupon"><?php echo $text_coupon; ?></a>
                                    <div class="collapse" id="sco-coupon">
                                        <div class="input-group">
                                            <input type="text" class="form-control" name="coupon" id="coupon"/>
                                            <span class="input-group-btn">
                                                <button class="btn btn-primary" type="button" id="coupon-button">
                                                    <i class="glyphicon glyphicon-plus"></i>
                                                </button>
                                            </span>
                                        </div>
                                        <div id="sco-coupon-alert" class="alert"></div>
                                    </div>
                                </li>
                                <?php } ?>
                                <!-- coupon - end -->

                                <!-- voucher - start -->
                                <?php if ($status_voucher) { ?>
                                <li class="list-group-item">
                                    <a data-toggle="collapse" href="#sco-voucher" aria-expanded="false"
                                       aria-controls="sco-voucher"><?php echo $text_voucher; ?></a>
                                    <div class="collapse" id="sco-voucher">
                                        <div class="input-group">
                                            <input type="text" class="form-control" name="voucher" id="voucher"/>
                                            <span class="input-group-btn">
                                                <button class="btn btn-primary" type="button" id="voucher-button">
                                                    <i class="glyphicon glyphicon-plus"></i>
                                                </button>
                                            </span>
                                        </div>
                                        <div id="sco-voucher-alert" class="alert"></div>
                                    </div>
                                </li>
                                <?php } ?>
                                <!-- voucher - end -->

                                <!-- order comment - start -->
                                <li class="list-group-item">
                                    <a data-toggle="collapse" href="#sco-comment" aria-expanded="false"
                                       aria-controls="sco-comment"><?php echo $text_comment; ?></a>
                                    <div class="collapse" id="sco-comment">
                                        <textarea class="form-control" name="comment" id="comment"><?php echo $order_comment; ?></textarea>
                                    </div>
                                </li>
                                <!-- order comment - end -->
                            </ul>
                            <!-- Miscellaneous section (coupons, vouchers, comment ...) - start -->

                        </div>

                        <!-- Place for order cart - start -->
                        <h3><?php echo $heading_cart; ?></h3>
                        <div id="sco-cart"></div>
                        <!-- Place for order cart - end -->

                    </section>
                </div>

                <!-- PLace holder for Svea checkout i-frame - start -->
                <div class="col-sm-5">
                    <section class="sco-right-column">
                        <h2 class="heading-payment"><?php echo $heading_payment; ?></h2>
                        <div id="sco-snippet-section">
                            <p><?php echo $text_checkout_into; ?></p>
                            <div class="text-center"></div>
                        </div>
                    </section>
                </div>
                <!-- PLace holder for Svea checkout i-frame - end -->

            </div>
        </div>
        <!-- Checkout main content - end -->
    </section>

</div>

<?php echo $footer; ?>
