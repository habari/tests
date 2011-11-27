<?php $form->out(); ?>

<?php if (isset($results)): ?>

<div class="container">
	<div class='item clear'><h2>Tests</h2><h3><span class='pct30 last'>Name</span><span class='pct10'>Complete</span><span class='pct10'>Passed</span><span class='pct10'>Failed</span></h3></div>
	<?php foreach ( $units as $unit ): ?>
	<div class='item settings clear' id='<?php echo $unit['name']?>'>
		<span class='pct30'><?php echo $unit['name']?></span><span class='pct10'><?php echo $unit['complete']?></span><span class='pct10'><?php echo $unit['pass']?></span><span class='pct10'><?php echo $unit['fail']?></span>
	</div>
	<?php endforeach; ?>
</div>

<?php endif; ?>
