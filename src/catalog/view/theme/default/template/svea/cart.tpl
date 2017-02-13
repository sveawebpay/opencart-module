<table class="table table-products">
	<?php foreach ($products as $product) { ?>
		<tr>
			<td class="text-left td-name">
				<?php echo $product['name']; ?>
				<?php foreach ($product['option'] as $option) { ?>
					<br />
					&nbsp;<small> - <?php echo $option['name']; ?>: <?php echo $option['value']; ?></small>
				<?php } ?>
			</td>
			<td class="text-right td-quantity">x <?php echo $product['quantity']; ?></td>
			<td class="text-right td-total"><?php echo $product['price']; ?></td>
		</tr>
	<?php } ?>

	<?php foreach ($vouchers as $voucher) { ?>
		<tr>
			<td class="text-left td-name"><?php echo $voucher['description']; ?></td>
			<td class="text-right td-quantity">x 1</td>
			<td class="text-right td-total"><?php echo $voucher['amount']; ?></td>
		</tr>
	<?php } ?>
</table>
<table class="table table-totals">
	<?php foreach ($totals as $total) { ?>
		<tr>
			<td class="text-right td-title"><?php echo $total['title']; ?></td>
			<td class="text-right td-value"><?php echo $total['text']; ?></td>
		</tr>
	<?php } ?>
</table>
<a class="sco-change-cart" href="<?php echo $cart; ?>" title="<?php echo $text_change_cart; ?>"><?php echo $text_change_cart; ?></a>
