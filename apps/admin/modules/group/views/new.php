<ol class="breadcrumb">
  <li><a href="/admin/group/">Group</a></li>
  <li class="active">Create Group</li>
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

<h3>New Group Info</h3>
<hr>
<div class="row">
  <div class="col-md-7">
    <?php include('form_group.php'); ?>
  </div>
</div>

<script type="text/javascript">
$(document).ready(function() {
  $('.btn-cancel-group').click(function() {
    window.location = "/admin/group/";
  });
});
</script>