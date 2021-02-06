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

    static function PUT($key) {
        $inputFileSrc = 'php://input';
        $lines = file($inputFileSrc);
    
        foreach($lines as $i =>  $line){
            $search = 'Content-Disposition: form-data; name="'.$key.'"';
            if(strpos($line, $search) !== false){
                return trim($lines[$i+2]);
            }
        }
    }

    static function generateRandomString($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
}