<?php
session_start();
header('Content-Type: text/html; charset=UTF-8');

require_once 'db.php';

$fields = [
    'fio' => [
        'required' => true,
        'label' => 'ФИО',
        'pattern' => '/^[а-яА-Яa-zA-Z\s\-]+$/u',
        'allowed' => 'буквы, пробелы и дефисы',
        'placeholder' => 'Иванов Иван Иванович'
    ],
    'email' => [
        'required' => true,
        'label' => 'Email',
        'pattern' => '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/',
        'allowed' => 'латинские буквы, цифры, @, точка, дефис',
        'placeholder' => 'user@example.com'
    ],
    'phone' => [
        'required' => true,
        'label' => 'Телефон',
        'pattern' => '/^[\d\+\-\(\)\s]{10,20}$/',
        'allowed' => 'цифры, +, -, (, ), пробелы',
        'placeholder' => '+7 (123) 456-78-90'
    ],
    'birthdate' => [
        'required' => true,
        'label' => 'Дата рождения',
        'pattern' => '/^\d{2}\.\d{2}\.\d{4}$/',
        'allowed' => 'формат ДД.ММ.ГГГГ (день.месяц.год)',
        'placeholder' => '15.01.1990'
    ],
    'gender' => [
        'required' => true,
        'label' => 'Пол',
        'pattern' => '/^(male|female)$/',
        'allowed' => 'male или female'
    ],
    'languages' => [
        'required' => true,
        'label' => 'Любимые языки программирования',
        'allowed' => 'Pascal, C, C++, JavaScript, PHP, Python, Java, Haskell, Clojure, Prolog, Scala, Go'
    ],
    'biography' => [
        'required' => false,
        'label' => 'Биография',
        'max_length' => 5000
    ],
    'contract_accepted' => [
        'required' => true,
        'label' => 'Согласие с контрактом'
    ]
];

$allowed_languages = ['Pascal', 'C', 'C++', 'JavaScript', 'PHP', 'Python', 'Java', 'Haskell', 'Clojure', 'Prolog', 'Scala', 'Go'];

// Обработка выхода
if (isset($_POST['logout_submit'])) {
    session_destroy();
    // Очищаем cookies, чтобы не путались
    foreach ($fields as $name => $config) {
        setcookie($name . '_value', '', time() - 3600, '/');
    }
    setcookie('save_success', '', time() - 3600, '/');
    header('Location: index.php');
    exit();
}

// Обработка авторизации
if (isset($_POST['login_submit']) && !empty($_POST['login']) && !empty($_POST['password'])) {
    $user = getUserByLogin($pdo, $_POST['login']);
    if ($user && password_verify($_POST['password'], $user['password_hash'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_login'] = $user['login'];
        $_SESSION['user_email'] = $user['email'];
        // После входа очищаем куки, чтобы не было конфликта
        foreach ($fields as $name => $config) {
            setcookie($name . '_value', '', time() - 3600, '/');
        }
        header('Location: index.php');
        exit();
    } else {
        $login_error = 'Неверный логин или пароль';
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    
    $messages = array();
    $errors = array();
    $values = array();
    $generated_credentials = null;
    
    // Проверяем, есть ли сообщение об успешной регистрации с паролем
    if (isset($_SESSION['generated_login']) && isset($_SESSION['generated_password'])) {
        $generated_credentials = [
            'login' => $_SESSION['generated_login'],
            'password' => $_SESSION['generated_password']
        ];
        unset($_SESSION['generated_login']);
        unset($_SESSION['generated_password']);
        $messages[] = '<div class="message success">✨ Регистрация успешна! ✨<br>
                       <strong>Логин:</strong> ' . htmlspecialchars($generated_credentials['login']) . '<br>
                       <strong>Пароль:</strong> ' . htmlspecialchars($generated_credentials['password']) . '<br>
                       <span style="font-size: 12px;">Сохраните эти данные для входа!</span></div>';
    }
    
    // Если пользователь авторизован, загружаем его данные из БД
    if (isset($_SESSION['user_id'])) {
        $user_data = getUserFormData($pdo, $_SESSION['user_id']);
        if ($user_data) {
            $values['fio'] = $user_data['fio'];
            $values['email'] = $_SESSION['user_email'];
            $values['phone'] = $user_data['phone'];
            $values['birthdate'] = $user_data['birthdate'];
            $values['gender'] = $user_data['gender'];
            $values['languages'] = $user_data['languages'];
            $values['biography'] = $user_data['biography'];
            $values['contract_accepted'] = $user_data['contract_accepted'];
        } else {
            // Если данных ещё нет, пробуем восстановить из cookies
            foreach ($fields as $name => $config) {
                if (isset($_COOKIE[$name . '_value'])) {
                    $values[$name] = $_COOKIE[$name . '_value'];
                }
            }
            if (isset($values['languages']) && !empty($values['languages'])) {
                $values['languages'] = explode(',', $values['languages']);
            }
            // Устанавливаем email из сессии
            $values['email'] = $_SESSION['user_email'];
        }
    } else {
        // Неавторизованный пользователь - загружаем из cookies (как в задании 4)
        if (isset($_COOKIE['save_success']) && $_COOKIE['save_success'] == '1') {
            setcookie('save_success', '', time() - 3600, '/');
            $messages[] = '<div class="message success">Форма успешно отправлена! Данные сохранены на год.</div>';
        }
        
        foreach ($fields as $name => $config) {
            $errors[$name] = isset($_COOKIE[$name . '_error']) && $_COOKIE[$name . '_error'] == '1';
            
            if ($errors[$name]) {
                setcookie($name . '_error', '', time() - 3600, '/');
                
                if ($name == 'languages') {
                    $messages[] = '<div class="message error">Ошибка в поле "' . $config['label'] . '": обязательно для заполнения. Допустимые языки: ' . $config['allowed'] . '.</div>';
                } elseif ($name == 'contract_accepted') {
                    $messages[] = '<div class="message error">Ошибка: необходимо подтвердить ознакомление с контрактом.</div>';
                } elseif ($name == 'biography') {
                    $messages[] = '<div class="message error">Ошибка в поле "' . $config['label'] . '": биография не должна превышать ' . $config['max_length'] . ' символов.</div>';
                } else {
                    $messages[] = '<div class="message error">Ошибка в поле "' . $config['label'] . '": ' . ($config['required'] ? 'обязательное поле. ' : '') . 'Допустимо: ' . ($config['allowed'] ?? '') . '.</div>';
                }
            }
        }
        
        foreach ($fields as $name => $config) {
            if (isset($_COOKIE[$name . '_value'])) {
                $values[$name] = $_COOKIE[$name . '_value'];
            } else {
                $values[$name] = '';
            }
        }
        
        if (isset($values['languages']) && !empty($values['languages'])) {
            $values['languages'] = explode(',', $values['languages']);
        } else {
            $values['languages'] = [];
        }
    }
    
    include 'form.php';
    
} else { // POST
    
    $has_errors = false;
    
    // Валидация (такая же как в задании 4, но с проверкой unique email для новых пользователей)
    foreach ($fields as $name => $config) {
        
        if ($name == 'languages') {
            $value = $_POST['languages'] ?? [];
            if (empty($value)) {
                setcookie('languages_error', '1', 0, '/');
                $has_errors = true;
            } else {
                $valid = true;
                foreach ($value as $lang) {
                    if (!in_array($lang, $allowed_languages)) {
                        $valid = false;
                        break;
                    }
                }
                if (!$valid) {
                    setcookie('languages_error', '1', 0, '/');
                    $has_errors = true;
                }
            }
            $langs_str = is_array($value) ? implode(',', $value) : '';
            setcookie('languages_value', $langs_str, time() + 30 * 24 * 60 * 60, '/');
            continue;
        }
        
        if ($name == 'contract_accepted') {
            $value = isset($_POST['contract_accepted']) && $_POST['contract_accepted'] == '1' ? '1' : '0';
            if ($value != '1') {
                setcookie('contract_accepted_error', '1', 0, '/');
                $has_errors = true;
            }
            setcookie('contract_accepted_value', $value, time() + 30 * 24 * 60 * 60, '/');
            continue;
        }
        
        $value = isset($_POST[$name]) ? trim($_POST[$name]) : '';
        
        if ($name == 'biography') {
            if (!empty($value) && strlen($value) > 5000) {
                setcookie('biography_error', '1', 0, '/');
                $has_errors = true;
            }
            setcookie('biography_value', $value, time() + 30 * 24 * 60 * 60, '/');
            continue;
        }
        
        if ($config['required'] && empty($value)) {
            setcookie($name . '_error', '1', 0, '/');
            $has_errors = true;
        }
        
        if (!empty($value) && isset($config['pattern']) && !preg_match($config['pattern'], $value)) {
            setcookie($name . '_error', '1', 0, '/');
            $has_errors = true;
        }
        
        setcookie($name . '_value', $value, time() + 30 * 24 * 60 * 60, '/');
    }
    
    if ($has_errors) {
        header('Location: index.php');
        exit();
    }
    
    // После успешной валидации
    $email = trim($_POST['email']);
    $user_id = null;
    $is_new_user = false;
    
    // Если пользователь авторизован
    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
        // Обновляем email в users если изменился
        if ($email != $_SESSION['user_email']) {
            updateUserData($pdo, $user_id, $email);
            $_SESSION['user_email'] = $email;
        }
    } else {
        // Неавторизованный - проверяем, существует ли пользователь с таким email
        $existing_user = getUserByEmail($pdo, $email);
        
        if ($existing_user) {
            // Пользователь с таким email уже есть - ошибка, предлагаем войти
            $messages[] = '<div class="message error">Пользователь с email ' . htmlspecialchars($email) . ' уже зарегистрирован. Пожалуйста, <a href="#" onclick="document.querySelector(\'input[name=\\\'login\\\']\')?.focus(); return false;">войдите</a> с полученными ранее логином и паролем.</div>';
            foreach ($fields as $name => $config) {
                setcookie($name . '_error', '1', 0, '/');
            }
            header('Location: index.php');
            exit();
        }
        
        // Новый пользователь - генерируем логин и пароль
        $login = generateLogin($email);
        $plain_password = generatePassword();
        $password_hash = password_hash($plain_password, PASSWORD_DEFAULT);
        
        $user_id = createUser($pdo, $email, $login, $password_hash);
        $is_new_user = true;
        
        // Сохраняем сгенерированные данные в сессию для показа
        $_SESSION['generated_login'] = $login;
        $_SESSION['generated_password'] = $plain_password;
    }
    
    // Сохраняем данные формы
    $form_data = [
        'fio' => trim($_POST['fio']),
        'email' => $email,
        'phone' => trim($_POST['phone']),
        'birthdate' => trim($_POST['birthdate']),
        'gender' => $_POST['gender'],
        'languages' => $_POST['languages'] ?? [],
        'biography' => trim($_POST['biography']),
        'contract_accepted' => isset($_POST['contract_accepted']) && $_POST['contract_accepted'] == '1' ? 1 : 0
    ];
    
    saveFormData($pdo, $user_id, $form_data);
    
    // Сохраняем cookies для неавторизованных (сохраняем логику Задания 4)
    foreach ($fields as $name => $config) {
        if ($name == 'languages') {
            $langs = $_POST['languages'] ?? [];
            if (!empty($langs)) {
                setcookie('languages_value', implode(',', $langs), time() + 365 * 24 * 60 * 60, '/');
            }
        } elseif ($name == 'contract_accepted') {
            $val = isset($_POST['contract_accepted']) && $_POST['contract_accepted'] == '1' ? '1' : '0';
            setcookie('contract_accepted_value', $val, time() + 365 * 24 * 60 * 60, '/');
        } elseif ($name == 'biography') {
            if (isset($_POST[$name])) {
                setcookie('biography_value', $_POST[$name], time() + 365 * 24 * 60 * 60, '/');
            }
        } else {
            if (isset($_POST[$name]) && !empty($_POST[$name])) {
                setcookie($name . '_value', $_POST[$name], time() + 365 * 24 * 60 * 60, '/');
            }
        }
        // Очищаем ошибки
        setcookie($name . '_error', '', time() - 3600, '/');
    }
    
    setcookie('save_success', '1', time() + 365 * 24 * 60 * 60, '/');
    
    // Если это новый пользователь, автоматически авторизуем его
    if ($is_new_user) {
        $_SESSION['user_id'] = $user_id;
        $_SESSION['user_login'] = $login;
        $_SESSION['user_email'] = $email;
        // Очищаем cookies при авторизации
        foreach ($fields as $name => $config) {
            setcookie($name . '_value', '', time() - 3600, '/');
        }
    }
    
    header('Location: index.php');
    exit();
}
?>