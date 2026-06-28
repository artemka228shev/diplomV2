<?php
use App\Core\View;

$isLanding = $isLanding ?? false;
$currentUser = $currentUser ?? $user ?? null;
$pageTitle = $pageTitle ?? 'Habitify — Трекер привычек';
$csrf = $csrf ?? '';
?><!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= View::escape($csrf) ?>">
    <title><?= View::escape($pageTitle) ?></title>
    <link rel="icon" type="image/svg+xml" href="<?= BASE_URL ?>/assets/img/favicon.svg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="/assets/css/style.css">
    <?php if (!empty($extraHead)) echo $extraHead; ?>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand fw-bold" href="/">Habitify</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <?php if ($isLanding): ?>
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item"><a class="nav-link" href="#features">Возможности</a></li>
                    <li class="nav-item"><a class="nav-link" href="#how">Как это работает</a></li>
                    <li class="nav-item"><a class="nav-link" href="#pricing">Тарифы</a></li>
                    <?php if (isset($currentUser) && !empty($currentUser['id'])): ?>
                        <?php if (($currentUser['role'] ?? '') === 'admin'): ?>
                        <li class="nav-item ms-lg-3">
                            <a class="btn btn-outline-light" href="/admin"><i class="bi bi-shield-lock me-1"></i>Админ-панель</a>
                        </li>
                        <li class="nav-item ms-2">
                            <a class="btn btn-primary" href="/logout">Выйти</a>
                        </li>
                        <?php else: ?>
                        <li class="nav-item ms-lg-3">
                            <a class="btn btn-outline-light" href="/habits"><i class="bi bi-grid me-1"></i>Мои привычки</a>
                        </li>
                        <li class="nav-item ms-2">
                            <a class="btn btn-primary" href="/logout">Выйти</a>
                        </li>
                        <?php endif; ?>
                    <?php else: ?>
                    <li class="nav-item ms-lg-3">
                        <a class="btn btn-outline-light" href="/login">Войти</a>
                    </li>
                    <li class="nav-item ms-2">
                        <a class="btn btn-primary" href="/register">Начать бесплатно</a>
                    </li>
                    <?php endif; ?>
                </ul>
                <?php else: ?>
                <ul class="navbar-nav me-auto">
                    <?php if (isset($currentUser) && ($currentUser['role'] ?? '') === 'admin'): ?>
                    <li class="nav-item"><a class="nav-link" href="/admin">Дашборд</a></li>
                    <li class="nav-item"><a class="nav-link" href="/admin/users">Пользователи</a></li>
                    <li class="nav-item"><a class="nav-link" href="/admin/audit">Аудит</a></li>
                    <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="/habits">Привычки</a></li>
                    <li class="nav-item"><a class="nav-link" href="/stats">Статистика</a></li>
                    <?php endif; ?>
                </ul>
                <ul class="navbar-nav">
                    <?php if (isset($currentUser) && !empty($currentUser['id'])): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                            <?= View::escape($currentUser['username'] ?? $currentUser['email']) ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <?php if (($currentUser['role'] ?? '') === 'admin'): ?>
                            <li><a class="dropdown-item" href="/admin">Дашборд</a></li>
                            <li><a class="dropdown-item" href="/admin/users">Пользователи</a></li>
                            <li><a class="dropdown-item" href="/admin/audit">Аудит</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="/logout">Выйти</a></li>
                            <?php else: ?>
                            <li><a class="dropdown-item" href="/settings">Настройки</a></li>
                            <li><a class="dropdown-item" href="/pricing">Подписка</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="/logout">Выход</a></li>
                            <?php endif; ?>
                        </ul>
                    </li>
                    <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="/login">Войти</a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-primary ms-2" href="/register">Регистрация</a>
                    </li>
                    <?php endif; ?>
                </ul>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <main class="<?= $isLanding ? '' : 'container' ?> mt-0">
        <?= $content ?? '' ?>
    </main>

    <?php if (!$isLanding): ?>
    <footer class="mt-5 py-4">
        <div class="container text-center text-muted">
            <p class="mb-0">&copy; 2026 Habitify. Все права защищены.</p>
        </div>
    </footer>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios@1.6.0/dist/axios.min.js"></script>
    <script src="/assets/js/app.js"></script>
    <?php if (!empty($extraScripts)) echo $extraScripts; ?>
</body>
</html>
