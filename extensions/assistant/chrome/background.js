var tennant_id;
var session_id;
var callType;
var notify_presence;
var notify_calls;
var currVersion = 1;
var sessionId;
var total_count = 0;
var token = null;
var capabilities = null, acl = null;
var lsRepUrl = '';
var svConfigs = '';
var windowId = 0;
var tabWindowId = 0;
var tabId = 0;
var creatInWindow = false;
var notification_id = 0;
var brokerage;
var caller_email;
var no_avatar = 'img/no-avatar.jpg';
var dashUrl = lsRepUrl + "dash/dash.php";
var tokenSessionMap = [];
var POPUP_HEIGHT = 280;
var POPUP_HEIGHT_LOGIN = 280;
var POPUP_WIDTH = 520;
var pendingNumber = 0;
var agentId, comm;

(function () {
    jQEngager = jQuery;
    notify_presence = (localStorage["notify_presence"] === 'false') ? false : true;
    caller_email = (localStorage["caller_email"]) ? localStorage["caller_email"] : 'caller_email';
    notify_calls = (localStorage["notify_calls"] === 'false') ? false : true;
    lsRepUrl = (localStorage["ls_rep_url"]) ? localStorage["ls_rep_url"] : lsRepUrl;
    lsRepUrl = (lsRepUrl.slice(-1) == '/') ? lsRepUrl : lsRepUrl + '/';
    dashUrl = lsRepUrl + 'dash/integration.php';

    if (localStorage["tennantId"]) {
        tennant_id = localStorage["tennantId"];
    }
    chrome.browserAction.onClicked.addListener(function () {
        loadButton();
    });

    chrome.runtime.onMessage.addListener(
            function (req, sender, sendResponse) {
                console.log('logged')
                if (req.msg === 'logged' && isLogged()) {
                    var data = JSON.parse(req.data);
                    tennant_id = localStorage["tennantId"] = data.tennant;
                    caller_email = localStorage["caller_email"] = data.email;

                    setIcon(true);
                    if (typeof (smartVideoLocale) == "undefined") {
                        loadConfig();
                    } else {
                        loadConnect();
                    }
                } else if (req.msg === 'logout') {
                    deleteToken();
                    setBadge(false);
                    comm.setDeleteAll();
                    location.reload();
                } else if (req.msg === 'settings') {
                    notify_presence = (localStorage["notify_presence"] === 'false') ? false : true;
                    notify_calls = (localStorage["notify_calls"] === 'false') ? false : true;
                } else if (req.msg === 'openDash') {
                    openDash();
                } else if (req.msg === 'openUrl') {
                    openUrl(req.url, undefined);
                } else if (req.msg === 'removeBadge') {
                    setBadge(false);
                }
                return true;
            }
    );


    document.addEventListener('Connected', function (e) {
        console.log('Connected Event');
        if (isLogged()) {
            setIcon(true);
        }
    });

    var loadScript = function (scipt, callback) {
        var push_script = document.createElement('script');
        push_script.setAttribute("type", "text/javascript");
        push_script.setAttribute("src", scipt);
        if (push_script.readyState) {
            push_script.onreadystatechange = function () { // For old versions of IE
                if (this.readyState === 'complete' || this.readyState === 'loaded') {
                    callback;
                }
            };
        } else {
            push_script.onload = callback;
        }
        (document.getElementsByTagName("head")[0] || document.documentElement).appendChild(push_script);
    };


    var startComm = function () {
        checkLogin(loadConnect);
    };

    var loadBundle = function () {
        loadScript(lsRepUrl + 'js/bundle.js', startComm);
    };

    var loadSocket = function () {
        if (svConfigs.iceServers.requirePass) {
            jQuery.ajax({
                type: 'POST',
                url: lsRepUrl + '/server/script.php',
                data: {'type': 'getpassphrase'}
            })
                    .done(function (data) {
                        if (data) {
                            svConfigs.iceServers.passPhrase = data;
                            loadScript(svConfigs.appWss + 'socket.io/socket.io.js', loadBundle);
                        }
                    })
                    .fail(function () {
//                                        console.log(false);
                    });
        } else {
            loadScript(svConfigs.appWss + '/socket.io/socket.io.js', loadBundle);
        }
        ;
    };

    var loadConfig = function () {
        chrome.manifest = chrome.runtime.getManifest();
        currVersion = chrome.manifest.version;
        jQuery.ajax({
            url: lsRepUrl + 'config/config.json?v=' + currVersion,
            type: 'GET',
            timeout: 5000,
            dataType: 'json',
            beforeSend: function (x) {
                if (x && x.overrideMimeType) {
                    x.overrideMimeType('application/j-son;charset=UTF-8');
                }
            },
            success: function (data) {
                svConfigs = data;
                loadSocket();
            }
        });
    };

    loadScript('js/notify_handler.js?v1', function () {
        loadWidget();
        if (!isLogged()) {
            if (lsRepUrl == '/') {
                notifyClass.notUrlNotification();
            } else {
                notifyClass.notLoggedNotification();
            }
        }
    });
    if (lsRepUrl != '/') {
        loadConfig();
    }

    var loadConnect = function () {
        if (lsRepUrl != '/' && isLogged()) {
            var options = {
                lsRepUrl: lsRepUrl,
                lang: svConfigs.smartVideoLanguage
            };
            smartVideoLocale.init(options, jQuery);
            comm = new comController();
            if (agentId) {
                comm.init('admin', 'dashboard' + agentId);
            } else {
            comm.init('admin', 'dashboard');
            }
            notify_handler = new notifyHandler();
            notify_handler.init();
        } else {
            if (lsRepUrl == '/') {
                notifyClass.notUrlNotification();
            } else {
                notifyClass.notLoggedNotification();
            }
        }
        return true;
    };

    var loadButton = function () {
        chrome.windows.get(windowId, function (chromeWindow) {
            if (!chrome.runtime.lastError && chromeWindow) {
                chrome.windows.update(windowId, {focused: true});
                return;
            }
            if (isLogged()) {
                chrome.windows.create({url: 'dash.html', type: 'popup', width: POPUP_WIDTH, height: POPUP_HEIGHT, focused: true}, function (windowTab) {
                    windowId = windowTab.id;
                });
            } else {
                chrome.windows.create({url: 'login.html', type: 'popup', width: POPUP_WIDTH, height: POPUP_HEIGHT_LOGIN, focused: true}, function (windowTab) {
                    windowId = windowTab.id;
                });
            }
        });
    };

    var loadOptions = function () {
        chrome.windows.get(windowId, function (chromeWindow) {
            if (!chrome.runtime.lastError && chromeWindow) {
                chrome.windows.update(windowId, {focused: true});
                return;
            }
            chrome.windows.create({url: 'options.html', type: 'popup', width: POPUP_WIDTH, height: POPUP_HEIGHT, focused: true}, function (windowTab) {
                windowId = windowTab.id;
            });
        });
        return true;
    };

    var tabCreateOrShow = function (sessionId, type, url) {
        chrome.tabs.get(tabId, function () {
            // Tab OK, bring windows to focus, bring tab to focus
            if (!chrome.runtime.lastError && tabId) {
                var updateUrl = {
                    selected: true,
                    active: true
                };
                if (tabWindowId) {
                    chrome.windows.update(tabWindowId, {focused: true, state: "maximized"}, function (win) {
                        console.debug(chrome.runtime.lastError, win);
                    });
                }
                chrome.tabs.update(tabId, updateUrl);
                if (sessionId !== undefined) {
                    //updateUrl.url = dashUrl + '?autoAccept=true&sessionId='+sessionId;
                    if (tabId) {
                        chrome.tabs.sendMessage(tabId, {msg: 'sessionId', value: sessionId, type: type}, function (response) {
                        });
                    }
                }
                return;
            } else {
                // No tabs, cteate em.
                chrome.tabs.create({
                    url: url,
                    active: true
                }, function (tab) {
                    tabId = tab.id;
                    tabWindowId = tab.windowId;
                    console.debug("Tab ID %d, windowId %d", tabId, tabWindowId);
                    chrome.windows.update(tabWindowId, {focused: true, state: "maximized"}, function (win) {
                        console.debug(chrome.runtime.lastError, win);
                    });


                    if (sessionId !== undefined) {
                        session_id = sessionId;
                        callType = type;
                    }
                });
            }
        });
    };

    chrome.tabs.onAttached.addListener(function (tabIdAttached, props) {
        console.debug(
                'tabs.onAttached -- window: ' + props.newWindowId + ' tab: ' + tabIdAttached +
                ' index ' + props.newPosition);
        if (tabId === tabIdAttached) {
            tabWindowId = props.newWindowId;
        }
    });
    var openDash = function (sessionId, type) {
        setBadge(false);
        var jsn = JSON.parse(localStorage['lsv_agent']);
        var url = '?wplogin=' + jsn.username + '&url=' + window.btoa(lsRepUrl);
        // Check if Chrome has window at all, create it, or place your tab in it.
        chrome.windows.getAll({populate: false}, function (wins) {
            if (wins.length === 0) {
                chrome.windows.create({url: dashUrl + url, state: "normal", focused: true}, function (win) {
                    tabWindowId = win.id;
                    if (sessionId !== undefined) {
                        session_id = sessionId;
                    }
                    tabId = win.tabs[0].id;
                });
            } else {
                tabCreateOrShow(sessionId, type, dashUrl + url);
            }

        });
    };
    var openUrl = function (url, type, sessionId) {
        setBadge(false);
        // Check if Chrome has window at all, create it, or place your tab in it.
        chrome.windows.getAll({populate: false}, function (wins) {
            if (wins.length === 0) {
                chrome.windows.create({url: url, state: "normal", focused: true}, function (win) {
                    tabWindowId = win.id;
                    if (sessionId !== undefined) {
                        session_id = sessionId;
                    }
                    tabId = win.tabs[0].id;
                });
            } else {
                tabCreateOrShow(sessionId, type, url);
            }

        });
        return true;
    };

    var delArray = function (array) {
        for (var index in array) {
            total_count--;
        }
        return false;
    };

    function loadWidget() {
        document.addEventListener('removeBadge', function (e) {
            setBadge(false);
        });
        document.addEventListener('addBadge', function (e) {
            setBadge(true);
        });
        document.addEventListener('notificationMessage', function (e) {
            switch (e.detail.msg) {
                case 'openDash':
                    var sessionId = (e.detail.sessionId) ? e.detail.sessionId : undefined;
                    var type = (e.detail.type) ? e.detail.type : undefined;
                    openDash(sessionId, type);
                    break;
                case 'openUrl':
                    openUrl(e.detail.url, undefined);
                    break;
                case 'loadButton':
                    loadButton();
                    break;
                case 'loadOptions':
                    loadOptions();
                    break;
            }
            setBadge(false);
        });
        
        document.addEventListener('PresenceError', function (e) {
            console.log('PresenceError Event');
        });

        document.addEventListener('ConnectFailed', function (e) {
            deleteToken();
            checkLogin(loadConnect);
        });

        document.addEventListener('ExtVisitorVideoSession', function (e) {
            console.log('ExtVisitorVideoSession');
            notifyClass.createCallNotification(e.detail, notify_calls);
        });

        document.addEventListener('ExtVisitorsCount', function (e) {
            if (notify_presence) {
                var count = e.detail.count
                setBadge(true, count.toString());
            }
//            notifyClass.createPresenceNotification(e.detail, notify_presence);
        });
    };

    var setBadge = function (enable, count) {
        if (enable) {
            chrome.browserAction.setBadgeBackgroundColor({color: "blue"});
            chrome.browserAction.setBadgeText({text: count});
        } else {
            chrome.browserAction.setBadgeText({text: ""});
        }
        return true;
    };

    var setIcon = function (logged) {
        var icon = '';
        if (logged) {
            icon = 'icon_logged.png';
        } else {
            icon = 'icon_not_logged.png';
        }
        chrome.browserAction.setIcon({
            path: icon
        });
        return true;
    };

    var getCookie = function () {
        name = 'token';
        var pattern = RegExp(name + "=.[^;]*");
        var matched = document.cookie.match(pattern);
        if (matched) {
            var cookie = matched[0].split('=');
            var cooki = decodeURIComponent(cookie[1]).replace(/"/g, "");
            return cooki;
        }
        return null;
    };

    var checkLogin = function (callback) {
        token = getCookie();
        if (token && localStorage['lsv_agent']) {
            var jsn = JSON.parse(localStorage['lsv_agent']);
            if (!jsn.is_master) {
                agentId = jsn.tenant;
            }
            setIcon(true);
            callback();
        } else {
            deleteToken();
            loadConnect();

        }
        return true;

    };

    var deleteToken = function () {
        token = null;
        setCookie('token', null);
        setIcon(false);
        localStorage.removeItem('lsv_agent');
    };

    var isLogged = function () {
        token = getCookie();
        if (token === null || typeof (token) === "undefined" && localStorage['lsv_agent']) {
            return false;
        } else {
            return true;
        }
    };


    var setCookie = function (name, value, hour) {
        var cookieValue = value;
        var d = new Date();
        var time = d.getTime();
        var expireTime = time + 1000 * 60 * 60 * parseInt(hour);
        d.setTime(expireTime);
        if (hour) {
            document.cookie = name + "=" + cookieValue + ";expires=" + d.toGMTString() + ";path=/";
        } else {
            document.cookie = name + "=" + cookieValue + ";path=/; expires=Thu, 01 Jan 1970 00:00:01 GMT;";
        }
    };


    //content script communication.
    chrome.runtime.onConnect.addListener(function (channel) {
        channel.onMessage.addListener(function (message) {
            console.log('message', message);

            switch (message.type) {

                case 'requestToken':
                    message.value = token;
                    message.type = 'token';
                    channel.postMessage(message);

                    break;
            }
        });
        chrome.runtime.onMessageExternal.addListener(
                function (req, sender, callback) {
                    if (req) {
                        if (req.message) {
                            if (req.message == "installed") {
                                callback(true);
                            }
                        }
                    }
                    return true;
                });
    });
})();

