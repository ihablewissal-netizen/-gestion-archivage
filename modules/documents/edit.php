<?php
$pageTitle = 'Modifier un document archivé';
$pageIcon  = 'pencil';
require_once __DIR__ . '/../../includes/header.php';

$pdo   = getPDO();
$role  = $_SESSION['role'];
$uid   = $_SESSION['user_id'];
$id    = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare("SELECT * FROM documents_archives WHERE id=?");
$stmt->execute([$id]);
$data = $stmt->fetch();
if (!$data) {
    setFlash('danger', 'Document introuvable.');
    redirect(APP_URL . '/modules/documents/index.php');
}

// Security: user can only edit own docs
if ($role !== 'admin' && $data['uploaded_by'] != $uid) {
    setFlash('danger', 'Accès refusé.');
    redirect(APP_URL . '/modules/documents/index.php');
}

$foncs = $pdo->query("SELECT matricule, CONCAT(prenom,' ',nom) as nom_complet, prenom, nom FROM fonctionnaires ORDER BY nom")->fetchAll();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $upd = [
        'matricule'         => trim($_POST['matricule'] ?? ''),
        'nom_fonctionnaire' => trim($_POST['nom_fonctionnaire'] ?? ''),
        'prenom'            => trim($_POST['prenom'] ?? ''),
        'type_document'     => trim($_POST['type_document'] ?? ''),
        'titre_document'    => trim($_POST['titre_document'] ?? ''),
    ];
    if (!$upd['titre_document']) $errors[] = 'Le titre est requis.';
    if (!$upd['matricule'])      $errors[] = 'Le matricule est requis.';

    $nom_fichier = $data['nom_fichier']; // keep existing
    if (isset($_FILES['fichier']) && $_FILES['fichier']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['fichier']['name'], PATHINFO_EXTENSION));
        $allowed = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png', 'gif', 'txt', 'zip'];
        if (!in_array($ext, $allowed)) {
            $errors[] = 'Type de fichier non autorisé.';
        } elseif ($_FILES['fichier']['size'] > 10 * 1024 * 1024) {
            $errors[] = 'Fichier trop volumineux (max 10MB).';
        } else {
            $newName = date('Ymd_His') . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $_FILES['fichier']['name']);
            if (!is_dir(UPLOAD_DIR)) mkdir(UPLOAD_DIR, 0755, true);
            if (move_uploaded_file($_FILES['fichier']['tmp_name'], UPLOAD_DIR . $newName)) {
                // Delete old file
                if ($data['nom_fichier'] && file_exists(UPLOAD_DIR . $data['nom_fichier'])) @unlink(UPLOAD_DIR . $data['nom_fichier']);
                $nom_fichier = $newName;
            } else {
                $errors[] = 'Erreur lors de l\'envoi du fichier.';
            }
        }
    }

    if (empty($errors)) {
        $pdo->prepare("UPDATE documents_archives SET matricule=?,nom_fonctionnaire=?,prenom=?,type_document=?,titre_document=?,nom_fichier=? WHERE id=?")
            ->execute([$upd['matricule'], $upd['nom_fonctionnaire'], $upd['prenom'], $upd['type_document'], $upd['titre_document'], $nom_fichier, $id]);
        // merge for display
        $data = array_merge($data, $upd, ['nom_fichier' => $nom_fichier]);
        setFlash('success', 'Document mis à jour.');
        redirect(APP_URL . '/modules/documents/index.php');
    }
}
$types = ['Décision', 'Arrêté', 'Attestation', 'Contrat', 'Rapport', 'Lettre', 'Circulaire', 'Note de service', 'Certificat', 'Autre'];
?>
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= APP_URL ?>/dashboard.php">Tableau de bord</a></li>
        <li class="breadcrumb-item"><a href="index.php">Documents archivés</a></li>
        <li class="breadcrumb-item active">Modifier</li>
    </ol>
</nav>
<?php if ($errors): ?><div class="alert alert-danger mb-3">
        <ul class="mb-0"><?php foreach ($errors as $e): ?><li><?= clean($e) ?></li><?php endforeach; ?></ul>
    </div><?php endif; ?>
<div class="card" style="max-width:700px;">
    <div class="card-header"><span><i class="bi bi-pencil me-2 text-warning"></i>Modifier le document archivé</span></div>
    <div class="card-body p-4">
        <form method="POST" enctype="multipart/form-data">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Fonctionnaire <span class="text-danger">*</span></label>
                    <select class="form-select" name="matricule" id="fonctionnaire_select" required>
                        <option value="">-- Sélectionner --</option>
                        <?php foreach ($foncs as $f): ?>
                            <option value="<?= clean($f['matricule']) ?>" data-nom="<?= clean($f['nom']) ?>" data-prenom="<?= clean($f['prenom']) ?>" <?= $data['matricule'] == $f['matricule'] ? 'selected' : '' ?>><?= clean($f['matricule']) ?> — <?= clean($f['nom_complet']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3"><label class="form-label">Nom</label><input type="text" class="form-control" name="nom_fonctionnaire" id="nom_input" value="<?= clean($data['nom_fonctionnaire']) ?>"></div>
                <div class="col-md-3"><label class="form-label">Prénom</label><input type="text" class="form-control" name="prenom" id="prenom_input" value="<?= clean($data['prenom']) ?>"></div>
                <div class="col-md-6"><label class="form-label">Titre du document <span class="text-danger">*</span></label><input type="text" class="form-control" name="titre_document" value="<?= clean($data['titre_document']) ?>" required></div>
                <div class="col-md-6">
                    <label class="form-label">Type de document</label>
                    <select class="form-select" name="type_document">
                        <option value="">-- Sélectionner --</option><?php foreach ($types as $t): ?><option value="<?= $t ?>" <?= $data['type_document'] === $t ? 'selected' : '' ?>><?= $t ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label">Nouveau fichier (optionnel)</label>
                    <?php if ($data['nom_fichier']): ?>
                        <div class="mb-2 p-2 bg-light rounded d-flex align-items-center gap-2">
                            <i class="bi bi-file-earmark text-primary"></i>
                            <span style="font-size:13px;"><?= clean($data['nom_fichier']) ?></span>
                            <a href="<?= APP_URL ?>/modules/documents/download.php?id=<?= $id ?>" class="btn btn-sm btn-outline-primary ms-auto">
                                <i class="bi bi-download me-1"></i>Télécharger
                            </a>
                        </div>
                    <?php endif; ?>
                    <input type="file" class="form-control" name="fichier" accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png,.gif,.txt,.zip">
                    <div class="form-text">Laissez vide pour garder le fichier actuel. Max 10MB.</div>
                </div>
            </div>
            <div class="section-divider"></div>
            <div class="d-flex gap-2"><button type="submit" class="btn btn-navy px-4"><i class="bi bi-check-lg me-2"></i>Mettre à jour</button><a href="index.php" class="btn btn-outline-secondary">Annuler</a></div>
        </form>
    </div>
</div>
<script>
    const sel = document.getElementById('fonctionnaire_select');
    if (sel) {
        sel.addEventListener('change', function() {
            const o = this.options[this.selectedIndex];
            document.getElementById('nom_input').value = o.getAttribute('data-nom') || '';
            document.getElementById('prenom_input').value = o.getAttribute('data-prenom') || '';
        });
    }
</script>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>