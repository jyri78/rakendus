<?php
require "fnc_news.php";


$newsHTML = readNews();

?>
<!DOCTYPE html>
<html lang="et">
<head>
	<meta charset="utf-8">
    <title>Teine loeng | Veebirakendused ja nende loomine 2020</title>
    <style>
body {max-width:1024px; margin:3vw auto;}
    </style>
</head>
<body>
    <h1>Uudiste vaatamine | Andmebaasid - Teine loeng</h1>
<?= $newsHTML ?>
</body>
</html>