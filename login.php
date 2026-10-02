<?php
session_start();
require_once 'config/database.php';

// Already logged in?
if (isset($_SESSION['user_id'])) {
    redirect(APP_URL . '/dashboard.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($email) || empty($password)) {
        $error = 'Veuillez remplir tous les champs.';
    } else {
        try {
            $pdo  = getPDO();
            $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE email = ? LIMIT 1");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['mot_de_passe'])) {
                $_SESSION['user_id']       = $user['id'];
                $_SESSION['nom_utilisateur'] = $user['nom_utilisateur'];
                $_SESSION['email']         = $user['email'];
                $_SESSION['role']          = $user['role'];
                $_SESSION['matricule']     = $user['matricule'];
                redirect(APP_URL . '/dashboard.php');
            } else {
                $error = 'Email ou mot de passe incorrect.';
            }
        } catch (PDOException $e) {
            $error = 'Erreur de connexion à la base de données. Veuillez réessayer.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion — Gestion Archivage</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --navy: #1B3A5C;
            --navy-dk: #122840;
            --accent: #2E86DE;
        }
        * { box-sizing: border-box; }
        body {
            font-family: 'Tajawal', sans-serif;
            background: linear-gradient(135deg, var(--navy-dk) 0%, var(--navy) 50%, #234B75 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-wrapper {
            width: 100%;
            max-width: 440px;
        }

        .login-logo {
            text-align: center;
            margin-bottom: 32px;
        }

        .login-logo .logo-icon {
            width: 72px; height: 72px;
            background: rgba(255,255,255,0.15);
            border-radius: 18px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            color: white;
            margin-bottom: 16px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.2);
        }

        .login-logo h1 {
            color: white;
            font-size: 22px;
            font-weight: 700;
            margin: 0;
        }

        .login-logo p {
            color: rgba(255,255,255,0.55);
            font-size: 13px;
            margin: 6px 0 0;
        }

        .login-card {
            background: white;
            border-radius: 20px;
            padding: 36px;
            box-shadow: 0 25px 60px rgba(0,0,0,0.3);
        }

        .login-card h2 {
            font-size: 20px;
            font-weight: 700;
            color: var(--navy);
            margin-bottom: 6px;
        }

        .login-card .subtitle {
            font-size: 13px;
            color: #718096;
            margin-bottom: 28px;
        }

        .form-floating label { font-size: 14px; color: #718096; }
        .form-floating .form-control {
            border-radius: 10px;
            border: 1.5px solid #E2E8F0;
            font-size: 14px;
            height: 52px;
            transition: border-color 0.2s;
        }
        .form-floating .form-control:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(46,134,222,0.12);
        }

        .btn-login {
            width: 100%;
            padding: 13px;
            background: var(--navy);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s, transform 0.1s;
            margin-top: 8px;
        }
        .btn-login:hover { background: var(--navy-dk); color: white; }
        .btn-login:active { transform: scale(0.99); }

        .demo-info {
            margin-top: 24px;
            padding: 14px;
            background: #F0F7FF;
            border-radius: 10px;
            border: 1px solid #BFDBFE;
        }
        .demo-info p { font-size: 12px; color: #1E40AF; margin: 0 0 4px; font-weight: 600; }
        .demo-info .cred { font-size: 12px; color: #374151; margin: 3px 0; }
        .demo-info code { background: #DBEAFE; color: #1D4ED8; padding: 2px 6px; border-radius: 4px; font-family: monospace; }
        .password-toggle { cursor: pointer; position: absolute; right: 12px; top: 50%; transform: translateY(-50%); color: #9CA3AF; z-index: 10; }
        .alert-error { background: #FEF2F2; border: 1px solid #FCA5A5; color: #991B1B; border-radius: 10px; padding: 12px 16px; font-size: 13px; margin-bottom: 20px; display: flex; align-items: center; gap: 8px; }
    </style>
</head>
<body>

<div class="login-wrapper">
    <div class="login-logo">
        <div class="logo-icon"><i class="bi bi-archive-fill"></i></div>
        <h1>Gestion Archivage</h1>
        <p>Système de Gestion des Fonctionnaires</p>
    </div>

    <div class="login-card">
        <h2>Bienvenue</h2>
        <p class="subtitle">Connectez-vous à votre espace de travail</p>

        <?php if ($error): ?>
        <div class="alert-error">
            <i class="bi bi-exclamation-circle-fill"></i>
            <?= htmlspecialchars($error) ?>
        </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-floating mb-3">
                <input type="email" class="form-control" id="email" name="email"
                       placeholder="Email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required autofocus>
                <label for="email"><i class="bi bi-envelope me-1"></i>Adresse email</label>
            </div>

            <div class="form-floating mb-4 position-relative">
                <input type="password" class="form-control" id="password" name="password"
                       placeholder="Mot de passe" required>
                <label for="password"><i class="bi bi-lock me-1"></i>Mot de passe</label>
                <span class="password-toggle" onclick="togglePassword()">
                    <i class="bi bi-eye" id="eye-icon"></i>
                </span>
            </div>

            <button type="submit" class="btn-login">
                <i class="bi bi-box-arrow-in-right me-2"></i>
                Se connecter
            </button>
        </form>

    
    </div>
</div>

<script>
function togglePassword() {
    const input = document.getElementById('password');
    const icon  = document.getElementById('eye-icon');
    if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'bi bi-eye-slash';
    } else {
        input.type = 'password';
        icon.className = 'bi bi-eye';
    }
}
</script>
</body>
</html>
