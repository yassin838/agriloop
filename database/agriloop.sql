CREATE DATABASE IF NOT EXISTS agriloop
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE agriloop;

CREATE TABLE IF NOT EXISTS utilisateur (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    email VARCHAR(180) NOT NULL UNIQUE,
    mot_de_passe VARCHAR(255) NOT NULL,
    telephone VARCHAR(30) NOT NULL,
    ville VARCHAR(100) NOT NULL,
    role ENUM('agriculteur', 'acheteur', 'administrateur') NOT NULL,
    actif BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS association (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(150) NOT NULL,
    domaine VARCHAR(100) NOT NULL,
    ville VARCHAR(100) NOT NULL,
    telephone VARCHAR(30) NOT NULL,
    email VARCHAR(180) NOT NULL,
    responsable VARCHAR(150) NOT NULL,
    statut ENUM('en_attente', 'active', 'inactive') NOT NULL DEFAULT 'en_attente',
    id_user INT UNSIGNED NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_association_email (email),
    CONSTRAINT fk_association_user
        FOREIGN KEY (id_user) REFERENCES utilisateur(id)
        ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS don (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    quantite FLOAT NOT NULL,
    date_retrait DATE NOT NULL,
    remarque TEXT NULL,
    statut ENUM('en_attente', 'confirme', 'annule') NOT NULL DEFAULT 'en_attente',
    id_association INT UNSIGNED NOT NULL,
    id_user INT UNSIGNED NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_don_association
        FOREIGN KEY (id_association) REFERENCES association(id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_don_user
        FOREIGN KEY (id_user) REFERENCES utilisateur(id)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

INSERT INTO association (nom, domaine, ville, telephone, email, responsable)
VALUES
('Solidarite rurale', 'Alimentation solidaire', 'Tunis', '22111222', 'contact@solidarite-rurale.tn', 'Nadia Ben Salem'),
('Terre partagee', 'Agriculture', 'Sousse', '23777888', 'contact@terre-partagee.tn', 'Walid Trabelsi');
