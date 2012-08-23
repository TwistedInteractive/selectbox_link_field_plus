(function($, undefined){

	var sblp = {
		views: {},
		current: '',
		edit: false,
		sort_order: false,

		init: function(){
			$.ajax({
				type: "GET",
				url: Symphony.Context.get('root')+'/symphony/extension/selectbox_link_field_plus/',
				data: { get: this.getEntryIDFromURL() },
				dataType: "JSON",
				success: function(data){
					sblp.sort_order = data;

					// this allows the views to register their instances
					$(sblp).trigger('sblp.initialization');
				}
			});

			// Create some elements, and style them:
			$("body").append('<div id="sblp-white"></div>');
			$("body").append('<div id="sblp-popup"><a href="javascript:void(0)" class="sblp-close">×</a><iframe id="sblp-iframe" src="" width="100%" height="100%" border="0" /></div>');

			sblp.$white = $("#sblp-white");
			sblp.$popup = $("#sblp-popup");
			sblp.$iframe = $("#sblp-iframe");

			// When the iFrame is loaded, hide some Symphony stuff:
			sblp.$iframe.hide().load(function(){
				var iFrame = document.getElementById('sblp-iframe');
				// Look at the URL to determine if the window should be closed (checks if the string '/edit/' occurs in it).
				var url = iFrame.contentWindow.location.href;
				if( url.indexOf('/edit/') != -1 && sblp.edit == false ){

					// Entry had errors. Keep window open
					if( $(".invalid", iFrame.contentWindow.document).length > 0){
						return false;
					}

					// Entry saved successfully. Close!
					sblp.$popup.hide();

					// Get the ID:
					var a = url.split('/edit/');
					a = a[1].split('/');
					var id = a[0];

					sblp.restoreCurrentView(id);
				}
				else{
					sblp.edit = false;

					// Hide the header and the footer of the edit window (after all, it's just a default Symphony page):
					$("#header, #footer, button.delete, #context .actions a.drawer, #drawer-drawer-left, #context ul.actions", iFrame.contentWindow.document).hide();
					$("#contents", iFrame.contentWindow.document).css('margin-left', 0);

					// Edit the form to send the parent ID
					var a = window.location.href.split('/edit/');
					if( a.length == 2 ){
						$("form", iFrame.contentWindow.document).append('<input type="hidden" name="sblp_parent" value="'+parseInt(a[1].replace('/', ''))+'" />');
					}

					sblp.$iframe.show();
				}
			});

			// Close window-button:
			sblp.$popup.on('click', "a.sblp-close", function(){
				sblp.$popup.add(sblp.$white).hide();
				return false;
			});

			// store sort order
			$('#contents > form').on('submit', function(){
				var data = {};
				for( var i in sblp.views ){
					data['id-'+sblp.views[i].$view.data('id')] = sblp.views[i].sort_order;
				}

				$('<input/>', {
					name: "sblp_sortorder",
					type: "text",
					value: JSON.stringify(data)
				}).appendTo(this);

				this.submit();
			});
		},

		/**
		 * Get sort order for specific field
		 *
		 * @param field_id
		 */
		getSorting: function(field_id){
			return this.sort_order['id-'+field_id];
		},

		/**
		 * Restore current view.
		 *
		 * @param id - extra ID to add to selected options
		 */
		restoreCurrentView: function(id){
			var current_view = this.views[ this.current ];

			// Get the selected items:
			var selected = current_view.$view.find(current_view.settings["select"]).val();
			// Prevent an empty array (when no items are selected):
			if( selected == null ){
				selected = [];
			}
			if( typeof selected == 'string' ){
				selected = [selected];
			}

			if( id !== null && id !== undefined )
				selected.push(id);

			// Reload the view with native Symphony functionality:
			$("#"+sblp.current).load(window.location.href+' #'+sblp.current, function(){
				// Restore the selected items:
				current_view.$view.find(current_view.settings["select"]).val(selected);

				// Initialize the view:
				current_view.update();

				sblp.$white.hide();
			});
		},

		/**
		 * Get entry ID from URL
		 */
		getEntryIDFromURL: function(){
			var entryID = String(window.location).split('/edit/');
			if( entryID.length == 2 ){
				entryID = entryID[1].split('/')[0];
			}else{
				entryID = 0;
			}
			return entryID;
		}
	};

	// export sblp
	window.sblp = sblp;

	$(document).ready(function(){
		sblp.init();
	});

})(jQuery);
