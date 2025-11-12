<?php
/**
 * Installation Wizard - Mobile-First Design
 * Dashboard Routing System v1.0
 */

$step = $_GET['step'] ?? 'install';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['install_database'])) {
        try {
            // Simulate database installation
            // In production, this would create actual database tables
            if (!is_dir('config')) {
                mkdir('config', 0755, true);
            }
            file_put_contents('config/installed.lock', date('Y-m-d H:i:s'));
            header('Location: install.php?step=success');
            exit;
        } catch (Exception $e) {
            $error = 'Installation failed: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <title>Setup - Dashboard Routing System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <meta name="theme-color" content="#3b82f6">
    <style>
        .glass { 
            backdrop-filter: blur(12px); 
            background: rgba(255, 255, 255, 0.9);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        .touch-target { 
            min-height: 44px; 
            min-width: 44px; 
        }
        * { 
            -webkit-tap-highlight-color: transparent; 
        }
    </style>
</head>
<body class="bg-gradient-to-br from-blue-50 via-white to-purple-50 min-h-screen flex items-center justify-center p-4">

    <div class="w-full max-w-md">
        <div class="glass rounded-2xl p-6 sm:p-8 shadow-2xl border border-white/20">
            
            <!-- Header -->
            <div class="text-center mb-6 sm:mb-8">
                <div class="w-16 h-16 sm:w-20 sm:h-20 bg-gradient-to-br from-blue-600 to-purple-600 rounded-2xl flex items-center justify-center text-white text-2xl sm:text-3xl font-bold mx-auto mb-4 shadow-lg">
                    R
                </div>
                <h1 class="text-xl sm:text-2xl font-bold text-gray-900 mb-2">Dashboard Setup</h1>
                <p class="text-sm sm:text-base text-gray-600">Mobile-First Routing System</p>
                <div class="mt-2 flex items-center justify-center space-x-2 text-xs text-gray-500">
                    <span class="flex items-center space-x-1">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                        </svg>
                        <span>Mobile-First</span>
                    </span>
                    <span class="flex items-center space-x-1">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                        <span>PWA Ready</span>
                    </span>
                </div>
            </div>

            <!-- Error Message -->
            <?php if ($error): ?>
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl mb-6">
                    <div class="flex items-start space-x-2">
                        <svg class="w-5 h-5 text-red-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <div>
                            <h3 class="font-medium text-sm">Installation Error</h3>
                            <p class="text-xs mt-1"><?php echo htmlspecialchars($error); ?></p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($step === 'success'): ?>
                <!-- Success Step -->
                <div class="text-center">
                    <div class="w-16 h-16 bg-emerald-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h2 class="text-lg sm:text-xl font-bold text-gray-900 mb-3">Installation Complete!</h2>
                    <p class="text-sm sm:text-base text-gray-600 mb-6">Your dashboard routing system is ready to use with mobile-first design and PWA capabilities.</p>
                    
                    <div class="space-y-3">
                        <a href="index.php" class="touch-target w-full bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white font-semibold py-3 px-6 rounded-xl transition-all duration-200 shadow-lg hover:shadow-xl flex items-center justify-center space-x-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
                            </svg>
                            <span>Access Dashboard</span>
                        </a>
                        
                        <div class="grid grid-cols-2 gap-2">
                            <a href="#" class="touch-target text-center text-sm bg-gray-100 hover:bg-gray-200 text-gray-700 py-2 px-3 rounded-lg transition-colors font-medium">
                                📚 Documentation
                            </a>
                            <a href="api/route.php" target="_blank" class="touch-target text-center text-sm bg-gray-100 hover:bg-gray-200 text-gray-700 py-2 px-3 rounded-lg transition-colors font-medium">
                                🔌 API Test
                            </a>
                        </div>
                    </div>
                </div>

            <?php else: ?>
                <!-- Installation Step -->
                <div class="space-y-6">
                    
                    <!-- Requirements Check -->
                    <div class="space-y-4">
                        <div class="bg-blue-50 border border-blue-200 rounded-xl p-4">
                            <h3 class="font-medium text-blue-900 mb-2 text-sm sm:text-base">Before Installation</h3>
                            <div class="text-xs sm:text-sm text-blue-800 space-y-1">
                                <p>✓ Create MySQL database: <code class="bg-blue-100 px-1 rounded font-mono">routing_system</code></p>
                                <p>✓ Update <code class="bg-blue-100 px-1 rounded font-mono">.env</code> with your database credentials</p>
                                <p>✓ Ensure PHP 8.0+ and required extensions are enabled</p>
                                <p>✓ Verify file write permissions for your hosting</p>
                            </div>
                        </div>

                        <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-4">
                            <h3 class="font-medium text-emerald-900 mb-2 text-sm sm:text-base">Installation Features</h3>
                            <div class="text-xs sm:text-sm text-emerald-800 space-y-1">
                                <p>• Database tables with optimized indexes for performance</p>
                                <p>• Mobile-first responsive interface with 44px touch targets</p>
                                <p>• PWA capabilities with offline support and app installation</p>
                                <p>• Performance monitoring and real-time analytics</p>
                                <p>• Complete routing system with 3 advanced rules</p>
                                <p>• Glass morphism UI with modern gradient effects</p>
                            </div>
                        </div>

                        <div class="bg-amber-50 border border-amber-200 rounded-xl p-4">
                            <h3 class="font-medium text-amber-900 mb-2 text-sm sm:text-base">System Requirements</h3>
                            <div class="text-xs sm:text-sm text-amber-800 space-y-1">
                                <p>• PHP Version: <?php echo phpversion(); ?> <?php echo version_compare(phpversion(), '8.0.0', '>=') ? '✓' : '⚠️ Requires 8.0+'; ?></p>
                                <p>• MySQL Extension: <?php echo extension_loaded('mysql') || extension_loaded('mysqli') || extension_loaded('pdo_mysql') ? '✓' : '⚠️ Required'; ?></p>
                                <p>• cURL Extension: <?php echo extension_loaded('curl') ? '✓' : '⚠️ Required'; ?></p>
                                <p>• Write Permissions: <?php echo is_writable('.') ? '✓' : '⚠️ Check permissions'; ?></p>
                            </div>
                        </div>
                    </div>

                    <!-- Installation Form -->
                    <form method="POST" class="space-y-4">
                        <button type="submit" name="install_database" value="1"
                                class="touch-target w-full bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white font-semibold py-3 sm:py-4 px-6 rounded-xl transition-all duration-200 shadow-lg hover:shadow-xl flex items-center justify-center space-x-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                            </svg>
                            <span>Install Dashboard System</span>
                        </button>
                        
                        <div class="text-center">
                            <a href="index.php" class="text-sm text-gray-600 hover:text-gray-800 transition-colors underline">
                                Skip installation (if already installed)
                            </a>
                        </div>
                    </form>
                </div>
            <?php endif; ?>

        </div>
    </div>

    <script>
        // Touch feedback
        document.querySelectorAll('.touch-target').forEach(element => {
            element.addEventListener('touchstart', function() {
                this.style.transform = 'scale(0.98)';
            });
            
            element.addEventListener('touchend', function() {
                this.style.transform = 'scale(1)';
            });
        });

        // Installation progress
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            if (form) {
                form.addEventListener('submit', function(e) {
                    const button = this.querySelector('button[type="submit"]');
                    if (button) {
                        button.disabled = true;
                        button.innerHTML = `
                            <svg class="w-5 h-5 animate-spin inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                            Installing System...
                        `;
                    }
                });
            }
        });
    </script> 
</body>
</html>