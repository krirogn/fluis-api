<?php
class Helper {
    static function strright($str, $separator) {
        if (intval($separator)) {
            return substr($str, -$separator);
        } elseif ($separator === 0) {
            return $str;
        } else {
            $strpos = strpos($str, $separator);
    
            if ($strpos === false) {
                return $str;
            } else {
                return substr($str, -$strpos + 1);
            }
        }
    }

    static function strleft($str, $separator) {
        if (intval($separator)) {
            return substr($str, 0, $separator);
        } elseif ($separator === 0) {
            return $str;
        } else {
            $strpos = strpos($str, $separator);
    
            if ($strpos === false) {
                return $str;
            } else {
                return substr($str, 0, $strpos);
            }
        }
    }
}