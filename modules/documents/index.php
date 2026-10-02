<?php
$pageTitle = 'Documents Archivés';
$pageIcon  = 'folder2-open';
require_once __DIR__ . '/../../includes/header.php';

$pdo    = getPDO();
$role   = $_SESSION['role'];
$uid    = $_SESSION['user_id'];
$search = trim($_GET['search'] ?? '');
$type   = $_GET['type'] ?? '';
$page   = max(1, (int)($_GET['page'] ?? 1));

// Build WHERE based on role
$conditions = [];
$params     = [];

if ($role !== 'admin') {
    $conditions[] = "d.uploaded_by = ?";
    $params[]     = $uid;
}
if ($search) {
    $conditions[] = "(d.titre_document LIKE ? OR d.matricule LIKE ? OR d.nom_fonctionnaire LIKE ?)";
    $s = "%$search%";
    $params = array_merge($params, [$s, $s, $s]);
}
if ($type) {
    $conditions[] = "d.type_document = ?";
    $params[] = $type;
}

$whereSQL = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';

$total = $pdo->prepare("SELECT COUNT(*) FROM documents_archives d $whereSQL");
$total->execute($params);
$total = (int)$total->fetchColumn();
$pg    = paginate($total, $page);

$stmt = $pdo->prepare("SELECT d.*, u.nom_utilisateur as uploaded_by_name FROM documents_archives d LEFT JOIN utilisateurs u ON d.uploaded_by=u.id $whereSQL ORDER BY d.created_at DESC LIMIT {$pg['perPage']} OFFSET {$pg['offset']}");
$stmt->execute($params);
$rows = $stmt->fetchAll();

// Get document types for filter
$types = $pdo->query("SELECT DISTINCT type_document FROM documents_archives WHERE type_document IS NOT NULL AND type_document != '' ORDER BY type_document")->fetchAll(PDO::FETCH_COLUMN);
?>

<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= APP_URL ?>/dashboard.php">Tableau de bord</a></li>
        <li class="breadcrumb-item active">Documents archivés</li>
    </ol>
</nav>

<?php if ($role !== 'admin'): ?>
    <div class="alert alert-info mb-3">
        <i class="bi bi-info-circle me-2"></i>
        <strong>Espace personnel :</strong> Vous ne voyez que vos propres documents archivés.
    </div>
<?php endif; ?>

<div class="card mb-4">
    <div class="card-body py-3">
        <form method="GET" class="row g-2 align-items-center">
            <div class="col-12 col-md-5">
                <div class="input-group"><span class="input-group-text"><i class="bi bi-search"></i></span><input type="text" class="form-control" name="search" placeholder="Titre, matricule, fonctionnaire..." value="<?= clean($search) ?>"></div>
            </div>
            <div class="col-6 col-md-3">
                <select class="form-select" name="type">
                    <option value="">Tous les types</option>
                    <?php foreach ($types as $t): ?>
                        <option value="<?= clean($t) ?>" <?= $type === $t ? 'selected' : '' ?>><?= clean($t) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <div class="d-flex gap-2"><button type="submit" class="btn btn-navy flex-fill">Filtrer</button><a href="?" class="btn btn-outline-secondary"><i class="bi bi-x"></i></a></div>
            </div>
            <div class="col-12 col-md-2 text-md-end"><a href="add.php" class="btn btn-success"><i class="bi bi-plus-lg me-1"></i>Archiver</a></div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <span><i class="bi bi-folder2-open me-2 text-primary"></i>
            <?= $role === 'admin' ? 'Tous les documents archivés' : 'Mes documents' ?>
        </span>
        <span class="badge bg-secondary"><?= $total ?></span>
    </div>
    <div class="card-body p-0">
        <?php if (empty($rows)): ?>
            <div class="text-center py-5 text-muted"><i class="bi bi-inbox" style="font-size:48px;opacity:0.3;display:block;"></i>
                <p class="mt-2">Aucun document trouvé.</p><a href="add.php" class="btn btn-sm btn-navy"><i class="bi bi-plus me-1"></i>Archiver un document</a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-custom mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Titre</th>
                            <th>Fonctionnaire</th>
                            <th>Matricule</th>
                            <th>Type</th>
                            <th>Date archive</th><?php if ($role === 'admin'): ?><th>Archivé par</th><?php endif; ?><th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody><?php foreach ($rows as $i => $r): ?><tr>
                                <td class="text-muted" style="font-size:12px;"><?= $pg['offset'] + $i + 1 ?></td>
                                <td>
                                    <div style="display:flex;align-items:center;gap:8px;">
                                        <?php
                                        $ext = strtolower(pathinfo($r['nom_fichier'] ?? '', PATHINFO_EXTENSION));
                                        $icons = ['pdf' => 'bi-file-earmark-pdf text-danger', 'doc' => 'bi-file-earmark-word text-primary', 'docx' => 'bi-file-earmark-word text-primary', 'xls' => 'bi-file-earmark-excel text-success', 'xlsx' => 'bi-file-earmark-excel text-success', 'jpg' => 'bi-file-earmark-image text-warning', 'jpeg' => 'bi-file-earmark-image text-warning', 'png' => 'bi-file-earmark-image text-warning'];
                                        $icon = $icons[$ext] ?? 'bi-file-earmark text-secondary';
                                        ?>
                                        <i class="bi <?= $icon ?>"></i>
                                        <span style="font-weight:500;"><?= clean($r['titre_document']) ?></span>
                                    </div>
                                </td>
                                <td><?= clean($r['nom_fonctionnaire']) ?> <?= clean($r['prenom']) ?></td>
                                <td><code><?= clean($r['matricule']) ?></code></td>
                                <td><span class="badge bg-light text-dark border"><?= clean($r['type_document']) ?: '—' ?></span></td>
                                <td class="text-muted" style="font-size:12px;"><?= $r['date_upload'] ? date('d/m/Y', strtotime($r['date_upload'])) : '—' ?></td>
                                <?php if ($role === 'admin'): ?><td class="text-muted" style="font-size:12px;"><?= clean($r['uploaded_by_name']) ?></td><?php endif; ?>
                                <td class="text-center">
                                    <?php if ($r['nom_fichier'] && file_exists(UPLOAD_DIR . $r['nom_fichier'])): ?>

                                        <!-- Télécharger -->
                                        <a href="<?= APP_URL ?>/modules/documents/download.php?id=<?= $r['id'] ?>"
                                            class="btn btn-action btn-outline-success me-1"
                                            title="Télécharger">
                                            <i class="bi bi-download"></i>
                                        </a>

                                        <!-- Imprimer -->
                                        <a href="<?= APP_URL ?>/modules/documents/download.php?id=<?= $r['id'] ?>&print=1"
                                            target="_blank"
                                            class="btn btn-action btn-outline-secondary me-1"
                                            title="Imprimer">
                                            <i class="bi bi-printer"></i>
                                        </a>

                                    <?php endif; ?>
                                    <a href="edit.php?id=<?= $r['id'] ?>" class="btn btn-action btn-outline-primary me-1" title="Modifier"><i class="bi bi-pencil"></i></a>
                                    <a href="delete.php?id=<?= $r['id'] ?>" class="btn btn-action btn-outline-danger btn-delete-confirm" title="Supprimer"><i class="bi bi-trash"></i></a>
                                </td>
                            </tr><?php endforeach; ?></tbody>
                </table>
            </div>
            <?php if ($pg['totalPages'] > 1): ?><div class="d-flex align-items-center justify-content-between px-4 py-3 border-top"><small class="text-muted">Affichage <?= $pg['offset'] + 1 ?>–<?= min($pg['offset'] + $pg['perPage'], $total) ?> sur <?= $total ?></small>
                    <nav>
                        <ul class="pagination pagination-sm mb-0"><?php for ($p = 1; $p <= $pg['totalPages']; $p++): ?><li class="page-item <?= $p == $pg['currentPage'] ? 'active' : '' ?>"><a class="page-link" href="?page=<?= $p ?>&search=<?= urlencode($search) ?>&type=<?= urlencode($type) ?>"><?= $p ?></a></li><?php endfor; ?></ul>
                    </nav>
                </div><?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>