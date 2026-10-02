<?php
$pageTitle = 'Modifier un fonctionnaire';
$pageIcon  = 'pencil';
require_once __DIR__ . '/../../includes/header.php';
requireAdmin();

$pdo = getPDO();
$id  = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare("SELECT * FROM fonctionnaires WHERE id = ?");
$stmt->execute([$id]);
$data = $stmt->fetch();
if (!$data) {
    setFlash('danger', 'Fonctionnaire introuvable.');
    redirect(APP_URL . '/modules/fonctionnaires/index.php');
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'id'             => $id,
        'matricule'      => trim($_POST['matricule'] ?? ''),
        'nom'            => trim($_POST['nom'] ?? ''),
        'prenom'         => trim($_POST['prenom'] ?? ''),
        'date_naissance' => trim($_POST['date_naissance'] ?? ''),
        'cin'            => trim($_POST['cin'] ?? ''),
        'telephone'      => trim($_POST['telephone'] ?? ''),
        'statut'         => trim($_POST['statut'] ?? 'Actif'),
    ];
    if (!$data['matricule']) $errors[] = 'Le matricule est requis.';
    if (!$data['nom'])       $errors[] = 'Le nom est requis.';
    if (!$data['prenom'])    $errors[] = 'Le prénom est requis.';

    if (empty($errors)) {
        $chk = $pdo->prepare("SELECT id FROM fonctionnaires WHERE matricule = ? AND id != ?");
        $chk->execute([$data['matricule'], $id]);
        if ($chk->fetch()) {
            $errors[] = 'Ce matricule est déjà utilisé.';
        } else {
            $stmt = $pdo->prepare("UPDATE fonctionnaires SET matricule=?,nom=?,prenom=?,date_naissance=?,cin=?,telephone=?,statut=? WHERE id=?");
            $stmt->execute([$data['matricule'], $data['nom'], $data['prenom'], $data['date_naissance'] ?: null, $data['cin'], $data['telephone'], $data['statut'], $id]);
            setFlash('success', 'Fonctionnaire mis à jour.');
            redirect(APP_URL . '/modules/fonctionnaires/index.php');
        }
    }
}
?>
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= APP_URL ?>/dashboard.php">Tableau de bord</a></li>
        <li class="breadcrumb-item"><a href="index.php">Fonctionnaires</a></li>
        <li class="breadcrumb-item active">Modifier</li>
    </ol>
</nav>
<?php if ($errors): ?>
    <div class="alert alert-danger mb-3"><i class="bi bi-exclamation-triangle me-2"></i>
        <ul class="mb-0"><?php foreach ($errors as $e): ?><li><?= clean($e) ?></li><?php endforeach; ?></ul>
    </div>
<?php endif; ?>
<div class="card" style="max-width:700px;">
    <div class="card-header"><span><i class="bi bi-pencil me-2 text-warning"></i>Modifier le fonctionnaire</span></div>
    <div class="card-body p-4">
        <form method="POST">
            <div class="row g-3">
                <div class="col-md-4"><label class="form-label">Matricule <span class="text-danger">*</span></label><input type="text" class="form-control" name="matricule" value="<?= clean($data['matricule']) ?>" required></div>
                <div class="col-md-4"><label class="form-label">Nom <span class="text-danger">*</span></label><input type="text" class="form-control" name="nom" value="<?= clean($data['nom']) ?>" required></div>
                <div class="col-md-4"><label class="form-label">Prénom <span class="text-danger">*</span></label><input type="text" class="form-control" name="prenom" value="<?= clean($data['prenom']) ?>" required></div>
                <div class="col-md-4"><label class="form-label">Date de naissance</label><input type="date" class="form-control" name="date_naissance" value="<?= clean($data['date_naissance']) ?>"></div>
                <div class="col-md-4"><label class="form-label">CIN</label><input type="text" class="form-control" name="cin" value="<?= clean($data['cin']) ?>"></div>
                <div class="col-md-4"><label class="form-label">Téléphone</label><input type="tel" class="form-control" name="telephone" value="<?= clean($data['telephone']) ?>"></div>
                <div class="col-md-4">
                    <label class="form-label">Statut</label>
                    <select class="form-select" name="statut">
                        <option value="Actif" <?= $data['statut'] === 'Actif'    ? 'selected' : '' ?>>Actif</option>
                        <option value="Inactif" <?= $data['statut'] === 'Inactif'  ? 'selected' : '' ?>>Inactif</option>
                        <option value="Retraité" <?= $data['statut'] === 'Retraité' ? 'selected' : '' ?>>Retraité</option>
                    </select>
                </div>
            </div>
            <div class="section-divider"></div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-navy px-4"><i class="bi bi-check-lg me-2"></i>Mettre à jour</button>
                <a href="index.php" class="btn btn-outline-secondary"><i class="bi bi-x me-1"></i>Annuler</a>
            </div>
        </form>
    </div>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>