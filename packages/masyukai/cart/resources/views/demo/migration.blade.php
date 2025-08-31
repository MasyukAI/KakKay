@extends('cart::demo.layout')

@section('title', 'Cart Migration - MasyukAI Cart Demo')

@section('content')
<div x-data="migrationDemo()">
    <div class="mb-8">
        <h2 class="text-2xl font-bold text-gray-900 mb-4">Cart Migration Demo</h2>
        <p class="text-gray-600 mb-6">
            Demonstrate guest-to-user cart migration functionality. This simulates what happens when a guest user
            with items in their cart logs into their account.
        </p>
    </div>
    
    <!-- Migration setup -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Setup Demo Carts</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <button @click="setupGuestCart()" 
                    class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 px-6 rounded-md transition-colors duration-200">
                Setup Guest Cart
            </button>
            <button @click="setupUserCart()" 
                    class="bg-green-600 hover:bg-green-700 text-white font-medium py-3 px-6 rounded-md transition-colors duration-200">
                Setup User Cart
            </button>
        </div>
        <p class="text-sm text-gray-500 mt-3">
            Click these buttons to populate the demo carts with sample items for testing migration scenarios.
        </p>
    </div>
    
    <!-- Current cart states -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Guest cart -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Guest Cart</h3>
                <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2 py-1 rounded-full">
                    {{ $guestCartItems->count() }} items
                </span>
            </div>
            
            @if($guestCartItems->isNotEmpty())
                <div class="space-y-3 mb-4">
                    @foreach($guestCartItems as $item)
                    <div class="border-b border-gray-200 pb-3">
                        <div class="flex justify-between items-start">
                            <div>
                                <h4 class="font-medium text-gray-900">{{ $item->name }}</h4>
                                @if($item->attributes->isNotEmpty())
                                <div class="text-xs text-gray-500 mt-1">
                                    @foreach($item->attributes as $key => $value)
                                        {{ ucfirst($key) }}: {{ $value }}@if(!$loop->last), @endif
                                    @endforeach
                                </div>
                                @endif
                            </div>
                            <div class="text-right">
                                <div class="font-medium">${{ number_format($item->price, 2) }}</div>
                                <div class="text-sm text-gray-500">Qty: {{ $item->quantity }}</div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                <div class="text-right font-semibold text-lg">
                    Total: ${{ number_format($guestCartItems->sum(fn($item) => $item->price * $item->quantity), 2) }}
                </div>
            @else
                <div class="text-center py-8 text-gray-500">
                    <svg class="mx-auto h-12 w-12 text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                    </svg>
                    <p>Guest cart is empty</p>
                    <button @click="setupGuestCart()" class="mt-2 text-blue-600 hover:text-blue-700 text-sm font-medium">
                        Add sample items
                    </button>
                </div>
            @endif
        </div>
        
        <!-- User cart -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">User Cart</h3>
                <span class="bg-green-100 text-green-800 text-xs font-medium px-2 py-1 rounded-full">
                    {{ $userCartItems->count() }} items
                </span>
            </div>
            
            @if($userCartItems->isNotEmpty())
                <div class="space-y-3 mb-4">
                    @foreach($userCartItems as $item)
                    <div class="border-b border-gray-200 pb-3">
                        <div class="flex justify-between items-start">
                            <div>
                                <h4 class="font-medium text-gray-900">{{ $item->name }}</h4>
                                @if($item->attributes->isNotEmpty())
                                <div class="text-xs text-gray-500 mt-1">
                                    @foreach($item->attributes as $key => $value)
                                        {{ ucfirst($key) }}: {{ $value }}@if(!$loop->last), @endif
                                    @endforeach
                                </div>
                                @endif
                            </div>
                            <div class="text-right">
                                <div class="font-medium">${{ number_format($item->price, 2) }}</div>
                                <div class="text-sm text-gray-500">Qty: {{ $item->quantity }}</div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                <div class="text-right font-semibold text-lg">
                    Total: ${{ number_format($userCartItems->sum(fn($item) => $item->price * $item->quantity), 2) }}
                </div>
            @else
                <div class="text-center py-8 text-gray-500">
                    <svg class="mx-auto h-12 w-12 text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                    </svg>
                    <p>User cart is empty</p>
                    <button @click="setupUserCart()" class="mt-2 text-green-600 hover:text-green-700 text-sm font-medium">
                        Add sample items
                    </button>
                </div>
            @endif
        </div>
    </div>
    
    <!-- Migration strategies -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Migration Strategies</h3>
        <p class="text-gray-600 mb-4">
            Choose how to handle conflicts when the same product exists in both guest and user carts:
        </p>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <label class="border border-gray-200 rounded-lg p-4 cursor-pointer hover:bg-gray-50" 
                   :class="migrationStrategy === 'add_quantities' ? 'border-blue-500 bg-blue-50' : ''">
                <input type="radio" x-model="migrationStrategy" value="add_quantities" class="sr-only">
                <div class="flex items-start space-x-3">
                    <div class="w-4 h-4 border-2 border-blue-500 rounded-full mt-1 flex-shrink-0"
                         :class="migrationStrategy === 'add_quantities' ? 'bg-blue-500' : 'bg-white'">
                        <div x-show="migrationStrategy === 'add_quantities'" class="w-2 h-2 bg-white rounded-full mx-auto mt-0.5"></div>
                    </div>
                    <div>
                        <h4 class="font-medium text-gray-900">Add Quantities</h4>
                        <p class="text-sm text-gray-600">Combine quantities of duplicate items</p>
                        <p class="text-xs text-gray-500 mt-1">Example: Guest(2) + User(3) = Final(5)</p>
                    </div>
                </div>
            </label>
            
            <label class="border border-gray-200 rounded-lg p-4 cursor-pointer hover:bg-gray-50"
                   :class="migrationStrategy === 'keep_highest_quantity' ? 'border-blue-500 bg-blue-50' : ''">
                <input type="radio" x-model="migrationStrategy" value="keep_highest_quantity" class="sr-only">
                <div class="flex items-start space-x-3">
                    <div class="w-4 h-4 border-2 border-blue-500 rounded-full mt-1 flex-shrink-0"
                         :class="migrationStrategy === 'keep_highest_quantity' ? 'bg-blue-500' : 'bg-white'">
                        <div x-show="migrationStrategy === 'keep_highest_quantity'" class="w-2 h-2 bg-white rounded-full mx-auto mt-0.5"></div>
                    </div>
                    <div>
                        <h4 class="font-medium text-gray-900">Keep Highest Quantity</h4>
                        <p class="text-sm text-gray-600">Keep the higher quantity</p>
                        <p class="text-xs text-gray-500 mt-1">Example: Guest(2) + User(3) = Final(3)</p>
                    </div>
                </div>
            </label>
            
            <label class="border border-gray-200 rounded-lg p-4 cursor-pointer hover:bg-gray-50"
                   :class="migrationStrategy === 'keep_user_cart' ? 'border-blue-500 bg-blue-50' : ''">
                <input type="radio" x-model="migrationStrategy" value="keep_user_cart" class="sr-only">
                <div class="flex items-start space-x-3">
                    <div class="w-4 h-4 border-2 border-blue-500 rounded-full mt-1 flex-shrink-0"
                         :class="migrationStrategy === 'keep_user_cart' ? 'bg-blue-500' : 'bg-white'">
                        <div x-show="migrationStrategy === 'keep_user_cart'" class="w-2 h-2 bg-white rounded-full mx-auto mt-0.5"></div>
                    </div>
                    <div>
                        <h4 class="font-medium text-gray-900">Keep User Cart</h4>
                        <p class="text-sm text-gray-600">Preserve user's existing quantities</p>
                        <p class="text-xs text-gray-500 mt-1">Example: Guest(2) + User(3) = Final(3)</p>
                    </div>
                </div>
            </label>
            
            <label class="border border-gray-200 rounded-lg p-4 cursor-pointer hover:bg-gray-50"
                   :class="migrationStrategy === 'replace_with_guest' ? 'border-blue-500 bg-blue-50' : ''">
                <input type="radio" x-model="migrationStrategy" value="replace_with_guest" class="sr-only">
                <div class="flex items-start space-x-3">
                    <div class="w-4 h-4 border-2 border-blue-500 rounded-full mt-1 flex-shrink-0"
                         :class="migrationStrategy === 'replace_with_guest' ? 'bg-blue-500' : 'bg-white'">
                        <div x-show="migrationStrategy === 'replace_with_guest'" class="w-2 h-2 bg-white rounded-full mx-auto mt-0.5"></div>
                    </div>
                    <div>
                        <h4 class="font-medium text-gray-900">Replace with Guest</h4>
                        <p class="text-sm text-gray-600">Use guest cart quantities</p>
                        <p class="text-xs text-gray-500 mt-1">Example: Guest(2) + User(3) = Final(2)</p>
                    </div>
                </div>
            </label>
        </div>
        
        <!-- Migration button -->
        <div class="flex justify-center">
            <button @click="performMigration()" 
                    class="bg-purple-600 hover:bg-purple-700 text-white font-medium py-3 px-8 rounded-md transition-colors duration-200">
                Perform Migration
            </button>
        </div>
    </div>
    
    <!-- Migration history/results -->
    <div x-show="migrationResults.length > 0" class="bg-white rounded-lg shadow-md p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Migration Results</h3>
        <div class="space-y-3">
            <template x-for="result in migrationResults" :key="result.timestamp">
                <div class="border border-gray-200 rounded-lg p-4">
                    <div class="flex items-center justify-between mb-2">
                        <span class="font-medium" :class="result.success ? 'text-green-600' : 'text-red-600'">
                            <span x-show="result.success">✓</span>
                            <span x-show="!result.success">✗</span>
                            <span x-text="result.message"></span>
                        </span>
                        <span class="text-sm text-gray-500" x-text="result.timestamp"></span>
                    </div>
                    <div class="text-sm text-gray-600">
                        Strategy: <span class="font-medium" x-text="result.strategy"></span>
                    </div>
                </div>
            </template>
        </div>
        
        <button @click="clearResults()" class="mt-4 text-sm text-gray-600 hover:text-gray-800">
            Clear Results
        </button>
    </div>
    
    <!-- Live migration testing -->
    <div class="bg-white rounded-lg shadow-md p-6 mt-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Live Testing</h3>
        <p class="text-gray-600 mb-4">
            Test different scenarios by setting up specific cart states and running migrations:
        </p>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <button @click="testEmptyGuestCart()" 
                    class="bg-gray-600 hover:bg-gray-700 text-white font-medium py-2 px-4 rounded-md transition-colors duration-200">
                Test Empty Guest Cart
            </button>
            <button @click="testConflictScenario()" 
                    class="bg-orange-600 hover:bg-orange-700 text-white font-medium py-2 px-4 rounded-md transition-colors duration-200">
                Test Conflict Scenario
            </button>
            <button @click="testLargeCartMigration()" 
                    class="bg-red-600 hover:bg-red-700 text-white font-medium py-2 px-4 rounded-md transition-colors duration-200">
                Test Large Cart Migration
            </button>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function migrationDemo() {
    return {
        migrationStrategy: 'add_quantities',
        migrationResults: [],
        
        setupGuestCart() {
            CartDemo.makeRequest('/cart-demo/migration/setup', {
                method: 'POST',
                body: JSON.stringify({ type: 'guest' })
            }).then(() => {
                setTimeout(() => window.location.reload(), 1000);
            });
        },
        
        setupUserCart() {
            CartDemo.makeRequest('/cart-demo/migration/setup', {
                method: 'POST',
                body: JSON.stringify({ type: 'user' })
            }).then(() => {
                setTimeout(() => window.location.reload(), 1000);
            });
        },
        
        performMigration() {
            const timestamp = new Date().toLocaleTimeString();
            
            CartDemo.makeRequest('/cart-demo/migration/perform', {
                method: 'POST',
                body: JSON.stringify({ strategy: this.migrationStrategy })
            }).then(data => {
                this.migrationResults.unshift({
                    success: data.success,
                    message: data.message,
                    strategy: data.strategy_used,
                    timestamp: timestamp
                });
                
                setTimeout(() => window.location.reload(), 2000);
            });
        },
        
        clearResults() {
            this.migrationResults = [];
        },
        
        testEmptyGuestCart() {
            CartDemo.showNotification('Setting up empty guest cart scenario...');
            // This would clear guest cart and add items only to user cart
            setTimeout(() => {
                CartDemo.showNotification('Empty guest cart scenario ready');
            }, 1000);
        },
        
        testConflictScenario() {
            CartDemo.showNotification('Setting up conflict scenario...');
            // This would add same products to both carts with different quantities
            Promise.all([
                this.setupGuestCart(),
                this.setupUserCart()
            ]).then(() => {
                CartDemo.showNotification('Conflict scenario ready - same products in both carts');
            });
        },
        
        testLargeCartMigration() {
            CartDemo.showNotification('Setting up large cart migration scenario...');
            // This would add many items to test performance
            setTimeout(() => {
                CartDemo.showNotification('Large cart scenario ready');
            }, 1000);
        }
    }
}
</script>
@endsection
