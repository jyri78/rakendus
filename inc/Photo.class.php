<?php
@include "Exif.class.php";


class Photo {
    private $picToUpload;
    private $imageFileType = null;

    private $fileName;
    private $originalTarget;
    private $normalTarget;
    private $addWatermark;
    private $watermarkSrc;
    private $imageMaxWidth;
    private $imageMaxHeight;
    private $imageThumbSize;

    private $myTempImage;
    private $myNewImage;




    /* =========================================================================================
       Staatilised klassimeetodid
       =========================================================================================
    */

    // -------------------------------------------------------------------------
    // Esmalt mõned abimeetodid, mida kasutatakse mitmes failis
    // -------------------------------------------------------------------------
    public static function createImageFromFile($imageFile, $imageFileType) {
        if ($imageFileType == 'jpeg') {
            $myTempImage = imagecreatefromjpeg($imageFile);
        } else {
            $myTempImage = imagecreatefrompng($imageFile);
        }
        return $myTempImage;
    }

    public static function resizePhoto(&$srcImage, $imageFileType
                , $width = IMG_MAX_WIDTH, $height = IMG_MAX_HEIGHT, $keepOrigProportion = true) {
        $imageW = imagesx($srcImage);
        $imageH = imagesy($srcImage);
        $newW = $width;
        $newH = $height;
        $cutX = 0;
        $cutY = 0;
        $cutSizeW = $imageW;
        $cutSizeH = $imageH;
        $ratioW = $imageW / $width;
        $ratioH = $imageH / $height;
        
        if ($width == $height) {
            if ($imageW > $imageH) {
                $cutSizeW = $imageH;
                $cutX = round(($imageW - $cutSizeW) / 2);
            } else {
                $cutSizeH = $imageW;
                $cutY = round(($imageH - $cutSizeH) / 2);
            }
        }
        elseif ($keepOrigProportion) {  // kui tuleb originaalproportsioone säilitada
            if ($ratioW > $ratioH) {
                $newH = round($imageH / $ratioW);
            } else {
                $newW = round($imageW / $ratioH);
            }
        } else {  // kui on vaja kindlasti etteantud suurust, ehk pisut ka kärpida
            if ($ratioW < $ratioH) {
                $cutSizeH = round($ratioW * $height);
                $cutY = round(($imageH - $cutSizeH) / 2);
            } else {
                $cutSizeW = round($ratioH * $width);
                $cutX = round(($imageW - $cutSizeW) / 2);
            }
        }
        
        // Loob uue ajutise pildiobjekti
        $myNewImage = imagecreatetruecolor($newW, $newH);

        // Kui on läbipaistvusega PNG-pilt, siis on vaja säilitada läbipaistvusega
        if ($imageFileType == 'png') {
            imagesavealpha($myNewImage, true);
            $transColor = imagecolorallocatealpha($myNewImage, 0, 0, 0, 127);
            imagefill($myNewImage, 0, 0, $transColor);
        }

        imagecopyresampled($myNewImage, $srcImage, 0, 0, $cutX, $cutY, $newW, $newH, $cutSizeW, $cutSizeH);
        return $myNewImage;
    }//resizePhoto(&$srcimage, $imageFileType, $width, $height, $keepOrigProportion = true)

    public static function addWatermark(&$targetImage, $wmFile, $wmLocation, $fromEdge) {
        $wmFileType = strtolower(pathinfo($wmFile, PATHINFO_EXTENSION));
        if ($wmFileType == 'jpg') $wmFileType = 'jpeg';
        ////$waterMark = imagecreatefrompng($wmFile);
        $waterMark = self::createImageFromFile($wmFile, $wmFileType);
        $waterMarkW = imagesx($waterMark);
        $waterMarkH = imagesy($waterMark);

        if ($wmLocation == 1 or $wmLocation == 4) $waterMarkX = $fromEdge;
        if ($wmLocation == 2 or $wmLocation == 3) $waterMarkX = imagesx($targetImage) - $waterMarkW - $fromEdge;

        if ($wmLocation == 1 or $wmLocation == 2) $waterMarkY = $fromEdge;
        if ($wmLocation == 3 or $wmLocation == 4) $waterMarkY = imagesy($targetImage) - $waterMarkH - $fromEdge;

        if ($wmLocation == 5) {
            $waterMarkX = round((imagesx($targetImage) - $waterMarkW) / 2, 0);
            $waterMarkY = round((imagesy($targetImage) - $waterMarkH) / 2, 0);
        }
        imagecopy($targetImage, $waterMark, $waterMarkX, $waterMarkY, 0, 0, $waterMarkW, $waterMarkH);
    }

    public static function saveImgToFile (&$image, $target, $imageFileType) {
        $notice = 0;
        if ($imageFileType == "jpeg") {
            if (imagejpeg($image, $target, 90)) $notice = 1;
        }
        if ($imageFileType == "png") {
            if (imagepng($image, $target, 6)) $notice = 1;
        }
        return $notice;
    }


    // -------------------------------------------------------------------------
    // ! Kuna objekti meetod hetkel ei tööta, kasutan seni selle
    // -------------------------------------------------------------------------
    public static function createThumb($originalImage, $type, $size) {
        if ($type == 'png') {
            $myTempImage = imagecreatefrompng($originalImage);
        } else {
            $myTempImage = imagecreatefromjpeg($originalImage);
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


    // -------------------------------------------------------------------------
    // Edasi andmebaasiga seotud tegevused (erinevad failid)
    // -------------------------------------------------------------------------
    public static function saveImage($filename, $origname, $thumb, $alttext, $privacy) {
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


    // -------------------------------------------------------------------------
    // gallery.php
    // -------------------------------------------------------------------------
    
    // Privaatne abifunktsiooni EXIF info saamiseks
    private static function _getExifData($targetImage, $isOwner) {
        $allowed = array(
            'user' => ['FILE.MimeType', 'IFD0.Make'
                        , 'IFD0.Model', 'IFD0.Orientation'
                        , 'EXIF.ExposureTime', 'EXIF.FNumber', 'EXIF.ISOSpeedRatings', 'EXIF.DateTimeOriginal'],
            'owner' => ['FILE.FileName', 'FILE.MimeType'
                        , 'IFD0.Make', 'IFD0.Model', 'IFD0.Orientation', 'IFD0.XResolution', 'IFD0.YResolution'
                        , 'IFD0.ResolutionUnit', 'IFD0.DateTime'
                        , 'EXIF.YCbCrPositioning', 'EXIF.ExposureTime', 'EXIF.FNumber', 'EXIF.ISOSpeedRatings'
                        , 'EXIF.SensitivityType', 'EXIF.ExifVersion', 'EXIF.DateTimeOriginal'
                        , 'EXIF.DateTimeDigitized', 'EXIF.CompressedBitsPerPixel', 'EXIF.ShutterSpeedValue'
                        , 'EXIF.ApertureValue', 'EXIF.MaxApertureValue', 'EXIF.MeteringMode', 'EXIF.FocalLength']
        );
        $sel = ($isOwner ? 'owner' : 'user');
        $exif = Exif::getExifData($targetImage);

        if ($exif) {
            foreach ($exif as $key => $section) {
                foreach ($section as $name => $value) {
                    if(!in_array($key.'.'.$name, $allowed[$sel])) continue;
                    $ret[$key][$name] = $value;
                }
            }
        }
        return (!$ret ? false : $ret );
    }
    public static function getGallery($privacy) {
        $uid = $_SESSION['userid'] ?? 0;
        $response = null;
        $modal = null;
        $modalE = null;
        $icons = [
            // Info ikoonike (EXIF-i kuvamiseks)
            '<svg class="bi bi-info-circle" width="1em" height="1em" viewBox="0 0 16 16" fill="currentColor"'
                    .' xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M8 15A7 7 0 108 1a7 7 0 000'
                    .' 14zm0 1A8 8 0 108 0a8 8 0 000 16z" clip-rule="evenodd"/><path d="M8.93 6.588l-2.29.287'
                    .'-.082.38.45.083c.294.07.352.176.288.469l-.738 3.468c-.194.897.105 1.319.808 1.319.545 0'
                    .' 1.178-.252 1.465-.598l.088-.416c-.2.176-.492.246-.686.246-.275 0-.375-.193-.304-.533L8.93'
                    .' 6.588z"/><circle cx="8" cy="4.5" r="1"/></svg>',
            // Avalik pilt
            '<svg class="bi bi-people-fill" width="1em" height="1em" viewBox="0 0 16 16" fill="currentColor"'
                    .' xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M7 14s-1 0-1-1 1-4 5-4 5 3'
                    .' 5 4-1 1-1 1H7zm4-6a3 3 0 100-6 3 3 0 000 6zm-5.784 6A2.238 2.238 0 015 13c0-1.355.68-2.75'
                    .' 1.936-3.72A6.325 6.325 0 005 9c-4 0-5 3-5 4s1 1 1 1h4.216zM4.5 8a2.5 2.5 0 100-5 2.5 2.5 0'
                    .' 000 5z" clip-rule="evenodd"/></svg>',
            // Ainult kasutajale pilt
            '<svg class="bi bi-person-check-fill" width="1em" height="1em" viewBox="0 0 16 16" fill="currentColor"'
                    .' xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M1 14s-1 0-1-1 1-4 6-4 6 3 6'
                    .' 4-1 1-1 1H1zm5-6a3 3 0 100-6 3 3 0 000 6zm9.854-2.854a.5.5 0 010 .708l-3 3a.5.5 0 01-.708'
                    .' 0l-1.5-1.5a.5.5 0 01.708-.708L12.5 7.793l2.646-2.647a.5.5 0 01.708 0z" clip-rule="evenodd"/></svg>',
            // Privaatne pilt
            '<svg class="bi bi-lock-fill" width="1em" height="1em" viewBox="0 0 16 16" fill="currentColor"'
                    .' xmlns="http://www.w3.org/2000/svg"><rect width="11" height="9" x="2.5" y="7" rx="2"/><path'
                    .' fill-rule="evenodd" d="M4.5 4a3.5 3.5 0 117 0v3h-1V4a2.5 2.5 0 00-5 0v3h-1V4z"'
                    .' clip-rule="evenodd"/></svg>',
            // Teiste poolt avalikuks tehtud pildid
            '<svg class="bi bi-people" width="1em" height="1em" viewBox="0 0 16 16" fill="currentColor"'
                    .' xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M15 14s1 0 1-1-1-4-5-4-5 3-5'
                    .' 4 1 1 1 1h8zm-7.995-.944v-.002.002zM7.022 13h7.956a.274.274 0 00.014-.002l.008-.002c-.002-.264'
                    .'-.167-1.03-.76-1.72C13.688 10.629 12.718 10 11 10c-1.717 0-2.687.63-3.24 1.276-.593.69-.759'
                    .' 1.457-.76 1.72a1.05 1.05 0 00.022.004zm7.973.056v-.002.002zM11 7a2 2 0 100-4 2 2 0 000 4zm3-2a3'
                    .' 3 0 11-6 0 3 3 0 016 0zM6.936 9.28a5.88 5.88 0 00-1.23-.247A7.35 7.35 0 005 9c-4 0-5 3-5 4 0'
                    .' .667.333 1 1 1h4.216A2.238 2.238 0 015 13c0-1.01.377-2.042 1.09-2.904.243-.294.526-.569.846'
                    .'-.816zM4.92 10c-1.668.02-2.615.64-3.16 1.276C1.163 11.97 1 12.739 1 13h3c0-1.045.323-2.086.92'
                    .'-3zM1.5 5.5a3 3 0 116 0 3 3 0 01-6 0zm3-2a2 2 0 100 4 2 2 0 000-4z" clip-rule="evenodd"/></svg>',
            // Teiste poolt sisseloginud kasutajatele mõeldud pildid
            '<svg class="bi bi-person" width="1em" height="1em" viewBox="0 0 16 16" fill="currentColor"'
                    .' xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M13 14s1 0 1-1-1-4-6-4-6 3-6'
                    .' 4 1 1 1 1h10zm-9.995-.944v-.002.002zM3.022 13h9.956a.274.274 0 00.014-.002l.008-.002c-.001'
                    .'-.246-.154-.986-.832-1.664C11.516 10.68 10.289 10 8 10c-2.29 0-3.516.68-4.168 1.332-.678.678'
                    .'-.83 1.418-.832 1.664a1.05 1.05 0 00.022.004zm9.974.056v-.002.002zM8 7a2 2 0 100-4 2 2 0 000'
                    .' 4zm3-2a3 3 0 11-6 0 3 3 0 016 0z" clip-rule="evenodd"/></svg>'
        ];

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
            $exif = self::_getExifData(IMG_ORIGINAL_PHOTO_DIR . $row['filename'], $row['userid']==$uid);
            $bI = $uid && $exif !== false;
            $icn = '';

            //* Kui Sisseloginud kasutaja
            if ($uid) {
                $i = $row['privacy'];  // valib esmalt ikooni indeksi privaatsuse järgi
                if ($row['userid'] != $uid) $i += 3;  // muul juhul ainult kasutajale mõeldud ikooni lisamine
                $icn = '<h3 class="card-img-overlay text-info" style="opacity:.7">'. $icons[$i] .'</h3>';
            }

            // Kui pilt kasutaja lisatud, kuvab muutmise ja kustutamise nupud, muul juhul autori nime
            $name = $row['userid'] == $uid
                    ? '<button type="button" class="btn btn-outline-primary mx-1 btn-sm change" data-id="'
                        . $row['id'] .'" title="Muuda pildi sätted">Muuda</button>'
                        .'<button type="button" class="btn btn-outline-danger btn-sm mx-1 del" data-id="'
                        . $row['id'] .'" data-title="'. $row['origname'] .'" title="Kustuta pilt">Kustuta</button>'
                    : '<h6>'. $row['firstname'] .' '. $row['lastname'] .'</h6>';

            $response .= "\n" .'<div class="card text-center m-2 img-card" id="'. $row['id'] .'">'
                    // Link pildiga (ja ikoon pildi kohal)
                    .'<a class="btn btn-outline-info py-2" href="#'. $id .'" data-toggle="modal" title="Vaata suuremalt">'
                    .'<div class="card m-0 p-0"><img class="img-thumbnail card-img" src="'. $row['thumb'] .'" alt="'
                    . $row['alttext'] .'">'. $icn .'</div></a><div class="container mt-2">'
                    // Pildi autor/nupud ja üleslaadimise kuupäev (EXIF olemasolul info ikoonike)
                    . $name .'<p><small class="text-secondary">'. ( !$bI ? '' :
                            '<a class="mr-2" href="#'. $id .'e" data-toggle="modal" title="info">'. $icons[0] .'</a>')
                    . $row['created'] .'</small></p></div></div>';

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
            
            $modalE .= "\n" .'<div class="modal fade" id="'. $id .'e">'
                    .'<div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal"><div class="modal-content">'
                    // Dialoogi päis faili originaalnimega
                    .'<div class="modal-header"><h4 class="modal-title">'. $row['origname']
                    .'</h4><button type="button" class="close" data-dismiss="modal">&times;</button></div>'
                    // Dialoogi keha EXIF infoga
                    .'<div class="modal-body"><ul class="list-group">'. "\n";
            foreach ($exif as $key => $section) {
                foreach ($section as $name => $val) {
                    $modalE .= '<li class="list-group-item"><b>'. $key .'</b>.'. $name
                            .' &nbsp; <span class="badge badge-info badge-pill">'. $val .'</span></li>';
                }
            }
            $modalE .= "\n" .'</ul></div>'
                    // Dialoogi jalus sulgemisnupuga
                    .'<div class="modal-footer">'
                    .'<button type="button" class="btn btn-danger" data-dismiss="modal">Sulge</button>'
                    .'</div></div></div></div>';
        }

        return [$response, $modal ."\n", $modalE ."\n"];
    }

    public static function getImageOwner($imgId) {
        $result = (new DB())
                ->select(['id', 'userid'])
                ->from('photos')
                ->where()->eq(['id', '?'])
                ->q($imgId, 'i')->fetch();

        return $result['userid'] ?? 0;
    }

    public static function deleteImage($id) {
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


    // -------------------------------------------------------------------------
    // picture.php
    // -------------------------------------------------------------------------
    public static function testImageName($name, $privacy) {
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


    // -------------------------------------------------------------------------
    // update_picture.php
    // -------------------------------------------------------------------------
    public static function getImageData($id) {
        $uid = $_SESSION['userid'] ?? 0;

        $result = (new DB())
                ->select(['id', 'userid', 'filename', 'origname', 'privacy', 'thumb', 'alttext', 'deleted'])
                ->from('photos')
                ->where()
                    ->isNull('deleted')
                    ->and()->eq(['id', '?'])
                    ->and()->eq(['userid', '?'])
                ->q([$id, $uid], 'ii')->fetch();

        // Lisab vesimärgi seadete info
        $result['addwm'] = strpos($result['filename'], IMG_WATERMARK_SUFIX) !== false;

        return $result;
    }

    public static function changeImageData($id, $alttext, $privacy, $addwm, $oldFileName) {
        $ok = true;
        $newFileName = $oldFileName;
        
        // Tuvastab vesiaärgi lisamise/"eemaldamise" vajaduse
        $wm = strpos($oldFileName, IMG_WATERMARK_SUFIX);
        $dot =  strpos($oldFileName, '.');
        $oldWM = $wm !== false;

        // Kui vesimärgi reegel on muutunud
        if ($oldWM != $addwm) {
            if ($oldWM) $newFileName = substr($oldFileName, 0, $wm) . substr($oldFileName, $dot);
            else        $newFileName = substr($oldFileName, 0, $dot) . IMG_WATERMARK_SUFIX . substr($oldFileName, $dot);

            // Galerii toimimiseks nimetab originaalfail ümber
            rename(IMG_ORIGINAL_PHOTO_DIR . $oldFileName, IMG_ORIGINAL_PHOTO_DIR . $newFileName);

            // Kui uut faili veel ei ole, siis loob selle (üleslaadimisel ei tehta mõlemat varianti)
            if (!file_exists(IMG_NORMAL_PHOTO_DIR . $newFileName)) {
                $type = substr($oldFileName, $dot+1);
                $myTempImage = self::createImageFromFile(IMG_ORIGINAL_PHOTO_DIR . $newFileName, $type);
                $myNewImage = self::resizePhoto($myTempImage, $type);

                // Lisab vajadusel vesimärgi
                if ($addwm) {
                    self::addWatermark($myNewImage, IMG_WATERMARK_SRC, 3, 20);
                }
                $result = self::saveImgToFile($myNewImage, IMG_NORMAL_PHOTO_DIR . $newFileName, $type);
                unset($myTempImage);
                unset($myNewImage);
            }
        }

        $db = (new DB())
                ->update('photos')
                ->set([['filename', '?'], ['alttext', '?'], ['privacy', '?']])
                ->where()->eq(['id', '?'])
                ->q([$newFileName, $alttext, $privacy, $id]);

        if ($db->affectedRows() == -1) $ok = false;
        return $ok;
    }




    /* =========================================================================================
       Objekti konstruktor ja destruktor
       =========================================================================================
    */
    function __construct($picToUpload, $addWatermark = true, $fileNamePrefix = IMG_FILE_NAME_PREFIX
                        , $originalPhotoDir = IMG_ORIGINAL_PHOTO_DIR, $normalPhotoDir = IMG_NORMAL_PHOTO_DIR
                        , $watermarkSrc = IMG_WATERMARK_SRC, $watermarkSufix = IMG_WATERMARK_SUFIX
                        , $imageMaxWidth = IMG_MAX_WIDTH, $imageMaxHeight = IMG_MAX_HEIGHT
                        , $imageThumbSize = IMG_THUMB_SIZE) {
        $this->picToUpload = $picToUpload;
        $this->addWatermark = $addWatermark;
        $this->watermarkSrc = $watermarkSrc;
        $this->imageMaxWidth = $imageMaxWidth;
        $this->imageMaxHeight = $imageMaxHeight;
        $this->imageThumbSize = $imageThumbSize;

        $check = getimagesize($picToUpload['tmp_name']);
        if ($check !== false) {
            if ($check['mime'] == 'image/jpeg')    $this->imageFileType = 'jpeg';
            elseif ($check['mime'] == 'image/png') $this->imageFileType = 'png';
            else                                   $this->imageFileType = false;
        }
        $wm = ($addWatermark ? $watermarkSufix : '');

        $timestamp = microtime(1) * 10000;
        $this->fileName = $fileNamePrefix . $timestamp . $wm .'.'. $this->imageFileType;

        $this->originalTarget = $originalPhotoDir . $this->fileName;
        $this->normalTarget = $normalPhotoDir . $this->fileName;

        if (!$this->imageFileType) return;  // kehtetu pildi korral edasi ei jätka
        $this->myTempImage = self::createImageFromFile($picToUpload['tmp_name'], $this->imageFileType);
    }//__construct()

    function __destruct () {
        if (isset($this->myTempImage)) imagedestroy($this->myTempImage);
        if (isset($this->myNewImage)) imagedestroy($this->myNewImage);
    }//__destruct()




    /* =========================================================================================
       Globaalsed objekti meetodid
       =========================================================================================
    */
    public function getImageFileType () { return $this->imageFileType; }
    public function getImageSize () { return $this->picToUpload['size']; }
    public function getNewImage () { return $this->myNewImage; }
    public function getFileName() { return $this->fileName; }
    public function getOriginalTarget () { return $this->originalTarget; }


    public function createNormalPhoto($width = 0, $height = 0, $keepOrigProportion = true
            , $wmLocation = 3, $fromEdge = 20) {
        // Kui pildi suurus määramata või ületab lubatud maksimumi
        if (!$width || $width > $this->imageMaxWidth) $width = $this->imageMaxWidth;
        if (!$height || $height > $this->imageMaxHeight) $height = $this->imageMaxHeight;

        $this->myNewImage = self::resizePhoto($this->myTempImage, $this->imageFileType
                                              , $width, $height, $keepOrigProportion);

        // Lisab vajadusel vesimärgi
        if ($this->addWatermark) {
            if ($wmLocation < 1 || $wmLocation > 5) $wmLocation = 3;
            self::addWatermark($this->myNewImage, $this->watermarkSrc, $wmLocation, $fromEdge);
        }
    }

    public function createThumbnail() {
        /*$myNewImage = $this->_resizePhoto($this->imageThumbSize, $this->imageThumbSize);

        // Loob reaalse pildi salvestamiseks
        ob_start();
        if ($this->ImageFileType == 'png') imagepng($myNewImage);
        else                               imagejpeg($myNewImage);
        $thumb = ob_get_contents();
        ob_end_clean();
        $thumb = self::createThumb($myNewImage, $this->imageFileType, $this->imageThumbSize);

        imagedestroy($myNewImage);
        return 'data:image/'. $this->imageFileType .';base64,'. base64_encode($thumb);*/

        //! Mingil põhjusel üleval olev kood ei tööta - base64 string pikem ja brauser ei renderda
        return self::createThumb($this->originalTarget, $this->imageFileType, $this->imageThumbSize);
    }

    public function saveImageData($alttext = '', $privacy = 3) {
        $thumb = self::createThumb($this->originalTarget, $this->imageFileType, $this->imageThumbSize);
        return self::saveImage($this->fileName, $this->picToUpload['name'], $thumb, $alttext, $privacy);
    }


    public function createAndSaveNormalPhoto($width = 0, $height = 0, $keepOrigProportion = true
            , $wmLocation = 3, $fromEdge = 20) {
        $this->createNormalPhoto($width, $height, $keepOrigProportion, $wmLocation, $fromEdge);
        return self::saveImgToFile($this->myNewImage, $this->normalTarget, $this->imageFileType);
    }

}//class::Photo
