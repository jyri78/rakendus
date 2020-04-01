<?php
require "../inc/fnc.php";
require "fnc_news.php";

$_self = filter_var($_SERVER["PHP_SELF"], FILTER_SANITIZE_URL);


SessionManager::sessionStart(SESSION_NAME, 0, SESSION_PATH, SESSION_DOMAIN);


// Lubab lehte kuvada ainult sisseloginul
if (!isset($_SESSION["userid"])) {
    // jõuga avalehele
    $_SESSION['errPage'] = 'addnews.php';
    $_SESSION['error'] = 'Uudise lisaseks logi esmalt sisse.';
    header("Location: page.php");
    exit;
}


// Tühistab uudiste lehe kontrollnumbri
unset($_SESSION["controlNum"]);


// Kui lisatakse uus uudis...
if (isset($_POST["newsBtn"])) {
    // Kontroll, kuigi viga ei tohiks tekkida...
    // kui üks või mitu välja täitmata, siis salvestab veateate sessiooni;
    // alates PHP 7 on toetatud 'Null Coalesce Operator', mis teeb kontrolli lihtsamaks
    $newsTitle = test_input($_POST["newsTitle"] ?? '');
    $newsEditor = test_input($_POST["newsEditor"] ?? '');

    if (!empty($newsTitle)) $_SESSION["newsTitle"] = $newsTitle;
    else $_SESSION["newsError"] = "Uudise pealkiri on sisestamata!";

    if (!empty($newsEditor)) $_SESSION["newsContent"] = $newsEditor;
    else $_SESSION["newsError"] .= " Uudise sisu on kirjutamata!";

    // Kui veateade määratud, siis salvestab ka veatüübi (Bootstrap'i alert-tüübi)
    if (isset($_SESSION["newsError"])) {
        $_SESSION["alert"] = "danger";
        $_SESSION["alert-title"] = "Viga";
    } else {
        //echo "Salvestame!";
        $response = saveNews($_SESSION["newsTitle"], $_SESSION["newsContent"]);

        if ($response[0] == 1) {
            $_SESSION["alert"] = "success";
            $_SESSION["alert-title"] = "Edu";
            $_SESSION["newsError"] = "Uudis on salvestatud.";
        } else {
            $_SESSION["alert"] = "warning";
            $_SESSION["alert-title"] = "Hoiatus";
            $_SESSION["newsError"] = "Uudise salvestamisel tekkis tõrge: ". $response[1];
        }
    }

    session_write_close();
    header("Location:". $_self);
    exit;
}
// ... või kui uus uudis salvestatud, kustutab välja info (ei lisa enam)
elseif (isset($_SESSION["alert"]) && $_SESSION["alert"] == "success") {
    // Uudis salvestatud, seega vormi ei täida enam
    unset($_SESSION["newsTitle"]);
    unset($_SESSION["newsContent"]);
}


// Lõpuks HTML vormile lisatav sisu, kui on
$newsTitle = $_SESSION["newsTitle"] ?? '';
$newsContent = $_SESSION["newsContent"] ?? '';


/* =========================================================================
    Lehe päis
   =========================================================================
*/
$_page = array(
    'title' => 'Loeng 3',
    'h1' => 'Uudise lisamine',
    'current' => 'addnews.php',
    'pages' => [['page.php', 'Loengu leht'], ['addnews.php', 'Lisa uudis'], ['news.php', 'Loe uudised']]
);

require '../inc/_header.inc';
?>

    <form method="post" action="<?= $_self ?>" class="was-validated">
        <div class="input-group my-3">
            <div class="input-group-prepend">
                <span class="input-group-text">Uudise pealkiri:</span>
            </div>
            <!--label for="newsTitle">Uudise pealkiri:</label-->
            <input type="text" name="newsTitle" class="form-control" id="newsTitle"
                    placeholder="Sisesta uudise pealkiri" value="<?= $newsTitle ?>" required>
            <div class="valid-feedback">Korras.</div>
            <div class="invalid-feedback">Palun sisesta uudise pealkiri.</div>
        </div>
        <div class="form-group">
            <label for="newsEditor">Uudise sisu:</label>
            <textarea name="newsEditor" id="newsEditor" class="form-control"
                    placeholder="Sisesta uudis" rows="6" cols="40" required><?= $newsContent
                    ?></textarea>
            <div class="valid-feedback">Korras.</div>
            <div class="invalid-feedback">Palun sisesta uudise sisu.</div>
        </div>
        <input type="submit" class="btn btn-primary" name="newsBtn" value="Salvesta uudis!">
    </form><?php
if (isset($_SESSION["alert"])):
?>

    <div class="alert alert-<?= $_SESSION["alert"] ?>">
        <strong><?= $_SESSION["alert-title"] ?>!</strong> <?= $_SESSION["newsError"] ?> 
    </div>
<?php
endif;    

require '../inc/_footer.inc';

// Nüüd võib sessiooni andmed kustutada
/*session_unset();
session_write_close();*/
unset($_SESSION['alert']);
unset($_SESSION['alert-title']);
unset($_SESSION['newsError']);
