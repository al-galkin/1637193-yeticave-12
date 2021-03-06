<?php
/**
 * @var array $categories
 * @var array $ad_information
 * @var string $user_name
 * @var string $is_auth
 */
require_once './functions/bootstrap.php'; //подключает все пользовательские функции и константы
require_once './getwinner.php'; //подключает сценарий "отправка письма победителю аукциона"

$connect = db_connection(); //подключение к БД
$categories = get_categories_from_db($connect); //вывод категорий лотов
$ad_information = get_ad_information_from_db($connect); //массив с информацией о всех открытых лотах

$items_count = count($ad_information); //Узнаем общее число лотов, подходящих по условиям поиска
$pagination = null; //пагинация по умолчанию отсутствует

//но если число открытых лотов больше 9, то подключим пагинацию:
if ($items_count > LIMIT_OF_SEARCH_RESULT) {
    $current_page = (int)filter_input(INPUT_GET, 'page', FILTER_SANITIZE_NUMBER_INT); //Получаем номер текущей страницы.
    if ($current_page === 0) {
        $current_page = 1;
    }

    $pages_count = ceil($items_count / LIMIT_OF_SEARCH_RESULT); //Считаем кол-во страниц, которые нужны для вывода результата
    $offset = ($current_page - 1) * LIMIT_OF_SEARCH_RESULT; //Считаем смещение

    $search_page = pathinfo($_SERVER['SCRIPT_NAME'], PATHINFO_BASENAME) ?? 'index.php';

    $pagination = get_pagination($pages_count, $current_page, $search_page); //подключаем пагинацию

    $ad_information = get_pagination_info_about_items($connect,
        $offset); //массив с информацией о лотах с ограничением  вывода на 1 страницу
}
$page_content = include_template('/main.php', compact('categories', 'ad_information', 'pagination'));

$layout_content = include_template('/layout.php', [
    'content' => $page_content,
    'categories' => $categories,
    'title' => 'Yeticave - Главная страница by Alexander Galkin',
    'user_name' => $user_name,
    'is_auth' => $is_auth,
    'is_index_page' => true
]);

print($layout_content);


