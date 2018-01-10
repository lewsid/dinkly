<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title><?php echo Dinkly::getConfigValue('app_name', 'admin'); ?> v<?php echo Dinkly::getConfigValue('dinkly_version', 'global'); ?></title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="description" content="">
	<meta name="author" content="">

	<link href="/css/bootstrap.4.0.0-beta.3.min.css" rel="stylesheet">
	<link href="/css/fontawesome-all.5.0.3.min.css" rel="stylesheet">
	<link href="/css/datatables.1.10.16.combined.min.css" rel="stylesheet">
	<link href="/css/dinkly.3.29.css" rel="stylesheet">

	<?php echo $this->getModuleHeader(); ?>
</head>
<body>
	<?php if(Dinkly::isDevMode()): ?>
		<!-- Handy Dev Mode Info Label -->
		<h3 class="dev-mode-indicator-label">
			<span class="badge badge-warning">
				Dev Mode
			</span>
		</h3>
		<h3 class="dev-mode-info-label">
			<span class="badge badge-info">
				<?php echo $this->getCurrentModule(); ?> -> <?php echo $this->getCurrentView(); ?>
			</span>
		</h3>
	<?php endif; ?>