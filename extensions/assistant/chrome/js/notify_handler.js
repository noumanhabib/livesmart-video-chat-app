var EXPIRATION_COUNT = 5;
var bg = chrome.extension.getBackgroundPage();
var SM = (function () {

    var my = {};

    my.get = function (key) {
        return localStorage.getItem(key);
    };

    my.put = function (key, value) {
        return localStorage.setItem(key, value);
    };

    my.delete = function (key) {
        return localStorage.removeItem(key);
    };

    return my;

}());


var notifyClass = (function (SM) {
    var my = {};
    my.notifyItems = {};

    if (!SM.get("presence")) {
        SM.put("presence", JSON.stringify(my.notifyItems));
    }
    if (!SM.get("incomingcall")) {
        SM.put("incomingcall", JSON.stringify(my.notifyItems));
    }
    if (!SM.get("incomingchat")) {
        SM.put("incomingchat", JSON.stringify(my.notifyItems));
    }
    if (!SM.get("returningcustomer")) {
        SM.put("returningcustomer", JSON.stringify(my.notifyItems));
    }



    my.add = function (id, type, value) {
        my.notifyItem = JSON.parse(SM.get(type));
        var keys = Object.keys(my.notifyItem);
        if (keys.length >= EXPIRATION_COUNT) {
            var last = keys[0];
            my.remove(last, type);
        }
        my.notifyItem[id] = value;
        SM.put(type, JSON.stringify(my.notifyItem));
        if (type === 'presence' && keys.length > 0) {
            my.delete(keys[keys.length - 1]);
        }
    };

    my.deleteAll = function () {
        chrome.notifications.getAll(function (nots) {
            Object.keys(nots).forEach(function (key) {
                chrome.notifications.clear(key, function () {});
            });

        });
    };

    my.delete = function (id) {
        chrome.notifications.clear(id, function () {
        });
    };

    my.remove = function (id, type) {
        my.notifyItem = JSON.parse(SM.get(type));
        delete my.notifyItem[id];
        SM.put(type, JSON.stringify(my.notifyItem));
    };

    my.updateCallNotification = function (id) {
        my.notifyItem = JSON.parse(SM.get('incomingcall'));
        my.notifyItem[id] = 'Missed Call';
        SM.put('incomingcall', JSON.stringify(my.notifyItem));
        chrome.notifications.update(id, {
            type: "basic",
            title: "Missed call",
            buttons: [{
                    title: "View on Dashboard",
                    iconUrl: "/img/plugin_go.png"
                }]
        }, function (id) {
            chrome.notifications.onButtonClicked.addListener(function (notifId, btnIdx) {
                if (btnIdx === 0 && notifId === id) {
                    var event = new CustomEvent('notificationMessage', {detail: {msg: 'openDash'}});
                    document.dispatchEvent(event);
                }
            });
        });

    };

    my.guestName = function (token) {
        if (token) {
            token.charCodeAt(0) + token.charCodeAt(token.length - 1);
            var s = 0;
            for (var i = 0; i < token.length; i++) {
                s += token.charCodeAt(i);
            }
            var numb = s % 100;
            return 'Guest-' + parseInt(numb + 1);
        }
    };

    my.createPresenceNotification = function (val, total_count, notify_presence) {
        var buttons = [{
                title: "View on Dashboard",
                iconUrl: "/img/plugin_go.png"
            }, {
                title: "Mute Page Visit notifications",
                iconUrl: "/img/delete.png"
            }];
        var userName = (val.user) ? val.user : my.guestName(val.visitorId);
        msg = userName + " is viewing: " + val.title;
        my.createNotification("New Page Visit", msg, notify_presence, 'presence', buttons,
                function (accepted, id) {
                    my.delete(id);
                    if (accepted === 1) {
                        var event = new CustomEvent('notificationMessage', {detail: {msg: 'openDash'}});
                        document.dispatchEvent(event);
                    } else if (accepted === 2) {

                        var event = new CustomEvent('notificationMessage', {detail: {msg: 'mute'}});
                        document.dispatchEvent(event);
                    }
                }
        );
    };

    my.createCallNotification = function (detail, notify_calls) {
        var name = (detail.name) ? detail.name : my.guestName(detail.sessionId);
        var from_url = detail.url;
        var peer_name = 'Name: ' + name + '\n';
        var message = peer_name + detail.title;
        var buttons = [{
                title: "Answer",
                iconUrl: "/img/accept.png"
            }, {
                title: "Decline",
                iconUrl: "/img/cancel.png"
            }];

        var badgeEvent = new CustomEvent('addBadge');
        document.dispatchEvent(badgeEvent);
        my.createNotification(name + ' is calling you', message, notify_calls, 'incomingcall', buttons,
                function (accepted, id) {
//                    my.delete(id);
                    my.deleteAll();
                    if (accepted === 1) {
                        var event = new CustomEvent('notificationMessage', {detail: {msg: 'openUrl', url: from_url, sessionId: detail.sessionId}});
                        document.dispatchEvent(event);
                    } else if (accepted === 2) {
                        event = new CustomEvent('removeBadge');
                        document.dispatchEvent(event);
                    }
                }, true
                );
    };


    my.notLoggedNotification = function () {
        var buttons = [{
                title: "Login",
                iconUrl: "/img/link_go.png"
            }];
        my.createNotification("You are not logged in", 'Please sign-in with your LiveSmart Video Chat credentials to use LiveSmart assistant.', true, 'presence', buttons,
                function (accepted) {
                    if (accepted === 1) {
                        var event = new CustomEvent('notificationMessage', {detail: {msg: 'loadButton'}});
                        document.dispatchEvent(event);
                    }
                }
        );
    };

    my.notUrlNotification = function () {
        var buttons = [{
                title: "Settings",
                iconUrl: "/img/link_go.png"
            }];
        my.createNotification("You have not specified LiveSmart URL", 'Please open options page and specify your LiveSmart URL there.', true, 'presence', buttons,
                function (accepted) {
                    if (accepted === 1) {
                        var event = new CustomEvent('notificationMessage', {detail: {msg: 'loadOptions'}});
                        document.dispatchEvent(event);
                    }
                }
        );
    };

    my.createNotification = function (title, message, permission, type, buttons, callback, requireIteraction) {
        var logo = 'img/logo.png';
        if (permission) {
            var jsonNotif = {
                type: "basic",
                title: title,
                message: message,
                iconUrl: logo,
                requireInteraction: (requireIteraction) ? requireIteraction : false
            };
            if (buttons !== undefined) {
                jsonNotif.buttons = buttons;
            }
            chrome.notifications.create("", jsonNotif, function (id) {
                if (callback !== undefined) {
                    for (var upd in bg.tokenSessionMap) {
                        if (bg.tokenSessionMap[upd].token === bg.sessionId) {
                            bg.tokenSessionMap[upd].notifId = id;
                        }
                    }
                    chrome.notifications.onButtonClicked.addListener(function (notifId, btnIdx) {
                        if (btnIdx === 0 && notifId === id) {
                            callback(1, id);
                        } else if (btnIdx === 1 && notifId === id) {
                            callback(2, id);
                        }
                    });
                }
                my.add(id, type, {value: message});
            });
        }
    };

    return my;
}(SM));