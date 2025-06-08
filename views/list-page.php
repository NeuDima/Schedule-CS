<?php
$path = plugin_dir_path(dirname(__FILE__)) . 'parser/schedule.json';
echo "<!-- Путь до JSON: $path -->";

if (!file_exists($path)) {
    echo "<strong style='color:red;'>Файл не найден по пути: $path</strong>";
} else {
    $data = json_decode(file_get_contents(__DIR__ . '/../parser/schedule.json'), true);
    if ($data === null) {
        echo "<strong style='color:red;'>Ошибка парсинга JSON</strong>";
    }
}
?>

<div class="schedule-wrapper">
    <h1>Расписание</h1>
    <h2>Выберите курс, группу и подгруппу</h2>

    <form method="get" action="" class="schedule-form">
        <label for="course">Курс:</label>
        <select name="course" id="course" required onchange="updateGroups()">
            <option value="">&mdash; выберите курс &mdash;</option>
            <?php foreach ($data as $course => $groups): ?>
                <option value="<?= htmlspecialchars($course) ?>"><?= $course ?></option>
            <?php endforeach; ?>
        </select>

        <label for="group">Группа:</label>
        <select name="group" id="group" required onchange="updateSubgroups()">
            <option value="">&mdash; выберите группу &mdash;</option>
        </select>

        <label for="subgroup">Подгруппа:</label>
        <select name="subgroup" id="subgroup" required>
            <option value="">&mdash; выберите подгруппу &mdash;</option>
        </select>

        <button onclick="goToSchedulePage(); return false;">Показать расписание</button>
    </form>
</div>

<script>
    const data = <?= json_encode($data) ?>;
</script>