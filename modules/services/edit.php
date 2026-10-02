<?php
$pageTitle = 'Modifier un service';
$pageIcon  = 'pencil';
require_once __DIR__ . '/../../includes/header.php';
requireAdmin();

$pdo = getPDO();
$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM service WHERE id=?");
$stmt->execute([$id]);
$data = $stmt->fetch();
if (!$data) {
    setFlash('danger', 'Service introuvable.');
    redirect(APP_URL . '/modules/services/index.php');
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = ['id' => $id, 'matricule_service' => trim($_POST['matricule_service'] ?? ''), 'nom_service' => trim($_POST['nom_service'] ?? ''), 'description' => trim($_POST['description'] ?? '')];
    if (!$data['matricule_service']) $errors[] = 'Le code service est requis.';
    if (!$data['nom_service'])       $errors[] = 'Le nom du service est requis.';
    if (empty($errors)) {
        $chk = $pdo->prepare("SELECT id FROM service WHERE matricule_service=? AND id!=?");
        $chk->execute([$data['matricule_service'], $id]);
        if ($chk->fetch()) {
            $errors[] = 'Ce code service est déjà utilisé.';
        } else {
            $pdo->prepare("UPDATE service SET matricule_service=?,nom_service=?,description=? WHERE id=?")->execute([$data['matricule_service'], $data['nom_service'], $data['description'], $id]);
            setFlash('success', 'Service mis à jour.');
            redirect(APP_URL . '/modules/services/index.php');
        }
    }
}
?>
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= APP_URL ?>/dashboard.php">Tableau de bord</a></li>
        <li class="breadcrumb-item"><a href="index.php">Services</a></li>
        <li class="breadcrumb-item active">Modifier</li>
    </ol>
</nav>
<?php if ($errors): ?><div class="alert alert-danger mb-3">
        <ul class="mb-0"><?php foreach ($errors as $e): ?><li><?= clean($e) ?></li><?php endforeach; ?></ul>
    </div><?php endif; ?>
<div class="card" style="max-width:600px;">
    <div class="card-header"><span><i class="bi bi-pencil me-2 text-warning"></i>Modifier le service</span></div>
    <div class="card-body p-4">
        <form method="POST">
            <div class="mb-3"><label class="form-label">Code Service <span class="text-danger">*</span></label><input type="text" class="form-control" name="matricule_service" value="<?= clean($data['matricule_service']) ?>" required></div>
            <div class="mb-3"><label class="form-label">Nom du Service <span class="text-danger">*</span></label><input type="text" class="form-control" name="nom_service" value="<?= clean($data['nom_service']) ?>" required></div>
            <div class="mb-3"><label class="form-label">Description</label><textarea class="form-control" name="description" rows="4"><?= clean($data['description'] ?? '') ?></textarea></div>
            <div class="d-flex gap-2"><button type="submit" class="btn btn-navy px-4"><i class="bi bi-check-lg me-2"></i>Mettre à jour</button><a href="index.php" class="btn btn-outline-secondary">Annuler</a></div>
        </form>
    </div>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>