/* the chrome content script which can listen to the page dom events */
var channel = chrome.runtime.connect();
channel.onMessage.addListener(function (message) {
    console.log('onMessage', message);
    window.postMessage(message, '*');
});

chrome.runtime.onMessage.addListener(
        function (req, sender, sendResponse) {
            if (req.msg === 'sessionId') {
                var message = {type: 'sessionId', value: req.value, callType: req.type};
                window.postMessage(message, '*');
            }
            if (req.msg === 'giveMeInfo') {
                var message = {type: 'giveMeInfo', value: ''};
                window.postMessage(message, '*');
            }
        }
);


window.addEventListener('message', function (event) {
    console.log('message from web', event.data);
    if (event.source !== window)
        return;
    if (!event.data)
        return;
    channel.postMessage(event.data);

});

