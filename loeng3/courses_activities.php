<?php
require "../inc/fnc.php";
require "fnc_study.php";


// Lubab lehte kuvada ainult sisseloginul
if (!isset($_SESSION["userid"])) {
    // jõuga avalehele
    $_SESSION['errPage'] = 'courses_activities.php';
    $_SESSION['error'] = 'Kursuste ja tegevuste lehele minekuks logi esmalt sisse.';
    header("Location: page.php");
    exit;
}


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


/* =========================================================================
    Lehe päis
   =========================================================================
*/
$_page = array(
    'title' => 'Loeng 3',
    'h1' => 'Kursused ja tegevused',
    'current' => 'courses_activities.php',
    'pages' => [['page.php', 'Loengu leht'], ['addstudy.php', 'Lisa õppimine'],
                ['studylog.php', 'Õppimise logi'], ['courses_activities.php', 'Kursused ja tegevused']]
);

require '../inc/_header.inc';
?>

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

<?php
require '../inc/_footer.inc';

// Nüüd võib sessiooni andmed kustutada
/*session_unset();
session_write_close();*/
unset($_SESSION['alert-c']);
unset($_SESSION['alert-a']);
unset($_SESSION['alert-title']);
unset($_SESSION['activityError']);
unset($_SESSION['courseError']);
