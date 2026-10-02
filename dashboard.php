<?php
$pageTitle = 'Tableau de bord';
$pageIcon  = 'speedometer2';
require_once __DIR__ . '/includes/header.php';

$pdo  = getPDO();
$role = $_SESSION['role'];
$uid  = $_SESSION['user_id'];
$mat  = $_SESSION['matricule'] ?? '';

// Stats (admin sees all, user sees own docs only)
$totalFonctionnaires = $pdo->query("SELECT COUNT(*) FROM fonctionnaires")->fetchColumn();
$totalServices       = $pdo->query("SELECT COUNT(*) FROM service")->fetchColumn();
$totalAffectations   = $pdo->query("SELECT COUNT(*) FROM affectations")->fetchColumn();

if ($role === 'admin') {
    $totalDocuments = $pdo->query("SELECT COUNT(*) FROM documents_archives")->fetchColumn();
} else {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM documents_archives WHERE uploaded_by = ?");
    $stmt->execute([$uid]);
    $totalDocuments = $stmt->fetchColumn();
}

// Recent documents
if ($role === 'admin') {
    $recentDocs = $pdo->query("SELECT * FROM documents_archives ORDER BY created_at DESC LIMIT 7")->fetchAll();
} else {
    $stmt = $pdo->prepare("SELECT * FROM documents_archives WHERE uploaded_by = ? ORDER BY created_at DESC LIMIT 7");
    $stmt->execute([$uid]);
    $recentDocs = $stmt->fetchAll();
}

// Recent fonctionnaires (admin only)
$recentFonc = [];
if ($role === 'admin') {
    $recentFonc = $pdo->query("SELECT * FROM fonctionnaires ORDER BY created_at DESC LIMIT 5")->fetchAll();
}
?>

<!-- Breadcrumb -->
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item active">Tableau de bord</li>
    </ol>
</nav>

<!-- Welcome Banner -->
<div class="mb-4 p-4 rounded-3" style="background:linear-gradient(135deg,#1B3A5C,#2E86DE);color:white;">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
        <div>
            <h4 class="mb-1 fw-bold">
                Bienvenue, <?= htmlspecialchars($_SESSION['nom_utilisateur']) ?> 👋
            </h4>
            <p class="mb-0 opacity-75" style="font-size:14px;">
                <?= date('l, d F Y') ?> —
                <?= $role === 'admin' ? 'Vue administrateur complète' : 'Espace personnel' ?>
            </p>
        </div>
        <div style="font-size:48px;opacity:0.3;">
            <i class="bi bi-archive-fill"></i>
        </div>
    </div>
</div>

<!-- Stat Cards -->
<div class="row g-3 mb-4">
    <?php if ($role === 'admin'): ?>
        <div class="col-6 col-lg-3">
            <div class="stat-card stat-navy shadow-sm">
                <div class="stat-icon" style="background:rgba(255,255,255,0.15);">
                    <i class="bi bi-people-fill" style="color:white;"></i>
                </div>
                <div class="stat-value"><?= number_format($totalFonctionnaires) ?></div>
                <div class="stat-label">Fonctionnaires</div>
                <i class="bi bi-people-fill stat-bg-icon"></i>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-card stat-blue shadow-sm">
                <div class="stat-icon" style="background:rgba(255,255,255,0.15);">
                    <i class="bi bi-building" style="color:white;"></i>
                </div>
                <div class="stat-value"><?= number_format($totalServices) ?></div>
                <div class="stat-label">Services</div>
                <i class="bi bi-building stat-bg-icon"></i>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-card stat-orange shadow-sm">
                <div class="stat-icon" style="background:rgba(255,255,255,0.15);">
                    <i class="bi bi-person-badge" style="color:white;"></i>
                </div>
                <div class="stat-value"><?= number_format($totalAffectations) ?></div>
                <div class="stat-label">Affectations</div>
                <i class="bi bi-person-badge stat-bg-icon"></i>
            </div>
        </div>
    <?php endif; ?>
    <div class="col-6 col-lg-<?= $role === 'admin' ? '3' : '4' ?>">
        <div class="stat-card stat-green shadow-sm">
            <div class="stat-icon" style="background:rgba(255,255,255,0.15);">
                <i class="bi bi-folder2-open" style="color:white;"></i>
            </div>
            <div class="stat-value"><?= number_format($totalDocuments) ?></div>
            <div class="stat-label">Documents archivés</div>
            <i class="bi bi-folder2-open stat-bg-icon"></i>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Recent Documents -->
    <div class="col-12 <?= $role === 'admin' ? 'col-xl-8' : '' ?>">
        <div class="card">
            <div class="card-header">
                <span><i class="bi bi-folder2-open text-primary me-2"></i>Documents récents</span>
                <a href="<?= APP_URL ?>/modules/documents/index.php" class="btn btn-sm btn-navy">
                    <i class="bi bi-eye me-1"></i>Voir tout
                </a>
            </div>
            <div class="card-body p-0">
                <?php if (empty($recentDocs)): ?>
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-inbox" style="font-size:48px;opacity:0.3;"></i>
                        <p class="mt-2">Aucun document archivé</p>
                        <a href="<?= APP_URL ?>/modules/documents/add.php" class="btn btn-sm btn-navy">
                            <i class="bi bi-plus me-1"></i>Ajouter un document
                        </a>
                    </div>
                <?php else: ?>
                    <table class="table table-custom mb-0">
                        <thead>
                            <tr>
                                <th>Titre</th>
                                <th>Fonctionnaire</th>
                                <th>Type</th>
                                <th>Date</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentDocs as $doc): ?>
                                <tr>
                                    <td>
                                        <i class="bi bi-file-earmark-text text-primary me-1"></i>
                                        <?= clean($doc['titre_document']) ?>
                                    </td>
                                    <td><?= clean($doc['nom_fonctionnaire']) ?> <?= clean($doc['prenom']) ?></td>
                                    <td><span class="badge bg-light text-dark border"><?= clean($doc['type_document']) ?></span></td>
                                    <td class="text-muted" style="font-size:12px;"><?= $doc['date_upload'] ?></td>
                                    <td>
                                        <?php if ($doc['nom_fichier']): ?>
                                            <a href="<?= APP_URL ?>/modules/documents/download.php?id=<?= $doc['id'] ?>"
                                                class="btn btn-action btn-outline-primary">
                                                <i class="bi bi-download"></i>
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php if ($role === 'admin'): ?>
        <!-- Recent Fonctionnaires -->
        <div class="col-12 col-xl-4">
            <div class="card">
                <div class="card-header">
                    <span><i class="bi bi-people text-success me-2"></i>Fonctionnaires récents</span>
                    <a href="<?= APP_URL ?>/modules/fonctionnaires/index.php" class="btn btn-sm btn-outline-secondary btn-sm">
                        Voir tout
                    </a>
                </div>
                <div class="card-body p-0">
                    <?php foreach ($recentFonc as $f): ?>
                        <div class="d-flex align-items-center gap-3 p-3" style="border-bottom:1px solid #F1F5F9;">
                            <div style="width:38px;height:38px;background:#EFF6FF;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:700;color:#1D4ED8;font-size:13px;flex-shrink:0;">
                                <?= strtoupper(substr($f['prenom'], 0, 1) . substr($f['nom'], 0, 1)) ?>
                            </div>
                            <div>
                                <div style="font-size:13px;font-weight:600;"><?= clean($f['prenom']) ?> <?= clean($f['nom']) ?></div>
                                <div style="font-size:11px;color:#718096;"><code><?= clean($f['matricule']) ?></code></div>
                            </div>
                            <div class="ms-auto">
                                <span class="badge-status badge-<?= strtolower($f['statut'] === 'Actif' ? 'actif' : ($f['statut'] === 'Retraité' ? 'retraite' : 'inactif')) ?>">
                                    <?= clean($f['statut']) ?>
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <?php if (empty($recentFonc)): ?>
                        <div class="text-center py-4 text-muted">
                            <i class="bi bi-people" style="font-size:32px;opacity:0.3;"></i>
                            <p class="mt-2 mb-0" style="font-size:13px;">Aucun fonctionnaire</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Quick Actions (admin) -->
            <div class="card mt-4">
                <div class="card-header">
                    <span><i class="bi bi-lightning text-warning me-2"></i>Actions rapides</span>
                </div>
                <div class="card-body d-flex flex-column gap-2">
                    <a href="<?= APP_URL ?>/modules/fonctionnaires/add.php" class="btn btn-outline-primary btn-sm text-start">
                        <i class="bi bi-person-plus me-2"></i>Nouveau fonctionnaire
                    </a>
                    <a href="<?= APP_URL ?>/modules/documents/add.php" class="btn btn-outline-success btn-sm text-start">
                        <i class="bi bi-file-earmark-plus me-2"></i>Archiver un document
                    </a>
                    <a href="<?= APP_URL ?>/modules/services/add.php" class="btn btn-outline-info btn-sm text-start">
                        <i class="bi bi-building-add me-2"></i>Nouveau service
                    </a>
                    <a href="<?= APP_URL ?>/modules/utilisateurs/add.php" class="btn btn-outline-secondary btn-sm text-start">
                        <i class="bi bi-person-plus-fill me-2"></i>Nouvel utilisateur
                    </a>
                </div>
            </div>
        </div>
    <?php else: ?>
        <!-- User Quick Actions -->
        <div class="col-12 col-md-4">
            <div class="card">
                <div class="card-header">
                    <span><i class="bi bi-lightning text-warning me-2"></i>Actions rapides</span>
                </div>
                <div class="card-body d-flex flex-column gap-2">
                    <a href="<?= APP_URL ?>/modules/documents/add.php" class="btn btn-navy">
                        <i class="bi bi-file-earmark-plus me-2"></i>Archiver un document
                    </a>
                    <a href="<?= APP_URL ?>/modules/documents/index.php" class="btn btn-outline-secondary">
                        <i class="bi bi-folder2-open me-2"></i>Mes documents
                    </a>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>