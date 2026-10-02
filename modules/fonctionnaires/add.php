<?php
$pageTitle = 'Ajouter un fonctionnaire';
$pageIcon  = 'person-plus';
require_once __DIR__ . '/../../includes/header.php';
requireAdmin();

$errors = [];
$data   = ['matricule' => '', 'nom' => '', 'prenom' => '', 'date_naissance' => '', 'cin' => '', 'telephone' => '', 'statut' => 'Actif'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
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
        try {
            $pdo  = getPDO();
            // Check unique matricule
            $chk = $pdo->prepare("SELECT id FROM fonctionnaires WHERE matricule = ?");
            $chk->execute([$data['matricule']]);
            if ($chk->fetch()) {
                $errors[] = 'Ce matricule existe déjà.';
            } else {
                $stmt = $pdo->prepare("INSERT INTO fonctionnaires (matricule,nom,prenom,date_naissance,cin,telephone,statut) VALUES (?,?,?,?,?,?,?)");
                $stmt->execute([
                    $data['matricule'],
                    $data['nom'],
                    $data['prenom'],
                    $data['date_naissance'] ?: null,
                    $data['cin'],
                    $data['telephone'],
                    $data['statut']
                ]);
                setFlash('success', 'Fonctionnaire ajouté avec succès.');
                redirect(APP_URL . '/modules/fonctionnaires/index.php');
            }
        } catch (PDOException $e) {
            $errors[] = 'Erreur base de données: ' . $e->getMessage();
        }
    }
}
?>

<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= APP_URL ?>/dashboard.php">Tableau de bord</a></li>
        <li class="breadcrumb-item"><a href="index.php">Fonctionnaires</a></li>
        <li class="breadcrumb-item active">Ajouter</li>
    </ol>
</nav>

<?php if ($errors): ?>
    <div class="alert alert-danger mb-3">
        <i class="bi bi-exclamation-triangle me-2"></i>
        <strong>Veuillez corriger les erreurs suivantes :</strong>
        <ul class="mb-0 mt-2">
            <?php foreach ($errors as $e): ?><li><?= clean($e) ?></li><?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<div class="card" style="max-width:700px;">
    <div class="card-header">
        <span><i class="bi bi-person-plus me-2 text-success"></i>Nouveau fonctionnaire</span>
    </div>
    <div class="card-body p-4">
        <form method="POST">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Matricule <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="matricule" value="<?= clean($data['matricule']) ?>" required >
                </div>
                <div class="col-md-4">
                    <label class="form-label">Nom <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="nom" value="<?= clean($data['nom']) ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Prénom <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="prenom" value="<?= clean($data['prenom']) ?>" required >
                </div>
                <div class="col-md-4">
                    <label class="form-label">Date de naissance</label>
                    <input type="date" class="form-control" name="date_naissance" value="<?= clean($data['date_naissance']) ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">CIN</label>
                    <input type="text" class="form-control" name="cin" value="<?= clean($data['cin']) ?>" >
                </div>
                <div class="col-md-4">
                    <label class="form-label">Téléphone</label>
                    <input type="tel" class="form-control" name="telephone" value="<?= clean($data['telephone']) ?>">
                </div>
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
                <button type="submit" class="btn btn-navy px-4">
                    <i class="bi bi-check-lg me-2"></i>Enregistrer
                </button>
                <a href="index.php" class="btn btn-outline-secondary">
                    <i class="bi bi-x me-1"></i>Annuler
                </a>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>