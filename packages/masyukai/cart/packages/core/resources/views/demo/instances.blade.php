@extends('cart::demo.layout')

@section('title', 'Cart Instances - MasyukAI Cart Demo')

@section('content')
<div x-data="instancesDemo()">
    <div class="mb-8">
        <h2 class="text-2xl font-bold text-gray-900 mb-4">Cart Instances Demo</h2>
        <p class="text-gray-600 mb-6">
            Demonstrate multiple cart instances functionality. You can maintain separate carts for different purposes
            like shopping cart, wishlist, and comparison list.
        </p>
        
        <!-- Instance switcher -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Switch Cart Instance</h3>
            <div class="flex space-x-4">
                <button @click="switchInstance('default')" 
                        :class="currentInstance === 'default' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                        class="px-4 py-2 rounded-md font-medium transition-colors duration-200">
                    Shopping Cart
                </button>
                <button @click="switchInstance('wishlist')" 
                        :class="currentInstance === 'wishlist' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                        class="px-4 py-2 rounded-md font-medium transition-colors duration-200">
                    Wishlist
                </button>
                <button @click="switchInstance('compare')" 
                        :class="currentInstance === 'compare' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                        class="px-4 py-2 rounded-md font-medium transition-colors duration-200">
                    Compare List
                </button>
            </div>
        </div>
    </div>
    
    <!-- Instance overview -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        @foreach($instanceData as $instance => $data)
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900 capitalize">
                    {{ $instance === 'default' ? 'Shopping Cart' : $instance }}
                </h3>
                <div class="text-right">
                    <div class="text-2xl font-bold text-blue-600">{{ $data['count'] }}</div>
                    <div class="text-sm text-gray-500">items</div>
                </div>
            </div>
            
            <div class="text-lg font-medium text-gray-900 mb-4">
                Total: ${{ number_format($data['total'], 2) }}
            </div>
            
            @if($data['items']->isNotEmpty())
                <div class="space-y-2">
                    @foreach($data['items']->take(3) as $item)
                    <div class="text-sm">
                        <div class="font-medium text-gray-900">{{ $item->name }}</div>
                        <div class="text-gray-500">Qty: {{ $item->quantity }} - ${{ number_format($item->price * $item->quantity, 2) }}</div>
                    </div>
                    @endforeach
                    
                    @if($data['items']->count() > 3)
                    <div class="text-sm text-gray-500">
                        +{{ $data['items']->count() - 3 }} more items
                    </div>
                    @endif
                </div>
            @else
                <div class="text-gray-500 text-sm">No items in this cart</div>
            @endif
            
            <div class="mt-4 flex space-x-2">
                <button @click="addSampleItem('{{ $instance }}')" 
                        class="flex-1 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium py-2 px-3 rounded-md transition-colors duration-200">
                    Add Sample Item
                </button>
                @if($data['count'] > 0)
                <button @click="clearInstance('{{ $instance }}')" 
                        class="bg-red-600 hover:bg-red-700 text-white text-sm font-medium py-2 px-3 rounded-md transition-colors duration-200">
                    Clear
                </button>
                @endif
            </div>
        </div>
        @endforeach
    </div>
    
    <!-- Sample products for instances -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Add Products to Current Instance</h3>
        <p class="text-gray-600 mb-4">Current instance: <span class="font-medium" x-text="getCurrentInstanceName()"></span></p>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <div class="border border-gray-200 rounded-lg p-4">
                <div class="w-full h-32 bg-blue-100 rounded-lg mb-3 flex items-center justify-center">
                    <span class="text-blue-600 font-medium">Laptop</span>
                </div>
                <h4 class="font-medium text-gray-900 mb-2">Gaming Laptop</h4>
                <div class="text-lg font-bold text-blue-600 mb-3">$1,299.99</div>
                <button @click="addProductToCurrentInstance('gaming-laptop', 'Gaming Laptop', 1299.99)" 
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium py-2 px-3 rounded-md transition-colors duration-200">
                    Add to <span x-text="getCurrentInstanceName()"></span>
                </button>
            </div>
            
            <div class="border border-gray-200 rounded-lg p-4">
                <div class="w-full h-32 bg-green-100 rounded-lg mb-3 flex items-center justify-center">
                    <span class="text-green-600 font-medium">Monitor</span>
                </div>
                <h4 class="font-medium text-gray-900 mb-2">4K Monitor</h4>
                <div class="text-lg font-bold text-blue-600 mb-3">$449.99</div>
                <button @click="addProductToCurrentInstance('4k-monitor', '4K Monitor', 449.99)" 
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium py-2 px-3 rounded-md transition-colors duration-200">
                    Add to <span x-text="getCurrentInstanceName()"></span>
                </button>
            </div>
            
            <div class="border border-gray-200 rounded-lg p-4">
                <div class="w-full h-32 bg-purple-100 rounded-lg mb-3 flex items-center justify-center">
                    <span class="text-purple-600 font-medium">Mouse</span>
                </div>
                <h4 class="font-medium text-gray-900 mb-2">Gaming Mouse</h4>
                <div class="text-lg font-bold text-blue-600 mb-3">$79.99</div>
                <button @click="addProductToCurrentInstance('gaming-mouse', 'Gaming Mouse', 79.99)" 
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium py-2 px-3 rounded-md transition-colors duration-200">
                    Add to <span x-text="getCurrentInstanceName()"></span>
                </button>
            </div>
        </div>
    </div>
    
    <!-- Instance operations -->
    <div class="bg-white rounded-lg shadow-md p-6 mt-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Instance Operations</h3>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <h4 class="font-medium text-gray-900 mb-3">Copy Between Instances</h4>
                <div class="space-y-3">
                    <div class="flex space-x-2">
                        <select x-model="copyFromInstance" class="flex-1 border border-gray-300 rounded-md px-3 py-2 text-sm">
                            <option value="">From instance</option>
                            <option value="default">Shopping Cart</option>
                            <option value="wishlist">Wishlist</option>
                            <option value="compare">Compare List</option>
                        </select>
                        <select x-model="copyToInstance" class="flex-1 border border-gray-300 rounded-md px-3 py-2 text-sm">
                            <option value="">To instance</option>
                            <option value="default">Shopping Cart</option>
                            <option value="wishlist">Wishlist</option>
                            <option value="compare">Compare List</option>
                        </select>
                    </div>
                    <button @click="copyBetweenInstances()" 
                            :disabled="!copyFromInstance || !copyToInstance || copyFromInstance === copyToInstance"
                            class="w-full bg-green-600 hover:bg-green-700 disabled:bg-gray-300 disabled:cursor-not-allowed text-white text-sm font-medium py-2 px-4 rounded-md transition-colors duration-200">
                        Copy Items
                    </button>
                </div>
            </div>
            
            <div>
                <h4 class="font-medium text-gray-900 mb-3">Merge Instances</h4>
                <div class="space-y-3">
                    <div class="flex space-x-2">
                        <select x-model="mergeFromInstance" class="flex-1 border border-gray-300 rounded-md px-3 py-2 text-sm">
                            <option value="">From instance</option>
                            <option value="default">Shopping Cart</option>
                            <option value="wishlist">Wishlist</option>
                            <option value="compare">Compare List</option>
                        </select>
                        <select x-model="mergeToInstance" class="flex-1 border border-gray-300 rounded-md px-3 py-2 text-sm">
                            <option value="">To instance</option>
                            <option value="default">Shopping Cart</option>
                            <option value="wishlist">Wishlist</option>
                            <option value="compare">Compare List</option>
                        </select>
                    </div>
                    <button @click="mergeInstances()" 
                            :disabled="!mergeFromInstance || !mergeToInstance || mergeFromInstance === mergeToInstance"
                            class="w-full bg-orange-600 hover:bg-orange-700 disabled:bg-gray-300 disabled:cursor-not-allowed text-white text-sm font-medium py-2 px-4 rounded-md transition-colors duration-200">
                        Merge Instances
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function instancesDemo() {
    return {
        currentInstance: '{{ session("current_cart_instance", "default") }}',
        copyFromInstance: '',
        copyToInstance: '',
        mergeFromInstance: '',
        mergeToInstance: '',
        
        getCurrentInstanceName() {
            switch(this.currentInstance) {
                case 'default': return 'Shopping Cart';
                case 'wishlist': return 'Wishlist';
                case 'compare': return 'Compare List';
                default: return this.currentInstance;
            }
        },
        
        switchInstance(instance) {
            this.currentInstance = instance;
            CartDemo.makeRequest('/cart-demo/switch-instance', {
                method: 'POST',
                body: JSON.stringify({ instance })
            });
        },
        
        addSampleItem(instance) {
            // Switch to instance, add item, then reload
            const originalInstance = this.currentInstance;
            CartDemo.makeRequest('/cart-demo/switch-instance', {
                method: 'POST',
                body: JSON.stringify({ instance })
            }).then(() => {
                return CartDemo.makeRequest('/cart-demo/add', {
                    method: 'POST',
                    body: JSON.stringify({
                        id: `sample-${instance}-${Date.now()}`,
                        name: `Sample ${instance.charAt(0).toUpperCase() + instance.slice(1)} Item`,
                        price: Math.floor(Math.random() * 100) + 10,
                        quantity: 1,
                        attributes: { instance: instance }
                    })
                });
            }).then(() => {
                setTimeout(() => window.location.reload(), 1000);
            });
        },
        
        clearInstance(instance) {
            if (confirm(`Are you sure you want to clear the ${instance} cart?`)) {
                const originalInstance = this.currentInstance;
                CartDemo.makeRequest('/cart-demo/switch-instance', {
                    method: 'POST',
                    body: JSON.stringify({ instance })
                }).then(() => {
                    return CartDemo.makeRequest('/cart-demo/clear', {
                        method: 'DELETE'
                    });
                }).then(() => {
                    setTimeout(() => window.location.reload(), 1000);
                });
            }
        },
        
        addProductToCurrentInstance(id, name, price) {
            CartDemo.makeRequest('/cart-demo/add', {
                method: 'POST',
                body: JSON.stringify({
                    id: `${id}-${this.currentInstance}`,
                    name,
                    price,
                    quantity: 1,
                    attributes: { 
                        instance: this.currentInstance,
                        added_at: new Date().toLocaleString()
                    }
                })
            }).then(() => {
                setTimeout(() => window.location.reload(), 1000);
            });
        },
        
        copyBetweenInstances() {
            CartDemo.showNotification(`Copying items from ${this.copyFromInstance} to ${this.copyToInstance}...`);
            // This would require additional backend implementation
            setTimeout(() => {
                CartDemo.showNotification('Copy operation completed (demo)');
                this.copyFromInstance = '';
                this.copyToInstance = '';
            }, 2000);
        },
        
        mergeInstances() {
            CartDemo.showNotification(`Merging ${this.mergeFromInstance} into ${this.mergeToInstance}...`);
            // This would require additional backend implementation
            setTimeout(() => {
                CartDemo.showNotification('Merge operation completed (demo)');
                this.mergeFromInstance = '';
                this.mergeToInstance = '';
            }, 2000);
        }
    }
}
</script>
@endsection
