<?php
$success = '';
$divErrors = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $liveSmartFolder = realpath(dirname(dirname(__FILE__)));
    $filenameCert = $_POST['filenameCert'];
    $filenameKey = $_POST['filenameKey'];

    $servername = 'localhost';
    $database = $_POST['database'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $charset = 'utf8mb4';
    $dbPrefix = '';
    $turnserverUser = $_POST['turnserverUser'];
    $turnserverPass = $_POST['turnserverPass'];

    $output = shell_exec('node -v');
    $curNumber = str_replace('v', '', $output);
    $curNumber = explode('.', $curNumber);
    $errors = array();
    if (!isset($curNumber[0])) {
        array_push($errors, 'Node version not suitable or Node is missing. You need to install it.<br/>');
    }
    if ($curNumber[0] < 12) {
        array_push($errors, 'Node version is less then 12.<br/>');
    }

    if (!is_dir($liveSmartFolder) && !is_dir($liveSmartFolder . '/config')) {
        array_push($errors, 'LiveSmart folder is not correct. Make sure the folder exists and your LiveSmart files are there.<br/>');
    }

    $compare = "https://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    $explodeUrl = explode('/install', $compare);

    $liveSmartURL = $explodeUrl[0];


    //    if (!file_exists($filenameCert)) {
    //        array_push($errors, 'Certificate file you provided is missing. Please make sure file location is properly provided.<br/>');
    //    }
    //
    //    if (!file_exists($filenameKey)) {
    //        array_push($errors, 'Key file you provided is missing. Please make sure file location is properly provided.<br/>');
    //    }

    $dsn = "mysql:host=$servername;dbname=$database;charset=$charset";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];

    try {
        $pdo = new PDO($dsn, $username, $password, $options);
    } catch (\PDOException $e) {
        if ($e->getCode() == 1045) {
            array_push($errors, 'Provided MySQL credentials are not proper.<br/>');
        } else if ($e->getCode() == 1049) {
            array_push($errors, 'Provided MySQL database is not existing.<br/>');
        } else if ($e->getCode() == 2002) {
            array_push($errors, 'Cannot connect to the host. Make sure your DB can access remote connections.<br/>');
        } else {
            array_push($errors, $e->getMessage() . '<br/>');
        }
    }

    if (count($errors) > 0) {
        foreach ($errors as $error) {
            $divErrors .= $error;
        }
    } else {

        $liveSmartURL = str_replace('www.', '', $liveSmartURL);
        $stripUrlArr = explode('/', str_replace('https://', '', $liveSmartURL));
        $stripUrl = $stripUrlArr[0];
        $liveSmartIP = gethostbyname($stripUrl);

        $sql = file_get_contents('../server/dump_2.0.1.sql');
        $sql .= file_get_contents('../server/dump_2.0.9.sql');
        $sql .= file_get_contents('../server/dump_2.0.28.sql');
        $sql .= file_get_contents('../server/dump_2.0.33.sql');
        $pdo->exec($sql);

        //server/connect.php file
        $phpContent = file_get_contents($liveSmartFolder . '/server/connect.php');
        $str = str_replace('$database = \'\';', '$database = \'' . $database . '\';', $phpContent);
        $str = str_replace('$username = \'\';', '$username = \'' . $username . '\';', $str);
        $str = str_replace('$password = \'\';', '$password = \'' . $password . '\';', $str);
        if (file_put_contents($liveSmartFolder . '/server/connect.php', $str) == false) {
            array_push($errors, 'Error writing to connect.php file. Make sure files in LiveSmart folder are not with root ownershi.<br/>');
        }

        //ws/server/config.json file
        $phpContent = file_get_contents($liveSmartFolder . '/ws/server/config.json');
        if (count($errors) == 0) {
            $success .= 'Database setup successfully finished!<br/>';
        }
        $arrs = json_decode($phpContent, true);
        foreach ($arrs as $key => $value) {
            if ($key == 'sslKey') {
                $arrs[$key] = $filenameKey;
            }
            if ($key == 'sslCert') {
                $arrs[$key] = $filenameCert;
            }
        }

        $str = json_encode($arrs, JSON_UNESCAPED_SLASHES);

        if (file_put_contents($liveSmartFolder . '/ws/server/config.json', $str) == false) {
            array_push($errors, 'Error writing to ws/server/config.json file. Make sure files in LiveSmart folder are not with root ownershi.<br/>');
        }

        //config/config.json file
        $phpContent = file_get_contents($liveSmartFolder . '/config/config.json');
        $urls[] = array('urls' => array('stun:' . $stripUrl . ':3478'), 'credential' => $turnserverPass, 'username' => $turnserverUser);
        $urls[] = array('urls' => array('turn:' . $stripUrl . ':5349'), 'credential' => $turnserverPass, 'username' => $turnserverUser);

        $iceServers = array('iceServers' => $urls);
        $arrs = json_decode($phpContent, true);
        foreach ($arrs as $key => $value) {
            if ($key == 'appWss') {
                $arrs[$key] = 'https://' . $stripUrl . ':9001/';
            }
            if ($key == 'iceServers') {
                $arrs[$key] = $iceServers;
            }
        }
        if (count($errors) == 0) {
            $success .= 'Configuration files successfully generated!<br/>';
        }
        $str = json_encode($arrs, JSON_UNESCAPED_SLASHES);

        if (file_put_contents($liveSmartFolder . '/config/config.json', $str) == false) {
            array_push($errors, 'Error writing to config/config.json file. Make sure files in LiveSmart folder are not with root ownershi.<br/>');
        }
        $content = '#listening-ip=' . $liveSmartIP . PHP_EOL . PHP_EOL;
        $content .= '#relay-ip=' . $liveSmartIP . PHP_EOL . PHP_EOL;
        $content .= 'external-ip=' . $liveSmartIP . PHP_EOL . PHP_EOL;
        $content .= 'server-name=' . $stripUrl . PHP_EOL . PHP_EOL;
        $content .= 'oauth' . PHP_EOL . PHP_EOL;
        $content .= 'user=' . $turnserverUser . ':' . $turnserverPass . PHP_EOL . PHP_EOL;
        $content .= 'realm=' . $stripUrl . PHP_EOL . PHP_EOL;
        $content .= 'cert=' . $filenameCert . PHP_EOL . PHP_EOL;
        $content .= 'pkey=' . $filenameKey;
        file_put_contents('turnserver.conf', $content);

        rename($liveSmartFolder . '/demo.htaccess', $liveSmartFolder . '/.htaccess');

        //HTML test files
        $phpContent = file_get_contents($liveSmartFolder . '/agent.html');
        $str = str_replace('YOUR_DOMAIN', $liveSmartURL, $phpContent);
        file_put_contents($liveSmartFolder . '/agent.html', $str);
        $phpContent = file_get_contents($liveSmartFolder . '/agent.php');
        $str = str_replace('YOUR_DOMAIN', $liveSmartURL, $phpContent);
        file_put_contents($liveSmartFolder . '/agent.php', $str);
        $phpContent = file_get_contents($liveSmartFolder . '/client.html');
        $str = str_replace('YOUR_DOMAIN', $liveSmartURL, $phpContent);
        file_put_contents($liveSmartFolder . '/client.html', $str);
        $phpContent = file_get_contents($liveSmartFolder . '/client.php');
        $str = str_replace('YOUR_DOMAIN', $liveSmartURL, $phpContent);
        file_put_contents($liveSmartFolder . '/client.php', $str);
        $phpContent = file_get_contents($liveSmartFolder . '/clientiframe.html');
        $str = str_replace('YOUR_DOMAIN', $liveSmartURL, $phpContent);

        if (file_put_contents($liveSmartFolder . '/clientiframe.html', $str) == false) {
            array_push($errors, 'Error writing to sample HTML files. Make sure files in LiveSmart folder are not with root ownershi.<br/>');
        }

        if (count($errors) == 0) {
            $success .= 'Turnserver configuration file successfully generated!<br/>';
            $success .= 'Now you have to login to your console/terminal with root user, go to installation folder at ' . $liveSmartFolder . ' and run the installation script from there. If you are on Ubuntu/Debian run <code>sudo bash script_ubuntu.sh</code> and if you are on CentOS, run <code>sudo bash script_centos.sh</code><br/>';
            $success .= 'Make sure your ports 9001, 3478 and 5349 are opened for TCP and 3478/5349 are opened for UDP traffic.<br/>';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>LiveSmart installation wizard</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <style>
    .bs-example {
        padding-top: 5px;
        margin: auto;
        width: 900px;
    }
    </style>
</head>

<body>
    <div class="bs-example">
        <p>
            Welcome to LiveSmart installation wizard! This script applies for Ubuntu/Debian and CentOS operating
            systems. In order to fully install LiveSmart and <a
                href="https://www.new-dev.com/page/ident/live_smart_video_chat_installation#stunturn"
                target="_blank">turnserver</a> on your server, you need:<br />
            - some basic knowledge of Linux administration;<br />
            - root access;<br />
            - SSL certificate and key;<br />
            - MySQL database;<br />
            When you click on Setup button, the config files will be generated and you will get instructions on how to
            finalize your installation. Check this <a href="https://www.youtube.com/watch?v=Sjk0nsiRu3E"
                target="_blank">video tutorial</a> for step by step instructions.
        </p>
        <hr>
        <?php if ($success) { ?>
        <div id="success" class="alert alert-success col-sm-10">
            <?php echo $success; ?>
        </div>
        <?php } ?>
        <?php if ($divErrors) { ?>
        <div id="errors" class="alert alert-danger col-sm-10">
            <?php echo $divErrors; ?>
        </div>
        <?php } ?>
        <form method="post">
            <div class="form-group row">
                <label for="filenameCert" class="col-sm-3 col-form-label">Certificate path</label>

                <div class="col-sm-8">
                    <input type="input" class="form-control" name="filenameCert" id="filenameCert"
                        value="<?php echo @$_POST['filenameCert'] ?>" placeholder="Certificate absolute path" required>
                </div>
            </div>
            <div class="form-group row">
                <label for="filenameKey" class="col-sm-3 col-form-label">Key path</label>

                <div class="col-sm-8">
                    <input type="input" class="form-control" name="filenameKey" id="filenameKey"
                        value="<?php echo @$_POST['filenameKey'] ?>" placeholder="Key absolute path" required>
                </div>
            </div>
            <div class="form-group row">
                <small>Absolute paths where your certificate and key are.
                    <br />CPanel/WHM users for CentOS can check this <a
                        href="https://www.new-dev.com/page/ident/live_smart_video_chat_cpanel"
                        target="_blank">tutorial</a>.
                    <br />Letsencrypt certificates are located in
                    cert: /etc/letsencrypt/live/YOURDOMAIN/fullchain.pem and key:
                    /etc/letsencrypt/live/YOURDOMAIN/privkey.pem
                    <br />
                    Other location, where you can check are /etc/ssl/private/ and /etc/ssl/certs
                </small>
            </div>
            <div class="form-group row">
                <label for="database" class="col-sm-3 col-form-label">Database</label>
                <div class="col-sm-8">
                    <input type="input" class="form-control" name="database" id="database" placeholder="Database name"
                        value="<?php echo @$_POST['database'] ?>" required>
                </div>
            </div>
            <div class="form-group row">
                <label for="username" class="col-sm-3 col-form-label">Database Username</label>
                <div class="col-sm-8">
                    <input type="input" class="form-control" name="username" id="username"
                        placeholder="Database username" value="<?php echo @$_POST['username'] ?>" required>
                </div>
            </div>
            <div class="form-group row">
                <label for="password" class="col-sm-3 col-form-label">Database Password</label>
                <div class="col-sm-8">
                    <input type="password" class="form-control" name="password" id="password"
                        placeholder="Database password" required>
                </div>
            </div>
            <div class="form-group row">
                <small>You need to provide here your database information, where you need LiveSmart to be installed.
                </small>
            </div>
            <div class="form-group row">
                <label for="turnserverUser" class="col-sm-3 col-form-label">Turnserver Username</label>
                <div class="col-sm-8">
                    <input type="input" class="form-control" name="turnserverUser" id="turnserverUser"
                        placeholder="Turnserver username" value="<?php echo @$_POST['turnserverUser'] ?>" required>
                </div>
            </div>
            <div class="form-group row">
                <label for="turnserverPass" class="col-sm-3 col-form-label">Turnserver Password</label>
                <div class="col-sm-8">
                    <input type="password" class="form-control" name="turnserverPass" id="turnserverPass"
                        placeholder="Turnserver password" value="<?php echo @$_POST['turnerverPass'] ?>" required>
                </div>
            </div>
            <div class="form-group row">
                <small>Username and password of your choice for the installation of your turnserver.
                </small>
            </div>
            <div class="form-group row">
                <div class="col-sm-10 offset-sm-2">
                    <button type="submit" class="btn btn-primary">Setup</button>
                </div>
            </div>
        </form>
    </div>
</body>

</html>