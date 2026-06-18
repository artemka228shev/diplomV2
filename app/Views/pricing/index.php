<?php
use App\Core\View;
$user = $user ?? null;
$isAuth = !empty($user['id']);
$currentPlan = $user['subscription_type'] ?? 'free';
?>

<div class="text-center mb-5">
    <h1>Тарифные планы</h1>
    <p class="text-muted">Выберите подходящий план для достижения целей</p>
</div>

<div class="row">
    <?php
    $plans = [
        ['key' => 'free', 'name' => 'Free', 'price' => '0', 'features' => ['До 5 привычек', 'Базовая статистика', 'Отметка выполнения', 'Напоминания']],
        ['key' => 'basic', 'name' => 'Basic', 'price' => '299', 'featured' => true, 'features' => ['До 15 привычек', 'Базовая статистика', 'Отметка выполнения', 'Напоминания', 'Без рекламы']],
        ['key' => 'premium', 'name' => 'Premium', 'price' => '599', 'features' => ['Безлимит привычек', 'Расширенная статистика', 'Отметка выполнения', 'Напоминания', 'Без рекламы', 'Расширенная аналитика', 'Экспорт данных']]
    ];
    foreach ($plans as $plan): ?>
    <div class="col-md-4 mb-4">
        <div class="card pricing-card h-100 <?= !empty($plan['featured']) ? 'featured' : '' ?>">
            <div class="card-body">
                <?php if (!empty($plan['featured'])): ?>
                <span class="badge bg-primary mb-3">Популярный</span>
                <?php endif; ?>
                <h3><?= View::escape($plan['name']) ?></h3>
                <div class="price"><?= $plan['price'] ?> ₽<small>/мес</small></div>
                <ul class="pricing-features">
                    <?php foreach ($plan['features'] as $f): ?>
                    <li><i class="bi bi-check-circle-fill"></i> <?= View::escape($f) ?></li>
                    <?php endforeach; ?>
                </ul>
                <?php if (!$isAuth): ?>
                    <a href="/register" class="btn btn-primary w-100">Начать бесплатно</a>
                <?php elseif ($currentPlan === $plan['key']): ?>
                <button class="btn btn-outline-primary w-100" disabled>Текущий план</button>
                <?php else: ?>
                <form action="/api/subscribe" method="POST">
                    <?= View::csrfField() ?>
                    <input type="hidden" name="plan" value="<?= $plan['key'] ?>">
                    <button type="submit" class="btn btn-primary w-100">Выбрать</button>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<script>
document.querySelectorAll('.pricing-card form').forEach(form => {
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        try {
            const response = await axios.post(this.action, formData);
            if (response.data.redirect) {
                window.location.href = response.data.redirect;
            }
        } catch (error) {
            const data = error.response?.data;
            alert(data?.error || 'Ошибка при смене тарифа');
        }
    });
});
</script>
