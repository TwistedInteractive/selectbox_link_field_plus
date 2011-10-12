var currentView;

jQuery(function($){
    // Create some elements, and style them:
    $("body").append('<div id="sblp-white"></div>');
    $("body").append('<div id="sblp-popup"><a href="#" class="sblp-close">×</a><iframe id="sblp-iframe" src="" width="100%" height="100%" border="0" /></div>');

    // When the iFrame is loaded, hide some Symphony stuff:
    $("#sblp-iframe").hide();
    $("#sblp-iframe").load(function(){
        var iFrame = document.getElementById('sblp-iframe');
        var url = iFrame.contentWindow.location.href;
        if(url.indexOf('/edit/') != -1)
        {
            // Entry saved successfully. Close!
            $("#sblp-popup").hide();
            // Reload the view:
            var selected = $("#" + currentView + " select").val();
            // Get the ID:
            var a = url.split('/edit/');
            var a = a[1].split('/');
            var id= a[0];
            if(selected == null) { selected = []; }
            selected.push(id);
            $("#" + currentView).load(window.location.href + ' #' + currentView, function(){
                $("#sblp-white").hide();
                $("#" + currentView + " select").val(selected);
                if(initView) { initView(); }
            });
        } else {
            $("#header, #footer", iFrame.contentWindow.document).hide();
            $("#sblp-iframe").show();
        }
    });

    if(typeof initView != 'undefined') { initView(); }

    // Bind the logic to the buttons:
    $("a.sblp-add").click(function(){
        currentView = $(this).parent().next().attr("id");
        // Open an iframe popup:
        $("#sblp-white, #sblp-popup").show();
        $("#sblp-popup iframe").attr("src", $(this).attr("href"));
        return false;
    });
    $("#sblp-popup a.sblp-close").click(function(){
        $("#sblp-white, #sblp-popup").hide();
        return false;
    });
});
