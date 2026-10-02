<?php
$pageTitle = 'Gestion des Fonctionnaires';
$pageIcon  = 'people-fill';
require_once __DIR__ . '/../../includes/header.php';
requireAdmin();

$pdo     = getPDO();
$search  = trim($_GET['search'] ?? '');
$statut  = $_GET['statut'] ?? '';
$page    = max(1, (int)($_GET['page'] ?? 1));

// Build query
$where  = [];
$params = [];
if ($search) {
    $where[]  = "(matricule LIKE ? OR nom LIKE ? OR prenom LIKE ? OR cin LIKE ?)";
    $s = "%$search%";
    $params = array_merge($params, [$s, $s, $s, $s]);
}
if ($statut) {
    $where[]  = "statut = ?";
    $params[] = $statut;
}
$whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$total   = $pdo->prepare("SELECT COUNT(*) FROM fonctionnaires $whereSQL");
$total->execute($params);
$total   = (int)$total->fetchColumn();
$pg      = paginate($total, $page);

$stmt = $pdo->prepare("SELECT * FROM fonctionnaires $whereSQL ORDER BY created_at DESC LIMIT {$pg['perPage']} OFFSET {$pg['offset']}");
$stmt->execute($params);
$rows = $stmt->fetchAll();
?>

<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= APP_URL ?>/dashboard.php">Tableau de bord</a></li>
        <li class="breadcrumb-item active">Fonctionnaires</li>
    </ol>
</nav>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body py-3">
        <form method="GET" class="row g-2 align-items-center">
            <div class="col-12 col-md-5">
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" class="form-control" name="search"
                        placeholder="Rechercher par matricule, nom, CIN..." value="<?= clean($search) ?>">
                </div>
            </div>
            <div class="col-6 col-md-3">
                <select class="form-select" name="statut">
                    <option value="">Tous les statuts</option>
                    <option value="Actif" <?= $statut === 'Actif'    ? 'selected' : '' ?>>Actif</option>
                    <option value="Inactif" <?= $statut === 'Inactif'  ? 'selected' : '' ?>>Inactif</option>
                    <option value="Retraité" <?= $statut === 'Retraité' ? 'selected' : '' ?>>Retraité</option>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-navy flex-fill">Filtrer</button>
                    <a href="?" class="btn btn-outline-secondary"><i class="bi bi-x"></i></a>
                </div>
            </div>
            <div class="col-12 col-md-2 text-md-end">
                <a href="<?= APP_URL ?>/modules/fonctionnaires/add.php" class="btn btn-success w-100">
                    <i class="bi bi-plus-lg me-1"></i>Ajouter
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Table -->
<div class="card">
    <div class="card-header">
        <span><i class="bi bi-people-fill me-2 text-primary"></i>Liste des fonctionnaires</span>
        <span class="badge bg-secondary"><?= $total ?> enregistrement(s)</span>
    </div>
    <div class="card-body p-0">
        <?php if (empty($rows)): ?>
            <div class="text-center py-5 text-muted">
                <i class="bi bi-inbox" style="font-size:48px;opacity:0.3;display:block;"></i>
                <p class="mt-2">Aucun fonctionnaire trouvé.</p>
                <a href="add.php" class="btn btn-sm btn-navy">
                    <i class="bi bi-plus me-1"></i>Ajouter un fonctionnaire
                </a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-custom mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Matricule</th>
                            <th>Nom complet</th>
                            <th>Date naissance</th>
                            <th>CIN</th>
                            <th>Téléphone</th>
                            <th>Statut</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rows as $i => $r): ?>
                            <tr>
                                <td class="text-muted" style="font-size:12px;"><?= $pg['offset'] + $i + 1 ?></td>
                                <td><code><?= clean($r['matricule']) ?></code></td>
                                <td>
                                    <div style="font-weight:600;"><?= clean($r['prenom']) ?> <?= clean($r['nom']) ?></div>
                                </td>
                                <td><?= $r['date_naissance'] ? date('d/m/Y', strtotime($r['date_naissance'])) : '—' ?></td>
                                <td><?= clean($r['cin']) ?: '—' ?></td>
                                <td><?= clean($r['telephone']) ?: '—' ?></td>
                                <td>
                                    <span class="badge-status badge-<?= strtolower($r['statut'] === 'Actif' ? 'actif' : ($r['statut'] === 'Retraité' ? 'retraite' : 'inactif')) ?>">
                                        <?= clean($r['statut']) ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <a href="edit.php?id=<?= $r['id'] ?>" class="btn btn-action btn-outline-primary me-1" title="Modifier">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <a href="delete.php?id=<?= $r['id'] ?>" class="btn btn-action btn-outline-danger btn-delete-confirm" title="Supprimer">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($pg['totalPages'] > 1): ?>
                <div class="d-flex align-items-center justify-content-between px-4 py-3 border-top">
                    <small class="text-muted">
                        Affichage <?= $pg['offset'] + 1 ?>–<?= min($pg['offset'] + $pg['perPage'], $total) ?> sur <?= $total ?>
                    </small>
                    <nav>
                        <ul class="pagination pagination-sm mb-0">
                            <li class="page-item <?= $pg['currentPage'] == 1 ? 'disabled' : '' ?>">
                                <a class="page-link" href="?page=<?= $pg['currentPage'] - 1 ?>&search=<?= urlencode($search) ?>&statut=<?= urlencode($statut) ?>">
                                    <i class="bi bi-chevron-left"></i>
                                </a>
                            </li>
                            <?php for ($p = 1; $p <= $pg['totalPages']; $p++): ?>
                                <li class="page-item <?= $p == $pg['currentPage'] ? 'active' : '' ?>">
                                    <a class="page-link" href="?page=<?= $p ?>&search=<?= urlencode($search) ?>&statut=<?= urlencode($statut) ?>">
                                        <?= $p ?>
                                    </a>
                                </li>
                            <?php endfor; ?>
                            <li class="page-item <?= $pg['currentPage'] == $pg['totalPages'] ? 'disabled' : '' ?>">
                                <a class="page-link" href="?page=<?= $pg['currentPage'] + 1 ?>&search=<?= urlencode($search) ?>&statut=<?= urlencode($statut) ?>">
                                    <i class="bi bi-chevron-right"></i>
                                </a>
                            </li>
                        </ul>
                    </nav>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>