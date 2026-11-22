-- AgriLoop — base de données du back office (d'après le diagramme de classes)
CREATE DATABASE IF NOT EXISTS agriloop CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE agriloop;

DROP TABLE IF EXISTS signalement;
DROP TABLE IF EXISTS avis;
DROP TABLE IF EXISTS offre;
DROP TABLE IF EXISTS utilisateur;

-- Tables des autres modules (simplifiées)
CREATE TABLE utilisateur (
  id  INT AUTO_INCREMENT PRIMARY KEY,
  nom VARCHAR(100) NOT NULL
);

CREATE TABLE offre (
  id  INT AUTO_INCREMENT PRIMARY KEY,
  nom VARCHAR(100) NOT NULL
);

-- Avis : reçu par une offre, rédigé par un utilisateur
CREATE TABLE avis (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  titre       VARCHAR(150) NOT NULL,
  note        TINYINT NOT NULL,                       -- de 1 à 5
  fraicheur   TINYINT NOT NULL,                       -- de 0 à 5
  commentaire TEXT NOT NULL,
  date_avis   DATE NOT NULL DEFAULT (CURRENT_DATE),
  statut      VARCHAR(20) NOT NULL DEFAULT 'publie',  -- publie ou masque
  idOffre     INT NOT NULL,
  idAuteur    INT NOT NULL,
  FOREIGN KEY (idOffre)  REFERENCES offre(id)       ON DELETE CASCADE,
  FOREIGN KEY (idAuteur) REFERENCES utilisateur(id) ON DELETE CASCADE
);

-- Signalement : vise un avis
CREATE TABLE signalement (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  motif       VARCHAR(20) NOT NULL,                   -- spam, injurieux, faux ou autre
  description TEXT NOT NULL,
  auteur      VARCHAR(100) NOT NULL,
  date_signalement DATE NOT NULL DEFAULT (CURRENT_DATE),
  statut      VARCHAR(20) NOT NULL DEFAULT 'attente', -- attente, traite ou rejete
  idAvis      INT NOT NULL,
  FOREIGN KEY (idAvis) REFERENCES avis(id) ON DELETE CASCADE
);

-- Données d'exemple (l'utilisateur 4 est celui du site : voir USER_ID dans avis.php et signalement.php)
INSERT INTO utilisateur (nom) VALUES ('Sami Ben Ali'), ('Maya Trabelsi'), ('Karim Jlassi'), ('Visiteur');
INSERT INTO offre (nom) VALUES ('Tomates bio'), ('Pommes de terre'), ('Oranges');

INSERT INTO avis (titre, note, fraicheur, commentaire, date_avis, idOffre, idAuteur) VALUES
('Produit très frais',       5, 5, 'Les tomates étaient vraiment fraîches et bien emballées.', '2026-11-20', 1, 1),
('Livraison correcte',       4, 4, 'Pommes de terre de bonne qualité, livrées à temps.',       '2026-11-21', 2, 2),
('Achetez sur mon site !!!', 1, 0, 'Promo incroyable sur www.exemple-spam.com',                '2026-11-21', 3, 3);

INSERT INTO signalement (motif, description, auteur, date_signalement, idAvis) VALUES
('spam', 'Publicité pour un site externe.', 'Sami Ben Ali', '2026-11-22', 3);
