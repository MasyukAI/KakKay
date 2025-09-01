<?php

declare(strict_types=1);

namespace MasyukAI\Cart\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use MasyukAI\Cart\Facades\Cart;
use MasyukAI\Cart\Conditions\CartCondition;
use MasyukAI\Cart\Services\CartMigrationService;

class DemoController extends Controller
{
    public function index()
    {
        $products = $this->getSampleProducts();
        $cartItems = Cart::getItems();
        $cartCount = Cart::count();
        $cartSubtotal = Cart::subtotal();
        $cartTotal = Cart::total();
        $cartConditions = Cart::getConditions();

        return view('cart::demo.index', compact(
            'products',
            'cartItems', 
            'cartCount',
            'cartSubtotal',
            'cartTotal',
            'cartConditions'
        ));
    }

    public function addToCart(Request $request)
    {
        $request->validate([
            'id' => 'required|string',
            'name' => 'required|string',
            'price' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:1',
            'attributes' => 'array',
            'attributes.color' => 'string',
            'attributes.size' => 'string',
        ]);

        Cart::add(
            $request->id,
            $request->name,
            $request->price,
            $request->quantity,
            $request->input('attributes', [])
        );

        return response()->json([
            'success' => true,
            'message' => 'Item added to cart successfully',
            'cart_count' => Cart::count(),
            'cart_total' => Cart::total()
        ]);
    }

    public function updateQuantity(Request $request)
    {
        $request->validate([
            'id' => 'required|string',
            'quantity' => 'required|integer|min:0',
        ]);

        if ($request->quantity == 0) {
            Cart::remove($request->id);
        } else {
            // Use absolute quantity update with array format
            Cart::update($request->id, ['quantity' => ['value' => $request->quantity]]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Cart updated successfully',
            'cart_count' => Cart::count(),
            'cart_total' => Cart::total()
        ]);
    }

    public function removeItem(Request $request)
    {
        $request->validate([
            'id' => 'required|string',
        ]);

        Cart::remove($request->id);

        return response()->json([
            'success' => true,
            'message' => 'Item removed from cart',
            'cart_count' => Cart::count(),
            'cart_total' => Cart::total()
        ]);
    }

    public function applyCondition(Request $request)
    {
        $request->validate([
            'type' => 'required|in:discount,charge',
            'name' => 'required|string',
            'value' => 'required|string',
            'target' => 'string|in:subtotal,item',
        ]);

        $condition = new CartCondition(
            name: $request->name,
            type: $request->type,
            target: $request->input('target', 'subtotal'),
            value: $request->value
        );

        Cart::condition($condition);

        return response()->json([
            'success' => true,
            'message' => 'Condition applied successfully',
            'cart_total' => Cart::total()
        ]);
    }

    public function removeCondition(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
        ]);

        Cart::removeCondition($request->name);

        return response()->json([
            'success' => true,
            'message' => 'Condition removed successfully',
            'cart_total' => Cart::total()
        ]);
    }

    public function clearCart()
    {
        Cart::clear();

        return response()->json([
            'success' => true,
            'message' => 'Cart cleared successfully',
            'cart_count' => 0,
            'cart_total' => 0
        ]);
    }

    public function instances()
    {
        // Demo different cart instances
        $instances = ['default', 'wishlist', 'compare'];
        $instanceData = [];

        foreach ($instances as $instance) {
            Cart::setInstance($instance);
            $instanceData[$instance] = [
                'count' => Cart::count(),
                'total' => Cart::total(),
                'items' => Cart::getItems()
            ];
        }

        // Reset to default
        Cart::setInstance('default');

        return view('cart::demo.instances', compact('instanceData'));
    }

    public function switchInstance(Request $request)
    {
        $request->validate([
            'instance' => 'required|string|in:default,wishlist,compare',
        ]);

        Cart::setInstance($request->instance);
        session(['current_cart_instance' => $request->instance]);

        return response()->json([
            'success' => true,
            'message' => "Switched to {$request->instance} cart",
            'cart_count' => Cart::count(),
            'cart_total' => Cart::total()
        ]);
    }

    public function migrationDemo()
    {
        $guestCartItems = collect();
        $userCartItems = collect();
        
        // Get guest cart items
        Cart::setInstance('guest_demo');
        $guestCartItems = Cart::getItems();
        
        // Get user cart items
        Cart::setInstance('user_demo');
        $userCartItems = Cart::getItems();
        
        // Reset to default
        Cart::setInstance('default');

        return view('cart::demo.migration', compact('guestCartItems', 'userCartItems'));
    }

    public function setupMigrationDemo(Request $request)
    {
        $type = $request->input('type', 'guest');
        
        if ($type === 'guest') {
            Cart::setInstance('guest_demo');
            // Add some sample items to guest cart
            Cart::add('guest-product-1', 'Guest Product 1', 25.99, 2, ['color' => 'blue']);
            Cart::add('guest-product-2', 'Guest Product 2', 15.50, 1, ['size' => 'large']);
        } else {
            Cart::setInstance('user_demo');
            // Add some sample items to user cart
            Cart::add('user-product-1', 'User Product 1', 35.00, 1, ['color' => 'red']);
            Cart::add('guest-product-1', 'Guest Product 1', 25.99, 1, ['color' => 'blue']); // Conflict item
        }

        Cart::setInstance('default');

        return response()->json([
            'success' => true,
            'message' => ucfirst($type) . ' cart setup completed'
        ]);
    }

    public function performMigration(Request $request)
    {
        $request->validate([
            'strategy' => 'required|string|in:add_quantities,keep_highest_quantity,keep_user_cart,replace_with_guest',
        ]);

        $migrationService = new CartMigrationService();
        
        // Store original strategy
        $originalStrategy = config('cart.migration.merge_strategy');
        config(['cart.migration.merge_strategy' => $request->strategy]);
        
        $success = $migrationService->migrateGuestCartToUser('guest_demo', 999); // Demo user ID
        
        // Restore original strategy
        config(['cart.migration.merge_strategy' => $originalStrategy]);

        return response()->json([
            'success' => $success,
            'message' => $success ? 'Migration completed successfully' : 'No items to migrate',
            'strategy_used' => $request->strategy
        ]);
    }

    protected function getSampleProducts(): array
    {
        return [
            [
                'id' => 'laptop-pro',
                'name' => 'Professional Laptop',
                'price' => 1299.99,
                'description' => 'High-performance laptop perfect for development and design work.',
                'image' => 'https://via.placeholder.com/200x200/007bff/ffffff?text=Laptop',
                'attributes' => [
                    'color' => ['Silver', 'Space Gray', 'Rose Gold'],
                    'storage' => ['256GB', '512GB', '1TB'],
                    'ram' => ['8GB', '16GB', '32GB']
                ]
            ],
            [
                'id' => 'wireless-headphones',
                'name' => 'Premium Wireless Headphones',
                'price' => 299.99,
                'description' => 'Noise-canceling wireless headphones with superior sound quality.',
                'image' => 'https://via.placeholder.com/200x200/28a745/ffffff?text=Headphones',
                'attributes' => [
                    'color' => ['Black', 'White', 'Blue'],
                    'type' => ['Over-ear', 'On-ear']
                ]
            ],
            [
                'id' => 'smartphone-x',
                'name' => 'Smartphone X',
                'price' => 899.99,
                'description' => 'Latest smartphone with advanced camera and AI features.',
                'image' => 'https://via.placeholder.com/200x200/dc3545/ffffff?text=Phone',
                'attributes' => [
                    'color' => ['Black', 'White', 'Blue', 'Red'],
                    'storage' => ['128GB', '256GB', '512GB']
                ]
            ],
            [
                'id' => 'gaming-mouse',
                'name' => 'Gaming Mouse Pro',
                'price' => 79.99,
                'description' => 'High-precision gaming mouse with customizable RGB lighting.',
                'image' => 'https://via.placeholder.com/200x200/ffc107/000000?text=Mouse',
                'attributes' => [
                    'color' => ['Black', 'White'],
                    'dpi' => ['12000', '16000', '20000']
                ]
            ],
            [
                'id' => 'mechanical-keyboard',
                'name' => 'Mechanical Keyboard',
                'price' => 159.99,
                'description' => 'Premium mechanical keyboard with Cherry MX switches.',
                'image' => 'https://via.placeholder.com/200x200/6f42c1/ffffff?text=Keyboard',
                'attributes' => [
                    'switch' => ['Blue', 'Brown', 'Red'],
                    'layout' => ['Full', 'TKL', '60%']
                ]
            ],
            [
                'id' => 'monitor-4k',
                'name' => '4K Monitor 27"',
                'price' => 449.99,
                'description' => '27-inch 4K monitor with HDR support and USB-C connectivity.',
                'image' => 'https://via.placeholder.com/200x200/17a2b8/ffffff?text=Monitor',
                'attributes' => [
                    'size' => ['24"', '27"', '32"'],
                    'panel' => ['IPS', 'VA', 'TN']
                ]
            ]
        ];
    }
}
