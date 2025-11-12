<?php
if ($_POST && isset($_POST['install'])) {
    if (!is_dir('config')) mkdir('config', 0755, true);
    file_put_contents('config/installed.lock', date('Y-m-d H:i:s'));
    header('Location: install.php?done=1');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup</title>
    <meta name="theme-color" content="#000000">
    
    <style>
        :root {
            --white: #ffffff;
            --black: #000000;
            --gray-50: #f9fafb;
            --gray-200: #e5e7eb;
            --gray-600: #4b5563;
            --gray-900: #111827;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            -webkit-tap-highlight-color: transparent;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, var(--gray-50) 0%, var(--white) 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 16px;
        }

        .card {
            background: var(--white);
            border: 1px solid var(--gray-200);
            border-radius: 16px;
            padding: 32px;
            max-width: 400px;
            width: 100%;
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
        }

        .btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            min-height: 48px;
            background: var(--black);
            color: var(--white);
            border: none;
            border-radius: 12px;
            font-family: inherit;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 150ms ease;
            text-decoration: none;
        }

        .btn:hover {
            background: var(--gray-600);
            transform: translateY(-1px);
        }
    </style>
</head>
<body>

    <div class="card">
        <div style="text-align: center; margin-bottom: 32px;">
            <div style="width: 64px; height: 64px; background: var(--black); color: var(--white); border-radius: 16px; display: flex; align-items: center; justify-content: center; font-size: 28px; font-weight: 700; margin: 0 auto 16px;">R</div>
            <h1 style="font-size: 24px; font-weight: 700; color: var(--gray-900); margin-bottom: 8px;">Setup</h1>
            <p style="color: var(--gray-600); font-size: 14px;">Modern Routing System</p>
        </div>

        <?php if (isset($_GET['done'])): ?>
            <div style="text-align: center;">
                <div style="width: 48px; height: 48px; background: #dcfce7; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px;">
                    <svg width="24" height="24" fill="none" stroke="#16a34a" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                <h2 style="font-size: 18px; font-weight: 600; color: var(--gray-900); margin-bottom: 12px;">Setup Complete</h2>
                <p style="color: var(--gray-600); font-size: 14px; margin-bottom: 24px;">System ready for use</p>
                <a href="index.php" class="btn">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                    </svg>
                    ACCESS DASHBOARD
                </a>
            </div>
        <?php else: ?>
            <div style="display: flex; flex-direction: column; gap: 20px;">
                <div style="background: var(--gray-50); border: 1px solid var(--gray-200); border-radius: 12px; padding: 16px;">
                    <h3 style="font-size: 14px; font-weight: 600; margin-bottom: 8px; color: var(--gray-900);">Features</h3>
                    <div style="font-size: 13px; color: var(--gray-600); line-height: 1.4;">
                        <div>• Clean black & white design</div>
                        <div>• Mobile-first responsive</div>
                        <div>• PWA installation ready</div>
                        <div>• Performance optimized</div>
                    </div>
                </div>

                <form method="POST">
                    <button type="submit" name="install" value="1" class="btn">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                        </svg>
                        INSTALL SYSTEM
                    </button>
                </form>
            </div>
        <?php endif; ?>

    </div>
</body>
</html>
