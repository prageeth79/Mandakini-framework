<?php
namespace app\core\util;

use app\core\Application;

class Csrf {
    private const TOKEN_KEY = '_csrf_token';

    /**
     * Generate or retrieve the current CSRF token from session.
     */
    public static function token(): string {
        $session = Application::$app->session;
        $token = $session->get(self::TOKEN_KEY);

        if (!$token) {
            $token = bin2hex(random_bytes(32));
            $session->set(self::TOKEN_KEY, $token);
        }

        return $token;
    }

    /**
     * Generate a hidden HTML input field for forms.
     */
    public static function field(): string {
        return '<input type="hidden" name="csrf_token" value="' . static::token() . '">';
    }

    /**
     * Validate an incoming token against the session token.
     */
    public static function validate(?string $token): bool {
        if (!$token) {
            return false;
        }
        $storedToken = Application::$app->session->get(self::TOKEN_KEY);
        
        return hash_equals((string)$storedToken, $token);
    }
}