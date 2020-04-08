<?php
require "../inc/fnc.php";
require "fnc_study.php";


// Lubab lehte kuvada ainult sisseloginul
if (!isset($_SESSION["userid"])) {
    // jõuga avalehele
    $_SESSION['errPage'] = 'studylog.php';
    $_SESSION['error'] = 'Õppimise logi lehele minekuks logi esmalt sisse.';
    header("Location: page.php");
    exit;
}


$studylog = readStudy();


/* =========================================================================
    Lehe päis
   =========================================================================
*/
$_page = array(
    'title' => 'Loeng 3',
    'h1' => 'Õppimise logi',
    'current' => 'studylog.php',
    'pages' => [['page.php', 'Loengu leht'], ['addstudy.php', 'Lisa õppimine'],
                ['studylog.php', 'Õppimise logi'], ['courses_activities.php', 'Kursused ja tegevused']]
);

require '../inc/_header.inc';
?>

    <table class="table table-striped table-hover my-5">
        <thead>
            <tr>
                <th>Kuupäev</th><th>Õppeaine</th><th>Tegevus</th><th>Aeg</th>
            </tr>
        </thead>
        <tbody>
<?= $studylog ?>

        </tbody>
    </table>

<?php
require '../inc/_footer.inc';
