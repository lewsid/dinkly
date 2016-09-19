<div class="row">
	<div class="col-md-12" style="text-align: center;">
		<h1>Forget your password?</h1>
		<p>No problem, we'll have this sorted out in no time!</p>
		
		<div class="row">
			<div class="col-md-6 col-md-offset-3">
				<?php if(DinklyFlash::exists('request_error')): ?>
					<div class="alert alert-danger">
						<?php echo DinklyFlash::get('request_error'); ?>
						<button type="button" class="close message-close" aria-hidden="true">&times;</button>
					</div>
				<?php endif; ?>

				<?php if(DinklyFlash::exists('request_success')): ?>
					<div class="alert alert-success">
						<?php echo DinklyFlash::get('request_success'); ?>
					</div>
				<?php endif; ?>
			</div>
		</div>

		<form class="form-inline" action="" method="post" style="padding-top: 50px;">
			<div class="form-group">
				<input id="email" name="email" type="text" class="form-control" placeholder="Enter Email Address">
			</div>
			<button type="submit" class="btn btn-primary">Reset Password</button>
		</form>
	</div>
</div>