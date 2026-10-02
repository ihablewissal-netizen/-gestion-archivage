-- ============================================================
-- Gestion Archivage - Database Schema
-- ============================================================

CREATE DATABASE IF NOT EXISTS gestion_archivage
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE gestion_archivage;

-- ----------------------------
-- Table: fonctionnaires
-- ----------------------------
CREATE TABLE IF NOT EXISTS fonctionnaires (
    id INT PRIMARY KEY AUTO_INCREMENT,
    matricule VARCHAR(50) UNIQUE NOT NULL,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    date_naissance DATE,
    cin VARCHAR(20),
    telephone VARCHAR(20),
    statut VARCHAR(50) DEFAULT 'Actif',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ----------------------------
-- Table: service
-- ----------------------------
CREATE TABLE IF NOT EXISTS service (
    id INT PRIMARY KEY AUTO_INCREMENT,
    matricule_service VARCHAR(50) UNIQUE NOT NULL,
    nom_service VARCHAR(150) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ----------------------------
-- Table: affectations
-- ----------------------------
CREATE TABLE IF NOT EXISTS affectations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    matricule VARCHAR(50) NOT NULL,
    poste_occupe VARCHAR(150) NOT NULL,
    date_debut DATE,
    date_fin DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ----------------------------
-- Table: situations_administratives
-- ----------------------------
CREATE TABLE IF NOT EXISTS situations_administratives (
    id INT PRIMARY KEY AUTO_INCREMENT,
    matricule VARCHAR(50) NOT NULL,
    grade VARCHAR(100),
    echelle VARCHAR(50),
    echelon VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ----------------------------
-- Table: carrieres
-- ----------------------------
CREATE TABLE IF NOT EXISTS carrieres (
    id INT PRIMARY KEY AUTO_INCREMENT,
    matricule VARCHAR(50) NOT NULL,
    date_debut DATE,
    date_fin DATE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ----------------------------
-- Table: documents_archives
-- ----------------------------
CREATE TABLE IF NOT EXISTS documents_archives (
    id INT PRIMARY KEY AUTO_INCREMENT,
    matricule VARCHAR(50) NOT NULL,
    nom_fonctionnaire VARCHAR(100),
    prenom VARCHAR(100),
    type_document VARCHAR(100),
    titre_document VARCHAR(200) NOT NULL,
    nom_fichier VARCHAR(255),
    date_upload DATE DEFAULT (CURRENT_DATE),
    uploaded_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ----------------------------
-- Table: utilisateurs
-- ----------------------------
CREATE TABLE IF NOT EXISTS utilisateurs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nom_utilisateur VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    role ENUM('admin', 'user') DEFAULT 'user',
    mot_de_passe VARCHAR(255) NOT NULL,
    matricule VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ----------------------------
-- Default Users
-- admin / admin123
-- user  / user123
-- ----------------------------
INSERT INTO utilisateurs (nom_utilisateur, email, role, mot_de_passe, matricule) VALUES
('Administrateur', 'admin@archivage.ma', 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL),
('Utilisateur Test', 'user@archivage.ma', 'user', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'MAT001');
('Utilisateur Test', 'user@archivage1.ma', 'user', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'MAT002'), 
('Utilisateur Test', 'user@archivage2.ma', 'user', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'MAT003'),
('Utilisateur Test', 'user@archivage3.ma', 'user', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'MAT004')

-- Note: Both passwords above are hashed "password" - run setup.php to set real passwords


-- ----------------------------
-- Sample Data: Services
-- ----------------------------
INSERT INTO service (matricule_service, nom_service, description) VALUES
('SRV001', 'Direction des Ressources Humaines', 'Gestion du personnel et des affaires sociales'),
('SRV002', 'Service Informatique', 'Infrastructure et systèmes d''information'),
('SRV003', 'Service Financier', 'Comptabilité et gestion budgétaire'),
('SRV004', 'Service des Archives', 'Conservation et gestion documentaire');

-- ----------------------------
-- Sample Data: Affectations
-- ----------------------------
INSERT INTO affectations (matricule, poste_occupe, date_debut, date_fin) VALUES
('MAT001', 'Chef de Service RH', '2020-01-01', NULL),
('MAT002', 'Ingénieur Informatique', '2019-06-15', NULL),
('MAT003', 'Directeur Financier', '2005-03-01', '2023-12-31'),
('MAT004', 'Archiviste Principal', '2021-09-01', NULL);

-- ----------------------------
-- Sample Data: Situations Administratives
-- ----------------------------
INSERT INTO situations_administratives (matricule, grade, echelle, echelon) VALUES
('MAT001', 'Administrateur Principal', 'Echelle 11', 'Echelon 4'),
('MAT002', 'Ingénieur d''Etat', 'Echelle 11', 'Echelon 2'),
('MAT003', 'Administrateur', 'Echelle 10', 'Echelon 8'),
('MAT004', 'Technicien Spécialisé', 'Echelle 9', 'Echelon 3');

-- ----------------------------
-- Sample Data: Carrières
-- ----------------------------
INSERT INTO carrieres (matricule, date_debut, date_fin, description) VALUES
('MAT001', '2010-09-01', NULL, 'Recrutement initial en tant qu\'agent administratif'),
('MAT002', '2015-06-01', NULL, 'Recrutement initial après concours'),
('MAT003', '1998-01-15', '2023-12-31', 'Carrière complète au sein de l\'administration'),
('MAT004', '2018-03-01', NULL, 'Recrutement au service des archives');
