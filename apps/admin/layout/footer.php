	</div><!-- Primary Container -->

	<?php echo $this->getModuleFooter(); ?>

	<?php if(DinklyUser::isLoggedIn()): ?>
		<?php include('modal_profile.php'); ?>
	<?php endif; ?>

	</body>
</html>
