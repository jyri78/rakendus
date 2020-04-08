<?php
//require "fnc_users.php";
require "../inc/fnc.php";

//var_dump($_SESSION);

// Lubab lehte kuvada ainult sisseloginul
if (!isset($_SESSION["userid"])) {
    // jõuga avalehele
    $_SESSION['errPage'] = $_self;
    $_SESSION['error'] = 'Lehe "home.php" kuvamiseks logi esmalt sisse.';
    header("Location: page.php");
    exit;
}

/* =========================================================================
    Lehe päis
   =========================================================================
*/
$_page = array(
    'title' => 'Loeng 4',
    'h1' => 'Meie äge koduleht',
    'current' => $_self,
    'pages' => [['page.php', 'Loengu leht'], ['home.php', 'Äge leht'],
                ['photoUpload.php', 'Fotode üleslaadimine'], ['gallery.php', 'Fotogalerii']]
);

require '../inc/_header.inc';
?>

        <p>Tere, <?= $_SESSION["userFirstName"] .' '. $_SESSION["userLastName"] ?>!</p>
        <p>See leht on valminud õppetöö raames!</p>
        <hr>
        <h2>Meie süsteemis leiad veel</h2>
        <p>Eelmise, 3. loengu lehed:</p>
        <ul>
            <li><a href="../loeng3/addnews.php" target="_blank">Uudiste lisamise</a></li>
            <li><a href="../loeng3/news.php" target="_blank">Uudiste lugemine</a></li>
            <li><a href="../loeng3/addstudy.php" target="_blank">Õppimise lisamine</a></li>
            <li><a href="../loeng3/studylog.php" target="_blank">Õppimise logi</a></li>
        </ul>
        <hr>
        <p><a href="page.php?logout=1">Logi välja</a></p>

<?php
require '../inc/_footer.inc';
