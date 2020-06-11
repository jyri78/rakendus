<?php
require "../inc/fnc.php";
require "../inc/Photo.class.php";


// Kui muudetakse piltide arvu lehel
if (isset($_POST["imagesPerPage"])) {
    $_SESSION["imagesPerPage"] = filter_input(INPUT_POST, "imagesPerPage", FILTER_SANITIZE_NUMBER_INT);
    session_write_close();
    header("Location: ". $_self);
    exit;
}


// Privaatsustase (võrdlemiseks):  3 - avalik,  2 - sisseloginud kasutaja
$privacy = 2;
if (isset($_SESSION["userid"])) $privacy = 3;
else { $_SESSION['errPage'] = $_self; }  // muudab lehe nime, kuhu peale sisselogimist minna

// Lehekülgedeks jagamine
$pgLimits = [5, 10, 15, 20, 25];
$pgPage = (filter_input(INPUT_GET, "page", FILTER_SANITIZE_NUMBER_INT) ?? 1) -1;
$pgLimit = $_SESSION['imagesPerPage'] ?? 10;
$pgTotal = ceil(Photo::getTotalImages($privacy)/$pgLimit);


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

// Sarnaselt kustutamisele kasutan pildi hindamisel AJAX tehnoloogiat
if (isset($_POST["rateImg"]) && isset($_SESSION["userid"])) {  // hinnata saavad ainult sisseloginud kasutajad
    $id = filter_input(INPUT_POST, "rateId", FILTER_SANITIZE_NUMBER_INT);
    $own = filter_input(INPUT_POST, "rateOwn", FILTER_SANITIZE_STRING);

    // Kui kasutaja on pildi autor, siis eirab hinnangut
    if ($own == 'y') echo Photo::getImageRatings($id);  // ei tohiks seda juhtuda
    else {
        $val = filter_input(INPUT_POST, "rateVal", FILTER_SANITIZE_NUMBER_INT);
        Photo::rateImage($id, $val);
    }
    exit;
}


$gallery = Photo::getGalleryNew($privacy, $pgPage*$pgLimit, $pgLimit);


/* =========================================================================
    Lehe päis
   =========================================================================
*/
$_page = array(
    'title' => 'Loeng 6',
    'style' => "
h6 {padding:2px}
.img-card {display:inline-block; width:". ($imageThumbSize + 38) ."px}
",
    'script' => "
$(function(){
    $('#photoModal').on('show.bs.modal', function (event) {
        var a = $(event.relatedTarget), m = $(this);
        m.find('.modal-title').text(a.data('origname'));
        m.find('.modal-body img').attr('src', 'picture.php?img='+a.data('filename'));
        m.find('.modal-body h5').html(a.find('img').attr('alt'));
        $('#avgRate').html(a.data('ratings'));
    });
    $('#imagesPerPage').change(function(){
        $(this).parent().submit();
    });
});
",
    'inc-js' => 1,
    'h1' => 'Fotogalerii',
    'current' => $_self,
    'pages' => [['page.php', 'Loengu leht'], ['photoUpload.php', 'Fotode üleslaadimine'], [$_self, 'Fotogalerii']]
);

// Enamus JavaScript'ist on sisseloginud kasutajale, esitab kogu koodi uuesti
if (isset($_SESSION['userid'])) $_page['script'] = "
var t,a,r=[],
// Lihtsustatud jQuery's ei ole AJAX funktsioone
delImg = function () {
    // Loob päringu objekti
    var xhr = new XMLHttpRequest(), id=t.data('id');

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
rateImg = function (id,val,own) {
    // Loob päringu objekti
    var xhr = new XMLHttpRequest();

    // Määrab tegevuse päringu sündmustele
    xhr.onreadystatechange = function() {
        // Kui päring teostatud ja server vastas staatusega 200 ehk \"OK\",
        // alles siis tegeleb vastavalt serveri vastusega edasi
        if (this.readyState == 4 && this.status == 200) {
            $('#avgRate').html(this.responseText);
            a.data('ratings', this.responseText);  // \"jätab meelde\" ka
        }
    };

    // Ei luba teist korda hinnata
    r.push(id);
    $('#btnRate').prop('class', 'btn btn-outline-light btn-block').prop('disabled',true);

    // Lõpuks teostab postituse (avab, seab päise ja saadab)
    xhr.open('POST', '". $_self ."', true);
    xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
    xhr.send('rateImg&rateOwn='+own+'&rateId='+id+'&rateVal='+val);
};
$(function(){
    $('#photoModal').on('show.bs.modal', function (event) {
        var m = $(this);
        a = $(event.relatedTarget);
        $('input[name=\"rate\"]').prop('checked', false);
        $('#btnRate').prop('class', 'btn btn-outline-light btn-block').prop('disabled',true);
        $('#avgRate').html(a.data('ratings'));
        m.find('.modal-title').text(a.data('origname'));
        m.find('.modal-body img').prop('src', 'picture.php?img='+a.data('filename'));
        m.find('.modal-body h5').html(a.find('img').attr('alt'));
    });
    $('input[name=\"rate\"]').click(function(){
        if(a.data('owner')=='n'&&r.indexOf(a.parent().attr('id'))==-1)
            $('#btnRate').prop('class', 'btn btn-primary btn-block').prop('disabled',false);
    });
    $('#btnRate').click(function(){
        rateImg(a.parent().attr('id'), $('input[name=\"rate\"]:checked').val(), a.data('owner'));
    });
    $('.change').click(function(){
        window.location.replace('update_picture.php?id=' + $(this).data('id'));
    });
    $('.del').click(function(){
        t = $(this);
        //if (confirm('Kas oled kindel, et soovid pildi \"'+ t.data('title') +'\" kustutada')) delImg(t.data('id'));
        var fClose = function(){ m.modal('hide'); };
        var m = $('#confirmModal');
        m.modal({backdrop: 'static'});
        $('#imagename').html(t.data('title'));
        $('#confirmYes').unbind().one('click', delImg).one('click', fClose);
        $('#confirmNo').unbind().one('click', fClose);
        return false;
    });
    $('#imagesPerPage').change(function(){
        $(this).parent().submit();
    });
});
";

require '../inc/_header.inc';
?>

    <div id="gallery" class="container mt-5 mb-3 p-3 border rounded-lg">
<?= $gallery[0] ?>


    </div>
    <div class="row">
        <div class="col-sm-9 col-md-10 col-xl-11">
            <ul class="pagination justify-content-center">
                <li class="page-item<?= ($pgPage==0 ? ' disabled' : '') ?>"><a class="page-link" href="<?=
($pgPage<2 ? 'gallery.php' : '?page='. $pgPage) ?>">Eelmine</a></li>
                <li class="page-item<?= ($pgPage==0 ? ' active' : '') ?>"><a class="page-link" href="gallery.php">1</a></li><?php
for ($i = 1; $i < $pgTotal; $i++)
    echo "\n" .'                <li class="page-item'. ($i==$pgPage ? ' active' : '')
            .'"><a class="page-link" href="?page='. ($i+1) .'">'. ($i+1) .'</a></li>';
?>

                <li class="page-item<?= ($pgPage+1==$pgTotal ? ' disabled' : '')
?>"><a class="page-link" href="<?= ($pgPage==$pgTotal ? '#' : '?page='. ($pgPage+2)) ?>">Järgmine</a></li>
            </ul>
        </div>
        <div class="col-sm-3 col-md-2 col-xl-1">
            <form method="post" action="<?= $_self ?>"">
                <select name="imagesPerPage" id="imagesPerPage" class="custom-select"><?php
foreach ($pgLimits as $limit):
?>

                    <option value="<?= $limit ?>"<?= $limit==$pgLimit ? ' selected' : '' ?>><?= $limit ?></option><?php
endforeach;
?>

                </select>
            </form>
        </div>
    </div>

    <div class="modal fade" id="confirmModal"><div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header"><h4 class="modal-title">Kustutamise kinnitus</h4></div>
            <div class="modal-body">Kas oled kindel, et soovid pildi "<span id="imagename"></span>" kustutada?</div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary col-2" id="confirmYes">Jah</button>
                <button type="button" class="btn btn-danger col-2" id="confirmNo">Ei</button>
            </div>
        </div>
    </div></div>

<?php
echo $gallery[1];
echo $gallery[2];

require '../inc/_footer.inc' ;
