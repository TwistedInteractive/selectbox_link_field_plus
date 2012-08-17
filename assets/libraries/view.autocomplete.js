(function($, undefined){

	$(sblp).on('sblp.initialization', function(){

		// register views
		$('div.sblp-view-autocomplete').each(function(){
			var $view = $(this);
			sblp.views[$view.attr('id')] = new sblp.SBLPView_Autocomplete($view);
		});

	});

})(jQuery);
