<?php

namespace app\core;



class Util{

    public static function uploadFiles(): ?util\UploadFiles{
        return new util\UploadFiles();
    }

    public static function QR($size = 200, $errorCorrection = 'M'): ?util\QR{
        return new util\QR($size, $errorCorrection);
    }

    public static function Barcode($type = 'C128', $widthFactor = 2, $totalHeight = 30): ?util\Barcode{
        return new util\Barcode($type, $widthFactor, $totalHeight);
    }

    public static function Encryption($key = null, $cipher = 'AES-256-CBC'): ?util\Encryption{
        return new util\Encryption($key, $cipher);
    }

    public static function Report($title = 'Report', $author = 'Author', $subject = 'Subject', $keywords = 'Keywords'): ?util\Report{
        return new util\Report($title, $author, $subject, $keywords);
    }

    public static function Csrf(): ?util\Csrf{
        return new util\Csrf();
    }

    public static function Globals(): ?util\Globals{
        return new util\Globals();
    }

    public static function getItemArray(db\DBModel $model, string $keyField, string $valueField, array $where = []): array{
        $items = [];
        $records = $model->findAll($where);
        foreach($records as $record){
            $items[$record->$keyField] = $record->$valueField;
        }
        return $items;
    }

    
}