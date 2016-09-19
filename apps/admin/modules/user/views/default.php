<?php if(DinklyFlash::exists('warning_user_message')): ?>
<div class="alert alert-warning">
  <?php echo DinklyFlash::get('warning_user_message'); ?>
  <button type="button" class="close message-close" aria-hidden="true">&times;</button>
</div>
<?php endif; ?>

<h3>Users <button type="button" class="btn btn-primary btn-create-user pull-right">Create User</button></h3>
<hr>

<?php if($users != array()): ?>
<table cellpadding="0" cellspacing="0" border="0"  class="table table-striped table-bordered dinkly-datatable" id="user-list">
  <thead>
    <tr>
      <th>Username</th>
      <th>Created</th>
      <th>Last Login</th>
      <th>Login Count</th>
      <th class="no-sort">Action</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach($users as $pos => $user): ?>
    <tr class="<?php echo ($pos % 2 == 0) ? 'odd' : 'even'; ?>">
      <td><?php echo $user->getUsername(); ?></td>
      <td><?php echo $user->getCreatedAt($date_format); ?></td>
      <td><?php echo $user->getLastLoginAt($date_format); ?></td>
      <td><?php echo $user->getLoginCount(); ?></td>
      <td><a href="/admin/user/detail/id/<?php echo $user->getId(); ?>">view</a></td>
    </tr> 
    <?php endforeach; ?>
  </tbody>
</table>
<?php else: ?>
  No users to display
<?php endif; ?>

<script type="text/javascript">
$(document).ready(function() {
  $('.btn-create-user').click(function() { 
    window.location = "/admin/user/new/";
    return true;
  });
}); 
</script>