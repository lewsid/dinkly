<div class="navbar navbar-inverse navbar-fixed-top" role="navigation">
  <div class="container">
    <div class="navbar-header">
      <a class="navbar-brand" href="/admin">
        <?php echo Dinkly::getConfigValue('app_name', 'admin'); ?>
      </a>
    </div>
    <div class="navbar-collapse collapse">
      <ul class="nav navbar-nav">
          <li <?php echo (Dinkly::getCurrentModule() == 'home') ? 'class="active"' : ''; ?>>
              <a href="/admin">Home</a>
          </li>
        <?php if(DinklyUser::isMemberOf('admin')): ?>
        <li <?php echo (Dinkly::getCurrentModule() == 'user') ? 'class="active"' : ''; ?>>
          <a href="/admin/user/">Users</a>
        </li>
        <li <?php echo (Dinkly::getCurrentModule() == 'group') ? 'class="active"' : ''; ?>>
          <a href="/admin/group/">Groups</a>
        </li>
        <?php endif; ?>
      </ul>
      <ul class="nav navbar-nav pull-right dinkly-admin-user-menu">
        <?php if(DinklyUser::isLoggedIn()): ?>
        <li>
          <div class="btn-group">
            <button type="button" class="btn  btn-sm btn-primary dropdown-toggle" data-toggle="dropdown">
              <?php echo DinklyUser::getLoggedUsername(); ?> <span class="caret"></span>
            </button>
            <ul class="dropdown-menu pull-right" role="menu">
              <li><a href="/admin/profile">Edit Profile</a></li>
              <li role="presentation" class="divider"></li>
              <li><a href="/admin/login/logout/">Logout</a></li>
            </ul>
          </div>
        </li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</div>