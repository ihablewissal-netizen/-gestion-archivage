<?php
$pageTitle = 'Ajouter une carrière';
$pageIcon  = 'graph-up-arrow';
require_once __DIR__ . '/../../includes/header.php';
requireAdmin();

$pdo   = getPDO();
$foncs = $pdo->query("SELECT matricule, CONCAT(prenom,' ',nom) as nom_complet FROM fonctionnaires ORDER BY nom")->fetchAll();
$errors = [];
$data   = ['matricule' => '', 'date_debut' => '', 'date_fin' => '', 'description' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = ['matricule' => trim($_POST['matricule'] ?? ''), 'date_debut' => trim($_POST['date_debut'] ?? ''), 'date_fin' => trim($_POST['date_fin'] ?? ''), 'description' => trim($_POST['description'] ?? '')];
    if (!$data['matricule']) $errors[] = 'Le matricule est requis.';
    if (empty($errors)) {
        $pdo->prepare("INSERT INTO carrieres (matricule,date_debut,date_fin,description) VALUES(?,?,?,?)")->execute([$data['matricule'], $data['date_debut'] ?: null, $data['date_fin'] ?: null, $data['description']]);
        setFlash('success', 'Carrière ajoutée.');
        redirect(APP_URL . '/modules/carrieres/index.php');
    }
}
?>
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= APP_URL ?>/dashboard.php">Tableau de bord</a></li>
        <li class="breadcrumb-item"><a href="index.php">Carrières</a></li>
        <li class="breadcrumb-item active">Ajouter</li>
    </ol>
</nav>
<?php if ($errors): ?><div class="alert alert-danger mb-3">
        <ul class="mb-0"><?php foreach ($errors as $e): ?><li><?= clean($e) ?></li><?php endforeach; ?></ul>
    </div><?php endif; ?>
<div class="card" style="max-width:600px;">
    <div class="card-header"><span><i class="bi bi-graph-up-arrow me-2 text-success"></i>Nouvelle entrée de carrière</span></div>
    <div class="card-body p-4">
        <form method="POST">
            <div class="mb-3"><label class="form-label">Fonctionnaire <span class="text-danger">*</span></label><select class="form-select" name="matricule" required>
                    <option value="">-- Sélectionner --</option><?php foreach ($foncs as $f): ?><option value="<?= clean($f['matricule']) ?>" <?= $data['matricule'] == $f['matricule'] ? 'selected' : '' ?>><?= clean($f['matricule']) ?> — <?= clean($f['nom_complet']) ?></option><?php endforeach; ?>
                </select></div>
            <div class="row g-3 mb-3">
                <div class="col-md-6"><label class="form-label">Date de début</label><input type="date" class="form-control" name="date_debut" value="<?= clean($data['date_debut']) ?>"></div>
                <div class="col-md-6"><label class="form-label">Date de fin</label><input type="date" class="form-control" name="date_fin" value="<?= clean($data['date_fin']) ?>"><small class="text-muted">Laisser vide si en cours</small></div>
            </div>
            <div class="mb-3"><label class="form-label">Description</label><textarea class="form-control" name="description" rows="4" placeholder="Détails de la carrière..."><?= clean($data['description']) ?></textarea></div>
            <div class="d-flex gap-2"><button type="submit" class="btn btn-navy px-4"><i class="bi bi-check-lg me-2"></i>Enregistrer</button><a href="index.php" class="btn btn-outline-secondary">Annuler</a></div>
        </form>
    </div>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>