<?php if ($coupon) { ?>
    <!-- Coupon successful - start -->
    <div class="input-group">
        <span class="sco-input-label"><?php echo $item_coupon; ?></span>
        <div id="sco-coupon-input" class="form-control sco-input" style="line-height: 35px;">
            <?php echo $coupon['code']; ?>
        </div>
        <span class="input-group-btn">
                <button class="btn btn-danger sco-btn-danger" id="sco-coupon-remove">
                    <i id="sco-coupon-button-icon" class="glyphicon glyphicon-trash"></i>
                </button>
            </span>
    </div>
    <script>
      $('#coupon-toggle-btn').addClass('used');
      $('#sco-coupon-input').addClass('sco-input-applied');
    </script>
    <!-- Coupon successful - end -->
    <?php } else { ?>
    <!-- Coupon unsuccessfully - start -->
    <div class="input-group">
        <input type="text" class="form-control sco-input" name="coupon" placeholder="<?php echo $item_coupon; ?>"/>
        <span class="input-group-btn">
                    <button class="btn sco-primary-btn" id="sco-coupon-add" type="button">
                        <i id="sco-coupon-button-icon" class="glyphicon glyphicon-plus"></i>
                    </button>
                </span>
    </div>
    <script>
      $('#coupon-toggle-btn').removeClass('used');
      $('#sco-coupon-input').removeClass('sco-input-applied');
    </script>
    <!-- Coupon unsuccessfully - end -->
<?php } ?>