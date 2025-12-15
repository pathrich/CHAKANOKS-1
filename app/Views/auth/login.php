<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SCMS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --brand-gold: #f59e0b;
            --brand-brown: #7c2d12;
            --ink: #111827;
            --muted: #6b7280;
            --surface: #ffffff;
            --border: rgba(124, 45, 18, 0.16);
            --shadow-sm: 0 2px 10px rgba(17, 24, 39, 0.10);
            --shadow-md: 0 18px 50px rgba(17, 24, 39, 0.18);
            --radius: 18px;
        }

        * { box-sizing: border-box; }

        body {
            font-family: 'Nunito', system-ui, -apple-system, Segoe UI, Roboto, sans-serif;
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            background:
                url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='140' height='140' viewBox='0 0 140 140'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg opacity='0.10'%3E%3Cpath fill='%237c2d12' d='M36 86c-10 0-18-8-18-18s8-18 18-18c5 0 9 2 12 5l26-26c2-2 5-2 7 0l10 10c2 2 2 5 0 7L65 72c3 3 5 7 5 12 0 10-8 18-18 18H36z'/%3E%3Cpath fill='%23f59e0b' d='M84 30c-3-3-3-8 0-11s8-3 11 0l7 7c3 3 3 8 0 11s-8 3-11 0l-7-7z'/%3E%3Ccircle cx='104' cy='44' r='5' fill='%23f59e0b'/%3E%3Ccircle cx='92' cy='16' r='5' fill='%23f59e0b'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E"),
                radial-gradient(1200px 600px at 10% 0%, rgba(245, 158, 11, 0.22), transparent 60%),
                radial-gradient(900px 500px at 100% 10%, rgba(124, 45, 18, 0.18), transparent 55%),
                #fffaf2;
            background-size: 140px 140px, auto, auto, auto;
            background-repeat: repeat, no-repeat, no-repeat, no-repeat;
            color: var(--ink);
        }

        .shell {
            width: min(1040px, 94vw);
            min-height: 560px;
            display: grid;
            grid-template-columns: 1.15fr 0.85fr;
            gap: 18px;
        }

        .panel {
            border-radius: var(--radius);
            overflow: hidden;
            background: rgba(255, 255, 255, 0.75);
            border: 1px solid rgba(124, 45, 18, 0.12);
            box-shadow: var(--shadow-md);
        }

        .brand {
            position: relative;
            padding: 42px;
            background:
                linear-gradient(135deg, rgba(180, 83, 9, 0.95), rgba(124, 45, 18, 0.95)),
                radial-gradient(900px 520px at 15% 10%, rgba(245, 158, 11, 0.65), transparent 55%);
            color: #fff;
        }

        .brand::before {
            content: "";
            position: absolute;
            inset: -40px -80px auto auto;
            width: 420px;
            height: 420px;
            background: radial-gradient(circle at 30% 30%, rgba(245, 158, 11, 0.85), rgba(245, 158, 11, 0.08) 62%, transparent 65%);
            transform: rotate(18deg);
        }

        .brand::after {
            content: "";
            position: absolute;
            inset: auto auto -120px -140px;
            width: 520px;
            height: 520px;
            background: radial-gradient(circle at 40% 40%, rgba(255, 255, 255, 0.18), rgba(255, 255, 255, 0.06) 58%, transparent 62%);
            transform: rotate(-12deg);
        }

        .brand-inner {
            position: relative;
            z-index: 1;
        }

        .logo {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 10px 14px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.14);
            border: 1px solid rgba(255, 255, 255, 0.18);
            backdrop-filter: blur(8px);
            font-weight: 800;
            letter-spacing: 0.3px;
        }

        .logo-badge {
            width: 34px;
            height: 34px;
            border-radius: 10px;
            background: linear-gradient(135deg, rgba(245, 158, 11, 1), rgba(251, 191, 36, 1));
            display: grid;
            place-items: center;
            color: #1f2937;
            box-shadow: 0 10px 20px rgba(0,0,0,0.18);
            font-weight: 900;
        }

        .brand h1 {
            margin: 18px 0 10px;
            font-size: 34px;
            line-height: 1.05;
            letter-spacing: 0.2px;
        }

        .brand p {
            margin: 0;
            color: rgba(255, 255, 255, 0.88);
            font-size: 15px;
            line-height: 1.55;
            max-width: 42ch;
        }

        .bullets {
            margin-top: 22px;
            display: grid;
            gap: 10px;
            color: rgba(255, 255, 255, 0.92);
            font-size: 14px;
        }

        .bullets div {
            display: flex;
            gap: 10px;
            align-items: flex-start;
        }

        .dot {
            width: 10px;
            height: 10px;
            margin-top: 5px;
            border-radius: 999px;
            background: rgba(245, 158, 11, 0.95);
            box-shadow: 0 0 0 4px rgba(245, 158, 11, 0.18);
            flex: 0 0 auto;
        }

        .form-wrap {
            padding: 34px;
            background: rgba(255, 255, 255, 0.86);
        }

        .form-card {
            background: var(--surface);
            border-radius: var(--radius);
            border: 1px solid var(--border);
            box-shadow: var(--shadow-sm);
            padding: 26px;
        }

        .form-card h2 {
            margin: 0 0 6px;
            font-size: 22px;
            letter-spacing: 0.2px;
        }

        .form-card .sub {
            margin: 0 0 16px;
            color: var(--muted);
            font-size: 14px;
        }

        label {
            display: block;
            margin: 12px 0 6px;
            color: #374151;
            font-size: 13px;
            font-weight: 700;
        }

        input {
            width: 100%;
            padding: 11px 12px;
            border: 1px solid rgba(124, 45, 18, 0.22);
            border-radius: 12px;
            font-size: 14px;
            outline: none;
            transition: box-shadow 0.15s ease, border-color 0.15s ease;
        }

        input:focus {
            border-color: rgba(245, 158, 11, 0.85);
            box-shadow: 0 0 0 0.2rem rgba(245, 158, 11, 0.22);
        }

        button {
            width: 100%;
            margin-top: 16px;
            padding: 11px 12px;
            border: 0;
            border-radius: 12px;
            background: linear-gradient(135deg, rgba(245, 158, 11, 1), rgba(180, 83, 9, 1));
            color: #111827;
            font-weight: 800;
            cursor: pointer;
            box-shadow: 0 14px 24px rgba(180, 83, 9, 0.25);
            transition: transform 0.12s ease, filter 0.12s ease;
        }

        button:hover {
            filter: brightness(0.99);
            transform: translateY(-1px);
        }

        .alert {
            margin-top: 10px;
            padding: 10px 12px;
            border-radius: 12px;
            background: rgba(220, 38, 38, 0.10);
            border: 1px solid rgba(220, 38, 38, 0.22);
            color: #991b1b;
            font-size: 13px;
        }

        .hint {
            margin-top: 14px;
            color: var(--muted);
            font-size: 13px;
        }

        @media (max-width: 900px) {
            .shell {
                grid-template-columns: 1fr;
                min-height: auto;
            }

            .brand {
                padding: 28px;
            }
        }
    </style>
    </head>
<body>
    <div class="shell">
        <div class="panel brand">
            <div class="brand-inner">
                <div class="logo">
                    <span class="logo-badge">C</span>
                    <span>CHAKANOKS SCMS</span>
                </div>
                <h1>Hot. Crispy. Controlled.</h1>
                <p>Manage inventory, purchase requests, suppliers, and deliveries across branches—fast and organized.</p>
                <div class="bullets">
                    <div><span class="dot"></span><span>Role-based access for Branch, Central, Supplier & Logistics</span></div>
                    <div><span class="dot"></span><span>Approval workflow from request → PO → delivery</span></div>
                    <div><span class="dot"></span><span>Activity logs & notifications for every action</span></div>
                </div>
            </div>
        </div>

        <div class="panel form-wrap">
            <div class="form-card">
                <h2>Sign in</h2>
                <p class="sub">Welcome back. Please enter your account details.</p>
                <?php if (session()->getFlashdata('error')): ?>
                    <div class="alert"><?= esc(session()->getFlashdata('error')) ?></div>
                <?php endif; ?>
                <form method="post" action="<?= site_url('login') ?>">
                    <label>Username</label>
                    <input type="text" name="username" required autocomplete="username">
                    <label>Password</label>
                    <input type="password" name="password" required autocomplete="current-password">
                    <button type="submit">Login</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>

