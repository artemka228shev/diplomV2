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
<div class="row mb-4">
    <div class="col-md-4 mb-3">
        <div class="stat-card">
            <div class="stat-number">0</div>
            <p>Активных привычек</p>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="stat-card">
            <div class="stat-number">—</div>
            <p>Текущая серия</p>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="stat-card">
            <div class="stat-number">—</div>
            <p>Выполнено сегодня</p>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-body text-center py-5">
                <div class="mb-3" style="font-size: 3rem;">🎯</div>
                <h4>Начните с одной привычки</h4>
                <p class="text-muted mb-3" style="max-width: 480px; margin: 0 auto 16px;">
                    Исследования показывают: чтобы сформировать привычку, нужно в среднем 66 дней. 
                    Начните с малого — добавьте одну привычку и отмечайте выполнение каждый день.
                </p>
                <button class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#createHabitModal">
                    <i class="bi bi-plus-lg me-2"></i>Создать первую привычку
                </button>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-body">
                <h5><i class="bi bi-lightbulb text-primary me-2"></i>Совет</h5>
                <p class="text-muted mb-0">Начинайте с привычек, которые занимают не больше 5 минут. 
                Например: выпить стакан воды, сделать 10 приседаний или прочитать одну страницу книги.</p>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-body">
                <h5><i class="bi bi-graph-up-arrow text-primary me-2"></i>Статистика</h5>
                <p class="text-muted mb-0">После добавления привычек вы сможете отслеживать прогресс, 
                смотреть графики выполнения и не прерывать серии на странице статистики.</p>
            </div>
        </div>
    </div>
</div>
<?php else: ?>
<div class="row" id="habitsList">
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
            location.reload();
        }
    } catch (error) {
        const data = error.response?.data;
        if (data?.error) {
            alert(data.error);
            if (data.upgrade_url) window.location.href = data.upgrade_url;
        }
    }
});

document.querySelectorAll('.btn-complete-habit').forEach(button => {
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

document.querySelectorAll('.btn-undo-habit').forEach(button => {
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

document.querySelectorAll('.btn-edit-habit').forEach(button => {
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

document.querySelectorAll('.btn-delete-habit').forEach(button => {
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
</script>
