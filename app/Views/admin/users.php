<?php
use App\Core\View;
$users = $users ?? [];
$currentPage = $currentPage ?? 1;
$totalPages = $totalPages ?? 1;
$search = $search ?? '';
?>

<div class="page-header">
    <h1>Управление пользователями</h1>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-8">
                <input type="text" name="search" class="form-control" placeholder="Поиск по email или логину" value="<?= View::escape($search) ?>">
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-primary me-2">Найти</button>
                <a href="/admin/users" class="btn btn-outline-secondary">Сбросить</a>
            </div>
        </form>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Email</th>
                <th>Роль</th>
                <th>Подписка</th>
                <th>Действия</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($users as $u): ?>
            <tr data-user-id="<?= (int)$u['id'] ?>">
                <td><?= (int)$u['id'] ?></td>
                <td><?= View::escape($u['email']) ?></td>
                <td>
                    <span class="badge <?= $u['role'] === 'admin' ? 'bg-danger' : 'bg-secondary' ?>">
                        <?= View::escape($u['role']) ?>
                    </span>
                </td>
                <td>
                    <select class="form-select form-select-sm subscription-select" data-user-id="<?= (int)$u['id'] ?>">
                        <option value="free" <?= $u['subscription_type'] === 'free' ? 'selected' : '' ?>>Free</option>
                        <option value="basic" <?= $u['subscription_type'] === 'basic' ? 'selected' : '' ?>>Basic</option>
                        <option value="premium" <?= $u['subscription_type'] === 'premium' ? 'selected' : '' ?>>Premium</option>
                    </select>
                </td>
                <td>
                    <?php if ($u['role'] !== 'admin'): ?>
                    <button class="btn btn-sm btn-outline-primary btn-make-admin" data-user-id="<?= (int)$u['id'] ?>">В админы</button>
                    <?php else: ?>
                    <button class="btn btn-sm btn-outline-secondary btn-remove-admin" data-user-id="<?= (int)$u['id'] ?>">Убрать</button>
                    <?php endif; ?>
                    <button class="btn btn-sm btn-outline-warning btn-toggle-ban" data-user-id="<?= (int)$u['id'] ?>">
                        <?= !empty($u['is_banned']) ? 'Разбанить' : 'Забанить' ?>
                    </button>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php if ($totalPages > 1): ?>
<nav>
    <ul class="pagination justify-content-center">
        <li class="page-item <?= $currentPage <= 1 ? 'disabled' : '' ?>">
            <a class="page-link" href="?page=<?= $currentPage - 1 ?>&search=<?= urlencode($search) ?>">Назад</a>
        </li>
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <?php if ($i == 1 || $i == $totalPages || abs($i - $currentPage) <= 2): ?>
            <li class="page-item <?= $i == $currentPage ? 'active' : '' ?>">
                <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>"><?= $i ?></a>
            </li>
            <?php endif; ?>
        <?php endfor; ?>
        <li class="page-item <?= $currentPage >= $totalPages ? 'disabled' : '' ?>">
            <a class="page-link" href="?page=<?= $currentPage + 1 ?>&search=<?= urlencode($search) ?>">Вперёд</a>
        </li>
    </ul>
</nav>
<?php endif; ?>

<script>
document.querySelectorAll('.subscription-select').forEach(sel => {
    sel.previousValue = sel.value;
    sel.addEventListener('change', async function() {
        const userId = this.dataset.userId;
        try {
            await axios.put(`/admin/users/${userId}/subscription`, { subscription_type: this.value });
            this.previousValue = this.value;
        } catch (e) {
            this.value = this.previousValue;
            alert('Ошибка при смене подписки');
        }
    });
});

document.querySelectorAll('.btn-make-admin').forEach(b => {
    b.addEventListener('click', async function() {
        if (!confirm('Выдать права администратора?')) return;
        try { await axios.post(`/admin/users/${this.dataset.userId}/make-admin`); location.reload(); }
        catch (e) { alert('Ошибка'); }
    });
});

document.querySelectorAll('.btn-remove-admin').forEach(b => {
    b.addEventListener('click', async function() {
        if (!confirm('Убрать права администратора?')) return;
        try { await axios.post(`/admin/users/${this.dataset.userId}/remove-admin`); location.reload(); }
        catch (e) { alert('Ошибка'); }
    });
});

document.querySelectorAll('.btn-toggle-ban').forEach(b => {
    b.addEventListener('click', async function() {
        if (!confirm('Изменить статус бана?')) return;
        try { await axios.post(`/admin/users/${this.dataset.userId}/toggle-ban`); location.reload(); }
        catch (e) { alert('Ошибка'); }
    });
});
</script>
