<?php
require "../inc/fnc.php";
require "fnc_study.php";


// Lubab lehte kuvada ainult sisseloginul
if (!isset($_SESSION["userid"])) {
    // jõuga avalehele
    $_SESSION['errPage'] = 'addstudy.php';
    $_SESSION['error'] = 'Õppimise lisamise lehele minekuks logi esmalt sisse.';
    header("Location: page.php");
    exit;
}


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
    if (isset($_SESSION["studyError"])) {
        $_SESSION["alert"] = "danger";
        $_SESSION["alert-title"] = "Viga";
    } else {
        //echo "Salvestame!";
        $response = saveStudy($_SESSION["course"], $_SESSION["activity"], $_SESSION["time"]);

        if ($response[0] == 1) {
            $_SESSION["alert"] = "success";
            $_SESSION["alert-title"] = "Edu";
            $_SESSION["studyError"] = "Õppetegevus on salvestatud.";
        } else {
            $_SESSION["alert"] = "warning";
            $_SESSION["alert-title"] = "Hoiatus";
            $_SESSION["studyError"] = "Õppetegevuse salvestamisel tekkis tõrge: ". $response[1];
        }
    }

    session_write_close();
    header("Location:". $_self);
    exit;
}


$courses = readCourses();
$activities = readActivities();


/* =========================================================================
    Lehe päis
   =========================================================================
*/
$_page = array(
    'title' => 'Loeng 3',
    'h1' => 'Lisa õppimine',
    'current' => 'addstudy.php',
    'pages' => [['page.php', 'Loengu leht'], ['addstudy.php', 'Lisa õppimine'],
                ['studylog.php', 'Õppimise logi'], ['courses_activities.php', 'Kursused ja tegevused']]
);

require '../inc/_header.inc';
?>

    <form method="post" action="<?= $_self ?>" class="was-validated my-5">
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
                <input type="number" class="form-control" min=".25" max="16" step=".25" name="time" placeholder="Aeg" required>
            </div>
        <!--/div>
        <div class="form-row my-4"-->
            <div class="col text-right">
                <input type="submit" class="btn btn-primary" name="studyBtn" value="Salvesta õppimine!">
            </div>
        </div>

    </form><?php
if (isset($_SESSION["alert"])):
?>

    <div class="alert alert-<?= $_SESSION["alert"] ?>">
        <strong><?= $_SESSION["alert-title"] ?>!</strong> <?= $_SESSION["studyError"] ?> 
    </div><?php
endif;    
?>

<?php
require '../inc/_footer.inc';

// Nüüd võib sessiooni andmed kustutada
/*session_unset();
session_write_close();*/
unset($_SESSION['course']);
unset($_SESSION['activity']);
unset($_SESSION['time']);
unset($_SESSION['alert']);
unset($_SESSION['alert-title']);
unset($_SESSION['studyError']);
