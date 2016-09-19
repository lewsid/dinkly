<div class="jumbotron">
	<h2>404 - Page Not Found</h2>
	
	<?php if($requested_app): ?>
		<h4>Requested App: <span style="font-weight: normal"><?php echo $requested_app; ?></span></h4>
	<?php endif; ?>

	<?php if($requested_module): ?>
		<h4>Requested Module: <span style="font-weight: normal"><?php echo $requested_module; ?></span></h4>
	<?php endif; ?>

	<?php if($requested_view): ?>
		<h4>Requested View: <span style="font-weight: normal"><?php echo $requested_view; ?></span></h4>
	<?php endif; ?>
</div>