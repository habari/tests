<form action="" accept-charset="UTF-8">
<div class="container navigation">
	<span class="pct70">
		<select name="unit" tabindex="1">
			<option value="all">All tests</option>
			<?php foreach($unit_names as $unit_name): ?>
			<option<?php if (isset($unit) && $unit == $unit_name) echo ' selected'?>><?php echo $unit_name ?></option>
			<?php endforeach; ?>
		</select>
	</span>
<span class="pct10" id="run"><input type="submit" name="run" class="button" value="Run" tabindex="3">
</span>
<span class="pct10" id="run"><input type="submit" name="run" class="button" value="Dry Run" tabindex="3">
</span>

</div>
</form>

<?php if(!empty($error)): ?>
<div class="container">
	<div class='item settings' id='error'>
		<?php echo $error; ?>
	</div>
</div>
<?php endif; ?>


<?php if (isset($results)): ?>

<div class="container">
	<div class="item settings" id="config">
		<ul class="<?php echo $connection_string ?>">
		<li><b>Connection String:</b> <?php echo $connection_string; ?></li>
			<li><b>Direct URL:</b> <a href="<?php echo $direct_url; ?>">HTML</a> &middot; <a href="<?php echo $symbolic_url; ?>">Symbolic</a></li>
			<li><a href="#xml" onclick="$('#xml').toggle();return false;">Show XML results</a></li>
		</ul>
	</div>
</div>

<div class="container" style="display:none;" id="xml">
	<div class="item settings">
		<textarea style="width:100%;height:300px"><?php echo $xmldata; ?></textarea>
	</div>
</div>

<?php elseif(isset($direct_url)): ?>

<div class="container">
	<div class="item settings" id="preconfig">
		<ul class="<?php echo $direct_url; ?>">
			<li><b>Direct URL:</b> <a href="<?php echo $direct_url; ?>">HTML</a> &middot; <a href="<?php echo $symbolic_url; ?>">Symbolic</a></li>
		</ul>
	</div>
</div>

<?php endif; ?>

<?php if (isset($results)): ?>

<div class="container">
	<?php foreach ( $results as $result ): ?>
	<div class='item settings' id='<?php echo $result['name']?>'>
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
		<table class="methods" id="<?php echo $result['name']; ?>">
			<tr><th class="passfail">Result</th><th>Test Method</th><th>Messages</th><th class="hasoutput">Output</th></tr>
			<?php foreach ( $result['methods'] as $method ): ?>
			<tr class="<?php echo $method['result']; ?>">
				<td class="passfail"><?php
					// @todo: Haha, this will be completely untranslatable later...
					echo '<span title="' . $method['result'] . '">';
					switch($method['result']) {
						case 'Pass':
							echo '&#x2714;';break;
						case 'Fail':
							echo '&#x2718;';break;
						case 'Incomplete':
							echo '&#x21e5;';break;
						case 'Skipped':
						case 'Dry Run':
							echo '&#x21b7;';break;
					}
					echo '</span>';
				?></td>
				<td class="methodname"><a href="tests?unit=<?php echo $result['name']; ?>&test=<?php echo $method['name']; ?>&run=Run" title="Run <?php echo $method['name']; ?>"><?php echo $method['name']?></a></td>
				<td class="messages"><?php echo isset( $method['messages'] ) ? $method['messages'] : ''; ?></td>
				<td class="hasoutput <?php echo (! empty( $method['output'] )) ? 'hasoutput_yes' : 'hasoutput_no' ?>"><?php echo (! empty( $method['output'] ) && trim($method['output']) != '') ? '<a href="#">details</a>' : '--' ?></td>
			</tr>
		<?php if (! empty( $method['output'] )):?>
			<tr><td class="output" colspan="4">
				<div class='method_output'><?php echo ($method['output']); ?></div>
			</td></tr>
		<?php endif; ?>
		<?php endforeach; ?>
		</table>
	</div>
	<?php endforeach; ?>
</div>

<script type="text/javascript">
	$(function(){
		$('.hasoutput_yes a').click(function(){
			$(this).parents('tr').next('tr').find('.method_output').slideToggle();
			return false;
		});
	});
</script>

<?php endif; ?>

