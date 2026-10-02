<?php


define('DB_HOST', 'localhost');
define('DB_NAME', 'gestion_archivage');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('UPLOAD_URL', 'uploads/');
define('ITEMS_PER_PAGE', 10);
define('APP_NAME', 'Gestion Archivage');
define('APP_URL', 'http://localhost/gestion_archivage');

function getPDO(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            die('<div style="font-family:sans-serif;padding:40px;color:#c00;">
                <h2>Erreur de connexion à la base de données</h2>
                <p>' . htmlspecialchars($e->getMessage()) . '</p>
                <p>Vérifiez les paramètres dans <code>config/database.php</code></p>
                </div>');
        }
    }
    return $pdo;
}


function clean(?string $val): string {
    if ($val === null) {
        return '';
    }
    return htmlspecialchars(trim($val), ENT_QUOTES, 'UTF-8');
}


function redirect(string $url): void {
    if (!headers_sent()) {
        header("Location: $url");
        exit;
    } else {
        echo "<script>window.location.href='".htmlspecialchars($url, ENT_QUOTES)."';</script>";
        exit;
    }
}


function setFlash(string $type, string $msg): void {
    if (session_status() === PHP_SESSION_NONE) session_start();
    $_SESSION['flash'] = ['type' => $type, 'msg' => $msg];
}

function getFlash(): ?array {
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (isset($_SESSION['flash'])) {
        $f = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $f;
    }
    return null;
}


function requireAdmin(): void {
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        setFlash('danger', 'Accès refusé. Droits administrateur requis.');
        redirect(APP_URL . '/dashboard.php');
    }
}

function requireLogin(): void {
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (!isset($_SESSION['user_id'])) {
        redirect(APP_URL . '/login.php');
    }
}


function paginate(int $total, int $page, int $perPage = ITEMS_PER_PAGE): array {
    $totalPages = max(1, (int)ceil($total / $perPage));
    $page = max(1, min($page, $totalPages));
    $offset = ($page - 1) * $perPage;
    return [
        'total'       => $total,
        'totalPages'  => $totalPages,
        'currentPage' => $page,
        'offset'      => $offset,
        'perPage'     => $perPage,
    ];
}