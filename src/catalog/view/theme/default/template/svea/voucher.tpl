<?php if ($voucher) { ?>
	<!-- Voucher successful - start -->
	<div class="input-group">
		<div class="form-control"><?php echo $text_voucher_code; ?> <b><?php echo $voucher['code']; ?></b></div>
		<span class="input-group-btn">
			<button class="btn btn-danger" id="sco-voucher-remove"><i class="glyphicon glyphicon-trash"></i></button>
		</span>
	</div>
	<!-- Voucher successful - end -->
<?php } else { ?>
	<!-- Voucher unsuccessfully - start -->
	<div class="input-group">
		<input type="text" class="form-control" name="voucher" />
		<span class="input-group-btn">
			<button class="btn btn-primary" id="sco-voucher-add" type="button"><i class="glyphicon glyphicon-plus"></i></button>
		</span>
	</div>
	<!-- Voucher unsuccessfully - end -->
<?php } ?>
