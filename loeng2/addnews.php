<?php
require "fnc_news.php";


$newsTitle = null;
$newsContent = null;
$newsError = null;

if (isset($_POST["newsBtn"])) {
    if (isset($_POST["newsTitle"]) and !empty(test_input($_POST["newsTitle"]))) {
        $newsTitle = test_input($_POST["newsTitle"]);
    } else {
        $newsError = "Uudise pealkiri on sisestamata!";
    }
    if (isset($_POST["newsEditor"]) and !empty(test_input($_POST["newsEditor"]))) {
        $newsContent = test_input($_POST["newsEditor"]);
    } else {
        $newsError .= "<br>Uudise sisu on kirjutamata!";
    }

    //echo $newsTitle;
    //echo $newsContent;

    if (empty($newsError)) {
        //echo "Salvestame!";
        $response = saveNews($newsTitle, $newsContent);

        if ($response == 1) {
            $newsError = "Uudis on salvestatud!";
        } else {
            $newsError = "Uudise salvestamisel tekkis tõrge!";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="et">
<head>
	<meta charset="utf-8">
    <title>Teine loeng | Veebirakendused ja nende loomine 2020</title>
    <style>
body {max-width:1024px; margin:3vw auto;}
    </style>
</head>
<body>
    <h1>Uudise lisamine | Andmebaasid - Teine loeng</h1>
    
	<form method="post" action="<?= htmlspecialchars($_SERVER["PHP_SELF"]) ?>">
        <label>Uudise pealkiri</label>
        <br><input type="text" name="newsTitle" placeholder="Uudise pealkiri" value="<?= $newsTitle ?>">
        <br>
        <label>Uudise sisu</label>
        <br><textarea name="newsEditor" placeholder="Uudis" rows="6" cols="40"><?= $newsContent ?></textarea>
        <br>
        <input type="submit" name="newsBtn" value="Salvesta uudis!">
    </form>
    <span><?= $newsError ?></span>
</body>
</html>