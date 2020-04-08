<?php
// Lisab andmebaasi ja sessiooni klassid
require("DB.class.php");
require("Session.class.php");

// Sessiooninimi ja kaust
define("SESSION_NAME", 'vr20_'. SESSION_ID);
define("SESSION_PATH", '/~juri.kormik/veebirakendused/');
define("SESSION_DOMAIN", 'tigu.hk.tlu.ee');


// Mõned globaalsed muutujad
$a = explode('/', filter_var($_SERVER["PHP_SELF"], FILTER_SANITIZE_URL));
$_self = end($a);
unset($a);

$originalPhotoDir = '../../../uploadOriginalPhoto/';
$normalPhotoDir = '../../../uploadNormalPhoto/';
$fileUploadSizeLimit = 1048576; //1024*1024;
$fileNamePrefix = 'vr_';
$imageMaxWidth = 600;
$imageMaxHeight = 400;
$imageThumbSize = 150;


// Üldkasutatav funktsioon
function test_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}


// Lõpuks käivitab sessiooni
SessionManager::sessionStart(SESSION_NAME, 0, SESSION_PATH, SESSION_DOMAIN);
