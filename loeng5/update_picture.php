<?php
require "../inc/fnc.php";
//require "fnc_photos.php";
require "../inc/Photo.class.php";


// Kui muudeti pilti (see saab toimuda kasutaja nime all)
if (isset($_POST['photoSubmit'])) {
    $uid = $_SESSION['userid'] ?? 0;
    $ids = explode('-', base64_decode(test_input($_POST['ids'] ?? '')));

    // Kontrollib igaks juhuks, kas ikka õige kasutaja
    if ($uid == $ids[0]) {
        $altText = test_input($_POST['altText'] ?? 'PILT');
        $privacy = test_input($_POST['privacy'] ?? 3);
        $addWM = !empty($_POST['addwm']);

        if ($privacy == 3) $addWM = false;
        if ($privacy == 1) $addWM = true;

        Photo::changeImageData($ids[1], $altText, $privacy, $addWM, $ids[2]);  // ei tohiks viga tekkida
    }

    // Suunab galerii lehele tagasi 
    header("Location: gallery.php");
    exit;
}

// Kui kasutaja ei ole sisse loginud või pildi ID määramata, suunab galerii lehele
if (!isset($_SESSION["userid"]) || !isset($_GET['id'])) {
    header("Location: gallery.php");
    exit;
}

// Loeb sisse vajaliku pildiinfo
$imageData = Photo::getImageData(filter_input(INPUT_GET, "id", FILTER_SANITIZE_NUMBER_INT));

// Kui pilti ei ole või pole kasutaja lisatud, suunab galerii lehele
if (!$imageData) {
    header("Location: gallery.php");
    exit;
}



/* =========================================================================
    Lehe päis
   =========================================================================
*/
$_page = array(
    'title' => 'Loeng 5',
    'inc-js' => 1,
    'h1' => 'Pildi privaatsussätete muutmine',
    'current' => $_self,
    'pages' => [['page.php', 'Loengu leht'], ['photoUpload.php', 'Fotode üleslaadimine'], [$_self, 'Fotogalerii']]
);

require '../inc/_header.inc';
?>

    <script>var $wm=<?= (!$imageData['addwm'] ? 'false' : 'true') ?></script>
    <form method="post" enctype="multipart/form-data" class="was-validated mt-4" action="<?= $_self ?>">
        <div class="row">
            <div class="col-md-4">
                <div class="my-3">
                    <img class="img-thumbnail rounded-lg" src="<?= $imageData['thumb'] ?>">
                </div>
            </div>
            <div class="col-md-8">
                <div class="input-group my-3">
                    <div class="input-group-prepend">
                        <span class="input-group-text">Alt tekst:</span>
                    </div>
                    <input type="text" name="altText" class="form-control" value="<?=
                            $imageData['alttext'] ?>" id="altText" required>
                </div>
                <div class="input-group my-3">
                    <div class="input-group-prepend">
                        <span class="input-group-text">Privaatsus:</span>
                    </div>
                    <div class="form-control col-sm-9">
                        <div class="custom-control custom-radio custom-control-inline">
                            <input type="radio" class="custom-control-input" id="priv1" name="privacy" value="3"
                                    onclick="$('#addwm').prop('disabled',true).prop('checked',false)"<?=
                                    $imageData['privacy']==3 ? ' checked' : '' ?>>
                            <label class="custom-control-label" for="priv1">privaatne</label>
                        </div>
                        <div class="custom-control custom-radio custom-control-inline">
                            <input type="radio" class="custom-control-input" id="priv2" name="privacy" value="2"
                                    onclick="$('#addwm').prop('disabled',false).prop('checked',$wm)"<?=
                                    $imageData['privacy']==2 ? ' checked' : '' ?>>
                            <label class="custom-control-label" for="priv2">sisselog. kasut.</label>
                        </div>
                        <div class="custom-control custom-radio custom-control-inline">
                            <input type="radio" class="custom-control-input" id="priv3" name="privacy" value="1"
                                    onclick="$('#addwm').prop('disabled',true).prop('checked',true)"<?=
                                    $imageData['privacy']==1 ? ' checked' : '' ?>>
                            <label class="custom-control-label" for="priv3">avalik</label>
                        </div>
                    </div>
                    <div class="form-control col-sm-3">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="addwm" name="addwm"
                                    onclick="$wm = $(this).is(':checked')"<?=
                                    $imageData['addwm'] ? ' checked' : '' ?><?=
                                    $imageData['privacy']==1 || $imageData['privacy']==3 ? ' disabled' : '' ?>>
                            <label class="custom-control-label" for="addwm">Vesimärk</label>
                        </div>
                    </div>
                </div>
                <input type="hidden" name="ids" value="<?=
                        base64_encode($imageData['userid'] .'-'. $imageData['id'] .'-'. $imageData['filename']) ?>">
                <div style="float:right"><a href="gallery.php" class="btn btn-danger">Loobu</a></div>
                <input type="submit" class="btn btn-primary mb-3" name="photoSubmit" value="Muuda sätted">
            </div>
        </div>
    </form>
<?php
require '../inc/_footer.inc' ;

