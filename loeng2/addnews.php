<?php
require "fnc_news.php";
$_self = filter_var($_SERVER["PHP_SELF"], FILTER_SANITIZE_URL);


// Sessioon võimaldab seadeid "meelde jätta" ehk toimib sarnaselt brauseri küpsistele,
// kuid erinevalt küpsistele salvestatakse andmed serverisse ehk teave internetti ei jõua
session_start();

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
?>
<!DOCTYPE html>
<html lang="et">
<head>
	<meta charset="utf-8">
    <title>Teine loeng | Veebirakendused ja nende loomine 2020</title>
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
            <li class="nav-item"><a class="nav-link disabled" href="#">Lisa uudis</a></li>
            <li class="nav-item"><a class="nav-link" href="news.php">Uudised</a></li>
        </ul>
    </nav>
    <h1>Uudiste lisamine</h1>
    
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
    </div><?php
endif;    
?>

</div>
</body>
</html><?php
// Nüüd võib sessiooni andmed kustutada
session_unset();
session_write_close();
