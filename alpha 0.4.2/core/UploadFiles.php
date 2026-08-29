<?php

namespace app\core;

class UploadFiles{
    public const FILE_NOFILE_UPLODED = 'no file uploaded';
    public const FILE_MISSING_EXT = 'missing ext';
    public const FILE_INVALID_EXT = 'invalid ext';
    public const FILE_UPLOAD_ERROR = 'upload error';
    public const FILE_TOO_LARGE = 'file too large';
    public const FILE_COULD_NOT_CREATE_DIR = 'could not create dir';
    public const FILE_UPLOAD_FAIL = 'upload fail';
    public const FILE_UPLOAD_SUCCESS = 'upload success';

    
    public function __construct(){
        
    }

    public function getFile($filename){
        return $_FILES[$filename];
    }

    public function hasFile($filename){
        return  $_FILES[$filename]['name'] !== '';
    }

    public function getFileExt($filename){
        $file = $_FILES[$filename];
        return strtolower(pathinfo(trim($file['name']), PATHINFO_EXTENSION));
    }

    public function upload_file($file_name, $upload_path, $allowed = ['jpg', 'jpeg', 'png', 'pdf'])
    {
        if (!isset($_FILES[$file_name]) || $_FILES[$file_name]['name'] =='' ) {
            return [false,'',$this::FILE_NOFILE_UPLODED]; // No file uploaded
        }

        $file = $_FILES[$file_name];
        $fileName = trim($file['name']);
        $fileTmpName = $file['tmp_name'];
        $fileSize = $file['size'];
        $fileError = $file['error'];

        $fileActualExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        if ($fileActualExt === '') {
            return [false, $fileName, $this::FILE_MISSING_EXT]; // Missing extension
        }

        if (!in_array($fileActualExt, $allowed, true)) {
            return [false, $fileName, $this::FILE_INVALID_EXT]; // Invalid extension
        }

        if ($fileError !== 0) {
            return [false, $fileName,$this::FILE_UPLOAD_ERROR]; // Upload error
        }

        if ($fileSize >= 5000000) {
            return [false, $fileName, $this::FILE_TOO_LARGE]; // File too large
        }

        $fileNameNew = uniqid('', true) . '.' . $fileActualExt;
        $normalizedUploadPath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $upload_path);

        if (strpos($normalizedUploadPath, DIRECTORY_SEPARATOR) === 0 || preg_match('/^[A-Za-z]:\\\\/', $normalizedUploadPath)) {
            $filesystemPath = $normalizedUploadPath;
        } else {
            $filesystemPath = Application::$ROOT_DIR . DIRECTORY_SEPARATOR . ltrim($normalizedUploadPath, DIRECTORY_SEPARATOR);
        }
        $filesystemPath = rtrim($filesystemPath, DIRECTORY_SEPARATOR);

        if (!is_dir($filesystemPath) && !mkdir($filesystemPath, 0755, true) && !is_dir($filesystemPath)) {
            return [false, $fileName, $this::FILE_COULD_NOT_CREATE_DIR]; // Could not create directory
        }

        $fileDestination = $filesystemPath . DIRECTORY_SEPARATOR . $fileNameNew;
        if (move_uploaded_file($fileTmpName, $fileDestination)) {
            return [true,rtrim(str_replace('\\', '/', $upload_path), '/\\') . '/' . $fileNameNew,$this::FILE_UPLOAD_SUCCESS];
        }

        return [false, $fileName,$this::FILE_UPLOAD_FAIL]; // Upload failed
    }

}