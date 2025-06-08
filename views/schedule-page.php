<?php
$course = $_GET['course'] ?? '';
$group = $_GET['group'] ?? '';
$subgroup = $_GET['subgroup'] ?? '';
$data = json_decode(file_get_contents(plugin_dir_path(__DIR__) . 'parser/schedule.json'), true);

$lessons = $data[$course][$group][$subgroup] ?? [];
$filtered = ['Числитель' => [], 'Знаменатель' => []];
foreach ($lessons as $lesson) {
    $key = mb_strtolower($lesson['неделя_занятия']) === 'числитель' ? 'Числитель' : 'Знаменатель';
    $filtered[$key][] = $lesson;
}
?>

<div class="schedule-wrapper">
    <h1>Расписание</h1>
    <h2><?= htmlspecialchars("$course $group $subgroup") ?></h2>

    <div class="schedule-buttons">
        <button onclick="showSchedule('Числитель')" id="btn-chisl" class="active">Числитель</button>
        <button onclick="showSchedule('Знаменатель')" id="btn-znam">Знаменатель</button>
    </div>

    <div id="schedule"></div>
</div>

<link rel="stylesheet" href="schedule-page.css">
<script src="schedule-page.js"></script>
<script>
    const scheduleData = <?= json_encode($filtered) ?>;
</script>