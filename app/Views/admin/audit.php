<?php
use App\Core\View;
$logs = $logs ?? [];
$actions = $actions ?? [];
$admins = $admins ?? [];
$selectedAction = $selectedAction ?? '';
$selectedAdminId = $selectedAdminId ?? '';
$startDate = $startDate ?? date('Y-m-d', strtotime('-30 days'));
$endDate = $endDate ?? date('Y-m-d');
$currentPage = $currentPage ?? 1;
$totalPages = $totalPages ?? 1;
?>

<div class="page-header d-flex justify-content-between align-items-center">
    <h1>Журнал аудита</h1>
    <a href="/admin" class="btn btn-outline-secondary">Назад</a>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Действие</label>
                <select name="action" class="form-select">
                    <option value="">Все</option>
                    <?php foreach ($actions as $act): ?>
                    <option value="<?= View::escape($act) ?>" <?= $selectedAction === $act ? 'selected' : '' ?>><?= View::escape($act) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Админ</label>
                <select name="admin_id" class="form-select">
                    <option value="">Все</option>
                    <?php foreach ($admins as $admin): ?>
                    <option value="<?= (int)$admin['id'] ?>" <?= $selectedAdminId == $admin['id'] ? 'selected' : '' ?>><?= View::escape($admin['email']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">С</label>
                <input type="date" name="start_date" class="form-control" value="<?= View::escape($startDate) ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label">По</label>
                <input type="date" name="end_date" class="form-control" value="<?= View::escape($endDate) ?>">
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary">Фильтр</button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <?php if (empty($logs)): ?>
        <p class="text-muted text-center py-4 mb-0">За выбранный период действий нет</p>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-sm table-striped">
                <thead>
                    <tr>
                        <th>Время</th>
                        <th>Админ</th>
                        <th>Действие</th>
                        <th>Цель</th>
                        <th>IP</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($logs as $log): ?>
                    <tr>
                        <td><?= date('d.m.Y H:i:s', strtotime($log['created_at'])) ?></td>
                        <td><?= View::escape($log['admin_email'] ?? 'Unknown') ?></td>
                        <td><?= View::escape($log['action']) ?></td>
                        <td><?= View::escape($log['target_email'] ?? '-') ?></td>
                        <td><code><?= View::escape($log['ip_address'] ?? '-') ?></code></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php if ($totalPages > 1): ?>
<nav>
    <ul class="pagination justify-content-center">
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <?php if ($i == 1 || $i == $totalPages || abs($i - $currentPage) <= 2): ?>
            <li class="page-item <?= $i == $currentPage ? 'active' : '' ?>">
                <a class="page-link" href="?page=<?= $i ?>&action=<?= urlencode($selectedAction) ?>&admin_id=<?= urlencode($selectedAdminId) ?>&start_date=<?= urlencode($startDate) ?>&end_date=<?= urlencode($endDate) ?>"><?= $i ?></a>
            </li>
            <?php endif; ?>
        <?php endfor; ?>
    </ul>
</nav>
<?php endif; ?>
