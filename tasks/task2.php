<?php

// задаём минимальное значение данных
define('MIN_VALUE', 0);

// задаём максимальное значение данных
define('MAX_VALUE', 1000);

// задаём полученное количество фатальных ошибок
$fatal = $_POST['fatal_errors'];

// задаём полученное количество ворнингов 
$warn = $_POST['warnings'];


// проверим полученные значения на существование и соответствие условию

$error_message = '';

switch (true) {
    case (!is_numeric($fatal)):
        $error_message = 'Фатальные ошибки не заданы!';
        break;
    case ($fatal < MIN_VALUE):
        $error_message = sprintf('Значение фатальных ошибок меньше %d!', MIN_VALUE);
        break;
    case ($fatal > MAX_VALUE):
        $error_message = sprintf('Значение фатальных ошибок больше %d!', MAX_VALUE);
        break;
    case (!is_numeric($warn)):
        $error_message = 'Ворнинги не заданы!';
        break;
    case ($warn < MIN_VALUE):
        $error_message = sprintf('Значение ворнингов меньше %d!', MIN_VALUE);
        break;
    case ($warn > MAX_VALUE):
        $error_message = sprintf('Значение ворнингов больше %d!', MAX_VALUE);
        break;
}

// вернём ошибку при наличии
if ($error_message) {
    echo '<p class="error">' . $error_message . '</p>';
    return;
}

// при отсутсвии фатальных ошибок и ворнингов исправления не нужны
if ($fatal == 0 && $warn == 0) echo '<p> Исправления не нужны. </p>';

// при нечетном количестве фатальных ошибок и отсутствии ворнингов исправить код невозможно
elseif ($fatal % 2 != 0 && $warn == 0) echo '<p> Исправить код невозможно: -1 </p>';

// ищем необходимое количество коммитов
else echo '<p>Необходимое колличество коммитов - ' . countCommits($fatal, $warn) . '</p>';



// Для собственной проверки написал функцию с выводом каждого коммита в таблицу. 

/*  else {
    $result = fullCountCommits($fatal, $warn);

    $html = '<table><tr><td>Коммит</td><td>Фатальные ошибки</td><td>Ворнинги</td></tr>';
    foreach ($result as $i => $commit) {
        $html .= '<tr>';
        $html .= '<td>' . $i . '</td>'; 
        $html .= '<td>' . $commit['fatal'] . '</td>';
        $html .= '<td>' . $commit['warn'] . '</td>';
        $html .= '</tr>';
    }
    $html .= '</table>';

    echo '<p>Необходимое колличество коммитов - ' . countCommits($fatal, $warn) . '</p>';
    echo $html;
} */

// функция подсчёта количества коммитов
function countCommits($fatal, $warn) {

    // обозначаем переменную для записи коммитов
    $result = 0;

    // проверим нечётность ворнингов
    if ($warn % 2 != 0) {
        // приведём к чётному значению исправив один ворнинг
        $warn++;
        $result++;
    }

    // проверим чётность фатальных ошибок с учетом появления новых от исправления ворнингов
    if (($fatal + $warn / 2) % 2 != 0) {
        // В случае нечётности исправим по одному ворнингу 2 раза
        $warn += 2;
        $result += 2;
    }

    // исправим все ворнинги по два за коммит
    $fatal += $warn / 2;
    $result += $warn / 2;

    // исправим все фатальные ошибки по две за коммит
    $result += $fatal / 2;

    // возвращаем результат
    return $result;
}

// функция таблицы со всеми коммитами 
function fullCountCommits($fatal, $warn) {

    // обозначаем переменную для записи коммитов
    $result = [['fatal' => $fatal, 'warn' => $warn]];

    // проверим нечётность ворнингов
    if ($warn % 2 != 0) {
        // приведём к чётному значению исправив один ворнинг
        $warn++;
        $result[] = ['fatal' => $fatal, 'warn' => $warn];
    }

    // проверим чётность фатальных ошибок с учетом появления новых от исправления ворнингов
    if (($fatal + $warn / 2) % 2 != 0) {
        // В случае нечётности исправим по одному ворнингу 2 раза
        for ($i = 0; $i < 2; $i++) {
            $warn++;
            $result[] = ['fatal' => $fatal, 'warn' => $warn];
        }
    }

    // исправим все ворнинги по два за коммит
    while ($warn != 0) {
        $warn -= 2;
        $fatal++;
        $result[] = ['fatal' => $fatal, 'warn' => $warn];
    }

    // исправим все фатальные ошибки по две за коммит
    while ($fatal != 0) {
        $fatal -= 2;
        $result[] = ['fatal' => $fatal, 'warn' => $warn];
    }

    // возвращаем результат
    return $result;
}
