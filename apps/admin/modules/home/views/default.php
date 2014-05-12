<?php if($invalid_login): ?>
<div class="alert alert-danger">
  Invalid login
  <button type="button" class="close login-error-close" aria-hidden="true">&times;</button>
</div>
<?php endif; ?>
<div class="jumbotron">
  <div>
    <h1>
      <?php echo Dinkly::getConfigValue('app_name', 'admin'); ?>
    </h1>
    <p>
      <?php echo Dinkly::getConfigValue('app_description', 'admin'); ?>
      (v<?php echo Dinkly::getConfigValue('dinkly_version', 'global'); ?>)
    </p>
  </div>
</div>