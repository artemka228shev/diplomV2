<?php
use App\Core\View;
$stats = $stats ?? [];
$weeklyData = $weeklyData ?? [];
$monthlyData = $monthlyData ?? [];
$topHabits = $topHabits ?? [];
$habits = $habits ?? [];
$user = $user ?? null;
?>

<h1 class="mb-4">Статистика</h1>

<div class="row mb-4">
    <div class="col-md-3">
        <div class="card stat-card text-center">
            <div class="card-body">
                <h3 class="mb-0"><?= $stats['total_habits'] ?? 0 ?></h3>
                <small class="text-muted">Активных</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card text-center">
            <div class="card-body">
                <h3 class="mb-0"><?= $stats['completion_rate'] ?? 0 ?>%</h3>
                <small class="text-muted">Выполнение</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card text-center">
            <div class="card-body">
                <h3 class="mb-0"><?= $stats['average_streak'] ?? 0 ?></h3>
                <small class="text-muted">Средняя серия</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card text-center">
            <div class="card-body">
                <h3 class="mb-0"><?= $stats['completed_logs'] ?? 0 ?></h3>
                <small class="text-muted">Всего выполнений</small>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header"><h5 class="mb-0">Прогресс за неделю</h5></div>
            <div class="card-body">
                <canvas id="weeklyChart" height="100"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-header"><h5 class="mb-0">Топ привычек</h5></div>
            <div class="card-body">
                <canvas id="habitsChart" height="200"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header"><h5 class="mb-0">Тепловая карта активности (30 дней)</h5></div>
    <div class="card-body">
        <div class="heatmap">
            <?php foreach ($monthlyData as $day): ?>
            <div class="heatmap-cell level-<?= (int)($day['percentage'] / 25) ?>"
                 title="<?= View::escape($day['date']) ?>: <?= $day['percentage'] ?>%"></div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
const weeklyCtx = document.getElementById('weeklyChart').getContext('2d');
new Chart(weeklyCtx, {
    type: 'bar',
    data: {
        labels: <?= json_encode(array_column($weeklyData, 'day')) ?>,
        datasets: [{
            label: 'Выполнено',
            data: <?= json_encode(array_column($weeklyData, 'completed')) ?>,
            backgroundColor: '#8bb590'
        }, {
            label: 'Всего',
            data: <?= json_encode(array_column($weeklyData, 'total')) ?>,
            backgroundColor: '#3a4045'
        }]
    },
    options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true } } }
});

const habitStats = <?= json_encode($topHabits) ?>;
if (habitStats.length > 0) {
    const habitsCtx = document.getElementById('habitsChart').getContext('2d');
    new Chart(habitsCtx, {
        type: 'doughnut',
        data: {
            labels: habitStats.map(h => h.name),
            datasets: [{
                data: habitStats.map(h => h.completed),
                backgroundColor: ['#8bb590', '#7aa9c7', '#c9a35f', '#a58dc8', '#d97878']
            }]
        },
        options: { responsive: true, maintainAspectRatio: false }
    });
}
</script>
