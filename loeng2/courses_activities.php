<?php
require "fnc_study.php";
$_self = filter_var($_SERVER["PHP_SELF"], FILTER_SANITIZE_URL);


session_start();

// Kui lisatakse uus kursus/tegevus...
// Kuna korduvad tegevused, kasutan FOREACH abi
$_ca = [
    ['course', 'c', 'Kursus'],
    ['activity', 'a', 'Tegevus']
];

foreach ($_ca as $i) {
    if (isset($_POST[$i[0] .'Btn'])) {
        // Kontroll...
        // kui üks või mitu välja täitmata, siis salvestab veateate sessiooni
        // alates PHP 7 on toetatud 'Null Coalesce Operator', mis teeb kontrolli lihtsamaks
        $ca = test_input($_POST[$i[0]] ?? '');

        // Kui sisestusväli tühi, siis salvestab vea ja veatüübi (Bootstrap'i alert-tüübi)
        if (empty($ca)) {
            $_SESSION['alert-'. $i[1]] = "danger";
            $_SESSION["alert-title"] = "Viga";
            $_SESSION[$i[0] .'Error'] = $i[2] .' on sisestamata!';
        } else {
            //echo "Salvestame!";
            $response = $i[1]=='c' ? saveCourse($ca) : saveActivity($ca);

            if ($response[0] == 1) {
                $_SESSION['alert-'. $i[1]] = "success";
                $_SESSION["alert-title"] = "Edu";
                $_SESSION[$i[0] .'Error'] = $i[2] .' on salvestatud.';
            } else {
                $_SESSION['alert-'. $i[1]] = "warning";
                $_SESSION["alert-title"] = "Hoiatus";
                $_SESSION[$i[0] .'Error'] = $i[2] .'e salvestamisel tekkis tõrge: '. $response[1];
            }
        }

        session_write_close();
        header("Location:". $_self);
        exit;
    }
}

$courses = readCourses('li');
$activities = readActivities('li');
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
            <li class="nav-item"><a class="nav-link" href="studylog.php">Õppimise logi</a></li>
            <li class="nav-item"><a class="nav-link disabled" href="#">Kursused ja tegevused</a></li>
        </ul>
    </nav>
    <h1>Kursused ja tegevused</h1>
    <div class="row">
        <div class="col border rounded-lg m-3 py-3 px-0">
            <div class="container">
                <h2>Kursused</h2>
                <form method="post" action="<?= $_self ?>" class="was-validated">
                    <div class="input-group my-3">
                        <input type="text" name="course" class="form-control" id="course"
                                placeholder="Sisesta uue kursuse nimi" required>
                        <div class="input-group-append">
                            <input type="submit" class="btn btn-primary" name="courseBtn" value="Lisa">
                        </div>
                    </div>
                </form><?php
if (isset($_SESSION["alert-c"])):
?>

                <div class="alert alert-<?= $_SESSION["alert-c"] ?>">
                    <strong><?= $_SESSION["alert-title"] ?>!</strong> <?= $_SESSION["courseError"] ?> 
                </div><?php
endif;    
?>

            </div>
            <div class="container">
                <ul class="list-group">

<?= $courses ?>

                </ul>
            </div>
        </div>
        <div class="col border rounded-lg m-3 py-3 px-0">
            <div class="container">
                <h2>Tegevused</h2>
                <form method="post" action="<?= $_self ?>" class="was-validated">
                    <div class="input-group my-3">
                        <input type="text" name="activity" class="form-control" id="activity"
                                placeholder="Sisesta uue tegevuse nimi" required>
                        <div class="input-group-append">
                            <input type="submit" class="btn btn-primary" name="activityBtn" value="Lisa">
                        </div>
                    </div>
                </form><?php
if (isset($_SESSION["alert-a"])):
?>

                <div class="alert alert-<?= $_SESSION["alert-a"] ?>">
                    <strong><?= $_SESSION["alert-title"] ?>!</strong> <?= $_SESSION["activityError"] ?> 
                </div><?php
endif;    
?>

            </div>
            <div class="container">
                <ul class="list-group">

<?= $activities ?>

                </ul>
            </div>
        </div>
    </div>
</div>
</body>
</html><?php
// Nüüd võib sessiooni andmed kustutada
session_unset();
session_write_close();
