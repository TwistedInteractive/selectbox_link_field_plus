(function($, undefined){

	sblp.SBLPView_ImageGrid = sblp.SBLPView.extend({

		init: function($view){
			this._super($view, {
				source_list: 'div.image'
			});
			/*
			* TODO: more efficient way to keep track of available and selected items
			*/
			// init selected items count
			$view.selected_count = 0;
			$view.available_count = 0;

			if( $view.data('alert') )
				alert("No links could be found. Are you sure you have selected a field of the type 'upload' for the relation in the Selectbox Link Plus Field?");

			// listen to clicks
			$view.on('click', "div.sblp-imagegrid div.image a.thumb", function(e){
				var $parent = $(this).parent();
				var id = $parent.attr("rel");
				

				// only run click event if 'panel' is .open
				if( $view.hasClass('open') ) {
					if( $view.data('multiple') ){
						$parent.toggleClass("selected");
						if( $parent.hasClass("selected") ){
							$view.find("select.target option[value=" + id + "]").attr("selected", "selected");
							$view.selected_count++;
						}
						else {
							$view.find("select.target option[value=" + id + "]").removeAttr("selected");
							$view.selected_count--;
						}
					}
					else {
						// allow deselection if single
						if( $parent.hasClass("selected") ){
							$parent.removeClass("selected");
							$view.find("select.target option[value=" + id + "]").removeAttr("selected");
							$view.selected_count = 0;
						} else {
							$view.find("div.sblp-imagegrid div.image").removeClass("selected");
							$view.find("select.target option").removeAttr("selected");
							$view.find("select.target option[value=" + id + "]").attr("selected", "selected");
							$parent.addClass("selected");
							$view.selected_count = 1;
						}
					}

					// remove .select## with wildcard, add updated .select##
					$view.removeClass( function(index, css) {
						return (css.match(/\bselect\S+/g) || []).join(' ');
					}).addClass("select" + $view.selected_count);

				}
				return false;
			});
			// initialize
			this.update();
		},

		update: function(){
			var view = this;

			// count how many items are selected
			this.$view.selected_count = view.$view.find("select.target option:selected").length;
			this.$view.available_count = view.$view.find("select.target option").length;
			if (this.$view.selected_count == 0) {
				// view.$view.addClass("noneselected");
				view.$view.find("a.sblp-edit").html($('input[name="editbtn_text_blank"]').val());
			}

			// fancy class removal with wildcard 
			view.$view.removeClass( function(index, css) {
				return (css.match(/\bselect\S+/g) || []).join(' ');
			}).addClass('select' + view.$view.selected_count);

			// add visual effects
			view.$view.find("select.target option:selected").each(function(){
				view.$view.find("div.image[rel=" + $(this).val() + "]").addClass("selected");
			});

			// initialize
			if( view.$view.data('multiple') ){
				// Load the sorting order-state:
				this.loadSorting();

				view.$view.find("div.sblp-imagegrid div.container").sortable({items: "div.image", update: function(){
					// Update the option list according to the div items:
					view.sortItems();
				}});

				view.$view.disableSelection();
			}

			// Hide non-selected:
			view.$view.find("a.sblp-edit").on('click', function(){
				// Only show selected (aka close)
				if(view.$view.hasClass("open")) {
					view.$view.removeClass("open").addClass("closed");

					if(view.$view.hasClass("select0")) {
						$(this).html($('input[name="editbtn_text_blank"]').val());
					} else {
						$(this).html($('input[name="editbtn_text"]').val());
					}
				}
				// Show everything (aka open)
				else {
					view.$view.removeClass("closed").addClass("open");
					$(this).html($('input[name="editbtn_text_close"]').val());
				}
			})

		}


	})

})(jQuery);