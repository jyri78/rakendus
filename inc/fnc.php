<?php
// Lisab andmebaasi ja sessiooni klassid
require("DB.class.php");
require("Session.class.php");

// Sessiooninimi ja kaust
define("SESSION_NAME", 'vr20_'. SESSION_ID);
define("SESSION_PATH", '/~juri.kormik/veebirakendused/');
define("SESSION_DOMAIN", 'tigu.hk.tlu.ee');


function test_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

