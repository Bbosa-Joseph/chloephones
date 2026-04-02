<!DOCTYPE html>
<!-- Auto-deploy test -->
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Chloe Phone Center - Login</title>
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
  <link rel="stylesheet" href="<?php echo base_url('assets/bower_components/bootstrap/dist/css/bootstrap.min.css')?>">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.5.0/css/font-awesome.min.css">
  <style>
    html,
    body {
      min-height: 100%;
    }

    body {
      margin: 0;
      font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
      background:
        radial-gradient(circle at top left, rgba(79, 70, 229, 0.16), transparent 34%),
        radial-gradient(circle at bottom right, rgba(255, 255, 255, 0.06), transparent 28%),
        linear-gradient(135deg, #141925 0%, #1c2333 45%, #101620 100%);
      color: #111827;
    }

    .login-shell {
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 28px 14px;
    }

    .login-panel {
      width: 100%;
      max-width: 1080px;
      background: rgba(255, 255, 255, 0.96);
      border-radius: 24px;
      overflow: hidden;
      box-shadow: 0 24px 60px rgba(0, 0, 0, 0.28);
      position: relative;
    }

    .login-panel:before {
      content: "";
      position: absolute;
      inset: 0;
      border: 1px solid rgba(255, 255, 255, 0.16);
      border-radius: 24px;
      pointer-events: none;
    }

    .login-layout {
      display: flex;
      flex-wrap: wrap;
      min-height: 640px;
    }

    .login-branding,
    .login-form-panel {
      position: relative;
      min-height: 1px;
    }

    .login-branding {
      width: 50%;
      padding: 54px 48px;
      color: #fff;
      background:
        linear-gradient(160deg, rgba(55, 48, 163, 0.96) 0%, rgba(79, 70, 229, 0.92) 55%, rgba(129, 120, 248, 0.88) 100%),
        linear-gradient(135deg, #3730a3 0%, #4f46e5 100%);
      overflow: hidden;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
    }

    .login-branding:before,
    .login-branding:after {
      content: "";
      position: absolute;
      border-radius: 50%;
      background: rgba(255, 255, 255, 0.08);
    }

    .login-branding:before {
      width: 260px;
      height: 260px;
      top: -90px;
      right: -70px;
    }

    .login-branding:after {
      width: 190px;
      height: 190px;
      bottom: 28px;
      left: -70px;
    }

    .login-branding-inner,
    .login-branding-footer {
      position: relative;
      z-index: 2;
    }

    .login-brand {
      display: inline-flex;
      align-items: center;
      gap: 14px;
      margin-bottom: 34px;
    }

    .brand-logo {
      width: 64px;
      height: 64px;
      border-radius: 18px;
      background: rgba(255, 255, 255, 0.14);
      display: inline-flex;
      align-items: center;
      justify-content: center;
      padding: 10px;
      box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.18);
    }

    .brand-logo img {
      max-width: 100%;
      max-height: 100%;
      object-fit: contain;
    }

    .brand-title-wrap {
      line-height: 1.2;
    }

    .brand-title {
      display: block;
      font-size: 28px;
      font-weight: 700;
      letter-spacing: 0.3px;
      color: #fff;
    }

    .brand-subtitle {
      display: block;
      margin-top: 4px;
      font-size: 12px;
      text-transform: uppercase;
      letter-spacing: 2px;
      color: rgba(255, 255, 255, 0.8);
    }

    .login-branding h1 {
      margin: 0 0 16px;
      font-size: 38px;
      line-height: 1.14;
      font-weight: 700;
      color: #fff;
    }

    .login-branding p {
      margin: 0;
      font-size: 16px;
      line-height: 1.8;
      color: rgba(255, 255, 255, 0.88);
      max-width: 430px;
    }

    .login-feature-list {
      list-style: none;
      padding: 0;
      margin: 34px 0 0;
      max-width: 430px;
    }

    .login-feature-list li {
      display: flex;
      align-items: center;
      gap: 12px;
      margin-bottom: 16px;
      font-size: 15px;
      color: rgba(255, 255, 255, 0.92);
    }

    .login-feature-list i {
      width: 30px;
      height: 30px;
      border-radius: 50%;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      background: rgba(255, 255, 255, 0.14);
      font-size: 14px;
    }

    .login-branding-footer {
      margin-top: 40px;
      font-size: 13px;
      color: rgba(255, 255, 255, 0.72);
    }

    .login-form-panel {
      width: 50%;
      background: #fff;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 54px 48px;
    }

    .login-form-wrap {
      width: 100%;
      max-width: 390px;
    }

    .mobile-brand {
      display: none;
      align-items: center;
      gap: 12px;
      margin-bottom: 28px;
    }

    .mobile-brand .brand-logo {
      width: 52px;
      height: 52px;
      background: linear-gradient(135deg, #3730a3 0%, #4f46e5 100%);
      box-shadow: none;
    }

    .mobile-brand .brand-title {
      color: #111827;
      font-size: 22px;
    }

    .mobile-brand .brand-subtitle {
      color: #6b7280;
      letter-spacing: 1.6px;
    }

    .login-form-header {
      margin-bottom: 28px;
    }

    .login-form-header .eyebrow {
      display: inline-block;
      margin-bottom: 10px;
      font-size: 12px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 1.8px;
      color: #4f46e5;
    }

    .login-form-header h2 {
      margin: 0 0 10px;
      font-size: 30px;
      line-height: 1.2;
      font-weight: 700;
      color: #111827;
    }

    .login-form-header p {
      margin: 0;
      font-size: 14px;
      line-height: 1.75;
      color: #6b7280;
    }

    .login-alert {
      border: none;
      border-left: 4px solid #4f46e5;
      border-radius: 12px;
      background: #eef2ff;
      color: #3730a3;
      padding: 14px 16px;
      margin-bottom: 22px;
    }

    .login-alert i {
      margin-right: 8px;
    }

    .form-group {
      margin-bottom: 20px;
    }

    .login-label {
      display: block;
      margin-bottom: 8px;
      font-size: 13px;
      font-weight: 700;
      letter-spacing: 0.2px;
      color: #374151;
    }

    .login-input-wrap {
      position: relative;
    }

    .login-input-wrap .input-icon {
      position: absolute;
      top: 50%;
      left: 16px;
      transform: translateY(-50%);
      color: #9ca3af;
      font-size: 15px;
      z-index: 2;
    }

    .login-input {
      width: 100%;
      height: 52px;
      border: 1px solid #dbe1ea;
      border-radius: 14px;
      background: #f9fafb;
      color: #111827;
      font-size: 15px;
      padding: 0 16px 0 44px;
      box-shadow: none;
      transition: all 0.2s ease;
    }

    .login-input:focus {
      border-color: #4f46e5;
      background: #fff;
      outline: none;
      box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.12);
    }

    .login-input::-webkit-input-placeholder {
      color: #9ca3af;
    }

    .login-input:-ms-input-placeholder {
      color: #9ca3af;
    }

    .login-input::placeholder {
      color: #9ca3af;
    }

    .btn-login {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
      width: 100%;
      height: 52px;
      margin-top: 10px;
      border: none;
      border-radius: 14px;
      background: linear-gradient(135deg, #3730a3 0%, #4f46e5 55%, #7c3aed 100%);
      color: #fff;
      font-size: 15px;
      font-weight: 700;
      letter-spacing: 0.3px;
      box-shadow: 0 16px 30px rgba(79, 70, 229, 0.25);
      transition: transform 0.2s ease, box-shadow 0.2s ease, opacity 0.2s ease;
    }

    .btn-login:hover,
    .btn-login:focus {
      color: #fff;
      transform: translateY(-1px);
      box-shadow: 0 20px 34px rgba(79, 70, 229, 0.3);
      opacity: 0.98;
      outline: none;
    }

    .login-help {
      margin-top: 22px;
      text-align: center;
      font-size: 13px;
      color: #9ca3af;
      line-height: 1.7;
    }

    @media (max-width: 991px) {
      .login-branding,
      .login-form-panel {
        width: 100%;
      }

      .login-branding {
        display: none;
      }

      .login-form-panel {
        padding: 40px 28px;
      }

      .mobile-brand {
        display: inline-flex;
      }

      .login-layout {
        min-height: auto;
      }
    }

    @media (max-width: 575px) {
      .login-shell {
        padding: 18px 10px;
      }

      .login-panel,
      .login-panel:before {
        border-radius: 18px;
      }

      .login-form-panel {
        padding: 30px 20px;
      }

      .login-form-header h2 {
        font-size: 26px;
      }

      .mobile-brand .brand-title {
        font-size: 20px;
      }
    }
  </style>
</head>
<body>
  <main class="login-shell">
    <section class="login-panel">
      <div class="login-layout">
        <aside class="login-branding">
          <div class="login-branding-inner">
            <div class="login-brand">
              <div class="brand-logo">
                <img src="<?php echo base_url('assets/images/product_image/chloe.png'); ?>" alt="Chloe Logo">
              </div>
              <span class="brand-title-wrap">
                <span class="brand-title">Chloe Phone Center</span>
                <span class="brand-subtitle">Inventory Workspace</span>
              </span>
            </div>

            <h1>Manage stock with a smarter, more focused workflow.</h1>
            <p>Access your inventory dashboard, follow sales activity, and keep store operations organized from one secure workspace.</p>

            <ul class="login-feature-list">
              <li><i class="fa fa-cubes"></i><span>Review products, stock movement, and item availability</span></li>
              <li><i class="fa fa-shopping-cart"></i><span>Track sales activity and daily order performance</span></li>
              <li><i class="fa fa-shield"></i><span>Sign in to a secure role-based management system</span></li>
            </ul>
          </div>

          <div class="login-branding-footer">
            © <?php echo date('Y'); ?> Chloe Phone Center
          </div>
        </aside>

        <section class="login-form-panel">
          <div class="login-form-wrap">
            <div class="mobile-brand">
              <div class="brand-logo">
                <img src="<?php echo base_url('assets/images/product_image/chloe.png'); ?>" alt="Chloe Logo">
              </div>
              <span class="brand-title-wrap">
                <span class="brand-title">Chloe Phone Center</span>
                <span class="brand-subtitle">Inventory Workspace</span>
              </span>
            </div>

            <div class="login-form-header">
              <span class="eyebrow">Secure access</span>
              <h2>Welcome back</h2>
              <p>Sign in to continue to your account and manage inventory operations with confidence.</p>
            </div>

            <?php if(isset($errors) && $errors): ?>
              <div class="alert login-alert" role="alert">
                <i class="fa fa-exclamation-circle"></i><?php echo htmlspecialchars($errors); ?>
              </div>
            <?php endif; ?>

            <form action="<?php echo base_url('auth/login') ?>" method="post" autocomplete="off">
              <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">
              <div class="form-group">
                <label class="login-label" for="email">Email</label>
                <div class="login-input-wrap">
                  <i class="fa fa-user input-icon"></i>
                  <input type="email" id="email" name="email" class="form-control login-input" placeholder="Enter your email" required autocomplete="username">
                </div>
              </div>

              <div class="form-group">
                <label class="login-label" for="password">Password</label>
                <div class="login-input-wrap">
                  <i class="fa fa-lock input-icon"></i>
                  <input type="password" id="password" name="password" class="form-control login-input" placeholder="Enter your password" required autocomplete="current-password">
                </div>
              </div>

              <button type="submit" class="btn btn-login">
                <span>Sign In</span>
                <i class="fa fa-arrow-right"></i>
              </button>
            </form>

            <p class="login-help">Contact your administrator if you need help accessing your account.</p>
          </div>
        </section>
      </div>
    </section>
  </main>
</body>
</html>