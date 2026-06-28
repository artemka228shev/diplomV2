<?php
use App\Core\View;
$user = $user ?? null;
?>

<section class="landing-hero">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-6">
                <div class="hero-badge">
                    <i class="bi bi-stars"></i>
                    Бесплатный старт — без кредитной карты
                </div>
                <h1 class="hero-title">Формируй привычки,<br>которые изменят жизнь</h1>
                <p class="hero-subtitle">
                    Простой трекер для ежедневных привычек.
                    Отслеживай прогресс, анализируй успехи и не прерывай серию выполнения.
                </p>
                <div class="d-flex gap-3 flex-wrap">
                    <?php if (isset($user) && !empty($user['id'])): ?>
                        <?php if (($user['role'] ?? '') === 'admin'): ?>
                        <a href="/admin" class="btn btn-primary btn-lg px-4">
                            <i class="bi bi-shield-lock me-2"></i>Админ-панель
                        </a>
                        <a href="/admin/users" class="btn btn-outline-light btn-lg px-4">
                            <i class="bi bi-people me-2"></i>Пользователи
                        </a>
                        <?php else: ?>
                        <a href="/habits" class="btn btn-primary btn-lg px-4">
                            <i class="bi bi-grid me-2"></i>Мои привычки
                        </a>
                        <a href="/stats" class="btn btn-outline-light btn-lg px-4">
                            <i class="bi bi-graph-up me-2"></i>Статистика
                        </a>
                        <?php endif; ?>
                    <?php else: ?>
                    <a href="/register" class="btn btn-primary btn-lg px-4">
                        <i class="bi bi-rocket me-2"></i>Создать аккаунт
                    </a>
                    <a href="#how" class="btn btn-outline-light btn-lg px-4">
                        Узнать больше
                    </a>
                    <?php endif; ?>
                </div>
                <div class="hero-stats">
                    <div class="hero-stat">
                        <div class="stat-number">5 000+</div>
                        <div class="stat-label">Пользователей</div>
                    </div>
                    <div class="hero-stat">
                        <div class="stat-number">50 000+</div>
                        <div class="stat-label">Привычек создано</div>
                    </div>
                    <div class="hero-stat">
                        <div class="stat-number">89%</div>
                        <div class="stat-label">Успешных дней</div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 d-none d-lg-block">
                <div class="app-preview">
                    <div class="app-preview-item">
                        <span><i class="bi bi-check-circle-fill"></i> Утренняя зарядка</span>
                        <span class="preview-meta">Серия: 12 дней</span>
                    </div>
                    <div class="app-preview-item">
                        <span><i class="bi bi-check-circle-fill"></i> Чтение книг</span>
                        <span class="preview-meta">Серия: 8 дней</span>
                    </div>
                    <div class="app-preview-item">
                        <span><i class="bi bi-circle text-muted-2"></i> Медитация</span>
                        <span class="preview-meta">Ожидает выполнения</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section id="features" class="landing-section">
    <div class="container">
        <div class="text-center">
            <h2 class="section-title">Всё, что нужно для привычек</h2>
            <p class="section-subtitle mx-auto" style="max-width: 540px;">Простые инструменты для ежедневного прогресса</p>
        </div>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="feature-box">
                    <div class="feature-icon"><i class="bi bi-check2-square"></i></div>
                    <h4>Отслеживание привычек</h4>
                    <p>Создавай привычки любого типа: простые (да/нет) или количественные с целевыми значениями.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-box">
                    <div class="feature-icon is-success"><i class="bi bi-graph-up-arrow"></i></div>
                    <h4>Статистика и графики</h4>
                    <p>Наглядные графики выполнения, недельная и месячная статистика по каждой привычке.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-box">
                    <div class="feature-icon is-warning"><i class="bi bi-fire"></i></div>
                    <h4>Серии выполнения</h4>
                    <p>Отслеживай текущую серию подряд и рекорд. Не прерывай цепочку успешных дней!</p>
                </div>
            </div>
        </div>
    </div>
</section>

<section id="pricing" class="landing-section">
    <div class="container">
        <div class="text-center">
            <h2 class="section-title">Тарифные планы</h2>
            <p class="section-subtitle mx-auto" style="max-width: 540px;">Выберите подходящий план для достижения целей</p>
        </div>
        <div class="row g-4">
            <?php
            $plans = [
                ['key' => 'free', 'name' => 'Free', 'price' => '0', 'features' => ['До 5 привычек', 'Базовая статистика', 'Отметка выполнения', 'Напоминания']],
                ['key' => 'basic', 'name' => 'Basic', 'price' => '299', 'featured' => true, 'features' => ['До 15 привычек', 'Базовая статистика', 'Отметка выполнения', 'Напоминания', 'Без рекламы']],
                ['key' => 'premium', 'name' => 'Premium', 'price' => '599', 'features' => ['Безлимит привычек', 'Расширенная статистика', 'Отметка выполнения', 'Напоминания', 'Без рекламы', 'Расширенная аналитика', 'Экспорт данных']]
            ];
            foreach ($plans as $plan): ?>
            <div class="col-md-4">
                <div class="card pricing-card h-100 <?= !empty($plan['featured']) ? 'featured' : '' ?>">
                    <div class="card-body text-center">
                        <?php if (!empty($plan['featured'])): ?>
                        <span class="badge bg-primary mb-3">Популярный</span>
                        <?php endif; ?>
                        <h3><?= \App\Core\View::escape($plan['name']) ?></h3>
                        <div class="price"><?= $plan['price'] ?> ₽<small>/мес</small></div>
                        <ul class="pricing-features list-unstyled mt-3">
                            <?php foreach ($plan['features'] as $f): ?>
                            <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i> <?= \App\Core\View::escape($f) ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <?php if (!isset($user) || empty($user['id'])): ?>
                            <a href="/register" class="btn btn-primary w-100">Начать бесплатно</a>
                        <?php elseif (($user['subscription_type'] ?? 'free') === $plan['key']): ?>
                            <button class="btn btn-outline-primary w-100" disabled>Текущий план</button>
                        <?php else: ?>
                            <a href="/pricing" class="btn btn-primary w-100">Выбрать</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="landing-cta">
    <div class="container">
        <?php if (isset($user) && !empty($user['id'])): ?>
            <?php if (($user['role'] ?? '') === 'admin'): ?>
            <h2>Добро пожаловать в админ-панель!</h2>
            <a href="/admin" class="btn btn-primary btn-lg px-5">
                <i class="bi bi-shield-lock me-2"></i>Перейти в админ-панель
            </a>
            <?php else: ?>
            <h2>Продолжайте формировать привычки!</h2>
            <a href="/habits" class="btn btn-primary btn-lg px-5">
                <i class="bi bi-grid me-2"></i>Перейти к привычкам
            </a>
            <?php endif; ?>
        <?php else: ?>
        <h2>Готовы изменить свою жизнь?</h2>
        <a href="/register" class="btn btn-primary btn-lg px-5">
            <i class="bi bi-rocket me-2"></i>Создать аккаунт бесплатно
        </a>
        <?php endif; ?>
    </div>
</section>
