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
    $sql = "INSERT INTO vr20_photos (userid, `filename`, origname, thumb, alttext, privacy) VALUES (";
    $params = [$_SESSION['userid'], $filename, $origname, $thumb, $alttext, $privacy];
    
    $db = new DB();
    $val = $db->values($params);

    $db->query($sql . $val .')', $params, 'issssi');

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

    $sql = "SELECT vr20_photos.id AS id, vr20_photos.userid AS uid,
                vr20_users.firstname AS fname, vr20_users.lastname AS lname,
                vr20_photos.filename AS filename, vr20_photos.origname AS origname,
                vr20_photos.thumb AS thumb, vr20_photos.created AS created,
                vr20_photos.alttext AS alttext, vr20_photos.privacy AS privacy, vr20_photos.deleted AS deleted
            FROM vr20_photos
            INNER JOIN vr20_users ON vr20_photos.userid = vr20_users.id
            WHERE deleted IS NULL AND (vr20_users.id=? OR privacy<?)
            ORDER BY vr20_photos.id DESC";
    
    $db = new DB();
    $result = $db->query($sql, [$uid, $privacy], 'ii')->fetchAll();

    foreach ($result as $row) {
        $id = preg_replace('/[\#\:\.\ ]/', '', $row['origname']) .'_'. $row['id'];

        // Kui pilt kasutaja lisatud, kuvab muutmise ja kustutamise nupud, muul juhul autori nime
        $name = $row['uid'] == $uid
                ? '<button type="button" class="btn btn-outline-primary mx-1 btn-sm change" data-id="'
                    . $row['id'] .'" title="Muuda pildi sätted">Muuda</button>'
                    .'<button type="button" class="btn btn-outline-danger btn-sm mx-1 del" data-id="'
                    . $row['id'] .'" data-title="'. $row['origname'] .'" title="Kustuta pilt">Kustuta</button>'
                : '<h6>'. $row['fname'] .' '. $row['lname'] .'</h6>';

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
    $result = null;
    $sql = "SELECT id, userid FROM vr20_photos WHERE id=?";

    //~ Loob andmebaasiühenduse ja teostab SQL päringu
    $db = new DB();
    $result = $db->query($sql, $imgId, 'i')->fetch();
    return $result['userid'];
}

function deleteImage($id) {
    $ok = true;
    $sql = "UPDATE vr20_photos SET deleted=NOW() WHERE id=?";

    //~ Loob andmebaasiühenduse ja teostab SQL päringu
    $db = new DB();
    $db->query($sql, $id, 'i');
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
    $sql = "SELECT userid, filename, origname, privacy, deleted FROM vr20_photos
            WHERE deleted IS NULL AND filename=? AND (userid=? OR privacy<?)";
    
    $db = new DB();
    $result = $db->query($sql, [$name, $uid, $privacy], 'sii')->fetch();

    if ($result) return true;
    return false;
}


/* =============================================================================
   update_picture.php
*/
function getImageData($id) {
    $uid = $_SESSION['userid'] ?? 0;
    $sql = "SELECT id, userid, filename, origname, privacy, thumb, alttext, deleted
            FROM vr20_photos
            WHERE deleted IS NULL AND id=? AND userid=?";

    $db = new DB();
    $result = $db->query($sql, [$id, $uid], 'ii')->fetch();
    return $result;
}

function changeImageData($id, $alttext, $privacy) {
    $ok = true;
    $sql = "UPDATE vr20_photos SET alttext=?, privacy=? WHERE id=?";

    //~ Loob andmebaasiühenduse ja teostab SQL päringu
    $db = new DB();
    $db->query($sql, [$alttext, $privacy, $id], 'sii');

    if ($db->affectedRows() == -1) $ok = false;
    return $ok;
}
