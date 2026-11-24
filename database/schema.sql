-- database/schema.sql : tables de ClubHub (à utiliser seulement si vous n'avez pas
-- celui du starter ; sinon gardez le vôtre et adaptez les noms de colonnes).
DROP TABLE IF EXISTS registrations;
DROP TABLE IF EXISTS events;
DROP TABLE IF EXISTS users;

CREATE TABLE users (
    id            INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    name          VARCHAR(100) NOT NULL,
    email         VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role          ENUM('student', 'organizer') NOT NULL DEFAULT 'student',
    created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE events (
    id          INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    club        VARCHAR(100) NOT NULL,
    title       VARCHAR(200) NOT NULL,
    description TEXT NOT NULL,
    place       VARCHAR(100) NOT NULL,
    starts_at   DATETIME NOT NULL,
    ends_at     DATETIME NOT NULL,
    capacity    INT UNSIGNED NOT NULL,
    program     TEXT NOT NULL,   -- une étape par ligne
    bring       TEXT NOT NULL    -- un élément par ligne
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE registrations (
    id         INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    user_id    INT UNSIGNED NOT NULL,
    event_id   INT UNSIGNED NOT NULL,
    motivation VARCHAR(300) NOT NULL DEFAULT '',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_reg_user  FOREIGN KEY (user_id)  REFERENCES users(id)  ON DELETE CASCADE,
    CONSTRAINT fk_reg_event FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    CONSTRAINT uq_user_event UNIQUE (user_id, event_id)   -- une seule inscription par personne et par événement
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
