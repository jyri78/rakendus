<?php
/*
    Uudise lisamise lehel kasutan PHP sessioone (et vältida dopeltpostitus).
*/
require "fnc_news.php";


session_start();

if (isset($_POST["newsBtn"])) {
    if (isset($_POST["newsTitle"]) and !empty(test_input($_POST["newsTitle"]))) {
        $_SESSION["newsTitle"] = test_input($_POST["newsTitle"]);
    } else {
        $_SESSION["newsError"] = "Uudise pealkiri on sisestamata!";
    }
    if (isset($_POST["newsEditor"]) and !empty(test_input($_POST["newsEditor"]))) {
        $_SESSION["newsContent"] = test_input($_POST["newsEditor"]);
    } else {
        $_SESSION["newsError"] .= " Uudise sisu on kirjutamata!";
    }

    if (isset($_SESSION["newsError"])) {
        $_SESSION["alert"] = "danger";
        $_SESSION["alert-title"] = "Viga";
    }

    //echo $newsTitle;
    //echo $newsContent;

    if (empty($_SESSION["newsError"])) {
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
    header("Location: addnews.php");
    exit;
}
elseif (isset($_SESSION["alert"]) && $_SESSION["alert"] == "success") {
    // Uudis salvestatud, seega ei täida enam
    unset($_SESSION["newsTitle"]);
    unset($_SESSION["newsContent"]);
}
?>
<!DOCTYPE html>
<html lang="et">
<head>
	<meta charset="utf-8">
    <title>Teine loeng | Veebirakendused ja nende loomine 2020</title>
    <link rel="stylesheet"
            href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css">
    <style>
body {max-width:1024px; margin:auto;}
    </style>
</head>
<body>
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
    
    <form method="post" action="<?= htmlspecialchars($_SERVER["PHP_SELF"]) ?>" class="was-validated">
        <div class="form-group">
            <label for="newsTitle">Uudise pealkiri:</label>
            <input type="text" name="newsTitle" class="form-control" id="newsTitle"
                    placeholder="Sisesta uudise pealkiri" value="<?= 
                    isset($_SESSION["newsTitle"]) ? $_SESSION["newsTitle"] : '' ?>" required>
            <div class="valid-feedback">Korras.</div>
            <div class="invalid-feedback">Palun sisesta uudise pealkiri.</div>
        </div>
        <div class="form-group">
            <label for="newsEditor">Uudise sisu:</label>
            <textarea name="newsEditor" id="newsEditor" class="form-control"
                    placeholder="Sisesta uudis" rows="6" cols="40" required><?= 
                    isset($_SESSION["newsContent"]) ? $_SESSION["newsContent"] : '' ?></textarea>
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

</body>
</html><?php
// Nüüd võib sessiooni andmed kustutada
session_unset();
session_write_close();
