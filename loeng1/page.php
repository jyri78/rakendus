<?php
    $my_name = "Jüri Kormik";
    $full_time_now = date("d.m.Y H:i:s");
    $time_HTML = '<p>Lehe avamise hetkel oli: <b>'. $full_time_now ."</b>.</p>\n";

    $hour_now = date("H");
    $part_of_day = "hägune aeg";

    if ($hour_now < 10) {
        $part_of_day = "hommik";
    }
    elseif ($hour_now < 18) {
        $part_of_day = "aeg aktiivselt tegutseda";
    }

    $part_of_day_HTML = "<p>Käes on ". $part_of_day ."!</p>";
?>
<!DOCTYPE html>
<html lang="et">
<head>
	<meta charset="utf-8">
	<title>Veebirakendused ja nende loomine 2020</title>
</head>
<body>
	<h1><?= $my_name ?></h1>
    <p>See leht on valminud õppetöö raames!</p>
    <?= $time_HTML ?>
    <?= $part_of_day_HTML ?>
</body>
</html>