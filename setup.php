<?php
/**
 * setup.php — Run this ONCE to create hashed passwords for default users
 * Access: http://localhost/gestion_archivage/setup.php
 * DELETE this file after running!
 */

require_once 'config/database.php';

$adminHash = password_hash('admin123', PASSWORD_BCRYPT);
$userHash  = password_hash('user123',  PASSWORD_BCRYPT);

try {
    $pdo = getPDO();

    // Update admin
    $stmt = $pdo->prepare("UPDATE utilisateurs SET mot_de_passe = ? WHERE email = 'admin@archivage.ma'");
    $stmt->execute([$adminHash]);

    // Update user
    $stmt = $pdo->prepare("UPDATE utilisateurs SET mot_de_passe = ? WHERE email = 'user@archivage.ma'");
    $stmt->execute([$userHash]);

    echo '<div style="font-family:sans-serif;max-width:600px;margin:60px auto;padding:30px;
          background:#f0fdf4;border:1px solid #86efac;border-radius:8px;">
        <h2 style="color:#166534;">✅ Configuration réussie !</h2>
        <p>Les mots de passe ont été configurés avec succès.</p>
        <table style="width:100%;border-collapse:collapse;margin:20px 0;">
            <tr style="background:#dcfce7;">
                <th style="padding:8px;text-align:left;border:1px solid #86efac;">Rôle</th>
                <th style="padding:8px;text-align:left;border:1px solid #86efac;">Email</th>
                <th style="padding:8px;text-align:left;border:1px solid #86efac;">Mot de passe</th>
            </tr>
            <tr>
                <td style="padding:8px;border:1px solid #86efac;"><strong>Admin</strong></td>
                <td style="padding:8px;border:1px solid #86efac;">admin@archivage.ma</td>
                <td style="padding:8px;border:1px solid #86efac;"><code>admin123</code></td>
            </tr>
            <tr>
                <td style="padding:8px;border:1px solid #86efac;"><strong>User</strong></td>
                <td style="padding:8px;border:1px solid #86efac;">user@archivage.ma</td>
                <td style="padding:8px;border:1px solid #86efac;"><code>user123</code></td>
            </tr>
        </table>
        <p style="color:#dc2626;font-weight:bold;">⚠️ SUPPRIMEZ ce fichier maintenant pour des raisons de sécurité !</p>
        <a href="login.php" style="display:inline-block;margin-top:10px;padding:10px 20px;
           background:#1B3A5C;color:white;border-radius:6px;text-decoration:none;">
           → Aller à la page de connexion
        </a>
    </div>';
} catch (Exception $e) {
    echo '<p style="color:red;">Erreur: ' . htmlspecialchars($e->getMessage()) . '</p>';
}
