# API Документация Habitify

## Аутентификация

### POST /login

**Body:**
```
login: string (email или username)
password: string
```

**Response 200:**
```json
{
  "redirect": "/"
}
```

**Response 401:**
```json
{
  "error": "Неверный логин или пароль"
}
```

### POST /register
Регистрация нового пользователя

**Body:**
```
username: string
email: string
password: string
password_confirm: string
```

**Response 200:**
```json
{
  "redirect": "/habits"
}
```

### GET /logout
Выход из системы

**Response:** 302 Redirect к /login

---

## Привычки

### GET /habits
Список привычек пользователя

**Response 200:** HTML страница со списком

### POST /habits
Создание новой привычки

**Body:**
```
title: string
type: "boolean" | "quantitative"
frequency: "daily" | "weekly" | "custom"
target_value: number (для quantitative)
unit: string (для quantitative)
days_of_week: array (для custom)
```

**Response 200:**
```json
{
  "success": true,
  "habit_id": 123
}
```

**Response 403:**
```json
{
  "error": "Достигнут лимит привычек",
  "limit": 5,
  "current": 5,
  "upgrade_url": "/pricing"
}
```

### GET /habits/{id}
Просмотр детали привычки

**Response 200:** HTML страница

### PUT /habits/{id}
Обновление привычки

**Body:**
```
title: string
type: string
frequency: string
target_value: number
unit: string
```

**Response 200:**
```json
{
  "success": true
}
```

### DELETE /habits/{id}
Удаление привычки

**Response 200:**
```json
{
  "success": true
}
```

### POST /habits/{id}/log
Отметка выполнения привычки

**Body:**
```
date: string (Y-m-d)
completed: "true" | "false"
value: number (для quantitative)
quality_rating: 1-5
```

**Response 200:**
```json
{
  "success": true
}
```

---

## Статистика

### GET /stats
Страница статистики

**Response 200:** HTML с графиками

---

## Подписки

### GET /pricing
Страница тарифов

**Response 200:** HTML страница

### POST /api/subscribe
Изменение подписки

**Body:**
```
plan: "free" | "basic" | "premium"
```

**Response 200:**
```json
{
  "success": true,
  "subscription_type": "basic",
  "redirect": "/habits"
}
```

---

## Настройки

### GET /settings
Страница настроек

**Response 200:** HTML страница

### POST /api/settings
Обновление настроек

**Body:**
```
action: "change_password" | "delete_account"
current_password: string (для change_password)
new_password: string
new_password_confirm: string
```

**Response 200:**
```json
{
  "success": true
}
```

---

## Админ-панель

### GET /admin
Дашборд администратора

**Response 200:** HTML страница
**Response 403:** Доступ запрещён

### GET /admin/users
Список пользователей

**Response 200:** HTML таблица

### PUT /admin/users/{id}/subscription
Изменение подписки пользователя

**Body:**
```
subscription_type: "free" | "basic" | "premium"
```

**Response 200:**
```json
{
  "success": true,
  "subscription_type": "premium"
}
```

### POST /admin/users/{id}/toggle-ban
Бан/разбан пользователя

**Response 200:**
```json
{
  "success": true,
  "is_banned": 0
}
```

### POST /admin/users/{id}/make-admin
Выдача прав администратора

**Response 200:**
```json
{
  "success": true
}
```

### POST /admin/users/{id}/remove-admin
Убирание прав администратора

**Response 200:**
```json
{
  "success": true
}
```

