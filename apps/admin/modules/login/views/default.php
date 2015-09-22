<?php if(DinklyFlash::exists('reset_success')): ?>
  <div class="alert alert-success">
    <?php echo DinklyFlash::get('reset_success'); ?>
  </div>
<?php endif; ?>
<div class="row">
  <div class="col-md-6 col-md-offset-3 admin-login" align="center">
    <div class="panel panel-primary" align="center" style="width: 80%;">
      <div class="panel-heading">Administrator Access</div>
      <div class="panel-body">
        <?php if(DinklyFlash::exists('invalid_login')): ?>
        <div class="alert alert-danger">
          <?php echo DinklyFlash::get('invalid_login'); ?>
          <button type="button" class="close message-close" aria-hidden="true">&times;</button>
        </div>
        <?php endif; ?>
        <form id="sign-in-form" action="/admin/login/" class="form-horizontal" role="form" method="post">
          <div class="form-group">
            <label for="inputEmail3" class="col-sm-3 control-label">Email</label>
            <div class="col-sm-8">
              <input type="email" class="form-control" id="inputEmail3" placeholder="Email" name="username">
            </div>
          </div>
          <div class="form-group">
            <label for="inputPassword3" class="col-sm-3 control-label">Password</label>
            <div class="col-sm-8">
              <input type="password" class="form-control" id="inputPassword3" placeholder="Password" name="password">
            </div>
          </div>
          <div class="form-group">
            <label for="signin-button" class="col-sm-3 control-label"></label>
            <div class="col-sm-8" align="left">
              <button name="signin" id="signin-button" type="submit" class="btn btn-primary">Sign in</button>
              <a class="btn btn-link forgot-password" href="/admin/login/forgot_password">Forgot your password?</a>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>