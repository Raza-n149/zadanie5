<?php
header('Content-Type: text/html; charset=UTF-8');

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

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    
    $messages = array();
    $errors = array();
    $values = array();
    
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
    
    include 'form.php';
    
} else {
    
    $has_errors = false;
    
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
    
    foreach ($fields as $name => $config) {
        setcookie($name . '_error', '', time() - 3600, '/');
    }
    
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
    }
    
    setcookie('save_success', '1', time() + 365 * 24 * 60 * 60, '/');
    
    header('Location: index.php');
    exit();
}
?>
