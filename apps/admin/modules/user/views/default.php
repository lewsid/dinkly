<h3>Users <button type="button" class="btn btn-primary btn-add-group pull-right">Create User</button></h3>
<hr>

<table cellpadding="0" cellspacing="0" border="0"  class="table table-striped table-bordered" id="user-list">
  <thead>
    <tr>
      <th>Username</th>
      <th>Created</th>
      <th>Last Login</th>
      <th>Login Count</th>
      <th>Action</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach($users as $pos => $user): ?>
    <tr class="<?php echo ($pos % 2 == 0) ? 'odd' : 'even'; ?>">
      <td><?php echo $user->getUsername(); ?></td>
      <td><?php echo $user->getCreatedAt(date('m-d-Y')); ?></td>
      <td><?php echo $user->getLastLoginAt(date('m-d-Y')); ?></td>
      <td><?php echo $user->getLoginCount(); ?></td>
      <td><a href="/admin/user/detail/id/<?php echo $user->getId(); ?>">view</a>
    </tr> 
    <?php endforeach; ?>
  </tbody>
</table>