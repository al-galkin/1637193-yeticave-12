<?php
/**
 * @var string $user_name
 * @var array $categories
 * @var string $is_auth
 */
require_once './functions/bootstrap.php'; //подключает все пользовательские функции и константы

$connect = db_connection();

$categories = get_categories_from_db($connect);

$user_id = $_SESSION['user']['id'] ?? null; //проверяем, авторизован ли пользователь
// показываем ошибку 403, если пользователь не авторизован на сайте
if (is_user_guest($user_id)) {
    error_output(403, 'add');
}
//если не выбрана категория, то показываем пустую форму для заполнения
if (!isset($_POST['category'])) {
    show_add_lot_page($user_name, $categories);
    exit(0);
}

// применение функций для проверки полей формы к каждому элементу формы внутри цикла
$rules = [
    'lot-name' => static function () {
        return validate_filled('lot-name', 'наименование лота');
    },
    'category' => static function () {
        return validate_category('category');
    },
    'message' => static function () {
        return validate_filled('message', 'описание лота');
    },
    'lot-rate' => static function () {
        return validate_number_value('lot-rate');
    },
    'lot-step' => static function () {
        return validate_number_value('lot-step');
    },
    'lot-date' => static function () {
        return validate_date_end('lot-date');
    }
];

//Валидация соответствующих полей и сохранение ошибок (при наличии) в массив $errors
foreach ($_POST as $key => $value) {
    if (!isset($rules[$key])) {
        continue;
    }
    $rule = $rules[$key];
    $errors[$key] = $rule();
}

//валидация файла изображения из массива $_FILES
$errors['lot-img'] = validate_file_before_saving('lot-img');

$errors = array_filter($errors); //фильтруем ошибки из массива - добавляем их в новый в случае присутствия самих ошибок

//при отсутствии ошибок - сохраняем добавленный файл
if (empty($errors)) {
    $file_url = save_file('lot-img');

    //если не получилось загрузить файл - показываем ошибку и страницу с формой для заполнения
    if (!$file_url) {
        $errors['lot-img'] = "Произошла ошибка сохранения файла. Попробуйте позже";
        show_add_lot_page($user_name, $categories, $errors);
        exit(0);
    }

    // создаем шаблон подготовленного выражения для вставки в БД
    $sql_add_lot = 'INSERT INTO item (completed_at,
                                      category_id,
                                      author_id,
                                      title,
                                      description,
                                      image_url,
                                      start_price,
                                      bet_step)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)';

    // формируем подготовленное выражение на основе SQL запроса из $sql_add_lot
    $stmt = db_get_prepare_stmt(
        $connect,
        $sql_add_lot,
        [
            $_POST['lot-date'],
            $_POST['category'],
            $user_id,
            $_POST['lot-name'],
            $_POST['message'],
            $file_url,
            $_POST['lot-rate'],
            $_POST['lot-step']
        ]
    );

//отправка сформированных SQL-выражений в БД
    $result_stmt = mysqli_stmt_execute($stmt);
    //если не прошло, то выводим ошибку и возвращаем на страницу с формой
    if (!$result_stmt) {
        $errors['lot-name'] = "Произошла ошибка сохранения в базу. Попробуйте еще раз позже";
        show_add_lot_page($user_name, $categories, $errors);
        die;
    }

    //создаем страницу нового добавленного лота
    $last_id = mysqli_insert_id($connect);
    header('Location: /lot.php?id=' . $last_id);
    die();
}

//если не заполняли форму - выводим пустую страницу с формой
show_add_lot_page($user_name, $categories, $errors);

