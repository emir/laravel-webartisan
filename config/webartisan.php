<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Enabled
    |--------------------------------------------------------------------------
    |
    | Master switch to enable or disable Webartisan entirely. When set to
    | false, all routes will return 404 regardless of environment.
    |
    */

    'enabled' => env('WEBARTISAN_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Enabled Environments
    |--------------------------------------------------------------------------
    |
    | Webartisan will only be available in these environments. This is a
    | critical security measure to prevent exposing artisan commands
    | in production. Set to ['*'] to allow all environments (not recommended).
    |
    */

    'enabled_environments' => [
        'local',
    ],

    /*
    |--------------------------------------------------------------------------
    | Route Prefix
    |--------------------------------------------------------------------------
    |
    | The URL prefix for the webartisan routes. By default, the terminal
    | will be accessible at /webartisan.
    |
    */

    'route_prefix' => env('WEBARTISAN_PREFIX', 'webartisan'),

    /*
    |--------------------------------------------------------------------------
    | Route Domain
    |--------------------------------------------------------------------------
    |
    | Optionally restrict webartisan to a specific domain. Useful for
    | multi-domain applications where you want the terminal available
    | only on an internal domain. Leave null to allow all domains.
    |
    */

    'domain' => env('WEBARTISAN_DOMAIN', null),

    /*
    |--------------------------------------------------------------------------
    | Route Middleware
    |--------------------------------------------------------------------------
    |
    | Middleware to apply to webartisan routes. The 'web' middleware group
    | is required for session and CSRF support. Add 'auth' or custom
    | authentication middleware here for additional security.
    |
    */

    'middleware' => ['web'],

    /*
    |--------------------------------------------------------------------------
    | Authorization Gate
    |--------------------------------------------------------------------------
    |
    | Define a gate name that will be checked before granting access to
    | Webartisan. This allows fine-grained access control beyond just
    | environment checks. Set to null to disable gate authorization.
    |
    | You can define the gate in your AuthServiceProvider:
    |
    |   Gate::define('viewWebartisan', function ($user) {
    |       return in_array($user->email, ['admin@example.com']);
    |   });
    |
    */

    'gate' => null,

    /*
    |--------------------------------------------------------------------------
    | Allowed Commands
    |--------------------------------------------------------------------------
    |
    | If set, only these artisan commands will be allowed to run.
    | Leave empty to allow all commands (except blocked ones).
    | Supports wildcard patterns: 'migrate:*' allows all migrate commands.
    |
    | Example: ['migrate:status', 'route:list', 'config:show', 'queue:*']
    |
    */

    'allowed_commands' => [],

    /*
    |--------------------------------------------------------------------------
    | Blocked Commands
    |--------------------------------------------------------------------------
    |
    | These artisan commands will never be allowed to run via the browser.
    | This provides a safety layer even when all commands are allowed.
    | Supports wildcard patterns: 'db:*' blocks all db commands.
    |
    */

    'blocked_commands' => [
        'down',
        'up',
        'env',
        'serve',
        'tinker',
        'key:generate',
        'package:discover',
        'migrate:fresh',
        'migrate:reset',
        'db:wipe',
        'db:seed',
        'storage:link',
        'vendor:publish',
    ],

    /*
    |--------------------------------------------------------------------------
    | Theme
    |--------------------------------------------------------------------------
    |
    | The terminal color theme. Available themes:
    | 'dark', 'light', 'monokai', 'dracula'
    |
    */

    'theme' => env('WEBARTISAN_THEME', 'dark'),

];
