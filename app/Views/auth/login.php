<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SCMS</title>
    <style>
        body { font-family: system-ui, -apple-system, Segoe UI, Roboto, sans-serif; background: #f6f7fb; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; }
        .card { background: #fff; padding: 24px; border-radius: 12px; width: 100%; max-width: 380px; box-shadow: 0 10px 30px rgba(0,0,0,0.08); }
        h1 { margin: 0 0 8px 0; font-size: 22px; color: #333; }
        p { margin: 0 0 16px 0; color: #666; font-size: 14px; }
        label { display: block; margin: 12px 0 6px; color: #333; font-size: 14px; }
        input { width: 100%; padding: 10px 12px; border: 1px solid #e5e7eb; border-radius: 8px; font-size: 14px; }
        button { width: 100%; margin-top: 16px; padding: 10px 12px; border: 0; border-radius: 8px; background: #1976d2; color: #fff; font-weight: 600; cursor: pointer; }
        .alert { margin-top: 10px; color: #b91c1c; font-size: 13px; }
    </style>
    </head>
<body>
    <div class="card">
        <h1>Sign in to SCMS</h1>
        <p>Demo: admin / password123</p>
        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert"><?= esc(session()->getFlashdata('error')) ?></div>
        <?php endif; ?>
        <form method="post" action="<?= site_url('login') ?>">
            <label>Username</label>
            <input type="text" name="username" required>
            <label>Password</label>
            <input type="password" name="password" required>
            <button type="submit">Login</button>
        </form>
    </div>
</body>
</html>

