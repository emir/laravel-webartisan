# Laravel Webartisan

[![Tests](https://github.com/emir/laravel-webartisan/actions/workflows/tests.yml/badge.svg)](https://github.com/emir/laravel-webartisan/actions/workflows/tests.yml)
[![Latest Version on Packagist](https://img.shields.io/packagist/v/emir/laravel-webartisan.svg)](https://packagist.org/packages/emir/laravel-webartisan)
[![Total Downloads](https://img.shields.io/packagist/dt/emir/laravel-webartisan.svg)](https://packagist.org/packages/emir/laravel-webartisan)
[![License](https://img.shields.io/packagist/l/emir/laravel-webartisan.svg)](https://packagist.org/packages/emir/laravel-webartisan)

A beautiful, modern browser-based terminal for running **Laravel Artisan** commands. Zero dependencies on the frontend, just works.

## Features

- Run any Artisan command from your browsers
- **Tab completion** for command names
- **Command history** with up/down arrow navigation
- **4 built-in themes**: Dark, Light, Monokai, Dracula
- **Security**: Environment restriction, Gate authorization, command allow/block lists
- **Wildcard patterns** for command filtering (`migrate:*`, `db:*`)
- **Artisan install command** for quick setup
- **Zero configuration** needed - works out of the box
- Supports **Laravel 10, 11, and 12**

## Requirements

- PHP 8.2+
- Laravel 10.x, 11.x, or 12.x

## Installation

```bash
composer require emir/laravel-webartisan --dev
```

The package uses **Laravel's auto-discovery**, so the service provider is registered automatically.

Run the install command to publish config and assets:

```bash
php artisan webartisan:install
```

That's it! Visit `/webartisan` in your browser.

### Manual Publishing

If you prefer to publish resources individually:

```bash
# Config file
php artisan vendor:publish --tag=webartisan-config

# Frontend assets
php artisan vendor:publish --tag=webartisan-assets

# Blade views (for customization)
php artisan vendor:publish --tag=webartisan-views
```

## Configuration

After publishing, the config file is located at `config/webartisan.php`:

```php
return [
    // Master switch
    'enabled' => env('WEBARTISAN_ENABLED', true),

    // Only available in these environments
    'enabled_environments' => ['local'],

    // URL prefix (accessible at /webartisan)
    'route_prefix' => env('WEBARTISAN_PREFIX', 'webartisan'),

    // Restrict to a specific domain
    'domain' => env('WEBARTISAN_DOMAIN', null),

    // Route middleware
    'middleware' => ['web'],

    // Gate-based authorization (see Security section)
    'gate' => null,

    // Only allow these commands (empty = all except blocked)
    'allowed_commands' => [],

    // Always block these commands
    'blocked_commands' => [
        'down', 'up', 'env', 'serve', 'tinker',
        'key:generate', 'migrate:fresh', 'migrate:reset',
        'db:wipe', 'db:seed', ...
    ],

    // Terminal theme: 'dark', 'light', 'monokai', 'dracula'
    'theme' => env('WEBARTISAN_THEME', 'dark'),
];
```

## Usage

### Terminal Commands

| Command | Description |
|---------|-------------|
| `help` | Show available terminal commands |
| `list` | List all artisan commands with descriptions |
| `clear` | Clear the terminal screen |
| `exit` / `quit` | Leave webartisan |

Type any Artisan command directly:

```
❯ route:list
❯ migrate:status
❯ make:model Post --migration --factory
❯ config:show database
```

### Keyboard Shortcuts

| Shortcut | Action |
|----------|--------|
| `Tab` | Autocomplete command names |
| `Up` / `Down` | Navigate command history |
| `Ctrl+L` | Clear terminal |

## Themes

Set the theme in your config or `.env` file:

```env
WEBARTISAN_THEME=dracula
```

Available themes: `dark` (default), `light`, `monokai`, `dracula`.

## Security

Webartisan is designed for **development use only**. Multiple security layers are built in:

### 1. Environment Restriction (Default)

By default, Webartisan is only available in the `local` environment:

```php
'enabled_environments' => ['local'],
```

### 2. Master Switch

Disable completely via environment variable:

```env
WEBARTISAN_ENABLED=false
```

### 3. Gate Authorization

For fine-grained access control, define a gate in your `AppServiceProvider`:

```php
use Illuminate\Support\Facades\Gate;

Gate::define('viewWebartisan', function ($user) {
    return in_array($user->email, [
        'admin@example.com',
    ]);
});
```

Then enable it in the config:

```php
'gate' => 'viewWebartisan',
'middleware' => ['web', 'auth'], // Add auth middleware
```

### 4. Custom Authorization

Use the `Webartisan::auth()` method in your `AppServiceProvider`:

```php
use Emir\Webartisan\Webartisan;

Webartisan::auth(function ($request) {
    return $request->user()?->isAdmin() ?? false;
});
```

### 5. Command Allow/Block Lists

```php
// Only allow specific commands
'allowed_commands' => ['route:list', 'migrate:status', 'queue:*'],

// Block dangerous commands (supports wildcards)
'blocked_commands' => ['db:*', 'migrate:fresh', 'tinker'],
```

### 6. Domain Restriction

Restrict to an internal domain:

```env
WEBARTISAN_DOMAIN=admin.myapp.test
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

1. Fork the project
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## Credits

- [Emir Karşıyakalı](https://github.com/emir)
- Inspired by [samdark/yii2-webshell](https://github.com/samdark/yii2-webshell)
- Built with [jQuery Terminal](https://terminal.jcubic.pl/)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
