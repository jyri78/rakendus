<?php

function createNewImage($type, $maxWidth, $maxHeight) {
    // Teen pildi väiksemaks
    if ($type == 'jpeg') {
        $myTempImage = imagecreatefromjpeg($_FILES['fileToUpload']['tmp_name']);
    } else {
        $myTempImage = imagecreatefrompng($_FILES['fileToUpload']['tmp_name']);
    }

    $imageW = imagesx($myTempImage);
    $imageH = imagesy($myTempImage);

    if ($imageW < $maxWidth && $imageH < $maxHeight) {
        $imageSizeRatio = 1;
    }
    elseif ($imageW / $maxWidth > $imageH / $maxHeight) {
        $imageSizeRatio = $imageW / $maxWidth;
    } else {
        $imageSizeRatio = $imageH / $maxHeight;
    }

    $newW = round($imageW / $imageSizeRatio);
    $newH = round($imageH / $imageSizeRatio);

    // Loob uue ajutise pildiobjekti
    $myNewImage = imagecreatetruecolor($newW, $newH);
    imagecopyresampled($myNewImage, $myTempImage, 0, 0, 0, 0, $newW, $newH, $imageW, $imageH);
    imagedestroy($myTempImage);

    return $myNewImage;
}

function createThumb($original, $type, $size) {
    // Teen pildi väiksemaks
    if ($type == 'png') {
        $myTempImage = imagecreatefrompng($original);
    } else {
        $myTempImage = imagecreatefromjpeg($original);
    }

    $imageW = imagesx($myTempImage);
    $imageH = imagesy($myTempImage);

    $imageMaxS = max($imageW, $imageH);
    $imageMinS = min($imageW, $imageH);

    // Abimuutuja pildilt x või y positsiooni määramiseks
    $pos = round(($imageMaxS-$imageMinS)/2);

    if ($imageMinS < $size) $size = $imageMinS;

    // Alguspunktid
    $x = 0;
    $y = 0;
    if ($imageMinS == $imageW) $y = $pos;  // kui püstine pilt, siis positsioneerib vertikaalselt
    else                       $x = $pos;

    // Loob uue ajutise pildiobjekti
    $myNewImage = imagecreatetruecolor($size, $size);
    imagecopyresampled($myNewImage, $myTempImage, 0, 0, $x, $y, $size, $size, $imageMinS, $imageMinS);

    // Loob reaalse pildi salvestamiseks
    ob_start();
    if ($type == 'png') imagepng($myNewImage);
    else                imagejpeg($myNewImage);
    $thumb = ob_get_contents();
    ob_end_clean();

    imagedestroy($myTempImage);
    imagedestroy($myNewImage);

    return 'data:image/'. $type .';base64,'. base64_encode($thumb);
}

function saveImage($filename, $origname, $thumb, $alttext, $privacy) {
    $params = [$_SESSION['userid'], $filename, $origname, $thumb, $alttext, $privacy];

    $db = (new DB())
            ->insert('photos', ['userid', 'filename', 'origname', 'thumb', 'alttext', 'privacy'])
            ->values($params)  // küsimärgid luuakse automaatselt
            ->q($params, 'issssi');

    if ($db->affectedRows() != -1) {
        $notice = "OK";
    } else {
        $notice = $db->stmtError;
    }

    return $notice;
}


/* =============================================================================
   gallery.php
*/
function getGallery($privacy) {
    $uid = $_SESSION['userid'] ?? 0;
    $response = null;
    $modal = null;

    //$db = new DB();
    // Ühekordsel tulemuse päringul ei ole objekti salvestamiseks vajadust;
    // pealegi, kõik eelnevad SQL "konstruktorid" tagastavad viite objektile
    $result = (new DB())
            ->select(['photos.id', 'photos.userid', 'users.firstname', 'users.lastname'
                    , 'photos.filename', 'photos.origname', 'photos.thumb', 'photos.created'
                    , 'photos.alttext', 'photos.privacy', 'photos.deleted'])
            ->from('photos')  // tabelinime eesliide lisatakse automaatselt
            ->join('users')->on(['photos.userid', 'users.id'])
            ->where()
                ->isNull('deleted')
                ->and(true)->eq(['users.id', '?'])  // and(true) - avab sulu
                ->or()->lt(['privacy', '?'])        // lisab sulgeva sulu automaatselt
            ->order(['photos.id', true])            // 'true' - sorteerib vastupidises suunas ehk 'DESC'
            ->q([$uid, $privacy], 'ii')  // SQL päringu sooritamine, andes ette bind_param() argumendid
            ->fetchAll();

    foreach ($result as $row) {
        $id = preg_replace('/[\#\:\.\ ]/', '', $row['origname']) .'_'. $row['id'];

        // Kui pilt kasutaja lisatud, kuvab muutmise ja kustutamise nupud, muul juhul autori nime
        $name = $row['userid'] == $uid
                ? '<button type="button" class="btn btn-outline-primary mx-1 btn-sm change" data-id="'
                    . $row['id'] .'" title="Muuda pildi sätted">Muuda</button>'
                    .'<button type="button" class="btn btn-outline-danger btn-sm mx-1 del" data-id="'
                    . $row['id'] .'" data-title="'. $row['origname'] .'" title="Kustuta pilt">Kustuta</button>'
                : '<h6>'. $row['firstname'] .' '. $row['lastname'] .'</h6>';

        $response .= "\n" .'<div class="card text-center m-2 img-card" id="'. $row['id'] .'">'
                // Link pildiga
                .'<a class="btn btn-outline-info py-2" href="#'. $id
                .'" data-toggle="modal" title="Vaata suuremalt"><img class="img-thumbnail" src="'
                . $row['thumb'] .'" alt="'. $row['alttext'] .'"></a><div class="container mt-2">'
                // Pildi autor/nupud ja üleslaadimise kuupäev
                . $name .'<p><small class="text-secondary">'. $row['created'] .'</small></p></div></div>';

        $modal .= "\n" .'<div class="modal fade" id="'. $id .'">'
                .'<div class="modal-dialog modal-dialog-centered modal-lg"><div class="modal-content">'
                // Dialoogi päis faili originaalnimega
                .'<div class="modal-header"><h4 class="modal-title">'. $row['origname']
                .'</h4><button type="button" class="close" data-dismiss="modal">&times;</button></div>'
                // Dialoogi keha pildi ja selle ALT tekstiga
                .'<div class="modal-body text-center"><img class="rounded-lg" src="picture.php?img='
                . $row['filename'] .'"><h5 class="mt-2">'. $row['alttext'] .'</h5></div>'
                // Dialoogi jalus sulgemisnupuga
                .'<div class="modal-footer">'
                .'<button type="button" class="btn btn-danger" data-dismiss="modal">Sulge</button>'
                .'</div></div></div></div>';
    }

    return [$response, $modal ."\n"];
}

function getImageOwner($imgId) {
    $result = (new DB())
            ->select(['id', 'userid'])
            ->from('photos')
            ->where()->eq(['id', '?'])
            ->q($imgId, 'i')->fetch();

    return $result['userid'] ?? 0;
}

function deleteImage($id) {
    $ok = true;

    $db = (new DB())
            ->update('photos')
            ->set(['deleted', 'now()'])
            ->where()->eq(['id', '?'])
            ->q($id, 'i');

    if ($db->affectedRows() == -1) $ok = false;

    //~ Väljastab kas kustutamine läks korda või mitte
    if ($ok) echo 'OK!';
    else     echo $db->stmtError;
}


/* =============================================================================
   picture.php
*/
function testImageName($name, $privacy) {
    $uid = $_SESSION['userid'] ?? 0;

    $result = (new DB())
            ->select(['userid', 'filename', 'origname', 'privacy', 'deleted'])
            ->from('photos')
            ->where()
                ->isNull('deleted')
                ->and()->eq(['filename', '?'])
                ->and(true)->eq(['userid', '?'])  // lisab sulu
                ->or()->lt(['privacy', '?'])      // sulgev sulg lisatakse automaatselt
            ->q([$name, $uid, $privacy], 'sii')->fetch();

    if ($result) return true;
    return false;
}


/* =============================================================================
   update_picture.php
*/
function getImageData($id) {
    $uid = $_SESSION['userid'] ?? 0;

    $result = (new DB())
            ->select(['id', 'userid', 'filename', 'origname', 'privacy', 'thumb', 'alttext', 'deleted'])
            ->from('photos')
            ->where()
                ->isNull('deleted')
                ->and()->eq(['id', '?'])
                ->and()->eq(['userid', '?'])
            ->q([$id, $uid], 'ii')->fetch();

    return $result;
}

function changeImageData($id, $alttext, $privacy) {
    $ok = true;

    $db = (new DB())
            ->update('photos')
            ->set([['alttext', '?'], ['privacy', '?']])
            ->where()->eq(['id', '?'])
            ->q([$alttext, $privacy, $id], 'sii');

    if ($db->affectedRows() == -1) $ok = false;
    return $ok;
}
