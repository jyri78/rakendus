<?php
/*
    Postituse kustutamisel kasutan AJAX tehnoloogiat.
*/
$num = random_int(101, 999);
require "fnc_news.php";


if (isset($_POST["delNews"])) {
    deleteNews(filter_input(INPUT_POST, "delId", FILTER_SANITIZE_NUMBER_INT));
    exit;
}

$newsHTML = readNews();
?>
<!DOCTYPE html>
<html lang="et">
<head>
	<meta charset="utf-8">
    <title>Teine loeng | Veebirakendused ja nende loomine 2020</title>
    <link rel="stylesheet"
            href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css">
    <style>
body {max-width:1024px; margin:auto}
.newsItem {border:1px solid #ccc; border-radius:5px; margin:10px 0; padding:8px 16px}
.closeBtn,.closeBtn:hover {text-decoration:none; border-radius:5px}
.closeBtn {border:1px solid white; float:right; text-decoration:none; margin-top:-8px; margin-right:-16px}
.closeBtn:hover {background-color:#fcc;border-color:#f77}
.date {font-size:.9em; color:#9a9; margin-top:-10px}
    </style>
    <script>
var i,$ = function(i) {return document.getElementById(i);},
post = function (id) {
    var xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            if (this.responseText == "OK!") {var o = $(i); o.parentNode.removeChild(o);}
            else alert("Uudise kustutamisega tekkis viga!");
        }
    };
    xhr.open("POST", "news.php", true);
    xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xhr.send("delNews&delId="+id);
},
deleteNews = function (obj,title) {
    i = obj.getAttribute('data-id');
    if (i.substring(0,3) != <?= $num ?>) return false;
    if (confirm('Kas oled kindel, et soovid uudise "'+ title +'" kustutada')) post(i.substring(3));
    return false;
};
    </script>
</head>
<body>
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
        <h1>Uudised</h1>
<?= $newsHTML ?>


    </div>
</body>
</html>