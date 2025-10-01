# Dynamic Condition Rules Examples

## Basic Rules

### 1. Minimum Cart Total
```json
{
  "min_total": 100
}
```
Applies when cart subtotal ≥ RM100

### 2. Minimum Item Count
```json
{
  "min_items": 3
}
```
Applies when cart has 3 or more items

### 3. Specific Categories
```json
{
  "has_category": "electronics"
}
```
Applies when cart contains items from "electronics" category

### 4. VIP User Only
```json
{
  "user_vip": true
}
```
Applies only for VIP users

## Advanced Rules (Multiple Conditions)

### 5. Bulk Purchase Discount
```json
{
  "min_total": 200,
  "min_items": 5
}
```
Applies when cart total ≥ RM200 AND has 5+ items

### 6. Holiday Special
```json
{
  "min_total": 150,
  "has_category": "books",
  "date_range": {
    "start": "2024-11-25",
    "end": "2024-12-02"
  }
}
```
Applies during Black Friday week for book purchases over RM150

### 7. Student Discount
```json
{
  "user_student": true,
  "max_total": 300,
  "allowed_categories": ["books", "stationery"]
}
```
Student discount for books/stationery under RM300

### 8. First-Time Customer
```json
{
  "first_purchase": true,
  "min_total": 50
}
```
Welcome discount for first-time customers spending RM50+

### 9. Loyalty Program
```json
{
  "loyalty_tier": "gold",
  "min_total": 250
}
```
Gold tier members get discount on RM250+ orders

### 10. Geographic Discount
```json
{
  "user_state": "Selangor",
  "min_total": 100
}
```
State-specific discount for Selangor residents

## Rule Types Supported

| Rule Type | Description | Example |
|-----------|-------------|---------|
| `min_total` | Minimum cart subtotal | `100` |
| `max_total` | Maximum cart subtotal | `500` |
| `min_items` | Minimum item count | `3` |
| `max_items` | Maximum item count | `10` |
| `has_category` | Cart contains category | `"electronics"` |
| `has_product` | Cart contains specific product | `"PROD-123"` |
| `user_vip` | User is VIP | `true` |
| `user_student` | User is student | `true` |
| `first_purchase` | First-time customer | `true` |
| `loyalty_tier` | User's loyalty tier | `"gold"` |
| `user_state` | User's state/province | `"Selangor"` |
| `date_range` | Date range (object) | `{"start": "2024-01-01", "end": "2024-12-31"}` |

## How Rules Work

1. **AND Logic**: All rules in the JSON must be true for condition to apply
2. **Automatic Evaluation**: Rules are checked after every cart change
3. **Real-time**: Conditions apply/remove instantly when rules are met/not met
4. **Performance**: Rules are evaluated efficiently without database queries

## Legacy Format (Deprecated)

For backward compatibility, the old format is still supported but **not recommended**:

```json
{
  "type": "min_item_count",
  "threshold": 3
}
```

**Please use the modern flat format instead:**

```json
{
  "min_items": 3
}
```

### Legacy Type Mappings

| Legacy Type | Modern Equivalent |
|-------------|-------------------|
| `min_item_count` | `min_items` |
| `max_item_count` | `max_items` |
| `min_total` | `min_total` |
| `max_total` | `max_total` |

## Testing Your Rules

Create a condition with these rules in the Filament admin, then test by:
1. Adding/removing items from cart
2. Changing quantities
3. Modifying user attributes
4. Checking cart total changes

The condition will automatically appear/disappear based on your rules!