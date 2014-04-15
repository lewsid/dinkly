<!DOCTYPE html>
<html lang="en">
  
  <head>
    <meta charset="utf-8">
    <title><?php echo Dinkly::getConfigValue('app_name'); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">
    <!-- Le styles -->
    <link href="/css/bootstrap-theme.css" rel="stylesheet">
    <link href="/css/bootstrap-theme.min.css" rel="stylesheet">
    <link href="/css/bootstrap.css" rel="stylesheet">
    <link href="/css/bootstrap.min.css" rel="stylesheet">
    <style>
      body { padding-top: 60px; /* 60px to make the container go all the way
      to the bottom of the topbar */ }
    </style>
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
    <div class="navbar navbar-inverse navbar-fixed-top" role="navigation">
      <div class="container">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="#">
            <?php echo Dinkly::getConfigValue('app_name'); ?>
          </a>
        </div>
        <div class="navbar-collapse collapse">
          <ul class="nav navbar-nav">
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
            </ul>
          <div class="nav navbar-nav navbar-right" >
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
            <div class="form-group">
              <input name="username" type="text" placeholder="Username" class="form-control">
            </div>
            <div class="form-group">
             <input name="password" type="password" placeholder="Password" class="form-control">
            </div>
            <div class="form-group">
              <button class="btn btn-success" id="sign-in">
                Sign in
              </button>
            </div>
          </div>
          <?php endif; ?>
        </div><!--/.navbar-collapse -->
      </div>
    </div>
  </div>
    <div class="container">
      <?php if(isset($_SESSION['dinkly']['badlogin'])): ?>
      <div class="alert alert-error">Invalid login</div>
      <?php endif; ?>