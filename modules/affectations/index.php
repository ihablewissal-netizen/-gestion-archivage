<?php
$pageTitle = 'Gestion des Affectations';
$pageIcon  = 'person-badge';
require_once __DIR__ . '/../../includes/header.php';
requireAdmin();

$pdo    = getPDO();
$search = trim($_GET['search'] ?? '');
$page   = max(1, (int)($_GET['page'] ?? 1));


$where  = $search ? "WHERE a.matricule LIKE ? OR a.poste_occupe LIKE ?" : "";
$params = $search ? ["%$search%", "%$search%"] : [];

$total = $pdo->prepare("SELECT COUNT(*) FROM affectations a $where");
$total->execute($params);
$total = (int)$total->fetchColumn();
$pg    = paginate($total, $page);


$stmt = $pdo->prepare("SELECT a.*, CONCAT(f.prenom,' ',f.nom) as nom_complet FROM affectations a LEFT JOIN fonctionnaires f ON a.matricule=f.matricule $where ORDER BY a.created_at DESC LIMIT {$pg['perPage']} OFFSET {$pg['offset']}");
$stmt->execute($params);
$rows = $stmt->fetchAll();
?>


<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item">
            <a href="<?= APP_URL ?>/dashboard.php">Tableau de bord</a>
        </li>
        <li class="breadcrumb-item active">Affectations</li>
    </ol>
</nav>

<div class="card mb-4">
    <div class="card-body py-3">
        <form method="GET" class="row g-2 align-items-center">
            <div class="col-12 col-md-6">
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="bi bi-search"></i>
                    </span>
                    <input type="text" class="form-control" name="search" placeholder="Matricule ou poste..." value="<?= clean($search) ?>">
                </div>
            </div>
            <div class="col-6 col-md-2">
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-navy flex-fill">Filtrer</button>
                    <a href="?" class="btn btn-outline-secondary"><i class="bi bi-x"></i></a>
                </div>
            </div>
            <div class="col-6 col-md-4 text-md-end"><a href="add.php" class="btn btn-success"><i class="bi bi-plus-lg me-1"></i>Ajouter</a></div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <span><i class="bi bi-person-badge me-2 text-primary"></i>Liste des affectations</span>
        <span class="badge bg-secondary"><?= $total ?></span>
    </div>
    <div class="card-body p-0">
        <?php if (empty($rows)): ?>
            <div class="text-center py-5 text-muted">
                <i class="bi bi-inbox" style="font-size:48px;opacity:0.3;display:block;"></i>
                <p class="mt-2">Aucune affectation trouvée.</p>
                <a href="add.php" class="btn btn-sm btn-navy">Ajouter une affectation</a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-custom mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Matricule</th>
                            <th>Fonctionnaire</th>
                            <th>Poste occupé</th>
                            <th>Date début</th>
                            <th>Date fin</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rows as $i => $r): ?>
                            <tr>
                                <td class="text-muted" style="font-size:12px;"><?= $pg['offset'] + $i + 1 ?></td>
                                <td><code><?= clean($r['matricule']) ?></code></td>
                                <td><?= clean($r['nom_complet']) ?: '<span class="text-muted">—</span>' ?></td>
                                <td><strong><?= clean($r['poste_occupe']) ?></strong></td>
                                <td><?= $r['date_debut'] ? date('d/m/Y', strtotime($r['date_debut'])) : '—' ?></td>
                                <td><?= $r['date_fin'] ? date('d/m/Y', strtotime($r['date_fin'])) : '<span class="badge-status badge-actif">En cours</span>' ?></td>
                                <td class="text-center">
                                    <a href="edit.php?id=<?= $r['id'] ?>" class="btn btn-action btn-outline-primary me-1">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <a href="delete.php?id=<?= $r['id'] ?>" class="btn btn-action btn-outline-danger btn-delete-confirm">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php if ($pg['totalPages'] > 1): ?>
                <div class="d-flex align-items-center justify-content-between px-4 py-3 border-top">
                    <small class="text-muted">Affichage <?= $pg['offset'] + 1 ?>–<?= min($pg['offset'] + $pg['perPage'], $total) ?> sur <?= $total ?></small>
                    <nav>
                        <ul class="pagination pagination-sm mb-0">
                            <?php for ($p = 1; $p <= $pg['totalPages']; $p++): ?>
                                <li class="page-item <?= $p == $pg['currentPage'] ? 'active' : '' ?>">
                                    <a class="page-link" href="?page=<?= $p ?>&search=<?= urlencode($search) ?>"><?= $p ?></a>
                                </li>
                            <?php endfor; ?>
                        </ul>
                    </nav>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>