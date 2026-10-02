<?php

session_start();
require_once __DIR__ . '/../../config/database.php';
requireLogin();

$pdo  = getPDO();
$id   = (int)($_GET['id'] ?? 0);
$role = $_SESSION['role'];
$uid  = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT * FROM documents_archives WHERE id=?");
$stmt->execute([$id]);
$doc  = $stmt->fetch();

if (!$doc) {
    die('Document introuvable.');
}


if ($role !== 'admin' && $doc['uploaded_by'] != $uid) {
    die('Accès refusé.');
}

$filepath = UPLOAD_DIR . $doc['nom_fichier'];
if (!$doc['nom_fichier'] || !file_exists($filepath)) {
    die('Fichier non trouvé sur le serveur.');
}

$mimeTypes = [
    'pdf'  => 'application/pdf',
    'doc'  => 'application/msword',
    'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'xls'  => 'application/vnd.ms-excel',
    'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    'jpg'  => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'png'  => 'image/png',
    'gif'  => 'image/gif',
    'txt'  => 'text/plain',
    'zip'  => 'application/zip',
];

$ext      = strtolower(pathinfo($doc['nom_fichier'], PATHINFO_EXTENSION));
$mimeType = $mimeTypes[$ext] ?? 'application/octet-stream';

$print = isset($_GET['print']);

header('Content-Type: ' . $mimeType);

if ($print) {
    // afficher dans le navigateur
    header('Content-Disposition: inline; filename="' . $doc['nom_fichier'] . '"');
} else {
    // téléchargement normal
    header('Content-Disposition: attachment; filename="' . $doc['nom_fichier'] . '"');
}
header('Content-Length: ' . filesize($filepath));
header('Cache-Control: no-cache, must-revalidate');
readfile($filepath);
