<?php

/**
 * Dashboard Routing System - Mobile-First PWA
 * Complete responsive dashboard with monochrome UI inspired by Shadcn.
 */

declare(strict_types=1);

// Mock configuration data (replace with database in production)
$config = [
    'system_on' => false,
    'is_active' => false,
    'rule_type' => 'static_route',
    'mute_duration' => 120,
    'unmute_duration' => 120,
    'current_state' => 'normal',
];

$urls = [];

$countries = [];

$stats = [
    'total_requests' => 1250,
    'target_decisions' => 875,
    'success_rate' => 95.2,
    'avg_time' => 45,
];

$successKey = filter_input(INPUT_GET, 'success', FILTER_SANITIZE_SPECIAL_CHARS);
$successMessage = null;
$systemOnChecked = $config['system_on'] ? 'checked' : '';
$isActiveChecked = $config['is_active'] ? 'checked' : '';
$ruleType = $config['rule_type'];
$activeCountryCodes = implode(', ', array_column($countries, 'code'));

if ($successKey !== null && $successKey !== '') {
    $successMessage = match ($successKey) {
        'config_updated' => 'Configuration updated successfully!',
        'url_added' => 'URL added successfully!',
        'countries_updated' => 'Countries updated successfully!',
        default => 'Operation completed successfully!',
    };
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = filter_input(INPUT_POST, 'action', FILTER_SANITIZE_SPECIAL_CHARS) ?? '';

    switch ($action) {
        case 'update_config':
            header('Location: index.php?success=config_updated');
            exit;
        case 'add_url':
            header('Location: index.php?success=url_added');
            exit;
        case 'update_countries':
            header('Location: index.php?success=countries_updated');
            exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <title>Dashboard Routing System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="manifest" href="manifest.json">
    <meta name="theme-color" content="#3b82f6">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="Routing Dashboard">
    
    <style>
        :root {
            color-scheme: dark;
            font-synthesis: none;
            text-rendering: optimizeLegibility;
        }

        * {
            -webkit-tap-highlight-color: transparent;
        }

        .glass {
            backdrop-filter: blur(18px);
            background: rgba(15, 23, 42, 0.78);
            border: 1px solid rgba(148, 163, 184, 0.25);
            box-shadow: 0 24px 60px rgba(15, 23, 42, 0.35);
        }

        .panel {
            border-radius: 0.75rem;
            padding: 1rem;
        }

        .panel-lg {
            border-radius: 1rem;
        }

        .card-hover {
            transition: transform 200ms ease, box-shadow 200ms ease;
        }

        .card-hover:hover {
            transform: translateY(-4px);
            box-shadow: 0 28px 80px rgba(2, 6, 23, 0.45);
        }

        .touch-target {
            min-height: 44px;
            min-width: 44px;
        }

        .btn {
            align-items: center;
            border-radius: 0.75rem;
            border: 1px solid transparent;
            display: inline-flex;
            font-size: 0.875rem;
            font-weight: 600;
            gap: 0.5rem;
            justify-content: center;
            padding: 0.5rem 1rem;
            transition: background-color 200ms ease, color 200ms ease,
                border-color 200ms ease, transform 200ms ease;
        }

        .btn-primary {
            background: #f8fafc;
            color: #020617;
            box-shadow: 0 20px 40px rgba(2, 6, 23, 0.35);
        }

        .btn-primary:hover {
            background: #ffffff;
        }

        .btn-muted {
            background: rgba(15, 23, 42, 0.6);
            border-color: rgba(71, 85, 105, 0.7);
            color: #e2e8f0;
        }

        .btn-muted:hover {
            background: rgba(15, 23, 42, 0.85);
        }

        .btn-ghost {
            color: #94a3b8;
        }

        .btn-ghost:hover {
            color: #e2e8f0;
        }

        .metric-card {
            background: rgba(15, 23, 42, 0.8);
            border: 1px solid rgba(71, 85, 105, 0.75);
            border-radius: 0.75rem;
            padding: 0.75rem;
            text-align: center;
            transition: transform 200ms ease, border-color 200ms ease;
        }

        .metric-card:hover {
            border-color: rgba(100, 116, 139, 0.8);
            transform: scale(1.02);
        }

        .badge {
            align-items: center;
            background: rgba(15, 23, 42, 0.7);
            border: 1px solid rgba(71, 85, 105, 0.75);
            border-radius: 9999px;
            color: #e2e8f0;
            display: inline-flex;
            font-size: 0.75rem;
            font-weight: 600;
            gap: 0.5rem;
            padding: 0.375rem 0.75rem;
        }

        .badge svg {
            height: 0.875rem;
            width: 0.875rem;
        }

        .input-field,
        .textarea-field {
            background: #020617;
            border: 1px solid rgba(71, 85, 105, 0.75);
            border-radius: 0.75rem;
            color: #e2e8f0;
            font-size: 0.875rem;
            padding: 0.625rem 0.875rem;
            transition: border-color 200ms ease, box-shadow 200ms ease;
            width: 100%;
        }

        .input-field:focus,
        .textarea-field:focus {
            border-color: rgba(226, 232, 240, 0.8);
            box-shadow: 0 0 0 2px rgba(226, 232, 240, 0.25);
            outline: none;
        }

        .badge-stack {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .alert-success {
            background: rgba(34, 197, 94, 0.1);
            border: 1px solid rgba(16, 185, 129, 0.4);
        }

        .status-dot {
            display: inline-flex;
            height: 0.5rem;
            position: relative;
            width: 0.5rem;
        }

        .status-ping {
            animation: status-ping 2.5s cubic-bezier(0.4, 0, 0.6, 1) infinite;
            background: rgba(52, 211, 153, 0.7);
            border-radius: 9999px;
            inset: 0;
            position: absolute;
        }

        .status-dot-inner {
            background: #6ee7b7;
            border-radius: 9999px;
            height: 100%;
            position: relative;
            width: 100%;
        }

        .checkbox-control {
            background: #0f172a;
            border: 1px solid #475569;
            border-radius: 0.5rem;
            color: #e2e8f0;
            height: 1.1rem;
            width: 1.1rem;
        }

        .checkbox-control:focus {
            border-color: rgba(226, 232, 240, 0.8);
            box-shadow: 0 0 0 2px rgba(226, 232, 240, 0.25);
            outline: none;
        }

        .field-label {
            color: #cbd5f5;
            display: block;
            font-size: 0.75rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .banner-container {
            border: 1px solid rgba(71, 85, 105, 0.75);
            bottom: 1rem;
            left: 1rem;
            position: fixed;
            right: 1rem;
        }

        .banner-transition {
            transform: translateY(100%);
            transition: transform 300ms ease;
        }

        .badge-code {
            color: #f8fafc;
            font-family: 'JetBrains Mono', ui-monospace, SFMono-Regular, Menlo, monospace;
            font-weight: 600;
            margin-right: 0.5rem;
        }

        .banner-icon {
            align-items: center;
            background: #f8fafc;
            border-radius: 0.75rem;
            box-shadow: 0 16px 32px rgba(2, 6, 23, 0.45);
            color: #020617;
            display: flex;
            font-size: 0.9rem;
            font-weight: 700;
            height: 2.5rem;
            justify-content: center;
            width: 2.5rem;
        }

        .logo-mark {
            align-items: center;
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 45%, #cbd5f5 100%);
            border-radius: 0.75rem;
            box-shadow: 0 20px 40px rgba(2, 6, 23, 0.45);
            color: #020617;
            display: flex;
            font-weight: 700;
            height: 2.25rem;
            justify-content: center;
            width: 2.25rem;
        }

        .control-card {
            background: rgba(15, 23, 42, 0.7);
            border: 1px solid rgba(71, 85, 105, 0.75);
            border-radius: 0.75rem;
            padding: 1rem;
        }

        .toggle-row {
            align-items: center;
            border: 1px solid transparent;
            border-radius: 0.75rem;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            padding: 0.5rem 0.75rem;
            transition: background-color 200ms ease, border-color 200ms ease;
        }

        .toggle-row:hover {
            background: rgba(15, 23, 42, 0.85);
            border-color: rgba(71, 85, 105, 0.7);
        }

        .option-card {
            align-items: flex-start;
            border: 1px solid rgba(71, 85, 105, 0.75);
            border-radius: 0.75rem;
            cursor: pointer;
            display: flex;
            gap: 0.75rem;
            padding: 0.75rem;
            transition: background-color 200ms ease, border-color 200ms ease;
        }

        .option-card:hover {
            background: rgba(15, 23, 42, 0.85);
            border-color: rgba(100, 116, 139, 0.8);
        }

        .option-grid {
            display: grid;
            gap: 0.75rem;
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .animate-pulse-slow {
            animation: pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }

        @keyframes status-ping {
            0% {
                transform: scale(1);
                opacity: 1;
            }

            75%,
            100% {
                transform: scale(2);
                opacity: 0;
            }
        }

        @media (min-width: 640px) {
            .panel {
                padding: 1.5rem;
            }

            .logo-mark {
                height: 2.75rem;
                width: 2.75rem;
            }

            .banner-container {
                left: auto;
                max-width: 20rem;
                right: 1rem;
            }
        }

        @media (max-width: 640px) {
            .mobile-scroll {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }
        }
    </style>
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen overflow-x-hidden">

    <!-- Mobile-First Header -->
    <header class="glass sticky top-0 z-50 border-b border-slate-800/80">
        <div class="max-w-7xl mx-auto px-4 py-3 sm:py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="logo-mark text-base sm:text-lg">
                        R
                    </div>
                        <div>
                            <h1 class="text-base sm:text-xl font-semibold text-slate-50">
                                Routing Dashboard
                            </h1>
                            <p class="text-xs sm:text-sm text-slate-400 hidden sm:block">
                                Performance Optimized • PWA Ready
                            </p>
                            <p class="text-xs text-slate-400 sm:hidden">Mobile Optimized</p>
                        </div>
                </div>

                <div class="flex items-center space-x-2 sm:space-x-4">
                    <div class="flex items-center space-x-1 sm:space-x-2">
                        <div class="w-2 h-2 sm:w-3 sm:h-3 bg-emerald-400 rounded-full animate-pulse-slow"></div>
                        <span class="text-xs sm:text-sm text-slate-400 hidden sm:inline">Online</span>
                        <span class="text-xs text-emerald-400 font-medium sm:hidden">●</span>
                    </div>

                    <button
                        onclick="location.reload()"
                        class="btn btn-muted touch-target text-xs sm:text-sm"
                    >
                        <svg
                            class="w-4 h-4"
                            viewBox="0 0 24 24"
                            fill="none"
                            xmlns="http://www.w3.org/2000/svg"
                        >
                            <path
                                d="M21 12a9 9 0 10-1.67 5.19"
                                stroke="currentColor"
                                stroke-width="1.5"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            ></path>
                            <path
                                d="M21 5v6h-6"
                                stroke="currentColor"
                                stroke-width="1.5"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            ></path>
                        </svg>
                        <span class="hidden sm:inline">Refresh</span>
                    </button>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 py-4 sm:py-8 space-y-4 sm:space-y-6">

        <!-- Success Messages -->
        <?php if ($successMessage !== null) : ?>
            <div class="glass panel card-hover alert-success transition-all duration-300">
                <div class="flex items-center space-x-3">
                    <svg
                        class="w-5 h-5 text-emerald-300 flex-shrink-0"
                        viewBox="0 0 24 24"
                        fill="none"
                        xmlns="http://www.w3.org/2000/svg"
                    >
                        <path
                            d="M5 13l4 4L19 7"
                            stroke="currentColor"
                            stroke-width="1.5"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        />
                    </svg>
                    <span class="text-emerald-100 font-medium text-sm sm:text-base">
                        <?php echo $successMessage; ?>
                    </span>
                </div>
            </div>
        <?php endif; ?>

        <!-- System Status Card -->
        <div class="glass panel panel-lg card-hover">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
                <div>
                    <h2 class="text-lg sm:text-xl font-semibold text-slate-100 flex items-center gap-2">
                        <span class="status-dot">
                            <span class="status-ping"></span>
                            <span class="status-dot-inner"></span>
                        </span>
                        <span>System Status</span>
                    </h2>
                    <p class="text-xs sm:text-sm text-slate-400 mt-1">Real-time monitoring • Performance optimized</p>
                </div>
                <div class="text-xs sm:text-sm text-slate-400 text-left sm:text-right">
                    <div>Last updated</div>
                    <div class="font-mono tracking-tight text-slate-200"><?php echo date('H:i:s'); ?></div>
                </div>
            </div>

            <!-- Mobile-first metrics grid -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4 mb-6">
                <div class="metric-card">
                    <div class="text-lg sm:text-xl lg:text-2xl font-semibold text-slate-50 leading-tight">
                        <?php echo $config['system_on'] ? 'ON' : 'OFF'; ?>
                    </div>
                    <div class="text-xs sm:text-sm text-slate-400 mt-1">System</div>
                </div>

                <div class="metric-card">
                    <div class="text-lg sm:text-xl lg:text-2xl font-semibold text-slate-50 leading-tight">
                        <?php echo $config['is_active'] ? 'Active' : 'Inactive'; ?>
                    </div>
                    <div class="text-xs sm:text-sm text-slate-400 mt-1">Status</div>
                </div>

                <div class="metric-card">
                    <div class="text-lg sm:text-xl lg:text-2xl font-semibold text-slate-50 leading-tight">
                        <?php echo number_format($stats['success_rate'], 1); ?>%
                    </div>
                    <div class="text-xs sm:text-sm text-slate-400 mt-1">Success</div>
                </div>

                <div class="metric-card">
                    <div class="text-lg sm:text-xl lg:text-2xl font-semibold text-slate-50 leading-tight">
                        <?php echo $stats['avg_time']; ?>ms
                    </div>
                    <div class="text-xs sm:text-sm text-slate-400 mt-1">Response</div>
                </div>
            </div>

            <!-- Feature badges -->
            <div class="border-t border-slate-800 pt-4 mt-4">
                <div class="badge-stack">
                    <span class="badge">
                        <svg
                            class="w-3.5 h-3.5"
                            viewBox="0 0 24 24"
                            fill="none"
                            xmlns="http://www.w3.org/2000/svg"
                        >
                            <path
                                d="M7 4h10a2 2 0 012 2v12a2 2 0 01-2 2H7a2 2 0 01-2-2V6a2 2 0 012-2z"
                                stroke="currentColor"
                                stroke-width="1.5"
                            />
                            <path
                                d="M9 18h6"
                                stroke="currentColor"
                                stroke-width="1.5"
                                stroke-linecap="round"
                            />
                        </svg>
                        Mobile-First
                    </span>
                    <span class="badge">
                        <svg
                            class="w-3.5 h-3.5"
                            viewBox="0 0 24 24"
                            fill="none"
                            xmlns="http://www.w3.org/2000/svg"
                        >
                            <path
                                d="M12 3v18"
                                stroke="currentColor"
                                stroke-width="1.5"
                                stroke-linecap="round"
                            />
                            <path
                                d="M5 9l7-6 7 6"
                                stroke="currentColor"
                                stroke-width="1.5"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            />
                        </svg>
                        PWA Ready
                    </span>
                    <span class="badge">
                        <svg
                            class="w-3.5 h-3.5"
                            viewBox="0 0 24 24"
                            fill="none"
                            xmlns="http://www.w3.org/2000/svg"
                        >
                            <path
                                d="M13 3h-2l-1 5h4l-1-5z"
                                stroke="currentColor"
                                stroke-width="1.5"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            />
                            <path
                                d="M5 21l2-9h10l2 9H5z"
                                stroke="currentColor"
                                stroke-width="1.5"
                                stroke-linejoin="round"
                            />
                        </svg>
                        Performance Optimized
                    </span>
                    <span class="badge">
                        <svg
                            class="w-3.5 h-3.5"
                            viewBox="0 0 24 24"
                            fill="none"
                            xmlns="http://www.w3.org/2000/svg"
                        >
                            <path
                                d="M12 6l7 5-7 5-7-5 7-5z"
                                stroke="currentColor"
                                stroke-width="1.5"
                                stroke-linejoin="round"
                            />
                            <path
                                d="M12 21V11"
                                stroke="currentColor"
                                stroke-width="1.5"
                                stroke-linecap="round"
                            />
                        </svg>
                        Touch Optimized
                    </span>
                </div>
            </div>
        </div>

        <!-- Configuration Panel -->
        <div class="glass panel panel-lg card-hover transition-all duration-300">
            <h2 class="text-lg sm:text-xl font-semibold text-slate-100 mb-4 sm:mb-6">System Configuration</h2>

            <form method="POST" class="space-y-4 sm:space-y-6" novalidate>
                <input type="hidden" name="action" value="update_config">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">
                    <div class="control-card">
                        <h3 class="font-medium text-slate-100 mb-3">System Controls</h3>
                        <div class="space-y-3">
                            <label class="toggle-row touch-target">
                                <span class="text-sm font-medium text-slate-200">System ON</span>
                                <input
                                    type="checkbox"
                                    name="system_on"
                                    <?php echo $systemOnChecked; ?>
                                    class="checkbox-control"
                                >
                            </label>
                            <label class="toggle-row touch-target">
                                <span class="text-sm font-medium text-slate-200">Activity Status</span>
                                <input
                                    type="checkbox"
                                    name="is_active"
                                    <?php echo $isActiveChecked; ?>
                                    class="checkbox-control"
                                >
                            </label>
                        </div>
                    </div>

                    <div class="control-card">
                        <h3 class="font-medium text-slate-100 mb-3">Current Status</h3>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between text-slate-300">
                                <span>URLs</span>
                                <span class="font-semibold text-slate-100"><?php echo count($urls); ?></span>
                            </div>
                            <div class="flex justify-between text-slate-300">
                                <span>Countries</span>
                                <span class="font-semibold text-slate-100"><?php echo count($countries); ?></span>
                            </div>
                            <div class="flex justify-between text-slate-300">
                                <span>Success</span>
                                <span class="font-semibold text-slate-100"><?php echo $stats['success_rate']; ?>%</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Routing Rules -->
                <div class="control-card">
                    <h3 class="font-medium text-slate-100 mb-4">Routing Rules</h3>
                    <div class="space-y-4">
                        <label class="option-card touch-target">
                            <input
                                type="radio"
                                name="rule_type"
                                value="static_route"
                                <?php echo $ruleType === 'static_route' ? 'checked' : ''; ?>
                                class="mt-1 text-slate-50 focus:ring-slate-100/20 border-slate-600 bg-slate-950"
                            >
                            <div>
                                <div class="font-medium text-slate-100 flex items-center gap-2">
                                    <svg
                                        class="w-4 h-4"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        xmlns="http://www.w3.org/2000/svg"
                                    >
                                        <path
                                            d="M5 12h14"
                                            stroke="currentColor"
                                            stroke-width="1.5"
                                            stroke-linecap="round"
                                        />
                                        <path
                                            d="M15 6l4 6-4 6"
                                            stroke="currentColor"
                                            stroke-width="1.5"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                        />
                                    </svg>
                                    Static Routing
                                </div>
                                <div class="text-sm text-slate-400">Always routes to highest priority URL</div>
                            </div>
                        </label>

                        <label class="option-card touch-target">
                            <input
                                type="radio"
                                name="rule_type"
                                value="random_route"
                                <?php echo $ruleType === 'random_route' ? 'checked' : ''; ?>
                                class="mt-1 text-slate-50 focus:ring-slate-100/20 border-slate-600 bg-slate-950"
                            >
                            <div>
                                <div class="font-medium text-slate-100 flex items-center gap-2">
                                    <svg
                                        class="w-4 h-4"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        xmlns="http://www.w3.org/2000/svg"
                                    >
                                        <path
                                            d="M5 5l14 14"
                                            stroke="currentColor"
                                            stroke-width="1.5"
                                            stroke-linecap="round"
                                        />
                                        <path
                                            d="M5 19l14-14"
                                            stroke="currentColor"
                                            stroke-width="1.5"
                                            stroke-linecap="round"
                                        />
                                    </svg>
                                    Random Routing
                                </div>
                                <div class="text-sm text-slate-400">Random selection based on weight</div>
                            </div>
                        </label>

                        <label class="option-card touch-target">
                            <input
                                type="radio"
                                name="rule_type"
                                value="mute_unmute"
                                <?php echo $ruleType === 'mute_unmute' ? 'checked' : ''; ?>
                                class="mt-1 text-slate-50 focus:ring-slate-100/20 border-slate-600 bg-slate-950"
                            >
                            <div class="flex-1">
                                <div class="font-medium text-slate-100 flex items-center gap-2">
                                    <svg
                                        class="w-4 h-4"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        xmlns="http://www.w3.org/2000/svg"
                                    >
                                        <path
                                            d="M4 4v6h6"
                                            stroke="currentColor"
                                            stroke-width="1.5"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                        />
                                        <path
                                            d="M20 20v-6h-6"
                                            stroke="currentColor"
                                            stroke-width="1.5"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                        />
                                        <path
                                            d="M6 18l12-12"
                                            stroke="currentColor"
                                            stroke-width="1.5"
                                            stroke-linecap="round"
                                        />
                                    </svg>
                                    Mute/Unmute Cycle
                                </div>
                                <div class="text-sm text-slate-400 mt-1">Toggles between targeting and normal</div>
                                <div class="mt-3 option-grid">
                                    <div>
                                        <label class="field-label">Mute (seconds)</label>
                                        <input
                                            type="number"
                                            name="mute_duration"
                                            value="<?php echo $config['mute_duration']; ?>"
                                            min="30"
                                            max="600"
                                            class="touch-target input-field"
                                        >
                                    </div>
                                    <div>
                                        <label class="field-label">Unmute (seconds)</label>
                                        <input
                                            type="number"
                                            name="unmute_duration"
                                            value="<?php echo $config['unmute_duration']; ?>"
                                            min="30"
                                            max="600"
                                            class="touch-target input-field"
                                        >
                                    </div>
                                </div>
                            </div>
                        </label>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary touch-target w-full">
                    Save Configuration
                </button>
            </form>
        </div>

        <!-- URL Management -->
        <div class="glass panel panel-lg card-hover transition-all duration-300">
            <h2 class="text-lg sm:text-xl font-semibold text-slate-100 mb-4 sm:mb-6">Target URLs</h2>

            <form method="POST" class="mb-6 space-y-3 sm:space-y-4" novalidate>
                <input type="hidden" name="action" value="add_url">
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-12 sm:gap-4">
                    <input
                        type="url"
                        name="url"
                        placeholder="https://your-target-site.com"
                        required
                        class="touch-target input-field sm:col-span-6"
                    >
                    <input
                        type="number"
                        name="weight"
                        placeholder="Weight"
                        value="1"
                        min="1"
                        max="100"
                        class="touch-target input-field sm:col-span-3"
                    >
                    <input
                        type="number"
                        name="priority"
                        placeholder="Priority"
                        value="0"
                        min="0"
                        max="999"
                        class="touch-target input-field sm:col-span-3"
                    >
                </div>
                <button type="submit" class="btn btn-primary touch-target">
                    <svg
                        class="w-4 h-4"
                        viewBox="0 0 24 24"
                        fill="none"
                        xmlns="http://www.w3.org/2000/svg"
                    >
                        <path
                            d="M12 5v14"
                            stroke="currentColor"
                            stroke-width="1.5"
                            stroke-linecap="round"
                        />
                        <path
                            d="M5 12h14"
                            stroke="currentColor"
                            stroke-width="1.5"
                            stroke-linecap="round"
                        />
                    </svg>
                    Add URL
                </button>
            </form>

            <div class="space-y-3">
                <?php foreach ($urls as $url) : ?>
                    <?php $statusClass = $url['active'] ? 'text-emerald-300' : 'text-rose-300'; ?>
                    <div class="control-card">
                        <div class="font-medium text-slate-100 truncate">
                            <a
                                href="<?php echo htmlspecialchars($url['url']); ?>"
                                target="_blank"
                                class="hover:underline"
                            >
                                <?php echo htmlspecialchars($url['url']); ?>
                            </a>
                        </div>
                        <div class="text-sm text-slate-400 mt-1">
                            Weight: <?php echo $url['weight']; ?> • Priority: <?php echo $url['priority']; ?>
                            <span class="ml-2 flex items-center gap-1 font-medium <?php echo $statusClass; ?>">
                                <span class="text-base leading-none">●</span>
                                <?php echo $url['active'] ? 'Active' : 'Inactive'; ?>
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Country Management -->
        <div class="glass panel panel-lg card-hover transition-all duration-300">
            <h2 class="text-lg sm:text-xl font-semibold text-slate-100 mb-4 sm:mb-6">Country Targeting</h2>

            <form method="POST" class="mb-6 space-y-3" novalidate>
                <input type="hidden" name="action" value="update_countries">
                <div class="space-y-3">
                    <label class="block text-sm font-medium text-slate-200">Country Codes (ISO 3166-1)</label>
                    <textarea
                        name="countries"
                        rows="3"
                        placeholder="US, UK, DE, FR, ID, JP, CN, IN, CA, AU"
                        class="touch-target textarea-field resize-none"
                    ><?php echo $activeCountryCodes; ?></textarea>
                    <p class="text-xs text-slate-400">Comma-separated country codes (e.g., US, UK, DE)</p>
                    <button type="submit" class="btn btn-primary touch-target">
                        <svg
                            class="w-4 h-4"
                            viewBox="0 0 24 24"
                            fill="none"
                            xmlns="http://www.w3.org/2000/svg"
                        >
                            <path
                                d="M5 12h14"
                                stroke="currentColor"
                                stroke-width="1.5"
                                stroke-linecap="round"
                            />
                            <path
                                d="M12 5v14"
                                stroke="currentColor"
                                stroke-width="1.5"
                                stroke-linecap="round"
                            />
                        </svg>
                        Update Countries
                    </button>
                </div>
            </form>

            <div>
                <h3 class="font-medium text-slate-100 mb-3">
                    Active Countries (<?php echo count($countries); ?>)
                </h3>
                <div class="flex flex-wrap gap-2">
                    <?php foreach ($countries as $country) : ?>
                        <span class="badge">
                            <span class="badge-code"><?php echo $country['code']; ?></span>
                            <?php echo $country['name']; ?>
                        </span>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

    </main>

    <!-- PWA Install Banner -->
    <div
        id="pwa-install-banner"
        class="banner-container glass panel text-slate-100 banner-transition"
    >
        <div class="flex items-center space-x-3">
            <div class="banner-icon">R</div>
            <div class="flex-1 min-w-0">
                <h4 class="font-semibold text-sm mb-1">Install Dashboard</h4>
                <p class="text-xs text-slate-400 mb-2">Add to home screen for native experience</p>
                <div class="flex space-x-2">
                    <button id="install-btn" class="btn btn-primary touch-target text-xs px-3 py-1.5">Install</button>
                    <button id="dismiss-btn" class="btn btn-ghost touch-target text-xs px-2 py-1.5">Later</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // PWA Installation
        let deferredPrompt;
        
        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            deferredPrompt = e;
            setTimeout(() => {
                document.getElementById('pwa-install-banner').style.transform = 'translateY(0)';
            }, 3000);
        });
        
        document.getElementById('install-btn').addEventListener('click', async () => {
            if (deferredPrompt) {
                deferredPrompt.prompt();
                const { outcome } = await deferredPrompt.userChoice;
                deferredPrompt = null;
                document.getElementById('pwa-install-banner').style.transform = 'translateY(100%)';
            }
        });
        
        document.getElementById('dismiss-btn').addEventListener('click', () => {
            document.getElementById('pwa-install-banner').style.transform = 'translateY(100%)';
        });
        
        // Touch feedback
        document.querySelectorAll('.touch-target').forEach((element) => {
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
