# Generate

Licence keys, discount codes, API keys, slugs and UUIDs — generated the way
each of them actually needs to be.

## What it does

Every application ends up writing these, and usually writes at least one of
them badly: a licence key from `rand()`, a slug that drops every accented
character, an API key stored in plain text, a v4 UUID used as a database key
so the index fragments.

This is the four of them done once, with the reasons applied rather than
explained.

## Features

* Generate a licence key or discount code people can read down a phone line
* Compare a code the way a person typed it, so `l` and `1` still match
* Turn a title into a slug that keeps accented characters legible
* Get a slug that is not taken yet, by asking your own callback
* Issue an API key and store only its hash
* Generate a v7 UUID, which sorts by time, or a v4 where that would leak one

## Installation

```bash
composer require arraypress/wp-generate-utils
```

## Quick start

Issue a licence key when an order completes, and a reference to show the
customer:

```php
use ArrayPress\Generate\Code;

$licence   = Code::license( 'ACME' );   // ACME-83FMD-Z7QHQ-G03P2-H061J
$reference = Code::reference( 'INV' );  // INV-JMN53Q
```

Checking one back, however it was typed:

```php
if ( Code::matches( $submitted, $stored ) ) {
	// Case, spacing and the usual O/0 confusions are already handled.
}
```

An API key you never store in the clear:

```php
use ArrayPress\Generate\Token;

[ 'key' => $key, 'hash' => $hash ] = Token::api_key( 'sk' );

// Show $key once. Store $hash. Verify with Token::matches().
```

## Requirements

* PHP 8.3 or later
* WordPress 7.1 or later

## License

GPL-2.0-or-later
