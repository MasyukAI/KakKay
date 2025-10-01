# Condition System Architecture Diagram

```
┌─────────────────────────────────────────────────────────────────────────────────────────┐
│                          CONDITION SYSTEM ARCHITECTURE                                   │
└─────────────────────────────────────────────────────────────────────────────────────────┘

┌───────────────────────────────────────────────────────────────────────────────────────────┐
│ 1. TEMPLATE LAYER (conditions table - Reusable Definitions)                               │
├───────────────────────────────────────────────────────────────────────────────────────────┤
│                                                                                            │
│  ┌──────────────────────────────────────────────────────────────────────────────┐        │
│  │  Condition Model (Template)                                                   │        │
│  ├──────────────────────────────────────────────────────────────────────────────┤        │
│  │  User Input Fields:                    Computed Fields (Auto):                │        │
│  │  • name                                • operator (+,-,*,/,%)                 │        │
│  │  • display_name                        • is_charge (boolean)                  │        │
│  │  • type                                • is_discount (boolean)                │        │
│  │  • target                              • is_percentage (boolean)              │        │
│  │  • value (e.g., "-20%")               • is_dynamic (boolean)                 │        │
│  │  • order                               • parsed_value (decimal)               │        │
│  │  • is_active                                                                  │        │
│  │  • rules (jsonb, optional)            ← Triggers is_dynamic = true           │        │
│  │  • attributes (jsonb)                                                         │        │
│  └──────────────────────────────────────────────────────────────────────────────┘        │
│                                           ↓                                                │
│                              Model::saving Event Fires                                     │
│                                           ↓                                                │
│                         computeDerivedFields() Executes                                    │
│                                           ↓                                                │
│                    Parses value → Computes all derived fields                             │
│                                                                                            │
└───────────────────────────────────────────────────────────────────────────────────────────┘
                                           ↓
┌───────────────────────────────────────────────────────────────────────────────────────────┐
│ 2. APPLICATION LAYER (CartCondition - Runtime Instances)                                  │
├───────────────────────────────────────────────────────────────────────────────────────────┤
│                                                                                            │
│  ┌────────────────────────────────────────────────────────────────────────────┐          │
│  │  Method: Condition::createCondition()                                       │          │
│  ├────────────────────────────────────────────────────────────────────────────┤          │
│  │  Converts template → CartCondition object                                   │          │
│  │                                                                              │          │
│  │  new CartCondition(                                                          │          │
│  │    name: $this->name,                                                        │          │
│  │    type: $this->type,                                                        │          │
│  │    target: $this->target,                                                    │          │
│  │    value: $this->value,                                                      │          │
│  │    attributes: [...],                                                        │          │
│  │    order: $this->order,                                                      │          │
│  │    rules: $this->is_dynamic ? $this->rules : null  ← Dynamic support        │          │
│  │  )                                                                            │          │
│  └────────────────────────────────────────────────────────────────────────────┘          │
│                                           ↓                                                │
│                        Applied to Cart Instance (in-memory)                                │
│                                           ↓                                                │
│                    Cart calculates totals with conditions                                  │
│                                                                                            │
└───────────────────────────────────────────────────────────────────────────────────────────┘
                                           ↓
┌───────────────────────────────────────────────────────────────────────────────────────────┐
│ 3. PERSISTENCE LAYER (cart_conditions table - Normalized Snapshots)                       │
├───────────────────────────────────────────────────────────────────────────────────────────┤
│                                                                                            │
│  ┌────────────────────────────────────────────────────────────────────────────┐          │
│  │  Event: CartUpdated (any cart change)                                       │          │
│  ├────────────────────────────────────────────────────────────────────────────┤          │
│  │  Listener: SyncCartToDatabase                                               │          │
│  │                                                                              │          │
│  │  Syncs ALL condition data:                                                   │          │
│  │  • name, type, target, value, attributes, order                              │          │
│  │  • operator, parsed_value                                                    │          │
│  │  • is_charge, is_discount, is_percentage, is_dynamic                         │          │
│  │  • rules (for dynamic conditions)                                            │          │
│  │  • price (computed impact on cart/item)                                      │          │
│  └────────────────────────────────────────────────────────────────────────────┘          │
│                                                                                            │
│  Result: Complete audit trail with all computed metadata                                  │
│                                                                                            │
└───────────────────────────────────────────────────────────────────────────────────────────┘


┌───────────────────────────────────────────────────────────────────────────────────────────┐
│                          CONDITION CREATION PATHS                                          │
└───────────────────────────────────────────────────────────────────────────────────────────┘

PATH A: Create via ConditionResource (Stored Template)
──────────────────────────────────────────────────────
User → ConditionResource → CreateCondition Page → Form Input
                                    ↓
                          Fill: name, type, target, value, rules
                                    ↓
                            Save (triggers Model::saving)
                                    ↓
                          computeDerivedFields() executes
                                    ↓
                    Template saved to conditions table (with computed fields)
                                    ↓
                          Ready to apply to any cart


PATH B: Apply Stored Condition to Cart
──────────────────────────────────────
User → CartResource → Cart → Conditions Tab → Apply Condition
                                    ↓
                          Select condition from dropdown
                                    ↓
                    Condition::createCondition() → CartCondition
                                    ↓
                          Cart::addCondition()
                                    ↓
                      Applied to cart (in-memory)
                                    ↓
                          Cart recalculates totals
                                    ↓
                    CartUpdated event → SyncCartToDatabase
                                    ↓
                Synced to cart_conditions table (with all fields)


PATH C: Create Custom Condition (One-Time Use)
──────────────────────────────────────────────
User → CartResource → Cart → Conditions Tab → Add Custom Condition
                                    ↓
                  Fill modal: name, type, target, value, rules, attributes
                                    ↓
                  Validate JSON rules (if dynamic enabled)
                                    ↓
            new CartCondition(..., rules: $rules) directly
                                    ↓
                          Cart::addCondition()
                                    ↓
                      Applied to cart (in-memory)
                                    ↓
                          Cart recalculates totals
                                    ↓
                    CartUpdated event → SyncCartToDatabase
                                    ↓
        Synced to cart_conditions table (NOT saved as template)


┌───────────────────────────────────────────────────────────────────────────────────────────┐
│                          DYNAMIC CONDITIONS FLOW                                           │
└───────────────────────────────────────────────────────────────────────────────────────────┘

Example: "Buy 3+ items, get 10% off"
─────────────────────────────────────

1. CREATE TEMPLATE
   ┌──────────────────────────────────────────────────────┐
   │ name: "Bulk Discount"                                 │
   │ value: "-10%"                                         │
   │ rules: {"min_items": 3}                               │
   │                                                        │
   │ Auto-computed:                                         │
   │ • is_dynamic: true  (because rules exist)             │
   │ • is_discount: true (because value starts with -)     │
   │ • is_percentage: true (because value ends with %)     │
   │ • operator: "%"                                        │
   │ • parsed_value: "-0.1"                                │
   └──────────────────────────────────────────────────────┘

2. APPLY TO CART (Manual or Auto)
   Cart state: 2 items → Rules not met → Condition not applied
                ↓
   User adds 3rd item → Cart recalculates
                ↓
   Cart::processConditions() checks dynamic rules
                ↓
   Rules met! (min_items: 3) → Auto-applies condition
                ↓
   Cart total recalculated with 10% discount
                ↓
   Syncs to cart_conditions table with is_dynamic = true

3. AUTO-REMOVE
   User removes item → Cart now has 2 items
                ↓
   Cart::processConditions() checks rules again
                ↓
   Rules not met → Auto-removes condition
                ↓
   Cart total recalculated without discount
                ↓
   Condition removed from cart_conditions table


┌───────────────────────────────────────────────────────────────────────────────────────────┐
│                          FILAMENT UI COMPONENTS                                            │
└───────────────────────────────────────────────────────────────────────────────────────────┘

ConditionsTable (List View)
────────────────────────────
┌─────────────────────────────────────────────────────────────────────────┐
│ Conditions                                                   [+ New]     │
├─────────────────────────────────────────────────────────────────────────┤
│ Name             | Type     | Value   | Operator | Discount | Dynamic   │
├─────────────────────────────────────────────────────────────────────────┤
│ Summer Sale      | discount | -20%    | %        | ✓        | ✗         │
│ Bulk Discount    | discount | -10%    | %        | ✓        | ✓         │
│ Shipping Fee     | fee      | +5.00   | +        | ✗        | ✗         │
│ Tax              | tax      | +6%     | %        | ✗        | ✗         │
└─────────────────────────────────────────────────────────────────────────┘
                      ↑                    ↑          ↑          ↑
              Always visible      Toggleable (hidden by default)


ConditionForm (Create/Edit)
────────────────────────────
┌─────────────────────────────────────────────────────────────────────────┐
│ Create Condition                                                         │
├─────────────────────────────────────────────────────────────────────────┤
│ Basic Information                                                        │
│ ┌─────────────────────────────────┬───────────────────────────────────┐ │
│ │ Name: [Holiday Discount 20%   ] │ Display: [Holiday Special       ] │ │
│ └─────────────────────────────────┴───────────────────────────────────┘ │
│                                                                          │
│ Condition Details                                                        │
│ ┌─────────────────┬──────────────┬──────────────┬────────────────────┐ │
│ │ Type: [Discount]│ Target: [Tot]│ Value: [-20%]│ Order: [0        ] │ │
│ └─────────────────┴──────────────┴──────────────┴────────────────────┘ │
│                                                                          │
│ Advanced Options                                              [Collapse] │
│ ┌──────────────────────────────────────────────────────────────────────┐│
│ │ ☑ Active Condition                                                   ││
│ │                                                                       ││
│ │ Dynamic Rules (JSON):                                                ││
│ │ ┌────────────────────────────────────────────────────────────────┐  ││
│ │ │ {"min_items": 3, "min_total": 100}                             │  ││
│ │ └────────────────────────────────────────────────────────────────┘  ││
│ └──────────────────────────────────────────────────────────────────────┘│
│                                                                          │
│ Computed Fields (Auto-Generated)                          [Collapsed]   │
│                                                                          │
│                                    [Cancel]  [Create Condition]          │
└─────────────────────────────────────────────────────────────────────────┘


ApplyConditionAction Modal (Custom)
────────────────────────────────────
┌─────────────────────────────────────────────────────────────────────────┐
│ Add Custom Condition                                                [×] │
├─────────────────────────────────────────────────────────────────────────┤
│ Name: [One-time Discount              ]                                 │
│                                                                          │
│ Type: [Discount ▼]     Target: [Total ▼]                                │
│                                                                          │
│ Value: [-50        ]   Order: [0   ]                                    │
│                                                                          │
│ ☐ Dynamic Condition                                                     │
│                                                                          │
│ Custom Attributes:                                                       │
│ ┌────────────────────┬──────────────────────┐                          │
│ │ promo_code         │ SAVE50               │  [+]                      │
│ └────────────────────┴──────────────────────┘                          │
│                                                                          │
│                                        [Cancel]  [Apply Condition]       │
└─────────────────────────────────────────────────────────────────────────┘
                                          ↓
                    (If Dynamic enabled, rules field appears)


┌───────────────────────────────────────────────────────────────────────────────────────────┐
│                          DATA FLOW SUMMARY                                                 │
└───────────────────────────────────────────────────────────────────────────────────────────┘

USER INPUT                  AUTOMATIC COMPUTATION              DATABASE STORAGE
──────────                  ─────────────────────              ────────────────
                                                               
name: "Summer Sale"         [Model::saving event]              conditions table:
type: "discount"            ↓                                  ✓ name
target: "total"             Parse "-20%"                       ✓ type
value: "-20%"               ↓                                  ✓ target
order: 0                    Extract operator: "%"              ✓ value
rules: null                 ↓                                  ✓ order
                            Compute parsed: -0.2               ✓ rules
                            ↓                                  ✓ operator (computed)
                            Set is_discount: true              ✓ is_discount (computed)
                            ↓                                  ✓ is_percentage (computed)
                            Set is_percentage: true            ✓ is_charge (computed)
                            ↓                                  ✓ is_dynamic (computed)
                            Set is_charge: false               ✓ parsed_value (computed)
                            ↓
                            Set is_dynamic: false
                            ↓
                            [Save to database]

                                    ↓ (when applied to cart)

                            [createCondition()]
                            ↓
                            CartCondition object
                            (in-memory, with rules)
                            ↓
                            [Cart::addCondition()]
                            ↓
                            Cart recalculates
                            ↓
                            [CartUpdated event]
                            ↓
                            [SyncCartToDatabase]
                            ↓
                            cart_conditions table:
                            ✓ All condition fields
                            ✓ All computed fields
                            ✓ price (impact on cart)

```
