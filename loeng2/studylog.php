<?php
require "fnc_study.php";

$studylog = readStudy();
?>
<!DOCTYPE html>
<html lang="et">
<head>
	<meta charset="utf-8">
    <title>Kursused ja tegevused | VR20 teine loeng</title>
    <link rel="stylesheet"
            href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css">
</head>
<body>
<div class="container">
    <nav class="navbar navbar-expand-sm bg-light navbar-light justify-content-center"
            style="margin-bottom:2vw">
        <ul class="navbar-nav">
            <li class="nav-item border-right"><a class="nav-link" href="/~juri.kormik/"
                    data-toggle="tooltip" title="Pealehele">🏠</a></li>
            <li class="nav-item"><a class="nav-link" href="./">Tagasi</a></li>
            &nbsp;
            <li class="nav-item"><a class="nav-link" href="addstudy.php">Lisa õppimine</a></li>
            <li class="nav-item"><a class="nav-link disabled" href="#">Õppimise logi</a></li>
            <li class="nav-item"><a class="nav-link" href="courses_activities.php">Kursused ja tegevused</a></li>
        </ul>
    </nav>
    <h1>Õppimise logi</h1>
    <table class="table table-striped table-hover my-3">
        <thead>
            <tr>
                <th>Kuupäev</th><th>Õppeaine</th><th>Tegevus</th><th>Aeg</th>
            </tr>
        </thead>
        <tbody>
<?= $studylog ?>

        </tbody>
    </table>
</div>
</body>
</html>