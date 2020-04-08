<?php
require "../inc/fnc.php";
require "fnc_photos.php";


// Privaatsustase (võrdlemiseks):  2 - avalik,  3 - sisseloginud kasutaja
$privacy = 2;
if (isset($_SESSION["userid"])) $privacy = 3;


if (isset($_GET['img'])) {
    $img = test_input($_GET['img']);
    $ok = testImageName($img, $privacy);

    // Kui pilti andmebaasist ei leitud (või pole lubatud kuvada), suunab galeriisse
    if (!$ok) header("Location: gallery.php");
    else {
        // Kui pilti mingil põhjusel ei ole, siis suunab galeriisse
        if (! file_exists($normalPhotoDir . $img)) header("Location: gallery.php");

        $type = substr($img, -3);
        if ($type == 'peg') $type = 'jpeg';

        header('Content-Type: image/'. $type);
        echo file_get_contents($normalPhotoDir . $img);
    }
} else {
    header("Location: gallery.php");
}
