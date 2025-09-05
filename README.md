# WordPress Generate Utils

A lean WordPress library for generating unique identifiers, professional codes, slugs, and secure tokens.

## Installation

```bash
composer require arraypress/wp-generate-utils
```

## Quick Start

```php
use ArrayPress\GenerateUtils\Generate;

// UUIDs
$id = Generate::uuid(); // "f47ac10b-58cc-4372-a567-0e02b2c3d479"

// Short IDs (YouTube/Bitly style)
$short = Generate::short_id(); // "Ab3Cd7K"

// Sequential IDs (invoices/orders)
$invoice = Generate::sequential_id( 'INV-', 8 ); // "INV-00001001"

// Human-readable IDs (Docker style)
$readable = Generate::readable_id(); // "happy-tiger-42"

// Professional codes
$coupon = Generate::code( [ 'length' => 6 ] ); // "ABCD12"

// Magic link tokens
$magic = Generate::magic_token( HOUR_IN_SECONDS, 'login' );
// Returns: ['token' => '...', 'expires' => '2025-01-15 10:00:00', 'expires_at' => 1737025200, 'context' => 'login']
```

## Why Use This?

WordPress can't generate professional codes or sequential IDs:

```php
// WordPress limitation
wp_generate_password( 8 ); // "aB3dE7gH" (ugly, random mix)

// Our solution  
Generate::code( [ 'segments' => 2 ] ); // "ABCD-EFGH" (professional)
Generate::sequential_id( 'INV-' ); // "INV-00001001" (sequential tracking)
```

## API Reference

### Core Identifiers

#### `uuid(): string`
Standard UUID v4 for database IDs, APIs.
```php
$id = Generate::uuid(); // "f47ac10b-58cc-4372-a567-0e02b2c3d479"
```

#### `key( string $prefix = 'id', int $length = 9 ): string`
Structured keys with prefix.
```php
Generate::key( 'order', 8 ); // "order_a1b2c3d4"
```

#### `short_id( int $length = 7 ): string`
URL-safe short IDs without confusing characters.
```php
Generate::short_id(); // "Ab3Cd7K"
Generate::short_id( 10 ); // "Ab3Cd7Kx9Z"
```

#### `sequential_id( string $prefix = '', int $padding = 8, string $context = 'default' ): string`
Sequential IDs with automatic increment.
```php
Generate::sequential_id( 'INV-' ); // "INV-00001001"
Generate::sequential_id( 'ORD-' ); // "ORD-00001002"
```

#### `readable_id( string $separator = '-', bool $with_number = true ): string`
Human-friendly IDs like Docker containers.
```php
Generate::readable_id(); // "happy-tiger-42"
Generate::readable_id( '_', false ); // "brave_eagle"
```

### Professional Codes

#### `code( array $options = [] ): string`
Generate professional codes with full control.

Options:
- `segments`: Number of segments (default: 1)
- `length`: Length per segment (default: 4)
- `separator`: Join segments with (default: '')
- `uppercase`: Use uppercase letters (default: true)
- `numbers`: Include numbers (default: true)
- `exclude`: Remove confusing chars (default: ['0','O','1','I'])
- `prefix`/`suffix`: Add text before/after

```php
// Simple code
Generate::code(); // "ABCD"

// Multi-segment license key
Generate::code( [
    'segments' => 4,
    'separator' => '-'
] ); // "ABCD-EFGH-IJKL-MNOP"

// Prefixed discount code
Generate::code( [
    'prefix' => 'SAVE',
    'length' => 6
] ); // "SAVEABCD12"
```

### Security Tokens

#### `token( int $length = 32, string $action = '', string $format = 'alnum' ): string`
Secure tokens with optional WordPress nonce integration.
```php
// Simple token
Generate::token(); // "a1B2c3D4..."

// Hex format token
Generate::token( 32, '', 'hex' ); // "a1b2c3d4..."

// WordPress-integrated token
Generate::token( 32, 'email_verify' ); // With nonce entropy
```

#### `magic_token( int $expires_in = DAY_IN_SECONDS, string $context = '', int $length = 32 ): array`
Complete magic link solution with expiration.
```php
$magic = Generate::magic_token( HOUR_IN_SECONDS, 'login' );
// Returns:
// [
//     'token' => '64-char-hex-string',
//     'expires' => '2025-01-15 10:00:00',
//     'expires_at' => 1737025200,
//     'context' => 'login'
// ]
```

### Utility Functions

#### `string( int $length = 16, string $charset = 'alnum', bool $secure = true ): string`
Random strings with custom charsets.
```php
// Alphanumeric
Generate::string( 10 ); // "a1B2c3D4e5"

// Numbers only (PIN)
Generate::string( 6, 'numeric' ); // "123456"

// Hex color
'#' . Generate::string( 6, 'hex' ); // "#a1b2c3"

// Custom charset
Generate::string( 8, 'ABC123' ); // "A1B3C2A1"
```

#### `slug( string $title, string $context = 'post', string $type = 'post' ): string`
WordPress-aware unique slugs.
```php
// Unique post slug
Generate::slug( 'My Product', 'post', 'product' );

// Unique username
Generate::slug( 'john.doe@email.com', 'user' );

// Unique term slug
Generate::slug( 'Electronics', 'term', 'product_cat' );
```

## Common Use Cases

### E-commerce & Orders
```php
// Sequential order numbers
$order_id = Generate::sequential_id( 'ORD-', 8 ); // "ORD-00001001"

// Invoice numbers
$invoice = Generate::sequential_id( 'INV-', 8, 'invoices' ); // "INV-00001001"

// SKUs
$sku = Generate::key( 'SKU', 8 ); // "SKU_a1b2c3d4"

// Discount codes
$coupon = Generate::code( [
    'prefix' => 'SAVE',
    'length' => 6,
    'uppercase' => true
] ); // "SAVEABC123"
```

### User Authentication
```php
// Magic link (passwordless login)
$magic = Generate::magic_token( 
    expires_in: 15 * MINUTE_IN_SECONDS,
    context: 'passwordless_login'
);
save_magic_token( $user_id, $magic['token'], $magic['expires'] );

// Password reset
$reset = Generate::magic_token( DAY_IN_SECONDS, 'password_reset' );

// Email verification
$verify = Generate::token( 32, 'email_verify' );

// API keys
$api_key = Generate::token( 40, '', 'hex' );
```

### License Management
```php
// Professional license keys
$license = Generate::code( [
    'segments' => 4,
    'length' => 4,
    'separator' => '-',
    'exclude' => [ '0', 'O', '1', 'I', 'l' ]
] ); // "ABCD-EFGH-JKLM-NPQR"

// Software activation codes
$activation = Generate::code( [
    'segments' => 5,
    'length' => 5,
    'separator' => '-'
] ); // "ABCDE-FGHIJ-KLMNO-PQRST-UVWXY"
```

### Content Management
```php
// URL shortener
$short_url = 'domain.com/' . Generate::short_id( 6 ); // "domain.com/Ab3Cd7"

// Temporary share links
$share_id = Generate::short_id( 10 );

// Human-friendly URLs
$friendly = Generate::readable_id(); // "brave-eagle-42"
```

### Session & Security
```php
// Session IDs
$session = Generate::key( 'sess', 16 ); // "sess_a1b2c3d4e5f6g7h8"

// CSRF tokens
$csrf = Generate::token( 32 );

// Nonces
$nonce = Generate::token( 16, 'form_submit' );

// Two-factor backup codes
$backup_codes = [];
for ( $i = 0; $i < 10; $i++ ) {
    $backup_codes[] = Generate::code( [
        'length' => 8,
        'numbers' => true,
        'uppercase' => false
	] );
}
```

## Advanced Examples

### Custom Code Formats
```php
// Gift cards
$gift_card = Generate::code( [
    'segments' => 4,
    'length' => 4,
    'separator' => ' ',
    'uppercase' => true,
    'numbers' => true
] ); // "ABCD EFGH 1234 5678"

// Referral codes (letters only)
$referral = Generate::code( [
    'length' => 8,
    'uppercase' => true,
    'numbers' => false
] ); // "ABCDEFGH"

// PIN codes
$pin = Generate::string( 4, 'numeric' ); // "1234"
```

### Batch Generation with Uniqueness
```php
function generate_unique_codes( int $count = 10 ): array {
    $codes = [];
    $attempts = 0;
    $max_attempts = $count * 10;
    
    while ( count( $codes ) < $count && $attempts < $max_attempts ) {
        $code = Generate::code( [
            'segments' => 3,
            'separator' => '-'
        ] );
        
        // Check uniqueness in database
        if ( ! code_exists_in_db( $code ) ) {
            $codes[] = $code;
        }
        
        $attempts++;
    }
    
    return $codes;
}
```

### Context-Aware Sequential IDs
```php
// Different sequences for different contexts
$invoice = Generate::sequential_id( 'INV-', 8, 'invoices' );
$receipt = Generate::sequential_id( 'REC-', 8, 'receipts' );
$refund = Generate::sequential_id( 'REF-', 8, 'refunds' );

// Year-prefixed sequences
$year = date( 'Y' );
$order = Generate::sequential_id( "ORD-{$year}-", 6, "orders_{$year}" );
// Result: "ORD-2025-001001"
```

## Requirements

- PHP 7.4 or higher
- WordPress 5.0 or higher

## License

GPL-2.0-or-later

## Support

- [Documentation](https://github.com/arraypress/wp-generate-utils)
- [Issue Tracker](https://github.com/arraypress/wp-generate-utils/issues)