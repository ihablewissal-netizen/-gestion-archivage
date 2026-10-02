<?php
$pageTitle = 'Modifier une affectation';
$pageIcon  = 'pencil';
require_once __DIR__ . '/../../includes/header.php';
requireAdmin();

$pdo   = getPDO();
$id = (int)($_GET['id'] ?? 0);
$stmt  = $pdo->prepare("SELECT * FROM affectations WHERE id=?");
$stmt->execute([$id]);
$data  = $stmt->fetch();
if (!$data) {
    setFlash('danger', 'Affectation introuvable.');
    redirect(APP_URL . '/modules/affectations/index.php');
}

$foncs = $pdo->query("SELECT matricule, CONCAT(prenom,' ',nom) as nom_complet FROM fonctionnaires ORDER BY nom")->fetchAll();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $matricule = trim($_POST['matricule'] ?? '');
    $poste_occupe = trim($_POST['poste_occupe'] ?? '');
    $date_debut = trim($_POST['date_debut'] ?? '');
    $date_fin = trim($_POST['date_fin'] ?? '');

    if (!$matricule) {
        $errors[] = 'Le matricule est requis.';
    }
    if (!$poste_occupe) {
        $errors[] = 'Le poste occupé est requis.';
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("UPDATE affectations SET matricule=?, poste_occupe=?, date_debut=?, date_fin=? WHERE id=?");
        $stmt->execute([
            $matricule,
            $poste_occupe,
            $date_debut ?: null,
            $date_fin ?: null,
            $id
        ]);
        setFlash('success', 'Affectation mise à jour avec succès.');
        redirect(APP_URL . '/modules/affectations/index.php');
    }
}
?>

<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= APP_URL ?>/dashboard.php">Tableau de bord</a></li>
        <li class="breadcrumb-item"><a href="index.php">Affectations</a></li>
        <li class="breadcrumb-item active">Modifier</li>
    </ol>
</nav>

<?php if ($errors): ?>
    <div class="alert alert-danger mb-3">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        <strong>Veuillez corriger les erreurs :</strong>
        <ul class="mb-0 mt-2">
            <?php foreach ($errors as $e): ?>
                <li><?= clean($e) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<div class="card" style="max-width:600px;">
    <div class="card-header">
        <span><i class="bi bi-pencil me-2 text-warning"></i>Modifier l'affectation</span>
    </div>
    <div class="card-body p-4">
        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Fonctionnaire <span class="text-danger">*</span></label>
                <select class="form-select" name="matricule" required>
                    <option value="">-- Sélectionner --</option>
                    <?php foreach ($foncs as $f): ?>
                        <option value="<?= clean($f['matricule']) ?>"
                            <?= $data['matricule'] == $f['matricule'] ? 'selected' : '' ?>>
                            <?= clean($f['matricule']) ?> — <?= clean($f['nom_complet']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Poste occupé <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="poste_occupe"
                    value="<?= clean($data['poste_occupe']) ?>" required>
            </div>

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Date de début</label>
                    <input type="date" class="form-control" name="date_debut"
                        value="<?= clean($data['date_debut']) ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Date de fin</label>
                    <input type="date" class="form-control" name="date_fin"
                        value="<?= clean($data['date_fin']) ?>">
                    <small class="text-muted">Laisser vide si en cours</small>
                </div>
            </div>

            <div style="margin-top: 30px; margin-bottom: 30px; border-top: 1px solid #e9ecef; padding-top: 20px;"></div>

            <div class="d-flex gap-2" style="margin-bottom: 30px;">
                <button type="submit" class="btn btn-navy px-4 py-2" style="min-width: 120px;">
                    <i class="bi bi-check-lg me-2"></i>Mettre à jour
                </button>
                <a href="index.php" class="btn btn-outline-secondary px-4 py-2" style="min-width: 120px;">
                    <i class="bi bi-x-lg me-1"></i>Annuler
                </a>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>