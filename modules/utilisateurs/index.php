<?php
$pageTitle = 'Gestion des Utilisateurs';
$pageIcon  = 'shield-lock';
require_once __DIR__ . '/../../includes/header.php';
requireAdmin();

$pdo    = getPDO();
$search = trim($_GET['search'] ?? '');
$role_f = $_GET['role'] ?? '';
$page   = max(1, (int)($_GET['page'] ?? 1));

$conditions = [];
$params     = [];
if ($search) {
    $conditions[] = "(nom_utilisateur LIKE ? OR email LIKE ?)";
    $s = "%$search%";
    $params = array_merge($params, [$s, $s]);
}
if ($role_f) {
    $conditions[] = "role=?";
    $params[] = $role_f;
}
$whereSQL = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';

$total = $pdo->prepare("SELECT COUNT(*) FROM utilisateurs $whereSQL");
$total->execute($params);
$total = (int)$total->fetchColumn();
$pg = paginate($total, $page);

$stmt = $pdo->prepare("SELECT * FROM utilisateurs $whereSQL ORDER BY created_at DESC LIMIT {$pg['perPage']} OFFSET {$pg['offset']}");
$stmt->execute($params);
$rows = $stmt->fetchAll();
?>
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= APP_URL ?>/dashboard.php">Tableau de bord</a></li>
        <li class="breadcrumb-item active">Utilisateurs</li>
    </ol>
</nav>
<div class="card mb-4">
    <div class="card-body py-3">
        <form method="GET" class="row g-2 align-items-center">
            <div class="col-12 col-md-5">
                <div class="input-group"><span class="input-group-text"><i class="bi bi-search"></i></span><input type="text" class="form-control" name="search" placeholder="Nom ou email..." value="<?= clean($search) ?>"></div>
            </div>
            <div class="col-6 col-md-2"><select class="form-select" name="role">
                    <option value="">Tous les rôles</option>
                    <option value="admin" <?= $role_f === 'admin' ? 'selected' : '' ?>>Admin</option>
                    <option value="user" <?= $role_f === 'user' ? 'selected' : '' ?>>Utilisateur</option>
                </select></div>
            <div class="col-6 col-md-2">
                <div class="d-flex gap-2"><button type="submit" class="btn btn-navy flex-fill">Filtrer</button><a href="?" class="btn btn-outline-secondary"><i class="bi bi-x"></i></a></div>
            </div>
            <div class="col-12 col-md-3 text-md-end"><a href="add.php" class="btn btn-success"><i class="bi bi-plus-lg me-1"></i>Ajouter un utilisateur</a></div>
        </form>
    </div>
</div>
<div class="card">
    <div class="card-header"><span><i class="bi bi-shield-lock me-2 text-primary"></i>Utilisateurs du système</span><span class="badge bg-secondary"><?= $total ?></span></div>
    <div class="card-body p-0">
        <?php if (empty($rows)): ?><div class="text-center py-5 text-muted"><i class="bi bi-inbox" style="font-size:48px;opacity:0.3;display:block;"></i>
                <p class="mt-2">Aucun utilisateur trouvé.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-custom mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nom d'utilisateur</th>
                            <th>Email</th>
                            <th>Rôle</th>
                            <th>Matricule lié</th>
                            <th>Créé le</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody><?php foreach ($rows as $i => $r): ?><tr>
                                <td class="text-muted" style="font-size:12px;"><?= $pg['offset'] + $i + 1 ?></td>
                                <td>
                                    <div style="display:flex;align-items:center;gap:10px;">
                                        <div style="width:32px;height:32px;background:<?= $r['role'] === 'admin' ? '#EFF6FF' : '#F5F3FF' ?>;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:12px;color:<?= $r['role'] === 'admin' ? '#1D4ED8' : '#5B21B6' ?>;flex-shrink:0;"><?= strtoupper(substr($r['nom_utilisateur'], 0, 1)) ?></div>
                                        <span style="font-weight:500;"><?= clean($r['nom_utilisateur']) ?></span>
                                        <?php if ($r['id'] == $_SESSION['user_id']): ?><span class="badge bg-warning text-dark" style="font-size:10px;">Vous</span><?php endif; ?>
                                    </div>
                                </td>
                                <td><?= clean($r['email']) ?></td>
                                <td><span class="badge-status badge-<?= $r['role'] ?>"><?= $r['role'] === 'admin' ? '🔑 Admin' : '👤 User' ?></span></td>
                                <td><?= $r['matricule'] ? '<code>' . clean($r['matricule']) . '</code>' : '<span class="text-muted">—</span>' ?></td>
                                <td class="text-muted" style="font-size:12px;"><?= $r['created_at'] ? date('d/m/Y', strtotime($r['created_at'])) : '—' ?></td>
                                <td class="text-center">
                                    <a href="edit.php?id=<?= $r['id'] ?>" class="btn btn-action btn-outline-primary me-1"><i class="bi bi-pencil"></i></a>
                                    <?php if ($r['id'] != $_SESSION['user_id']): ?>
                                        <a href="delete.php?id=<?= $r['id'] ?>" class="btn btn-action btn-outline-danger btn-delete-confirm"><i class="bi bi-trash"></i></a>
                                    <?php endif; ?>
                                </td>
                            </tr><?php endforeach; ?></tbody>
                </table>
            </div>
            <?php if ($pg['totalPages'] > 1): ?><div class="d-flex align-items-center justify-content-between px-4 py-3 border-top"><small class="text-muted">Affichage <?= $pg['offset'] + 1 ?>–<?= min($pg['offset'] + $pg['perPage'], $total) ?> sur <?= $total ?></small>
                    <nav>
                        <ul class="pagination pagination-sm mb-0"><?php for ($p = 1; $p <= $pg['totalPages']; $p++): ?><li class="page-item <?= $p == $pg['currentPage'] ? 'active' : '' ?>"><a class="page-link" href="?page=<?= $p ?>&search=<?= urlencode($search) ?>&role=<?= urlencode($role_f) ?>"><?= $p ?></a></li><?php endfor; ?></ul>
                    </nav>
                </div><?php endif; ?>
        <?php endif; ?>
    </div>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>