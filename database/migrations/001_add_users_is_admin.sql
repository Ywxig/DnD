-- Миграция для уже существующей базы: флаг администратора у пользователя.
-- Выполнить один раз:
--   psql -h localhost -p 5433 -U dnd -d dnd -f database/migrations/001_add_users_is_admin.sql

ALTER TABLE users
    ADD COLUMN IF NOT EXISTS is_admin BOOLEAN NOT NULL DEFAULT FALSE;

-- Делаем тестового пользователя aragorn администратором.
UPDATE users SET is_admin = TRUE WHERE lower(username) = 'aragorn';
