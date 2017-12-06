<?php if ($coupon) { ?>
    <!-- Coupon successful - start -->
    <div class="input-group">
        <div class="form-control"><?php echo $text_coupon_code; ?> <b><?php echo $coupon['code']; ?></b></div>
        <span class="input-group-btn">
            <button class="btn btn-danger" id="sco-coupon-remove">
                <i class="glyphicon glyphicon-trash"></i>
            </button>
        </span>
    </div>
    <!-- Coupon successful - end -->
<?php } else { ?>
    <!-- Coupon unsuccessfully - start -->
    <div class="input-group">
        <input type="text" class="form-control" name="coupon"/>
        <span class="input-group-btn">
            <button class="btn btn-primary" id="sco-coupon-add" type="button">
                <i class="glyphicon glyphicon-plus"></i>
            </button>
        </span>
    </div>
    <!-- Coupon unsuccessfully - end -->
<?php } ?>
