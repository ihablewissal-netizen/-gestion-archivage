<?php
session_start();
require_once __DIR__ . '/../../config/database.php';
requireLogin();
requireAdmin();

$pdo = getPDO();
$id  = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare("DELETE FROM fonctionnaires WHERE id = ?");
$stmt->execute([$id]);

if ($stmt->rowCount()) {
    setFlash('success', 'Fonctionnaire supprimé avec succès.');
} else {
    setFlash('danger', 'Fonctionnaire introuvable.');
}
redirect(APP_URL . '/modules/fonctionnaires/index.php');
