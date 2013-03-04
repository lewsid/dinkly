<!DOCTYPE html>
<html lang="en">
  
  <head>
    <meta charset="utf-8">
    <title><?php echo Dinkly::getConfigValue('app_name'); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">
    <!-- Le styles -->
    <link href="/css/bootstrap.css" rel="stylesheet">
    <style>
      body { padding-top: 60px; /* 60px to make the container go all the way
      to the bottom of the topbar */ }
    </style>
    <link href="/css/bootstrap-responsive.css" rel="stylesheet">
    <!-- Le HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
      <script src="http://html5shim.googlecode.com/svn/trunk/html5.js">
      </script>
    <![endif]-->
    <!-- Le fav and touch icons -->
    <link rel="shortcut icon" href="assets/ico/favicon.ico">
    <link rel="apple-touch-icon-precomposed" sizes="144x144" href="assets/ico/apple-touch-icon-144-precomposed.png">
    <link rel="apple-touch-icon-precomposed" sizes="114x114" href="assets/ico/apple-touch-icon-114-precomposed.png">
    <link rel="apple-touch-icon-precomposed" sizes="72x72" href="assets/ico/apple-touch-icon-72-precomposed.png">
    <link rel="apple-touch-icon-precomposed" href="assets/ico/apple-touch-icon-57-precomposed.png">
    
    <script type="text/javascript" src="/js/jquery-1.9.1.min.js"></script>
    <script type="text/javascript" src="/js/bootstrap.js"></script>

    <script type="text/javascript">
    $("#sign-in").click(function() {
      $('#sign-in-form').submit();
    });
    </script>

    <?php echo $this->getModuleHeader(); ?>

  </head>
  <body>
    <div class="navbar navbar-fixed-top navbar-inverse">
      <div class="navbar-inner">
        <div class="container">
          <a class="brand" href="#">
            <?php echo Dinkly::getConfigValue('app_name'); ?>
          </a>
          <ul class="nav">
            <li>
              <a href="/">
                Home
              </a>
            </li>
            <?php if(AdminUser::isLoggedIn()): ?>
            <li>
              <a href="/user/user_list/">
                User List
              </a>
            </li>
            <?php endif; ?>
            <?php if(AdminUser::isLoggedIn()): ?>
            <li>
              <a href="/login/logout/">
                Logout
              </a>
            </li>
            <?php endif; ?>
          </ul>
          <?php if(!AdminUser::isLoggedIn()): ?>
          <form id="sign-in-form" class="navbar-form pull-right" action="/login/" method="post">
            <input name="username" type="text" placeholder="Username" class="span2">
            <input name="password" type="password" placeholder="Password" class="span2">
            <button class="btn" id="sign-in">
              Sign in
            </button>
          </form>
          <?php endif; ?>
        </div>
      </div>
    </div>
    <div class="container">
      <?php if(isset($_SESSION['dinkly']['badlogin'])): ?>
      <div class="alert alert-error">Invalid login</div>
      <?php endif; ?>