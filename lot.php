<?php
/**
 * @var string $user_name
 * @var string $is_auth
 */
require_once './functions/bootstrap.php'; //подключает все пользовательские функции и константы

$connect = db_connection();
$bets = []; //информация о ставках
$errors = []; //массив с возможными ошибками
$count_bet = 0; //количество ставок по данному лоту
$cost = ''; //введенное значение ставки

//если лот с текущим ID не существует, выводим ошибку
if (!(isset($_GET['id']))) {
    error_output(400);
}

//получаем значение ставки из формы
if (isset($_POST['cost']) && $_POST['cost'] !== '') {
    $cost = (int)$_POST['cost'];
}

$item_id = (int)$_GET['id']; //получаем ID текущего лота
$categories = get_categories_from_db($connect); //получаем список категорий из БД
$lot = get_info_about_lot_from_db($item_id, $connect); //получаем информацию о лотах из БД

$sql_bet_history = get_bet_history($item_id, $connect); //история о последних 10 ставках по этому лоту

if (mysqli_num_rows($sql_bet_history) > 0) {
    //соберем информацию о последних 10 ставках в массив
    $bets = mysqli_fetch_all($sql_bet_history, MYSQLI_ASSOC);
    //определим количество ставок по данному лоту, если их больше 0 и менее 10
    $count_bet = $bets !== null ? count($bets) : 0;
}

$user_id = $_SESSION['user']['id'] ?? null; //проверяем, авторизован ли пользователь

// отправка нового значения ставки
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = check_errors_before_add_bet($lot, $errors);
    //если ошибок нет, то добавляем ставку в БД:
    if (empty($errors)) {
        $result_add_bet = add_bet_in_db($item_id, $connect, $user_id); //отправляем запрос о добавлении новой ставки
        //если не прошло, то выводим ошибку
        if (!$result_add_bet) {
            $errors['cost'] = "Произошла ошибка сохранения в базу. Попробуйте еще раз позже";
        }
        // если удачно добавили ставку, переадресовываем снова на страницу этого лота
        header('Location: /lot.php?id=' . $item_id);
    }
}

$show_bet_add = true; //добавлять новую ставку по-умолчанию можно

//проверка ограничений показа блока добавления ставки
if (
    is_user_guest($user_id) ||
    is_lot_completed($lot) ||
    is_user_author_of_lot($lot, $user_id) ||
    is_user_made_last_bet($bets, $user_id)
) {
    $show_bet_add = false;
}

$page_content = include_template('/lot_page.php',
    compact('item_id', 'lot', 'errors', 'bets', 'count_bet', 'show_bet_add', 'cost'));

$layout_content = include_template('/layout.php', [
    'content' => $page_content,
    'categories' => $categories,
    'title' => htmlspecialchars($lot['title'], ENT_QUOTES | ENT_HTML5),
    'user_name' => $user_name,
    'is_auth' => $is_auth
]);

print($layout_content);
