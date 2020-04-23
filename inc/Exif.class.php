<?php

class Exif {
    public static function getExifData($targetImage) {
        $ret = [];
        $ut = array(
            'UndefinedTag:0x8830'=>'SensitivityType'
        );
        $exif = @exif_read_data($targetImage, null, true);

        // IPTC data
        // https://stackoverflow.com/questions/1417138/making-iptc-data-searchable
        /*getimagesize(IMG_ORIGINAL_PHOTO_DIR . $row['filename'], $info);
        if (isset($info['APP13'])) $exif['IPTC'] = iptcparse($info['APP13']);
        var_dump($exif);*/

        foreach ($exif as $key => $section) {
            foreach ($section as $name => $value) {
                if (array_key_exists($name, $ut)) $name = $ut[$name];  // Parandab vajadusel nime
                if(!$value) continue;  // Kui väärtus tühi, pole mõtet jätta
                $ret[$key][$name] = self::_manageExifVal($key.'.'.$name, $value);
            }
        }

        return (!$ret ? false : $ret );
    }


    private static function _manageExifVal($header, $value) {
        // https://www.media.mit.edu/pia/Research/deepview/exif.html
        // https://exiftool.org/TagNames/EXIF.html
        // https://github.com/php/php-src/blob/master/ext/exif/exif.c
        // https://www.exif.org/Exif2-2.PDF
        switch($header) {
            case 'IFD0.Orientation':
                switch ($value) {
                    case 1:  $ret = 'Horizontal (normal)'; break;
                    case 2:  $ret = 'Mirror horizontal'; break;
                    case 3:  $ret = 'Rotate 180'; break;
                    case 4:  $ret = 'Mirror vertical'; break;
                    case 5:  $ret = 'Mirror horizontal and rotate 270 CW'; break;
                    case 6:  $ret = 'Rotate 90 CW'; break;
                    case 7:  $ret = 'Mirror horizontal and rotate 90 CW'; break;
                    case 8:  $ret = 'Rotate 270 CW'; break;
                    case 9:  $ret = 'UNDEFINED'; break;
                    default: $ret = '&lt;UNKNOWN&gt;: '. $value;
                }
            break;
            case 'IFD0.XResolution':
            case 'IFD0.YResolution':
                $ret = self::_exifValToNum($value);
            break;
            case 'IFD0.ResolutionUnit':
                switch ($value) {
                    case 1:  $ret = 'None'; break;
                    case 2:  $ret = 'inches'; break;
                    case 3:  $ret = 'cm'; break;
                    default: $ret = '&lt;UNKNOWN&gt;: '.$value;
                }
            break;
            case 'IFD0.YCbCrPositioning':
                switch ($value) {
                    case 1:  $ret = 'Centered'; break;
                    case 2:  $ret = 'Co-sited'; break;
                    default: $ret = '&lt;UNKNOWN&gt;: '. $value;
                }
            break;
            case 'EXIF.ExposureTime':
                $ret = $value .' sec';
            break;
            case 'EXIF.FNumber':
                $ret = sprintf('%0.2f', self::_exifValToNum($value));
            break;
            case 'EXIF.SensitivityType':
                switch ($value) {
                    case 0:  $ret = 'Unknown'; break;
                    case 1:  $ret = 'Standard Output Sensitivity'; break;
                    case 2:  $ret = 'Recommended Exposure Index'; break;
                    case 3:  $ret = 'ISO Speed'; break;
                    case 4:  $ret = 'Standard Output Sensitivity and Recommended Exposure Index'; break;
                    case 5:  $ret = 'Standard Output Sensitivity and ISO Speed'; break;
                    case 6:  $ret = 'Recommended Exposure Index and ISO Speed'; break;
                    case 7:  $ret = 'Standard Output Sensitivity, Recommended Exposure Index and ISO Speed'; break;
                    default: $ret = '&lt;UNKNOWN&gt;: '. $value;
                }
            break;
            case 'EXIF.CompressedBitsPerPixel':
                $ret = sprintf('%d (bits/pixel)', self::_exifValToNum($value));
            break;
            case 'EXIF.ShutterSpeedValue':
                $val = self::_exifValToNum($value);
                $ret = sprintf('1/%d sec', pow(2,$val));
            break;
            case 'EXIF.ApertureValue':
            case 'EXIF.MaxApertureValue':
                $val = self::_exifValToNum($value);
                $ret = sprintf('F %0.2f', pow(1.414213562373095,$val));
            break;
            case 'EXIF.MeteringMode':
                switch ($value) {
                    case 0:  $ret = 'Unknown'; break;
                    case 1:  $ret = 'Average'; break;
                    case 2:  $ret = 'Center-weighted average'; break;
                    case 3:  $ret = 'Spot'; break;
                    case 4:  $ret = 'Multi-spot'; break;
                    case 5:  $ret = 'Multi-segment'; break;
                    case 6:  $ret = 'Partial'; break;
                    case 255:  $ret = 'Other'; break;
                    default: $ret = '&lt;UNKNOWN&gt;: '. $value;
                }
            break;
            case 'EXIF.FocalLength':
                $ret = sprintf('%0.2f mm', self::_exifValToNum($value));
            break;
            default:
            $ret = $value;
        }

        return $ret;
    }

    private static function _exifValToNum($value) {
        $val = explode('/', $value);
        if (isset($val[1]) && $val[1] != 0) return $val[0] / $val[1];
        return $val[0];
    }
}