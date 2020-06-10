<?php
require "../inc/fnc.php";
//require "fnc_photos.php";
require "../inc/Photo.class.php";


// Lubab lehte kuvada ainult sisseloginul
if (!isset($_SESSION["userid"])) {
    // jõuga avalehele
    $_SESSION['errPage'] = $_self;
    $_SESSION['error'] = 'Fotode üleslaadimiseks logi esmalt sisse.';
    header("Location: page.php");
    exit;
}

$error = null;
$success = null;


if (isset($_POST['photoSubmit']) && !empty($_FILES['fileToUpload']['tmp_name'])) {
    $addWM = !empty($_POST['addwm']);

    // Kui privaatsuseks määratud "privaatne" või "avalik", siis eirab seadet ja kirjutab üle
    if ($_POST['privacy'] == 3) $addWM = false;
    if ($_POST['privacy'] == 1) $addWM = true;

    $photoUp = new Photo($_FILES['fileToUpload'], $addWM);

    if (!$photoUp->getImageFileType()) {
        if ($photoUp->getImageFileType() === false)
            $error = 'Ainult JPG ja PNG pildid on lubatud!';
        else
            $error = "Valitud fail ei ole pilt!";
    }

    if ($photoUp->getImageSize() > $fileUploadSizeLimit) {
        $error .= ' Valitud fail on liiga suur';
    }

    // Kui vigu pole
    if (!$error) {
        if ($photoUp->createAndSaveNormalPhoto() == 1) {
            $success = 'Vähendatud pilt laeti üles.';
        } else {
            $error = 'Vähendatud pildi salvestamisel tekkis viga!';
        }

        if (move_uploaded_file($_FILES['fileToUpload']['tmp_name'], $photoUp->getOriginalTarget())) {
            $success .= ' Originaalpilt laeti üles.';
        } else {
            $error .= ' Originaalpildi laadimisel tekkis viga!';
        }

        // Salvestab nüüd andmebaasi
        $altText = test_input($_POST['altText'] ?? 'PILT');
        $privacy = test_input($_POST['privacy'] ?? 3);

        $save = $photoUp->saveImageData($altText, $privacy);

        if ($save == 'OK') {
            $success .= ' Pilt andmebaasi salvestatud.';
        } else {
            $error .= ' Andmebaasi salvestamine ebaõnnestus: '. $save;
        }

        //imagedestroy($myNewImage);
        unset($photoUp);
    }

    if ($error) $_SESSION['error'] = $error;
    if ($success) $_SESSION['notice'] = $success;

    header("Location: ". $_self);
    exit;
}


/* =========================================================================
    Lehe päis
   =========================================================================
*/
$_page = array(
    'title' => 'Loeng 4',
    'inc-js' => 1,
    'script' => "
$(function() {
    $('.custom-file-input').on('change', function() {
        var fileName = $(this).val().split(\"\\\\\").pop();
        $(this).siblings('.custom-file-label').addClass('selected').html(fileName);
    });
});
",
    'h1' => 'Fotode üleslaadimine',
    'current' => $_self,
    'pages' => [['page.php', 'Loengu leht'], ['photoUpload.php', 'Fotode üleslaadimine'], ['gallery.php', 'Fotogalerii']]
);

require '../inc/_header.inc';
?>

    <script>var $wm=true</script>
    <form method="post" enctype="multipart/form-data" class="was-validated mt-5" action="<?= $_self ?>">
        <div class="custom-file">
            <input type="file" name="fileToUpload" class="custom-file-input" id="fileToUpload" required>
            <label class="custom-file-label" for="fileToUpload">Vali pildifail</label>
        </div>
        <div class="input-group my-3">
            <div class="input-group-prepend">
                <span class="input-group-text">Alt tekst:</span>
            </div>
            <input type="text" name="altText" class="form-control" id="altText" required>
        </div>
        <div class="input-group my-3">
            <div class="input-group-prepend">
                <span class="input-group-text">Privaatsus:</span>
            </div>
            <div class="form-control col-sm-9">
                <div class="custom-control custom-radio custom-control-inline">
                    <input type="radio" class="custom-control-input" id="priv1" name="privacy" value="3"
                            onclick="$('#addwm').prop('disabled',true).prop('checked',false)" checked>
                    <label class="custom-control-label" for="priv1">privaatne</label>
                </div>
                <div class="custom-control custom-radio custom-control-inline">
                    <input type="radio" class="custom-control-input" id="priv2" name="privacy" value="2"
                            onclick="$('#addwm').prop('disabled',false).prop('checked',$wm)">
                    <label class="custom-control-label" for="priv2">sisseloginud kasutajatele</label>
                </div>
                <div class="custom-control custom-radio custom-control-inline">
                    <input type="radio" class="custom-control-input" id="priv3" name="privacy" value="1"
                            onclick="$('#addwm').prop('disabled',true).prop('checked',true)">
                    <label class="custom-control-label" for="priv3">avalik</label>
                </div>
            </div>
            <div class="form-control col-sm-3">
                <div class="custom-control custom-switch">
                    <input type="checkbox" class="custom-control-input" id="addwm" name="addwm"
                            onclick="$wm = $(this).is(':checked')" disabled>
                    <label class="custom-control-label" for="addwm">Vesimärk</label>
                </div>
            </div>
        </div>
        <div style="float:right"><a href="page.php?logout=1" class="form-control">Logi välja</a></div>
        <input type="submit" class="btn btn-primary mb-3" name="photoSubmit" value="Lae valitud pilt üles!">
    </form><?php

if (isset($_SESSION["error"])):
?>

    <div class="alert alert-danger">
        <strong>Viga!</strong> <?= $_SESSION["error"] ?> 
    </div>
<?php
endif;
if (isset($_SESSION["notice"])):
?>

    <div class="alert alert-success">
        <strong>Edu!</strong> <?= $_SESSION["notice"] ?> 
    </div>
<?php
endif;    

require '../inc/_footer.inc';

// Nüüd võib sessiooni andmed kustutada
unset($_SESSION['error']);
unset($_SESSION['notice']);
