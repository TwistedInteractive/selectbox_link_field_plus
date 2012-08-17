(function($, undefined){

	$(sblp).on('sblp.initialization', function(){

		// register views
		$('div.sblp-view-checkboxes').each(function(){
			var $view = $(this);
			sblp.views[$view.attr('id')] = new sblp.SBLPView_Checkboxes($view);
		});

	});

})(jQuery);
