<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'MasyukAI Cart Demo')</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- Custom styles -->
    <style>
        [x-cloak] { display: none !important; }
        
        .cart-item-enter {
            transform: translateX(100%);
            opacity: 0;
        }
        
        .cart-item-enter-active {
            transition: all 0.3s ease-out;
            transform: translateX(0);
            opacity: 1;
        }
        
        .cart-item-leave {
            transform: translateX(0);
            opacity: 1;
        }
        
        .cart-item-leave-active {
            transition: all 0.3s ease-in;
            transform: translateX(-100%);
            opacity: 0;
        }
        
        .loading-spinner {
            border: 2px solid #f3f4f6;
            border-top: 2px solid #3b82f6;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .notification {
            transform: translateX(100%);
            transition: transform 0.3s ease-out;
        }
        
        .notification.show {
            transform: translateX(0);
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Navigation -->
    <nav class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <h1 class="text-xl font-bold text-gray-900">
                            <span class="text-blue-600">MasyukAI</span> Cart Demo
                        </h1>
                    </div>
                    <div class="hidden sm:ml-8 sm:flex sm:space-x-8">
                        <a href="{{ route('cart.demo.index') }}" 
                           class="text-gray-900 hover:text-blue-600 px-3 py-2 text-sm font-medium {{ Request::routeIs('cart.demo.index') ? 'border-b-2 border-blue-600' : '' }}">
                            Shop
                        </a>
                        <a href="{{ route('cart.demo.instances') }}" 
                           class="text-gray-900 hover:text-blue-600 px-3 py-2 text-sm font-medium {{ Request::routeIs('cart.demo.instances') ? 'border-b-2 border-blue-600' : '' }}">
                            Cart Instances
                        </a>
                        <a href="{{ route('cart.demo.migration') }}" 
                           class="text-gray-900 hover:text-blue-600 px-3 py-2 text-sm font-medium {{ Request::routeIs('cart.demo.migration') ? 'border-b-2 border-blue-600' : '' }}">
                            Migration Demo
                        </a>
                    </div>
                </div>
                
                <!-- Cart summary -->
                <div class="flex items-center space-x-4">
                    <div class="text-sm text-gray-600" id="cart-summary">
                        <span id="cart-count">{{ $cartCount ?? 0 }}</span> items - 
                        $<span id="cart-total">{{ number_format($cartTotal ?? 0, 2) }}</span>
                    </div>
                    
                    <!-- Theme toggle -->
                    <button x-data="{ dark: false }" 
                            @click="dark = !dark; document.documentElement.setAttribute('data-theme', dark ? 'dark' : 'light')"
                            class="p-2 rounded-md text-gray-600 hover:text-gray-900 hover:bg-gray-100">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main content -->
    <main class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        @yield('content')
    </main>

    <!-- Notification container -->
    <div id="notifications" class="fixed top-4 right-4 z-50 space-y-2"></div>

    <!-- Loading overlay -->
    <div id="loading-overlay" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 flex items-center space-x-3">
            <div class="loading-spinner"></div>
            <span>Loading...</span>
        </div>
    </div>

    <script>
        // Global cart functionality
        window.CartDemo = {
            showLoading() {
                document.getElementById('loading-overlay').classList.remove('hidden');
                document.getElementById('loading-overlay').classList.add('flex');
            },
            
            hideLoading() {
                document.getElementById('loading-overlay').classList.add('hidden');
                document.getElementById('loading-overlay').classList.remove('flex');
            },
            
            showNotification(message, type = 'success') {
                const notification = document.createElement('div');
                notification.className = `notification bg-${type === 'success' ? 'green' : 'red'}-500 text-white px-6 py-3 rounded-lg shadow-lg max-w-sm`;
                notification.innerHTML = `
                    <div class="flex items-center justify-between">
                        <span>${message}</span>
                        <button onclick="this.parentElement.parentElement.remove()" class="ml-4 text-white hover:text-gray-200">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                `;
                
                document.getElementById('notifications').appendChild(notification);
                
                // Show notification
                setTimeout(() => notification.classList.add('show'), 100);
                
                // Auto remove after 5 seconds
                setTimeout(() => {
                    notification.classList.remove('show');
                    setTimeout(() => notification.remove(), 300);
                }, 5000);
            },
            
            updateCartSummary(count, total) {
                document.getElementById('cart-count').textContent = count;
                document.getElementById('cart-total').textContent = parseFloat(total).toFixed(2);
            },
            
            async makeRequest(url, options = {}) {
                try {
                    this.showLoading();
                    
                    const defaultOptions = {
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    };
                    
                    const response = await fetch(url, { ...defaultOptions, ...options });
                    const data = await response.json();
                    
                    if (data.success) {
                        this.showNotification(data.message);
                        if (data.cart_count !== undefined && data.cart_total !== undefined) {
                            this.updateCartSummary(data.cart_count, data.cart_total);
                        }
                    } else {
                        this.showNotification(data.message || 'An error occurred', 'error');
                    }
                    
                    return data;
                } catch (error) {
                    this.showNotification('Network error occurred', 'error');
                    console.error('Request failed:', error);
                } finally {
                    this.hideLoading();
                }
            }
        };
        
        // CSRF token setup for all AJAX requests
        document.addEventListener('DOMContentLoaded', function() {
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            
            // Set default headers for fetch requests
            const originalFetch = window.fetch;
            window.fetch = function(url, options = {}) {
                options.headers = options.headers || {};
                if (!options.headers['X-CSRF-TOKEN']) {
                    options.headers['X-CSRF-TOKEN'] = token;
                }
                return originalFetch(url, options);
            };
        });
    </script>
    
    @yield('scripts')
</body>
</html>
