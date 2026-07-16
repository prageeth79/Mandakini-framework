<?php
namespace app\core;

class Session {

    protected const FLASH_KEY = 'flash_messages';

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION[self::FLASH_KEY])) {
            $_SESSION[self::FLASH_KEY] = [];
        }

        // Mark existing flash messages to be removed at the end of this request.
        // Support older stored format (string) by converting to array with 'value' and 'remove' flag.
        foreach ($_SESSION[self::FLASH_KEY] as $key => $flash) {
            if (is_array($flash)) {
                $_SESSION[self::FLASH_KEY][$key]['remove'] = true;
            } else {
                $_SESSION[self::FLASH_KEY][$key] = [
                    'value' => $flash,
                    'remove' => true,
                ];
            }
        }
    }

    public function setFlash($key, $message) {
        $_SESSION[self::FLASH_KEY][$key] = [
            'value' => $message,
            'remove' => false,
        ];
    }

    public function getFlash($key) {
        if (isset($_SESSION[self::FLASH_KEY][$key])) {
            $flash = $_SESSION[self::FLASH_KEY][$key];
            $message = is_array($flash) ? ($flash['value'] ?? null) : $flash;
            unset($_SESSION[self::FLASH_KEY][$key]);
            return $message;
        }
        return null;
    }


    public function hasFlash($key) {
        return isset($_SESSION[self::FLASH_KEY][$key]);
    }

    public function set($key, $value) {
        $_SESSION[$key] = $value;
    }

    public function get($key) {
        return $_SESSION[$key] ?? null;
    }

    public function remove($key) {
        unset($_SESSION[$key]); 
    }

    public function clear() {
        session_unset();
    }

    public function destroy() {
        session_destroy();
        return "Session destroyed successfully.";
    }

    public function __destruct() {
        // Remove flash messages that were marked for removal.
        foreach ($_SESSION[self::FLASH_KEY] as $key => $flash) {
            if (is_array($flash) && ($flash['remove'] ?? false)) {
                unset($_SESSION[self::FLASH_KEY][$key]);
            }
        }
    }

}
