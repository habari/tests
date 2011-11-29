<form action="" accept-charset="UTF-8">
<div class="container navigation">
	<span class="pct40">
		<select name="unit" tabindex="1">
			<option value="all">All tests</option>
			<?php foreach($unit_names as $unit_name): ?>
			<option<?php if (isset($unit) && $unit == $unit_name) echo ' selected'?>><?php echo $unit_name ?></option>
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
	<?php foreach ( $results as $result ): ?>
	<div class='item settings clear' id='<?php echo $result['name']?>'>
		<h2><a href="tests?unit=<?php echo $result['name']; ?>&run=Run" title="Run <?php echo $result['name']; ?>"><?php echo $result['name']; ?></a></h2>
		<h4><?php echo $result['complete']; ?>/<?php echo $result['cases']; ?> tests completed.</h4>
		<h4><?php echo $result['incomplete']; ?> incomplete methods.</h4>
		<h4><?php echo $result['pass']; ?> assertions passed.</h4>
		<h4><?php echo $result['fail']; ?> assertions failed.</h4>
		<h4><?php echo $result['exception']; ?> unexpected exceptions.</h4>
		<ul id='<?php echo $result['name']; ?>' class='methods'>
		<?php foreach ( $result['methods'] as $method ): ?>
      <li class='<?php echo $method['result']; ?>'> <span class="name"><a href="tests?unit=<?php echo $result['name']; ?>&test=<?php echo $method['name']; ?>&run=Run" title="Run <?php echo $method['name']; ?>"><?php echo $method['name']?></a></span><span class="messages"><?php echo isset( $method['messages'] ) ? $method['messages'] : ''; ?></span></li>
		<?php endforeach; ?>
		</ul>
	</div>
	<?php endforeach; ?>
</div>

<?php endif; ?>

