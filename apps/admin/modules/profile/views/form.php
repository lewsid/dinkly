<form role="form" action="/admin/profile/"  method="post" id="site-form">
	<div class="row">
		<div class="col-md-12">
			<?php if($errors != array()): ?>
			<div class="alert alert-danger">
				<button type="button" class="close message-close" aria-hidden="true">&times;</button>
				<ul>
					<?php foreach($errors as $error): ?>
						<li><?php echo $error; ?></li>
					<?php endforeach; ?>
				</ul>
			</div>
			<?php endif; ?>
			<div class="row">
				<div class="col-sm-6">
					<legend>Basic Info</legend>
					<div class="form-group">
						<label for="first-name" required="required">First Name</label>
						<input maxlength="24" value="<?php echo $user->getFirstName(); ?>" type="text" class="form-control" id="account-first-name" name="first-name">
					</div>
					<div class="form-group">
						<label for="last-name" required="required">Last Name</label>
						<input maxlength="24" value="<?php echo $user->getLastName(); ?>" type="text" class="form-control" id="account-last-name" name="last-name">
					</div>
					<div class="form-group">
						<label for="title">Title</label>
						<input maxlength="128" value="<?php echo $user->getTitle(); ?>" type="text" class="form-control" id="account-title" name="title">
					</div>
					<hr>
					
					<div class="form-group">
						<label for="email">Email</label>
						<input required="required" maxlength="24" value="<?php echo $user->getUsername(); ?>" type="email" class="form-control" id="account-email" name="email">
					</div>
					<div class="form-group">
						<label for="password">Change Password</label>
						<input maxlength="1024" placeholder="************" type="password" class="form-control" id="account-password" name="password">
						<span class="help-block">Must be at least 8 characters long</span>
					</div>
					<div class="form-group">
						<label for="confirm-password">Confirm Password</label>
						<input placeholder="************" type="password" class="form-control" id="account-confirm-password" name="confirm-password">
						<p id="account-password-match-error" class="text-danger" style="display: none;">Passwords do not match</p>
					</div>
				</div>
				<div class="col-sm-6">
					<legend>Preferences</legend>
					<div class="row">
						<div class="col-md-6">
							<div class="form-group">
								<label for="date-format">Date Format</label>
								<div class="radio">
									<label>
										<input type="radio" name="date-format" class="date-format" value="MM/DD/YY" <?php echo ($user->getDateFormat() == "m/d/y") ? 'checked="checked"' : ''; ?>> MM/DD/YY
									</label>
								</div>
								<div class="radio">
									<label>
										<input type="radio" name="date-format" class="date-format" value="DD/MM/YY" <?php echo ($user->getDateFormat() == "d/m/y") ? 'checked="checked"' : ''; ?>> DD/MM/YY
									</label>
								</div>
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group">
								<label for="time-format">Time Format</label>
								<div class="radio">
									<label>
										<input type="radio" name="time-format" class="time-format" value="12" <?php echo ($user->getTimeFormat() == "g:i a" || $user->getTimeFormat() == '') ? 'checked="checked"' : ''; ?>> 12 hour
									</label>
								</div>
								<div class="radio">
									<label>
										<input type="radio" name="time-format" class="time-format" value="24" <?php echo ($user->getTimeFormat() == "H:i") ? 'checked="checked"' : ''; ?>> 24 hour
									</label>
								</div>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-md-6">
							<div class="form-group">
								<label for="time-format">Time Zone</label>
								<select class="form-control" name="time-zone" id="time-zone">
									<option value="">Select Timezone...</option>
									<?php echo $select_options; ?>
								</select>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<input type="hidden" name="user-id" id="account-user-id" value="<?php echo $user->getId(); ?>">
		<input type="hidden" name="username" id="account-username" value="<?php echo $user->getUsername(); ?>">
	</div>
	<div class="row">
		<hr>
		<div class="col-md-12">
			<button type="submit" class="btn btn-primary btn-save-account">Update Profile</button>
			<a href="/dashboard" class="btn btn-link">Cancel Changes</a>
		</div>
	</div>
</form>