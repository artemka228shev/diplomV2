<?php
use App\Core\View;
$stats = $stats ?? [];
$recentActions = $recentActions ?? [];
?>

<div class="page-header">
    <h1>Дашборд администратора</h1>
</div>

<div class="row mb-4">
    <div class="col-md-3">
        <div class="card stat-card text-center">
            <div class="card-body">
                <h3 class="mb-0"><?= $stats['total_users'] ?? 0 ?></h3>
                <small class="text-muted">Всего</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card text-center">
            <div class="card-body">
                <h3 class="mb-0 text-tier-free"><?= $stats['free_users'] ?? 0 ?></h3>
                <small class="text-muted">Free</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card text-center">
            <div class="card-body">
                <h3 class="mb-0 text-tier-basic"><?= $stats['basic_users'] ?? 0 ?></h3>
                <small class="text-muted">Basic</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card text-center">
            <div class="card-body">
                <h3 class="mb-0 text-tier-premium"><?= $stats['premium_users'] ?? 0 ?></h3>
                <small class="text-muted">Premium</small>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-4">
        <div class="card">
            <div class="card-body text-center">
                <div class="text-muted small">Регистрации за неделю</div>
                <h2 class="text-tier-free"><?= $stats['registrations_week'] ?? 0 ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body text-center">
                <div class="text-muted small">За месяц</div>
                <h2 class="text-tier-basic"><?= $stats['registrations_month'] ?? 0 ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body text-center">
                <div class="text-muted small">За год</div>
                <h2 class="text-tier-premium"><?= $stats['registrations_year'] ?? 0 ?></h2>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Последние действия администраторов</h5>
        <a href="/admin/audit" class="btn btn-sm btn-outline-secondary">Все действия</a>
    </div>
    <div class="card-body">
        <?php if (empty($recentActions)): ?>
            <p class="text-muted mb-0">Действий нет</p>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>Время</th>
                        <th>Админ</th>
                        <th>Действие</th>
                        <th>Цель</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($recentActions as $action): ?>
                    <tr>
                        <td><?= date('d.m.Y H:i', strtotime($action['created_at'])) ?></td>
                        <td><?= View::escape($action['admin_email'] ?? 'Unknown') ?></td>
                        <td><?= View::escape($action['action']) ?></td>
                        <td><?= View::escape($action['target_email'] ?? '-') ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>
