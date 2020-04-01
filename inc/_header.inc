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
    <link rel="stylesheet"
            href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css"><?php
if (isset($_page['style'])) {
    echo "\n\n    <style>". $_page['style'] ."    </style>\n";
}

if (isset($_page['script'])) {
    echo "\n\n    <script>". $_page['script'] ."    </script>\n";
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
