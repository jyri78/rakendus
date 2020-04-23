<?php
require "../inc/fnc.php";
//require "fnc_photos.php";
require "../inc/Photo.class.php";


// Privaatsustase (võrdlemiseks):  2 - avalik,  3 - sisseloginud kasutaja
$privacy = 2;
if (isset($_SESSION["userid"])) $privacy = 3;
else { $_SESSION['errPage'] = $_self; }  // muudab lehe nime, kuhu peale sisselogimist minna


// Pildi kustutamisel kasutan AJAX tehnoloogiat.
if (isset($_POST["delImg"]) && isset($_SESSION["userid"])) {  // lubab kustutada ainult sisseloginud kasutajal
    $id = filter_input(INPUT_POST, "delId", FILTER_SANITIZE_NUMBER_INT);

    // Kui kasutaja on pildi autor, alles siis kustutab selle
    if (Photo::getImageOwner($id) == $_SESSION["userid"]) {
        Photo::deleteImage($id);
    } else {
        echo 'NO_RIGHTS_TO_DELETE';
    }
    exit;
}

$gallery = Photo::getGallery($privacy);
//var_dump(exif_read_data(IMG_ORIGINAL_PHOTO_DIR . 'vr_15866920089002.jpeg'));


/* =========================================================================
    Lehe päis
   =========================================================================
*/
$_page = array(
    'title' => 'Loeng 4',
    'style' => "
.img-card {display:inline-block; width:". ($imageThumbSize + 38) ."px}
",
    'inc-js' => 1,
    'h1' => 'Fotogalerii',
    'current' => $_self,
    'pages' => [['page.php', 'Loengu leht'], ['photoUpload.php', 'Fotode üleslaadimine'], [$_self, 'Fotogalerii']]
);

// JavaScript'i lisab ainult sisseloginud kasutajale, võimaldamaks pildi kustutamist
if (isset($_SESSION['userid'])) $_page['script'] = "
var t,
// Lihtsustatud jQuery's ei ole AJAX funktsioone
post = function (id) {
    // Loob päringu objekti
    var xhr = new XMLHttpRequest();

    // Määrab tegevuse päringu sündmustele
    xhr.onreadystatechange = function() {
        // Kui päring teostatud ja server vastas staatusega 200 ehk \"OK\",
        // alles siis tegeleb vastavalt serveri vastusega edasi
        if (this.readyState == 4 && this.status == 200) {
            if (this.responseText == 'OK!') $('#'+t.data('id')).remove();
            else alert('Pildi kustutamisega tekkis viga: ' + this.responseText);
        }
    };

    // Lõpuks teostab postituse (avab, seab päise ja saadab)
    xhr.open('POST', '". $_self ."', true);
    xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
    xhr.send('delImg&delId='+id);
};
$(function(){
    $('.change').click(function () {
        window.location.replace('update_picture.php?id=' + $(this).data('id'));
    });
    $('.del').click(function () {
        t = $(this);
        if (confirm('Kas oled kindel, et soovid pildi \"'+ t.data('title') +'\" kustutada')) post(t.data('id'));
        return false;
    });
});
";

require '../inc/_header.inc';
?>

    <div class="container my-5 p-3 border rounded-lg">
<?= $gallery[0] ?>


    </div>
<?php
echo $gallery[1];
echo $gallery[2];

require '../inc/_footer.inc' ;
