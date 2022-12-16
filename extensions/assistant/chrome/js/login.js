jQuery(document).ready(function ($) {
    var bg = chrome.extension.getBackgroundPage();
    var serverUrl = localStorage["ls_rep_url"];
    $("input:text:visible:first").focus();

    $('#register').on('click', function () {
        if (serverUrl) {
            var newURL = serverUrl + '/dash/regform.php';
            chrome.tabs.create({url: newURL});
            closeWidget();
        } else {
            window.location = 'options.html';
        }
    });

    $('#form_reg input').keypress(function (event) {
        if (event.which === 13) {
            $('#loginButton').click();
            return false;
        }
    });

    $('#loginButton').on('click', function () {

        $('#error').html('');
        $('#form_cont').hide();
        $('#loading').show();
        try {

            $.ajax({
                url: serverUrl + '/server/script.php',
                type: "POST",
                data: {type: 'loginagentext', username: $("#username").val(), password: $("#password").val()},
                success: function (data) {
                    if (data) {
                        localStorage['remember'] = $("#remember").is(':checked');
                        localStorage['lsv_agent'] = data;
                        var date = new Date();
                        date.setTime(date.getTime() + (24 * 60 * 60 * 1000));


                        var token = 'lsv_token';
                        var date = new Date();
                        date.setTime(date.getTime() + (24 * 60 * 60 * 1000));
                        document.cookie = 'token=' + token;

                        chrome.runtime.sendMessage({msg: 'logged', data: data}, function (response) {
                        });
                        closeWidget();

                    } else {
                        $('#form_cont').show();
                        $('#error').html('Invalid Credentials');
                        $('#loading').hide();
                    }
                },
                error: function (err) {
                    console.log(err);
                    $('#error').html('Please check if server URL is set in the <a href="options.html">options</a> page.');
                    jQuery('#form_cont').show();
                    jQuery('#loading').hide();
                }
            });


        } catch (ex) {
            console.error("get agent's properties failed: " + ex.toString());
        }
    });


    var closeWidget = function () {
        this.close();
    };
});