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
    setFlash('danger', 'Document introuvable.');
    redirect(APP_URL . '/modules/documents/index.php');
}

// Security
if ($role !== 'admin' && $doc['uploaded_by'] != $uid) {
    setFlash('danger', 'Accès refusé. Vous ne pouvez supprimer que vos propres documents.');
    redirect(APP_URL . '/modules/documents/index.php');
}

// Delete file from disk
if ($doc['nom_fichier'] && file_exists(UPLOAD_DIR . $doc['nom_fichier'])) {
    @unlink(UPLOAD_DIR . $doc['nom_fichier']);
}

$pdo->prepare("DELETE FROM documents_archives WHERE id=?")->execute([$id]);
setFlash('success', 'Document supprimé avec succès.');
redirect(APP_URL . '/modules/documents/index.php');
