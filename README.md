# 📁 Gestion Archivage — Guide d'installation complet

**Application de gestion et d'archivage des documents des fonctionnaires**

---

## 🖥️ Prérequis

Vous avez besoin de :
- **WAMP** (Windows) ou **XAMPP** (Windows/Mac/Linux) ou **LAMP** (Linux)
- **PHP 8.0+** avec extensions : `pdo_mysql`, `fileinfo`, `mbstring`
- **MySQL 5.7+** ou **MariaDB 10.3+**
- Un navigateur web moderne

---

## 🚀 ÉTAPE 1 — Installer WAMP (Windows)

1. Téléchargez **WampServer** sur : https://www.wampserver.com/
2. Lancez l'installateur et suivez les instructions
3. Une fois installé, lancez WampServer (icône dans la barre des tâches)
4. L'icône doit devenir **verte** (serveur démarré)
5. Cliquez sur l'icône → **Localhost** pour vérifier que ça fonctionne

> **Avec XAMPP :** Téléchargez sur https://www.apachefriends.org/, installez et démarrez **Apache** + **MySQL** depuis le panneau de contrôle XAMPP.

---

## 📂 ÉTAPE 2 — Installer l'application

### 2.1 Copier les fichiers

1. Localisez le dossier web de votre serveur :
   - **WAMP** : `C:\wamp64\www\`
   - **XAMPP** : `C:\xampp\htdocs\`
   - **Linux/Mac LAMP** : `/var/www/html/`

2. Copiez le dossier `gestion_archivage` dans ce répertoire

   Résultat : `C:\wamp64\www\gestion_archivage\` (ou équivalent)

### 2.2 Permissions du dossier uploads

Le dossier `uploads/` doit être accessible en écriture :

**Windows** : Clic droit sur `uploads/` → Propriétés → Sécurité → Donner le contrôle total à "Tout le monde"

**Linux/Mac** :
```bash
chmod 755 /var/www/html/gestion_archivage/uploads/
chown www-data:www-data /var/www/html/gestion_archivage/uploads/
```

---

## 🗄️ ÉTAPE 3 — Créer la base de données

### 3.1 Ouvrir phpMyAdmin

1. Dans WampServer : clic sur l'icône → **phpMyAdmin**
2. Dans XAMPP : http://localhost/phpmyadmin/
3. Connectez-vous (par défaut : user=`root`, password=vide)

### 3.2 Importer le fichier SQL

1. Cliquez sur **"Nouveau"** dans le panneau gauche (pour créer une BDD)
2. Nommez-la `gestion_archivage` → Interclassement : `utf8mb4_unicode_ci` → **Créer**
3. Dans la BDD créée, cliquez sur l'onglet **"Importer"**
4. Cliquez **"Parcourir"** et sélectionnez le fichier `database.sql`
5. Cliquez **"Importer"** en bas de la page
6. ✅ Vous devriez voir "L'importation s'est bien terminée"

---

## ⚙️ ÉTAPE 4 — Configurer la connexion

Ouvrez le fichier `config/database.php` et modifiez si nécessaire :

```php
define('DB_HOST', 'localhost');      // Hôte de la BDD
define('DB_NAME', 'gestion_archivage'); // Nom de la BDD
define('DB_USER', 'root');           // Utilisateur MySQL
define('DB_PASS', '');               // Mot de passe MySQL (vide par défaut)

define('APP_URL', 'http://localhost/gestion_archivage'); // URL de l'app
```

> ⚠️ **Important** : Assurez-vous que `APP_URL` correspond exactement à l'URL de votre application.

---

## 🔑 ÉTAPE 5 — Configurer les mots de passe (OBLIGATOIRE)

Le fichier SQL inclut des utilisateurs mais les mots de passe doivent être initialisés :

1. Ouvrez votre navigateur et accédez à :
   ```
   http://localhost/gestion_archivage/setup.php
   ```

2. Vous verrez une page de confirmation avec les identifiants configurés

3. ✅ **SUPPRIMEZ `setup.php` immédiatement après** pour des raisons de sécurité !

---

## 🌐 ÉTAPE 6 — Lancer l'application

Ouvrez votre navigateur et accédez à :
```
http://localhost/gestion_archivage/login.php
```

### Identifiants par défaut

| Rôle | Email | Mot de passe |
|------|-------|--------------|
| 🔑 Admin | admin@archivage.ma | admin123 |
| 👤 User | user@archivage.ma | user123 |

---

## 🔐 Différences Admin vs Utilisateur

### 👑 Rôle Admin — Accès complet
- ✅ Tableau de bord avec toutes les statistiques
- ✅ Gestion des fonctionnaires (CRUD)
- ✅ Gestion des services (CRUD)
- ✅ Gestion des affectations (CRUD)
- ✅ Gestion des situations administratives (CRUD)
- ✅ Gestion des carrières (CRUD)
- ✅ **Tous** les documents archivés (voir, ajouter, modifier, supprimer)
- ✅ Gestion des utilisateurs (CRUD)

### 👤 Rôle User — Accès restreint
- ✅ Tableau de bord personnel (ses propres stats)
- ✅ Voir **uniquement** ses propres documents archivés
- ✅ Archiver de nouveaux documents (liés à son matricule)
- ✅ Modifier/supprimer ses propres documents
- ✅ Télécharger ses propres fichiers
- ❌ Accès aux modules fonctionnaires, services, etc.
- ❌ Accès à la gestion des utilisateurs

---

## 📋 Structure du projet

```
gestion_archivage/
├── 📄 login.php          — Page de connexion
├── 📄 logout.php         — Déconnexion
├── 📄 dashboard.php      — Tableau de bord
├── 📄 setup.php          — Initialisation des mots de passe (supprimer après)
├── 📄 database.sql       — Script SQL de la base de données
├── config/
│   └── 📄 database.php   — Configuration BDD + fonctions utilitaires
├── includes/
│   ├── 📄 header.php     — En-tête HTML + CSS
│   ├── 📄 sidebar.php    — Menu latéral
│   └── 📄 footer.php     — Pied de page + JS
├── modules/
│   ├── fonctionnaires/   — CRUD fonctionnaires (admin)
│   ├── services/         — CRUD services (admin)
│   ├── affectations/     — CRUD affectations (admin)
│   ├── situations/       — CRUD situations administratives (admin)
│   ├── carrieres/        — CRUD carrières (admin)
│   ├── documents/        — CRUD documents archivés (admin+user)
│   └── utilisateurs/     — CRUD utilisateurs (admin)
└── uploads/              — Fichiers archivés uploadés
```

---

## 🛠️ Fonctionnalités

| Fonctionnalité | Description |
|---|---|
| 🔐 Authentification | Session PHP sécurisée, mots de passe hashés (bcrypt) |
| 👥 Gestion rôles | Admin (accès total) vs User (accès restreint) |
| 📄 Upload fichiers | PDF, Word, Excel, Images, ZIP — Max 10MB |
| ⬇️ Téléchargement | Fichiers archivés téléchargeables |
| 🔍 Recherche | Filtres de recherche sur toutes les listes |
| 📃 Pagination | 10 enregistrements par page |
| ✅ Validation | Validation côté serveur pour tous les formulaires |
| 📱 Responsive | Interface adaptée mobile/tablette/desktop |
| ⚠️ Confirmations | Confirmation avant suppression |
| 🔔 Notifications | Messages flash (succès/erreur) |

---

## ❓ Résolution de problèmes courants

### ❌ "Erreur de connexion à la base de données"
→ Vérifiez DB_HOST, DB_USER, DB_PASS dans `config/database.php`
→ Assurez-vous que MySQL est démarré dans WAMP/XAMPP

### ❌ "Page non trouvée" (404)
→ Vérifiez que `APP_URL` dans `config/database.php` correspond à votre URL
→ Vérifiez que les fichiers sont dans le bon dossier (`www/` ou `htdocs/`)

### ❌ "Erreur lors de l'envoi du fichier"
→ Vérifiez les permissions du dossier `uploads/`
→ Vérifiez que `upload_max_filesize` dans `php.ini` est ≥ 10M

### ❌ Mot de passe incorrect après import SQL
→ Relancez `http://localhost/gestion_archivage/setup.php`
→ Le fichier SQL contient un hash générique, `setup.php` le remplace

### ❌ Modules non accessibles
→ Assurez-vous d'être connecté en tant qu'**admin** pour accéder aux modules restreints

---

## 🔒 Sécurité en production

- Changez les mots de passe par défaut immédiatement
- Supprimez `setup.php` après utilisation
- Configurez HTTPS sur votre serveur
- Changez `DB_USER`/`DB_PASS` pour des credentials non-root
- Ajoutez un `.htaccess` dans `uploads/` pour désactiver l'exécution PHP

---

*Application développée avec PHP 8+, MySQL, Bootstrap 5 — Gestion Archivage v1.0*
