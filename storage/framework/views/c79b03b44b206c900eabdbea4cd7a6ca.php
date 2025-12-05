<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title><?php echo $__env->yieldContent('title', 'Boarding House Finder'); ?></title>

    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>

    <style>
        #map {
            height: 400px;
            border-radius: 12px;
        }

        /* Mid-Century Modern Color Palette */
        :root {
            --mcm-mustard: #D4A574;
            --mcm-teal: #4A7C7E;
            --mcm-coral: #D2691E;
            --mcm-olive: #6B8E23;
            --mcm-cream: #F5E6D3;
            --mcm-navy: #2C3E50;
            --mcm-terracotta: #CD853F;
            --mcm-turquoise: #5F9EA0;
            --mcm-burnt-orange: #E8A87C;
        }

        body {
            background-color: var(--mcm-cream) !important;
        }

        .bg-mcm-mustard {
            background-color: var(--mcm-mustard) !important;
        }

        .bg-mcm-teal {
            background-color: var(--mcm-teal) !important;
        }

        .bg-mcm-coral {
            background-color: var(--mcm-coral) !important;
        }

        .bg-mcm-olive {
            background-color: var(--mcm-olive) !important;
        }

        .bg-mcm-cream {
            background-color: var(--mcm-cream) !important;
        }

        .bg-mcm-navy {
            background-color: var(--mcm-navy) !important;
        }

        .bg-mcm-terracotta {
            background-color: var(--mcm-terracotta) !important;
        }

        .bg-mcm-turquoise {
            background-color: var(--mcm-turquoise) !important;
        }

        .bg-mcm-burnt-orange {
            background-color: var(--mcm-burnt-orange) !important;
        }

        .text-mcm-mustard {
            color: var(--mcm-mustard) !important;
        }

        .text-mcm-teal {
            color: var(--mcm-teal) !important;
        }

        .text-mcm-coral {
            color: var(--mcm-coral) !important;
        }

        .text-mcm-olive {
            color: var(--mcm-olive) !important;
        }

        .text-mcm-cream {
            color: var(--mcm-cream) !important;
        }

        .text-mcm-navy {
            color: var(--mcm-navy) !important;
        }

        .text-mcm-terracotta {
            color: var(--mcm-terracotta) !important;
        }

        .text-mcm-turquoise {
            color: var(--mcm-turquoise) !important;
        }

        .text-mcm-burnt-orange {
            color: var(--mcm-burnt-orange) !important;
        }

        .border-mcm-mustard {
            border-color: var(--mcm-mustard) !important;
        }

        .border-mcm-teal {
            border-color: var(--mcm-teal) !important;
        }

        .border-mcm-coral {
            border-color: var(--mcm-coral) !important;
        }

        .border-mcm-olive {
            border-color: var(--mcm-olive) !important;
        }

        .border-mcm-navy {
            border-color: var(--mcm-navy) !important;
        }

        .border-mcm-turquoise {
            border-color: var(--mcm-turquoise) !important;
        }

        .hover\:bg-mcm-mustard:hover {
            background-color: var(--mcm-mustard) !important;
        }

        .hover\:bg-mcm-teal:hover {
            background-color: var(--mcm-teal) !important;
        }

        .hover\:bg-mcm-coral:hover {
            background-color: var(--mcm-coral) !important;
        }

        .hover\:bg-mcm-olive:hover {
            background-color: var(--mcm-olive) !important;
        }

        .hover\:bg-mcm-turquoise:hover {
            background-color: var(--mcm-turquoise) !important;
        }

        .hover\:text-mcm-teal:hover {
            color: var(--mcm-teal) !important;
        }

        .hover\:text-mcm-turquoise:hover {
            color: var(--mcm-turquoise) !important;
        }

        .hover\:text-mcm-mustard:hover {
            color: var(--mcm-mustard) !important;
        }

        .hover\:text-mcm-coral:hover {
            color: var(--mcm-coral) !important;
        }

        .btn-mcm-primary {
            background-color: var(--mcm-teal) !important;
            color: white !important;
            transition: all 0.3s ease;
        }

        .btn-mcm-primary:hover {
            background-color: var(--mcm-turquoise) !important;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(74, 124, 126, 0.4);
        }

        .btn-mcm-secondary {
            background-color: var(--mcm-mustard) !important;
            color: var(--mcm-navy) !important;
            transition: all 0.3s ease;
        }

        .btn-mcm-secondary:hover {
            background-color: var(--mcm-burnt-orange) !important;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(212, 165, 116, 0.4);
        }

        .transition-smooth {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .card-hover {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .card-hover:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 25px rgba(44, 62, 80, 0.15);
        }

        /* Gradient backgrounds */
        .bg-gradient-mcm {
            background: linear-gradient(135deg, var(--mcm-teal) 0%, var(--mcm-turquoise) 50%, var(--mcm-olive) 100%);
        }

        /* Border utilities with opacity */
        .border-mcm-teal\/20 {
            border-color: rgba(74, 124, 126, 0.2) !important;
        }

        .border-mcm-teal\/30 {
            border-color: rgba(74, 124, 126, 0.3) !important;
        }

        .border-mcm-mustard\/20 {
            border-color: rgba(212, 165, 116, 0.2) !important;
        }

        .border-mcm-coral\/20 {
            border-color: rgba(210, 105, 30, 0.2) !important;
        }

        /* Text opacity utilities */
        .text-mcm-navy\/70 {
            color: rgba(44, 62, 80, 0.7) !important;
        }

        .text-mcm-navy\/80 {
            color: rgba(44, 62, 80, 0.8) !important;
        }

        .text-white\/90 {
            color: rgba(255, 255, 255, 0.9) !important;
        }

        /* Background opacity utilities */
        .bg-mcm-coral\/10 {
            background-color: rgba(210, 105, 30, 0.1) !important;
        }

        .bg-mcm-navy\/90 {
            background-color: rgba(44, 62, 80, 0.9) !important;
        }
    </style>
</head>

<body style="background-color: #F5E6D3;">
    <nav class="bg-white shadow-lg" style="border-bottom: 4px solid #4A7C7E;">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-20">
                <div class="flex">
                    <div class="flex-shrink-0 flex items-center">
                        <a href="/" style="font-size: 1.5rem; font-weight: bold; color: #4A7C7E;"
                            class="hover:opacity-80 transition-smooth">Boardmate</a>
                    </div>
                    <div class="hidden sm:ml-8 sm:flex sm:space-x-6">
                        <a href="/" style="border-bottom: 3px solid #4A7C7E; color: #2C3E50;"
                            class="inline-flex items-center px-3 pt-1 text-sm font-semibold transition-smooth hover:opacity-80">
                            Browse Properties
                        </a>
                        <a href="/properties/create" style="color: #2C3E50;"
                            class="border-transparent inline-flex items-center px-3 pt-1 border-b-3 text-sm font-semibold transition-smooth hover:opacity-80">
                            Add Property
                        </a>
                        <a href="/my-properties" style="color: #2C3E50;"
                            class="border-transparent inline-flex items-center px-3 pt-1 border-b-3 text-sm font-semibold transition-smooth hover:opacity-80">
                            My Properties
                        </a>
                        <a href="/favorites" style="color: #2C3E50;"
                            class="border-transparent inline-flex items-center px-3 pt-1 border-b-3 text-sm font-semibold transition-smooth hover:opacity-80">
                            Favorites
                        </a>
                        <span id="adminLink" class="hidden">
                            <a href="/admin" style="color: #2C3E50;"
                                class="border-transparent inline-flex items-center px-3 pt-1 border-b-3 text-sm font-semibold transition-smooth hover:opacity-80">
                                Admin Dashboard
                            </a>
                        </span>
                    </div>
                </div>
                <div class="flex items-center gap-3" id="authSection">
                    <span id="userWelcome" style="color: #2C3E50;" class="font-medium mr-2 hidden"></span>
                    <button id="logoutBtn" onclick="logout()" style="background-color: #D2691E; color: white;"
                        class="px-5 py-2 rounded-lg font-semibold shadow-md transition-smooth hidden hover:opacity-90">Logout</button>
                    <a href="/login" id="loginLink" style="color: #2C3E50;"
                        class="px-4 py-2 rounded-lg text-sm font-semibold transition-smooth hover:opacity-80">Login</a>
                    <a href="/register" id="registerLink"
                        class="btn-mcm-primary px-6 py-2 rounded-lg font-semibold shadow-md">Register</a>
                </div>
            </div>
        </div>
    </nav>

    <main>
        <?php echo $__env->yieldContent('content'); ?>
    </main>

    <footer style="background-color: #2C3E50; color: #F5E6D3; border-top: 4px solid #4A7C7E;" class="mt-16">
        <div class="max-w-7xl mx-auto py-10 px-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-6">
                <div>
                    <h3 style="color: #D4A574;" class="text-xl font-bold mb-3">About Us</h3>
                    <p style="color: rgba(245, 230, 211, 0.8);">Find your perfect boarding house in Tacloban City.
                        Connecting tenants with quality accommodations.</p>
                </div>
                <div>
                    <h3 style="color: #D4A574;" class="text-xl font-bold mb-3">Quick Links</h3>
                    <ul class="space-y-2">
                        <li><a href="/" style="color: rgba(245, 230, 211, 0.8);"
                                class="hover:opacity-80 transition-smooth">Browse Properties</a></li>
                        <li><a href="/properties/create" style="color: rgba(245, 230, 211, 0.8);"
                                class="hover:opacity-80 transition-smooth">List Your Property</a></li>
                        <li><a href="/login" style="color: rgba(245, 230, 211, 0.8);"
                                class="hover:opacity-80 transition-smooth">Login</a></li>
                    </ul>
                </div>
                <div>
                    <h3 style="color: #D4A574;" class="text-xl font-bold mb-3">Contact</h3>
                    <p style="color: rgba(245, 230, 211, 0.8);">Tacloban City, Philippines</p>
                    <p style="color: rgba(245, 230, 211, 0.8);">Email: info@boardinghousefinder.com</p>
                </div>
            </div>
            <div style="border-top: 1px solid rgba(74, 124, 126, 0.3);" class="pt-6">
                <p style="color: rgba(245, 230, 211, 0.7);" class="text-center">&copy; <?php echo e(date('Y')); ?> Boarding
                    House Finder. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script>
        const API_BASE = '/api';
        let authToken = localStorage.getItem('auth_token');
        let currentUser = null;

        // Check authentication status
        function checkAuth() {
            authToken = localStorage.getItem('auth_token');
            const userStr = localStorage.getItem('user');

            if (authToken && userStr) {
                try {
                    currentUser = JSON.parse(userStr);
                    // Get first name only
                    const firstName = currentUser.name ? currentUser.name.split(' ')[0] : 'User';
                    document.getElementById('userWelcome').textContent = `Welcome, ${firstName}`;
                    document.getElementById('userWelcome').classList.remove('hidden');
                    document.getElementById('logoutBtn').classList.remove('hidden');
                    document.getElementById('loginLink').classList.add('hidden');
                    document.getElementById('registerLink').classList.add('hidden');

                    if (currentUser.role === 'admin') {
                        document.getElementById('adminLink').classList.remove('hidden');
                    }
                } catch (e) {
                    console.error('Error parsing user data:', e);
                }
            } else {
                document.getElementById('userWelcome').classList.add('hidden');
                document.getElementById('logoutBtn').classList.add('hidden');
                document.getElementById('loginLink').classList.remove('hidden');
                document.getElementById('registerLink').classList.remove('hidden');
                document.getElementById('adminLink').classList.add('hidden');
            }
        }

        // Set up axios defaults
        if (typeof axios !== 'undefined') {
            axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').content;
            if (authToken) {
                axios.defaults.headers.common['Authorization'] = `Bearer ${authToken}`;
            }
        }

        function logout() {
            if (authToken) {
                fetch(`${API_BASE}/logout`, {
                    method: 'POST',
                    headers: {
                        'Authorization': `Bearer ${authToken}`,
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                }).catch(() => {});
            }
            localStorage.removeItem('auth_token');
            localStorage.removeItem('user');
            window.location.href = '/';
        }

        // Check auth on page load
        document.addEventListener('DOMContentLoaded', checkAuth);
    </script>
</body>

</html>
<?php /**PATH /var/www/resources/views/layouts/app.blade.php ENDPATH**/ ?>