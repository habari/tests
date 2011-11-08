<div class="container navigation">
	<span class="pct40">
		<select name="navigationdropdown" onchange="navigationDropdown.filter();" tabindex="1">
			<option value="all">All tests</option>
			<?php foreach($units as $unit): ?>
			<option><?php echo $unit; ?></option>
			<?php endforeach; ?>
		</select>
	</span>
	<span class="or pct20"> or </span>
	<span class="pct40">
		<input type="search" id="search" placeholder="search tests" tabindex="2">
	</span>
</div>

	<?php echo $content; ?>