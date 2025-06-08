function showSchedule(weekType) {
    document.getElementById('btn-chisl').classList.remove('active');
    document.getElementById('btn-znam').classList.remove('active');
    if (weekType === 'Числитель') {
        document.getElementById('btn-chisl').classList.add('active');
    } else {
        document.getElementById('btn-znam').classList.add('active');
    }

    const container = document.getElementById('schedule');
    const lessons = scheduleData[weekType] || [];

    if (lessons.length === 0) {
        container.innerHTML = `<p>Нет занятий для "${weekType}".</p>`;
        return;
    }

    const days = {};
    lessons.forEach(lesson => {
        const day = lesson['день_недели'];
        if (!days[day]) days[day] = [];
        days[day].push(lesson);
    });

    const columns = [
        ["Понедельник", "Вторник", "Среда"],
        ["Четверг", "Пятница", "Суббота"]
    ];

    let html = '<div class="schedule-columns">';

    columns.forEach(dayGroup => {
        html += '<div class="schedule-column">';
        dayGroup.forEach(day => {
            html += `<h3>${day}</h3>`;
            if (days[day]) {
                html += `<table class="widefat striped">
                    <tbody>`;

                days[day].forEach(lesson => {
                    html += `<tr>
                        <td>${lesson['время']}</td>
                        <td>${lesson['дисциплина']}</td>
                        <td>${lesson['преподаватель']}</td>
                        <td>${lesson['аудитория']}</td>
                    </tr>`;
                });

                html += '</tbody></table><br>';
            } else {
                html += `<p>Нет занятий</p>`;
            }
        });
        html += '</div>';
    });

    html += '</div>';
    container.innerHTML = html;
}

document.addEventListener('DOMContentLoaded', function() {
    showSchedule('Числитель');
});