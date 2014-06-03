<ol class="breadcrumb">
  <li><a href="/admin/user/">Users</a></li>
  <li class="active">User Detail</li>
</ol>

<?php if(DinklyFlash::exists('good_user_message')): ?>
<div class="alert alert-success">
  <?php echo DinklyFlash::get('good_user_message'); ?>
  <button type="button" class="close message-close" aria-hidden="true">&times;</button>
</div>
<?php endif; ?>

<h3>User Detail <button type="button" data-toggle="modal" data-target="#delete-user-modal" class="pull-right btn btn-link">Delete User</button></h3>
<hr>
<div class="row">
	<div class="col-md-5">
		<form class="form-horizontal" role="form" action="" method="post" id="user-detail-form">	  
			<legend>User Info <button style="" type="button" class="btn btn-xs btn-link btn-edit-user">Edit</button></legend>
			<div class="form-group">
				<label class="col-sm-3 control-label" for="created">Created</label>
				<div class="col-md-7">
					<input value="<?php echo $user->getCreatedAt($date_format); ?>" type="text" disabled="disabled" class="form-control" id="created" name="created">
				</div>
			</div>	
			<div class="form-group">
				<label class="col-sm-3 control-label" for="updated">Updated</label>
				<div class="col-md-7">
					<input value="<?php echo $user->getUpdatedAt($date_format); ?>" type="text" disabled="disabled" class="form-control" id="updated" name="updated">
				</div>
			</div>
			<hr>
			<div class="form-group">
				<label class="col-sm-3 control-label" for="username">Username</label>
				<div class="col-md-7">
					<input value="<?php echo $user->getUsername(); ?>" type="text" disabled="disabled" class="form-control" id="username" name="username">
				</div>
			</div>
			<div class="form-group">
				<label class="col-sm-3 control-label" for="password">Password</label>
				<div class="col-md-7">
					<input value="************" type="password" disabled="disabled" class="form-control" id="password" name="password">
				</div>
			</div>		
			<div class="form-group">
				<label for="first-name" class="col-sm-3 control-label">First Name</label>
				<div class="col-md-7">
					<input value="<?php echo $user->getFirstName(); ?>" type="text" disabled="disabled" class="form-control" id="first-name" name="first-name">
				</div>
			</div>
			<div class="form-group">
				<label for="last-name" class="col-sm-3 control-label">Last Name</label>
				<div class="col-md-7">
					<input value="<?php echo $user->getLastName(); ?>" type="text" disabled="disabled" class="form-control" id="last-name" name="last-name">
				</div>
			</div>
			<div class="form-group">
				<label for="title" class="col-sm-3 control-label">Title</label>
				<div class="col-md-7">
					<input value="<?php echo $user->getTitle(); ?>" type="text" disabled="disabled" class="form-control" id="title" name="title">
				</div>
			</div>
		</form>
	</div>
	<div class="col-md-7">
		<legend>Groups <button data-toggle="modal" data-target="#add-group-modal" type="button" class="btn btn-xs btn-link btn-add-group">Add</button></legend>
		<table class="table">
			<thead>
				<tr>
					<th>Name</th>
					<th>Abbreviation</th>
					<th>Action</th>
				</tr>
			</thead>
			<tbody class="table-striped table-hover">
				<?php if($user->getGroups() != array()): ?>
					<?php foreach($user->getGroups() as $group): ?>
						<tr>
							<td><?php echo $group->getName(); ?></td>
							<td><?php echo $group->getAbbreviation(); ?></td>
							<td><a href="/admin/user/remove_group/id/<?php echo $user->getId(); ?>/group_id/<?php echo $group->getId(); ?>">remove</a>
						</tr>
					<?php endforeach; ?>
				<?php else: ?>
					<tr>
						<td colspan="3"><em>This user is currently not in any groups</em></td>
					</tr>
				<?php endif; ?>
			</tbody>
		</table>
	</div>
</div>

<div class="modal fade" id="add-group-modal">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h4 class="modal-title">Add User to Group</h4>
			</div>
			<div class="modal-body">
				<?php if($available_groups != array()): ?>
					<form class="form-horizontal" id="add-group-form" method="post" action="/admin/user/add_group/id/<?php echo $user->getId(); ?>" role="form">
						<select id="group" name="group[]" class="form-control multiselect" multiple="multiple">
							<?php if($available_groups != array()): ?>
								<?php foreach($available_groups as $group): ?>
									<option value="<?php echo $group->getId(); ?>"><?php echo $group->getName(); ?></option>
								<?php endforeach; ?>
							<?php endif; ?>
						</select>
					</form>
				<?php else: ?>
					This user is already in all available groups
				<?php endif; ?>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
				<?php if($available_groups != array()): ?>
					<button type="button" class="btn btn-primary btn-add-user-to-group">Add User to Selected Groups</button>
				<?php endif; ?>
			</div>
		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<div class="modal fade" id="delete-user-modal">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title">Delete User</h4>
      </div>
      <div class="modal-body">
        Are you sure you wish to delete this user?
      </div>
      <div class="modal-footer">
      	<button type="button" class="btn btn-default btn-cancel-delete-user" data-dismiss="modal">No</button>
        <button type="button" class="btn btn-danger btn-delete-user">Yes</button>
      </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<script type="text/javascript">
$(document).ready(function() {
	$('.btn-edit-user').click(function() { 
		window.location = "/admin/user/edit/id/<?php echo $user->getId(); ?>";
		return true;
	});

	$('.btn-add-user-to-group').click(function() {
		$('#add-group-form').submit();
		return false;
	});

	$('.btn-delete-user').click(function() {
		window.location = "/admin/user/delete/id/<?php echo $user->getId(); ?>";
	});
});	
</script>