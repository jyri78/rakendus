<?php
session_start();

// Kustutab kõik sessiooniga seonduva
session_unset();
session_write_close();
?>
<!DOCTYPE html>
<html lang="et">
<head>
	<meta charset="utf-8">
    <title>Loeng 2 | Veebirakendused ja nende loomine 2020</title>
    <style>
        body {max-width:1024px; margin:3vw auto;}
    </style>
</head>
<body>
    <h1>Andmebaasid - Teine loeng | Jüri Kormik</h1>
    <p><a href="/~juri.kormik/" alt="Jüri Kormik - RIF19">🏠 Pealehele</a></p>
	<p>See leht on valminud õppetöö raames!</p>
    <p><a href="addnews.php">Lisa uudis</a> | <a href="news.php">Loe uudiseid</a></p>
    <p><b>Iseseisvalt:</b> <a href="addstudy.php">Lisa õppimine</a> | <a href="studylog.php"
            >Õppimise logi</a> | <a href="courses_activities.php">Kursused ja tegevused</a></p>
</body>
</html>