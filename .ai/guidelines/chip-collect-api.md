# CHIP Collect API Guidelines

## Overview
This application uses CHIP Collect API as the payment gateway. CHIP Collect is a comprehensive payment processing solution that supports multiple payment methods including credit/debit cards, FPX online banking, e-wallets, DuitNow QR, and Buy Now Pay Later options.

## Documentation Access
The complete CHIP Collect API documentation is available through the Context7 MCP tool. To access it:

1. Use the `mcp_context7_resolve-library-id` tool to find CHIP Collect API
2. Use the `mcp_context7_get-library-docs` tool with library ID `/websites/docs_chip-in_asia-chip-collect-overview-introduction`
3. Specify relevant topics like "API integration", "payment processing", "Laravel PHP", etc.

**API Base URLs**:
- Staging: `https://staging-gate.chip-in.asia/api/v1/`
- Production: `https://gate.chip-in.asia/api/v1/`

## Authentication
All API requests require Bearer token authentication:
```
Authorization: Bearer <your_auth_token>
```

## Configuration Setup for Laravel Integration

When implementing CHIP Collect API in this Laravel application, use the Context7 MCP tool to get the latest API documentation and examples. Here's the basic configuration setup:

### Environment Variables
```env
CHIP_BASE_URL=https://gate.chip-in.asia/api/v1
CHIP_AUTH_TOKEN=your_auth_token_here
CHIP_BRAND_ID=your_brand_id_here
```

### Laravel Service Configuration
```php
// config/services.php
return [
    'chip' => [
        'base_url' => env('CHIP_BASE_URL', 'https://gate.chip-in.asia/api/v1'),
        'auth_token' => env('CHIP_AUTH_TOKEN'),
        'brand_id' => env('CHIP_BRAND_ID'),
    ],
];
```

## Key Implementation Guidelines

### 1. Always Use Context7 for Latest Documentation
Before implementing any CHIP Collect functionality, use the MCP Context7 tool to get:
- Current API endpoints and parameters
- Request/response formats
- Error handling patterns
- Authentication requirements
- Payment flow examples

### 2. Common Integration Patterns
- **Payment Links**: Create purchases and redirect to `checkout_url`
- **Pre-Authorization**: Use `skip_capture: true` for hold/capture workflows
- **Recurring Payments**: Use `force_recurring: true` to tokenize payment methods
- **Webhooks**: Always implement proper webhook handling for payment status updates

### 3. Laravel Best Practices
- Create a dedicated `ChipPaymentService` class
- Use Laravel HTTP client for API calls
- Implement proper error handling and logging
- Store purchase IDs for transaction tracking
- Use events for payment status changes

### 4. Security Considerations
- Always use HTTPS for webhook endpoints
- Validate webhook signatures when available
- Never store sensitive payment data
- Use test mode during development
- Handle PCI DSS compliance requirements

## Quick Reference

### Getting Documentation
```php
// Use Context7 MCP to get specific documentation
// Example topics to search for:
// - "create purchase API"
// - "capture payment"
// - "recurring payments"
// - "webhook handling"
// - "refund payment"
```

### Payment Amount Format
All amounts in CHIP Collect API are in cents:
```php
$amountInRM = 29.99;
$amountInCents = (int) ($amountInRM * 100); // 2999 for API
```

### Common Payment Methods
- `visa`, `mastercard`, `maestro`: Card payments
- `fpx`: FPX Online Banking (Malaysia)
- `ewallet`: E-wallet payments  
- `duitnow_qr`: DuitNow QR payments
- `bnpl`: Buy Now, Pay Later

For detailed implementation examples, API endpoints, request/response formats, and troubleshooting, always refer to the Context7 MCP tool for the most up-to-date CHIP Collect API documentation.
