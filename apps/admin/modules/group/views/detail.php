<ol class="breadcrumb">
  <li><a href="/admin/group/">Groups</a></li>
  <li class="active">Group Detail</li>
</ol>

<?php if(DinklyFlash::exists('good_group_message')): ?>
<div class="alert alert-success">
  <?php echo DinklyFlash::get('good_group_message'); ?>
  <button type="button" class="close message-close" aria-hidden="true">&times;</button>
</div>
<?php endif; ?>

<h3>Group Detail <button type="button" data-toggle="modal" data-target="#delete-group-modal" class="pull-right btn btn-link">Delete Group</button></h3>
<hr>
<div class="row">
	<div class="col-md-7">
		<form class="form-horizontal" role="form" action="" method="post" id="user-detail-form">	  
			<legend>Group Info <button style="" type="button" class="btn btn-xs btn-link btn-edit-group">Edit</button></legend>
			<div class="form-group">
				<label class="col-sm-3 control-label" for="name">Name</label>
				<div class="col-md-5">
					<input value="<?php echo $group->getName(); ?>" type="text" disabled="disabled" class="form-control" id="name" name="name">
				</div>
			</div>	
			<div class="form-group">
				<label class="col-sm-3 control-label" for="abbreviation">Abbreviation</label>
				<div class="col-md-5">
					<input value="<?php echo $group->getAbbreviation(); ?>" type="text" disabled="disabled" class="form-control" id="abbreviation" name="abbreviation">
				</div>
			</div>
			<div class="form-group">
				<label class="col-sm-3 control-label" for="description">Description</label>
				<div class="col-md-7">
					<textarea disabled="disabled" class="form-control" id="description" name="description"><?php echo $group->getDescription(); ?></textarea>
				</div>
			</div>
		</form>
	</div>
	<div class="col-md-5">
		<legend>Permissions <button data-toggle="modal" data-target="#add-permission-modal" type="button" class="btn btn-xs btn-link btn-add-permission">Add</button></legend>
		<table class="table">
			<thead>
				<tr>
					<th>Name</th>
					<th>Action</th>
				</tr>
			</thead>
			<tbody class="table-striped table-hover">
				<?php if($group->getPermissions() != array()): ?>
					<?php foreach($group->getPermissions() as $perm): ?>
						<tr>
							<td><?php echo $perm->getName(); ?></td>
							<td><a href="/admin/group/remove_permission/id/<?php echo $group->getId(); ?>/permission_id/<?php echo $perm->getId(); ?>">remove</a>
						</tr>
					<?php endforeach; ?>
				<?php else: ?>
					<tr>
						<td colspan="3"><em>This group is currently associated with any permissions</em></td>
					</tr>
				<?php endif; ?>
			</tbody>
		</table>
	</div>
</div>

<div class="modal fade" id="add-permission-modal">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h4 class="modal-title">Apply Permission to Group</h4>
			</div>
			<div class="modal-body">
				<?php if($available_permissions != array()): ?>
					<form class="form-horizontal" id="add-permission-form" method="post" action="/admin/group/add_permission/id/<?php echo $group->getId(); ?>" role="form">
						<select id="permission" name="permission[]" class="form-control multiselect" multiple="multiple">
							<?php if($available_permissions != array()): ?>
								<?php foreach($available_permissions as $perm): ?>
									<option value="<?php echo $perm->getId(); ?>"><?php echo $perm->getName(); ?></option>
								<?php endforeach; ?>
							<?php endif; ?>
						</select>
					</form>
				<?php else: ?>
					This group is already associated with all available permissions
				<?php endif; ?>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
				<?php if($available_permissions != array()): ?>
					<button type="button" class="btn btn-primary btn-add-permission-to-group">Apply Selected Permissions to Group</button>
				<?php endif; ?>
			</div>
		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<div class="modal fade" id="delete-group-modal">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title">Delete Group</h4>
      </div>
      <div class="modal-body">
        Are you sure you wish to delete this group?
      </div>
      <div class="modal-footer">
      	<button type="button" class="btn btn-default btn-cancel-delete-group" data-dismiss="modal">No</button>
        <button type="button" class="btn btn-danger btn-delete-group">Yes</button>
      </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<script type="text/javascript">
$(document).ready(function() {
	$('.btn-edit-group').click(function() { 
		window.location = "/admin/group/edit/id/<?php echo $group->getId(); ?>";
		return true;
	});

	$('.btn-add-permission-to-group').click(function() {
		$('#add-permission-form').submit();
		return false;
	});

	$('.btn-delete-group').click(function() {
		window.location = "/admin/group/delete/id/<?php echo $group->getId(); ?>";
	});
});	
</script>