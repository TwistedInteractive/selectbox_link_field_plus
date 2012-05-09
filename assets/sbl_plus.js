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
			if(typeof selected == 'string') { selected = [selected]; }
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
		    // Edit the form to send the parent ID
		    var a = window.location.href.split('/edit/');
		    if(a.length == 2)
		    {
			    var action = $("form", iFrame.contentWindow.document).attr("action");
			    $("form", iFrame.contentWindow.document).append('<input type="hidden" name="sblp_parent" value="' + parseInt(a[1].replace('/', '')) + '" />');
		    }
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
    jQuery("#sblp-popup iframe").attr("src", Symphony.Context.get('root') + '/symphony/publish/' + sectionHandle + '/edit/' + id);
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
        jQuery.post(Symphony.Context.get('root') + '/symphony/publish/' + sectionHandle + '/', data, function(){
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

/**
 * Sort the hidden selectbox according to the sorted elements
 * @param viewName          The name of the view.
 * @param sourceList        A jQuery-object, containing all elements that are sortables.
 * @param attributeName     The name of the attribute in the sortables that is used to store the elements' id.
 */
function sblp_sortItems(viewName, sourceList, attributeName, save)
{
    var $ = jQuery;
    save = save == null ? true : save;
    // Options are in a optgroup
    // Remove the options first from the optgroup
    var options = [];
    $("#" + viewName + " select option").each(function(){
        options[$(this).val()] = $(this).remove();
    });
    // Re-arange them according to the sourceList:
    var ids = [];
    $("#" + viewName + " select").html('');
    sourceList.each(function(){
        var id = $(this).attr(attributeName);
        ids.push(id);
        $("#" + viewName + " select").append(options[id]);
    });
    // Post an AJAX-call to store the state for editing purposes only:
    // console.log(save);
    if(save)
    {
        $.post(Symphony.Context.get('root') + '/symphony/extension/selectbox_link_field_plus/', {
            id: sblp_getEntryIDFromURL(),
            order: ids.join(',')
        });
    }
}

/**
 * Load the sorting order
 * @param viewName              The name of the view
 * @param sourceListSelector    The selector for the sortable elements
 * @param attributeName         The name of the attribute in the sortables that is used to store the elements' id.
 */
function sblp_loadSorting(viewName, sourceListSelector, attributeName)
{
    var $ = jQuery;
    // First of all, get the ids:
    var entryID = sblp_getEntryIDFromURL();
    $.get(Symphony.Context.get('root') + '/symphony/extension/selectbox_link_field_plus/', {
        get: entryID
    }, function(data){
        var ids = String(data).split(',');
        // Now we have an array with all the IDs in the correct order. Now sort each container individually:
        var elements = [];
        $(sourceListSelector).each(function(){
            var id = $(this).attr(attributeName);
            elements[id] = [$(this).parent(), $(this).detach()];
        });
        // Now re-attach the items, according to the ids-array:
        for(var i=0, l=ids.length; i<l; i++)
        {
            if(elements[ids[i]] != undefined)
            {
                elements[ids[i]][0].append(elements[ids[i]][1]);
                elements[ids[i]] = undefined;
            }
        }
        // Last but not least, re-attach the items that have not been attached, to prevent later created items
        // from not being shown:
        for(i=0, l=elements.length; i<l; i++)
        {
            if(elements[i] != undefined)
            {
                elements[i][0].append(elements[i][1]);
            }
        }
        // Also re-arange the options list:
        sblp_sortItems(viewName, $(sourceListSelector), attributeName, entryID != 0);
    });
}

/**
 * Get the Entry ID according to the URL.
 * If no ID is found (which is the case with new entries) '0' is returned.
 */
function sblp_getEntryIDFromURL()
{
    var entryID = String(window.location).split('/edit/');
    if(entryID.length == 2)
    {
        entryID = entryID[1].split('/')[0];
    } else {
        entryID = 0;
    }
    return entryID;
}