<?php

/* Lehe päis
*/
$_page = array(
    'title' => 'Loeng 3',
    'h1' => 'Andmebaasid 2 - Kolmas loeng',
    'current' => './',
    'pages' => [['page.php', 'Loengu leht'], ['home.php', 'Äge leht'], ['newuser.php', 'Lisa kasutaja']]
);

require '../inc/_header.inc';
?>

	<p>See leht on valminud õppetöö raames!</p>
    <div class="card p-3 my-5">
        <div class="input-group my-2">
            <div class="input-group-prepend">
                <label class="input-group-text"><b>Loengus tehtud asjad</b></label>
            </div>
            <a class="form-control btn btn-outline-primary" role="button" href="page.php">Leht (sisselogimine)</a>
            <a class="form-control btn btn-outline-primary" role="button" href="home.php">Äge leht (ainult kasutaja)</a>
            <a class="form-control btn btn-outline-primary" role="button" href="newuser.php">Konto loomine</a>
        </div>
        <div class="input-group my-2">
            <div class="input-group-prepend">
                <label class="input-group-text"><b>Eelmise loengu asjad</b></label>
            </div>
            <a class="form-control btn btn-outline-primary" role="button" href="addnews.php">Lisa uudis</a>
            <a class="form-control btn btn-outline-primary" role="button" href="news.php">Loe uudised</a>
        </div>
        <div class="input-group my-2">
            <div class="input-group-prepend">
                <label class="input-group-text"><b>Iseseisvad tööd</b></label>
            </div>
            <a class="form-control btn btn-outline-primary" role="button" href="addstudy.php">Lisa õppimine</a>
            <a class="form-control btn btn-outline-primary" role="button" href="studylog.php">Õppimise logi</a>
            <a class="form-control btn btn-outline-primary" role="button" href="courses_activities.php">Kursused ja tegevused</a>
        </div>
    </div>
<?php
require '../inc/_footer.inc' ;
