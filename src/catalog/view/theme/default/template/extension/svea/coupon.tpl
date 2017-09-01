<?php if ($coupon) { ?>
    <!-- Coupon successful - start -->
    <div class="input-group">
        <span class="sco-input-label"><?php echo $item_coupon; ?></span>
        <div class="form-control sco-input" style="line-height: 35px;">
            <b><?php echo $coupon['code']; ?></b>
        </div>
        <span class="input-group-btn">
                <button class="btn btn-danger sco-btn-danger" id="sco-coupon-remove">
                    <i class="glyphicon glyphicon-trash"></i>
                </button>
            </span>
    </div>
    <script>
      $('#coupon-toggle-btn').addClass('used');
    </script>
    <!-- Coupon successful - end -->
    <?php } else { ?>
    <!-- Coupon unsuccessfully - start -->
    <div class="input-group">
        <input type="text" class="form-control sco-input" name="coupon" placeholder="Coupon"/>
        <span class="input-group-btn">
                    <button class="btn sco-primary-btn" id="sco-coupon-add" type="button">
                        <i class="glyphicon glyphicon-plus"></i>
                    </button>
                </span>
    </div>
    <script>
      $('#coupon-toggle-btn').removeClass('used');
    </script>
    <!-- Coupon unsuccessfully - end -->
<?php } ?>