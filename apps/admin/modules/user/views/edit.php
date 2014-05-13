<ol class="breadcrumb">
  <li><a href="/admin/user/">Users</a></li>
  <li><a href="/admin/user/detail/id/<?php echo $user->getId(); ?>">User Detail</a></li>
  <li class="active">Edit User Info</li>
</ol>
<h3>Edit User Info <button type="button" data-toggle="modal" data-target="#delete-user-modal" class="pull-right btn btn-link">Delete User</button></h3>
<hr>
<div class="row">
  <div class="col-md-5">
    <?php include('form_user.php'); ?>
  </div>
</div>

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
	$('.btn-cancel-user').click(function() {
		window.location = "/admin/user/detail/id/<?php echo $user->getId(); ?>";
	});

	$('.btn-delete-user').click(function() {
		window.location = "/admin/user/delete/id/<?php echo $user->getId(); ?>";
	});
});
</script>