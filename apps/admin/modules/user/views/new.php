<ol class="breadcrumb">
  <li><a href="/admin/user/">Users</a></li>
  <li class="active">Create User</li>
</ol>

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

<h3>New User Info</h3>
<hr>
<div class="row">
  <div class="col-md-5">
    <?php include('form_user.php'); ?>
  </div>
</div>

<script type="text/javascript">
$(document).ready(function() {
  $('.btn-cancel-user').click(function() {
    window.location = "/admin/user/";
  });
});
</script>