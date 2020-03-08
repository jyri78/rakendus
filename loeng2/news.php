<?php
require "fnc_news.php";
$_self = filter_var($_SERVER["PHP_SELF"], FILTER_SANITIZE_URL);


// Sessioon võimaldab seadeid "meelde jätta" ehk toimib sarnaselt brauseri küpsistele,
// kuid erinevalt küpsistele salvestatakse andmed serverisse ehk teave internetti ei jõua
session_start();
if (!isset($_SESSION["controlNum"])) $_SESSION["controlNum"] = random_int(101, 999);


// Postituse kustutamisel kasutan AJAX tehnoloogiat.
if (isset($_POST["delNews"])) {
    $n = filter_input(INPUT_POST, "delId", FILTER_SANITIZE_NUMBER_INT);
    $controlNum = substr($n, 0, 3);

    // testib kontrollarvu kehtivust
    if ($controlNum == $_SESSION["controlNum"]) {
        deleteNews(substr($n, 3));
    } else {
        echo 'WRONG_CONTROL_NUMBER!';
    }
    exit;
}

// Kuvatavate uudiste arv
$newsLimits = [1, 3, 5, 8, 10, 15];
$limitNews = $_SESSION["limitNews"] ?? 5;  // kasutaja valik või vaikimisi 5

// Kontrollib, kas on valitud uudiste arv, kui on,
// siis taaslaeb lehe, et postitus "üles rippuma" ei jääks
if (isset($_POST["newsCount"])) {
    $_SESSION["limitNews"] = filter_input(INPUT_POST, "newsCount", FILTER_SANITIZE_NUMBER_INT);
    session_write_close();
    header("Location: ". $_self);
    exit;
}

$newsHTML = readNews($_SESSION["controlNum"], $limitNews);
?>
<!DOCTYPE html>
<html lang="et">
<head>
	<meta charset="utf-8">
    <title>Teine loeng | Veebirakendused ja nende loomine 2020</title>
    <link rel="stylesheet"
            href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css">
    <style>
.newsItem {border:1px solid #ccc; border-radius:5px; margin:10px 0; padding:8px 16px}
.closeBtn,.closeBtn:hover {text-decoration:none; border-radius:5px}
.closeBtn {border:1px solid white; float:right; text-decoration:none; margin-top:-8px; margin-right:-16px}
.closeBtn:hover {background-color:#fcc;border-color:#f77}
.badge {opacity:.3; font-size:.65em}
.date {font-size:.9em; color:#9a9; margin-top:-10px}
    </style>
    <script>
var i,$ = function(i) {return document.getElementById(i);},
post = function (id) {
    // Loob päringu objekti
    var xhr = new XMLHttpRequest();

    // Määrab tegevuse päringu sündmustele
    xhr.onreadystatechange = function() {
        // Kui päring teostatud ja server vastas staatusega 200 ehk "OK",
        // alles siis tegeleb vastavalt serveri vastusega edasi
        if (this.readyState == 4 && this.status == 200) {
            if (this.responseText == "OK!") {
                //var o = $(i); o.parentNode.removeChild(o);
 
                // Kuna soovime, et kuvatavate uudiste arv jääks samas,
                // siis laeb lehte uuesti (Node eemaldamise asemel)
                location.reload();
                }
            else alert("Uudise kustutamisega tekkis viga!");
        }
    };

    // Lõpuks teostab postituse (avab, seab päise ja saadab)
    xhr.open("POST", "<?= $_self ?>", true);
    xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xhr.send("delNews&delId="+id);
},
deleteNews = function (obj,title) {
    i = obj.getAttribute('data-id');
    if (i.substring(0,3) != <?= $_SESSION["controlNum"] ?>) return false;
    if (confirm('Kas oled kindel, et soovid uudise "'+ title +'" kustutada')) post(i);
    return false;
};
    </script>
</head>
<body>
<div class="container">
    <nav class="navbar navbar-expand-sm bg-light navbar-light justify-content-center"
            style="margin-bottom:2vw">
        <ul class="navbar-nav">
            <li class="nav-item border-right">
                <a class="nav-link" href="/~juri.kormik/" data-toggle="tooltip" title="Pealehele">🏠</a>
            </li>
            <li class="nav-item"><a class="nav-link" href="./">Tagasi</a></li>
            <li class="nav-item"><a class="nav-link" href="addnews.php">Lisa uudis</a></li>
            <li class="nav-item"><a class="nav-link disabled" href="#">Uudised</a></li>
        </ul>
    </nav>
    <div class="container">
        <form method="post" action="<?= $_self ?>" style="float:right">
            <label for="newsCount">Uudiste arv:</label>
            <select name="newsCount" id="newsCount"><?php
foreach ($newsLimits as $limit):
?>

                <option value="<?= $limit ?>"<?= $limit==$limitNews ? ' selected' : ''
                ?>><?= $limit ?></option><?php
endforeach;
?>

            </select>
            &nbsp;
            <button type="submit" class="btn btn-primary">Vali</button>
        </form>
        <h1>Uudised</h1>
<?= $newsHTML ?>


    </div>
</div>
</body>
</html>