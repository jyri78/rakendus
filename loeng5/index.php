<?php

/* Lehe päis
*/
$_page = array(
    'title' => 'Loeng 5',
    'h1' => 'Klassid - Viies loeng',
    'current' => './',
    'pages' => [['page.php', 'Loengu leht']]
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
            <!--a class="form-control btn btn-outline-primary" role="button" href="home.php">Äge leht (ainult kasutaja)</a-->
            <a class="form-control btn btn-outline-primary" role="button" href="photoUpload.php">Fotode üleslaadimine</a>
        </div>
        <div class="input-group my-2">
            <div class="input-group-prepend">
                <label class="input-group-text"><b>Iseseisvad tööd</b></label>
            </div>
            <a class="form-control btn btn-outline-primary" role="button" href="gallery.php">Fotogalerii</a>
        </div>
    </div>
<?php
require '../inc/_footer.inc' ;
