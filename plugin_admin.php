<form action="" accept-charset="UTF-8">
<div class="container navigation">
	<span class="pct40">
		<select name="test" tabindex="1">
			<option value="all">All tests</option>
			<?php foreach($units as $unit): ?>
			<option<?php if (isset($test) && $test == $unit) echo ' selected'?>><?php echo $unit ?></option>
			<?php endforeach; ?>
		</select>
	</span>
	<span class="or pct20"> or </span>
	<span class="pct40">
		<input type="search" id="search" placeholder="search tests" tabindex="2">
	</span>
</div>

<div class="container transparent formcontrol" id="run"><input type="submit" name="run" class="button" value="Run" tabindex="3">
</div>
</form>


<?php if (isset($results)): ?>

<div class="container">
	<div class='item clear'><h2>Tests</h2><h3><span class='pct30 last'>Name</span><span class='pct10'>Complete</span><span class='pct10'>Passed</span><span class='pct10'>Failed</span></h3></div>
	<?php foreach ( $results as $result ): ?>
	<div class='item settings clear' id='<?php echo $result['name']?>'>
		<span class='pct30'><?php echo $result['name']?></span><span class='pct10'><?php echo $result['complete']?></span><span class='pct10'><?php echo $result['pass']?></span><span class='pct10'><?php echo $result['fail']?></span><span class='pct40'>&nbsp;</span>
		<ul id='<?php echo "{$result['name']}_{$method}"?>'>
		<?php foreach ( $result['methods'] as $method ): ?>
			<li><?php echo $method['name']?></li>
		<?php endforeach; ?>
		</ul>
	</div>
	<?php endforeach; ?>
</div>

<?php endif; ?>

