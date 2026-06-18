# Habitify - Трекер привычек

Веб-приложение для отслеживания и формирования полезных привычек.

## Стек технологий

- **Backend**: PHP 8.0+
- **Database**: MySQL 8.0+
- **Frontend**: HTML5, CSS3, JavaScript (Fetch API + Axios)
- **UI Framework**: Bootstrap 5.3
- **Charts**: Chart.js
- **Pattern**: MVC с кастомным роутером

## Установка

### Требования

- PHP >= 8.0
- MySQL >= 8.0
- Composer
- Веб-сервер (Apache/Nginx или встроенный PHP сервер)

### Шаги установки

1. Клонируйте репозиторий:
```bash
git clone <repository-url>
cd habitify
```

2. Установите зависимости:
```bash
composer install
```

3. Настройте окружение:
```bash
cp .env.example .env
```

Отредактируйте `.env` с вашими данными:
```
DB_HOST=localhost
DB_PORT=3306
DB_NAME=habitify
DB_USER=root
DB_PASS=
```

4. Создайте базу данных и импортируйте схему:
```bash
mysql -u root -p < database/schema.sql
```

Если база уже существует, выполните миграцию:
```bash
mysql -u root -p < database/add_username.sql
```

5. Настройте веб-сервер:
- Document Root: `public/`
- Или запустите встроенный сервер для разработки:
```bash
php -S localhost:8000 -t public
```

6. Очистите кэш и сгенерируйте автолоадер:
```bash
composer dump-autoload
```

## Доступы по умолчанию

| Роль | Email | Пароль |
|------|-------|--------|
| Admin | admin@habitify.local | admin123 |
| User | user@habitify.local | user123 |

**Важно**: Измените пароли после первого входа!

## Маршруты

- `/` - Главная страница (landing)
- `/login` - Вход
- `/register` - Регистрация
- `/habits` - Список привычек
- `/stats` - Статистика
- `/pricing` - Тарифы
- `/settings` - Настройки
- `/admin` - Админ-панель

## Структура проекта

```
habitify/
├── app/
│   ├── Controllers/     # Контроллеры
│   ├── Models/          # Модели
│   ├── Views/           # Шаблоны
│   ├── Core/            # Ядро (Router, Database, Auth)
│   └── config/          # Конфигурация
├── public/
│   ├── assets/          # CSS, JS, изображения
│   └── index.php        # Точка входа
├── routes/
│   └── web.php          # Маршруты
├── database/
│   └── schema.sql       # Схема БД
├── composer.json
└── README.md
```

## Функционал

### Пользователь

- Регистрация и авторизация
- CRUD привычек
- Отметка выполнения (факт/количество)
- Просмотр статистики и прогресса
- Настройка частоты выполнения
- Оценка качества выполнения (1-5 звёзд)

### Подписки

| Тариф | Привычки | Реклама | Статистика |
|-------|----------|---------|------------|
| Free | 5 | Да | Базовая |
| Basic | 15 | Нет | Расширенная |
| Premium | Безлимит | Нет | Расширенная + экспорт |

### Админ-панель

- Просмотр статистики по пользователям
- Управление подписками
- Бан/разбан пользователей
- Назначение прав администратора

## API Endpoints

### Auth
- `GET /login` - Форма входа
- `POST /login` - Вход
- `GET /register` - Форма регистрации
- `POST /register` - Регистрация
- `GET /logout` - Выход

### Habits
- `GET /habits` - Список привычек
- `POST /habits` - Создание
- `GET /habits/{id}` - Просмотр
- `PUT /habits/{id}` - Обновление
- `DELETE /habits/{id}` - Удаление
- `POST /habits/{id}/log` - Отметка выполнения

### Admin
- `GET /admin` - Дашборд
- `GET /admin/users` - Список пользователей
- `PUT /admin/users/{id}/subscription` - Смена подписки
- `POST /admin/users/{id}/toggle-ban` - Бан/разбан

## Разработка

### Запуск локально

```bash
php -S localhost:8000 -t public
```

Откройте http://localhost:8000

### Логирование

Логи записываются в `error_log` PHP. Для отладки проверьте:
```bash
tail -f /var/log/php/error.log
```

## Лицензия

Copyright (c) 2026 NLP-Core-Team
