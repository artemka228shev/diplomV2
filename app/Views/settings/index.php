<?php
use App\Core\View;
$user = $user ?? null;
?>

<h1 class="mb-4">Настройки</h1>

<div class="row">
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header"><h5 class="mb-0">Профиль</h5></div>
            <div class="card-body">
                <form id="profileForm">
                    <?= View::csrfField() ?>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" value="<?= View::escape($user['email'] ?? '') ?>" disabled>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Текущий тариф</label>
                        <input type="text" class="form-control" value="<?= View::escape(ucfirst($user['subscription_type'] ?? 'free')) ?>" disabled>
                        <a href="/pricing" class="btn btn-outline-primary btn-sm mt-2">Изменить тариф</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header"><h5 class="mb-0">Безопасность</h5></div>
            <div class="card-body">
                <form id="passwordForm">
                    <?= View::csrfField() ?>
                    <div class="mb-3">
                        <label class="form-label">Текущий пароль</label>
                        <input type="password" class="form-control" name="current_password">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Новый пароль</label>
                        <input type="password" class="form-control" name="new_password" minlength="6">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Подтвердите новый пароль</label>
                        <input type="password" class="form-control" name="new_password_confirm">
                    </div>
                    <button type="submit" class="btn btn-warning">Сменить пароль</button>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h5 class="mb-0 text-danger">Опасная зона</h5></div>
            <div class="card-body">
                <p>Удаление аккаунта необратимо. Все ваши привычки и данные будут удалены.</p>
                <button class="btn btn-danger" id="deleteAccountBtn">Удалить аккаунт</button>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('passwordForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    formData.append('action', 'change_password');
    try {
        await axios.post('/api/settings', formData);
        alert('Пароль успешно изменён');
        this.reset();
    } catch (e) {
        alert(e.response?.data?.error || 'Ошибка');
    }
});

document.getElementById('deleteAccountBtn').addEventListener('click', async function() {
    if (!confirm('Удалить аккаунт безвозвратно?')) return;
    const formData = new FormData();
    formData.append('action', 'delete_account');
    try {
        await axios.post('/api/settings', formData);
        window.location.href = '/logout';
    } catch (e) {
        alert(e.response?.data?.error || 'Ошибка');
    }
});
</script>
