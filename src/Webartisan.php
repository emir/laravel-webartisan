<?php

namespace Emir\Webartisan;

class Webartisan
{
    /**
     * The package version.
     */
    public const VERSION = '2.0.0';

    /**
     * Indicates if Webartisan routes have been registered.
     */
    protected static bool $routesRegistered = false;

    /**
     * The callback used to authorize access to Webartisan.
     *
     * @var callable|null
     */
    protected static $authUsing;

    /**
     * Register the Webartisan authorization callback.
     */
    public static function auth(callable $callback): static
    {
        static::$authUsing = $callback;

        return new static;
    }

    /**
     * Determine if the given request can access Webartisan.
     */
    public static function check(mixed $request): bool
    {
        return (static::$authUsing ?: function () {
            return app()->environment(config('webartisan.enabled_environments', ['local']));
        })($request);
    }

    /**
     * Check if Webartisan is enabled.
     */
    public static function isEnabled(): bool
    {
        if (! config('webartisan.enabled', true)) {
            return false;
        }

        $allowedEnvs = config('webartisan.enabled_environments', ['local']);

        if (in_array('*', $allowedEnvs)) {
            return true;
        }

        return app()->environment($allowedEnvs);
    }

    /**
     * Determine if a command is allowed to run.
     */
    public static function isCommandAllowed(string $commandName): bool
    {
        $blockedCommands = config('webartisan.blocked_commands', []);

        foreach ($blockedCommands as $pattern) {
            if (static::commandMatches($pattern, $commandName)) {
                return false;
            }
        }

        $allowedCommands = config('webartisan.allowed_commands', []);

        if (empty($allowedCommands)) {
            return true;
        }

        foreach ($allowedCommands as $pattern) {
            if (static::commandMatches($pattern, $commandName)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if a command name matches a pattern (supports wildcards).
     */
    protected static function commandMatches(string $pattern, string $commandName): bool
    {
        if ($pattern === $commandName) {
            return true;
        }

        $regex = str_replace(
            ['\*', '\?'],
            ['.*', '.'],
            preg_quote($pattern, '/')
        );

        return (bool) preg_match('/^'.$regex.'$/i', $commandName);
    }

    /**
     * Mark routes as registered.
     */
    public static function routesRegistered(): void
    {
        static::$routesRegistered = true;
    }

    /**
     * Check if routes have been registered.
     */
    public static function hasRoutes(): bool
    {
        return static::$routesRegistered;
    }
}
