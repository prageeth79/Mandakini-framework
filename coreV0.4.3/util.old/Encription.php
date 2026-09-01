<?php

namespace app\core\util;

/**
 * Encryption Class
 * 
 * Provides secure encryption, decryption, and hashing functionality
 * for the Mandakini Framework
 * 
 * Features:
 * - AES-256 encryption/decryption
 * - Password hashing with bcrypt
 * - HMAC signing/verification
 * - Secure token generation
 * - JWT-like token generation
 * - Data encoding/decoding
 * 
 * Usage:
 *   $encryption = new Encription();
 *   $hash = $encryption->hashPassword('mypassword');
 *   $encrypted = $encryption->encrypt('sensitive data');
 */
class Encription
{
    private $cipher = 'AES-256-CBC';
    private $hashAlgo = 'sha256';
    private $encryptionKey;
    private $hmacKey;

    /**
     * Constructor
     * 
     * @param string|null $encryptionKey Custom encryption key (optional)
     * @param string|null $hmacKey Custom HMAC key (optional)
     */
    public function __construct($encryptionKey = null, $hmacKey = null)
    {
        // Use APP_KEY from environment or generate one
        $this->encryptionKey = $encryptionKey ?: $this->getAppKey();
        $this->hmacKey = $hmacKey ?: $this->getAppKey('hmac');
    }

    /**
     * Get application key from environment
     * 
     * @param string $type Type of key to retrieve
     * @return string Encryption key
     */
    private function getAppKey($type = 'encryption')
    {
        if ($type === 'hmac') {
            return getenv('APP_HMAC_KEY') ?: 'default-hmac-key-change-in-production';
        }
        return getenv('APP_KEY') ?: 'default-encryption-key-change-in-production';
    }

    /**
     * Hash a password using bcrypt
     * 
     * @param string $password Password to hash
     * @param int $cost Bcrypt cost parameter (default: 10)
     * @return string Hashed password
     */
    public function hashPassword($password, $cost = 10)
    {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => $cost]);
    }

    /**
     * Verify a password against a hash
     * 
     * @param string $password Password to verify
     * @param string $hash Hash to verify against
     * @return bool True if password matches hash
     */
    public function verifyPassword($password, $hash)
    {
        return password_verify($password, $hash);
    }

    /**
     * Check if a password hash needs rehashing
     * 
     * @param string $hash Hash to check
     * @param int $cost Bcrypt cost parameter
     * @return bool True if rehashing is needed
     */
    public function needsRehash($hash, $cost = 10)
    {
        return password_needs_rehash($hash, PASSWORD_BCRYPT, ['cost' => $cost]);
    }

    /**
     * Encrypt data using AES-256-CBC
     * 
     * @param string $data Data to encrypt
     * @return string Base64 encoded encrypted data with IV
     */
    public function encrypt($data)
    {
        // Generate a random IV
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($this->cipher));
        
        // Ensure key is proper length (256 bits = 32 bytes)
        $key = hash('sha256', $this->encryptionKey, true);
        
        // Encrypt the data
        $encrypted = openssl_encrypt($data, $this->cipher, $key, OPENSSL_RAW_DATA, $iv);
        
        // Combine IV and encrypted data, then base64 encode
        return base64_encode($iv . $encrypted);
    }

    /**
     * Decrypt data encrypted with encrypt() method
     * 
     * @param string $encryptedData Base64 encoded encrypted data
     * @return string|bool Decrypted data or false on failure
     */
    public function decrypt($encryptedData)
    {
        try {
            // Decode from base64
            $data = base64_decode($encryptedData, true);
            
            if ($data === false) {
                return false;
            }
            
            // Extract IV and encrypted content
            $ivLength = openssl_cipher_iv_length($this->cipher);
            $iv = substr($data, 0, $ivLength);
            $encrypted = substr($data, $ivLength);
            
            // Ensure key is proper length
            $key = hash('sha256', $this->encryptionKey, true);
            
            // Decrypt the data
            $decrypted = openssl_decrypt($encrypted, $this->cipher, $key, OPENSSL_RAW_DATA, $iv);
            
            return $decrypted !== false ? $decrypted : false;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Generate HMAC signature for data
     * 
     * @param string $data Data to sign
     * @param string|null $algo Hash algorithm
     * @return string Hexadecimal HMAC signature
     */
    public function sign($data, $algo = null)
    {
        $algo = $algo ?: $this->hashAlgo;
        return hash_hmac($algo, $data, $this->hmacKey);
    }

    /**
     * Verify HMAC signature
     * 
     * @param string $data Original data
     * @param string $signature Signature to verify
     * @param string|null $algo Hash algorithm
     * @return bool True if signature is valid
     */
    public function verifySignature($data, $signature, $algo = null)
    {
        $algo = $algo ?: $this->hashAlgo;
        $expectedSignature = hash_hmac($algo, $data, $this->hmacKey);
        
        // Use hash_equals to prevent timing attacks
        return hash_equals($expectedSignature, $signature);
    }

    /**
     * Generate a secure random token
     * 
     * @param int $length Token length in bytes (default: 32)
     * @return string Hexadecimal random token
     */
    public function generateToken($length = 32)
    {
        return bin2hex(random_bytes($length));
    }

    /**
     * Generate a secure random string
     * 
     * @param int $length String length
     * @param string $chars Available characters
     * @return string Random string
     */
    public function generateRandomString($length = 16, $chars = null)
    {
        if ($chars === null) {
            $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        }
        
        $string = '';
        $charCount = strlen($chars);
        
        for ($i = 0; $i < $length; $i++) {
            $string .= $chars[random_int(0, $charCount - 1)];
        }
        
        return $string;
    }

    /**
     * Generate JWT-like token with payload
     * 
     * @param array $payload Token payload
     * @param int $expiresIn Expiration time in seconds (default: 3600)
     * @return string JWT-like token
     */
    public function generateJWTToken($payload, $expiresIn = 3600)
    {
        // Add standard claims
        $payload['iat'] = time(); // Issued at
        $payload['exp'] = time() + $expiresIn; // Expiration time
        
        // Encode payload
        $payloadEncoded = $this->base64UrlEncode(json_encode($payload));
        $header = $this->base64UrlEncode(json_encode(['typ' => 'JWT', 'alg' => 'HS256']));
        
        // Create signature
        $signatureInput = $header . '.' . $payloadEncoded;
        $signature = $this->base64UrlEncode(hash_hmac('sha256', $signatureInput, $this->hmacKey, true));
        
        return $signatureInput . '.' . $signature;
    }

    /**
     * Verify and decode JWT-like token
     * 
     * @param string $token JWT-like token to verify
     * @return array|bool Payload array or false if invalid/expired
     */
    public function verifyJWTToken($token)
    {
        $parts = explode('.', $token);
        
        if (count($parts) !== 3) {
            return false;
        }
        
        $header = $this->base64UrlDecode($parts[0]);
        $payload = $this->base64UrlDecode($parts[1]);
        $signature = $parts[2];
        
        if (!$header || !$payload) {
            return false;
        }
        
        // Verify signature
        $signatureInput = $parts[0] . '.' . $parts[1];
        $expectedSignature = $this->base64UrlEncode(
            hash_hmac('sha256', $signatureInput, $this->hmacKey, true)
        );
        
        if (!hash_equals($expectedSignature, $signature)) {
            return false;
        }
        
        // Decode and validate payload
        $payloadArray = json_decode($payload, true);
        
        if (!$payloadArray) {
            return false;
        }
        
        // Check expiration
        if (isset($payloadArray['exp']) && $payloadArray['exp'] < time()) {
            return false; // Token expired
        }
        
        return $payloadArray;
    }

    /**
     * Base64 URL encode
     * 
     * @param string $data Data to encode
     * @return string Base64 URL encoded string
     */
    private function base64UrlEncode($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * Base64 URL decode
     * 
     * @param string $data Base64 URL encoded string
     * @return string|bool Decoded data or false on error
     */
    private function base64UrlDecode($data)
    {
        $data = str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT);
        return base64_decode($data, true);
    }

    /**
     * Hash data using SHA256
     * 
     * @param string $data Data to hash
     * @return string Hexadecimal hash
     */
    public function hash($data)
    {
        return hash('sha256', $data);
    }

    /**
     * Hash data using SHA512
     * 
     * @param string $data Data to hash
     * @return string Hexadecimal hash
     */
    public function hash512($data)
    {
        return hash('sha512', $data);
    }

    /**
     * Generate a unique hash for a file
     * 
     * @param string $filePath Path to file
     * @return string|bool File hash or false if file not found
     */
    public function hashFile($filePath)
    {
        if (!file_exists($filePath)) {
            return false;
        }
        
        return hash_file('sha256', $filePath);
    }

    /**
     * Generate API key pair (public and secret)
     * 
     * @return array Array with 'public' and 'secret' keys
     */
    public function generateAPIKeyPair()
    {
        $publicKey = 'pk_' . $this->generateRandomString(32, 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789');
        $secretKey = 'sk_' . $this->generateRandomString(64, 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789');
        
        return [
            'public' => $publicKey,
            'secret' => $secretKey,
            'pair_hash' => $this->hash($publicKey . $secretKey)
        ];
    }

    /**
     * Encode data to base64
     * 
     * @param string $data Data to encode
     * @return string Base64 encoded data
     */
    public function encode($data)
    {
        return base64_encode($data);
    }

    /**
     * Decode base64 data
     * 
     * @param string $data Base64 encoded data
     * @return string|bool Decoded data or false on error
     */
    public function decode($data)
    {
        return base64_decode($data, true);
    }

    /**
     * Create checksum for data integrity verification
     * 
     * @param string $data Data to create checksum for
     * @param string $algo Hash algorithm
     * @return string Checksum
     */
    public function createChecksum($data, $algo = 'md5')
    {
        return hash($algo, $data);
    }

    /**
     * Verify checksum
     * 
     * @param string $data Data to verify
     * @param string $checksum Checksum to verify against
     * @param string $algo Hash algorithm
     * @return bool True if checksum matches
     */
    public function verifyChecksum($data, $checksum, $algo = 'md5')
    {
        return hash_equals(hash($algo, $data), $checksum);
    }

    /**
     * Salt and hash a value
     * 
     * @param string $value Value to salt and hash
     * @param string|null $salt Custom salt (optional)
     * @return array Array with 'hash' and 'salt' keys
     */
    public function saltAndHash($value, $salt = null)
    {
        $salt = $salt ?: bin2hex(random_bytes(16));
        $hash = hash('sha256', $salt . $value);
        
        return [
            'hash' => $hash,
            'salt' => $salt
        ];
    }

    /**
     * Verify salted hash
     * 
     * @param string $value Value to verify
     * @param string $hash Hash to verify against
     * @param string $salt Salt used in hashing
     * @return bool True if value matches hash
     */
    public function verifySaltedHash($value, $hash, $salt)
    {
        $computedHash = hash('sha256', $salt . $value);
        return hash_equals($computedHash, $hash);
    }

    /**
     * Obfuscate sensitive data (for logging)
     * 
     * @param string $data Data to obfuscate
     * @param int $visibleChars Number of visible characters at start and end
     * @return string Obfuscated data
     */
    public function obfuscate($data, $visibleChars = 3)
    {
        $length = strlen($data);
        
        if ($length <= ($visibleChars * 2)) {
            return str_repeat('*', $length);
        }
        
        $start = substr($data, 0, $visibleChars);
        $end = substr($data, -$visibleChars);
        $middle = str_repeat('*', $length - ($visibleChars * 2));
        
        return $start . $middle . $end;
    }

    /**
     * Check if string is a valid hash format
     * 
     * @param string $hash Hash to validate
     * @param string $algo Hash algorithm
     * @return bool True if hash is valid format
     */
    public function isValidHash($hash, $algo = 'sha256')
    {
        $lengths = [
            'md5' => 32,
            'sha1' => 40,
            'sha256' => 64,
            'sha512' => 128,
            'crc32' => 8
        ];
        
        $expectedLength = $lengths[$algo] ?? null;
        
        if (!$expectedLength) {
            return false;
        }
        
        return strlen($hash) === $expectedLength && ctype_xdigit($hash);
    }
}
