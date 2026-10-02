<?php
session_start();
require_once __DIR__ . '/../../config/database.php';
requireLogin();
requireAdmin();
$pdo = getPDO();
$id = (int)($_GET['id'] ?? 0);
if ($id == $_SESSION['user_id']) {
    setFlash('danger', 'Vous ne pouvez pas supprimer votre propre compte.');
    redirect(APP_URL . '/modules/utilisateurs/index.php');
}
$stmt = $pdo->prepare("DELETE FROM utilisateurs WHERE id=?");
$stmt->execute([$id]);
setFlash($stmt->rowCount() ? 'success' : 'danger', $stmt->rowCount() ? 'Utilisateur supprimé.' : 'Introuvable.');
redirect(APP_URL . '/modules/utilisateurs/index.php');
