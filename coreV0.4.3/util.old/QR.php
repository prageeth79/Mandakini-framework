<?php

namespace app\core\util;

/**
 * QR Code Generator Class
 * 
 * Generates QR codes using external API (qrserver.com)
 * No additional dependencies required
 * 
 * Usage:
 *   $qr = new QR();
 *   echo $qr->render('https://example.com');
 *   $url = $qr->getUrl('Contact Info');
 *   $base64 = $qr->getBase64('Payment Data');
 */
class QR
{
    private $apiUrl = 'https://api.qrserver.com/v1/create-qr-code/';
    private $size = 200;
    private $errorCorrection = 'M';
    private $encoding = 'UTF-8';
    private $margin = 0;

    /**
     * Constructor
     * 
     * @param int $size QR code size in pixels (default: 200)
     * @param string $errorCorrection Error correction level: L, M, Q, H (default: M)
     */
    public function __construct($size = 200, $errorCorrection = 'M')
    {
        $this->size = $size;
        $this->errorCorrection = $errorCorrection;
    }

    /**
     * Set QR code size
     * 
     * @param int $size Size in pixels
     * @return $this
     */
    public function setSize($size)
    {
        $this->size = $size;
        return $this;
    }

    /**
     * Set error correction level
     * 
     * @param string $level L (7%), M (15%), Q (25%), H (30%)
     * @return $this
     */
    public function setErrorCorrection($level)
    {
        if (in_array($level, ['L', 'M', 'Q', 'H'])) {
            $this->errorCorrection = $level;
        }
        return $this;
    }

    /**
     * Set margin around QR code
     * 
     * @param int $margin Margin in pixels
     * @return $this
     */
    public function setMargin($margin)
    {
        $this->margin = $margin;
        return $this;
    }

    /**
     * Get QR code API URL
     * 
     * @param string $data Data to encode
     * @return string Full URL to QR code image
     */
    public function getUrl($data)
    {
        $params = [
            'size' => $this->size . 'x' . $this->size,
            'data' => $data,
            'format' => 'png',
            'ecc' => $this->errorCorrection,
            'margin' => $this->margin,
            'encoding' => $this->encoding
        ];

        return $this->apiUrl . '?' . http_build_query($params);
    }

    /**
     * Get QR code as HTML img tag
     * 
     * @param string $data Data to encode
     * @param array $attributes HTML attributes for img tag
     * @return string HTML img tag
     */
    public function render($data, $attributes = [])
    {
        $url = $this->getUrl($data);
        
        $attrs = array_merge([
            'src' => $url,
            'alt' => 'QR Code',
            'width' => $this->size,
            'height' => $this->size
        ], $attributes);

        $htmlAttrs = '';
        foreach ($attrs as $key => $value) {
            $htmlAttrs .= ' ' . htmlspecialchars($key) . '="' . htmlspecialchars($value) . '"';
        }

        return '<img' . $htmlAttrs . ' />';
    }

    /**
     * Get QR code as base64 encoded data URI
     * 
     * @param string $data Data to encode
     * @return string Base64 data URI
     */
    public function getBase64($data)
    {
        $url = $this->getUrl($data);
        $imageData = @file_get_contents($url);
        
        if ($imageData === false) {
            throw new \Exception('Failed to generate QR code. Check network connection.');
        }

        return 'data:image/png;base64,' . base64_encode($imageData);
    }

    /**
     * Save QR code to file
     * 
     * @param string $data Data to encode
     * @param string $filePath Path where to save the image
     * @return bool True on success
     */
    public function save($data, $filePath)
    {
        $url = $this->getUrl($data);
        $imageData = @file_get_contents($url);
        
        if ($imageData === false) {
            throw new \Exception('Failed to generate QR code. Check network connection.');
        }

        $directory = dirname($filePath);
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        return file_put_contents($filePath, $imageData) !== false;
    }

    /**
     * Generate QR code for contact information (vCard format)
     * 
     * @param array $contact Contact data ['name', 'phone', 'email', 'organization', 'url']
     * @return string HTML img tag
     */
    public function renderContact($contact)
    {
        $vcard = "BEGIN:VCARD\nVERSION:3.0\n";
        
        if (isset($contact['name'])) {
            $vcard .= "FN:" . $this->escapeVCard($contact['name']) . "\n";
        }
        if (isset($contact['phone'])) {
            $vcard .= "TEL:" . $contact['phone'] . "\n";
        }
        if (isset($contact['email'])) {
            $vcard .= "EMAIL:" . $contact['email'] . "\n";
        }
        if (isset($contact['organization'])) {
            $vcard .= "ORG:" . $this->escapeVCard($contact['organization']) . "\n";
        }
        if (isset($contact['url'])) {
            $vcard .= "URL:" . $contact['url'] . "\n";
        }
        
        $vcard .= "END:VCARD";
        
        return $this->render($vcard);
    }

    /**
     * Generate QR code for WiFi connection
     * 
     * @param array $wifi WiFi data ['ssid', 'password', 'security', 'hidden']
     * @return string HTML img tag
     */
    public function renderWifi($wifi)
    {
        $security = isset($wifi['security']) ? strtoupper($wifi['security']) : 'WPA';
        $hidden = isset($wifi['hidden']) && $wifi['hidden'] ? 'true' : 'false';
        
        $wifiString = "WIFI:T:{$security};S:" . $this->escapeWifi($wifi['ssid']) . ";P:" . 
                      $this->escapeWifi($wifi['password']) . ";H:{$hidden};;";
        
        return $this->render($wifiString);
    }

    /**
     * Escape special characters for vCard format
     * 
     * @param string $text Text to escape
     * @return string Escaped text
     */
    private function escapeVCard($text)
    {
        return str_replace(["\n", "\r", ";", ","], ["\\n", "", "\\;", "\\,"], $text);
    }

    /**
     * Escape special characters for WiFi QR format
     * 
     * @param string $text Text to escape
     * @return string Escaped text
     */
    private function escapeWifi($text)
    {
        return preg_replace('/([;,:"])/', '\\\\$1', $text);
    }

    /**
     * Validate QR code data
     * 
     * @param string $data Data to validate
     * @return bool True if data is valid
     */
    public function validate($data)
    {
        return !empty($data) && strlen($data) <= 4296;
    }
}
