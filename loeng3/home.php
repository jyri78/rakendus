<?php
//require "fnc_users.php";
require "../inc/fnc.php";

// Sessiooni käivitamine või kasutamine
//session_start();
//require("classes/Session.class.php");
//SessionManager::sessionStart(SESSION_NAME, 0, SESSION_PATH, SESSION_DOMAIN);

//var_dump($_SESSION);

// Lubab lehte kuvada ainult sisseloginul
if (!isset($_SESSION["userid"])) {
    // jõuga avalehele
    $_SESSION['errPage'] = 'home.php';
    $_SESSION['error'] = 'Lehe "home.php" kuvamiseks logi esmalt sisse.';
    header("Location: page.php");
    exit;
}

// Logib välja
if (isset($_GET["logout"])) {
    session_destroy();
    header("Location: page.php");
}

/* =========================================================================
    Lehe päis
   =========================================================================
*/
$_page = array(
    'title' => 'Loeng 3',
    'h1' => 'Meie äge koduleht',
    'current' => 'home.php',
    'pages' => [['page.php', 'Loengu leht'], ['home.php', 'Äge leht']/*, ['newuser.php', 'Lisa kasutaja']*/]
);

require '../inc/_header.inc';
?>

        <p>Tere, <?= $_SESSION["userFirstName"] .' '. $_SESSION["userLastName"] ?>!</p>
        <p>See leht on valminud õppetöö raames!</p>
        <hr>
        <p><a href="?logout=1">Logi välja</a></p>

<?php
require '../inc/_footer.inc';
