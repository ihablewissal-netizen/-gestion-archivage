<?php
$pageTitle = 'Modifier une situation administrative';
$pageIcon  = 'pencil';
require_once __DIR__ . '/../../includes/header.php';
requireAdmin();

$pdo = $getPDO = getPDO();
$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM situations_administratives WHERE id=?");
$stmt->execute([$id]);
$data = $stmt->fetch();
if (!$data) {
    setFlash('danger', 'Situation introuvable.');
    redirect(APP_URL . '/modules/situations/index.php');
}

$foncs = $pdo->query("SELECT matricule, CONCAT(prenom,' ',nom) as nom_complet FROM fonctionnaires ORDER BY nom")->fetchAll();
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = ['id' => $id, 'matricule' => trim($_POST['matricule'] ?? ''), 'grade' => trim($_POST['grade'] ?? ''), 'echelle' => trim($_POST['echelle'] ?? ''), 'echelon' => trim($_POST['echelon'] ?? '')];
    if (!$data['matricule']) $errors[] = 'Le matricule est requis.';
    if (empty($errors)) {
        $pdo->prepare("UPDATE situations_administratives SET matricule=?,grade=?,echelle=?,echelon=? WHERE id=?")->execute([$data['matricule'], $data['grade'], $data['echelle'], $data['echelon'], $id]);
        setFlash('success', 'Situation mise à jour.');
        redirect(APP_URL . '/modules/situations/index.php');
    }
}
?>
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= APP_URL ?>/dashboard.php">Tableau de bord</a></li>
        <li class="breadcrumb-item"><a href="index.php">Situations</a></li>
        <li class="breadcrumb-item active">Modifier</li>
    </ol>
</nav>
<?php if ($errors): ?><div class="alert alert-danger mb-3">
        <ul class="mb-0"><?php foreach ($errors as $e): ?><li><?= clean($e) ?></li><?php endforeach; ?></ul>
    </div><?php endif; ?>
<div class="card" style="max-width:600px;">
    <div class="card-header"><span><i class="bi bi-pencil me-2 text-warning"></i>Modifier la situation administrative</span></div>
    <div class="card-body p-4">
        <form method="POST">
            <div class="mb-3"><label class="form-label">Fonctionnaire <span class="text-danger">*</span></label><select class="form-select" name="matricule" required>
                    <option value="">-- Sélectionner --</option><?php foreach ($foncs as $f): ?><option value="<?= clean($f['matricule']) ?>" <?= $data['matricule'] == $f['matricule'] ? 'selected' : '' ?>><?= clean($f['matricule']) ?> — <?= clean($f['nom_complet']) ?></option><?php endforeach; ?>
                </select></div>
            <div class="mb-3"><label class="form-label">Grade</label><input type="text" class="form-control" name="grade" value="<?= clean($data['grade']) ?>"></div>
            <div class="row g-3">
                <div class="col-md-6"><label class="form-label">Échelle</label><input type="text" class="form-control" name="echelle" value="<?= clean($data['echelle']) ?>"></div>
                <div class="col-md-6"><label class="form-label">Échelon</label><input type="text" class="form-control" name="echelon" value="<?= clean($data['echelon']) ?>"></div>
            </div>
            <div class="section-divider"></div>
            <div class="d-flex gap-2"><button type="submit" class="btn btn-navy px-4"><i class="bi bi-check-lg me-2"></i>Mettre à jour</button><a href="index.php" class="btn btn-outline-secondary">Annuler</a></div>
        </form>
    </div>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>