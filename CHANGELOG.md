# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [2.0.0] - 2026-02-16

### Added
- Laravel 10, 11, and 12 support
- PHP 8.2+ requirement
- Package auto-discovery (no manual service provider registration)
- Configurable route prefix, middleware, and environment restrictions
- Command allowlist and blocklist for security
- Environment-checking middleware
- Artisan install command (`php artisan webartisan:install`)
- Gate-based authorization support
- Tab completion for artisan commands
- Modern terminal UI with dark theme
- CSRF protection on all endpoints
- JSON API responses with proper status codes
- `webartisan:commands` endpoint for listing available commands
- Orchestra Testbench test suite
- GitHub Actions CI pipeline
- `.editorconfig`, `.gitattributes`, `CHANGELOG.md`

### Changed
- Replaced `popen()` shell execution with `Artisan::call()` facade (security)
- Updated jQuery Terminal from 0.8.8 to 2.45.2 (CDN)
- Updated jQuery from 3.0.0-alpha1 to 3.7.1 (CDN)
- Controller now extends `Illuminate\Routing\Controller` instead of `App\Http\Controllers\Controller`
- Routes use modern `Route::` facade syntax with controller array syntax
- Assets published to `public/vendor/webartisan/` (was `public/emir/webartisan/`)
- Complete CSS rewrite with modern dark theme

### Removed
- PHP 5.4 support
- Laravel 5.x support
- Bundled jQuery Terminal files (now loaded from CDN)
- `popen()`/`pclose()` command execution (security risk)
- Legacy route helper syntax

## [1.0.0] - 2015-09-01

### Added
- Initial release
- Browser-based artisan command execution
- jQuery Terminal integration
- Laravel 5.0 support
