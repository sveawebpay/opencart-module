<?php if ($voucher) { ?>
	<!-- Voucher successful - start -->
	<div class="input-group">
		<span class="sco-input-label"><?php echo $item_voucher; ?></span>
		<div class="form-control sco-input" style="line-height: 35px;">
			<b><?php echo $voucher['code']; ?></b>
		</div>
		<span class="input-group-btn">
					<button class="btn btn-danger sco-btn-danger" id="sco-voucher-remove"><i class="glyphicon glyphicon-trash"></i></button>
				</span>
	</div>
	<script>
	  $('#voucher-toggle-btn').addClass('used');
	</script>
	<!-- Voucher successful - end -->
	<?php } else { ?>
	<!-- Voucher unsuccessfully - start -->
	<div class="input-group">
		<input type="text"  placeholder="Voucher code" class="form-control sco-input" name="voucher" />
		<span class="input-group-btn">
					<button class="btn sco-primary-btn" id="sco-voucher-add" type="button"><i class="glyphicon glyphicon-plus"></i></button>
				</span>
	</div>
	<script>
	  $('#voucher-toggle-btn').removeClass('used');
	</script>
	<!-- Voucher unsuccessfully - end -->
<?php } ?>