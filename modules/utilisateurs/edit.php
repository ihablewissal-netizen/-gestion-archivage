<?php
$pageTitle = 'Modifier un utilisateur';
$pageIcon  = 'pencil';
require_once __DIR__ . '/../../includes/header.php';
requireAdmin();

$pdo = $getPDO = getPDO();
$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE id=?");
$stmt->execute([$id]);
$data = $stmt->fetch();
if (!$data) {
    setFlash('danger', 'Utilisateur introuvable.');
    redirect(APP_URL . '/modules/utilisateurs/index.php');
}

$foncs = $pdo->query("SELECT matricule, CONCAT(prenom,' ',nom) as nom_complet FROM fonctionnaires ORDER BY nom")->fetchAll();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $upd = ['nom_utilisateur' => trim($_POST['nom_utilisateur'] ?? ''), 'email' => trim($_POST['email'] ?? ''), 'role' => trim($_POST['role'] ?? 'user'), 'matricule' => trim($_POST['matricule'] ?? '')];
    $password = trim($_POST['password'] ?? '');
    $password2 = trim($_POST['password2'] ?? '');

    if (!$upd['nom_utilisateur']) $errors[] = 'Le nom est requis.';
    if (!$upd['email'] || !filter_var($upd['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Email valide requis.';
    if ($password && strlen($password) < 6) $errors[] = 'Le mot de passe doit contenir au moins 6 caractères.';
    if ($password && $password != $password2) $errors[] = 'Les mots de passe ne correspondent pas.';

    if (empty($errors)) {
        $chk = $pdo->prepare("SELECT id FROM utilisateurs WHERE email=? AND id!=?");
        $chk->execute([$upd['email'], $id]);
        if ($chk->fetch()) {
            $errors[] = 'Cet email est déjà utilisé.';
        } else {
            if ($password) {
                $hash = password_hash($password, PASSWORD_BCRYPT);
                $pdo->prepare("UPDATE utilisateurs SET nom_utilisateur=?,email=?,role=?,mot_de_passe=?,matricule=? WHERE id=?")->execute([$upd['nom_utilisateur'], $upd['email'], $upd['role'], $hash, $upd['matricule'] ?: null, $id]);
            } else {
                $pdo->prepare("UPDATE utilisateurs SET nom_utilisateur=?,email=?,role=?,matricule=? WHERE id=?")->execute([$upd['nom_utilisateur'], $upd['email'], $upd['role'], $upd['matricule'] ?: null, $id]);
            }
            $data = array_merge($data, $upd);
            setFlash('success', 'Utilisateur mis à jour.');
            redirect(APP_URL . '/modules/utilisateurs/index.php');
        }
    }
}
?>
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= APP_URL ?>/dashboard.php">Tableau de bord</a></li>
        <li class="breadcrumb-item"><a href="index.php">Utilisateurs</a></li>
        <li class="breadcrumb-item active">Modifier</li>
    </ol>
</nav>
<?php if ($errors): ?><div class="alert alert-danger mb-3">
        <ul class="mb-0"><?php foreach ($errors as $e): ?><li><?= clean($e) ?></li><?php endforeach; ?></ul>
    </div><?php endif; ?>
<div class="card" style="max-width:600px;">
    <div class="card-header"><span><i class="bi bi-pencil me-2 text-warning"></i>Modifier l'utilisateur</span></div>
    <div class="card-body p-4">
        <form method="POST">
            <div class="mb-3"><label class="form-label">Nom d'utilisateur <span class="text-danger">*</span></label><input type="text" class="form-control" name="nom_utilisateur" value="<?= clean($data['nom_utilisateur']) ?>" required></div>
            <div class="mb-3"><label class="form-label">Adresse email <span class="text-danger">*</span></label><input type="email" class="form-control" name="email" value="<?= clean($data['email']) ?>" required></div>
            <div class="row g-3 mb-3">
                <div class="col-md-6"><label class="form-label">Nouveau mot de passe</label><input type="password" class="form-control" name="password" placeholder="Laisser vide pour ne pas changer" minlength="6"></div>
                <div class="col-md-6"><label class="form-label">Confirmer MDP</label><input type="password" class="form-control" name="password2" minlength="6"></div>
            </div>
            <div class="row g-3 mb-3">
                <div class="col-md-6"><label class="form-label">Rôle</label><select class="form-select" name="role"><?php if ($data['id'] != $_SESSION['user_id']): ?><option value="user" <?= $data['role'] === 'user' ? 'selected' : '' ?>>👤 Utilisateur</option>
                            <option value="admin" <?= $data['role'] === 'admin' ? 'selected' : '' ?>>🔑 Administrateur</option><?php else: ?><option value="admin" selected>🔑 Administrateur (vous)</option><?php endif; ?>
                    </select></div>
                <div class="col-md-6"><label class="form-label">Fonctionnaire lié</label><select class="form-select" name="matricule">
                        <option value="">-- Aucun --</option><?php foreach ($foncs as $f): ?><option value="<?= clean($f['matricule']) ?>" <?= $data['matricule'] == $f['matricule'] ? 'selected' : '' ?>><?= clean($f['matricule']) ?> — <?= clean($f['nom_complet']) ?></option><?php endforeach; ?>
                    </select></div>
            </div>
            <div class="section-divider"></div>
            <div class="d-flex gap-2"><button type="submit" class="btn btn-navy px-4"><i class="bi bi-check-lg me-2"></i>Mettre à jour</button><a href="index.php" class="btn btn-outline-secondary">Annuler</a></div>
        </form>
    </div>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>