<form class="form-horizontal" role="form" action="" method="post" id="user-form">	  
	<div class="form-group">
		<label class="col-sm-4 control-label" for="username">Username</label>
		<div class="col-md-7">
			<input value="<?php echo $user->getUsername(); ?>" type="text" class="form-control" id="username" name="username">
		</div>
	</div>
	<div class="form-group">
		<label class="col-sm-4 control-label" for="password">Password</label>
		<div class="col-md-7">
			<input placeholder="************" type="password" class="form-control" id="password" name="password">
		</div>
	</div>
	<div class="form-group">
		<label class="col-sm-4 control-label" for="password">Confirm Password</label>
		<div class="col-md-7">
			<input placeholder="************" type="password" class="form-control" id="confirm-password" name="confirm-password">
			<p id="password-match-error" class="text-danger" style="display: none;">Passwords do not match</p>
		</div>
	</div>
	<div class="form-group">
		<label for="first-name" class="col-sm-4 control-label">First Name</label>
		<div class="col-md-7">
			<input value="<?php echo $user->getFirstName(); ?>" type="text" class="form-control" id="first-name" name="first-name">
		</div>
	</div>
	<div class="form-group">
		<label for="last-name" class="col-sm-4 control-label">Last Name</label>
		<div class="col-md-7">
			<input value="<?php echo $user->getLastName(); ?>" type="text" class="form-control" id="last-name" name="last-name">
		</div>
	</div>
	<div class="form-group">
		<label for="title" class="col-sm-4 control-label">Title</label>
		<div class="col-md-7">
			<input value="<?php echo $user->getTitle(); ?>" type="text" class="form-control" id="title" name="title">
		</div>
	</div>
	<div class="form-group edit-user-controls">
		<label for="btn-create-user" class="col-sm-4 control-label"></label>
		<div class="col-md-7">
			<button type="button" class="btn btn-primary btn-save-user">Save</button>
			<button type="button" class="btn btn-link btn-cancel-user" data-dismiss="modal">Cancel</button>
		</div>
	</div>
</form>

<script type="text/javascript">
$(document).ready(function() {
  $('.btn-save-user').click(function() {
    var valid = true;
    valid = validateRequired($('#username'));
    valid = validateRequired($('#first-name'));
    valid = validateRequired($('#last-name'));

    if(($('#password').val() != $('#confirm-password').val()) && $('#password').val().length > 0) {
    	$('#password').parents('.form-group').addClass('has-error');
    	$('#confirm-password').parents('.form-group').addClass('has-error');
    	$('#password-match-error').show();
    }
    else {
    	$('#password').parents('.form-group').removeClass('has-error');
    	$('#confirm-password').parents('.form-group').removeClass('has-error');
    	$('#password-match-error').hide();
    }

    if(valid) {
      $('#user-form').submit();
    }
  });
});
</script>