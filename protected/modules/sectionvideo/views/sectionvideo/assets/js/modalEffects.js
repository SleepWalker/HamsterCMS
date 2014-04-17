/**
 * modalEffects.js v1.0.0
 * http://www.codrops.com
 *
 * Licensed under the MIT license.
 * http://www.opensource.org/licenses/mit-license.php
 * 
 * Copyright 2013, Codrops
 * http://www.codrops.com
 *
 * Адаптация под Yii и HamsterCMS: dev@udf.su
 */
 $(function() {
	// TODO: если релизить это модальное окно, то нужно доделать обратно 3д эффекты (md-setperspective | md-perspective)

	function hideModals()
	{
		$els = $('.md-show');
		$els.removeClass('md-show');

		setTimeout(function() {$('.md-modal').trigger('close');$els.remove();}, 700);

		return false;
	}

	//$('<div class="md-overlay"></div>').appendTo('body').on('click', hideModals);
	$('body').on('click', '.md-modal .md-close, .md-overlay', hideModals);
	$('body').on('click', '.md-modal', function(e) {e.stopPropagation();});
});