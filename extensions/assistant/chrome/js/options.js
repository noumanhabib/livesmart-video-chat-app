jQuery(document).ready(function ($) {
    var bg = chrome.extension.getBackgroundPage();
    var presence = (localStorage["notify_presence"] === "false") ? false : true;
    var calls = (localStorage["notify_calls"]  === "false") ? false : true;
    var lsRepUrl = (localStorage["ls_rep_url"]) ? localStorage["ls_rep_url"] : '';
    $('#notify_presence').attr('checked', presence);
    $('#notify_calls').attr('checked', calls);
    $('#ls_rep_url').val(lsRepUrl);
    
    $('#save_button').on('click', function () {
        var presence = $("#notify_presence").is(':checked');
        var calls = $("#notify_calls").is(':checked');
        var ls_rep_url = $("#ls_rep_url").val();
        if (!ls_rep_url) {
            $('#error').html('LiveSmart URL is mandatory');
            return;
        }
        localStorage["notify_presence"] = presence;
        localStorage["notify_calls"] = calls;
        localStorage["ls_rep_url"] = ls_rep_url;
        ls_rep_url = (lsRepUrl.slice(-1) == '/') ? ls_rep_url : ls_rep_url + '/';
        bg.lsRepUrl = ls_rep_url;
        bg.dashUrl = ls_rep_url + 'dash/integration.php';
        var txt = (localStorage['lsv_agent']) ? '' : 'Go to <a href="login.html">login</a> page.'
        $('#error').html('Settings are updated. ' + txt);
        if (!localStorage['lsv_agent'] && ls_rep_url) {
            window.location = 'login.html';
        }
        
        chrome.runtime.sendMessage({msg: 'settings'}, function () {

        });
    });
    
    
});