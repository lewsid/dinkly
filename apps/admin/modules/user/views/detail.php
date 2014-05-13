<ol class="breadcrumb">
  <li><a href="/admin/user/">Users</a></li>
  <li class="active">User Detail</li>
</ol>

<?php if($saved): ?>
<div class="alert alert-success">
  User Saved
  <button type="button" class="close message-close" aria-hidden="true">&times;</button>
</div>
<?php endif; ?>

<h3>User Detail</h3>
<hr>
<div class="row">
	<div class="col-md-5">
		<form class="form-horizontal" role="form" action="" method="post" id="new-project-form">	  
			<legend>User Info <button style="" type="button" class="btn btn-xs btn-link btn-edit-project">Edit</button></legend>
			<div class="form-group">
				<label class="col-sm-3 control-label" for="created">Created</label>
				<div class="col-md-7">
					<input value="<?php echo $user->getCreatedAt(date('m-d-Y G:i')); ?>" type="text" disabled="disabled" class="form-control" id="created" name="created">
				</div>
			</div>	
			<div class="form-group">
				<label class="col-sm-3 control-label" for="updated">Updated</label>
				<div class="col-md-7">
					<input value="<?php echo $user->getUpdatedAt(date('m-d-Y G:i')); ?>" type="text" disabled="disabled" class="form-control" id="updated" name="updated">
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
		<legend>Groups <button data-toggle="modal" data-target="#new-list-modal" type="button" class="btn btn-xs btn-link btn-add-list">Create</button></legend>
		<table class="table">
			<thead>
				<tr>
					<th>Created</th>
					<th>Title</th>
					<th>Short Name</th>
					<th>Action</th>
				</tr>
			</thead>
			<tbody class="table-striped table-hover">
				
			</tbody>
		</table>
	</div>
</div>

<script type="text/javascript">
$(document).ready(function() {
	$('.btn-edit-project').click(function() { 
		window.location = "/admin/user/edit/id/<?php echo $user->getId(); ?>";
		return true;
	})
});	
</script>