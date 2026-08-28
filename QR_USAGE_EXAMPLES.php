<?php
/**
 * QR Code Generator - Usage Examples
 * 
 * File: core/QR.php
 * Namespace: app\core
 */

// Basic Usage Examples:

// 1. Simple URL QR Code
$qr = new \app\core\QR();
echo $qr->render('https://example.com');
// Output: <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=..." alt="QR Code" width="200" height="200" />

// 2. Get QR code URL only
$url = $qr->getUrl('Your Text Here');
// Output: https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=Your+Text+Here&...

// 3. Customize size and error correction
$qr = new \app\core\QR(300, 'H'); // 300px, High error correction
echo $qr->render('https://example.com');

// 4. Generate as base64 (for emails or dynamic displays)
$base64 = $qr->getBase64('Email content');
echo '<img src="' . $base64 . '" />';

// 5. Save QR code to file
$qr->save('Certificate Data', 'public/uploads/certificate-qr.png');

// 6. Contact Information QR (vCard format)
$contact = [
    'name' => 'John Doe',
    'phone' => '+1-555-0123',
    'email' => 'john@example.com',
    'organization' => 'Company Name',
    'url' => 'https://example.com'
];
echo $qr->renderContact($contact);

// 7. WiFi Connection QR
$wifi = [
    'ssid' => 'MyWiFiNetwork',
    'password' => 'SecurePassword123',
    'security' => 'WPA' // or 'WEP', 'nopass'
];
echo $qr->renderWifi($wifi);

// 8. Method Chaining
$qr = new \app\core\QR();
echo $qr->setSize(250)
        ->setErrorCorrection('H')
        ->setMargin(10)
        ->render('Certificate #12345');

// 9. Using in Controllers
// In controllers/YourController.php:
// $qr = new QR(200, 'M');
// $data['qrCode'] = $qr->render('https://myapp.com/certificates/abc123');
// return $this->view('certificateView', $data);

// 10. Using in Views
// In views/certificateView.php:
// <?php echo isset($qrCode) ? $qrCode : ''; ?>

?>
