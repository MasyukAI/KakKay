<div class="add-to-cart">
    @if(!$showForm)
        <!-- Quick Add Button -->
        <button 
            wire:click="quickAdd"
            class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md transition-colors flex items-center justify-center space-x-2"
            @if(empty($productId) || empty($productName) || $productPrice < 0) disabled @endif
        >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-1.5 6H19M7 13l-1.5 6m4.5-6h6"></path>
            </svg>
            <span>Add to Cart</span>
        </button>
        
        @if(!empty($productId))
            <button 
                wire:click="toggleForm"
                class="mt-2 w-full text-sm text-gray-600 hover:text-gray-700 transition-colors"
            >
                Customize options
            </button>
        @endif
    @else
        <!-- Detailed Form -->
        <div class="bg-white border border-gray-200 rounded-lg p-4 space-y-4">
            <div class="flex justify-between items-center">
                <h3 class="text-lg font-medium text-gray-800">Add to Cart</h3>
                <button 
                    wire:click="toggleForm"
                    class="text-gray-400 hover:text-gray-600 transition-colors"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <div class="space-y-4">
                <div>
                    <label for="productId" class="block text-sm font-medium text-gray-700">Product ID</label>
                    <input 
                        type="text" 
                        id="productId"
                        wire:model="productId"
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                        required
                    >
                    @error('productId') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="productName" class="block text-sm font-medium text-gray-700">Product Name</label>
                    <input 
                        type="text" 
                        id="productName"
                        wire:model="productName"
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                        required
                    >
                    @error('productName') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="productPrice" class="block text-sm font-medium text-gray-700">Price</label>
                    <div class="mt-1 relative rounded-md shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="text-gray-500 sm:text-sm">$</span>
                        </div>
                        <input 
                            type="number" 
                            step="0.01"
                            id="productPrice"
                            wire:model="productPrice"
                            class="block w-full pl-7 pr-3 border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                            min="0"
                            required
                        >
                    </div>
                    @error('productPrice') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="quantity" class="block text-sm font-medium text-gray-700">Quantity</label>
                    <div class="mt-1 flex items-center space-x-2">
                        <button 
                            wire:click="decreaseQuantity"
                            type="button"
                            class="w-8 h-8 flex items-center justify-center bg-gray-100 hover:bg-gray-200 rounded-md transition-colors"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                            </svg>
                        </button>
                        
                        <input 
                            type="number" 
                            id="quantity"
                            wire:model="quantity"
                            class="w-20 text-center border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                            min="1"
                            required
                        >
                        
                        <button 
                            wire:click="increaseQuantity"
                            type="button"
                            class="w-8 h-8 flex items-center justify-center bg-gray-100 hover:bg-gray-200 rounded-md transition-colors"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                        </button>
                    </div>
                    @error('quantity') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <!-- Attributes Section -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">Product Attributes</label>
                    <div class="mt-1 space-y-2">
                        @foreach($productAttributes as $key => $value)
                            <div class="flex items-center space-x-2">
                                <span class="text-sm text-gray-600">{{ $key }}:</span>
                                <span class="text-sm font-medium">{{ $value }}</span>
                                <button 
                                    wire:click="removeAttribute('{{ $key }}')"
                                    class="text-red-500 hover:text-red-700 transition-colors"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="flex space-x-3">
                    <button 
                        wire:click="addToCart"
                        class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md transition-colors flex items-center justify-center space-x-2"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-1.5 6H19M7 13l-1.5 6m4.5-6h6"></path>
                        </svg>
                        <span>Add to Cart</span>
                    </button>
                    
                    <button 
                        wire:click="toggleForm"
                        class="px-4 py-2 border border-gray-300 text-gray-700 hover:bg-gray-50 rounded-md transition-colors"
                    >
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    @endif

    @if(session()->has('cart.message'))
        <div class="mt-4 p-3 bg-green-50 border border-green-200 rounded-md">
            <p class="text-sm text-green-700">{{ session('cart.message') }}</p>
        </div>
    @endif

    @if(session()->has('cart.error'))
        <div class="mt-4 p-3 bg-red-50 border border-red-200 rounded-md">
            <p class="text-sm text-red-700">{{ session('cart.error') }}</p>
        </div>
    @endif
</div>
