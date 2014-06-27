<?php if(Dinkly::getCurrentModule() != 'home' 
	|| (Dinkly::getCurrentModule() == 'home' && Dinkly::getCurrentView() != 'default')): ?>
	<ol class="breadcrumb">
		<li><a href="/doc/">Table of Contents</a></li>
		<?php if(Dinkly::getCurrentModule() != 'home'): ?>
			<?php if(Dinkly::getCurrentView() == 'default'): ?>
				<li><?php echo ucwords(str_replace('_', ' ', Dinkly::getCurrentModule())); ?></li>
			<?php else: ?>
				<li><a href="/doc/<?php echo Dinkly::getCurrentModule(); ?>"><?php echo ucwords(str_replace('_', ' ', Dinkly::getCurrentModule())); ?></a></li>
				<li><?php echo ucwords(str_replace('_', ' ', Dinkly::getCurrentView())); ?></li>
			<?php endif; ?>
		<?php endif; ?>
	</ol>
<?php endif; ?>