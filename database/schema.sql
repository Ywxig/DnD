-- ============================================================
-- Схема базы данных приложения «Персонажи D&D»
-- СУБД: PostgreSQL
-- ============================================================

DROP TABLE IF EXISTS password_resets, characters, races, classes, users CASCADE;

-- Пользователи: уникальные username и email, роль (user/admin)
CREATE TABLE users (
    id            BIGSERIAL    PRIMARY KEY,
    username      VARCHAR(50)  NOT NULL UNIQUE,
    email         VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role          VARCHAR(20)  NOT NULL DEFAULT 'user'
                  CHECK (role IN ('user', 'admin')),
    created_at    TIMESTAMPTZ  NOT NULL DEFAULT now()
);

-- Справочник рас
CREATE TABLE races (
    id          SERIAL      PRIMARY KEY,
    name        VARCHAR(50) NOT NULL UNIQUE,
    description TEXT        NOT NULL DEFAULT ''
);

-- Справочник классов
CREATE TABLE classes (
    id          SERIAL      PRIMARY KEY,
    name        VARCHAR(50) NOT NULL UNIQUE,
    description TEXT        NOT NULL DEFAULT ''
);

-- Персонажи: внешние ключи на пользователя, расу и класс
CREATE TABLE characters (
    id            BIGSERIAL   PRIMARY KEY,
    user_id       BIGINT      NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    race_id       INTEGER     NOT NULL REFERENCES races(id),
    class_id      INTEGER     NOT NULL REFERENCES classes(id),
    name          VARCHAR(50) NOT NULL,
    level         INTEGER     NOT NULL CHECK (level BETWEEN 1 AND 20),
    strength      INTEGER     NOT NULL CHECK (strength BETWEEN 3 AND 18),
    dexterity     INTEGER     NOT NULL CHECK (dexterity BETWEEN 3 AND 18),
    constitution  INTEGER     NOT NULL CHECK (constitution BETWEEN 3 AND 18),
    intelligence  INTEGER     NOT NULL CHECK (intelligence BETWEEN 3 AND 18),
    wisdom        INTEGER     NOT NULL CHECK (wisdom BETWEEN 3 AND 18),
    charisma      INTEGER     NOT NULL CHECK (charisma BETWEEN 3 AND 18),
    alignment     VARCHAR(50) NOT NULL,
    backstory     TEXT        NOT NULL DEFAULT '',
    created_at    TIMESTAMPTZ NOT NULL DEFAULT now()
);

-- Индексы по внешним ключам и уровню
CREATE INDEX idx_characters_user_id  ON characters(user_id);
CREATE INDEX idx_characters_race_id  ON characters(race_id);
CREATE INDEX idx_characters_class_id ON characters(class_id);
CREATE INDEX idx_characters_level    ON characters(level);
CREATE INDEX idx_characters_created  ON characters(created_at);

-- Токены восстановления пароля: хранится только SHA-256-хеш токена
CREATE TABLE password_resets (
    id         BIGSERIAL   PRIMARY KEY,
    user_id    BIGINT      NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    token_hash CHAR(64)    NOT NULL UNIQUE,
    expires_at TIMESTAMPTZ NOT NULL,
    used       BOOLEAN     NOT NULL DEFAULT FALSE,
    created_at TIMESTAMPTZ NOT NULL DEFAULT now()
);

CREATE INDEX idx_password_resets_user_id ON password_resets(user_id);
