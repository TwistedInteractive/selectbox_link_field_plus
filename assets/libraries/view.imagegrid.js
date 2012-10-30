(function($, undefined){

	$(sblp).on('sblp.initialization', function(){

		// register views
		$('div.sblp-view-imagegrid').each(function(){
			var $view = $(this);
			sblp.views[$view.attr('id')] = new sblp.SBLPView_ImageGrid($view);
		});

	});

})(jQuery);
