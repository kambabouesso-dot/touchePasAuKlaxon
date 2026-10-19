CREATE DATABASE IF NOT EXISTS touche_pas_au_klaxon
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE touche_pas_au_klaxon;

DROP TABLE IF EXISTS trajet;
DROP TABLE IF EXISTS utilisateur;
DROP TABLE IF EXISTS agence;

CREATE TABLE agence (
    id_agence INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nom VARCHAR(100) NOT NULL,

    PRIMARY KEY (id_agence),
    CONSTRAINT uq_agence_nom UNIQUE (nom)
) ENGINE=InnoDB;

CREATE TABLE utilisateur (
    id_utilisateur INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    email VARCHAR(254) NOT NULL,
    telephone VARCHAR(25) NOT NULL,
    mot_de_passe_hash VARCHAR(255) NOT NULL,
    role ENUM('utilisateur', 'admin') NOT NULL DEFAULT 'utilisateur',

    PRIMARY KEY (id_utilisateur),
    CONSTRAINT uq_utilisateur_email UNIQUE (email)
) ENGINE=InnoDB;

CREATE TABLE trajet (
    id_trajet INT UNSIGNED NOT NULL AUTO_INCREMENT,
    date_depart DATETIME NOT NULL,
    date_arrivee DATETIME NOT NULL,
    places_total SMALLINT UNSIGNED NOT NULL,
    places_disponibles SMALLINT UNSIGNED NOT NULL,
    id_auteur INT UNSIGNED NOT NULL,
    id_agence_depart INT UNSIGNED NOT NULL,
    id_agence_arrivee INT UNSIGNED NOT NULL,

    PRIMARY KEY (id_trajet),
    INDEX idx_trajet_date_depart (date_depart),

    CONSTRAINT fk_trajet_auteur
        FOREIGN KEY (id_auteur)
        REFERENCES utilisateur (id_utilisateur)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_trajet_agence_depart
        FOREIGN KEY (id_agence_depart)
        REFERENCES agence (id_agence)
        ON UPDATE RESTRICT
        ON DELETE RESTRICT,

    CONSTRAINT fk_trajet_agence_arrivee
        FOREIGN KEY (id_agence_arrivee)
        REFERENCES agence (id_agence)
        ON UPDATE RESTRICT
        ON DELETE RESTRICT,

    CONSTRAINT chk_trajet_agences_differentes
        CHECK (id_agence_depart <> id_agence_arrivee),

    CONSTRAINT chk_trajet_dates
        CHECK (date_arrivee > date_depart),

    CONSTRAINT chk_trajet_places_total
        CHECK (places_total > 0),

    CONSTRAINT chk_trajet_places_disponibles
        CHECK (places_disponibles <= places_total)
) ENGINE=InnoDB;