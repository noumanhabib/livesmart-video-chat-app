jQuery(document).ready(function ($) {
    var bg = chrome.extension.getBackgroundPage();
    var agentUrl, visitorUrl, agentBroadcastUrl, viewerBroadcastLink;
    var generateLink = function (isBroadcast) {
        sessionId = Math.random().toString(36).slice(2).substring(0, 15);
        var lsRepUrl = (localStorage["ls_rep_url"]) ? localStorage["ls_rep_url"] : '';
        lsRepUrl = (lsRepUrl.slice(-1) == '/') ? lsRepUrl : lsRepUrl + '/';
        var str = {};
        str.lsRepUrl = lsRepUrl;
        if (bg.agentId) {
            str.agentId = bg.agentId;
        }

        var encodedString = window.btoa(unescape(encodeURIComponent(JSON.stringify(str))));
        encodedString = encodedString.split('=').join('');
        visitorUrl = lsRepUrl + 'pages/r.html?room=' + sessionId + '&p=' + encodedString;
        viewerBroadcastLink = lsRepUrl + 'pages/r.html?room=' + sessionId + '&p=' + encodedString + '&broadcast=1';
        var aux = document.createElement("input");
        if (isBroadcast) {
            aux.setAttribute("value", viewerBroadcastLink);
        } else {
            aux.setAttribute("value", visitorUrl);
        }
        
        document.body.appendChild(aux);
        aux.select();
        document.execCommand("copy");
        document.body.removeChild(aux);
        
        str.isAdmin = 1;
        encodedString = window.btoa(unescape(encodeURIComponent(JSON.stringify(str))));
        encodedString = encodedString.split('=').join('');
        agentUrl = lsRepUrl + 'pages/r.html?room=' + sessionId + '&p=' + encodedString + '&isAdmin=1';
        agentBroadcastUrl = lsRepUrl + 'pages/r.html?room=' + sessionId + '&p=' + encodedString + '&isAdmin=1&broadcast=1';
    };


    var agent = (localStorage["lsv_agent"]) ? JSON.parse(localStorage["lsv_agent"]) : null;
    if (agent) {
        $('#firstName').html(agent.first_name + ' ' + agent.last_name);
        $('#callerAvatar').attr('src', (agent.avatar) ? agent.avatar : 'img/no-avatar.jpg');
    }

    $('#dashboard').on('click', function () {
        gotoDash();
    });

    $('#newmeeting').on('click', function () {
        generateLink(false);
        $('#urls').html('Visitor URL is stored in your clipboard. Paste it and sent it to your attendee using SMS, email or messenger');
        chrome.runtime.sendMessage({msg: 'openUrl', url: agentUrl}, function () {});
    });

    $('#newbroadcast').on('click', function () {
        generateLink(true);
        $('#urls').html('Visitor URL is stored in your clipboard. Paste it and sent it to your attendee using SMS, email or messenger');
        chrome.runtime.sendMessage({msg: 'openUrl', url: agentBroadcastUrl}, function () {});
    });

    $('#logout').on('click', function () {
        chrome.runtime.sendMessage({msg: 'logout'}, function () {});
        closeWidget();
    });

    var gotoDash = function () {
        chrome.runtime.sendMessage({msg: 'openDash'}, function () {});
        closeWidget();
    };

    var closeWidget = function () {
        this.close();
    };


});