<?php if (!isset($_SESSION['user_id'])): ?>
<div class="login-section" style="margin-bottom: 20px; padding: 15px; background: #fff0f0; border-radius: 16px;">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
        <div>
            <strong style="color: #e96479;">🔐 Авторизация</strong>
            <span style="font-size: 12px; color: #999; margin-left: 8px;">Уже есть логин и пароль?</span>
        </div>
        <form method="POST" style="display: flex; gap: 10px; flex-wrap: wrap;">
            <input type="text" name="login" placeholder="Логин" required 
                   style="padding: 8px 12px; border: 1px solid #ffc0c0; border-radius: 8px;">
            <input type="password" name="password" placeholder="Пароль" required
                   style="padding: 8px 12px; border: 1px solid #ffc0c0; border-radius: 8px;">
            <button type="submit" name="login_submit" class="submit-btn" 
                    style="padding: 8px 20px; width: auto; margin: 0;">Войти</button>
        </form>
    </div>
    <?php if (isset($login_error)): ?>
        <div class="message error" style="margin-top: 12px; margin-bottom: 0;"><?php echo $login_error; ?></div>
    <?php endif; ?>
</div>
<?php else: ?>
<div class="user-info" style="margin-bottom: 20px; padding: 15px; background: #e8f8e8; border-radius: 16px; display: flex; justify-content: space-between; align-items: center;">
    <div>
        <strong>👤 Вы авторизованы как:</strong> <?php echo htmlspecialchars($_SESSION['user_login']); ?>
        <span style="margin-left: 12px; font-size: 12px; color: #666;">Вы можете редактировать свои данные</span>
    </div>
    <form method="POST">
        <button type="submit" name="logout_submit" class="submit-btn" 
                style="background: #999; padding: 8px 16px; width: auto; margin: 0;">Выйти</button>
    </form>
</div>
<?php endif; ?>