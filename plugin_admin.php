<form action="" accept-charset="UTF-8">
<div class="container navigation">
	<span class="pct75">
		<select name="unit" tabindex="1">
			<option value="all">All tests</option>
			<?php foreach($unit_names as $unit_name): ?>
			<option<?php if (isset($unit) && $unit == $unit_name) echo ' selected'?>><?php echo $unit_name ?></option>
			<?php endforeach; ?>
		</select>
	</span>
<span class="pct20" id="run"><input type="submit" name="run" class="button" value="Run" tabindex="3">
</span>

</div>
</form>


<?php if (isset($results)): ?>

<div class="container">
	<?php foreach ( $results as $result ): ?>
	<div class='item settings clear' id='<?php echo $result['name']?>'>
		<h2><a href="tests?unit=<?php echo $result['name']; ?>&run=Run" title="Run <?php echo $result['name']; ?>"><?php echo $result['name']; ?></a><?php if (isset($test)) echo ': '.$test; ?></h2>
		<ul class="attributes">
			<?php if (isset($test)): ?>
			<li>1 test completed (<?php echo ($result['complete'] - 1); ?> more not executed)</li>
			<?php else: ?>
			<li><?php echo $result['complete']; ?>/<?php echo $result['cases']; ?> tests completed.</li>
			<?php endif; ?>
			<li><?php echo $result['incomplete']; ?> incomplete methods.</li>
			<li><?php echo $result['pass']; ?> assertions passed.</li>
			<li><?php echo $result['fail']; ?> assertions failed.</li>
			<li><?php echo $result['exception']; ?> unexpected exceptions.</li>
		</ul>
		<ul id='<?php echo $result['name']; ?>' class='methods'>
		<?php foreach ( $result['methods'] as $method ): ?>
      <li class='<?php echo $method['result']; ?>'> <span class="name"><a href="tests?unit=<?php echo $result['name']; ?>&test=<?php echo $method['name']; ?>&run=Run" title="Run <?php echo $method['name']; ?>"><?php echo $method['name']?></a></span><span class="messages"><?php echo isset( $method['messages'] ) ? $method['messages'] : ''; ?></span></li>
		<?php endforeach; ?>
		</ul>
	</div>
	<?php endforeach; ?>
</div>

<?php endif; ?>

