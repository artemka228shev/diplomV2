<?php
use App\Core\View;
$user = $user ?? null;
$habits = $habits ?? [];
?>

<div class="page-header">
    <h1>Мои привычки</h1>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createHabitModal">
        <i class="bi bi-plus-lg"></i> Добавить привычку
    </button>
</div>

<?php if (empty($habits)): ?>
<div class="col-12">
    <div class="empty-state" id="emptyState">
        <svg width="120" height="120" viewBox="0 0 120 120" fill="none" xmlns="http://www.w3.org/2000/svg" style="margin-bottom: 20px;">
            <rect width="120" height="120" rx="20" fill="#1f2327"/>
            <circle cx="60" cy="48" r="22" stroke="#8bb590" stroke-width="2.5" fill="none"/>
            <path d="M52 48l6 6 10-10" stroke="#8bb590" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
            <rect x="30" y="76" width="60" height="4" rx="2" fill="#272c31"/>
            <rect x="30" y="86" width="45" height="4" rx="2" fill="#272c31"/>
            <rect x="30" y="96" width="52" height="4" rx="2" fill="#272c31"/>
        </svg>
        <h3>Привычки пока отсутствуют</h3>
        <p>Добавьте первую привычку, чтобы начать отслеживание своих целей</p>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createHabitModal">
            <i class="bi bi-plus-lg me-2"></i>Создать привычку
        </button>
    </div>
</div>
<?php endif; ?>

<div class="row" id="habitsList" style="<?= empty($habits) ? 'display: none;' : '' ?>">
    <?php if (!empty($habits)): ?>
        <?php foreach ($habits as $habit): ?>
        <div class="col-md-6 col-lg-4 mb-4" data-habit-id="<?= View::escape($habit['id']) ?>">
            <div class="card habit-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <h5 class="card-title mb-0"><?= View::escape($habit['title']) ?></h5>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-link text-muted-custom" data-bs-toggle="dropdown">
                                <i class="bi bi-three-dots-vertical"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <button class="dropdown-item btn-edit-habit" data-id="<?= View::escape($habit['id']) ?>" data-title="<?= View::escape($habit['title']) ?>" data-description="<?= View::escape($habit['description'] ?? '') ?>" data-type="<?= View::escape($habit['type']) ?>" data-target-value="<?= (int)$habit['target_value'] ?>" data-unit="<?= View::escape($habit['unit'] ?? '') ?>" data-frequency="<?= View::escape($habit['frequency']) ?>">
                                        <i class="bi bi-pencil me-2"></i> Редактировать
                                    </button>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <button class="dropdown-item text-danger btn-delete-habit" data-id="<?= View::escape($habit['id']) ?>">
                                        <i class="bi bi-trash me-2"></i> Удалить
                                    </button>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <p class="text-muted-custom mb-3">
                        <?php
                        $freqLabels = ['daily' => 'Ежедневно', 'weekly' => 'Еженедельно', 'custom' => 'По расписанию'];
                        echo $freqLabels[$habit['frequency']] ?? View::escape($habit['frequency']);
                        ?>
                    </p>

                    <?php if (!empty($habit['description'])): ?>
                    <p class="text-muted-custom mb-3 small">
                        <?php
                        $desc = $habit['description'];
                        if (mb_strlen($desc) > 80) {
                            echo View::escape(mb_substr($desc, 0, 80)) . '...';
                        } else {
                            echo View::escape($desc);
                        }
                        ?>
                    </p>
                    <?php endif; ?>

                    <?php if ($habit['type'] === 'quantitative'): ?>
                    <p class="mb-3">
                        Цель: <strong class="text-primary"><?= (int)$habit['target_value'] ?> <?= View::escape($habit['unit'] ?? '') ?></strong>
                    </p>
                    <?php endif; ?>

                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <?php
                        $todayLog = $habit['today_log'] ?? null;
                        $isCompleted = $todayLog && !empty($todayLog['value']);
                        ?>

                        <div class="d-flex gap-2 flex-wrap">
                            <?php if ($isCompleted): ?>
                            <button class="btn btn-outline-danger btn-sm btn-undo-habit" data-id="<?= View::escape($habit['id']) ?>">
                                <i class="bi bi-x-lg me-1"></i> Отменить
                            </button>
                            <?php else: ?>
                            <button class="btn btn-complete-habit btn-sm" data-id="<?= View::escape($habit['id']) ?>">
                                <i class="bi bi-check-lg me-1"></i> Выполнить
                            </button>
                            <?php endif; ?>

                            <a href="/habits/<?= View::escape($habit['id']) ?>" class="btn btn-outline-secondary btn-sm">
                                <i class="bi bi-eye me-1"></i> Подробнее
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php if (($user['subscription_type'] ?? 'free') === 'free'): ?>
<div class="row mt-4">
    <div class="col-12">
        <div class="alert-warning-custom d-flex align-items-center p-3">
            <i class="bi bi-info-circle me-2 fs-5"></i>
            <div>У вас бесплатный тариф. Максимум 5 привычек. <a href="/pricing">Улучшить подписку</a></div>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="modal fade" id="createHabitModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Добавить привычку</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="createHabitForm" method="post">
                    <?= View::csrfField() ?>
                    <div class="mb-3">
                        <label class="form-label">Название</label>
                        <input type="text" class="form-control" name="title" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Описание</label>
                        <textarea class="form-control" name="description" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Тип</label>
                        <select class="form-select" name="type" id="habitType" required>
                            <option value="boolean">Факт выполнения (да/нет)</option>
                            <option value="quantitative">Количественный показатель</option>
                        </select>
                    </div>
                    <div class="mb-3" id="targetValueContainer" style="display: none;">
                        <label class="form-label">Целевое значение</label>
                        <div class="input-group">
                            <input type="number" class="form-control" name="target_value" min="1" step="1">
                            <input type="text" class="form-control" name="unit" placeholder="Единица (мин, шт)">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Частота</label>
                        <select class="form-select" name="frequency" required>
                            <option value="daily">Ежедневно</option>
                            <option value="weekly">Еженедельно</option>
                            <option value="custom">По дням недели</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Создать</button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="editHabitModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Редактировать привычку</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editHabitForm">
                    <?= View::csrfField() ?>
                    <input type="hidden" name="id" id="editHabitId">
                    <div class="mb-3">
                        <label class="form-label">Название</label>
                        <input type="text" class="form-control" id="editHabitTitle" name="title" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Описание</label>
                        <textarea class="form-control" id="editHabitDescription" name="description" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Тип</label>
                        <select class="form-select" id="editHabitType" name="type" required>
                            <option value="boolean">Факт выполнения</option>
                            <option value="quantitative">Количественный</option>
                        </select>
                    </div>
                    <div class="mb-3" id="editTargetValueContainer" style="display: none;">
                        <label class="form-label">Целевое значение</label>
                        <div class="input-group">
                            <input type="number" class="form-control" id="editHabitTarget" name="target_value" min="1" step="1">
                            <input type="text" class="form-control" id="editHabitUnit" name="unit">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Частота</label>
                        <select class="form-select" id="editHabitFrequency" name="frequency" required>
                            <option value="daily">Ежедневно</option>
                            <option value="weekly">Еженедельно</option>
                            <option value="custom">По дням недели</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Сохранить</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('habitType').addEventListener('change', function() {
    document.getElementById('targetValueContainer').style.display = this.value === 'quantitative' ? 'block' : 'none';
});
document.getElementById('editHabitType').addEventListener('change', function() {
    document.getElementById('editTargetValueContainer').style.display = this.value === 'quantitative' ? 'block' : 'none';
});

document.getElementById('createHabitForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    try {
        const response = await axios.post('/habits', formData);
        if (response.data.success) {
            bootstrap.Modal.getInstance(document.getElementById('createHabitModal')).hide();
            this.reset();
            // Добавляем новую привычку динамически
            const habitId = response.data.habit_id;
            const title = formData.get('title');
            const description = formData.get('description') || '';
            const type = formData.get('type');
            const frequency = formData.get('frequency');
            const targetValue = formData.get('target_value') || 0;
            const unit = formData.get('unit') || '';

            // Убираем пустое состояние, если оно есть
            const emptyState = document.getElementById('emptyState');
            if (emptyState) {
                const emptyCol = emptyState.closest('.col-12');
                if (emptyCol) emptyCol.remove();
                // Показываем контейнер для привычек
                const habitsList = document.getElementById('habitsList');
                if (habitsList) habitsList.style.display = 'flex';
            }

            const freqLabels = {'daily': 'Ежедневно', 'weekly': 'Еженедельно', 'custom': 'По расписанию'};
            const freqLabel = freqLabels[frequency] || frequency;

            let extraHtml = '';
            if (type === 'quantitative') {
                extraHtml = `<p class="mb-3">Цель: <strong class="text-primary">${targetValue} ${unit}</strong></p>`;
            }

            const card = document.createElement('div');
            card.className = 'col-md-6 col-lg-4 mb-4';
            card.setAttribute('data-habit-id', habitId);
            card.innerHTML = `
                <div class="card habit-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <h5 class="card-title mb-0">${title}</h5>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-link text-muted-custom" data-bs-toggle="dropdown">
                                    <i class="bi bi-three-dots-vertical"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <button class="dropdown-item btn-edit-habit" data-id="${habitId}" data-title="${title}" data-description="${description}" data-type="${type}" data-target-value="${targetValue}" data-unit="${unit}" data-frequency="${frequency}">
                                            <i class="bi bi-pencil me-2"></i> Редактировать
                                        </button>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <button class="dropdown-item text-danger btn-delete-habit" data-id="${habitId}">
                                            <i class="bi bi-trash me-2"></i> Удалить
                                        </button>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <p class="text-muted-custom mb-3">${freqLabel}</p>
                        ${description ? `<p class="text-muted-custom mb-3 small">${description.length > 80 ? description.substring(0, 80) + '...' : description}</p>` : ''}
                        ${extraHtml}
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <div class="d-flex gap-2 flex-wrap">
                                <button class="btn btn-complete-habit btn-sm" data-id="${habitId}">
                                    <i class="bi bi-check-lg me-1"></i> Выполнить
                                </button>
                                <a href="/habits/${habitId}" class="btn btn-outline-secondary btn-sm">
                                    <i class="bi bi-eye me-1"></i> Подробнее
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            const habitsList = document.getElementById('habitsList');
            if (habitsList) {
                habitsList.appendChild(card);
                // Навешиваем обработчики на новую карточку
                attachHabitHandlers(card);
            }
        }
    } catch (error) {
        const data = error.response?.data;
        if (data?.error) {
            alert(data.error);
            if (data.upgrade_url) window.location.href = data.upgrade_url;
        }
    }
});

function attachHabitHandlers(container) {
    container.querySelectorAll('.btn-complete-habit').forEach(button => {
        button.addEventListener('click', async function() {
            const habitId = this.dataset.id;
            const formData = new FormData();
            formData.append('completed', 'true');
            try {
                await axios.post(`/habits/${habitId}/log`, formData);
                location.reload();
            } catch (error) {
                alert('Ошибка: ' + (error.response?.data?.error || error.message));
            }
        });
    });

    container.querySelectorAll('.btn-undo-habit').forEach(button => {
        button.addEventListener('click', async function() {
            const habitId = this.dataset.id;
            if (!confirm('Отменить выполнение?')) return;
            const formData = new FormData();
            formData.append('_method', 'DELETE');
            try {
                await axios.post(`/habits/${habitId}/log`, formData);
                location.reload();
            } catch (error) {
                alert('Ошибка: ' + (error.response?.data?.error || error.message));
            }
        });
    });

    container.querySelectorAll('.btn-edit-habit').forEach(button => {
        button.addEventListener('click', function() {
            document.getElementById('editHabitId').value = this.dataset.id;
            document.getElementById('editHabitTitle').value = this.dataset.title;
            document.getElementById('editHabitDescription').value = this.dataset.description;
            document.getElementById('editHabitType').value = this.dataset.type;
            document.getElementById('editHabitTarget').value = this.dataset.targetValue;
            document.getElementById('editHabitUnit').value = this.dataset.unit;
            document.getElementById('editHabitFrequency').value = this.dataset.frequency;
            document.getElementById('editTargetValueContainer').style.display =
                this.dataset.type === 'quantitative' ? 'block' : 'none';
            new bootstrap.Modal(document.getElementById('editHabitModal')).show();
        });
    });

    container.querySelectorAll('.btn-delete-habit').forEach(button => {
        button.addEventListener('click', async function() {
            if (!confirm('Удалить эту привычку?')) return;
            const habitId = this.dataset.id;
            try {
                await axios.delete(`/habits/${habitId}`);
                document.querySelector(`[data-habit-id="${habitId}"]`).remove();
            } catch (error) {
                alert('Ошибка при удалении');
            }
        });
    });
}

// Навешиваем обработчики на существующие карточки
document.querySelectorAll('#habitsList > div').forEach(attachHabitHandlers);

document.getElementById('editHabitForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const habitId = document.getElementById('editHabitId').value;
    try {
        const response = await axios.put(`/habits/${habitId}`, formData);
        if (response.data.success) {
            bootstrap.Modal.getInstance(document.getElementById('editHabitModal')).hide();
            location.reload();
        }
    } catch (error) {
        alert('Ошибка: ' + (error.response?.data?.error || error.message));
    }
});
</script>
