<div class="hero-unit">
  <div>
  	<?php if(AuthUser::isLoggedIn()): ?>
  	<h4>
	  <h4>Logged in as <?php echo AuthUser::getUsername(); ?></h4>
  	</h4>
  	<?php endif; ?>
    <h1>
      <?php echo Dinkly::getConfigValue('app_name'); ?>
    </h1>
    <p>
      <?php echo Dinkly::getConfigValue('app_description'); ?>
    </p>
  </div>
</div>