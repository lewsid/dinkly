<div class="main">
	<div class="container">
		<?php if(DinklyFlash::exists('good_user_message')): ?>
			<div class="alert alert-success">
			<?php echo DinklyFlash::get('good_user_message'); ?>
				<button type="button" class="close message-close" aria-hidden="true">&times;</button>
			</div>
		<?php endif; ?>
		<?php if(DinklyFlash::exists('error')): ?>
			<div class="alert alert-danger">
				<?php echo DinklyFlash::get('error'); ?>
				<button type="button" class="close message-close" aria-hidden="true">&times;</button>
			</div>
		<?php endif; ?>

		<div class="row">
			<div class="col-xs-12">
				<div class="section">
					<div class="section-header">
						<h1>Edit Profile</h1>
					</div>
					<div class="section-content">
						<?php include('form.php'); ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>