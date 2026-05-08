<?php
// db.php - подключение к БД и функции

$host = 'localhost';
$dbname = 'your_database'; // замените на вашу БД
$username = 'your_user';     // замените на вашего пользователя
$password = 'your_password'; // замените на ваш пароль

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Ошибка подключения к БД: " . $e->getMessage());
}

// Создание таблиц, если их нет
function initDatabase($pdo) {
    // Таблица пользователей
    $sql = "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        login VARCHAR(100) UNIQUE NOT NULL,
        password_hash VARCHAR(255) NOT NULL,
        email VARCHAR(255) UNIQUE NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $pdo->exec($sql);
    
    // Таблица данных формы (связь с пользователем)
    $sql = "CREATE TABLE IF NOT EXISTS form_data (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT UNIQUE NOT NULL,
        fio VARCHAR(255) NOT NULL,
        phone VARCHAR(50) NOT NULL,
        birthdate VARCHAR(20) NOT NULL,
        gender VARCHAR(10) NOT NULL,
        languages TEXT NOT NULL,
        biography TEXT,
        contract_accepted TINYINT(1) NOT NULL,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )";
    $pdo->exec($sql);
}

initDatabase($pdo);

// Генерация случайного пароля
function generatePassword($length = 10) {
    $chars = 'abcdefghijkmnopqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789!@#$%';
    return substr(str_shuffle($chars), 0, $length);
}

// Генерация логина на основе email
function generateLogin($email) {
    $base = preg_replace('/[^a-zA-Z0-9]/', '_', explode('@', $email)[0]);
    $base = substr($base, 0, 20);
    return $base . '_' . rand(100, 999);
}

// Сохранение или обновление данных пользователя
function saveFormData($pdo, $user_id, $data) {
    $sql = "INSERT INTO form_data (user_id, fio, phone, birthdate, gender, languages, biography, contract_accepted) 
            VALUES (:user_id, :fio, :phone, :birthdate, :gender, :languages, :biography, :contract_accepted)
            ON DUPLICATE KEY UPDATE 
            fio = VALUES(fio), phone = VALUES(phone), birthdate = VALUES(birthdate), 
            gender = VALUES(gender), languages = VALUES(languages), 
            biography = VALUES(biography), contract_accepted = VALUES(contract_accepted)";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':user_id' => $user_id,
        ':fio' => $data['fio'],
        ':phone' => $data['phone'],
        ':birthdate' => $data['birthdate'],
        ':gender' => $data['gender'],
        ':languages' => is_array($data['languages']) ? implode(',', $data['languages']) : $data['languages'],
        ':biography' => $data['biography'],
        ':contract_accepted' => $data['contract_accepted']
    ]);
}

// Получение данных пользователя по user_id
function getUserFormData($pdo, $user_id) {
    $stmt = $pdo->prepare("SELECT * FROM form_data WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $result = $stmt->fetch();
    
    if ($result && $result['languages']) {
        $result['languages'] = explode(',', $result['languages']);
    }
    
    return $result;
}

// Получение пользователя по логину
function getUserByLogin($pdo, $login) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE login = ?");
    $stmt->execute([$login]);
    return $stmt->fetch();
}

// Получение пользователя по email
function getUserByEmail($pdo, $email) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    return $stmt->fetch();
}

// Создание нового пользователя
function createUser($pdo, $email, $login, $password_hash) {
    $stmt = $pdo->prepare("INSERT INTO users (email, login, password_hash) VALUES (?, ?, ?)");
    $stmt->execute([$email, $login, $password_hash]);
    return $pdo->lastInsertId();
}

// Обновление данных пользователя (не меняем логин/пароль)
function updateUserData($pdo, $user_id, $email) {
    // только email можно обновить, логин и пароль не меняем
    $stmt = $pdo->prepare("UPDATE users SET email = ? WHERE id = ?");
    $stmt->execute([$email, $user_id]);
}
?>