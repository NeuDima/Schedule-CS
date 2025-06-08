<?php
require __DIR__ . '/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

function loadPositions($filename) {
    return array_map('trim', file($filename, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES));
}

function loadSubjectMap($filename) {
    $lines = file($filename, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $map = [];

    foreach ($lines as $line) {
        [$key, $value] = array_map('trim', explode('=', $line, 2));
        $map[$key] = $value;
    }

    return $map;
}

function parseLessonInfo($lessonStr, $positions, $subjectMap = []) {
    $lessonStr = preg_replace('/\s*\(id=\d+\)/u', '', $lessonStr);

    $addressRegex = '/(ул\.|корпус|пр[-\.])[^,]*(,\s*[^,]*)*/ui';
    preg_match($addressRegex, $lessonStr, $addressMatches);
    $addressInfo = $addressMatches[0] ?? '';

    if ($addressInfo) {
        $lessonStr = str_replace($addressInfo, '', $lessonStr);
        $addressInfo = trim($addressInfo, " ,");
    }

    foreach ($positions as $position) {
        if (strpos($lessonStr, $position) !== false) {
            $parts = explode($position, $lessonStr, 2);
            $subject = trim($parts[0]);
            $rest = trim($parts[1]);

            if ($addressInfo !== '') {
                $subject .= ' (' . $addressInfo . ')';
            }

            if (isset($subjectMap[$subject])) {
                $subject = $subjectMap[$subject];
            }

            $tokens = preg_split('/\s+/', $rest);
            $last = end($tokens);

            if (preg_match('/^\(?[А-ЯA-Z0-9]{2,}\)?$/u', $last)) {
                array_pop($tokens);
                $auditorium = $last;
            } else {
                $auditorium = '';
            }

            $teacher = implode(' ', $tokens);

            return [
                'дисциплина' => $subject,
                'преподаватель' => trim($position . ' ' . $teacher),
                'аудитория' => $auditorium
            ];
        }
    }

    $lessonStr = trim($lessonStr);
    if (isset($subjectMap[$lessonStr])) {
        $lessonStr = $subjectMap[$lessonStr];
    }

    return [
        'дисциплина' => $lessonStr,
        'преподаватель' => '',
        'аудитория' => ''
    ];
}

function parseMultipleLessons($lessonStr, $positions, $subjectMap = []) {
    $lessonParts = preg_split('/\s*\/\s*/u', $lessonStr); // разбиваем по "/"
    $parsedLessons = [];

    foreach ($lessonParts as $part) {
        $parsedLessons[] = parseLessonInfo($part, $positions, $subjectMap);
    }

    return $parsedLessons;
}

function getMergedValue($sheet, $mergedCells, $address) {
    $cell = $sheet->getCell($address);
    $value = $cell->getValue();

    if ($value !== null && $value !== '') {
        return $value;
    }

    foreach ($mergedCells as $range) {
        [$start, $end] = explode(':', $range);
        [$startCol, $startRow] = Coordinate::coordinateFromString($start);
        [$endCol, $endRow] = Coordinate::coordinateFromString($end);

        $startColIndexRange = Coordinate::columnIndexFromString($startCol);
        $endColIndexRange = Coordinate::columnIndexFromString($endCol);

        [$colLetter, $rowNum] = Coordinate::coordinateFromString($address);
        $colIndex = Coordinate::columnIndexFromString($colLetter);

        if (
            $colIndex >= $startColIndexRange && $colIndex <= $endColIndexRange &&
            $rowNum >= $startRow && $rowNum <= $endRow
        ) {
            return $sheet->getCell($start)->getValue();
        }
    }

    return null;
}

function getGroupMergeBounds($mergedCells, $colLetter) {
    foreach ($mergedCells as $range) {
        [$start, $end] = explode(':', $range);
        [$startCol, $startRow] = Coordinate::coordinateFromString($start);
        [$endCol, $endRow] = Coordinate::coordinateFromString($end);

        $startColIndex = Coordinate::columnIndexFromString($startCol);
        $endColIndex = Coordinate::columnIndexFromString($endCol);
        $colIndex = Coordinate::columnIndexFromString($colLetter);

        if ($startRow == 2 && $endRow == 2 &&
            $colIndex >= $startColIndex && $colIndex <= $endColIndex) {
            return [$startColIndex, $endColIndex];
        }
    }

    $colIndex = Coordinate::columnIndexFromString($colLetter);
    return [$colIndex, $colIndex];
}

function getGroupRangeByName($sheet, $startColIndex, $endColIndex, $currentColIndex) {
    $groupName = $sheet->getCell(Coordinate::stringFromColumnIndex($currentColIndex) . '2')->getValue();
    if ($groupName === null || trim($groupName) === '') {
        return [$currentColIndex, $currentColIndex];
    }

    $left = $currentColIndex;
    $right = $currentColIndex;

    // Влево
    for ($col = $currentColIndex - 1; $col >= $startColIndex; $col--) {
        $val = $sheet->getCell(Coordinate::stringFromColumnIndex($col) . '2')->getValue();
        if ($val === $groupName) {
            $left = $col;
        } else {
            break;
        }
    }

    // Вправо
    for ($col = $currentColIndex + 1; $col <= $endColIndex; $col++) {
        $val = $sheet->getCell(Coordinate::stringFromColumnIndex($col) . '2')->getValue();
        if ($val === $groupName) {
            $right = $col;
        } else {
            break;
        }
    }

    return [$left, $right];
}

// === Основной код ===

try {
    $spreadsheet = IOFactory::load(__DIR__ . '/schedule.xlsx');
} catch (\Throwable $e) {
    error_log("Ошибка при загрузке Excel: " . $e->getMessage());
    wp_die("Ошибка загрузки Excel-файла: " . esc_html($e->getMessage()));
}

$sheet = $spreadsheet->getActiveSheet();

$startColumn = 'C';
$startRow = 5;
$startRowCopy = 5;
$startColIndex = Coordinate::columnIndexFromString($startColumn);

$mergedCells = $sheet->getMergeCells();
$maxColIndex = $startColIndex;

foreach ($mergedCells as $range) {
    [$start, $end] = explode(':', $range);
    [$startCol, $startRowCopy] = Coordinate::coordinateFromString($start);
    [$endCol, $endRow] = Coordinate::coordinateFromString($end);

    if ((int)$startRowCopy === 1) {
        $endColIndex = Coordinate::columnIndexFromString($endCol);
        if ($endColIndex > $maxColIndex) {
            $maxColIndex = $endColIndex;
        }
    }
}

$endColIndex = $maxColIndex;
$endColumn = Coordinate::stringFromColumnIndex($endColIndex);

$highestRow = $sheet->getHighestDataRow();
$endRow = $startRowCopy;
for ($row = $startRowCopy; $row <= $highestRow; $row++) {
    $val = $sheet->getCell('B' . $row)->getValue();
    if ($val !== null && trim($val) !== '') {
        $endRow = $row;
    }
}

$endRow = $endRow + 1;
$startColIndex = Coordinate::columnIndexFromString($startColumn);
$endColIndex = Coordinate::columnIndexFromString($endColumn);

$mergedCells = $sheet->getMergeCells();

$schedule = [];

$positions = loadPositions(__DIR__ . '/positions.txt');
$subjectMap = loadSubjectMap(__DIR__ . '/subject_map.txt');

$countEmptyRow = 0;
for ($row = $startRow; $row <= $endRow; $row++) {
    for ($col = $startColIndex; $col <= $endColIndex; $col++) {
        $colLetter = Coordinate::stringFromColumnIndex($col);
        $address = $colLetter . $row;

        $weekday = getMergedValue($sheet, $mergedCells, 'A' . $row);
        $time = getMergedValue($sheet, $mergedCells, 'B' . $row);

        if ($weekday === null || trim($weekday) === '' || $time === null || trim($time) === '') {
            $countEmptyRow++;
            break;
        }

        $value = getMergedValue($sheet, $mergedCells, $address);
        if ($value === null || trim($value) === '') continue;

        $kurs = getMergedValue($sheet, $mergedCells, $colLetter . '1');
        $group = getMergedValue($sheet, $mergedCells, $colLetter . '2');

        [$mergeStart, $mergeEnd] = getGroupMergeBounds($mergedCells, $colLetter);

        if ($mergeStart !== $mergeEnd) {
            $groupStartColIndex = $mergeStart;
        } else {
            [$groupStartColIndex, $groupEndColIndex] = getGroupRangeByName($sheet, $startColIndex, $endColIndex, $col);
        }

        $subgroupIndex = $col - $groupStartColIndex + 1;
        $subgroup = "$subgroupIndex подгруппа";

        if (!$kurs || !$group || !$weekday || !$time) continue;

        $weekType = (($row - $countEmptyRow) % 2 === 0) ? 'Знаменатель' : 'Числитель';

        $parsedList = parseMultipleLessons($value, $positions, $subjectMap);

        foreach ($parsedList as $parsed) {
            $entry = [
                'ячейка' => $address,
                'дисциплина' => $parsed['дисциплина'],
                'преподаватель' => $parsed['преподаватель'],
                'аудитория' => $parsed['аудитория'],
                'день_недели' => $weekday,
                'время' => $time,
                'неделя_занятия' => $weekType
            ];

            if (!isset($schedule[$kurs])) $schedule[$kurs] = [];
            if (!isset($schedule[$kurs][$group])) $schedule[$kurs][$group] = [];
            if (!isset($schedule[$kurs][$group][$subgroup])) $schedule[$kurs][$group][$subgroup] = [];

            $schedule[$kurs][$group][$subgroup][] = $entry;
        }
    }
}

file_put_contents(__DIR__ . "/schedule.json", json_encode($schedule, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));


echo "Парсинг завершён. Данные сохранены в schedule.json" . PHP_EOL;
