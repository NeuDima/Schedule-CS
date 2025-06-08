function updateGroups() {
  const course = document.getElementById('course').value;
  const groupSelect = document.getElementById('group');
  const subgroupSelect = document.getElementById('subgroup');

  groupSelect.innerHTML = '<option value="">&mdash; выберите группу &mdash;</option>';
  subgroupSelect.innerHTML = '<option value="">&mdash; выберите подгруппу &mdash;</option>';

  if (course && data[course]) {
    const groups = Object.keys(data[course]);

    groups.sort((a, b) => {
      const numA = parseInt(a);
      const numB = parseInt(b);
      const isNumA = !isNaN(numA);
      const isNumB = !isNaN(numB);

      if (isNumA && isNumB) return numA - numB;
      if (isNumA) return -1;
      if (isNumB) return 1;
      return a.localeCompare(b, 'ru');
    });

    groups.forEach((group) => {
      groupSelect.innerHTML += `<option value="${group}">${group}</option>`;
    });
  }
}

function updateSubgroups() {
  const course = document.getElementById('course').value;
  const group = document.getElementById('group').value;
  const subgroupSelect = document.getElementById('subgroup');

  subgroupSelect.innerHTML = '<option value="">&mdash; выберите подгруппу &mdash;</option>';

  if (course && group && data[course][group]) {
    Object.keys(data[course][group]).forEach((subgroup) => {
      subgroupSelect.innerHTML += `<option value="${subgroup}">${subgroup}</option>`;
    });
  }
}

function goToSchedulePage() {
  const course = document.getElementById('course').value;
  const group = document.getElementById('group').value;
  const subgroup = document.getElementById('subgroup').value;

  if (course && group && subgroup) {
    const baseUrl = window.location.origin + '/schedule/';
    // const baseUrl = window.location.origin + '/schedule/wordpress/расписание/';
    const url =
      baseUrl +
      '?course=' +
      encodeURIComponent(course) +
      '&group=' +
      encodeURIComponent(group) +
      '&subgroup=' +
      encodeURIComponent(subgroup);
    window.location.href = url;
  } else {
    alert('Пожалуйста, выберите курс, группу и подгруппу.');
  }
}

document.addEventListener('DOMContentLoaded', function () {});
