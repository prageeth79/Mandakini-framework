<?php
/**
 * Encryption - Usage Examples
 * 
 * File: core/encoder/Encription.php
 * Namespace: app\core\encoder
 */

// ============================================
// INITIALIZATION
// ============================================

// 1. Create encryption instance (uses default keys from .env)
$encryption = new \app\core\encoder\Encription();

// 2. Create encryption instance with custom keys
$customEncryption = new \app\core\encoder\Encription(
    'my-custom-encryption-key',
    'my-custom-hmac-key'
);

// ============================================
// PASSWORD HASHING
// ============================================

// 3. Hash a password
$password = 'mySecurePassword123!';
$hashedPassword = $encryption->hashPassword($password);
echo "Hashed password: " . $hashedPassword . "\n";

// 4. Verify password
$isCorrect = $encryption->verifyPassword('mySecurePassword123!', $hashedPassword);
echo "Password match: " . ($isCorrect ? 'Yes' : 'No') . "\n";

// 5. Check if password needs rehashing (after algorithm updates)
if ($encryption->needsRehash($hashedPassword)) {
    $newHash = $encryption->hashPassword($password);
}

// ============================================
// DATA ENCRYPTION/DECRYPTION
// ============================================

// 6. Encrypt sensitive data
$sensitiveData = 'Credit card: 4532-1234-5678-9010';
$encrypted = $encryption->encrypt($sensitiveData);
echo "Encrypted: " . $encrypted . "\n";

// 7. Decrypt data
$decrypted = $encryption->decrypt($encrypted);
echo "Decrypted: " . $decrypted . "\n";

// 8. Encrypt database credentials
$dbConfig = json_encode([
    'host' => 'localhost',
    'user' => 'admin',
    'password' => 'secretpassword'
]);
$encryptedConfig = $encryption->encrypt($dbConfig);

// ============================================
// HMAC SIGNING & VERIFICATION
// ============================================

// 9. Sign data with HMAC
$data = 'Important transaction';
$signature = $encryption->sign($data);
echo "Signature: " . $signature . "\n";

// 10. Verify signature
$isValid = $encryption->verifySignature($data, $signature);
echo "Signature valid: " . ($isValid ? 'Yes' : 'No') . "\n";

// 11. Sign API requests
$apiData = 'GET /api/users/123';
$apiSignature = $encryption->sign($apiData);
// Send both $apiData and $apiSignature to verify request integrity

// ============================================
// TOKEN GENERATION
// ============================================

// 12. Generate secure random token
$token = $encryption->generateToken(32); // 32 bytes = 64 hex chars
echo "Token: " . $token . "\n";

// 13. Generate random string
$randomString = $encryption->generateRandomString(16);
echo "Random string: " . $randomString . "\n";

// 14. Generate with custom characters
$randomCode = $encryption->generateRandomString(8, '0123456789');
echo "Verification code: " . $randomCode . "\n";

// ============================================
// JWT-LIKE TOKENS
// ============================================

// 15. Generate JWT token with payload
$payload = [
    'user_id' => 123,
    'username' => 'john_doe',
    'email' => 'john@example.com'
];
$jwtToken = $encryption->generateJWTToken($payload, 3600); // 1 hour validity
echo "JWT Token: " . $jwtToken . "\n";

// 16. Verify and decode JWT token
$decodedPayload = $encryption->verifyJWTToken($jwtToken);
if ($decodedPayload) {
    echo "Valid token - User ID: " . $decodedPayload['user_id'] . "\n";
} else {
    echo "Invalid or expired token\n";
}

// 17. JWT with custom expiration
$longLivedToken = $encryption->generateJWTToken($payload, 86400); // 24 hours

// ============================================
// HASHING
// ============================================

// 18. Hash data with SHA256
$hash256 = $encryption->hash('some data');
echo "SHA256: " . $hash256 . "\n";

// 19. Hash data with SHA512
$hash512 = $encryption->hash512('some data');
echo "SHA512: " . $hash512 . "\n";

// 20. Hash file
$fileHash = $encryption->hashFile('public/uploads/document.pdf');
echo "File hash: " . $fileHash . "\n";

// ============================================
// API KEY GENERATION
// ============================================

// 21. Generate API key pair
$apiKeyPair = $encryption->generateAPIKeyPair();
// Output:
// [
//     'public' => 'pk_...',
//     'secret' => 'sk_...',
//     'pair_hash' => '...'
// ]

// Store only the pair_hash in database for validation
echo "Public Key: " . $apiKeyPair['public'] . "\n";
echo "Secret Key: " . $apiKeyPair['secret'] . "\n";

// ============================================
// BASE64 ENCODING/DECODING
// ============================================

// 22. Encode to base64
$encoded = $encryption->encode('Hello, World!');
echo "Encoded: " . $encoded . "\n"; // SGVsbG8sIFdvcmxkIQ==

// 23. Decode from base64
$decoded = $encryption->decode($encoded);
echo "Decoded: " . $decoded . "\n"; // Hello, World!

// ============================================
// CHECKSUM/INTEGRITY
// ============================================

// 24. Create checksum for file integrity
$fileContent = file_get_contents('public/uploads/file.zip');
$checksum = $encryption->createChecksum($fileContent);
// Store checksum in database

// 25. Verify checksum
$downloadedFile = file_get_contents('downloaded_file.zip');
$isIntact = $encryption->verifyChecksum($downloadedFile, $checksum);
echo "File integrity: " . ($isIntact ? 'Valid' : 'Corrupted') . "\n";

// ============================================
// SALT AND HASH
// ============================================

// 26. Salt and hash value
$saltedHash = $encryption->saltAndHash('sensitive_value');
// $saltedHash = [
//     'hash' => '...',
//     'salt' => '...'
// ]

// 27. Verify salted hash
$isMatch = $encryption->verifySaltedHash(
    'sensitive_value',
    $saltedHash['hash'],
    $saltedHash['salt']
);
echo "Salted hash match: " . ($isMatch ? 'Yes' : 'No') . "\n";

// ============================================
// OBFUSCATION (for logging)
// ============================================

// 28. Obfuscate sensitive data for logging
$email = 'john.doe@example.com';
$obfuscated = $encryption->obfuscate($email, 3);
echo "Obfuscated email: " . $obfuscated . "\n"; // joh*****com

$creditCard = '4532123456789010';
$obfuscatedCC = $encryption->obfuscate($creditCard, 4);
echo "Obfuscated CC: " . $obfuscatedCC . "\n"; // 4532****9010

// ============================================
// HASH VALIDATION
// ============================================

// 29. Validate hash format
$hash = 'e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855';
$isValidHash = $encryption->isValidHash($hash, 'sha256');
echo "Valid SHA256 hash: " . ($isValidHash ? 'Yes' : 'No') . "\n";

// ============================================
// CONTROLLER USAGE
// ============================================

/*
// In controllers/AuthController.php:
class AuthController extends Controller
{
    private $encryption;
    
    public function __construct()
    {
        $this->encryption = new \app\core\encoder\Encription();
    }
    
    public function register()
    {
        $request = new Request();
        $password = $request->getPost('password');
        
        // Hash password before storing
        $hashedPassword = $this->encryption->hashPassword($password);
        
        // Store user with hashed password
        $user = new User();
        $user->password = $hashedPassword;
        $user->save();
    }
    
    public function login()
    {
        $request = new Request();
        $user = User::findByEmail($request->getPost('email'));
        
        // Verify password
        if ($this->encryption->verifyPassword($request->getPost('password'), $user->password)) {
            // Generate JWT token
            $token = $this->encryption->generateJWTToken([
                'user_id' => $user->id,
                'email' => $user->email
            ]);
            
            return json_encode(['token' => $token]);
        }
    }
    
    public function protected()
    {
        $token = $this->getAuthToken();
        $payload = $this->encryption->verifyJWTToken($token);
        
        if (!$payload) {
            throw new ForbiddenException('Invalid token');
        }
        
        // Token is valid, proceed
        $userId = $payload['user_id'];
    }
}
*/

// ============================================
// MODEL USAGE
// ============================================

/*
// In models/User.php - Auto encrypt sensitive fields
class User extends Model
{
    private $encryption;
    
    protected $encryptedFields = ['social_security', 'phone'];
    
    public function __construct()
    {
        parent::__construct();
        $this->encryption = new \app\core\encoder\Encription();
    }
    
    public function save()
    {
        // Encrypt sensitive fields
        foreach ($this->encryptedFields as $field) {
            if (isset($this->attributes[$field])) {
                $this->attributes[$field] = $this->encryption->encrypt(
                    $this->attributes[$field]
                );
            }
        }
        
        parent::save();
    }
    
    public function getDecrypted($field)
    {
        return $this->encryption->decrypt($this->{$field});
    }
}
*/

// ============================================
// ENVIRONMENT CONFIGURATION
// ============================================

/*
// In .env file:
APP_KEY=your-very-long-random-encryption-key-min-32-chars
APP_HMAC_KEY=your-very-long-random-hmac-key-min-32-chars

// Generate strong keys in terminal:
// php -r "echo bin2hex(random_bytes(32));"
*/

?>
