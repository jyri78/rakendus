<?php
// Lisab andmebaasi ja sessiooni klassid
require("DB.class.php");
require("Session.class.php");

// Sessiooninimi ja kaust
define("SESSION_NAME", 'vr20_'. SESSION_ID);
define("SESSION_PATH", '/~juri.kormik/veebirakendused/');
define("SESSION_DOMAIN", 'tigu.hk.tlu.ee');
// Tabelinime eesliide
define("SQL_TABLE_PREFIX", 'vr20__');
define("TABLE_PREFIX", SQL_TABLE_PREFIX);  // tagasiühilduvus 3. loengule


// Mõned globaalsed muutujad
$a = explode('/', filter_var($_SERVER["PHP_SELF"], FILTER_SANITIZE_URL));
$_self = end($a);
unset($a);

// Kasutusel 4. loengu failides
$originalPhotoDir = '../../../uploadOriginalPhoto/';
$normalPhotoDir = '../../../uploadNormalPhoto/';
$fileUploadSizeLimit = 1048576; //1024*1024;
$fileNamePrefix = 'vr_';
$imageMaxWidth = 600;
$imageMaxHeight = 400;
$imageThumbSize = 150;

// Kasutusel alates 5. loengu failides (peamiselt klass)
define("IMG_WATERMARK_SRC", '../inc/vr_watermark.png');
define("IMG_WATERMARK_SUFIX", '_wm');
define("IMG_ORIGINAL_PHOTO_DIR", $originalPhotoDir);
define("IMG_NORMAL_PHOTO_DIR", $normalPhotoDir);
define("IMG_FILE_NAME_PREFIX", $fileNamePrefix);
define("IMG_MAX_WIDTH", $imageMaxWidth);
define("IMG_MAX_HEIGHT", $imageMaxHeight);
define("IMG_THUMB_SIZE", $imageThumbSize);


// Üldkasutatav funktsioon
function test_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}


// Lõpuks käivitab sessiooni
SessionManager::sessionStart(SESSION_NAME, 0, SESSION_PATH, SESSION_DOMAIN);
