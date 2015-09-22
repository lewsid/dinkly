<div class="row">
	<div class="col-md-12" style="text-align: center;">
		<h1>Reset Password</h1>
		<p>Set a new password, and you're good to go!</p>
		
		<div class="row">
			<div class="col-md-6 col-md-offset-3">
				<?php if(DinklyFlash::exists('reset_error')): ?>
					<div class="alert alert-danger">
						<?php echo DinklyFlash::get('reset_error'); ?>
						<button type="button" class="close message-close" aria-hidden="true">&times;</button>
					</div>
				<?php endif; ?>

				<?php if(DinklyFlash::exists('reset_success')): ?>
					<div class="alert alert-success">
						<?php echo DinklyFlash::get('reset_success'); ?>
					</div>
				<?php endif; ?>
			</div>
		</div>

		<div class="row">
			<form class="form-inline" action="" method="post" style="padding-top: 50px;">
				<div class="form-group">
					<input placeholder="Enter new password" id="password" name="password" type="password" class="form-control">
				</div>
				<div class="form-group">
					<input placeholder="Confirm password" id="password-confirm" name="password-confirm" type="password" class="form-control">
				</div>
				<button type="submit" class="btn btn-primary">Change Password</button>
			</form>
		</div>
	</div>
</div>