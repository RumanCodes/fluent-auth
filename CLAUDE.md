# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What This Is

FluentAuth (fluent-security) is a WordPress security plugin providing login security, audit logging, magic login, 2FA, social login (Google/GitHub/Facebook), login customization, and system email management.

## Commands

```bash
# PHP tests
composer test                          # Run PHPUnit suite
./vendor/bin/phpunit tests/Unit/HelperTest.php  # Single test file
./vendor/bin/phpunit --filter "testMethodName"   # Single test method
composer test-coverage                 # HTML coverage report

# Static analysis
composer phpstan                       # PHPStan level 5 (outputs to phpstan-errors.md)

# Frontend build
npm run dev                            # Development build (Laravel Mix)
npm run prod                           # Production build
npm run watch                          # Watch mode

# Release build
sh build.sh --loco --node-build        # Full build for WP.org distribution
```

## Architecture

### Plugin Bootstrap

Entry point: `fluent-security.php` — defines constants, registers a custom PSR-4-style SPL autoloader for the `FluentAuth\` namespace (maps `FluentAuth\App\*` to `app/*`), then loads hooks and REST routes.

### Namespace & Directory Map

- `FluentAuth\App\Http\Controllers\` — REST API controllers
- `FluentAuth\App\Http\routes.php` — All REST endpoint definitions (namespace: `/wp-json/fluent-auth/`)
- `FluentAuth\App\Services\` — Business logic (AuthService, social auth services, SystemEmailService)
- `FluentAuth\App\Services\DB\` — Custom fluent query builder wrapping wpdb; accessed via `flsDb()` global
- `FluentAuth\App\Hooks\Handlers\` — WordPress hook handlers, each with a `register()` method
- `FluentAuth\App\Hooks\hooks.php` — Instantiates and registers all handlers
- `FluentAuth\App\Helpers\` — Helper (settings/utilities), Arr (array ops), Activator (DB migrations), BrowserDetection

### Key Patterns

- **Database access**: Always use `flsDb()->table('fls_auth_logs')->where(...)->get()` (Laravel-style query builder). Two custom tables: `fls_auth_logs`, `fls_login_hashes`.
- **REST API**: Routes defined in `app/Http/routes.php` using the custom `Router` class with method chaining. Default permission: `manage_options`, overridable via `fluent_auth/app_permission` filter.
- **Error handling**: Return `\WP_Error` objects from controllers/services for API errors.
- **Settings**: Stored in `wp_options` under key `__fls_auth_settings`. Retrieved/managed via `Helper::getAuthSettings()`.
- **Hook handlers**: Each handler in `app/Hooks/Handlers/` is a class with a `register()` method that hooks into WordPress actions/filters.
- **Frontend**: Vue 3 + Element Plus + Vue Router, built with Laravel Mix. Admin app bootstraps from `src/admin/app.js` using `window.fluentAuthAdmin` for server-side data. REST calls use mixin methods `$get`, `$post`, `$put`, `$del`.
- **i18n text domain**: `'fluent-security'`

### Testing

Tests run standalone without WordPress via comprehensive mocks in `tests/bootstrap.php` (mocks WP functions, database, REST classes). Test namespace: `FluentAuth\Tests\` (PSR-4 in `autoload-dev`). Unit tests in `tests/Unit/`, integration tests in `tests/Integration/`.

## Code Style

- **PHP**: PascalCase classes, camelCase methods/variables, UPPER_SNAKE_CASE constants. Follow WordPress coding standards. Sanitize input (`sanitize_text_field`, `sanitize_url`), escape output (`esc_html`, `esc_url`).
- **Vue/JS**: PascalCase component files, kebab-case in templates. ES6+, async/await. SCSS with BEM naming (`.fframe_app`, `&__body`).
- **Commits**: Run `composer phpstan` before committing.
