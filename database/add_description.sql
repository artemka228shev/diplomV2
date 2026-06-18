-- Добавить поле description в таблицу habits
ALTER TABLE habits ADD COLUMN description TEXT NULL AFTER title;
