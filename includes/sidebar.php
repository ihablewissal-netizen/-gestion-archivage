<?php

$role    = $_SESSION['role'] ?? 'user';
$current = $_SERVER['PHP_SELF'];

function isActive(string $path): string {
    return (strpos($_SERVER['PHP_SELF'], $path) !== false) ? 'active' : '';
}
?>


<div id="sidebar-overlay" onclick="document.getElementById('sidebar').classList.remove('show')"
     style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:999;"></div>

<nav id="sidebar">
   
    <div class="sidebar-brand">
        <div class="brand-icon">
            <i class="bi bi-archive-fill"></i>
        </div>
        <div class="brand-text">
            Gestion Archivage
            <span>Système de Gestion</span>
        </div>
    </div>

    
    <div class="flex-fill overflow-auto py-2">
      
        <div class="nav-section-title">Principal</div>

        <a href="<?= APP_URL ?>/dashboard.php" class="nav-link <?= isActive('dashboard') ?>">
            <i class="bi bi-speedometer2"></i>
            Tableau de bord
        </a>

        <?php if ($role === 'admin'): ?>
  
        <div class="nav-section-title" style="margin-top:8px;">Gestion du Personnel</div>

        <a href="<?= APP_URL ?>/modules/fonctionnaires/index.php" class="nav-link <?= isActive('fonctionnaires') ?>">
            <i class="bi bi-people-fill"></i>
            Fonctionnaires
        </a>

        <a href="<?= APP_URL ?>/modules/services/index.php" class="nav-link <?= isActive('services') ?>">
            <i class="bi bi-building"></i>
            Services
        </a>

        <a href="<?= APP_URL ?>/modules/affectations/index.php" class="nav-link <?= isActive('affectations') ?>">
            <i class="bi bi-person-badge"></i>
            Affectations
        </a>

        <a href="<?= APP_URL ?>/modules/situations/index.php" class="nav-link <?= isActive('situations') ?>">
            <i class="bi bi-clipboard-data"></i>
            Situations Admin.
        </a>

        <a href="<?= APP_URL ?>/modules/carrieres/index.php" class="nav-link <?= isActive('carrieres') ?>">
            <i class="bi bi-graph-up-arrow"></i>
            Carrières
        </a>

        <div class="nav-section-title" style="margin-top:8px;">Documents & Accès</div>
        <?php endif; ?>

        <a href="<?= APP_URL ?>/modules/documents/index.php" class="nav-link <?= isActive('documents') ?>">
            <i class="bi bi-folder2-open"></i>
            Documents Archivés
        </a>

        <?php if ($role === 'admin'): ?>
        <a href="<?= APP_URL ?>/modules/utilisateurs/index.php" class="nav-link <?= isActive('utilisateurs') ?>">
            <i class="bi bi-shield-lock"></i>
            Utilisateurs
        </a>
        <?php endif; ?>
    </div>

   
    <div class="sidebar-footer">
        <div class="user-info">
            <div class="user-avatar">
                <?= strtoupper(substr($_SESSION['nom_utilisateur'] ?? 'U', 0, 1)) ?>
            </div>
            <div>
                <div class="user-name"><?= htmlspecialchars($_SESSION['nom_utilisateur'] ?? '') ?></div>
                <div class="user-role">
                    <?= $role === 'admin' ? '🔑 Administrateur' : '👤 Utilisateur' ?>
                </div>
            </div>
        </div>
        <a href="<?= APP_URL ?>/logout.php" class="nav-link" style="padding:8px 12px;background:rgba(231,76,60,0.15);border-radius:8px;border:none;">
            <i class="bi bi-box-arrow-right" style="color:#E74C3C;"></i>
            <span style="color:#E74C3C;font-size:13px;">Déconnexion</span>
        </a>
    </div>
</nav>

<script>

const sidebar = document.getElementById('sidebar');
const overlay = document.getElementById('sidebar-overlay');
if(sidebar && overlay) {
    const observer = new MutationObserver(() => {
        overlay.style.display = sidebar.classList.contains('show') ? 'block' : 'none';
    });
    observer.observe(sidebar, { attributes: true, attributeFilter: ['class'] });
}
</script>
