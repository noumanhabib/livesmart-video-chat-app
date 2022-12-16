<div id="chats-lsv-admin"></div>
<script>
    var copyUrl = function (url) {
        var aux = document.createElement("input");
        aux.setAttribute("value", url);
        document.body.appendChild(aux);
        aux.select();
        document.execCommand("copy");
        document.body.removeChild(aux);
    };

    var deleteItem = function (itemid, type, event) {
        event.preventDefault()
        if (type === 'room') {
            $.ajax({
                type: 'POST',
                url: '../server/script.php',
                data: {'type': 'deleteroom', 'agentId': agentId, 'roomId': itemid}
            })
                    .done(function (data) {
                        location.reload();
                    })
                    .fail(function () {
                        console.log(false);
                    });
        } else if (type === 'agent') {
            $.ajax({
                type: 'POST',
                url: '../server/script.php',
                data: {'type': 'deleteagent', 'agentId': itemid}
            })
                    .done(function (data) {
                        location.reload();
                    })
                    .fail(function () {
                        console.log(false);
                    });
        } else if (type === 'user') {
            $.ajax({
                type: 'POST',
                url: '../server/script.php',
                data: {'type': 'deleteuser', 'userId': itemid}
            })
                    .done(function (data) {
                        location.reload();
                    })
                    .fail(function () {
                        console.log(false);
                    });
        } else if (type === 'recording') {
            $.ajax({
                type: 'POST',
                url: '../server/script.php',
                data: {'type': 'deleterecording', 'recordingId': itemid}
            })
                    .done(function (data) {
                        location.reload();
                    })
                    .fail(function () {
                        console.log(false);
                    });
        }
    };

    var getCurrentDateFormatted = function (date) {
        var currentdate = new Date(date);
        if (currentdate.getDate()) {
            return currentdate.format('isoDate')

        } else {
            return '';
        }
    };
    var isAdmin = true;
    var roomId = false;
<?php if (@$_SESSION["tenant"] == 'lsv_mastertenant') { ?>
        var agentId = false;
<?php } else { ?>
        var agentId = "<?php echo @$_SESSION["tenant"]; ?>";
<?php } ?>
</script>



</div>
<!-- /.container-fluid -->

</div>
<!-- End of Main Content -->
<?php
if ($isInclude) {
    ?>
    <!-- Footer -->
    <footer class="sticky-footer bg-white">
        <div class="container my-auto">
            <div class="copyright text-center my-auto">
                <span>Copyright &copy; LiveSmart Video Chat 2019-<?php echo date('Y'); ?></span>
            </div>
        </div>
    </footer>
    <!-- End of Footer -->
    <?php
}
?>
</div>
<!-- End of Content Wrapper -->

</div>
<!-- End of Page Wrapper -->

<!-- Scroll to Top Button-->
<a class="scroll-to-top rounded" href="#page-top">
    <i class="fas fa-angle-up"></i>
</a>

<!-- Logout Modal-->
<div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel" data-localize="ready_leave"></h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body" data-localize="select_logout"></div>
            <div class="modal-footer">
                <button class="btn btn-secondary" type="button" data-dismiss="modal" data-localize="cancel"></button>
                <a class="btn btn-primary" href="logout.php" data-localize="logout"></a>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="generateBroadcastLinkModal" tabindex="-1" role="dialog" aria-labelledby="generateBroadcastLinkModal" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel" data-localize="broadcasting_attendee_url"></h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body" data-localize="broadcasting_attendee_info"></div>
            <div class="modal-footer">
                <button class="btn btn-primary mr-auto" type="button" id="copyBroadcastUrl" data-localize="copy_url"></button>
                <button class="btn btn-secondary" type="button" data-dismiss="modal" data-localize="close"></button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="generateLinkModal" tabindex="-1" role="dialog" aria-labelledby="generateLinkModal" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel" data-localize="video_attendee_url"></h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body" data-localize="video_attendee_info"></div>
            <div class="modal-footer">
                <button class="btn btn-primary mr-auto" type="button" id="copyAttendeeUrl" data-localize="copy_url"></button>
                <button class="btn btn-secondary" type="button" data-dismiss="modal" data-localize="close"></button>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap core JavaScript-->
<script src="vendor/jquery/jquery.min.js"></script>
<script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

<!-- Core plugin JavaScript-->
<script src="vendor/jquery-easing/jquery.easing.min.js"></script>

<!-- Custom scripts for all pages-->
<script src="js/sb-admin-2.min.js"></script>
<script src="js/detect.js"></script>
<script>
    $('#generateLink').on('click', function () {
        generateLink(false);
        window.open(agentUrl);
        var text = $('#generateLinkModal').html();
        $('#generateLinkModal').html(text.replace('[generateLink]', visitorUrl));
        $('#generateLinkModal').modal('toggle');
        $('#copyAttendeeUrl').off();
        $('#copyAttendeeUrl').on('click', function () {
            $('#generateLinkModal').modal('hide');
            copyUrl(visitorUrl);
        });
    });
</script>
<?php if ($basename == 'agent.php') { ?>


    <script>

    <?php
    if (isset($_GET['id'])) {
        ?>
            $('#usernameDiv').hide();
        <?php
    } else {
        ?>
            $('#usernameDiv').show();
        <?php
    }
    ?>
        jQuery(document).ready(function ($) {
            $('#error').hide();
            $('#saveAgent').click(function (event) {
    <?php
    if (isset($_GET['id'])) {
        ?>
                    var dataObj = {'type': 'editagent', 'agentId': <?php echo $_GET['id']; ?>, 'firstName': $('#first_name').val(), 'lastName': $('#last_name').val(), 'tenant': $('#tenant').val(), 'email': $('#email').val(), 'password': $('#password').val(), 'usernamehidden': $('#usernamehidden').val()};
        <?php
    } else {
        ?>
                    var dataObj = {'type': 'addagent', 'username': $('#username').val(), 'firstName': $('#first_name').val(), 'lastName': $('#last_name').val(), 'tenant': $('#tenant').val(), 'email': $('#email').val(), 'password': $('#password').val()};
        <?php
    }
    ?>
                $.ajax({
                    type: 'POST',
                    url: '../server/script.php',
                    data: dataObj
                })
                        .done(function (data) {
                            if (data) {
                                location.href = 'agents.php';
                            } else {
                                $('#error').show();
                                $('#error').html('<span data-localize="error_agent_save"></span>');
                                var opts = {language: 'en', pathPrefix: 'locales', loadBase: true};
                                $('[data-localize]').localize('dashboard', opts);
                            }
                        })
                        .fail(function () {
                        });
            });
            $.ajax({
                type: 'POST',
                url: '../server/script.php',
                data: {'type': 'getadmin', 'id': <?php echo (int) @$_GET['id'] ?>}
            })
                    .done(function (data) {
                        if (data) {
                            data = JSON.parse(data);
                            $('#agentTitle').html(data.first_name + ' ' + data.last_name);
                            $('#usernamehidden').val(data.username);
                            $('#username').val(data.username);
                            if (data.password) {
                                $('#leftblank').html(' <span data-localize="left_blank_changed"></span>');
                            }
                            //$('#password').val(data.password);
                            $('#first_name').val(data.first_name);
                            $('#last_name').val(data.last_name);
                            $('#tenant').val(data.tenant);
                            $('#email').val(data.email);
                            var opts = {language: 'en', pathPrefix: 'locales', loadBase: true};
                            $('[data-localize]').localize('dashboard', opts);
                        }
                    })
                    .fail(function (e) {
                        console.log(e);
                    });
        });</script>

    <?php
}
if ($basename == 'config.php') {
    ?>


    <script>


        jQuery(document).ready(function ($) {
            $('#error').hide();


            let voices = [];
            window.speechSynthesis.onvoiceschanged = () => {
                voices = window.speechSynthesis.getVoices();
                let voiceSelect = document.querySelector("#text_speech_lang");
                voices.forEach((voice, i) => (voiceSelect.options[i] = new Option(voice.name, i)));
            };

            $('#videoScreen_exitMeetingDrop').on('change', function () {
                if (this.value == 3) {
                    $('#videoScreen_exitMeeting').show();
                } else {
                    $('#videoScreen_exitMeeting').hide();
                }
            });
            $('#saveConfig').click(function (event) {
                if ($('#videoScreen_exitMeetingDrop').val() == 1) {
                    var exitMeeting = false;
                } else if ($('#videoScreen_exitMeetingDrop').val() == 2) {
                    exitMeeting = '/';
                } else if ($('#videoScreen_exitMeetingDrop').val() == 3) {
                    exitMeeting = $('#videoScreen_exitMeeting').val()
                }
                var dataObj = {'type': 'updateconfig', 'fileName': '<?php echo $fileConfig; ?>', 'data': {
                        'appWss': $('#appWss').val(),
                        'agentName': $('#agentName').val(),
                        'agentAvatar': $('#agentAvatar').val(),
                        'smartVideoLanguage': $('#smartVideoLanguage').val(),
                        'anonVisitor': $('#anonVisitor').val(),
                        'entryForm.enabled': $('#entryForm_enabled').prop('checked'),
                        'entryForm.required': $('#entryForm_required').prop('checked'),
                        'entryForm.private': $('#entryForm_private').prop('checked'),
                        'entryForm.showEmail': $('#entryForm_showEmail').prop('checked'),
                        'entryForm.showAvatar': $('#entryForm_showAvatar').prop('checked'),
                        'entryForm.terms': $('#entryForm_terms').val(),
                        'recording.enabled': $('#recording_enabled').prop('checked'),
                        'recording.download': $('#recording_download').prop('checked'),
                        'recording.saveServer': $('#recording_saveServer').prop('checked'),
                        'recording.autoStart': $('#recording_autoStart').prop('checked'),
                        'recording.screen': $('#recording_screen').prop('checked'),
                        'recording.oneWay': $('#recording_oneWay').prop('checked'),
                        'recording.transcode': $('#recording_transcode').prop('checked'),
                        'recording.filename': $('#recording_filename').val(),
                        'recording.recordingConstraints': ($('#recording_recordingConstraints').val()) ? JSON.parse($('#recording_recordingConstraints').val()) : '',
                        'whiteboard.enabled': $('#whiteboard_enabled').prop('checked'),
                        'whiteboard.allowAnonymous': $('#whiteboard_allowAnonymous').prop('checked'),
                        'videoScreen.greenRoom': $('#videoScreen_greenRoom').prop('checked'),
                        'videoScreen.waitingRoom': $('#videoScreen_waitingRoom').prop('checked'),
                        'videoScreen.videoConference': $('#videoScreen_videoConference').prop('checked'),
                        'videoScreen.onlyAgentButtons': $('#videoScreen_onlyAgentButtons').prop('checked'),
                        'videoScreen.getSnapshot': $('#videoScreen_getSnapshot').prop('checked'),
                        'videoScreen.separateScreenShare': $('#videoScreen_separateScreenShare').prop('checked'),
                        'videoScreen.enableLogs': $('#videoScreen_enableLogs').prop('checked'),
                        'videoScreen.broadcastAttendeeVideo': $('#videoScreen_broadcastAttendeeVideo').prop('checked'),
                        'videoScreen.allowOtherSee': $('#videoScreen_allowOtherSee').prop('checked'),
                        'videoScreen.localFeedMirrored': $('#videoScreen_localFeedMirrored').prop('checked'),
                        'videoScreen.exitMeetingOnTime': $('#videoScreen_exitMeetingOnTime').prop('checked'),
                        'videoScreen.exitMeetingOnTimeAgent': $('#videoScreen_exitMeetingOnTimeAgent').prop('checked'),
                        'videoScreen.meetingTimer': $('#videoScreen_meetingTimer').prop('checked'),
                        'videoScreen.admit': $('#videoScreen_admit').prop('checked'),
                        'videoScreen.pipEnabled': $('#videoScreen_pipEnabled').prop('checked'),
                        'videoScreen.primaryCamera': $('#videoScreen_primaryCamera').val(),
                        'videoScreen.dateFormat': $('#videoScreen_dateFormat').val(),
                        'videoScreen.videoFileStream': $('#videoScreen_videoFileStream').val(),
                        'videoScreen.videoConstraint': ($('#videoScreen_videoConstraint').val()) ? JSON.parse($('#videoScreen_videoConstraint').val()) : '',
                        'videoScreen.audioConstraint': ($('#videoScreen_audioConstraint').val()) ? JSON.parse($('#videoScreen_audioConstraint').val()) : '',
                        'videoScreen.screenConstraint': ($('#videoScreen_screenConstraint').val()) ? JSON.parse($('#videoScreen_screenConstraint').val()) : '',
                        'videoScreen.exitMeeting': exitMeeting,
                        'serverSide.loginForm': $('#serverSide_loginForm').prop('checked'),
                        'serverSide.chatHistory': $('#serverSide_chatHistory').prop('checked'),
                        'serverSide.feedback': $('#serverSide_feedback').prop('checked'),
                        'serverSide.checkRoom': $('#serverSide_checkRoom').prop('checked'),
                        'serverSide.videoLogs': $('#serverSide_videoLogs').prop('checked'),
                        'iceServers.iceServers': ($('#iceServers').val()) ? JSON.parse($('#iceServers').val()) : '',
                        'iceServers.requirePass': $('#iceServers_requirePass').prop('checked'),
                        'transcribe.languageTo': $('#transcribe_languageTo').val(),
                        'transcribe.language': $('#transcribe_language').val(),
                        'transcribe.direction': $('#transcribe_direction').val(),
                        'transcribe.apiKey': $('#transcribe_apiKey').val(),
                        'transcribe.enabled': $('#transcribe_enabled').prop('checked'),
                        'social.facebookId': $('#social_facebookId').val(),
                        'social.googleId': $('#social_googleId').val(),
                        'social.enabled': $('#social_enabled').prop('checked'),
                        'transcribe.text_speech_lang': $('#text_speech_lang').val(),
                        'transcribe.text_speech_chat': $('#text_speech_chat').prop('checked'),
                        'transcribe.text_speech_transcribe': $('#text_speech_transcribe').prop('checked'),
                        'virtualBackground.blur': $('#virtual_blur').prop('checked'),
                        'virtualBackground.backgrounds': $('#virtual_backgrounds').prop('checked')
                    }};
                $.ajax({
                    type: 'POST',
                    cache: false,
                    dataType: 'json',
                    url: '../server/script.php',
                    data: dataObj
                })
                        .done(function (data) {
                            if (data) {
                                location.href = 'config.php?file=<?php echo $fileConfig; ?>';
                            } else {
                                $('#error').show();
                                $('#error').html('<span data-localize="error_config_save"></span>');
                                var opts = {language: 'en', pathPrefix: 'locales', loadBase: true};
                                $('[data-localize]').localize('dashboard', opts);
                            }
                        })
                        .fail(function (e) {
                            console.log(e);
                        });
            });
            $('#addConfig').click(function (event) {
                var dataObj = {'type': 'addconfig', 'fileName': $('#fileName').val()};
                $.ajax({
                    type: 'POST',
                    cache: false,
                    dataType: 'json',
                    url: '../server/script.php',
                    data: dataObj
                })
                        .done(function (data) {
                            if (data) {
                                location.href = 'config.php?file=' + $('#fileName').val();
                            } else {
                                $('#error').show();
                                $('#error').html('<span data-localize="error_config_add"></span>');
                                var opts = {language: 'en', pathPrefix: 'locales', loadBase: true};
                                $('[data-localize]').localize('dashboard', opts);
                            }
                        })
                        .fail(function (e) {
                            console.log(e);
                        });
            });
    <?php
    $jsonString = file_get_contents('../config/' . $fileConfig . '.json');
    $data = json_decode($jsonString);
    ?>

            $('#appWss').val('<?php echo @$data->appWss; ?>');
            $('#agentName').val('<?php echo @$data->agentName; ?>');
            $('#agentAvatar').val('<?php echo @$data->agentAvatar; ?>');
            $('#smartVideoLanguage').val('<?php echo @$data->smartVideoLanguage; ?>');
            $('#anonVisitor').val('<?php echo @$data->anonVisitor; ?>');
            $('#entryForm_enabled').prop('checked', <?php echo @$data->entryForm->enabled; ?>);
            $('#entryForm_required').prop('checked', <?php echo @$data->entryForm->required; ?>);
            $('#entryForm_private').prop('checked', <?php echo @$data->entryForm->private; ?>);
            $('#entryForm_showEmail').prop('checked', <?php echo @$data->entryForm->showEmail; ?>);
            $('#entryForm_showAvatar').prop('checked', <?php echo @$data->entryForm->showAvatar; ?>);
            $('#entryForm_terms').val('<?php echo @$data->entryForm->terms; ?>');
            $('#recording_enabled').prop('checked', <?php echo @$data->recording->enabled; ?>);
            $('#recording_download').prop('checked', <?php echo @$data->recording->download; ?>);
            $('#recording_saveServer').prop('checked', <?php echo @$data->recording->saveServer; ?>);
            $('#recording_autoStart').prop('checked', <?php echo @$data->recording->autoStart; ?>);
            $('#recording_screen').prop('checked', <?php echo @$data->recording->screen; ?>);
            $('#recording_oneWay').prop('checked', <?php echo @$data->recording->oneWay; ?>);
            $('#recording_transcode').prop('checked', <?php echo @$data->recording->transcode; ?>);
            $('#recording_filename').val('<?php echo @$data->recording->filename; ?>');
            $('#recording_recordingConstraints').val('<?php echo (isset($data->recording->recordingConstraints)) ? json_encode($data->recording->recordingConstraints, JSON_FORCE_OBJECT) : ''; ?>');
            $('#whiteboard_enabled').prop('checked', <?php echo @$data->whiteboard->enabled; ?>);
            $('#whiteboard_allowAnonymous').prop('checked', <?php echo @$data->whiteboard->allowAnonymous; ?>);
            $('#videoScreen_greenRoom').prop('checked', <?php echo @$data->videoScreen->greenRoom; ?>);
            $('#videoScreen_waitingRoom').prop('checked', <?php echo @$data->videoScreen->waitingRoom; ?>);
            $('#videoScreen_videoConference').prop('checked', <?php echo @$data->videoScreen->videoConference; ?>);
            $('#videoScreen_onlyAgentButtons').prop('checked', <?php echo @$data->videoScreen->onlyAgentButtons; ?>);
            $('#videoScreen_getSnapshot').prop('checked', <?php echo @$data->videoScreen->getSnapshot; ?>);
            $('#videoScreen_separateScreenShare').prop('checked', <?php echo @$data->videoScreen->separateScreenShare; ?>);
            $('#videoScreen_broadcastAttendeeVideo').prop('checked', <?php echo @$data->videoScreen->broadcastAttendeeVideo; ?>);
            $('#videoScreen_allowOtherSee').prop('checked', <?php echo @$data->videoScreen->allowOtherSee; ?>);
            $('#videoScreen_localFeedMirrored').prop('checked', <?php echo @$data->videoScreen->localFeedMirrored; ?>);
            $('#videoScreen_exitMeetingOnTime').prop('checked', <?php echo @$data->videoScreen->exitMeetingOnTime; ?>);
            $('#videoScreen_exitMeetingOnTimeAgent').prop('checked', <?php echo @$data->videoScreen->exitMeetingOnTimeAgent; ?>);
            $('#videoScreen_meetingTimer').prop('checked', <?php echo @$data->videoScreen->meetingTimer; ?>);
            $('#videoScreen_admit').prop('checked', <?php echo @$data->videoScreen->admit; ?>);
            $('#videoScreen_pipEnabled').prop('checked', <?php echo @$data->videoScreen->pipEnabled; ?>);
            $('#videoScreen_primaryCamera').val('<?php echo @$data->videoScreen->primaryCamera; ?>');
            $('#videoScreen_dateFormat').val('<?php echo @$data->videoScreen->dateFormat; ?>');
            $('#videoScreen_videoFileStream').val('<?php echo @$data->videoScreen->videoFileStream; ?>');
            $('#videoScreen_videoConstraint').val('<?php echo (isset($data->videoScreen->videoConstraint)) ? json_encode($data->videoScreen->videoConstraint, JSON_FORCE_OBJECT) : ''; ?>');
            $('#videoScreen_audioConstraint').val('<?php echo (isset($data->videoScreen->audioConstraint)) ? json_encode($data->videoScreen->audioConstraint, JSON_FORCE_OBJECT) : ''; ?>');
            $('#videoScreen_screenConstraint').val('<?php echo (isset($data->videoScreen->screenConstraint)) ? json_encode($data->videoScreen->screenConstraint, JSON_FORCE_OBJECT) : ''; ?>');
            var exitMeeting = '<?php echo addslashes($data->videoScreen->exitMeeting); ?>';
            if (exitMeeting == false) {
                $('#videoScreen_exitMeetingDrop').val(1);
                $('#videoScreen_exitMeeting').hide();
            } else if (exitMeeting == '/') {
                $('#videoScreen_exitMeetingDrop').val(2);
                $('#videoScreen_exitMeeting').hide();
            } else {
                $('#videoScreen_exitMeetingDrop').val(3);
                $('#videoScreen_exitMeeting').show();
                $('#videoScreen_exitMeeting').val(exitMeeting);
            }

            $('#serverSide_loginForm').prop('checked', <?php echo @$data->serverSide->loginForm; ?>);
            $('#serverSide_chatHistory').prop('checked', <?php echo @$data->serverSide->chatHistory; ?>);
            $('#serverSide_feedback').prop('checked', <?php echo @$data->serverSide->feedback; ?>);
            $('#serverSide_checkRoom').prop('checked', <?php echo @$data->serverSide->checkRoom; ?>);
            $('#serverSide_videoLogs').prop('checked', <?php echo @$data->serverSide->videoLogs; ?>);
            $('#iceServers').val('<?php echo (isset($data->iceServers->iceServers)) ? json_encode($data->iceServers->iceServers) : ''; ?>')
            $('#iceServers_requirePass').prop('checked', <?php echo @$data->iceServers->requirePass; ?>);
            $('#videoScreen_enableLogs').prop('checked', <?php echo @$data->videoScreen->enableLogs; ?>);
            $('#transcribe_enabled').prop('checked', <?php echo @$data->transcribe->enabled; ?>);
            $('#transcribe_language').val('<?php echo @$data->transcribe->language; ?>');
            $('#transcribe_languageTo').val('<?php echo @$data->transcribe->languageTo; ?>');
            $('#transcribe_direction').val('<?php echo @$data->transcribe->direction; ?>');
            $('#transcribe_apiKey').val('<?php echo @$data->transcribe->apiKey; ?>');
            $('#social_enabled').prop('checked', <?php echo @$data->social->enabled; ?>);
            $('#social_facebookId').val('<?php echo @$data->social->facebookId; ?>');
            $('#social_googleId').val('<?php echo @$data->social->googleId; ?>');
            $('#text_speech_chat').prop('checked', <?php echo @$data->transcribe->text_speech_chat; ?>);
            $('#text_speech_transcribe').prop('checked', <?php echo @$data->transcribe->text_speech_transcribe; ?>);
            $('#virtual_blur').prop('checked', <?php echo @$data->virtualBackground->blur; ?>);
            $('#virtual_backgrounds').prop('checked', <?php echo @$data->virtualBackground->backgrounds; ?>);
            setTimeout(function () {
                $('#text_speech_lang').val('<?php echo @$data->transcribe->text_speech_lang; ?>');
            }, 300);
        });</script>

    <?php
}
if ($basename == 'locale.php') {
    ?>


    <script>

    <?php
    $jsonString = file_get_contents('../locales/' . $fileLocale . '.json');

    $data = json_decode($jsonString, true);
    $fileContent = '';
    $fileData = '';
    foreach ($data as $key => $value) {
        $fileContent .= '<div class="form-group"><label for="roomName"><h6>' . $key . ':</h6></label><input type="text" class="form-control" id="' . $key . '" aria-describedby="' . $key . '" value="' . htmlentities(addslashes($value)) . '"></div>';
        $fileData .= "'" . $key . "': $('#" . $key . "').val(),";
    };
    $fileData = substr($fileData, 0, -1);
    ?>
        jQuery(document).ready(function ($) {
            $('#error').hide();
            $('#saveLocale').click(function (event) {
                var dataObj = {'type': 'updatelocale', 'fileName': '<?php echo $fileLocale; ?>', 'data': {<?php echo $fileData; ?>}};
                $.ajax({
                    type: 'POST',
                    cache: false,
                    dataType: 'json',
                    url: '../server/script.php',
                    data: dataObj
                })
                        .done(function (data) {
                            if (data) {
                                location.href = 'locale.php?file=<?php echo $fileLocale; ?>';
                            } else {
                                $('#error').show();
                                $('#error').html('<span data-localize="error_locale_save"></span>');
                                var opts = {language: 'en', pathPrefix: 'locales', loadBase: true};
                                $('[data-localize]').localize('dashboard', opts);
                            }
                        })
                        .fail(function (e) {
                            console.log(e);
                        });
            });
            $('#addLocale').click(function (event) {
                var dataObj = {'type': 'addlocale', 'fileName': $('#fileName').val()};
                $.ajax({
                    type: 'POST',
                    cache: false,
                    dataType: 'json',
                    url: '../server/script.php',
                    data: dataObj
                })
                        .done(function (data) {
                            if (data) {
                                location.href = 'locale.php?file=' + $('#fileName').val();
                            } else {
                                $('#error').show();
                                $('#error').html('<span data-localize="error_locale_add"></span>');
                                var opts = {language: 'en', pathPrefix: 'locales', loadBase: true};
                                $('[data-localize]').localize('dashboard', opts);
                            }
                        })
                        .fail(function (e) {
                            console.log(e);
                        });
            });

            $('#localeStrings').html('<?php echo $fileContent; ?>');
        });</script>

    <?php
}
if ($basename == 'user.php') {
    ?>


    <script>


        jQuery(document).ready(function ($) {
            $('#error').hide();
            $('#saveUser').click(function (event) {
                var isBlocked = ($('#is_blocked').prop('checked')) ? 1 : 0;
    <?php
    if (isset($_GET['id'])) {
        ?>
                    var name = $('#first_name').val() + ' ' + $('#last_name').val();
                    var dataObj = {'type': 'edituser', 'userId': <?php echo $_GET['id']; ?>, 'name': name, 'firstName': $('#first_name').val(), 'lastName': $('#last_name').val(), 'username': $('#email').val(), 'password': $('#password').val(), 'isBlocked': isBlocked};
        <?php
    } else {
        ?>
                    var dataObj = {'type': 'adduser', 'username': $('#email').val(), 'firstName': $('#first_name').val(), 'lastName': $('#last_name').val(), 'name': $('#first_name').val() + ' ' + $('#last_name').val(), 'password': $('#password').val(), 'isBlocked': isBlocked};
        <?php
    }
    ?>
                $.ajax({
                    type: 'POST',
                    url: '../server/script.php',
                    data: dataObj
                })
                        .done(function (data) {
                            if (data) {
                                location.href = 'users.php';
                            } else {
                                $('#error').show();
                                $('#error').html('<span data-localize="error_user_save"></span>');
                                var opts = {language: 'en', pathPrefix: 'locales', loadBase: true};
                                $('[data-localize]').localize('dashboard', opts);
                            }
                        })
                        .fail(function () {
                        });
            });
            $.ajax({
                type: 'POST',
                url: '../server/script.php',
                data: {'type': 'getuser', 'id': <?php echo (int) @$_GET['id'] ?>}
            })
                    .done(function (data) {
                        if (data) {
                            data = JSON.parse(data);
                            $('#userTitle').html(data.name);
                            $('#username').val(data.username);
                            if (data.password) {
                                $('#leftblank').html('<span data-localize="left_blank_changed"></span>');
                            }
                            //$('#password').val(data.password);
                            if (!data.first_name && !data.last_name) {
                                var name = data.name.split(' ');
                                data.first_name = name[0];
                                data.last_name = name[1];
                            }
                            $('#first_name').val(data.first_name);
                            $('#last_name').val(data.last_name);
                            $('#email').val(data.username);
                            $('#is_blocked').prop('checked', (data.is_blocked == "1"));
                            var opts = {language: 'en', pathPrefix: 'locales', loadBase: true};
                            $('[data-localize]').localize('dashboard', opts);
                        }
                    })
                    .fail(function (e) {
                        console.log(e);
                    });
        });</script>

    <?php
}
if ($basename == 'agents.php') {
    ?>
    <script>

        jQuery(document).ready(function ($) {

            $(document).on('click', '.deleteAgentRow', function (e) {
                var $btn = $(this);
                var $tr = $btn.closest('tr');
                var dataTableRow = dataTable.row($tr[0]);
                var rowData = dataTableRow.data();
                deleteItem(rowData.agent_id, 'agent', e);
            });

            var dataTable = $('#agents_table').DataTable({
                "pagingType": "numbers",
                "order": [[0, 'asc']],
                "processing": true,
                "serverSide": true,
                "ajax": {
                    "url": "../server/script.php",
                    "type": "POST",
                    "data": {'type': 'getagents'}
                },
                "columns": [
                    {
                        "data": "username",
                        "name": "username",
                        render: function (data, type) {
                            return data;
                        }
                    },
                    {
                        "data": "first_name",
                        "name": "first_name",
                        render: function (data, type, row) {
                            return row.first_name + ' ' + row.last_name;
                        }
                    },
                    {
                        "data": "tenant",
                        "data": "tenant",
                        render: function (data, type) {
                            return data;
                        }
                    },
                    {
                        "data": "email",
                        "name": "email",
                        render: function (data, type) {
                            return data;
                        }
                    },
                    {
                        "data": "agent_id",
                        "orderable": false,
                        render: function (data, type, row) {
                            if (row.is_master == 1) {
                                var link = '<a href="agent.php?id=' + row.agent_id + '" data-localize="edit"></a>';
                            } else {
                                link = '<a href="agent.php?id=' + row.agent_id + '" data-localize="edit"></a> | <a href="#" class="deleteAgentRow" data-localize="delete"></a>';
                            }
                            return link;
                        }
                    }
                ],
                "language": {
                    "url": "locales/table.json"
                },
                "drawCallback": function (settings) {
                    var opts = {language: 'en', pathPrefix: 'locales', loadBase: true};
                    $('[data-localize]').localize('dashboard', opts);
                }
            });

        });</script>

    <?php
}
if ($basename == 'users.php') {
    ?>
    <script>

        jQuery(document).ready(function ($) {
            $(document).on('click', '.deleteUserRow', function (e) {
                var $btn = $(this);
                var $tr = $btn.closest('tr');
                var dataTableRow = dataTable.row($tr[0]);
                var rowData = dataTableRow.data();
                deleteItem(rowData.user_id, 'user', e);
            });

            var dataTable = $('#users_table').DataTable({
                "pagingType": "numbers",
                "order": [[0, 'asc']],
                "processing": true,
                "serverSide": true,
                "ajax": {
                    "url": "../server/script.php",
                    "type": "POST",
                    "data": {'type': 'getusers'}
                },
                "columns": [

                    {
                        "data": "name",
                        "name": "name",
                        render: function (data, type) {
                            return data;
                        }
                    },
                    {
                        "data": "username",
                        "name": "username",
                        render: function (data, type) {
                            return data;
                        }
                    },                    {
                        "data": "is_blocked",
                        "data": "is_blocked",
                        "orderable": false,
                        render: function (data, type) {
                            var yesNo = (data == "1") ? '<span data-localize="yes"></span>' : '<span data-localize="no"></span>';
                            return yesNo;
                        }
                    },
                    {
                        "data": "user_id",
                        "orderable": false,
                        render: function (data, type) {
                            var link = '<a href="user.php?id=' + data + '" data-localize="edit"></a> | <a href="#" class="deleteUserRow" data-localize="delete"></a>';
                            return link;
                        }
                    }
                ],
                "language": {
                    "url": "locales/table.json"
                },
                "drawCallback": function (settings) {
                    var opts = {language: 'en', pathPrefix: 'locales', loadBase: true};
                    $('[data-localize]').localize('dashboard', opts);
                }
            });
        });</script>

    <?php
}
if ($basename == 'recordings.php') {
    ?>
    <script>

        jQuery(document).ready(function ($) {

            $(document).on('click', '.deleteRecordingRow', function (e) {
                var $btn = $(this);
                var $tr = $btn.closest('tr');
                var dataTableRow = dataTable.row($tr[0]);
                var rowData = dataTableRow.data();
                deleteItem(rowData.recotding_id, 'recording', e);
            });

            var dataTable = $('#recordings_table').DataTable({
                "pagingType": "numbers",
                "order": [[3, 'desc']],
                "processing": true,
                "serverSide": true,
                "ajax": {
                    "url": "../server/script.php",
                    "type": "POST",
                    "data": {'type': 'getrecordings'}
                },
                "columns": [

                    {
                        "data": "filename",
                        "name": "filename",
                        render: function (data, type) {
                            return '<a href="../server/recordings/' + data + '" target="_blank">' + data + '</a>';
                        }
                    },
                    {
                        "data": "room_id",
                        "name": "room_id",
                        render: function (data, type) {
                            return data;
                        }
                    },                    
                    {
                        "data": "agent_id",
                        "name": "agent_id",
                        render: function (data, type) {
                            return data;
                        }
                    },
                    {
                        "data": "date_created",
                        "name": "date_created",
                        render: function (data, type) {
                            return data;
                        }
                    },
                    {
                        "data": "recording_id",
                        "orderable": false,
                        render: function (data, type, row) {
                            var link = '<a href="../server/recordings/' + row.filename + '" target="_blank" data-localize="view"></a> | <a href="#" class="deleteRecordingRow" data-localize="delete"></a>';
                            return link;
                        }
                    }
                ],
                "language": {
                    "url": "locales/table.json"
                },
                "drawCallback": function (settings) {
                    var opts = {language: 'en', pathPrefix: 'locales', loadBase: true};
                    $('[data-localize]').localize('dashboard', opts);
                }
            });

        });</script>

    <?php
}
if ($basename == 'rooms.php') {
    ?>
    <script>
        var dateFormat = function () {
            var token = /d{1,4}|m{1,4}|yy(?:yy)?|([HhMsTt])\1?|[LloSZ]|"[^"]*"|'[^']*'/g,
                    timezone = /\b(?:[PMCEA][SDP]T|(?:Pacific|Mountain|Central|Eastern|Atlantic) (?:Standard|Daylight|Prevailing) Time|(?:GMT|UTC)(?:[-+]\d{4})?)\b/g,
                    timezoneClip = /[^-+\dA-Z]/g,
                    pad = function (val, len) {
                        val = String(val);
                        len = len || 2;
                        while (val.length < len)
                            val = "0" + val;
                        return val;
                    };

            // Regexes and supporting functions are cached through closure
            return function (date, mask, utc) {
                var dF = dateFormat;

                // You can't provide utc if you skip other args (use the "UTC:" mask prefix)
                if (arguments.length == 1 && Object.prototype.toString.call(date) == "[object String]" && !/\d/.test(date)) {
                    mask = date;
                    date = undefined;
                }

                // Passing date through Date applies Date.parse, if necessary
                date = date ? new Date(date) : new Date;
                if (isNaN(date))
                    throw SyntaxError("invalid date");

                mask = String(dF.masks[mask] || mask || dF.masks["default"]);

                // Allow setting the utc argument via the mask
                if (mask.slice(0, 4) == "UTC:") {
                    mask = mask.slice(4);
                    utc = true;
                }

                var _ = utc ? "getUTC" : "get",
                        d = date[_ + "Date"](),
                        D = date[_ + "Day"](),
                        m = date[_ + "Month"](),
                        y = date[_ + "FullYear"](),
                        H = date[_ + "Hours"](),
                        M = date[_ + "Minutes"](),
                        s = date[_ + "Seconds"](),
                        L = date[_ + "Milliseconds"](),
                        o = utc ? 0 : date.getTimezoneOffset(),
                        flags = {
                            d: d,
                            dd: pad(d),
                            ddd: dF.i18n.dayNames[D],
                            dddd: dF.i18n.dayNames[D + 7],
                            m: m + 1,
                            mm: pad(m + 1),
                            mmm: dF.i18n.monthNames[m],
                            mmmm: dF.i18n.monthNames[m + 12],
                            yy: String(y).slice(2),
                            yyyy: y,
                            h: H % 12 || 12,
                            hh: pad(H % 12 || 12),
                            H: H,
                            HH: pad(H),
                            M: M,
                            MM: pad(M),
                            s: s,
                            ss: pad(s),
                            l: pad(L, 3),
                            L: pad(L > 99 ? Math.round(L / 10) : L),
                            t: H < 12 ? "a" : "p",
                            tt: H < 12 ? "am" : "pm",
                            T: H < 12 ? "A" : "P",
                            TT: H < 12 ? "AM" : "PM",
                            Z: utc ? "UTC" : (String(date).match(timezone) || [""]).pop().replace(timezoneClip, ""),
                            o: (o > 0 ? "-" : "+") + pad(Math.floor(Math.abs(o) / 60) * 100 + Math.abs(o) % 60, 4),
                            S: ["th", "st", "nd", "rd"][d % 10 > 3 ? 0 : (d % 100 - d % 10 != 10) * d % 10]
                        };

                return mask.replace(token, function ($0) {
                    return $0 in flags ? flags[$0] : $0.slice(1, $0.length - 1);
                });
            };
        }();

        // Some common format strings
        dateFormat.masks = {
            "default": "dd-mm-yyyy HH:MM",
            shortDate: "m/d/yy HH:MM",
            mediumDate: "mmm d, yyyy HH:MM",
            longDate: "mmmm d, yyyy HH:MM",
            fullDate: "dddd, mmmm d, yyyy HH:MM",
            isoDate: "yyyy-mm-dd HH:MM",
            isoUtcDateTime: "UTC:yyyy-mm-dd'T'HH:MM:ss'Z'"
        };

        // Internationalization strings
        dateFormat.i18n = {
            dayNames: [
                "Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat",
                "Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"
            ],
            monthNames: [
                "Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec",
                "January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"
            ]
        };

        Date.prototype.format = function (mask, utc) {
            return dateFormat(this, mask, utc);
        };
        jQuery(document).ready(function ($) {

            $(document).on('click', '.deleteClassRow', function (e) {
                var $btn = $(this);
                var $tr = $btn.closest('tr');
                var dataTableRow = dataTable.row($tr[0]);
                var rowData = dataTableRow.data();
                deleteItem(rowData.room_id, 'room', e);
            });

            var dataTable = $('#rooms_table').DataTable({
                "pagingType": "numbers",
                "order": [[0, 'desc']],
                "processing": true,
                "serverSide": true,
                "createdRow": function (row, data, index) {
                    $('td', row).eq(0).attr('id', 'roomid_' + data.roomId);

                },
                "ajax": {
                    "url": "../server/script.php",
                    "type": "POST",
                    "data": {'type': 'getrooms', 'agentId': agentId}
                },
                "columns": [
                    {
                        "data": "roomId",
                        "name": "roomId",
                        render: function (data, type) {
                            return data;
                        }
                    },
                    {
                        "data": "agent",
                        "name": "agent",
                        render: function (data, type) {
                            return data;
                        }
                    },
                    {
                        "data": "visitor",
                        "data": "visitor",
                        render: function (data, type) {
                            return data;
                        }
                    },
                    {
                        "data": "shortagenturl",
                        "name": "shortagenturl",
                        render: function (data, type, row) {
                            let link = '<a target="_blank" title="Conference agent URL" href="' + row.agenturl + '"><?php echo $actual_link; ?>' + row.shortagenturl + '</a><br/><a title="Broadcast agent URL" target="_blank" href="' + row.agenturl_broadcast + '"><?php echo $actual_link; ?>' + row.shortagenturl_broadcast + '</a>';
                            return link;
                        }
                    },
                    {
                        "data": "shortvisitorurl",
                        "data": "shortvisitorurl",
                        render: function (data, type, row) {
                            let link = '<a target="_blank" title="Conference agent URL" href="' + row.visitorurl + '"><?php echo $actual_link; ?>' + row.shortvisitorurl + '</a><br/><a title="Broadcast agent URL" target="_blank" href="' + row.visitorurl_broadcast + '"><?php echo $actual_link; ?>' + row.shortvisitorurl_broadcast + '</a>';
                            return link;
                        }
                    },
                    {
                        "data": "datetime",
                        "data": "datetime",
                        render: function (data, type, row) {
                            var datetimest = '';
                            if (row.datetime) {
                                datetimest = getCurrentDateFormatted(row.datetime) + ' / ';
                            }
                            if (row.duration) {
                                datetimest += row.duration;
                            }
                            return datetimest;
                        }
                    },
                    {
                        "data": "is_active",
                        "data": "is_active",
                        render: function (data, type) {
                            var isActive = (data == "1") ? '<span data-localize="yes">Yes</span>' : '<span data-localize="no">No</span>';
                            return isActive;
                        }
                    },
                    {
                        "data": "room_id",
                        "orderable": false,
                        render: function (data, type) {
                            let link = '<a href="room.php?id=' + data + '" data-localize="edit"></a> | <a href="#" class="deleteClassRow" data-localize="delete"></a>';
                            return link;
                        }
                    }
                ],
                "language": {
                    "url": "locales/table.json"
                },
                "drawCallback": function (settings) {
                    var opts = {language: 'en', pathPrefix: 'locales', loadBase: true};
                    $('[data-localize]').localize('dashboard', opts);
                }
            });
        });</script>

    <?php
}
if ($basename == 'visitors.php') {
    ?>
    <script>

        jQuery(document).ready(function ($) {
            setTimeout(function () {
                svConfigs.agentName = '<?php echo @$_SESSION["agent"]["first_name"] . ' ' . @$_SESSION["agent"]["last_name"]; ?>';
            }, 3000);
        });</script>

    <?php
}
if ($basename == 'integration.php') {
    ?>
    <script>

        jQuery(document).ready(function ($) {
            setTimeout(function () {
                svConfigs.agentName = '<?php echo @$_SESSION["agent"]["first_name"] . ' ' . @$_SESSION["agent"]["last_name"]; ?>';
            }, 3000);

            $(document).on('click', '.deleteIntegrationRow', function (e) {
                var $btn = $(this);
                var $tr = $btn.closest('tr');
                var dataTableRow = dataTable.row($tr[0]);
                var rowData = dataTableRow.data();
                deleteItem(rowData.room_id, 'room', e);
            });

            var dataTable = $('#rooms_table').DataTable({
                "pagingType": "numbers",
                "order": [[0, 'desc']],
                "processing": true,
                "serverSide": true,
                "createdRow": function (row, data, index) {
                    $('td', row).eq(0).attr('id', 'roomid_' + data.roomId);

                },
                "ajax": {
                    "url": "../server/script.php",
                    "type": "POST",
                    "data": {'type': 'getrooms', 'agentId': agentId}
                },
                "columns": [
                    {
                        "data": "roomId",
                        "name": "roomId",
                        render: function (data, type) {
                            return data;
                        }
                    },
                    {
                        "data": "agent",
                        "name": "agent",
                        render: function (data, type) {
                            return data;
                        }
                    },
                    {
                        "data": "visitor",
                        "data": "visitor",
                        render: function (data, type) {
                            return data;
                        }
                    },
                    {
                        "data": "shortagenturl",
                        "name": "shortagenturl",
                        render: function (data, type, row) {
                            let link = '<a target="_blank" title="Conference agent URL" href="' + row.agenturl + '"><?php echo $actual_link; ?>' + row.shortagenturl + '</a><br/><a title="Broadcast agent URL" target="_blank" href="' + row.agenturl_broadcast + '"><?php echo $actual_link; ?>' + row.shortagenturl_broadcast + '</a>';
                            return link;
                        }
                    },
                    {
                        "data": "shortvisitorurl",
                        "data": "shortvisitorurl",
                        render: function (data, type, row) {
                            let link = '<a target="_blank" title="Conference agent URL" href="' + row.visitorurl + '"><?php echo $actual_link; ?>' + row.shortvisitorurl + '</a><br/><a title="Broadcast agent URL" target="_blank" href="' + row.visitorurl_broadcast + '"><?php echo $actual_link; ?>' + row.shortvisitorurl_broadcast + '</a>';
                            return link;
                        }
                    }
                ],
                "language": {
                    "url": "locales/table.json"
                },
                "drawCallback": function (settings) {
                    var opts = {language: 'en', pathPrefix: 'locales', loadBase: true};
                    $('[data-localize]').localize('dashboard', opts);
                }
            });
        });</script>

    <?php
}
if ($basename == 'chats.php') {
    ?>
    <script>

        jQuery(document).ready(function ($) {


            $('#chats_table').DataTable({
                "pagingType": "numbers",
                "order": [[0, 'desc']],
                "processing": true,
                "serverSide": true,
                "ajax": {
                    "url": "../server/script.php",
                    "type": "POST",
                    "data": {'type': 'getchats', 'agentId': agentId}
                },
                "columns": [
                    {"data": "date_created"},
                    {"data": "room_id"},
                    {"data": "messages", "orderable": false},
                    {"data": "agent", "orderable": false}
                ],
                "language": {
                    "url": "locales/table.json"
                },
                "drawCallback": function (settings) {
                    var opts = {language: 'en', pathPrefix: 'locales', loadBase: true};
                    $('[data-localize]').localize('dashboard', opts);
                }
            });
        });</script>

    <?php
}
if ($basename == 'videologs.php') {
    ?>
    <script>

        jQuery(document).ready(function ($) {



            $('#logs_table').DataTable({
                "pagingType": "numbers",
                "order": [[0, 'desc']],
                "processing": true,
                "serverSide": true,
                "ajax": {
                    "url": "../server/script.php",
                    "type": "POST",
                    "data": {'type': 'getlogs', 'agentId': agentId}
                },
                "columns": [
                    {"data": "date_created"},
                    {"data": "room_id"},
                    {"data": "messages", "orderable": false},
                    {"data": "agent", "orderable": false}
                ],
                "language": {
                    "url": "locales/table.json"
                },
                "drawCallback": function (settings) {
                    var opts = {language: 'en', pathPrefix: 'locales', loadBase: true};
                    $('[data-localize]').localize('dashboard', opts);
                }
            });
        });</script>

    <?php
}
if ($basename == 'dash.php') {
    ?>
    <script>

        jQuery(document).ready(function ($) {

            $.ajax({
                type: 'POST',
                url: '../server/script.php',
                data: {'type': 'getrooms', 'agentId': agentId}
            })
                    .done(function (data) {
                        if (data) {
                            var result = JSON.parse(data);
                            $('#roomsCount').html(result.recordsTotal);
                        }
                    })
                    .fail(function () {
                        console.log(false);
                    });
            $.ajax({
                type: 'POST',
                url: '../server/script.php',
                data: {'type': 'getagents', 'agentId': agentId}
            })
                    .done(function (data) {
                        if (data) {
                            var result = JSON.parse(data);
                            $('#agentsCount').html(result.recordsTotal);
                        }
                    })
                    .fail(function () {
                        console.log(false);
                    });
            $.ajax({
                type: 'POST',
                url: '../server/script.php',
                data: {'type': 'getusers', 'agentId': agentId}
            })
                    .done(function (data) {
                        if (data) {
                            var result = JSON.parse(data);
                            $('#usersCount').html(result.recordsTotal);
                        }
                    })
                    .fail(function () {
                        console.log(false);
                    });
            $.ajaxSetup({cache: false});
            $.getJSON('https://www.new-dev.com/version/version.json', function (data) {
                if (data) {
                    var currentVersion = '<?php echo $currentVersion; ?>';
                    var newNumber = data.version.split('.');
                    var curNumber = currentVersion.split('.');
                    var isNew = false;
                    if (parseInt(curNumber[0]) < parseInt(newNumber[0])) {
                        isNew = true;
                    }
                    if (parseInt(curNumber[0]) == parseInt(newNumber[0]) && parseInt(curNumber[1]) < parseInt(newNumber[1])) {
                        isNew = true;
                    }
                    if (parseInt(curNumber[0]) == parseInt(newNumber[0]) && parseInt(curNumber[1]) == parseInt(newNumber[1]) && parseInt(curNumber[2]) < parseInt(newNumber[2])) {
                        isNew = true;
                    }

                    if (isNew) {
    <?php if (@$_SESSION["tenant"] == 'lsv_mastertenant') { ?>
                            $('#remoteVersion').html('<span data-localize="new_lsv_version"></span>' + data.version + '<br/><br/><span data-localize="new_lsv_features"></span><br/>' + data.text + '<br/><br/><span data-localize="update_location"></span>');
    <?php } else { ?>
                            $('#remoteVersion').html('<span data-localize="new_lsv_version"></span>' + data.version + '<br/><br/><span data-localize="new_lsv_features"></span><br/>' + data.text);
    <?php } ?>
                    } else {
                        $('#remoteVersion').html('<span data-localize="version_uptodate"></span>');
                    }

                } else {
                    $('#remoteVersion').html('<span data-localize="cannot_connect"></span>');
                }
                var opts = {language: 'en', pathPrefix: 'locales', loadBase: true, callback: function (data, defaultCallback) {
                        document.title = data.title;
                        defaultCallback(data);
                    }};
                $('[data-localize]').localize('dashboard', opts);
            });
        });</script>

    <?php
}
if ($basename == 'room.php') {
    ?><script>
    <?php
    if (isset($_GET['id'])) {
        ?>

                var queryStr = function (url) {
                    // This function is anonymous, is executed immediately and
                    // the return value is assigned to QueryString!
                    var query_string = {};
                    var query = url.substring(1);
                    var vars = query.split("&");
                    for (var i = 0; i < vars.length; i++) {
                        var pair = vars[i].split("=");
                        if (typeof query_string[pair[0]] === "undefined") {
                            query_string[pair[0]] = pair[1];
                        } else if (typeof query_string[pair[0]] === "string") {
                            var arr = [query_string[pair[0]], pair[1]];
                            query_string[pair[0]] = arr;
                        } else {
                            query_string[pair[0]].push(pair[1]);
                        }
                    }
                    return query_string;
                };
                $.ajax({
                    type: 'POST',
                    url: '../server/script.php',
                    data: {'type': 'getroombyid', 'room_id': <?php echo (int) @$_GET['id'] ?>}
                })
                        .done(function (data) {
                            if (data) {
                                data = JSON.parse(data);
                                var parsed = {};
                                if (data.visitorurl) {
                                    var visitorUrl = data.visitorurl;
                                    var parser = document.createElement('a');
                                    parser.href = visitorUrl;
                                    parsed = JSON.parse(decodeURIComponent(escape(window.atob(queryStr(parser.search).p))));
                                }
                                $('#roomName').val(data.roomId);
                                $('#names').val(data.agent);
                                $('#visitorName').val(parsed.visitorName);
                                $('#shortagent').val(data.shortagenturl);
                                $('#shortvisitor').val(data.shortvisitorurl);
                                $('#config').val(parsed.config);
                                if (data.datetime) {
                                    let current_datetime = new Date(data.datetime);
                                    var formatted_date = (current_datetime.getMonth() + 1) + '/' + current_datetime.getDate() + '/' + current_datetime.getFullYear() + ' ' + current_datetime.getHours() + ':' + current_datetime.getMinutes();
                                    $('#datetime').val(formatted_date);
                                }

                                $('#duration').val(data.duration);
                                if (data.duration != 15 || data.duration != 30 || data.duration != 45) {
                                    $('#durationtext').val(data.duration);
                                }
                                if (parsed.disableVideo) {
                                    $('#disableVideo').prop('checked', true);
                                }
                                if (parsed.disableAudio) {
                                    $('#disableAudio').prop('checked', true);
                                }
                                if (parsed.disableScreenShare) {
                                    $('#disableScreenShare').prop('checked', true);
                                }
                                if (parsed.disableWhiteboard) {
                                    $('#disableWhiteboard').prop('checked', true);
                                }
                                if (parsed.disableTransfer) {
                                    $('#disableTransfer').prop('checked', true);
                                }
                                if (parsed.autoAcceptVideo) {
                                    $('#autoAcceptVideo').prop('checked', true);
                                }
                                if (parsed.autoAcceptAudio) {
                                    $('#autoAcceptAudio').prop('checked', true);
                                }
                                $('#active').prop('checked', (data.is_active == "1"));
                            }
                        })
                        .fail(function (e) {
                            console.log(e);
                        });
        <?php
    }
    ?>



            var agentUrl, visitorUrl, sessionId, shortAgentUrl, shortVisitorUrl, agentBroadcastUrl, viewerBroadcastLink, shortAgentUrl_broadcast, shortVisitorUrl_broadcast;
            jQuery(document).ready(function ($) {
                $('#error').hide();
                $('#saveRoom').on('click', function () {
                    generateLink();
                    var datetime = ($('#datetime').val()) ? new Date($('#datetime').val()).toISOString() : '';
                    var duration = ($('#durationtext').val()) ? $('#durationtext').val() : $('#duration').val();
    <?php
    if (isset($_GET['id'])) {
        ?>
                        var dataObj = {'room_id': '<?php echo $_GET['id']; ?>', 'type': 'editroom', 'agentId': agentId, 'agent': $('#names').val(), 'agenturl': agentUrl, 'visitor': $('#visitorName').val(), 'visitorurl': visitorUrl,
                            'password': $('#roomPass').val(), 'session': sessionId, 'datetime': datetime, 'duration': duration, 'shortVisitorUrl': shortVisitorUrl, 'shortAgentUrl': shortAgentUrl,
                            'agenturl_broadcast': agentBroadcastUrl, 'visitorurl_broadcast': viewerBroadcastLink, 'shortVisitorUrl_broadcast': shortVisitorUrl_broadcast, 'shortAgentUrl_broadcast': shortAgentUrl_broadcast, 'is_active': $('#active').prop('checked')};
        <?php
    } else {
        ?>
                        var dataObj = {'type': 'scheduling', 'agentId': agentId, 'agent': $('#names').val(), 'agenturl': agentUrl, 'visitor': $('#visitorName').val(), 'visitorurl': visitorUrl,
                            'password': $('#roomPass').val(), 'session': sessionId, 'datetime': datetime, 'duration': duration, 'shortVisitorUrl': shortVisitorUrl, 'shortAgentUrl': shortAgentUrl,
                            'agenturl_broadcast': agentBroadcastUrl, 'visitorurl_broadcast': viewerBroadcastLink, 'shortVisitorUrl_broadcast': shortVisitorUrl_broadcast, 'shortAgentUrl_broadcast': shortAgentUrl_broadcast, 'is_active': $('#active').prop('checked')};
                        //                        var dataObj = {'type': 'addroom', 'lsRepUrl': '<?php echo $actual_link; ?>', 'roomId': $('#roomName').val(), 'agentName': $('#names').val(), 'visitorName': $('#visitorName').val(), 'agentShortUrl': $('#shortagent').val(), 'visitorShortUrl': $('#shortvisitor').val(), 'password': $('#roomPass').val(),
                        //                            'config': $('#config').val(), 'dateTime': datetime, 'duration': $('#duration').val(), 'disableVideo': $('#disableVideo').prop('checked'), 'disableAudio': $('#disableAudio').prop('checked'), 'disableScreenShare': $('#disableScreenShare').prop('checked'), 'disableWhiteboard': $('#disableWhiteboard').prop('checked'), 'disableTransfer': $('#disableTransfer').prop('checked'), 'is_active': $('#active').prop('checked')};
        <?php
    }
    ?>
                    $.ajax({
                        type: 'POST',
                        url: '../server/script.php',
                        data: dataObj
                    })
                            .done(function (data) {
                                if (data == 200) {
                                    location.href = 'rooms.php';
                                } else {
                                    $('#error').show();
                                    $('#error').html('<span data-localize="error_room_save"></span>');
                                    var opts = {language: 'en', pathPrefix: 'locales', loadBase: true};
                                    $('[data-localize]').localize('dashboard', opts);
                                }
                            })
                            .fail(function () {
                                $('#error').show();
                                $('#error').html('<span data-localize="error_room_save"></span>');
                                var opts = {language: 'en', pathPrefix: 'locales', loadBase: true};
                                $('[data-localize]').localize('dashboard', opts);
                            });
                });

                $('#generateBroadcastLink').on('click', function () {
                    generateLink(true);
                    window.open(agentUrl);
                    var text = $('#generateBroadcastLinkModal').html();
                    $('#generateBroadcastLinkModal').html(text.replace('[generateBroadcastLink]', viewerBroadcastLink));
                    $('#generateBroadcastLinkModal').modal('toggle');
                    $('#copyBroadcastUrl').off();
                    $('#copyBroadcastUrl').on('click', function () {
                        $('#generateBroadcastLinkModal').modal('hide');
                        copyUrl(viewerBroadcastLink);
                    });
                });
                var d = new Date();
                //            $('#datetime').datetimepicker({
                //                timeFormat: 'h:mm TT',
                //                stepHour: 1,
                //    //                        stepMinute: 15,
                //                controlType: 'select',
                //                hourMin: 8,
                //                hourMax: 21,
                //                minDate: new Date(d.getFullYear(), d.getMonth(), d.getDate(), d.getHours(), 0),
                //                oneLine: true
                //            });
                $('#datetime').datetimepicker({

                    format: 'MM/DD/YYYY HH:mm',
                    minDate: new Date(d.getFullYear(), d.getMonth(), d.getDate(), d.getHours(), 0),
                    icons: {
                        time: 'fa fa-clock',
                        date: 'fa fa-calendar',
                        up: 'fa fa-chevron-up',
                        down: 'fa fa-chevron-down',
                        previous: 'fa fa-chevron-left',
                        next: 'fa fa-chevron-right',
                        today: 'fa fa-check',
                        clear: 'fa fa-trash',
                        close: 'fa fa-times'
                    }
                });
            });



    </script>

<?php } ?>
<script>
    jQuery(document).ready(function ($) {
        var opts = {language: 'en', pathPrefix: 'locales', loadBase: true, callback: function (data, defaultCallback) {
                document.title = data.title;
                defaultCallback(data);
            }};
        $('[data-localize]').localize('dashboard', opts);
    });

</script>
<script src="vendor/datatables/jquery.dataTables.min.js"></script>
<script src="vendor/datatables/dataTables.bootstrap4.min.js"></script>
<script src="js/moment.min.js"></script>
<script src="js/bootstrap-datetimepicker.js"></script>
<script src="js/jquery.localize.js" type="text/javascript" charset="utf-8"></script>
<script src="<?php echo $actual_link; ?>js/loader.v2.js" data-source_path="<?php echo $actual_link; ?>" ></script>
</body>

</html>
