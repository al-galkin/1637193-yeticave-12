<?php
/**
 * @var string $user_name
 * @var string $is_auth
 */
require_once './functions/bootstrap.php'; //подключает все пользовательские функции и константы

$connect = db_connection();

if (isset($_GET['id'])) {
    $item_id = (int)$_GET['id']; //получаем ID текущего лота
} else {
    http_response_code(400);
    $error = 'Произошла ошибка: &#129298; ';
    $error_description = 'Страница не найдена. &#128532; ';
    $error_link = '/index.php';
    $error_link_description = 'Предлагаем вернуться на главную.';
    $page_content = include_template(
        '/error_page.php',
        [
            'error' => $error,
            'error_description' => $error_description,
            'error_link' => $error_link,
            'error_link_description' => $error_link_description
        ]
    );
    $layout_content = include_template('/layout.php', [
        'content' => $page_content,
        'categories' => [],
        'title' => 'Страница не найдена',
        'user_name' => $user_name,
        'is_auth' => $is_auth
    ]);

    exit($layout_content);
}
$categories = get_categories_from_db($connect); //получаем список категорий из БД
$lot = get_info_about_lot_from_db($item_id, $connect, $categories); //получаем информацию о лотах из БД
$show_bet_add = true; //добавлять новую ставку по-умолчанию можно

//история ставок по данному лоту
$sql_bet = 'SELECT bet.item_id, bet.created_at as date, bet.total, bet.user_id, users.name as username
            FROM bet
            INNER JOIN users ON users.id = bet.user_id
            WHERE item_id = ?
            ORDER BY date DESC
            LIMIT 10';
$sql_bet_stmt = db_get_prepare_stmt($connect, $sql_bet, [$item_id]);
mysqli_stmt_execute($sql_bet_stmt);
$sql_bet_result = mysqli_stmt_get_result($sql_bet_stmt);

if (mysqli_num_rows($sql_bet_result) > 0) {
    $bets = mysqli_fetch_all($sql_bet_result, MYSQLI_ASSOC);
    //количество ставок по данному лоту
    $count_bet = $bets !== null ? count($bets) : 0;
}
// отправка нового значения ставки
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // берем из БД текущий размер ставки
    if (isset($lot['current_price']) && isset($lot['bet_step'])) {
        $min_bet = ($lot['bet_step'] + $lot['current_price']);
    }
    //правило для обязательного поля ввода новой ставки
    $rules = [
        'cost' => function () use (&$min_bet) {
            return validate_bet_add('cost', $min_bet);
        }
    ];
    // сбор возможных ошибок валидации
    foreach ($_POST as $key => $value) {
        if (isset($rules[$key])) {
            $rule = $rules[$key];
            $errors[$key] = $rule();
        }
    }
    $errors = array_filter($errors);

    //если ошибок нет, то добавляем ставку в БД:
    if (empty($errors)) {
        // фильтр входящего значения на соответствие числу, введенного в форму
        $filter_value_bet = filter_input(INPUT_POST, 'cost', FILTER_SANITIZE_NUMBER_INT);
        // создаем шаблон подготовленного выражения для вставки в БД
        $sql_add_bet = 'INSERT INTO bet (total,
                             user_id,
                             item_id)
                    VALUES (?, ?, ?)';

        // формируем подготовленное выражение на основе SQL запроса из $sql_add_bet
        $stmt_add_bet = db_get_prepare_stmt(
            $connect,
            $sql_add_bet,
            [
                $filter_value_bet,
                $_SESSION['user']['id'],
                $item_id
            ]
        );
        //отправка сформированных SQL-выражений в БД
        $result_stmt = mysqli_stmt_execute($stmt_add_bet);
        //если не прошло, то выводим ошибку
        if (!$result_stmt) {
            $errors['cost'] = "Произошла ошибка сохранения в базу. Попробуйте еще раз позже";
        }
        // если удачно добавили ставку, переадресовываем снова на страницу этого лота
        header('Location: /lot.php?id=' . $item_id);
    }
}
//проверка ограничений показа блока добавления ставки
if (
    !isset($_SESSION['user']['id']) ||
    strtotime($lot['completed_at']) < time() ||
    $lot['author_id'] === $_SESSION['user']['id'] ||
    isset($bets[0]['user_id']) && $bets[0]['user_id'] === $_SESSION['user']['id']
) {
    $show_bet_add = false;
}

$page_content = include_template('/lot_page.php', compact('item_id', 'lot', 'errors', 'bets', 'count_bet', 'show_bet_add'));

$layout_content = include_template('/layout.php', [
    'content' => $page_content,
    'categories' => $categories,
    'title' => htmlspecialchars($lot['title']),
    'user_name' => $user_name,
    'is_auth' => $is_auth
]);

print($layout_content);
