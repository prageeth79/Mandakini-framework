<?php

namespace app\core;

class Util{

    public static function uploadFiles(){
        return new uploadFiles();
    }

    public static function QR($size = 200, $errorCorrection = 'M'){
        return new encoder\QR($size, $errorCorrection);
    }

    public static function Barcode($type = 'C128', $widthFactor = 2, $totalHeight = 30){
        return new encoder\Barcode($type, $widthFactor, $totalHeight);
    }

    public static function Encryption($key = null, $cipher = 'AES-256-CBC'){
        return new encoder\Encription($key, $cipher);
    }

    
}