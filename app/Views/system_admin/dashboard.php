<?php
$title = $title ?? 'Settings & Users';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc($title) ?></title>
    <style>
        * { box-sizing:border-box; margin:0; padding:0; }
        body { font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background:#f4f6f8; color:#2c3e50; }
        .page-shell { max-width:1180px; margin:2rem auto; padding:0 1.5rem 2rem; }
        .page-header { margin-bottom:1.5rem; }
        .page-title { font-size:1.6rem; font-weight:600; margin-bottom:.25rem; }
        .page-subtitle { font-size:.9rem; color:#7f8c8d; }

        .tabs { display:flex; border-bottom:1px solid #e1e5eb; margin-bottom:1.25rem; }
        .tab { padding:.75rem 1.5rem; cursor:pointer; font-size:.95rem; border-bottom:3px solid transparent; color:#7f8c8d; text-decoration:none; }
        .tab.active { color:#16a085; border-bottom-color:#16a085; background:#ecfdf8; }

        .section { background:#ffffff; border-radius:10px; box-shadow:0 2px 8px rgba(15,23,42,0.04); padding:1.25rem 1.5rem; margin-bottom:1rem; }
        .section-title { font-size:1rem; font-weight:600; margin-bottom:.5rem; display:flex; align-items:center; gap:.5rem; }
        .section-subtitle { font-size:.85rem; color:#7f8c8d; margin-bottom:1rem; }

        .grid-2 { display:grid; grid-template-columns:repeat(auto-fit,minmax(260px,1fr)); gap:1rem; }
        .field-label { font-size:.85rem; color:#7f8c8d; margin-bottom:.25rem; }
        .field-input { padding:.55rem .7rem; border-radius:6px; border:1px solid #d0d7e2; width:100%; font-size:.9rem; }
        .field-input[disabled] { background:#f9fafb; color:#7f8c8d; }

        .link-card { border-radius:8px; border:1px solid #e1e5eb; padding:.9rem 1rem; display:flex; justify-content:space-between; align-items:center; text-decoration:none; color:#2c3e50; transition:background .15s, box-shadow .15s, border-color .15s; }
        .link-card:hover { background:#f8fffd; border-color:#16a085; box-shadow:0 2px 6px rgba(15,23,42,0.1); }
        .link-title { font-size:.95rem; font-weight:500; }
        .link-subtitle { font-size:.8rem; color:#7f8c8d; }
        .link-chevron { font-size:.9rem; color:#bdc3c7; }

        .badge-pill { display:inline-flex; align-items:center; padding:.15rem .5rem; font-size:.7rem; border-radius:999px; background:#eafaf1; color:#1e8449; margin-left:.4rem; }
    </style>
</head>
<body>
    <div class="page-shell">
        <header class="page-header">
            <div class="page-title">Settings &amp; Users</div>
            <div class="page-subtitle">Configure system behavior and manage administrative resources.</div>
        </header>

        <nav class="tabs" aria-label="Settings navigation">
            <span class="tab active">System Settings</span>
            <a class="tab" href="<?= site_url('system-admin/users') ?>">User Management</a>
        </nav>

        <section class="section">
            <div class="section-title">General Settings</div>
            <div class="section-subtitle">High-level information about your supply chain system.</div>
            <div class="grid-2">
                <div>
                    <div class="field-label">System Name</div>
                    <input class="field-input" type="text" value="CHAKANOKS Supply Chain" disabled>
                </div>
                <div>
                    <div class="field-label">Environment</div>
                    <input class="field-input" type="text" value="Production" disabled>
                </div>
            </div>
        </section>

        <section class="section">
            <div class="section-title">Administration</div>
            <div class="section-subtitle">Manage branches, security configuration, and scheduled backups.</div>
            <div class="grid-2">
                <a class="link-card" href="<?= site_url('system-admin/branches') ?>">
                    <div>
                        <div class="link-title">Branch Management</div>
                        <div class="link-subtitle">Configure branches and their details.</div>
                    </div>
                    <div class="link-chevron">&rsaquo;</div>
                </a>
                <a class="link-card" href="<?= site_url('system-admin/security') ?>">
                    <div>
                        <div class="link-title">Security Settings</div>
                        <div class="link-subtitle">Password policies, session settings, audit controls.</div>
                    </div>
                    <div class="link-chevron">&rsaquo;</div>
                </a>
            </div>
        </section>

        <section class="section">
            <div class="section-title">Maintenance</div>
            <div class="section-subtitle">Keep the system healthy and recoverable.</div>
            <div class="grid-2">
                <a class="link-card" href="<?= site_url('system-admin/backups') ?>">
                    <div>
                        <div class="link-title">Backups &amp; Restore <span class="badge-pill">Recommended</span></div>
                        <div class="link-subtitle">Create and restore database snapshots.</div>
                    </div>
                    <div class="link-chevron">&rsaquo;</div>
                </a>
                <a class="link-card" href="<?= site_url('dashboard') ?>">
                    <div>
                        <div class="link-title">System Overview</div>
                        <div class="link-subtitle">Open the main dashboard and reports.</div>
                    </div>
                    <div class="link-chevron">&rsaquo;</div>
                </a>
            </div>
        </section>
    </div>
</body>
</html>
