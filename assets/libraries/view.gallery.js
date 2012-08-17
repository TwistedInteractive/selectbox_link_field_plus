(function($, undefined){

	$(sblp).on('sblp.initialization', function(){

		// register views
		$('div.sblp-view-gallery').each(function(){
			var $view = $(this);
			sblp.views[$view.attr('id')] = new sblp.SBLPView_Gallery($view);
		});

	});

})(jQuery);
