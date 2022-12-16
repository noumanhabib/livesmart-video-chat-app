<?php

function liveSmartInsertUser($username, $password, $email, $lsRepUrl) {

    $posts = http_build_query(array('type' => 'addagent', 'username' => $username, 'password' => $password, 'firstName' => $username, 'lastName' => $username, 'email' => $email, 'tenant' => $username));
    $ch = curl_init();
    curl_setopt_array($ch, array(
        CURLOPT_URL => $lsRepUrl . 'server/script.php',
        CURLOPT_POST => 1,
        CURLOPT_POSTFIELDS => $posts,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_TIMEOUT => 2
    ));

    $response = @curl_exec($ch);

    if (curl_errno($ch) > 0) {
        curl_close($ch);
        return false;
    } else {

        $responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($responseCode !== 200) {
            curl_close($ch);
            return false;
        }
        curl_close($ch);
        $posts = http_build_query(array('type' => 'addroom', 'lsRepUrl' => $lsRepUrl, 'agentId' => $username, 'agentName' => $username, 'visitorName' => '', 'agentShortUrl' => $username . '_a', 'visitorShortUrl' => $username, 'is_active' => true));
        $ch = curl_init();
        curl_setopt_array($ch, array(
            CURLOPT_URL => $lsRepUrl . 'server/script.php',
            CURLOPT_POST => 1,
            CURLOPT_POSTFIELDS => $posts,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_TIMEOUT => 2
        ));

        $response = @curl_exec($ch);

        if (curl_errno($ch) > 0) {
            curl_close($ch);
            return false;
        } else {

            $responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            if ($responseCode !== 200) {
                curl_close($ch);
                return false;
            }
            curl_close($ch);
            return true;
        }
    }
}

function liveSmartCheckUser($username, $password, $email, $lsRepUrl) {

    $posts = http_build_query(array('type' => 'loginagent', 'username' => $username, 'password' => $password));
    $ch = curl_init();
    curl_setopt_array($ch, array(
        CURLOPT_URL => $lsRepUrl . 'server/script.php',
        CURLOPT_POST => 1,
        CURLOPT_POSTFIELDS => $posts,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_TIMEOUT => 2
    ));

    $response = curl_exec($ch);
    curl_close($ch);
    if (!$response) {
        liveSmartInsertUser($username, $password, $email, $lsRepUrl);
    }
}

function liveSmartDeleteUser($username, $lsRepUrl) {

    $posts = http_build_query(array('type' => 'deleteagentbyusername', 'username' => $username));
    $ch = curl_init();
    curl_setopt_array($ch, array(
        CURLOPT_URL => $lsRepUrl . 'server/script.php',
        CURLOPT_POST => 1,
        CURLOPT_POSTFIELDS => $posts,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_TIMEOUT => 2
    ));

    curl_exec($ch);
    curl_close($ch);
    $posts = http_build_query(array('type' => 'deleteroombyagent', 'agentId' => $username));
    $ch = curl_init();
    curl_setopt_array($ch, array(
        CURLOPT_URL => $lsRepUrl . 'server/script.php',
        CURLOPT_POST => 1,
        CURLOPT_POSTFIELDS => $posts,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_TIMEOUT => 2
    ));

    curl_exec($ch);
    curl_close($ch);
}

function convertToHoursMins($time) {
    if ($time < 1) {
        return;
    }
    $hours = substr($time, 0, 2);
    $minutes = substr($time, 2, 2);
    return array($hours, $minutes);
}

function insertLiveSmartRoom($booking, $agentUrl = null, $visitorUrl = null) {
    $start_date = $booking['date'];
    $datetimeStart = convertToHoursMins($booking['start_time']);
    $datetimeEnd = convertToHoursMins($booking['end_time']);

    $dateStart = get_gmt_from_date($start_date . ' ' . $datetimeStart[0] . ':' . $datetimeStart[1], 'Y-m-d\TH:i:s\Z');
    $duration = (strtotime($start_date . ' ' . $datetimeEnd[0] . ':' . $datetimeEnd[1]) - strtotime($start_date . ' ' . $datetimeStart[0] . ':' . $datetimeStart[1])) / 60;

    $lsRepUrl = get_option('livesmart_server_url');

    $posts = http_build_query(array('type' => 'addroom', 'lsRepUrl' => $lsRepUrl, 'agentShortUrl' => $agentUrl, 'visitorShortUrl' => $visitorUrl, 'agentId' => $booking['agent_user'], 'dateTime' => $dateStart, 'duration' => $duration, 'agentName' => $booking['agent_name'], 'visitorName' => $booking['visitor_name'], 'is_active' => true));
    $ch = curl_init();
    curl_setopt_array($ch, array(
        CURLOPT_URL => $lsRepUrl . 'server/script.php',
        CURLOPT_POST => 1,
        CURLOPT_POSTFIELDS => $posts,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_TIMEOUT => 2
    ));

    $response = @curl_exec($ch);

    if (curl_errno($ch) > 0) {
        curl_close($ch);
        return false;
    } else {

        $responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($responseCode !== 200) {
            curl_close($ch);
            return false;
        }
        curl_close($ch);
        return true;
    }
}
