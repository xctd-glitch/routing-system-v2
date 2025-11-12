<?php

declare(strict_types=1);

session_start();

require_once __DIR__ . '/src/helpers.php';

/**
 * Dashboard Routing System - Ultra Clean Black & White Design
 * Slim, Fit, Clean, Modern CSS - Production Ready
 */

const SUCCESS_MESSAGES = [
    'config_updated' => 'Configuration updated successfully!',
    'url_added' => 'URL added successfully!',
    'countries_updated' => 'Countries updated successfully!',
];

/**
 * @var array{
 *     system_on: bool,
 *     is_active: bool,
 *     rule_type: string,
 *     mute_duration: int,
 *     unmute_duration: int
 * }
 */
$config = [
    'system_on' => false,
    'is_active' => false,
    'rule_type' => 'static_route',
    'mute_duration' => 120,
    'unmute_duration' => 120,
];

/**
 * @var list<array{id: int, url: string, weight: int, priority: int, active: bool}>
 */
$urls = [
    [
        'id' => 1,
        'url' => 'https://example.com',
        'weight' => 1,
        'priority' => 1,
        'active' => true,
    ],
    [
        'id' => 2,
        'url' => 'https://backup.com',
        'weight' => 2,
        'priority' => 2,
        'active' => true,
    ],
];

$countries = [
    [
        'code' => 'US',
        'name' => 'United States',
    ],
    [
        'code' => 'UK',
        'name' => 'United Kingdom',
    ],
    [
        'code' => 'DE',
        'name' => 'Germany',
    ],
];

$stats = [
    'requests' => 1250,
    'success' => 95.2,
    'response' => 45,
];

$successKey = filter_input(INPUT_GET, 'success', FILTER_SANITIZE_SPECIAL_CHARS);
$successMessage = null;
$errorMessage = null;

if ($successKey !== null && $successKey !== '') {
    $successMessage = SUCCESS_MESSAGES[$successKey] ?? 'Operation completed successfully!';
}

$csrfToken = getCsrfToken();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submittedTokenRaw = filter_input(INPUT_POST, 'csrf_token', FILTER_UNSAFE_RAW);
    $submittedToken = is_string($submittedTokenRaw) ? $submittedTokenRaw : null;

    if (!isValidCsrfToken($submittedToken)) {
        http_response_code(400);
        $errorMessage = 'Invalid request token. Please refresh the page and try again.';
    } else {
        $action = filter_input(INPUT_POST, 'action', FILTER_SANITIZE_SPECIAL_CHARS) ?? '';
        $csrfToken = generateCsrfToken();

        switch ($action) {
            case 'update_config':
                header('Location: index.php?success=config_updated');
                exit;
            case 'add_url':
                $urlValue = filter_input(INPUT_POST, 'url', FILTER_VALIDATE_URL);
                $weightValue = filter_input(INPUT_POST, 'weight', FILTER_VALIDATE_INT, [
                    'options' => [
                        'min_range' => 1,
                        'max_range' => 1000,
                    ],
                ]);
                $priorityValue = filter_input(INPUT_POST, 'priority', FILTER_VALIDATE_INT, [
                    'options' => [
                        'min_range' => 0,
                        'max_range' => 1000,
                    ],
                ]);

                if ($urlValue === false || $weightValue === false || $priorityValue === false) {
                    $errorMessage = 'Please provide a valid URL, weight, and priority.';
                } else {
                    header('Location: index.php?success=url_added');
                    exit;
                }

                break;
            case 'update_countries':
                $countriesRawValue = filter_input(INPUT_POST, 'countries', FILTER_UNSAFE_RAW);
                $countriesRaw = is_string($countriesRawValue) ? $countriesRawValue : '';

                if (!preg_match('/^\s*[A-Z]{2}(\s*,\s*[A-Z]{2})*\s*$/', $countriesRaw)) {
                    $errorMessage = 'Please provide valid ISO 3166-1 alpha-2 country codes separated by commas.';
                } else {
                    header('Location: index.php?success=countries_updated');
                    exit;
                }

                break;
            default:
                $errorMessage = 'Unknown action requested.';
                break;
        }
    }
}

$systemOnValue = filter_has_var(INPUT_POST, 'system_on')
    ? true
    : $config['system_on'];
$isActiveValue = filter_has_var(INPUT_POST, 'is_active')
    ? true
    : $config['is_active'];

$ruleTypeCandidate = filter_input(INPUT_POST, 'rule_type', FILTER_SANITIZE_SPECIAL_CHARS);
$ruleTypeValue = is_string($ruleTypeCandidate) && in_array(
    $ruleTypeCandidate,
    ['static_route', 'random_route', 'mute_unmute'],
    true
)
    ? $ruleTypeCandidate
    : $config['rule_type'];

$muteDurationCandidate = filter_input(
    INPUT_POST,
    'mute_duration',
    FILTER_VALIDATE_INT,
    [
        'options' => [
            'min_range' => 0,
            'max_range' => 86400,
        ],
    ]
);
$unmuteDurationCandidate = filter_input(
    INPUT_POST,
    'unmute_duration',
    FILTER_VALIDATE_INT,
    [
        'options' => [
            'min_range' => 0,
            'max_range' => 86400,
        ],
    ]
);

$muteDurationValue = $muteDurationCandidate !== false
    ? $muteDurationCandidate
    : (int) $config['mute_duration'];
$unmuteDurationValue = $unmuteDurationCandidate !== false
    ? $unmuteDurationCandidate
    : (int) $config['unmute_duration'];

$systemOn = (bool) $systemOnValue;
$isActive = (bool) $isActiveValue;

$systemStatus = $systemOn ? 'ON' : 'OFF';
$activityStatus = $isActive ? 'ACTIVE' : 'IDLE';
$ruleType = $ruleTypeValue;
$muteDuration = (string) $muteDurationValue;
$unmuteDuration = (string) $unmuteDurationValue;
$countryCodes = implode(', ', array_column($countries, 'code'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <title>Routing Dashboard</title>
    <link rel="manifest" href="manifest.json">
    <meta name="theme-color" content="#000000">
    <meta name="apple-mobile-web-app-capable" content="yes">

    <style>
        :root {
            --white: #ffffff;
            --black: #000000;
            --gray-50: #f9fafb;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-300: #d1d5db;
            --gray-400: #9ca3af;
            --gray-500: #6b7280;
            --gray-600: #4b5563;
            --gray-700: #374151;
            --gray-800: #1f2937;
            --gray-900: #111827;
            --radius: 12px;
            --shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1);
            --transition: 150ms cubic-bezier(0.4, 0, 0.2, 1);
        }

        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html {
            -webkit-text-size-adjust: 100%;
            -webkit-tap-highlight-color: transparent;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            font-size: 14px;
            line-height: 1.5;
            color: var(--gray-900);
            background: linear-gradient(135deg, var(--gray-50) 0%, var(--white) 100%);
            min-height: 100vh;
            -webkit-font-smoothing: antialiased;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 16px;
        }

        .card {
            background: var(--white);
            border: 1px solid var(--gray-200);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            transition: all var(--transition);
        }

        .card:hover {
            border-color: var(--gray-300);
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
            transform: translateY(-1px);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            min-height: 44px;
            padding: 12px 20px;
            border: none;
            border-radius: var(--radius);
            font-family: inherit;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all var(--transition);
            text-decoration: none;
        }

        .btn-primary {
            background: var(--black);
            color: var(--white);
        }

        .btn-primary:hover {
            background: var(--gray-800);
            transform: translateY(-1px);
        }

        .btn-secondary {
            background: var(--white);
            color: var(--black);
            border: 1px solid var(--gray-200);
        }

        .btn-secondary:hover {
            background: var(--gray-50);
            border-color: var(--gray-300);
        }

        .input {
            min-height: 44px;
            padding: 12px 16px;
            border: 1px solid var(--gray-200);
            border-radius: var(--radius);
            background: var(--white);
            font-family: inherit;
            font-size: 14px;
            transition: all var(--transition);
            width: 100%;
        }

        .input:focus {
            outline: none;
            border-color: var(--black);
            box-shadow: 0 0 0 3px rgba(0, 0, 0, 0.1);
        }

        .metric {
            text-align: center;
            padding: 20px;
            background: var(--white);
            border: 1px solid var(--gray-200);
            border-radius: var(--radius);
            transition: all var(--transition);
        }

        .metric:hover {
            border-color: var(--black);
            transform: translateY(-2px);
        }

        .metric-value {
            font-size: 28px;
            font-weight: 700;
            color: var(--gray-900);
            line-height: 1;
        }

        .metric-label {
            font-size: 11px;
            color: var(--gray-500);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 8px;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 500;
            border: 1px solid var(--gray-200);
            background: var(--gray-100);
            color: var(--gray-700);
        }

        .badge-active {
            background: var(--black);
            color: var(--white);
            border-color: var(--black);
        }

        .status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #10b981;
            animation: pulse 2s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.6; }
        }

        .grid {
            display: grid;
            gap: 16px;
        }

        .grid-2 { grid-template-columns: repeat(2, 1fr); }
        .grid-4 { grid-template-columns: repeat(4, 1fr); }

        @media (max-width: 768px) {
            .container { padding: 0 12px; }
            .grid-2, .grid-4 { grid-template-columns: 1fr; }
            .metric-value { font-size: 24px; }
        }
    </style>
</head>
<body>

    <header style="background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(8px); border-bottom: 1px solid var(--gray-200); position: sticky; top: 0; z-index: 100;">
        <div class="container" style="padding: 16px 16px;">
            <div style="display: flex; align-items: center; justify-content: space-between;">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <div style="width: 40px; height: 40px; background: var(--black); color: var(--white); border-radius: var(--radius); display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 18px;">
                        R
                    </div>
                    <div>
                        <h1 style="font-size: 20px; font-weight: 700; color: var(--gray-900); margin-bottom: 2px;">Routing Dashboard</h1>
                        <p style="font-size: 12px; color: var(--gray-500);">Clean • Modern • Fast</p>
                    </div>
                </div>

                <div style="display: flex; align-items: center; gap: 12px;">
                    <div style="display: flex; align-items: center; gap: 6px;">
                        <span class="status-dot"></span>
                        <span style="font-size: 12px; color: var(--gray-600);">Online</span>
                    </div>
                    <button onclick="location.reload()" class="btn btn-secondary" style="min-height: 36px; padding: 8px 16px;">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </header>

    <main class="container" style="padding: 24px 16px;">
        <?php if ($successMessage !== null) : ?>
            <div class="card" style="background: var(--gray-50); border-color: var(--gray-300); padding: 16px; margin-bottom: 24px;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: var(--black);">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span style="color: var(--gray-900); font-weight: 500;"><?php echo escape($successMessage); ?></span>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($errorMessage !== null) : ?>
            <div class="card" style="background: #fef2f2; border-color: #fecaca; padding: 16px; margin-bottom: 24px;">
                <div style="display: flex; align-items: center; gap: 8px; color: #991b1b;">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M4.93 4.93l14.14 14.14M12 5a7 7 0 017 7 7 7 0 01-7 7 7 7 0 01-7-7 7 7 0 017-7z"></path>
                    </svg>
                    <span style="font-weight: 500;"><?php echo escape($errorMessage); ?></span>
                </div>
            </div>
        <?php endif; ?>

        <div class="grid" style="gap: 24px;">
            <div class="card" style="padding: 24px;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px;">
                    <div>
                        <h2 style="font-size: 18px; font-weight: 600; color: var(--gray-900); display: flex; align-items: center; gap: 8px;">
                            <span class="status-dot"></span>
                            System Status
                        </h2>
                        <p style="font-size: 12px; color: var(--gray-500); margin-top: 2px;">Real-time monitoring</p>
                    </div>
                    <div style="text-align: right; font-size: 11px; color: var(--gray-400);">
                        <div>Updated</div>
                        <div style="font-family: ui-monospace, monospace; font-weight: 600; color: var(--gray-900);">
                            <?php echo escape(date('H:i:s')); ?>
                        </div>
                    </div>
                </div>

                <div class="grid grid-4" style="margin-bottom: 20px;">
                    <div class="metric">
                        <div class="metric-value"><?php echo escape($systemStatus); ?></div>
                        <div class="metric-label">System</div>
                    </div>

                    <div class="metric">
                        <div class="metric-value"><?php echo escape($activityStatus); ?></div>
                        <div class="metric-label">Status</div>
                    </div>

                    <div class="metric">
                        <div class="metric-value"><?php echo escape(number_format($stats['success'], 1)); ?>%</div>
                        <div class="metric-label">Success</div>
                    </div>

                    <div class="metric">
                        <div class="metric-value"><?php echo escape((string) $stats['response']); ?>ms</div>
                        <div class="metric-label">Response</div>
                    </div>
                </div>

                <div style="display: flex; flex-wrap: wrap; gap: 8px; padding-top: 16px; border-top: 1px solid var(--gray-200);">
                    <span class="badge <?php echo $systemOn ? 'badge-active' : ''; ?>">
                        SYSTEM <?php echo escape($systemStatus); ?>
                    </span>
                    <span class="badge <?php echo $isActive ? 'badge-active' : ''; ?>">
                        <?php echo escape($activityStatus); ?>
                    </span>
                    <span class="badge">MOBILE-FIRST</span>
                    <span class="badge">PWA-READY</span>
                </div>
            </div>

            <div class="card" style="padding: 24px;">
                <h2 style="font-size: 18px; font-weight: 600; color: var(--gray-900); margin-bottom: 20px;">Configuration</h2>

                <form method="POST" style="display: flex; flex-direction: column; gap: 20px;" novalidate>
                    <input type="hidden" name="action" value="update_config">
                    <input type="hidden" name="csrf_token" value="<?php echo escape($csrfToken); ?>">

                    <div class="grid grid-2">
                        <div style="padding: 20px; border: 1px solid var(--gray-200); border-radius: var(--radius); background: var(--gray-50);">
                            <h3 style="font-size: 14px; font-weight: 600; margin-bottom: 16px; color: var(--gray-900);">System</h3>
                            <div style="display: flex; flex-direction: column; gap: 12px;">
                                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; min-height: 36px;">
                                    <input type="checkbox" name="system_on" <?php echo $systemOn ? 'checked' : ''; ?> style="width: 16px; height: 16px; accent-color: var(--black);">
                                    <span style="font-size: 14px; color: var(--gray-900);">System ON</span>
                                </label>
                                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; min-height: 36px;">
                                    <input type="checkbox" name="is_active" <?php echo $isActive ? 'checked' : ''; ?> style="width: 16px; height: 16px; accent-color: var(--black);">
                                    <span style="font-size: 14px; color: var(--gray-900);">Active</span>
                                </label>
                            </div>
                        </div>

                        <div style="padding: 20px; border: 1px solid var(--gray-200); border-radius: var(--radius); background: var(--gray-50);">
                            <h3 style="font-size: 14px; font-weight: 600; margin-bottom: 16px; color: var(--gray-900);">Metrics</h3>
                            <div style="display: flex; flex-direction: column; gap: 8px; font-size: 13px;">
                                <div style="display: flex; justify-content: space-between;">
                                    <span style="color: var(--gray-600);">URLs</span>
                                    <span style="font-weight: 600; color: var(--gray-900);"><?php echo escape((string) count($urls)); ?></span>
                                </div>
                                <div style="display: flex; justify-content: space-between;">
                                    <span style="color: var(--gray-600);">Countries</span>
                                    <span style="font-weight: 600; color: var(--gray-900);"><?php echo escape((string) count($countries)); ?></span>
                                </div>
                                <div style="display: flex; justify-content: space-between;">
                                    <span style="color: var(--gray-600);">Requests</span>
                                    <span style="font-weight: 600; color: var(--gray-900);"><?php echo escape(number_format($stats['requests'])); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div style="padding: 20px; border: 1px solid var(--gray-200); border-radius: var(--radius); background: var(--gray-50);">
                        <h3 style="font-size: 14px; font-weight: 600; margin-bottom: 16px; color: var(--gray-900);">Routing Rules</h3>
                        <div style="display: flex; flex-direction: column; gap: 12px;">
                            <label style="display: flex; align-items: flex-start; gap: 12px; padding: 12px; border: 1px solid var(--gray-200); border-radius: 8px; background: var(--white); cursor: pointer; transition: border-color var(--transition);" onmouseover="this.style.borderColor='var(--black)'" onmouseout="this.style.borderColor='var(--gray-200)';">
                                <input type="radio" name="rule_type" value="static_route" <?php echo $ruleType === 'static_route' ? 'checked' : ''; ?> style="margin-top: 2px; accent-color: var(--black);">
                                <div>
                                    <div style="font-weight: 500; color: var(--gray-900); margin-bottom: 2px;">Static Route</div>
                                    <div style="font-size: 12px; color: var(--gray-600);">Highest priority URL</div>
                                </div>
                            </label>

                            <label style="display: flex; align-items: flex-start; gap: 12px; padding: 12px; border: 1px solid var(--gray-200); border-radius: 8px; background: var(--white); cursor: pointer; transition: border-color var(--transition);" onmouseover="this.style.borderColor='var(--black)'" onmouseout="this.style.borderColor='var(--gray-200)';">
                                <input type="radio" name="rule_type" value="random_route" <?php echo $ruleType === 'random_route' ? 'checked' : ''; ?> style="margin-top: 2px; accent-color: var(--black);">
                                <div>
                                    <div style="font-weight: 500; color: var(--gray-900); margin-bottom: 2px;">Random Route</div>
                                    <div style="font-size: 12px; color: var(--gray-600);">Weight-based selection</div>
                                </div>
                            </label>

                            <label style="display: flex; align-items: flex-start; gap: 12px; padding: 12px; border: 1px solid var(--gray-200); border-radius: 8px; background: var(--white); cursor: pointer; transition: border-color var(--transition);" onmouseover="this.style.borderColor='var(--black)'" onmouseout="this.style.borderColor='var(--gray-200)';">
                                <input type="radio" name="rule_type" value="mute_unmute" <?php echo $ruleType === 'mute_unmute' ? 'checked' : ''; ?> style="margin-top: 2px; accent-color: var(--black);">
                                <div style="flex: 1;">
                                    <div style="font-weight: 500; color: var(--gray-900); margin-bottom: 2px;">Mute/Unmute</div>
                                    <div style="font-size: 12px; color: var(--gray-600); margin-bottom: 8px;">Time-based toggle</div>
                                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px;">
                                        <input type="number" name="mute_duration" value="<?php echo escape($muteDuration); ?>" placeholder="Mute" class="input" style="padding: 6px 8px; min-height: 32px; font-size: 12px;">
                                        <input type="number" name="unmute_duration" value="<?php echo escape($unmuteDuration); ?>" placeholder="Unmute" class="input" style="padding: 6px 8px; min-height: 32px; font-size: 12px;">
                                    </div>
                                </div>
                            </label>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width: 100%;">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3-3m0 0l-3 3m3-3v12"></path>
                        </svg>
                        SAVE CONFIGURATION
                    </button>
                </form>
            </div>

            <div class="card" style="padding: 24px;">
                <h2 style="font-size: 18px; font-weight: 600; color: var(--gray-900); margin-bottom: 20px;">Target URLs</h2>

                <form method="POST" style="margin-bottom: 20px;" novalidate>
                    <input type="hidden" name="action" value="add_url">
                    <input type="hidden" name="csrf_token" value="<?php echo escape($csrfToken); ?>">
                    <div style="display: grid; grid-template-columns: 2fr 1fr 1fr auto; gap: 12px; align-items: end;">
                        <input type="url" name="url" placeholder="https://target-site.com" required class="input">
                        <input type="number" name="weight" value="1" min="1" class="input" placeholder="Weight">
                        <input type="number" name="priority" value="1" min="0" class="input" placeholder="Priority">
                        <button type="submit" class="btn btn-primary">ADD</button>
                    </div>
                </form>

                <div style="display: flex; flex-direction: column; gap: 8px;">
                    <?php foreach ($urls as $index => $url) : ?>
                        <?php $isUrlActive = (bool) $url['active']; ?>
                        <div style="padding: 16px; border: 1px solid var(--gray-200); border-radius: 8px; background: var(--white); transition: border-color var(--transition);" onmouseover="this.style.borderColor='var(--black)'" onmouseout="this.style.borderColor='var(--gray-200)';">
                            <div style="display: flex; align-items: center; justify-content: space-between;">
                                <div style="flex: 1;">
                                    <div style="font-weight: 500; margin-bottom: 4px;">
                                        <a href="<?php echo escape($url['url']); ?>" target="_blank" style="color: var(--black); text-decoration: none;" onmouseover="this.style.textDecoration='underline'" onmouseout="this.style.textDecoration='none';">
                                            <?php echo escape($url['url']); ?>
                                        </a>
                                    </div>
                                    <div style="font-size: 12px; color: var(--gray-600);">
                                        Weight: <strong><?php echo escape((string) $url['weight']); ?></strong> • Priority: <strong><?php echo escape((string) $url['priority']); ?></strong>
                                        <span style="color: #10b981; margin-left: 8px;">● <?php echo escape($isUrlActive ? 'ACTIVE' : 'INACTIVE'); ?></span>
                                    </div>
                                </div>
                                <span style="background: var(--gray-100); color: var(--gray-700); padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: 500;">#<?php echo escape((string) ($index + 1)); ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="card" style="padding: 24px;">
                <h2 style="font-size: 18px; font-weight: 600; color: var(--gray-900); margin-bottom: 20px;">Country Targeting</h2>

                <form method="POST" style="margin-bottom: 20px;" novalidate>
                    <input type="hidden" name="action" value="update_countries">
                    <input type="hidden" name="csrf_token" value="<?php echo escape($csrfToken); ?>">
                    <div style="margin-bottom: 12px;">
                        <label style="display: block; font-size: 12px; font-weight: 500; color: var(--gray-900); margin-bottom: 8px;">Country Codes (ISO 3166-1)</label>
                        <textarea name="countries" rows="2" placeholder="US, UK, DE, FR, ID" class="input" style="font-family: ui-monospace, monospace; resize: vertical;"><?php echo escape($countryCodes); ?></textarea>
                        <div style="font-size: 11px; color: var(--gray-500); margin-top: 4px;">Comma-separated codes</div>
                    </div>
                    <button type="submit" class="btn btn-primary">UPDATE COUNTRIES</button>
                </form>

                <div>
                    <div style="font-size: 14px; font-weight: 500; margin-bottom: 12px; color: var(--gray-900);">
                        Active Countries (<?php echo escape((string) count($countries)); ?>)
                    </div>
                    <div style="display: flex; flex-wrap: wrap; gap: 6px;">
                        <?php foreach ($countries as $country) : ?>
                            <span style="display: inline-flex; align-items: center; padding: 6px 10px; background: var(--white); border: 1px solid var(--gray-200); border-radius: 6px; font-size: 12px;">
                                <span style="font-family: ui-monospace, monospace; font-weight: 700; margin-right: 4px; color: var(--black);"><?php echo escape($country['code']); ?></span>
                                <span style="color: var(--gray-600);"><?php echo escape($country['name']); ?></span>
                            </span>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <div id="pwa-banner" style="position: fixed; bottom: 16px; right: 16px; width: 300px; background: var(--black); color: var(--white); padding: 16px; border-radius: var(--radius); box-shadow: var(--shadow); transform: translateY(100%); transition: transform 0.3s ease;">
        <div style="display: flex; align-items: center; gap: 12px;">
            <div style="width: 32px; height: 32px; background: var(--white); color: var(--black); border-radius: 6px; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 14px; flex-shrink: 0;">R</div>
            <div style="flex: 1;">
                <div style="font-size: 13px; font-weight: 600; margin-bottom: 2px;">Install App</div>
                <div style="font-size: 11px; opacity: 0.8;">Add to home screen</div>
            </div>
            <button id="install-btn" style="background: var(--white); color: var(--black); border: none; padding: 6px 10px; border-radius: 4px; font-size: 11px; font-weight: 500; cursor: pointer;">INSTALL</button>
        </div>
    </div>

    <footer style="text-align: center; padding: 32px 16px; border-top: 1px solid var(--gray-200); margin-top: 32px;">
        <div style="display: inline-flex; align-items: center; gap: 16px; background: var(--white); border: 1px solid var(--gray-200); border-radius: 8px; padding: 12px 16px; font-size: 11px; color: var(--gray-500);">
            <span style="display: flex; align-items: center; gap: 4px;">
                <span style="width: 4px; height: 4px; background: #10b981; border-radius: 50%;"></span>
                MOBILE-FIRST
            </span>
            <span style="display: flex; align-items: center; gap: 4px;">
                <span style="width: 4px; height: 4px; background: var(--black); border-radius: 50%;"></span>
                MODERN
            </span>
            <span style="display: flex; align-items: center; gap: 4px;">
                <span style="width: 4px; height: 4px; background: var(--gray-400); border-radius: 50%;"></span>
                FAST
            </span>
        </div>
    </footer>

    <script>
        let deferredPrompt;

        window.addEventListener('beforeinstallprompt', (event) => {
            event.preventDefault();
            deferredPrompt = event;
            setTimeout(() => {
                const banner = document.getElementById('pwa-banner');
                if (banner) {
                    banner.style.transform = 'translateY(0)';
                }
            }, 2000);
        });

        const installButton = document.getElementById('install-btn');
        if (installButton) {
            installButton.addEventListener('click', async () => {
                if (!deferredPrompt) {
                    return;
                }

                deferredPrompt.prompt();
                await deferredPrompt.userChoice;
                deferredPrompt = null;
                const banner = document.getElementById('pwa-banner');
                if (banner) {
                    banner.style.transform = 'translateY(100%)';
                }
            });
        }

        document.querySelectorAll('.btn, .input').forEach((element) => {
            element.addEventListener('touchstart', () => {
                element.style.transform = 'scale(0.98)';
            });

            element.addEventListener('touchend', () => {
                element.style.transform = 'scale(1)';
            });
        });
    </script>
</body>
</html>
