<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Задание 5 - Форма с авторизацией</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 40px 20px;
        }
        .container {
            max-width: 700px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 { font-size: 28px; margin-bottom: 10px; }
        .form-body { padding: 30px; }
        .form-group { margin-bottom: 20px; }
        label { display: block; font-weight: 600; margin-bottom: 8px; color: #333; }
        .required { color: #e74c3c; }
        input:not([type="radio"]):not([type="checkbox"]), select, textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 14px;
        }
        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: #667eea;
        }
        .error-field {
            border-color: #e74c3c !important;
            background-color: #fff5f5 !important;
        }
        .error-message {
            color: #e74c3c;
            font-size: 12px;
            margin-top: 5px;
        }
        .help-text { font-size: 12px; color: #888; margin-top: 5px; }
        .radio-group { display: flex; gap: 20px; padding: 8px 0; }
        .radio-group label { display: flex; align-items: center; gap: 8px; font-weight: normal; cursor: pointer; }
        .radio-group input { width: auto; }
        select[multiple] { height: 120px; }
        .message {
            padding: 12px 15px;
            margin-bottom: 20px;
            border-radius: 8px;
            font-size: 14px;
        }
        .message.success {
            background: #d4edda;
            color: #155724;
            border-left: 4px solid #28a745;
        }
        .message.error {
            background: #f8d7da;
            color: #721c24;
            border-left: 4px solid #e74c3c;
        }
        button {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 10px;
        }
        button:hover { transform: translateY(-2px); transition: transform 0.2s; }
        .login-form {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 25px;
        }
        .login-form h3 { margin-bottom: 15px; color: #333; }
        .inline-buttons { display: flex; gap: 10px; }
        .inline-buttons button { margin-top: 0; }
        .btn-secondary {
            background: #6c757d;
        }
        .edit-link {
            text-align: center;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }
        .edit-link a {
            color: #667eea;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📝 Задание 5 - Форма с авторизацией</h1>
            <p>Заполните анкету или войдите для редактирования</p>
        </div>
        <div class="form-body">
            
            <?php foreach ($messages as $msg): ?>
                <?php echo $msg; ?>
            <?php endforeach; ?>
            
            <?php if ($show_login_form && !isset($_SESSION['user_id'])): ?>
                <div class="login-form">
                    <h3>🔐 Вход для редактирования</h3>
                    <form method="POST">
                        <input type="hidden" name="login_action" value="1">
                        <div class="form-group">
                            <label>Логин</label>
                            <input type="text" name="login" required placeholder="Ваш логин">
                        </div>
                        <div class="form-group">
                            <label>Пароль</label>
                            <input type="password" name="password" required placeholder="Ваш пароль">
                        </div>
                        <button type="submit">Войти</button>
                    </form>
                </div>
                <div class="edit-link">
                    <a href="index.php">← Вернуться к заполнению формы</a>
                </div>
            <?php else: ?>
                
                <?php if ($show_edit_form && $user_data): ?>
                    <div class="message success">
                        ✏️ Режим редактирования. Вы можете изменить свои данные.
                    </div>
                <?php endif; ?>
                
                <form method="POST">
                    <?php if ($show_edit_form && $user_data): ?>
                        <input type="hidden" name="application_id" value="<?php echo $_SESSION['application_id']; ?>">
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label>ФИО <span class="required">*</span></label>
                        <input type="text" name="fio" value="<?php echo htmlspecialchars($values['fio'] ?? ''); ?>"
                               class="<?php echo !empty($errors['fio']) ? 'error-field' : ''; ?>">
                        <div class="help-text">Только буквы, пробелы и дефисы</div>
                    </div>
                    
                    <div class="form-group">
                        <label>Email <span class="required">*</span></label>
                        <input type="email" name="email" value="<?php echo htmlspecialchars($values['email'] ?? ''); ?>"
                               class="<?php echo !empty($errors['email']) ? 'error-field' : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>Телефон <span class="required">*</span></label>
                        <input type="tel" name="phone" value="<?php echo htmlspecialchars($values['phone'] ?? ''); ?>"
                               class="<?php echo !empty($errors['phone']) ? 'error-field' : ''; ?>">
                        <div class="help-text">Цифры, +, -, (, ), пробелы</div>
                    </div>
                    
                    <div class="form-group">
                        <label>Дата рождения <span class="required">*</span></label>
                        <input type="text" name="birthdate" value="<?php echo htmlspecialchars($values['birthdate'] ?? ''); ?>"
                               class="<?php echo !empty($errors['birthdate']) ? 'error-field' : ''; ?>"
                               placeholder="15.01.1990">
                        <div class="help-text">Формат: ДД.ММ.ГГГГ</div>
                    </div>
                    
                    <div class="form-group">
                        <label>Пол <span class="required">*</span></label>
                        <div class="radio-group">
                            <label><input type="radio" name="gender" value="male" <?php echo (($values['gender'] ?? '') == 'male') ? 'checked' : ''; ?>> Мужской</label>
                            <label><input type="radio" name="gender" value="female" <?php echo (($values['gender'] ?? '') == 'female') ? 'checked' : ''; ?>> Женский</label>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Любимые языки программирования <span class="required">*</span></label>
                        <select name="languages[]" multiple class="<?php echo !empty($errors['languages']) ? 'error-field' : ''; ?>">
                            <option value="Pascal" <?php echo (isset($values['languages']) && in_array('Pascal', $values['languages'])) ? 'selected' : ''; ?>>Pascal</option>
                            <option value="C" <?php echo (isset($values['languages']) && in_array('C', $values['languages'])) ? 'selected' : ''; ?>>C</option>
                            <option value="C++" <?php echo (isset($values['languages']) && in_array('C++', $values['languages'])) ? 'selected' : ''; ?>>C++</option>
                            <option value="JavaScript" <?php echo (isset($values['languages']) && in_array('JavaScript', $values['languages'])) ? 'selected' : ''; ?>>JavaScript</option>
                            <option value="PHP" <?php echo (isset($values['languages']) && in_array('PHP', $values['languages'])) ? 'selected' : ''; ?>>PHP</option>
                            <option value="Python" <?php echo (isset($values['languages']) && in_array('Python', $values['languages'])) ? 'selected' : ''; ?>>Python</option>
                            <option value="Java" <?php echo (isset($values['languages']) && in_array('Java', $values['languages'])) ? 'selected' : ''; ?>>Java</option>
                            <option value="Haskell" <?php echo (isset($values['languages']) && in_array('Haskell', $values['languages'])) ? 'selected' : ''; ?>>Haskell</option>
                            <option value="Clojure" <?php echo (isset($values['languages']) && in_array('Clojure', $values['languages'])) ? 'selected' : ''; ?>>Clojure</option>
                            <option value="Prolog" <?php echo (isset($values['languages']) && in_array('Prolog', $values['languages'])) ? 'selected' : ''; ?>>Prolog</option>
                            <option value="Scala" <?php echo (isset($values['languages']) && in_array('Scala', $values['languages'])) ? 'selected' : ''; ?>>Scala</option>
                            <option value="Go" <?php echo (isset($values['languages']) && in_array('Go', $values['languages'])) ? 'selected' : ''; ?>>Go</option>
                        </select>
                        <div class="help-text">Ctrl+Click для выбора нескольких</div>
                    </div>
                    
                    <div class="form-group">
                        <label>Биография</label>
                        <textarea name="biography" rows="4"><?php echo htmlspecialchars($values['biography'] ?? ''); ?></textarea>
                        <div class="help-text">Необязательно</div>
                    </div>
                    
                    <div class="form-group">
                        <label>
                            <input type="checkbox" name="contract_accepted" value="1" <?php echo (($values['contract_accepted'] ?? 0) == 1) ? 'checked' : ''; ?>>
                            Я ознакомлен(а) с условиями контракта <span class="required">*</span>
                        </label>
                    </div>
                    
                    <button type="submit"><?php echo ($show_edit_form && $user_data) ? '✏️ Обновить данные' : '💾 Отправить форму'; ?></button>
                </form>
                
                <?php if (!$show_edit_form && !isset($_SESSION['user_id'])): ?>
                    <div class="edit-link">
                        <a href="?edit=1">✏️ Уже заполняли форму? Войдите, чтобы отредактировать данные</a>
                    </div>
                <?php endif; ?>
                
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
