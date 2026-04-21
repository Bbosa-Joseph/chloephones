<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Password Reset</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 0; }
        .wrapper { max-width: 560px; margin: 40px auto; background: #fff; border-radius: 6px; overflow: hidden; }
        .header  { background: #1e2a3a; padding: 28px 32px; }
        .header h1 { color: #fff; margin: 0; font-size: 22px; }
        .body    { padding: 32px; color: #333; line-height: 1.6; }
        .btn     { display: inline-block; margin: 24px 0; padding: 12px 28px;
                   background: #e74c3c; color: #fff; text-decoration: none;
                   border-radius: 4px; font-weight: bold; }
        .footer  { padding: 16px 32px; background: #f8f8f8; font-size: 12px; color: #888; }
    </style>
</head>
<body>
<div class="wrapper">
    <div class="header"><h1>ChloePhones</h1></div>
    <div class="body">
        <?php $firstName = $user['first_name'] ?? $user['firstname'] ?? 'there'; ?>
        <p>Hi <?= esc($firstName) ?>,</p>
        <p>We received a request to reset the password for your account (<strong><?= esc($user['email']) ?></strong>).</p>
        <p>Click the button below to choose a new password. This link expires in <strong><?= (int)$expiry_min ?> minutes</strong>.</p>
        <a href="<?= esc($reset_link) ?>" class="btn">Reset My Password</a>
        <p>If you did not request a password reset, you can safely ignore this email — your password will not change.</p>
        <p>For security, never share this link with anyone.</p>
    </div>
    <div class="footer">
        If the button does not work, copy and paste this URL into your browser:<br>
        <a href="<?= esc($reset_link) ?>"><?= esc($reset_link) ?></a>
    </div>
</div>
</body>
</html>
