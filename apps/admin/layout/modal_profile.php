<div class="modal fade" id="profile-modal">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h4 class="modal-title">Edit Profile</h4>
			</div>
			<div class="modal-body">
				<div class="profile-body">
					<form class="form-horizontal" role="form" action="" method="post" id="user-form">
						<div class="form-group">
							<label for="first-name" class="col-sm-4 control-label">First Name</label>
							<div class="col-md-7">
								<input maxlength="24" value="<?php echo $logged_user->getFirstName(); ?>" type="text" class="form-control" id="profile-first-name" name="first-name">
							</div>
						</div>
						<div class="form-group">
							<label for="last-name" class="col-sm-4 control-label">Last Name</label>
							<div class="col-md-7">
								<input maxlength="24" value="<?php echo $logged_user->getLastName(); ?>" type="text" class="form-control" id="profile-last-name" name="last-name">
							</div>
						</div>
						<div class="form-group">
							<label for="title" class="col-sm-4 control-label">Title</label>
							<div class="col-md-7">
								<input maxlength="128" value="<?php echo $logged_user->getTitle(); ?>" type="text" class="form-control" id="profile-title" name="title">
							</div>
						</div>
						<hr>
						<div class="form-group">
							<label for="title" class="col-sm-4 control-label">Date Format</label>
							<div class="col-md-7">
								<div class="radio">
									<label>
								    	<input type="radio" name="date-format" class="date-format" value="MM/DD/YY" <?php echo ($logged_user->getDateFormat() == "m/d/y") ? 'checked="checked"' : ''; ?>>
								    	MM/DD/YY
								  	</label>
								</div>
								<div class="radio">
									<label>
								    	<input type="radio" name="date-format" class="date-format" value="YYYY-MM-DD" <?php echo ($logged_user->getDateFormat() == "Y-m-d") ? 'checked="checked"' : ''; ?>>
								    	YYYY-MM-DD
								  	</label>
								</div>
							</div>
						</div>
						<hr>
						<div class="form-group">
							<label class="col-sm-4 control-label" for="password">Change Password</label>
							<div class="col-md-7">
								<input required="required" maxlength="1024" placeholder="************" type="password" class="form-control" id="profile-password" name="password">
								<span class="help-block">Must be at least 8 characters long</span>
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-4 control-label" for="confirm-password">Confirm Password</label>
							<div class="col-md-7">
								<input maxlength="1024" placeholder="************" type="password" class="form-control" id="profile-confirm-password" name="confirm-password">
								<p id="profile-password-match-error" class="text-danger" style="display: none;">Passwords do not match</p>
							</div>
						</div>
						<input type="hidden" name="user-id" id="profile-user-id" value="<?php echo $logged_user->getId(); ?>">
						<input type="hidden" name="username" id="profile-username" value="<?php echo $logged_user->getUsername(); ?>">
						<input type="hidden" name="source" id="source" value="profile">
					</form>
				</div>
				<div class="profile-saved" style="display: none; text-align: center; font-size: 28px; color: green">
					<i class="fa fa-check-circle-o"></i> <strong>Profile Updated!</strong>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
				<button type="button" class="btn btn-primary btn-save-profile">Save</button>
			</div>
		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<script type="text/javascript">
$(document).ready(function() {
	$('#profile-first_name').val("<?php echo htmlentities($logged_user->getFirstName()); ?>");
	$('#profile-last_name').val("<?php echo htmlentities($logged_user->getLastName()); ?>");
	$('#profile-title').val("<?php echo htmlentities($logged_user->getTitle()); ?>");

	//Reset profile form state
	$('#profile-modal').on('hidden.bs.modal', function (e) {
  		$('.btn-save-profile').show();
  		$('.profile-body').show();
  		$('.profile-saved').hide();

  		$('.form-group').removeClass('has-error');
	})

	$('.btn-save-profile').click(function() {
		var valid = false;
		if(validateRequired($('#profile-first-name')) && validateRequired($('#profile-last-name'))) { 
			valid = true;
		}

		if(($('#profile-password').val() != $('#profile-confirm-password').val()) && $('#profile-password').val().length > 0) {
			$('#profile-password').parents('.form-group').addClass('has-error');
			$('#profile-confirm-password').parents('.form-group').addClass('has-error');
			$('#profile-password-match-error').show();
		}
		else {
			$('#profile-password').parents('.form-group').removeClass('has-error');
			$('#profile-confirm-password').parents('.form-group').removeClass('has-error');
			$('#profile-password-match-error').hide();
		}

		if(valid) {
			$.ajax({
				type: "POST",
				url: "/admin/user/edit/id/" + $('#profile-user-id').val(),
				data: { 
					'source': $('#source').val(),
					'user-id': $('#profile-user-id').val(),
					'first-name': $('#profile-first-name').val(),
					'last-name': $('#profile-last-name').val(),
					'username': $('#profile-username').val(),
					'title': $('#profile-title').val(),
					'password': $('#profile-password').val(),
					'confirm-password': $('#profile-confirm-password').val(),
					'date-format': $('.date-format:checked').val()
				},
				success: function(response) {
					if(response == 'success') {
						$('.btn-save-profile').fadeOut();
						$('.profile-body').fadeOut('fast', function() {
							$('.profile-saved').fadeIn();
						});
					}
				}
			});
		}

		return false;
	});
});
</script>