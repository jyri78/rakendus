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

    $part_of_day_HTML = "<p>Käes on ". $part_of_day ."!</p>\n";

    $semester_start = new DateTime("2020-01-27");
    $semester_end = new DateTime("2020-06-22");
    $semester_duration = $semester_start->diff($semester_end);
    //var_dump($semester_duration);
    $today = new DateTime("now");
    $from_semester_start = $semester_start->diff($today);
    //var_dump($from_semester_start);

    if ($from_semester_start->days < 1) {
        $semester_duration_HTML = "<p>Semester pole veel alanud!</p>";
    }
    elseif ($from_semester_start->days > $semester_duration->days) {
        $semester_duration_HTML = "<p>Semester on juba läbi!</p>";
    }
    else {
        $semester_duration_HTML = '<p>Semester on hoos: <meter value="'
                . $from_semester_start->days .'" min="0" max="'
                . $semester_duration->days .'">'
                . $from_semester_start->format("%r%a") .'/'
                . $semester_duration->format("%r%a") .'</meter></p>'
                . "\n";
    }

    // Fotode lugemine
    $pics_dir = "../../pics/";
    $photo_types_allowed = ["image/jpeg", "image/png"];
    $photo_list = [];

    $all_files = array_slice(scandir($pics_dir), 2);
    //var_dump($all_files);

    foreach ($all_files as $file) {
        $file_info = getimagesize($pics_dir . $file);

        if (in_array($file_info["mime"], $photo_types_allowed)) {
            $photo_list[] = $file;
            //array_push($photo_list, $file);
        }
    }
    //var_dump($photo_list);
    $photo_count = count($photo_list);
    $photo_num = mt_rand(0, $photo_count-1);

    $random_image_HTML = '<img src="'. $pics_dir . $photo_list[$photo_num]
            .'" alt="Juhuslik pilt Haapsalust" />'. "\n";
?>
<!DOCTYPE html>
<html lang="et">
<head>
	<meta charset="utf-8">
	<title>Veebirakendused ja nende loomine 2020</title>
    <style>
body {max-width:980px; margin: 3vw auto}
    </style>
</head>
<body>
	<h1><?= $my_name ?></h1>
    <p>See leht on valminud õppetöö raames!</p>
    <?= $time_HTML ?>
    <?= $part_of_day_HTML ?>
    <?= $semester_duration_HTML ?>
    <?= $random_image_HTML ?>
</body>
</html>