<?php
if (!isset($_page)) {
    header("Location: ../");
}
?>
<!DOCTYPE html>
<html lang="et">
<head>
	<meta charset="utf-8">
    <title><?= $_page['title'] ?> | Veebirakendused ja nende loomine 2020</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css"><?php
if (isset($_page['inc-js'])):
?>


<script src="https://code.jquery.com/jquery-3.4.1.slim.min.js" integrity="sha384-J6qa4849blE2+poT4WnyKhv5vZF5SrPo0iEjwBvKU7imGFAV0wwj1yYfoRSJoZ+n" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>
<?php
endif;

if (isset($_page['style'])) {
    echo "\n    <style>". $_page['style'] ."    </style>";
}

if (isset($_page['script'])) {
    echo "\n    <script>". $_page['script'] ."    </script>";
}
?>

</head>
<body>
<div class="container">
    <nav class="navbar navbar-expand-sm bg-light navbar-light justify-content-center border rounded-lg mb-5">
        <ul class="navbar-nav">

<li class="nav-item"><a class="nav-link" href="/~juri.kormik/" data-toggle="tooltip" title="Pealehele">🏠</a></li>
<li class="nav-item pr-3 mr-3 border-right"><a class="nav-link<?= $_page['current']=='./'
        ? ' disabled border rounded-lg" href="#' : '" href="./' ?>">Index</a></li>
<?php
foreach ($_page['pages'] as $pg):
?>
<li class="nav-item"><a class="nav-link<?= $pg[0] == $_page['current']
        ? ' disabled mx-2 border rounded-lg" href="#' : '" href="'. $pg[0] ?>"><?= $pg[1] ?></a></li>
<?php
endforeach;
?>

        </ul>
    </nav><?php
if ($_page['h1']) {
    echo "\n    <h1>". $_page['h1'] .'</h1>';
}
