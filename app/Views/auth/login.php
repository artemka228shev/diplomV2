<div class="row justify-content-center">
    <div class="col-md-6 col-lg-4">
        <div class="card shadow">
            <div class="card-body p-4">
                <h2 class="card-title text-center mb-4">Вход</h2>

                <div id="alertPlaceholder"></div>

                <form id="loginForm" method="post">
                    <?= \App\Core\View::csrfField() ?>

                    <div class="mb-3">
                        <label for="login" class="form-label">Логин или Email</label>
                        <input type="text" class="form-control" id="login" name="login" required>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Пароль</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Войти</button>
                </form>

                <p class="text-center mt-3">
                    Нет аккаунта? <a href="/register">Зарегистрироваться</a>
                </p>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('loginForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const alertPlaceholder = document.getElementById('alertPlaceholder');
    alertPlaceholder.innerHTML = '';

    try {
        const response = await axios.post('/login', formData);
        if (response.data.redirect) {
            window.location.href = response.data.redirect;
        }
    } catch (error) {
        const message = error.response?.data?.error || 'Ошибка входа';
        alertPlaceholder.innerHTML = `
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>`;
    }
});
</script>
