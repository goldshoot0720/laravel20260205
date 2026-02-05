<?php
require_once 'includes/functions.php';

// 建立上傳目錄
$uploadDir = 'uploads/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['error' => '請使用 POST 方法'], 400);
}

if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    jsonResponse(['error' => '請上傳檔案'], 400);
}

$file = $_FILES['file'];
$originalName = $file['name'];
$fileType = $file['type'];
$fileSize = $file['size'];

// 檢查檔案大小 (最大 10MB)
if ($fileSize > 10 * 1024 * 1024) {
    jsonResponse(['error' => '檔案大小不能超過 10MB'], 400);
}

// 生成唯一檔名
$ext = pathinfo($originalName, PATHINFO_EXTENSION);
$newName = generateUUID() . '.' . $ext;
$filePath = $uploadDir . $newName;

if (move_uploaded_file($file['tmp_name'], $filePath)) {
    jsonResponse([
        'success' => true,
        'file' => $filePath,
        'filename' => $originalName,
        'filetype' => $fileType
    ]);
} else {
    jsonResponse(['error' => '檔案上傳失敗'], 500);
}
