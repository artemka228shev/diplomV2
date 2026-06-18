<?php
use App\Core\View;
$habit = $habit ?? [];
$logs = $logs ?? [];
$streak = $streak ?? 0;
?>

<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <a href="/habits" class="text-muted-custom text-decoration-none small">
            <i class="bi bi-arrow-left me-1"></i>Назад к списку
        </a>
        <h1 class="mt-2 mb-0"><?= View::escape($habit['title']) ?></h1>
    </div>
    <a href="/habits" class="btn btn-outline-secondary">
        <i class="bi bi-grid me-1"></i>Все привычки
    </a>
</div>

<div class="row mt-4">
    <div class="col-lg-4 mb-4">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title"><i class="bi bi-info-circle me-2"></i>Описание</h5>
                <?php if (!empty($habit['description'])): ?>
                    <p class="text-muted-custom"><?= nl2br(View::escape($habit['description'])) ?></p>
                <?php else: ?>
                    <p class="text-muted-custom fst-italic">Описание не указано</p>
                <?php endif; ?>

                <hr>
                <ul class="list-unstyled">
                    <li class="mb-2"><strong>Тип:</strong> <?= $habit['type'] === 'quantitative' ? 'Количественный' : 'Факт выполнения' ?></li>
                    <li class="mb-2">
                        <strong>Частота:</strong>
                        <?php
                        $freqLabels = ['daily' => 'Ежедневно', 'weekly' => 'Еженедельно', 'custom' => 'По расписанию'];
                        echo $freqLabels[$habit['frequency']] ?? View::escape($habit['frequency']);
                        ?>
                    </li>
                    <?php if ($habit['type'] === 'quantitative'): ?>
                    <li class="mb-2"><strong>Цель:</strong> <?= (int)$habit['target_value'] ?> <?= View::escape($habit['unit'] ?? '') ?></li>
                    <?php endif; ?>
                    <li class="mb-2">
                        <strong>Серия:</strong>
                        <span class="badge bg-warning text-dark"><?= (int)$streak ?> дн.</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <div class="col-lg-8 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i>История выполнения</h5>
                <span class="badge bg-secondary"><?= count($logs) ?> записей</span>
            </div>
            <div class="card-body">
                <?php if (empty($logs)): ?>
                    <p class="text-muted-custom mb-0 text-center py-4">История пока пуста</p>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Дата</th>
                                <th>Значение</th>
                                <th>Статус</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($logs as $log): ?>
                            <tr>
                                <td><?= date('d.m.Y', strtotime($log['date'])) ?></td>
                                <td>
                                    <?php if ($habit['type'] === 'quantitative' && !empty($log['value'])): ?>
                                        <strong><?= (int)$log['value'] ?></strong> <?= View::escape($habit['unit'] ?? '') ?>
                                    <?php elseif (!empty($log['value'])): ?>
                                        <span class="text-success"><i class="bi bi-check-lg"></i> Выполнено</span>
                                    <?php else: ?>
                                        <span class="text-muted-custom">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($log['value'])): ?>
                                        <span class="badge bg-success">Выполнено</span>
                                    <?php else: ?>
                                        <span class="badge bg-light text-dark">Пропущено</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
