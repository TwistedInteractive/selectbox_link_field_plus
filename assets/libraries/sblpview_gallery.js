(function($, undefined){

	sblp.SBLPView_Gallery = sblp.SBLPView.extend({

		init: function($view){
			this._super($view, {
				source_list: 'div.image'
			});

			if( $view.data('alert') )
				alert("No links could be found. Are you sure you have selected a field of the type 'upload' for the relation in the Selectbox Link Plus Field?");

			// listen to clicks
			$view.on('click', "div.sblp-gallery div.image a.thumb", function(e){
				var $parent = $(this).parent();
				var id = $parent.attr("rel");

				if( $view.data('multiple') ){
					$parent.toggleClass("selected");
					if( $parent.hasClass("selected") ){
						$view.find("select.target option[value=" + id + "]").attr("selected", "selected");
					}
					else {
						$view.find("select.target option[value=" + id + "]").removeAttr("selected");
					}
				}
				else {
					$view.find("div.sblp-gallery div.image").removeClass("selected");
					$view.find("select.target option").removeAttr("selected");
					$view.find("select.target option[value=" + id + "]").attr("selected", "selected");
					$parent.addClass("selected");
				}

				return false;
			});

			// initialize
			this.update();
		},

		update: function(){
			var view = this;

			// add visual effects
			view.$view.find("select.target option:selected").each(function(){
				view.$view.find("div.image[rel=" + $(this).val() + "]").addClass("selected");
			});

			// initialize
			if( view.$view.data('multiple') ){
				// Load the sorting order-state:
				this.loadSorting();

				view.$view.find("div.sblp-gallery div.container").sortable({items: "div.image", update: function(){
					// Update the option list according to the div items:
					view.sortItems();
				}});

				view.$view.disableSelection();
			}

			// Hide others:
			view.$view.find("input[name=show_created]").on('change', function(){
				// Only show the selected items:
				if( $(this).attr("checked") ){
					view.$view.find("div.image").not(".selected").hide();
				}

				// Show everything:
				else{
					view.$view.find("div.image").show();
				}
			}).trigger('change');
		}

	})

})(jQuery);
