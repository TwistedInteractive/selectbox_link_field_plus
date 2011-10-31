var sblp_currentView;
var sblp_edit = false;
var sblp_initview = [];

jQuery(function($){
    // Create some elements, and style them:
    $("body").append('<div id="sblp-white"></div>');
    $("body").append('<div id="sblp-popup"><a href="#" class="sblp-close">×</a><iframe id="sblp-iframe" src="" width="100%" height="100%" border="0" /></div>');

    // When the iFrame is loaded, hide some Symphony stuff:
    $("#sblp-iframe").hide();
    $("#sblp-iframe").load(function(){
        var iFrame = document.getElementById('sblp-iframe');
        // Look at the URL to determine if the window should be closed (checks if the string '/edit/' occurs in it).
        var url = iFrame.contentWindow.location.href;
        if(url.indexOf('/edit/') != -1 && sblp_edit == false)
        {
            // Entry saved successfully. Close!
            $("#sblp-popup").hide();
            // Reload the view:
            // Get the selected items:
            var selected = $("#" + sblp_currentView + " select").val();
            // Get the ID:
            var a = url.split('/edit/');
            var a = a[1].split('/');
            var id= a[0];
            // Prevent an empty array (when no items are selected):
            if(selected == null) { selected = []; }
            selected.push(id);
            // Reload the view with native Symphony functionality:
            $("#" + sblp_currentView).load(window.location.href + ' #' + sblp_currentView, function(){
                // Restore the selected items:
                $("#" + sblp_currentView + " select").val(selected);
                // Initialize the view (executes some javascript provided by the view):
                if(typeof sblp_initview[sblp_currentView] != 'undefined') { sblp_initview[sblp_currentView](); }
                $("#sblp-white").hide();
            });
        } else {
            sblp_edit = false;
            // Hide the header and the footer of the edit window (after all, it's just a default Symphony page):
            $("#header, #footer, button.delete", iFrame.contentWindow.document).hide();
            $("#sblp-iframe").show();
        }
    });

    // Initialize the view (executes some javascript provided by the view):
    for(var i in sblp_initview)
    {
        sblp_initview[i]();
    }

    // Bind the logic to the buttons:
    $("a.sblp-add").click(function(){
        sblp_currentView = $(this).parent().next().attr("id");
        // Open an iframe popup:
        $("#sblp-white, #sblp-popup").show();
        // Use native Symphony functionality to create a new entry:
        $("#sblp-popup iframe").attr("src", $(this).attr("href"));
        return false;
    });
    // Close window-button:
    $("#sblp-popup a.sblp-close").click(function(){
        $("#sblp-white, #sblp-popup").hide();
        return false;
    });
});

/**
 * Edit an entry
 * @param viewName          The name of the view. This is 'sblp-view-[field-id]'.
 * @param sectionHandle     The handle of the section where the entry is in.
 * @param id                The ID of the entry you wish to edit.
 */
function sblp_editEntry(viewName, sectionHandle, id)
{
    sblp_currentView = viewName;
    sblp_edit = true; // Set this parameter to prevent the edit-window from closing automaticly:
    jQuery("#sblp-white, #sblp-popup").show();
    // Use native Symphony functionality to edit an entry:
    jQuery("#sblp-popup iframe").attr("src", Symphony.WEBSITE + '/symphony/publish/' + sectionHandle + '/edit/' + id);
}

/**
 * Delete an entry
 * @param viewName          The name of the view. This is 'sblp-view-[field-id]'.
 * @param sectionHandle     The handle of the section where the entry is in.
 * @param id                The ID of the entry you wish to edit.
 */
function sblp_deleteEntry(viewName, sectionHandle, id)
{
    sblp_currentView = viewName;
    jQuery("#sblp-white").show();
    var ok = confirm('Are you sure you want to delete this entry? This entry will also be removed from other entries which are related. This action cannot be undone!');
    if(ok)
    {
        // Use native Symphony functionality to delete the entry:
        var data = {
            'action[apply]': 'Apply',
            'with-selected': 'delete'
        };
        data['items[' + id + ']'] = 'yes';
        jQuery.post(Symphony.WEBSITE + '/symphony/publish/' + sectionHandle + '/', data, function(){
            // Reload the view:
            var selected = jQuery("#" + sblp_currentView + " select").val();
            jQuery("#" + sblp_currentView).load(window.location.href + ' #' + sblp_currentView, function(){
                // Restore the selected items:
                jQuery("#" + sblp_currentView + " select").val(selected);
                // Initialize the view (executes some javascript provided by the view):
                if(typeof sblp_initview[sblp_currentView] != 'undefined') { sblp_initview[sblp_currentView](); }
                jQuery("#sblp-white").hide();
            });
        });
    } else {
        jQuery("#sblp-white").hide();
    }
}