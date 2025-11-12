<?php
/**
 * Dashboard Routing System - Mobile-First PWA
 * Complete responsive dashboard with glass morphism UI
 */

// Mock configuration data (replace with database in production)
$config = [
    'system_on' => false,
    'is_active' => false,
    'rule_type' => 'static_route',
    'mute_duration' => 120,
    'unmute_duration' => 120,
    'current_state' => 'normal'
];

$urls = [];

$countries = [];

$stats = [
    'total_requests' => 1250,
    'target_decisions' => 875,
    'success_rate' => 95.2,
    'avg_time' => 45
];

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'update_config':
                // Configuration update logic here
                header('Location: index.php?success=config_updated');
                exit;
            case 'add_url':
                // URL addition logic here
                header('Location: index.php?success=url_added');
                exit;
            case 'update_countries':
                // Countries update logic here
                header('Location: index.php?success=countries_updated');
                exit;
        }
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
        /* Mobile-first optimizations */
        .glass { 
            backdrop-filter: blur(12px); 
            background: rgba(255, 255, 255, 0.85);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        .touch-target { 
            min-height: 44px; 
            min-width: 44px; 
        }
        .animate-pulse-slow { 
            animation: pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite; 
        }
        
        @media (max-width: 640px) {
            .mobile-scroll { 
                overflow-x: auto; 
                -webkit-overflow-scrolling: touch; 
            }
        }
        
        /* Performance optimizations */
        * { 
            -webkit-tap-highlight-color: transparent; 
        }
        
        .card-hover:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body class="bg-gradient-to-br from-blue-50 via-white to-purple-50 min-h-screen overflow-x-hidden">

    <!-- Mobile-First Header -->
    <header class="glass sticky top-0 z-50 border-b border-white/20">
        <div class="max-w-7xl mx-auto px-4 py-3 sm:py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 sm:w-10 sm:h-10 bg-gradient-to-br from-blue-600 to-purple-600 rounded-xl flex items-center justify-center text-white font-bold text-sm sm:text-base shadow-lg">
                        R
                    </div>
                    <div>
                        <h1 class="text-base sm:text-xl font-bold text-gray-900">Routing Dashboard</h1>
                        <p class="text-xs sm:text-sm text-gray-600 hidden sm:block">Performance Optimized • PWA Ready</p>
                        <p class="text-xs text-gray-600 sm:hidden">Mobile Optimized</p>
                    </div>
                </div>
                
                <div class="flex items-center space-x-2 sm:space-x-4">
                    <div class="flex items-center space-x-1 sm:space-x-2">
                        <div class="w-2 h-2 sm:w-3 sm:h-3 bg-emerald-500 rounded-full animate-pulse-slow"></div>
                        <span class="text-xs sm:text-sm text-gray-600 hidden sm:inline">Online</span>
                        <span class="text-xs text-emerald-600 font-medium sm:hidden">●</span>
                    </div>
                    
                    <button onclick="location.reload()" 
                            class="touch-target text-xs sm:text-sm bg-blue-50 hover:bg-blue-100 text-blue-600 px-2 sm:px-3 py-1.5 sm:py-2 rounded-lg transition-colors shadow-sm">
                        <svg class="w-3 h-3 sm:w-4 sm:h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
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
        <?php if (isset($_GET['success'])): ?>
            <div class="glass rounded-xl p-4 border border-green-200 bg-green-50/80 card-hover transition-all duration-300">
                <div class="flex items-center space-x-2">
                    <svg class="w-5 h-5 text-green-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span class="text-green-800 font-medium text-sm sm:text-base">
                        <?php
                        switch ($_GET['success']) {
                            case 'config_updated': echo 'Configuration updated successfully!'; break;
                            case 'url_added': echo 'URL added successfully!'; break;
                            case 'countries_updated': echo 'Countries updated successfully!'; break;
                            default: echo 'Operation completed successfully!'; break;
                        }
                        ?>
                    </span>
                </div>
            </div>
        <?php endif; ?>

        <!-- System Status Card -->
        <div class="glass rounded-xl sm:rounded-2xl p-4 sm:p-6 shadow-lg hover:shadow-xl transition-all duration-300 card-hover">
            <div class="flex items-center justify-between mb-4 sm:mb-6">
                <div>
                    <h2 class="text-lg sm:text-xl font-bold text-gray-900 flex items-center space-x-2">
                        <div class="w-3 h-3 bg-emerald-500 rounded-full animate-pulse-slow"></div>
                        <span>System Status</span>
                    </h2>
                    <p class="text-xs sm:text-sm text-gray-600 mt-1">Real-time monitoring • Performance optimized</p>
                </div>
                <div class="text-xs sm:text-sm text-gray-500 text-right">
                    <div>Last updated</div>
                    <div class="font-mono"><?php echo date('H:i:s'); ?></div>
                </div>
            </div>

            <!-- Mobile-first metrics grid -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4 mb-6">
                <div class="text-center p-3 sm:p-4 bg-gradient-to-br from-slate-50 to-slate-100/50 rounded-xl border border-slate-200/50 hover:border-slate-300/50 transition-all duration-200 hover:scale-105 transform cursor-default">
                    <div class="text-lg sm:text-xl lg:text-2xl font-bold text-slate-900 leading-tight">
                        <?php echo $config['system_on'] ? 'ON' : 'OFF'; ?>
                    </div>
                    <div class="text-xs sm:text-sm text-slate-500 mt-1">System</div>
                </div>
                
                <div class="text-center p-3 sm:p-4 bg-gradient-to-br from-blue-50 to-blue-100/50 rounded-xl border border-blue-200/50 hover:border-blue-300/50 transition-all duration-200 hover:scale-105 transform cursor-default">
                    <div class="text-lg sm:text-xl lg:text-2xl font-bold text-blue-700 leading-tight">
                        <?php echo $config['is_active'] ? 'Active' : 'Inactive'; ?>
                    </div>
                    <div class="text-xs sm:text-sm text-blue-600 mt-1">Status</div>
                </div>
                
                <div class="text-center p-3 sm:p-4 bg-gradient-to-br from-emerald-50 to-emerald-100/50 rounded-xl border border-emerald-200/50 hover:border-emerald-300/50 transition-all duration-200 hover:scale-105 transform cursor-default">
                    <div class="text-lg sm:text-xl lg:text-2xl font-bold text-emerald-700 leading-tight">
                        <?php echo number_format($stats['success_rate'], 1); ?>%
                    </div>
                    <div class="text-xs sm:text-sm text-emerald-600 mt-1">Success</div>
                </div>
                
                <div class="text-center p-3 sm:p-4 bg-gradient-to-br from-purple-50 to-purple-100/50 rounded-xl border border-purple-200/50 hover:border-purple-300/50 transition-all duration-200 hover:scale-105 transform cursor-default">
                    <div class="text-lg sm:text-xl lg:text-2xl font-bold text-purple-700 leading-tight">
                        <?php echo $stats['avg_time']; ?>ms
                    </div>
                    <div class="text-xs sm:text-sm text-purple-600 mt-1">Response</div>
                </div>
            </div>

            <!-- Feature badges -->
            <div class="border-t border-slate-200 pt-4 mt-4">
                <div class="flex flex-wrap gap-1.5 sm:gap-2">
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">📱 Mobile-First</span>
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">⚡ PWA Ready</span>
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">🚀 Performance Optimized</span>
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-orange-100 text-orange-800">🎯 Touch Optimized</span>
                </div>
            </div>
        </div>

        <!-- Configuration Panel -->
        <div class="glass rounded-xl sm:rounded-2xl p-4 sm:p-6 shadow-lg card-hover transition-all duration-300">
            <h2 class="text-lg sm:text-xl font-bold text-gray-900 mb-4 sm:mb-6">System Configuration</h2>
            
            <form method="POST" class="space-y-4 sm:space-y-6">
                <input type="hidden" name="action" value="update_config">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">
                    <div class="p-4 rounded-lg border border-gray-200 bg-gradient-to-br from-gray-50/50 to-gray-100/30">
                        <h3 class="font-semibold text-gray-900 mb-3">System Controls</h3>
                        <div class="space-y-3">
                            <label class="flex items-center touch-target cursor-pointer hover:bg-gray-50 p-2 rounded-lg transition-colors">
                                <input type="checkbox" name="system_on" <?php echo $config['system_on'] ? 'checked' : ''; ?> 
                                       class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 w-4 h-4">
                                <span class="ml-3 text-sm font-medium text-gray-700">System ON</span>
                            </label>
                            <label class="flex items-center touch-target cursor-pointer hover:bg-gray-50 p-2 rounded-lg transition-colors">
                                <input type="checkbox" name="is_active" <?php echo $config['is_active'] ? 'checked' : ''; ?>
                                       class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 w-4 h-4">
                                <span class="ml-3 text-sm font-medium text-gray-700">Activity Status</span>
                            </label>
                        </div>
                    </div>
                    
                    <div class="p-4 rounded-lg border border-gray-200 bg-gradient-to-br from-blue-50/50 to-blue-100/30">
                        <h3 class="font-semibold text-gray-900 mb-3">Current Status</h3>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-600">URLs:</span>
                                <span class="font-semibold text-blue-700"><?php echo count($urls); ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Countries:</span>
                                <span class="font-semibold text-blue-700"><?php echo count($countries); ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Success:</span>
                                <span class="font-semibold text-emerald-700"><?php echo $stats['success_rate']; ?>%</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Routing Rules -->
                <div class="p-4 rounded-lg border border-gray-200 bg-gradient-to-br from-purple-50/50 to-purple-100/30">
                    <h3 class="font-semibold text-gray-900 mb-4">Routing Rules</h3>
                    <div class="space-y-4">
                        <label class="flex items-start touch-target cursor-pointer hover:bg-white/50 p-3 rounded-lg transition-colors">
                            <input type="radio" name="rule_type" value="static_route" 
                                   <?php echo $config['rule_type'] === 'static_route' ? 'checked' : ''; ?>
                                   class="mt-1 text-purple-600 focus:ring-purple-500">
                            <div class="ml-3">
                                <div class="font-medium text-gray-900">🎯 Static Routing</div>
                                <div class="text-sm text-gray-600">Always routes to highest priority URL</div>
                            </div>
                        </label>
                        
                        <label class="flex items-start touch-target cursor-pointer hover:bg-white/50 p-3 rounded-lg transition-colors">
                            <input type="radio" name="rule_type" value="random_route" 
                                   <?php echo $config['rule_type'] === 'random_route' ? 'checked' : ''; ?>
                                   class="mt-1 text-purple-600 focus:ring-purple-500">
                            <div class="ml-3">
                                <div class="font-medium text-gray-900">🎲 Random Routing</div>
                                <div class="text-sm text-gray-600">Random selection based on weight</div>
                            </div>
                        </label>
                        
                        <label class="flex items-start touch-target cursor-pointer hover:bg-white/50 p-3 rounded-lg transition-colors">
                            <input type="radio" name="rule_type" value="mute_unmute" 
                                   <?php echo $config['rule_type'] === 'mute_unmute' ? 'checked' : ''; ?>
                                   class="mt-1 text-purple-600 focus:ring-purple-500">
                            <div class="ml-3 flex-1">
                                <div class="font-medium text-gray-900">🔄 Mute/Unmute Cycle</div>
                                <div class="text-sm text-gray-600 mt-1">Toggles between targeting and normal</div>
                                <div class="mt-3 grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 mb-1">Mute (seconds)</label>
                                        <input type="number" name="mute_duration" value="<?php echo $config['mute_duration']; ?>" 
                                               min="30" max="600" class="touch-target w-full text-xs border rounded-lg px-2 py-1.5">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 mb-1">Unmute (seconds)</label>
                                        <input type="number" name="unmute_duration" value="<?php echo $config['unmute_duration']; ?>" 
                                               min="30" max="600" class="touch-target w-full text-xs border rounded-lg px-2 py-1.5">
                                    </div>
                                </div>
                            </div>
                        </label>
                    </div>
                </div>

                <button type="submit" class="touch-target w-full bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white py-3 sm:py-4 px-4 rounded-xl font-semibold transition-all duration-200 shadow-lg hover:shadow-xl">
                    Save Configuration
                </button>
            </form>
        </div>

        <!-- URL Management -->
        <div class="glass rounded-xl sm:rounded-2xl p-4 sm:p-6 shadow-lg card-hover transition-all duration-300">
            <h2 class="text-lg sm:text-xl font-bold text-gray-900 mb-4 sm:mb-6">Target URLs</h2>
            
            <form method="POST" class="mb-6">
                <input type="hidden" name="action" value="add_url">
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-12 sm:gap-4">
                    <input type="url" name="url" placeholder="https://your-target-site.com" required
                           class="touch-target sm:col-span-6 border border-gray-300 rounded-lg px-3 py-2.5 focus:ring-2 focus:ring-emerald-500">
                    <input type="number" name="weight" placeholder="Weight" value="1" min="1" max="100"
                           class="touch-target sm:col-span-3 border border-gray-300 rounded-lg px-3 py-2.5 focus:ring-2 focus:ring-emerald-500">
                    <input type="number" name="priority" placeholder="Priority" value="0" min="0" max="999"
                           class="touch-target sm:col-span-3 border border-gray-300 rounded-lg px-3 py-2.5 focus:ring-2 focus:ring-emerald-500">
                </div>
                <button type="submit" class="touch-target mt-3 bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2.5 rounded-lg transition-colors font-medium shadow-md">
                    Add URL
                </button>
            </form>

            <div class="space-y-3">
                <?php foreach ($urls as $url): ?>
                    <div class="border border-gray-200 rounded-lg p-3 sm:p-4 bg-white/50 hover:bg-white/80 transition-colors">
                        <div class="font-medium text-blue-600 truncate">
                            <a href="<?php echo htmlspecialchars($url['url']); ?>" target="_blank" class="hover:underline">
                                <?php echo htmlspecialchars($url['url']); ?>
                            </a>
                        </div>
                        <div class="text-sm text-gray-500 mt-1">
                            Weight: <?php echo $url['weight']; ?> • Priority: <?php echo $url['priority']; ?>
                            <span class="ml-2 <?php echo $url['active'] ? 'text-emerald-600' : 'text-red-600'; ?> font-medium">
                                <?php echo $url['active'] ? '● Active' : '● Inactive'; ?>
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?> 
            </div>
        </div>
 
        <!-- Country Management -->
        <div class="glass rounded-xl sm:rounded-2xl p-4 sm:p-6 shadow-lg card-hover transition-all duration-300">
            <h2 class="text-lg sm:text-xl font-bold text-gray-900 mb-4 sm:mb-6">Country Targeting</h2>
            
            <form method="POST" class="mb-6">
                <input type="hidden" name="action" value="update_countries">
                <div class="space-y-3">
                    <label class="block text-sm font-medium text-gray-700">Country Codes (ISO 3166-1)</label>
                    <textarea name="countries" rows="3" placeholder="US, UK, DE, FR, ID, JP, CN, IN, CA, AU"
                              class="touch-target w-full border border-gray-300 rounded-lg px-3 py-2.5 focus:ring-2 focus:ring-blue-500 resize-none"><?php 
                        echo implode(', ', array_column($countries, 'code')); 
                    ?></textarea>
                    <p class="text-xs text-gray-500">Comma-separated country codes (e.g., US, UK, DE)</p>
                    <button type="submit" class="touch-target bg-blue-600 hover:bg-blue-700 text-white px-4 py-2.5 rounded-lg transition-colors font-medium shadow-md">
                        Update Countries
                    </button>
                </div>
            </form>

            <div>
                <h3 class="font-medium text-gray-900 mb-3">Active Countries (<?php echo count($countries); ?>)</h3>
                <div class="flex flex-wrap gap-2">
                    <?php foreach ($countries as $country): ?>
                        <span class="inline-flex items-center px-3 py-1.5 rounded-full text-sm bg-blue-50 text-blue-800 border border-blue-200">
                            <span class="font-mono font-bold mr-2"><?php echo $country['code']; ?></span>
                            <?php echo $country['name']; ?>
                        </span>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

    </main>

    <!-- PWA Install Banner -->
    <div id="pwa-install-banner" class="fixed bottom-4 left-4 right-4 glass rounded-xl p-4 text-gray-900 shadow-2xl transform translate-y-full transition-transform duration-300 border border-white/30 sm:max-w-sm sm:left-auto sm:right-4">
        <div class="flex items-center space-x-3">
            <div class="w-10 h-10 bg-gradient-to-br from-blue-600 to-purple-600 rounded-lg flex items-center justify-center text-white font-bold text-sm shadow-lg">R</div>
            <div class="flex-1 min-w-0">
                <h4 class="font-semibold text-sm mb-1">Install Dashboard</h4>
                <p class="text-xs text-gray-600 mb-2">Add to home screen for native experience</p>
                <div class="flex space-x-2">
                    <button id="install-btn" class="touch-target text-xs bg-blue-600 hover:bg-blue-700 text-white px-3 py-1.5 rounded-lg transition-colors">Install</button>
                    <button id="dismiss-btn" class="touch-target text-xs text-gray-600 hover:text-gray-800 px-2 py-1.5">Later</button>
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
        document.querySelectorAll('.touch-target').forEach(element => {
            element.addEventListener('touchstart', function() {
                this.style.transform = 'scale(0.98)';
            });
            
            element.addEventListener('touchend', function() {
                this.style.transform = 'scale(1)';
            });
        });
        
        // Performance monitoring
        window.addEventListener('load', () => {
            if ('performance' in window) {
                const loadTime = performance.timing.loadEventEnd - performance.timing.navigationStart;
                console.log(`Page Load Time: ${loadTime}ms`);
            }
        });
    </script>
</body>
</html>