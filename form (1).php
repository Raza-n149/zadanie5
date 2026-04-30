<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Задание 4 - Валидация формы</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', 'Roboto', 'Helvetica Neue', sans-serif;
            background: linear-gradient(135deg, #f5b0b0 0%, #f38181 50%, #e96479 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .form-card {
            background: rgba(255, 255, 255, 0.98);
            border-radius: 24px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            width: 100%;
            max-width: 600px;
            overflow: hidden;
            transition: transform 0.3s ease;
        }

        .form-card:hover {
            transform: translateY(-5px);
        }

        .form-header {
            background: linear-gradient(135deg, #f38181 0%, #e96479 100%);
            color: white;
            padding: 30px 30px 25px;
            text-align: center;
        }

        .form-header h1 {
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .form-header p {
            font-size: 14px;
            opacity: 0.9;
        }

        .form-body {
            padding: 30px;
        }

        .message {
            padding: 14px 18px;
            margin-bottom: 20px;
            border-radius: 12px;
            font-size: 14px;
            animation: slideIn 0.3s ease;
        }

        @keyframes slideIn {
            from {
                transform: translateY(-10px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .message.success {
            background: linear-gradient(135deg, #d4fc79 0%, #96e6a1 100%);
            color: #1a5b2a;
            border-left: 4px solid #2ecc71;
        }

        .message.error {
            background: #fff5f5;
            color: #c0392b;
            border-left: 4px solid #e74c3c;
        }

        .form-group {
            margin-bottom: 22px;
        }

        label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            color: #2c3e50;
            font-size: 14px;
        }

        .required {
            color: #e96479;
            margin-left: 4px;
        }

        input:not([type="radio"]):not([type="checkbox"]),
        select,
        textarea {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #ffe0e0;
            border-radius: 12px;
            font-size: 14px;
            transition: all 0.2s ease;
            background: white;
            font-family: inherit;
        }

        input:focus,
        select:focus,
        textarea:focus {
            outline: none;
            border-color: #f38181;
            box-shadow: 0 0 0 3px rgba(243, 129, 129, 0.15);
        }

        .error-field {
            border: 2px solid #e74c3c !important;
            background-color: #fff0f0 !important;
        }

        .error-field:focus {
            box-shadow: 0 0 0 3px rgba(231, 76, 60, 0.1) !important;
        }

        .help-text {
            font-size: 11px;
            color: #d4a5a5;
            margin-top: 6px;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .help-text::before {
            content: "🌸";
            font-size: 11px;
        }

        .radio-group {
            display: flex;
            gap: 24px;
            padding: 8px 0;
            flex-wrap: wrap;
        }

        .radio-option {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            padding: 8px 16px;
            background: #fff5f5;
            border-radius: 40px;
            transition: all 0.2s;
        }

        .radio-option:hover {
            background: #ffebeb;
        }

        .radio-option input[type="radio"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
            accent-color: #f38181;
        }

        .radio-option label {
            margin: 0;
            cursor: pointer;
            font-weight: normal;
        }

        select[multiple] {
            min-height: 150px;
        }

        .checkbox-label {
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            font-weight: normal;
        }

        .checkbox-label input {
            width: 18px;
            height: 18px;
            cursor: pointer;
            accent-color: #f38181;
        }

        .submit-btn {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #f38181 0%, #e96479 100%);
            color: white;
            border: none;
            border-radius: 40px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
            font-family: inherit;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px -5px rgba(243, 129, 129, 0.5);
        }

        .submit-btn:active {
            transform: translateY(0);
        }
    </style>
</head>
<body>
    <div class="form-card">
        <div class="form-header">
            <h1>Регистрационная форма</h1>
            <p>Заполните все обязательные поля</p>
        </div>
        
        <div class="form-body">
            <?php if (!empty($messages)): ?>
                <div id="messages">
                    <?php foreach ($messages as $msg): ?>
                        <?php echo $msg; ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <form action="" method="POST">
                <div class="form-group">
                    <label>ФИО <span class="required">*</span></label>
                    <input type="text" name="fio" value="<?php echo htmlspecialchars($values['fio'] ?? ''); ?>"
                           <?php echo !empty($errors['fio']) ? 'class="error-field"' : ''; ?>
                           placeholder="Иванов Иван Иванович" autocomplete="off">
                    <div class="help-text">Только буквы, пробелы и дефисы</div>
                </div>
                
                <div class="form-group">
                    <label>Email <span class="required">*</span></label>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($values['email'] ?? ''); ?>"
                           <?php echo !empty($errors['email']) ? 'class="error-field"' : ''; ?>
                           placeholder="user@example.com" autocomplete="off">
                    <div class="help-text">Формат: name@domain.com</div>
                </div>
                
                <div class="form-group">
                    <label>Телефон <span class="required">*</span></label>
                    <input type="tel" name="phone" value="<?php echo htmlspecialchars($values['phone'] ?? ''); ?>"
                           <?php echo !empty($errors['phone']) ? 'class="error-field"' : ''; ?>
                           placeholder="+7 (123) 456-78-90" autocomplete="off">
                    <div class="help-text">Цифры, +, -, (, ), пробелы</div>
                </div>
                
                <div class="form-group">
                    <label>Дата рождения <span class="required">*</span></label>
                    <input type="text" name="birthdate" value="<?php echo htmlspecialchars($values['birthdate'] ?? ''); ?>"
                           <?php echo !empty($errors['birthdate']) ? 'class="error-field"' : ''; ?>
                           placeholder="15.01.1990" autocomplete="off">
                    <div class="help-text">Формат: ДД.ММ.ГГГГ (день.месяц.год), например 15.01.1990</div>
                </div>
                
                <div class="form-group">
                    <label>Пол <span class="required">*</span></label>
                    <div class="radio-group">
                        <label class="radio-option">
                            <input type="radio" name="gender" value="male" <?php echo (($values['gender'] ?? '') == 'male') ? 'checked' : ''; ?>>
                            Мужской
                        </label>
                        <label class="radio-option">
                            <input type="radio" name="gender" value="female" <?php echo (($values['gender'] ?? '') == 'female') ? 'checked' : ''; ?>>
                            Женский
                        </label>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Любимые языки программирования <span class="required">*</span></label>
                    <select name="languages[]" multiple="multiple" <?php echo !empty($errors['languages']) ? 'class="error-field"' : ''; ?>>
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
                    <div class="help-text">Выберите один или несколько языков (Ctrl+Click для множественного выбора)</div>
                </div>
                
                <div class="form-group">
                    <label>Биография</label>
                    <textarea name="biography" rows="4" <?php echo !empty($errors['biography']) ? 'class="error-field"' : ''; ?>
                              placeholder="Расскажите немного о себе..."><?php echo htmlspecialchars($values['biography'] ?? ''); ?></textarea>
                    <div class="help-text">Необязательное поле. Максимум 5000 символов.</div>
                </div>
                
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="contract_accepted" value="1"
                               <?php echo (($values['contract_accepted'] ?? '') == '1') ? 'checked' : ''; ?>>
                        Я ознакомлен(а) с условиями контракта <span class="required">*</span>
                    </label>
                </div>
                
                <button type="submit" class="submit-btn">Отправить форму</button>
            </form>
        </div>
    </div>
</body>
</html>
