<?php

namespace app\core;

class Util{

    public static function uploadFiles(){
        return new util\UploadFiles();
    }

    public static function QR($size = 200, $errorCorrection = 'M'){
        return new util\QR($size, $errorCorrection);
    }

    public static function Barcode($type = 'C128', $widthFactor = 2, $totalHeight = 30){
        return new util\Barcode($type, $widthFactor, $totalHeight);
    }

    public static function Encryption($key = null, $cipher = 'AES-256-CBC'){
        return new util\Encryption($key, $cipher);
    }

    public static function Report($title = 'Report', $author = 'Author', $subject = 'Subject', $keywords = 'Keywords'){
        return new util\Report($title, $author, $subject, $keywords);
    }

    
}