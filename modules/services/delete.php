<?php
session_start();
require_once __DIR__ . '/../../config/database.php';
requireLogin();
requireAdmin();
$pdo = getPDO();
$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("DELETE FROM service WHERE id=?");
$stmt->execute([$id]);
setFlash($stmt->rowCount() ? 'success' : 'danger', $stmt->rowCount() ? 'Service supprimé.' : 'Introuvable.');
redirect(APP_URL . '/modules/services/index.php');
