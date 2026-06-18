// Общий JS приложения Habitify

document.addEventListener('DOMContentLoaded', function() {
    // Автоматическая подстановка CSRF-токена в axios-запросы
    const csrfMeta = document.querySelector('meta[name="csrf-token"]');
    if (csrfMeta && typeof axios !== 'undefined') {
        const token = csrfMeta.getAttribute('content');
        axios.defaults.headers.common['X-CSRF-TOKEN'] = token;

        // Также добавляем токен в FormData при каждом запросе
        axios.interceptors.request.use(function(config) {
            if (config.data instanceof FormData && token) {
                // Не перезаписываем, если уже задан
                if (!config.data.has('_csrf')) {
                    config.data.append('_csrf', token);
                }
            }
            return config;
        }, function(error) {
            return Promise.reject(error);
        });
    }
});

function formatNumber(num) {
    return Number(num).toLocaleString('ru-RU');
}

function formatDate(date) {
    return new Date(date).toLocaleDateString('ru-RU');
}

function confirmAction(message) {
    return confirm(message || 'Вы уверены?');
}
