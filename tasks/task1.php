<?php

// задаём количество слов, оборачиваемых в ссылку
define('LINK_SIZE', 3);

// задаём максимальное количество символов для превью
define('MAX_SYMBOL_VALUE', 200);

// проверяем, был ли отправлен текст статьи. Предварительно удалим пробелы с помощью trim, чтобы строка только с пробелами не прошла условие
if (!empty(trim($_POST['articleText']))) {
    // преобразуем специальные символы и вырезаем пробелы в начале и конце строки, после чего записываем текст в переменную 
    $articleText = trim(htmlspecialchars($_POST['articleText']));
} else {
    // уведомляем об использовании стандартного текста
    echo '<p class="error">Текст не отправлен, используется стандартный!</p>';

    // записываем стандартный вариант статьи
    $articleText = 'Шаи — в египетской мифологии бог урожая винограда, виноградной лозы; бог судьбы и бог-покровитель человека. Шаи было не только именем бога, но и означало также предопределение, судьбу, участь. В противоположность греческим мойрам или римским паркам он был лишь орудием великих богов, в частности, бога Тота. Шаи определял срок человеческой жизни. Вместе с Месхенет следил за поведением человека, а на суде в загробном мире рассказывал обо всех поступках богам Великой Эннеады. Если загробный Суд признавал умершего безгрешным, Шаи провожал его душу в Поля Иалу.';
}

// проверяем, была ли отправлена ссылка
if (filter_input(INPUT_POST, 'articleLink', FILTER_VALIDATE_URL)) {
    // преобразуем специальные символы и вырезаем пробелы в начале и конце строки, после чего записываем ссылку в переменную 
    $articleLink = trim(htmlspecialchars($_POST['articleLink']));
} else {
    // уведомляем об использовании стандартной ссылки
    echo '<p class="error">Ссылка не отправлена или имеет не верный формат, используется стандартная!</p>';

    // записываем стандартный вариант статьи
    $articleLink = 'https://ru.wikipedia.org/wiki/Шаи';
}

// создадим превью статьи
$articlePreview = createPreview($articleText, $articleLink);

// выведем полученное превью
echo '<p>' . $articlePreview . '</p>';


// функция создания превью
function createPreview($articleText, $articleLink) {

    // обрежем статью согласно условию. Применим mb_substr для корректной обрезки в многобайтовой кодировке
    $articlePreview = mb_substr($articleText, 0, MAX_SYMBOL_VALUE, 'UTF-8');

    // при наличии удалим некоторые знаки препинания, не сочетающиеся с многоточием, в конце обрезанного текста
    // также удалим возможные пробелы, поскольку дальнейшее приведение строки к массиву создаст лишний элемент и собьёт формирование ссылки
    $articlePreview = rtrim($articlePreview, '.,:; ');

    // проверим, оканчивается ли строка на восклицательный или вопросительный знак
    if (preg_match("/[!?]$/", $articlePreview)) {
        // добавим 2 точки вместо 3
        $articlePreview .= '..';
    } else {
        // добавим 3 точки
        $articlePreview .= '...';
    }

    // отделим слова по пробелам в массив
    $articlePreview = explode(' ', $articlePreview);

    // выделим последние 3 слова
    $linkWords = array_slice($articlePreview, -LINK_SIZE);

    // вырежем последние 3 слова из основного текста
    $articlePreview = array_slice($articlePreview, 0, count($articlePreview) - LINK_SIZE);

    // обернём последние 3 слова в ссылку, прибавим многоточие  и вернём строку
    $linkWords = '<a href="' . $articleLink . '">' . implode(' ', $linkWords) . '</a>';

    // преобразуем массив обратно в строку и добавим последние 3 слова , предварительно добавив пробел
    $articlePreview = implode(' ', $articlePreview) . ' ' . $linkWords;

    // вернём полученный текст
    return $articlePreview;
}


/* Проблемы, обнаруженные в ходе решения этой задачи:

А) Общие проблемы:

Функция substr не позволяет корректно обрабатывать символы, занимающие более одного байта памяти. 
В связи с этим результат этой функции сократит строку на неверное количество символов, но верное количество байтов. 
Для решения этой проблемы используется функция mb_substr, позволяющая получить корректное количество символов. 
Однако функция mb_substr имеет две другие проблемы: во-первых, она не является встроенной функцией php, 
в связи с чем эта функция может быть не доступна на отдельных конфигурациях, и выполнение кода вызовет ошибку.
Во вторых корректная работа функции подразумевает знание кодировки поступающего текста, из-за чего при 
динамическом формировании статьи может попасть текст с другой кодировкой, что вызовет ошибку.


Б) проблемы, связанные с выбранным способом реализации задания:

1. Поскольку данные поступают от пользователя - имеется необходимость фильтрации значений для предотвращения хакерских атак.
Фильтр текста сработал нормально, а фильтр ссылки создал проблемы c проверкой ссылок на киррилице. 
Встроеная команда фильтра принимает только url, состоящие из символов ASCII. Прочие символы не проходят валидацию. 
Возможным решением является введение проверки правильности формата ссылки до отправки с помощью javascript
и оставить только преобразование специальных символов и вырезание лишних пробелов. 

2. Поскольку существование добавленного текста проверяется с помощью empty - отправленный "0" также возвращает уведомление об
использовании стандартного текста статьи. Данную проблему можно опустить, поскольку в контексте задачи один символ нуля 
слабо подходит на превью статьи с ссылкой в 3 слова.

*/