<?php
require "fnc_study.php";
$_self = filter_var($_SERVER["PHP_SELF"], FILTER_SANITIZE_URL);


session_start();


if (isset($_POST['studyBtn'])) {
    // Kontroll, kuigi viga ei tohiks tekkida...
    // kui üks või mitu välja täitmata, siis salvestab veateate sessiooni
    // alates PHP 7 on toetatud 'Null Coalesce Operator', mis teeb kontrolli lihtsamaks
    $course = test_input($_POST["course"] ?? '');
    $activity = test_input($_POST["activity"] ?? '');
    $time = test_input($_POST["time"] ?? '');

    if (!empty($course)) $_SESSION["course"] = $course;
    else $_SESSION["studyError"] = "Kursus valimata!";

    if (!empty($activity)) $_SESSION["activity"] = $activity;
    else $_SESSION["studyError"] .= " Tegevus valimata!";

    if (!empty($time)) $_SESSION["time"] = $time;
    else $_SESSION["studyError"] .= " Kulunud aeg sisestamata!";

    // Kui veateade määratud, siis salvestab ka veatüübi (Bootstrap'i alert-tüübi)
    if (isset($_SESSION["newsError"])) {
        $_SESSION["alert"] = "danger";
        $_SESSION["alert-title"] = "Viga";
    } else {
        //echo "Salvestame!";
        $response = saveStudy($_SESSION["course"], $_SESSION["activity"], $_SESSION["time"]);

        if ($response[0] == 1) {
            $_SESSION["alert"] = "success";
            $_SESSION["alert-title"] = "Edu";
            $_SESSION["newsError"] = "Õppetegevus on salvestatud.";
        } else {
            $_SESSION["alert"] = "warning";
            $_SESSION["alert-title"] = "Hoiatus";
            $_SESSION["newsError"] = "Õppetegevuse salvestamisel tekkis tõrge: ". $response[1];
        }
    }

    session_write_close();
    header("Location:". $_self);
    exit;
}


$courses = readCourses();
$activities = readActivities();
?>
<!DOCTYPE html>
<html lang="et">
<head>
	<meta charset="utf-8">
    <title>Õppimise lisamine | VR20 teine loeng</title>
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
            <li class="nav-item"><a class="nav-link disabled" href="#">Lisa õppimine</a></li>
            <li class="nav-item"><a class="nav-link" href="studylog.php">Õppimise logi</a></li>
            <li class="nav-item"><a class="nav-link" href="courses_activities.php">Kursused ja tegevused</a></li>
        </ul>
    </nav>
    <h1>Lisa õppimine</h1>
    <form method="post" action="<?= $_self ?>" class="was-validated my-3">
        <div class="form-row">
            <div class="col">
                <select class="custom-select" name="course" id="course" required>

<option value="" disabled selected>Vali kursus</option>
<?= $courses ?>

                </select>
            </div>
            <div class="col">
                <select class="custom-select" name="activity" id="activity" required>

<option value="" disabled selected>Vali tegevus</option>
<?= $activities ?>

                </select>
            </div>
            <div class="col">
                <input type="number" min=".25" max="16" step=".25" name="time" placeholder="Aeg" required>
            </div>
        </div>
        <div class="form-row my-4">
            <div class="col text-right">
                <input type="submit" class="btn btn-primary" name="studyBtn" value="Salvesta õppimine!">
            </div>
        </div>
        <!--div class="form-group">
            <label for="newsEditor">Uudise sisu:</label>
            <textarea name="newsEditor" id="newsEditor" class="form-control"
                    placeholder="Sisesta uudis" rows="6" cols="40" required><?= $newsContent
                    ?></textarea>
            <div class="valid-feedback">Korras.</div>
            <div class="invalid-feedback">Palun sisesta uudise sisu.</div>
        </div>
        <input type="submit" class="btn btn-primary" name="newsBtn" value="Salvesta uudis!"-->
    </form><?php
if (isset($_SESSION["alert"])):
?>

    <div class="alert alert-<?= $_SESSION["alert"] ?>">
        <strong><?= $_SESSION["alert-title"] ?>!</strong> <?= $_SESSION["newsError"] ?> 
    </div><?php
endif;    
?>

</div>
</body>
</html><?php
// Nüüd võib sessiooni andmed kustutada
session_unset();
session_write_close();
