<?php

/**
 * Функция проверяет, заполнено ли указанное поле
 * @param $name string Проверяемое поле в форме
 * @param $name_in_russian string Название поля на русском языке либо описание поля
 * @return string|NULL В случае незаполненности возвращает требование о необходимости добавить данные либо NULL
 */
function validate_filled(string $name, $name_in_russian)
{
    if (empty($_POST[$name])) {
        if (!$name_in_russian) {
            return "Данное поле должно быть заполнено";
        }
        return "Необходимо заполнить поле " . '"' . $name_in_russian . '"';
    }
    return NULL;
}

/**
 * Функция проверяет категорию: если категория равна "Выберите категорию", то валидация не пройдена.
 * @param string $field_name Имя поля
 * @return string|NULL Причина ошибки валидации или NULL при отсутствии ошибок
 **/
function validate_category(string $field_name)
{
    if (empty($_POST[$field_name])) {
        return "Необходимо выбрать категорию у добавляемого лота";
    }
    return NULL;
}


/**
 * Функция валидации изображения, в случае успешной валидации возвращает NULL.
 * @param string $field_name Имя поля изображения
 * @return string|NULL Причина ошибки валидации или NULL при отсутствии ошибок
 */
function validate_file(string $field_name): ?string
{
    if (isset($_FILES[$field_name]) && !empty($_FILES[$field_name]['name'])) {
        $file_name = $_FILES[$field_name]['tmp_name'];
        $file_size = $_FILES[$field_name]['size'];
        $type_file = mime_content_type($file_name);

        if ($type_file === 'image/jpeg' || $type_file === 'image/png' || $type_file === 'image/jpg') {
            return NULL;
        }
        if ($file_size > UPLOAD_MAX_SIZE) {
            return "Максимальный размер файла: 2 Мб";
        }
        return 'Изображение должно быть в одном из данных форматов: jpeg, jpg и png';
    }
    return 'Поле не заполнено';
}


/**
 * Функция валидации полей с цифровым значением (начальной цены лота и шага ставки),
 * в случае успешной валидации возвращает NULL.
 * @param string $field_name Имя поля
 * @return string|NULL Причина ошибки валидации или NULL при отсутствии ошибок
 **/
function validate_number_value(string $field_name): ?string
{
    if ($empty = validate_filled($field_name, NULL)) {
        return $empty;
    } elseif (!is_numeric($_POST[$field_name])) {
        return 'Значение должно быть числом';
    } elseif ($_POST[$field_name] <= 0) {
        return 'Значение должно быть больше нуля';
    }
    return NULL;
}


/**
 * Функция валидации даты окончания лота, в случае успешной валидации возвращает NULL.
 * @param string $field_name Имя поля
 * @return string|NULL Причина ошибки валидации или NULL при отсутствии ошибок
 **/
function validate_date_end(string $field_name): ?string
{
    $tomorrow_date = date_create('tomorrow');

    if ($empty = validate_filled($field_name, NULL)) {
        return $empty;
    } elseif (!is_date_valid($_POST[$field_name], CORRECT_DATE_TIME)) {
        return 'Некорректный формат даты, исправьте на "ГГГГ-ММ-ДД"';
    } elseif (date_create($_POST[$field_name]) < $tomorrow_date) {
        return 'Некорректная дата завершения лота';
    }
    return NULL;
}

/**
 * Функция валидации пароля, в случае успешной валидации возвращает NULL
 * @param string $password Введенный пароль пользователя
 * @return string|NULL Причина ошибки валидации или NULL при отсутствии ошибок
 */
function validate_password(string $password)
{
    if (empty($password)) {
        return "Придумайте и введите пароль для вашего аккаунта";
    }
    if (strlen($password) < 8) {
        return "Придуманный пароль должен быть не менее 8 символов, попробуйте дополнить.";
    }
    if (preg_match("([а-яА-ЯёЁ]+)", $password)) {
        return "В придуманном пароле не должно быть букв из кириллицы: допустимы только латинские буквы, цифры и спец. символы";
    }
    if (!preg_match("([0-9]+)", $password)) {
        return "В введенном пароле не хватает цифр";
    }
    if (!preg_match("/([a-zA-Z]+)/", $password)) {
        return "В введенном пароле не хватает латинских букв";
    }
    return NULL;
}

/**
 * Функция валидации адреса электронной почты, в случае успешной валидации возвращает NULL
 * @param $email string Введенная почта пользователя
 * @return string|NULL Причина ошибки валидации или NULL при отсутствии ошибок
 */
function validate_email(string $email)
{
    if (empty($email)) {
        return "Введите адрес электронной почты";
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return "Введите корректный e-mail в формате name@post.com";
    }
    return NULL;
}

/**
 * Функция запроса на поиск в БД записи в таблице пользователей по введенному в форме email
 * @param mysqli $connect Данные о подключении к БД
 * @return mysqli_result|false Переданный запрос в БД
 */
function verify_existence_email_db(mysqli $connect)
{
    $check_sql = "SELECT * FROM users WHERE email = ? LIMIT 1"; // запрос на поиск записи в таблице пользователей по переданному email
    $prepared_sql = db_get_prepare_stmt($connect, $check_sql, [strtolower($_POST['email'])]); //создаем выражение на основе SQL запроса и данных из формы о e-mail
    mysqli_stmt_execute($prepared_sql);//отправка сформированного SQL-выражения в БД
    return $result_prepared_sql = mysqli_stmt_get_result($prepared_sql); //полученный результат из подготовленного запроса
}

/**
 * Функция валидации уникальности адреса электронной почты, в случае успешной валидации возвращает NULL
 * @param mysqli $connect Данные о подключении к БД
 * @return string|NULL Сообщение о том, что пользователь под данным e-mail уже зарегистрирован или NULL при отсутствии ошибок
 */
function validate_unique_email(mysqli $connect)
{
    $check_result = verify_existence_email_db($connect);

    if (mysqli_num_rows($check_result) > 0) {
        return 'Пользователь с этим email уже зарегистрирован';
    }
    return NULL;
}

/**
 * Функция валидации контактных данных пользователя, в случае успешной валидации возвращает NULL
 * @param $contacts string Контактные данные пользователя
 * @return string|NULL Причина ошибки валидации или NULL при отсутствии ошибок
 */
function validate_contacts(string $contacts)
{
    if (empty($contacts)) {
        return "Оставьте свои контактные данные для связи";
    }
    if (strlen($contacts) > 255) {
        return "Контакты должны занимать менее 255 символов";
    }
    return NULL;
}

/**
 * Функция валидации добавления ставки лота
 * @param string $bet_field Имя поля формы добавления ставки
 * @param int $min_bet Минимальный размер ставки
 * @return string|NULL Возвращает ошибку валидации или NULL при отсутствии ошибок
 **/
function validate_bet_add(string $bet_field, int $min_bet): ?string
{
    if ($empty = validate_filled($bet_field, 'Ваша ставка')) {
        return $empty;
    } elseif (!filter_var($_POST[$bet_field], FILTER_VALIDATE_INT)) {
        return 'Шаг ставки должен быть целым числом больше ноля';
    } elseif ((int)$_POST[$bet_field]  < $min_bet) {
        return 'Ваша ставка должна быть не меньше размера минимальной ставки';
    }
    return NULL;
}

/**
 * Функция проверяет, заполнены ли поля в форме авторизации пользователя
 * @return array Массив, содержащий строки в виде возможных ошибок
 */
function validate_if_filled_in()
{
    //массив, где будут храниться ошибки
    $errors = [];
    //обязательные для заполнения поля
    $rules = [
        'email' => function () {
            return validate_filled('email', 'e-mail');
        },
        'password' => function () {
            return validate_filled('password', 'пароль');
        }
    ];
    //Проверяем все поля на заполненность
    foreach ($form = $_POST as $key => $value) {
        if (isset($rules[$key])) {
            $rule = $rules[$key];
            $errors[$key] = $rule();
        }
    }
    return array_filter($errors);
}

/**
 * Фукнция проверяет, является ли текущий пользователь неавторизованным
 * @param int $user_id ID текущего пользователя
 * @return bool Возвращает true при положительном ответе и false при отрицательном
 */
function is_user_guest(int $user_id): bool
{
    return !isset($user_id);
}

/**
 * Функция сверяет дату и время завершения лота с текущими датой и временем
 * @param array $lot Массив с информацией о лоте, полученной из БД
 * @return bool В случае, если дата завершения в прошлом, возвращает true, иначе false
 */
function is_lot_completed($lot): bool
{
    return strtotime($lot['completed_at']) < time();
}


/**
 * Функция проверяет, является ли текущий пользователь автором выбранного лота
 * @param array $lot Массив с информацией о лоте, полученной из БД
 * @param int $user_id ID текущего пользователя
 * @return bool Возвращает true, если это один и тот же пользователь, иначе false
 */
function is_user_author_of_lot($lot, int $user_id): bool
{
    return $lot['author_id'] === $user_id;
}

/**
 * Фукнция проверяет, сделал ли данный пользователь последнюю ставку по данному лоту
 * @param $bets Массив с информацией о последних 10 ставках по лоту
 * @param int $user_id ID текущего пользователя
 * @return bool Возвращает true, если последнюю ставку сделал этот пользователь, иначе false
 */
function is_user_made_last_bet($bets, int $user_id): bool
{
    return isset($bets[0]['user_id']) && $bets[0]['user_id'] === $user_id;
}
