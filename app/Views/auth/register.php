<div class="row justify-content-center">
    <div class="col-md-6 col-lg-4">
        <div class="card shadow">
            <div class="card-body p-4">
                <h2 class="card-title text-center mb-4">Регистрация</h2>

                <div id="alertPlaceholder"></div>

                <form id="registerForm" method="post">
                    <?= \App\Core\View::csrfField() ?>

                    <div class="mb-3">
                        <label for="username" class="form-label">Логин</label>
                        <input type="text" class="form-control" id="username" name="username" required minlength="3" value="<?= \App\Core\View::escape(\App\Core\View::old('username')) ?>">
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" required value="<?= \App\Core\View::escape(\App\Core\View::old('email')) ?>">
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Пароль</label>
                        <input type="password" class="form-control" id="password" name="password" required minlength="6">
                    </div>

                    <div class="mb-3">
                        <label for="password_confirm" class="form-label">Подтвердите пароль</label>
                        <input type="password" class="form-control" id="password_confirm" name="password_confirm" required>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Зарегистрироваться</button>
                </form>

                <p class="text-center mt-3">
                    Уже есть аккаунт? <a href="/login">Войти</a>
                </p>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('registerForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const alertPlaceholder = document.getElementById('alertPlaceholder');
    alertPlaceholder.innerHTML = '';

    try {
        const response = await axios.post('/register', formData);
        if (response.data.redirect) {
            window.location.href = response.data.redirect;
        }
    } catch (error) {
        const data = error.response?.data;
        if (data?.errors) {
            let message = '';
            for (const field in data.errors) {
                message += data.errors[field].join('. ') + '. ';
            }
            alertPlaceholder.innerHTML = `
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>`;
        } else {
            alertPlaceholder.innerHTML = `
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    ${data?.error || 'Ошибка регистрации'}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>`;
        }
    }
});
</script>
