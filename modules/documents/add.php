<?php
$pageTitle = 'Archiver un document';
$pageIcon  = 'file-earmark-plus';
require_once __DIR__ . '/../../includes/header.php';

$pdo   = getPDO();
$role  = $_SESSION['role'];
$uid   = $_SESSION['user_id'];
$mat   = $_SESSION['matricule'] ?? '';

// Fonctionnaires list (admin sees all, user only sees self)
if ($role === 'admin') {
    $foncs = $pdo->query("SELECT matricule, CONCAT(prenom,' ',nom) as nom_complet, prenom, nom FROM fonctionnaires ORDER BY nom")->fetchAll();
} else {
    $foncs = $pdo->prepare("SELECT matricule, CONCAT(prenom,' ',nom) as nom_complet, prenom, nom FROM fonctionnaires WHERE matricule=?");
    $foncs->execute([$mat]);
    $foncs = $foncs->fetchAll();
}

$errors = [];
$data   = ['matricule' => $mat, 'nom_fonctionnaire' => '', 'prenom' => '', 'type_document' => '', 'titre_document' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'matricule'        => trim($_POST['matricule'] ?? ''),
        'nom_fonctionnaire' => trim($_POST['nom_fonctionnaire'] ?? ''),
        'prenom'           => trim($_POST['prenom'] ?? ''),
        'type_document'    => trim($_POST['type_document'] ?? ''),
        'titre_document'   => trim($_POST['titre_document'] ?? ''),
    ];

    // Security: non-admin can only use their own matricule
    if ($role !== 'admin' && $data['matricule'] !== $mat) {
        $errors[] = 'Vous ne pouvez archiver que vos propres documents.';
    }
    if (!$data['titre_document']) $errors[] = 'Le titre du document est requis.';
    if (!$data['matricule'])      $errors[] = 'Le matricule est requis.';

    $nom_fichier = null;
    // Handle file upload
    if (isset($_FILES['fichier']) && $_FILES['fichier']['error'] === UPLOAD_ERR_OK) {
        $tmpName  = $_FILES['fichier']['tmp_name'];
        $origName = $_FILES['fichier']['name'];
        $ext      = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
        $allowed  = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png', 'gif', 'txt', 'zip'];

        if (!in_array($ext, $allowed)) {
            $errors[] = 'Type de fichier non autorisé. Types acceptés: ' . implode(', ', $allowed);
        } elseif ($_FILES['fichier']['size'] > 10 * 1024 * 1024) {
            $errors[] = 'Fichier trop volumineux (max 10MB).';
        } else {
            $nom_fichier = date('Ymd_His') . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $origName);
            if (!is_dir(UPLOAD_DIR)) mkdir(UPLOAD_DIR, 0755, true);
            if (!move_uploaded_file($tmpName, UPLOAD_DIR . $nom_fichier)) {
                $errors[] = 'Erreur lors de l\'envoi du fichier.';
                $nom_fichier = null;
            }
        }
    } elseif (isset($_FILES['fichier']) && $_FILES['fichier']['error'] !== UPLOAD_ERR_NO_FILE) {
        $errors[] = 'Erreur d\'upload (code: ' . $_FILES['fichier']['error'] . ')';
    }

    if (empty($errors)) {
        $pdo->prepare("INSERT INTO documents_archives 
        (matricule,nom_fonctionnaire,prenom,type_document,titre_document,nom_fichier,date_upload,uploaded_by) 
        VALUES(?,?,?,?,?,?,CURDATE(),?)")
            ->execute([$data['matricule'], $data['nom_fonctionnaire'], $data['prenom'], $data['type_document'],
             $data['titre_document'], $nom_fichier, $uid]);
        setFlash('success', 'Document archivé avec succès.');
        redirect(APP_URL . '/modules/documents/index.php');
    }
}
?>
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= APP_URL ?>/dashboard.php">Tableau de bord</a></li>
        <li class="breadcrumb-item"><a href="index.php">Documents archivés</a></li>
        <li class="breadcrumb-item active">Archiver</li>
    </ol>
</nav>

<?php if ($errors): ?><div class="alert alert-danger mb-3"><i class="bi bi-exclamation-triangle me-2"></i>
        <ul class="mb-0"><?php foreach ($errors as $e): ?><li><?= clean($e) ?></li><?php endforeach; ?></ul>
    </div><?php endif; ?>

<div class="card" style="max-width:700px;">
    <div class="card-header"><span><i class="bi bi-file-earmark-plus me-2 text-success"></i>Archiver un nouveau document</span></div>
    <div class="card-body p-4">
        <form method="POST" enctype="multipart/form-data">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Fonctionnaire <span class="text-danger">*</span></label>
                    <select class="form-select" name="matricule" id="fonctionnaire_select" required <?= $role !== 'admin' ? 'disabled' : '' ?>>
                        <option value="">-- Sélectionner --</option>
                        <?php foreach ($foncs as $f): ?>
                            <option value="<?= clean($f['matricule']) ?>"
                                data-nom="<?= clean($f['nom']) ?>"
                                data-prenom="<?= clean($f['prenom']) ?>"
                                <?= $data['matricule'] == $f['matricule'] ? 'selected' : '' ?>>
                                <?= clean($f['matricule']) ?> — <?= clean($f['nom_complet']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if ($role !== 'admin'): ?>
                        <input type="hidden" name="matricule" value="<?= clean($mat) ?>">
                    <?php endif; ?>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Nom</label>
                    <input type="text" class="form-control" name="nom_fonctionnaire" id="nom_input" value="<?= clean($data['nom_fonctionnaire']) ?>" placeholder="Auto-rempli">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Prénom</label>
                    <input type="text" class="form-control" name="prenom" id="prenom_input" value="<?= clean($data['prenom']) ?>" placeholder="Auto-rempli">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Titre du document <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="titre_document" value="<?= clean($data['titre_document']) ?>" required placeholder="ex: Décision de promotion 2024">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Type de document</label>
                    <select class="form-select" name="type_document">
                        <option value="">-- Sélectionner --</option>
                        <?php
                        $types = ['Décision', 'Arrêté', 'Attestation', 'Contrat', 'Rapport', 'Lettre', 'Circulaire', 'Note de service', 'Certificat', 'Autre'];
                        foreach ($types as $t): ?>
                            <option value="<?= $t ?>" <?= $data['type_document'] === $t ? 'selected' : '' ?>><?= $t ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>Z
                <div class="col-12">
                    <label class="form-label">Fichier à archiver</label>
                    <input type="file" class="form-control" name="fichier" accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png,.gif,.txt,.zip">
                    <div class="form-text">PDF, Word, Excel, Images, ZIP — Max 10MB</div>
                </div>
            </div>
            <div class="section-divider"></div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-navy px-4"><i class="bi bi-archive me-2"></i>Archiver le document</button>
                <a href="index.php" class="btn btn-outline-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>

<script>
    // Auto-fill nom/prenom when fonctionnaire is selected
    const sel = document.getElementById('fonctionnaire_select');
    if (sel) {
        sel.addEventListener('change', function() {
            const opt = this.options[this.selectedIndex];
            document.getElementById('nom_input').value = opt.getAttribute('data-nom') || '';
            document.getElementById('prenom_input').value = opt.getAttribute('data-prenom') || '';
        });
        // Trigger on load if pre-selected
        if (sel.value) sel.dispatchEvent(new Event('change'));
    }
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>