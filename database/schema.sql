-- Схема базы данных приложения «Персонажи D&D»
-- СУБД: PostgreSQL

DROP TABLE IF EXISTS posts, characters, races, classes, users CASCADE;

-- Пользователи: уникальные username и email
CREATE TABLE users (
    id BIGSERIAL PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMPTZ  NOT NULL DEFAULT now()
);

-- Справочник рас
CREATE TABLE races (
    id SERIAL      PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    description TEXT        NOT NULL DEFAULT ''
);

-- Справочник классов
CREATE TABLE classes (
    id SERIAL      PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    description TEXT        NOT NULL DEFAULT ''
);

-- Персонажи: внешние ключи на пользователя, расу и класс
CREATE TABLE characters (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    race_id INTEGER NOT NULL REFERENCES races(id),
    class_id INTEGER NOT NULL REFERENCES classes(id),
    name VARCHAR(50) NOT NULL,
    level INTEGER NOT NULL CHECK (level BETWEEN 1 AND 20),
    strength INTEGER NOT NULL CHECK (strength BETWEEN 3 AND 18),
    dexterity INTEGER NOT NULL CHECK (dexterity BETWEEN 3 AND 18),
    constitution INTEGER NOT NULL CHECK (constitution BETWEEN 3 AND 18),
    intelligence INTEGER NOT NULL CHECK (intelligence BETWEEN 3 AND 18),
    wisdom INTEGER NOT NULL CHECK (wisdom BETWEEN 3 AND 18),
    charisma INTEGER NOT NULL CHECK (charisma BETWEEN 3 AND 18),
    alignment VARCHAR(50) NOT NULL,
    backstory TEXT NOT NULL DEFAULT '',
    created_at TIMESTAMPTZ NOT NULL DEFAULT now()
);

-- Индекс по владельцу: выборка «мои персонажи»
CREATE INDEX idx_characters_user_id ON characters(user_id);

-- Посты: объявления об играх (ваншот или кампания)
CREATE TABLE posts (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    title VARCHAR(100) NOT NULL,
    format VARCHAR(20) NOT NULL CHECK (format IN ('oneshot', 'campaign')),
    start_level INTEGER NOT NULL CHECK (start_level BETWEEN 1 AND 20),
    description TEXT NOT NULL DEFAULT '',
    created_at TIMESTAMPTZ NOT NULL DEFAULT now()
);

-- Индексы: лента постов и «мои посты»
CREATE INDEX idx_posts_user_id ON posts(user_id);
CREATE INDEX idx_posts_created_at ON posts(created_at DESC);
