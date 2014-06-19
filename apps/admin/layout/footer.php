      <div class="footer">
      	<hr>
    	© <?php echo Dinkly::getConfigValue('copyright', 'admin'); ?> <?php echo date('Y'); ?>   
      </div>
  </div><!-- Primary Container -->

  <?php if(DinklyUser::isLoggedIn()): ?>
  	<?php include('modal_profile.php'); ?>
  <?php endif; ?>

  <?php echo $this->getModuleFooter(); ?>
  
  </body>
</html>
