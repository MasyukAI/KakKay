<?php

declare(strict_types=1);

use App\Models\Product;
use App\Models\User;
use Livewire\Livewire;
use MasyukAI\Cart\Facades\Cart;
use MasyukAI\FilamentCart\Models\Condition;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);

    // Set Filament panel for testing
    \Filament\Facades\Filament::setCurrentPanel('app');
});

describe('Condition Model', function () {
    it('can create condition templates with factory', function () {
        $discount = Condition::factory()->discount()->create();
        $tax = Condition::factory()->tax()->create();
        $shipping = Condition::factory()->shipping()->create();

        expect($discount->isDiscount())->toBeTrue();
        expect($tax->isTax())->toBeTrue();
        expect($shipping->isShipping())->toBeTrue();
        expect($shipping->isPercentage())->toBeFalse();
    });

    it('can create cart conditions from templates', function () {
        $template = Condition::factory()->create();

        // Update the template with specific values
        $uniqueName = 'Test Discount '.uniqid();
        $template->update([
            'name' => $uniqueName,
            'display_name' => $uniqueName,
            'type' => 'discount',
            'value' => -15,
        ]);

        $condition = $template->createCondition();

        expect($condition->getName())->toBe($uniqueName);
        expect($condition->getType())->toBe('discount');
        expect($condition->getValue())->toBe(-15.0);
    });

    it('can convert template to condition array', function () {
        $template = Condition::factory()->create();

        $array = $template->toConditionArray();

        expect($array)->toHaveKeys(['name', 'type', 'target', 'value', 'attributes']);
        expect($array['attributes'])->toHaveKey('condition_id');
        expect($array['attributes'])->toHaveKey('condition_name');
        expect($array['attributes']['condition_id'])->toBe($template->id);
        expect($array['attributes']['condition_name'])->toBe($template->name);
    });
});

describe('Cart Integration', function () {
    it('can apply conditions to cart', function () {
        Cart::clear();
        Cart::setInstance('test-cart');

        $product = Product::factory()->create(['price' => 100.00]);

        // Add item to cart
        Cart::add($product->id, $product->name, $product->price, 1);

        $initialTotal = Cart::total()->getAmount();

        // Create and apply discount condition
        $discount = Condition::factory()->discount()->create([
            'name' => 'Test Discount '.uniqid(),
            'value' => -10,
            'target' => 'subtotal', // Ensure target is subtotal so discount applies to cart total
        ]);

        $condition = $discount->createCondition();
        Cart::addCondition($condition);

        $finalTotal = Cart::total()->getAmount();

        expect($finalTotal)->toBeLessThan($initialTotal);
        expect(Cart::getConditions())->toHaveCount(1);
    });

    it('can apply multiple conditions', function () {
        Cart::clear();
        Cart::setInstance('test-multi-cart');

        $product = Product::factory()->create(['price' => 100.00]);
        Cart::add($product->id, $product->name, $product->price, 2);

        // Create multiple conditions
        $discount = Condition::factory()->discount()->create([
            'name' => 'Multi Discount '.uniqid(),
            'value' => -15,
        ]);

        $tax = Condition::factory()->tax()->create([
            'name' => 'Multi Tax '.uniqid(),
            'value' => 8.5,
        ]);

        $shipping = Condition::factory()->shipping()->create([
            'name' => 'Multi Shipping '.uniqid(),
            'value' => 10,
        ]);

        // Apply all conditions
        Cart::addCondition($discount->createCondition());
        Cart::addCondition($tax->createCondition());
        Cart::addCondition($shipping->createCondition());

        $conditions = Cart::getConditions();

        expect($conditions)->toHaveCount(3);

        $types = [];
        foreach ($conditions as $condition) {
            $types[] = $condition->getType();
        }

        expect($types)->toContain('discount');
        expect($types)->toContain('tax');
        expect($types)->toContain('shipping');
    });
});

describe('Filament Resource Integration', function () {
    it('can access condition template list page', function () {
        $conditions = Condition::factory()->count(3)->create();

        Livewire::test(\MasyukAI\FilamentCart\Resources\ConditionResource\Pages\ListConditions::class)
            ->assertOk()
            ->assertCanSeeTableRecords($conditions);
    });

    it('can create condition template through Filament', function () {
        $conditionData = Condition::factory()->discount()->make([
            'name' => 'Test Discount',
            'display_name' => 'Test Discount Display',
            'value' => -10,
            'target' => 'subtotal',
        ]);

        Livewire::test(\MasyukAI\FilamentCart\Resources\ConditionResource\Pages\CreateCondition::class)
            ->fillForm([
                'name' => $conditionData->name,
                'display_name' => $conditionData->display_name,
                'type' => $conditionData->type,
                'value' => $conditionData->value,
                'target' => $conditionData->target,
                'is_active' => true,
            ])
            ->call('create')
            ->assertNotified();

        expect(Condition::where('name', 'Test Discount')->exists())->toBeTrue();
    });

    it('can edit condition template through Filament', function () {
        $condition = Condition::factory()->create(['name' => 'Original Name']);

        Livewire::test(\MasyukAI\FilamentCart\Resources\ConditionResource\Pages\EditCondition::class, [
            'record' => $condition->id,
        ])
            ->assertOk()
            ->fillForm(['name' => 'Updated Name'])
            ->call('save')
            ->assertNotified();

        expect($condition->refresh()->name)->toBe('Updated Name');
    });

    it('validates condition template form data', function () {
        Livewire::test(\MasyukAI\FilamentCart\Resources\ConditionResource\Pages\CreateCondition::class)
            ->fillForm([
                'name' => null, // Required field
                'type' => 'discount',
                'value' => -10,
                'target' => 'subtotal',
            ])
            ->call('create')
            ->assertHasFormErrors(['name' => 'required'])
            ->assertNotNotified();
    });
});

describe('Condition Template Scopes', function () {
    it('can filter active condition templates', function () {
        Condition::factory()->active()->count(3)->create();
        Condition::factory()->inactive()->count(2)->create();

        $activeTemplates = Condition::active()->get();

        expect($activeTemplates)->toHaveCount(3);
        expect($activeTemplates->every(fn ($template) => $template->is_active))->toBeTrue();
    });

    it('can filter condition templates by type', function () {
        Condition::factory()->discount()->count(2)->create();
        Condition::factory()->tax()->count(3)->create();
        Condition::factory()->shipping()->count(1)->create();

        $discountTemplates = Condition::ofType('discount')->get();
        $taxTemplates = Condition::ofType('tax')->get();

        expect($discountTemplates)->toHaveCount(2);
        expect($taxTemplates)->toHaveCount(3);
        expect($discountTemplates->every(fn ($template) => $template->type === 'discount'))->toBeTrue();
    });

    it('can filter condition templates for items', function () {
        Condition::factory()->forItems()->count(3)->create();
        Condition::factory()->create(['target' => 'subtotal']);
        Condition::factory()->create(['target' => 'total']);

        $itemTemplates = Condition::forItems()->get();

        expect($itemTemplates)->toHaveCount(3);
        expect($itemTemplates->every(fn ($template) => $template->target === 'item'))->toBeTrue();
    });
});
