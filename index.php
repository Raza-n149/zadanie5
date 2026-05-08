<?php
session_start();
header('Content-Type: text/html; charset=UTF-8');

// Подключение к БД
$db_host = 'localhost';
$db_user = 'u82260';
$db_pass = '3052562';  // ЗАМЕНИТЕ!
$db_name = 'u82260';

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Ошибка БД: " . $e->getMessage());
}

// Конфигурация полей (как в Задании 4)
$fields = [
    'fio' => [
        'required' => true,
        'label' => 'ФИО',
        'pattern' => '/^[а-яА-Яa-zA-Z\s\-]+$/u',
        'allowed' => 'буквы, пробелы и дефисы'
    ],
    'email' => [
        'required' => true,
        'label' => 'Email',
        'pattern' => '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/',
        'allowed' => 'латинские буквы, цифры, @, точка, дефис'
    ],
    'phone' => [
        'required' => true,
        'label' => 'Телефон',
        'pattern' => '/^[\d\+\-\(\)\s]{10,20}$/',
        'allowed' => 'цифры, +, -, (, ), пробелы'
    ],
    'birthdate' => [
        'required' => true,
        'label' => 'Дата рождения',
        'pattern' => '/^\d{2}\.\d{2}\.\d{4}$/',
        'allowed' => 'формат ДД.ММ.ГГГГ'
    ],
    'gender' => [
        'required' => true,
        'label' => 'Пол',
        'pattern' => '/^(male|female)$/',
        'allowed' => 'male или female'
    ],
    'languages' => [
        'required' => true,
        'label' => 'Языки программирования',
        'allowed' => 'Pascal, C, C++, JavaScript, PHP, Python, Java, Haskell, Clojure, Prolog, Scala, Go'
    ],
    'biography' => [
        'required' => false,
        'label' => 'Биография'
    ],
    'contract_accepted' => [
        'required' => true,
        'label' => 'Согласие с контрактом'
    ]
];

$allowed_languages = ['Pascal', 'C', 'C++', 'JavaScript', 'PHP', 'Python', 'Java', 'Haskell', 'Clojure', 'Prolog', 'Scala', 'Go'];

// Функция генерации логина
function generateLogin($fio, $id) {
    $parts = explode(' ', trim($fio));
    $lastname = $parts[0] ?? 'user';
    $login = strtolower(transliterator_transliterate('Any-Latin; Latin-ASCII;', $lastname)) . $id;
    return $login;
}

// Функция генерации пароля
function generatePassword($length = 10) {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%';
    return substr(str_shuffle($chars), 0, $length);
}

// Обработка POST-запроса (сохранение/обновление)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && !isset($_POST['login_action'])) {
    
    $has_errors = false;
    $form_data = [];
    $is_update = isset($_SESSION['user_id']) && isset($_POST['application_id']);
    $app_id = $is_update ? intval($_POST['application_id']) : null;
    
    // Валидация (как в Задании 4)
    // ФИО
    $fio = trim($_POST['fio'] ?? '');
    if (empty($fio)) {
        setcookie('fio_error', '1', 0, '/');
        $has_errors = true;
    } elseif (!preg_match($fields['fio']['pattern'], $fio)) {
        setcookie('fio_error', '1', 0, '/');
        $has_errors = true;
    } else {
        $form_data['fio'] = $fio;
    }
    
    // Email
    $email = trim($_POST['email'] ?? '');
    if (empty($email)) {
        setcookie('email_error', '1', 0, '/');
        $has_errors = true;
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        setcookie('email_error', '1', 0, '/');
        $has_errors = true;
    } else {
        $form_data['email'] = $email;
    }
    
    // Телефон
    $phone = trim($_POST['phone'] ?? '');
    if (empty($phone)) {
        setcookie('phone_error', '1', 0, '/');
        $has_errors = true;
    } elseif (!preg_match($fields['phone']['pattern'], $phone)) {
        setcookie('phone_error', '1', 0, '/');
        $has_errors = true;
    } else {
        $form_data['phone'] = $phone;
    }
    
    // Дата рождения
    $birthdate = trim($_POST['birthdate'] ?? '');
    if (empty($birthdate)) {
        setcookie('birthdate_error', '1', 0, '/');
        $has_errors = true;
    } elseif (!preg_match($fields['birthdate']['pattern'], $birthdate)) {
        setcookie('birthdate_error', '1', 0, '/');
        $has_errors = true;
    } else {
        $form_data['birthdate'] = $birthdate;
    }
    
    // Пол
    $gender = $_POST['gender'] ?? '';
    if (!in_array($gender, ['male', 'female'])) {
        setcookie('gender_error', '1', 0, '/');
        $has_errors = true;
    } else {
        $form_data['gender'] = $gender;
    }
    
    // Языки
    $selected_languages = $_POST['languages'] ?? [];
    if (empty($selected_languages)) {
        setcookie('languages_error', '1', 0, '/');
        $has_errors = true;
    } else {
        $valid = true;
        foreach ($selected_languages as $lang) {
            if (!in_array($lang, $allowed_languages)) {
                $valid = false;
                break;
            }
        }
        if (!$valid) {
            setcookie('languages_error', '1', 0, '/');
            $has_errors = true;
        } else {
            $form_data['languages'] = $selected_languages;
        }
    }
    
    // Биография
    $biography = trim($_POST['biography'] ?? '');
    $form_data['biography'] = $biography;
    
    // Чекбокс
    $contract_accepted = isset($_POST['contract_accepted']) && $_POST['contract_accepted'] == '1';
    if (!$contract_accepted) {
        setcookie('contract_accepted_error', '1', 0, '/');
        $has_errors = true;
    }
    $form_data['contract_accepted'] = $contract_accepted ? 1 : 0;
    
    // Сохраняем значения в cookies (для неавторизованных - Задание 4)
    foreach ($form_data as $key => $val) {
        if ($key != 'languages') {
            setcookie($key . '_value', $val, time() + 30 * 24 * 60 * 60, '/');
        }
    }
    setcookie('languages_value', implode(',', $selected_languages), time() + 30 * 24 * 60 * 60, '/');
    
    if ($has_errors) {
        header('Location: index.php');
        exit();
    }
    
    // Сохранение в БД
    try {
        $pdo->beginTransaction();
        
        $birthdate_obj = DateTime::createFromFormat('d.m.Y', $form_data['birthdate']);
        $birthdate_sql = $birthdate_obj ? $birthdate_obj->format('Y-m-d') : null;
        
        if ($is_update && $app_id) {
            // Обновление существующей заявки
            $stmt = $pdo->prepare("
                UPDATE applications SET 
                    full_name = :fio, phone = :phone, email = :email, 
                    birth_date = :birthdate, gender = :gender, 
                    biography = :biography, contract_accepted = :contract_accepted
                WHERE id = :id
            ");
            $stmt->execute([
                ':fio' => $form_data['fio'],
                ':phone' => $form_data['phone'],
                ':email' => $form_data['email'],
                ':birthdate' => $birthdate_sql,
                ':gender' => $form_data['gender'],
                ':biography' => $form_data['biography'],
                ':contract_accepted' => $form_data['contract_accepted'],
                ':id' => $app_id
            ]);
            
            // Удаляем старые языки
            $pdo->prepare("DELETE FROM application_languages WHERE application_id = ?")->execute([$app_id]);
            $application_id = $app_id;
            $success_message = "Данные успешно обновлены!";
            
        } else {
            // Новая заявка
            $stmt = $pdo->prepare("
                INSERT INTO applications (full_name, phone, email, birth_date, gender, biography, contract_accepted) 
                VALUES (:fio, :phone, :email, :birthdate, :gender, :biography, :contract_accepted)
            ");
            $stmt->execute([
                ':fio' => $form_data['fio'],
                ':phone' => $form_data['phone'],
                ':email' => $form_data['email'],
                ':birthdate' => $birthdate_sql,
                ':gender' => $form_data['gender'],
                ':biography' => $form_data['biography'],
                ':contract_accepted' => $form_data['contract_accepted']
            ]);
            $application_id = $pdo->lastInsertId();
            
            // Генерация логина и пароля
            $login = generateLogin($form_data['fio'], $application_id);
            $password = generatePassword();
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            
            // Сохраняем пользователя
            $stmt = $pdo->prepare("INSERT INTO users (login, password_hash, application_id) VALUES (:login, :hash, :app_id)");
            $stmt->execute([':login' => $login, ':hash' => $password_hash, ':app_id' => $application_id]);
            
            // Обновляем заявку ссылкой на пользователя
            $pdo->prepare("UPDATE applications SET user_id = :user_id WHERE id = :id")->execute([':user_id' => $pdo->lastInsertId(), ':id' => $application_id]);
            
            $success_message = "Данные сохранены!<br>Ваш логин: <strong>$login</strong><br>Пароль: <strong>$password</strong><br><span style='color:red;'>Сохраните эти данные! Они показываются только один раз.</span>";
        }
        
        // Сохраняем языки
        $stmt_lang = $pdo->prepare("INSERT INTO application_languages (application_id, language_id) VALUES (:app_id, :lang_id)");
        $lang_map = [];
        $stmt_l = $pdo->query("SELECT id, name FROM programming_languages");
        foreach ($stmt_l->fetchAll() as $lang) {
            $lang_map[$lang['name']] = $lang['id'];
        }
        foreach ($form_data['languages'] as $lang_name) {
            if (isset($lang_map[$lang_name])) {
                $stmt_lang->execute([':app_id' => $application_id, ':lang_id' => $lang_map[$lang_name]]);
            }
        }
        
        $pdo->commit();
        
        // Сохраняем успешные значения в cookies на год (Задание 4)
        foreach ($form_data as $key => $val) {
            if ($key != 'languages') {
                setcookie($key . '_value', $val, time() + 365 * 24 * 60 * 60, '/');
            }
        }
        setcookie('languages_value', implode(',', $selected_languages), time() + 365 * 24 * 60 * 60, '/');
        setcookie('save_success', '1', time() + 365 * 24 * 60 * 60, '/');
        
        $_SESSION['temp_message'] = $success_message;
        header('Location: index.php');
        exit();
        
    } catch (PDOException $e) {
        $pdo->rollBack();
        setcookie('db_error', '1', 0, '/');
        header('Location: index.php');
        exit();
    }
}

// Обработка входа
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login_action'])) {
    $login = trim($_POST['login'] ?? '');
    $password = $_POST['password'] ?? '';
    
    $stmt = $pdo->prepare("SELECT u.*, a.* FROM users u JOIN applications a ON u.application_id = a.id WHERE u.login = :login");
    $stmt->execute([':login' => $login]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['application_id'] = $user['application_id'];
        $_SESSION['login'] = $user['login'];
        $_SESSION['just_logged_in'] = true;
        header('Location: index.php');
        exit();
    } else {
        $_SESSION['login_error'] = 'Неверный логин или пароль';
        header('Location: index.php');
        exit();
    }
}

// Выход
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit();
}

// GET-запрос - показ формы
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    
    $messages = [];
    $errors = [];
    $values = [];
    $show_login_form = false;
    $show_edit_form = false;
    $user_data = null;
    
    // Сообщение после сохранения
    if (isset($_SESSION['temp_message'])) {
        $messages[] = '<div class="message success">' . $_SESSION['temp_message'] . '</div>';
        unset($_SESSION['temp_message']);
    }
    
    // Ошибка входа
    if (isset($_SESSION['login_error'])) {
        $messages[] = '<div class="message error">' . $_SESSION['login_error'] . '</div>';
        unset($_SESSION['login_error']);
    }
    
    // Если пользователь авторизован
    if (isset($_SESSION['user_id'])) {
        $show_edit_form = true;
        $stmt = $pdo->prepare("
            SELECT a.*, GROUP_CONCAT(pl.name SEPARATOR ',') as languages 
            FROM applications a
            LEFT JOIN application_languages al ON a.id = al.application_id
            LEFT JOIN programming_languages pl ON al.language_id = pl.id
            WHERE a.id = :id
            GROUP BY a.id
        ");
        $stmt->execute([':id' => $_SESSION['application_id']]);
        $user_data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user_data) {
            $values['fio'] = $user_data['full_name'];
            $values['email'] = $user_data['email'];
            $values['phone'] = $user_data['phone'];
            $values['birthdate'] = $user_data['birth_date'] ? date('d.m.Y', strtotime($user_data['birth_date'])) : '';
            $values['gender'] = $user_data['gender'];
            $values['languages'] = explode(',', $user_data['languages'] ?? '');
            $values['biography'] = $user_data['biography'];
            $values['contract_accepted'] = $user_data['contract_accepted'];
        }
        
        $messages[] = '<div class="message success">Вы вошли как: ' . htmlspecialchars($_SESSION['login']) . ' | <a href="?logout">Выйти</a></div>';
        
    } elseif (isset($_GET['edit']) || isset($_SESSION['just_logged_in'])) {
        unset($_SESSION['just_logged_in']);
        $show_login_form = true;
    } else {
        // Неавторизованный режим - Задание 4 (cookies)
        if (isset($_COOKIE['save_success']) && $_COOKIE['save_success'] == '1') {
            setcookie('save_success', '', time() - 3600, '/');
            $messages[] = '<div class="message success">Форма успешно отправлена! Данные сохранены на год.</div>';
        }
        
        foreach ($fields as $name => $config) {
            $errors[$name] = isset($_COOKIE[$name . '_error']) && $_COOKIE[$name . '_error'] == '1';
            if ($errors[$name]) {
                setcookie($name . '_error', '', time() - 3600, '/');
                $messages[] = '<div class="message error">Ошибка в поле "' . $config['label'] . '"</div>';
            }
        }
        
        foreach ($fields as $name => $config) {
            $values[$name] = $_COOKIE[$name . '_value'] ?? '';
        }
        $values['languages'] = isset($values['languages']) && !empty($values['languages']) ? explode(',', $values['languages']) : [];
    }
    
    // Данные для входа
    if (isset($_GET['edit'])) {
        $show_login_form = true;
    }
    
    include 'form.php';
}
?>
