<?php

namespace app\core\util;

/**
 * Barcode Generator Class
 * 
 * Generates barcodes in multiple formats using barcode API
 * No additional dependencies required
 * 
 * Supported Formats:
 * CODE128, CODE39, EAN13, EAN8, UPC-A, UPC-E, Codabar, MSI, PZN
 * 
 * Usage:
 *   $barcode = new Barcode('CODE128');
 *   echo $barcode->render('123456789');
 *   $url = $barcode->getUrl('123456789');
 *   $barcode->save('123456789', 'public/barcodes/product.png');
 */
class Barcode
{
    private $format;
    private $apiUrl = 'https://barcodeapi.org/api/';
    private $width = 300;
    private $height = 80;
    private $encoding = 'UTF-8';
    private $showText = true;
    
    // Supported barcode formats
    const FORMAT_CODE128 = 'code128';
    const FORMAT_CODE39 = 'code39';
    const FORMAT_EAN13 = 'ean13';
    const FORMAT_EAN8 = 'ean8';
    const FORMAT_UPC_A = 'upca';
    const FORMAT_UPC_E = 'upce';
    const FORMAT_CODABAR = 'codabar';
    const FORMAT_MSI = 'msi';
    const FORMAT_PZN = 'pzn';

    private $supportedFormats = [
        'CODE128', 'CODE39', 'EAN13', 'EAN8', 'UPC-A', 'UPC-E', 
        'Codabar', 'MSI', 'PZN', 'code128', 'code39', 'ean13', 
        'ean8', 'upca', 'upce', 'codabar', 'msi', 'pzn'
    ];

    /**
     * Constructor
     * 
     * @param string $format Barcode format (default: CODE128)
     * @param int $width Width in pixels (default: 300)
     * @param int $height Height in pixels (default: 80)
     */
    public function __construct($format = 'CODE128', $width = 300, $height = 80)
    {
        $this->setFormat($format);
        $this->width = $width;
        $this->height = $height;
    }

    /**
     * Set barcode format
     * 
     * @param string $format Barcode format
     * @return $this
     */
    public function setFormat($format)
    {
        $normalizedFormat = strtolower(str_replace('-', '', $format));
        
        if (in_array($format, $this->supportedFormats) || in_array($normalizedFormat, array_map('strtolower', $this->supportedFormats))) {
            $this->format = $normalizedFormat;
        } else {
            throw new \InvalidArgumentException("Unsupported barcode format: {$format}");
        }
        
        return $this;
    }

    /**
     * Set barcode width
     * 
     * @param int $width Width in pixels
     * @return $this
     */
    public function setWidth($width)
    {
        $this->width = $width;
        return $this;
    }

    /**
     * Set barcode height
     * 
     * @param int $height Height in pixels
     * @return $this
     */
    public function setHeight($height)
    {
        $this->height = $height;
        return $this;
    }

    /**
     * Toggle text display below barcode
     * 
     * @param bool $show Show or hide barcode text
     * @return $this
     */
    public function showText($show = true)
    {
        $this->showText = $show;
        return $this;
    }

    /**
     * Get barcode image URL
     * 
     * @param string $data Data to encode
     * @return string URL to barcode image
     */
    public function getUrl($data)
    {
        if (!$this->validate($data)) {
            throw new \InvalidArgumentException("Invalid barcode data for format {$this->format}");
        }

        $params = [
            'format' => $this->format,
            'value' => $data,
            'width' => $this->width,
            'height' => $this->height,
            'text' => $this->showText ? 'true' : 'false'
        ];

        return $this->apiUrl . '?' . http_build_query($params);
    }

    /**
     * Get barcode as HTML img tag
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
            'alt' => 'Barcode: ' . htmlspecialchars($data),
            'width' => $this->width,
            'height' => $this->height,
            'style' => 'display: block;'
        ], $attributes);

        $htmlAttrs = '';
        foreach ($attrs as $key => $value) {
            $htmlAttrs .= ' ' . htmlspecialchars($key) . '="' . htmlspecialchars($value) . '"';
        }

        return '<img' . $htmlAttrs . ' />';
    }

    /**
     * Get barcode as base64 encoded data URI
     * 
     * @param string $data Data to encode
     * @return string Base64 data URI
     */
    public function getBase64($data)
    {
        $url = $this->getUrl($data);
        $imageData = @file_get_contents($url);
        
        if ($imageData === false) {
            throw new \Exception('Failed to generate barcode. Check network connection.');
        }

        return 'data:image/png;base64,' . base64_encode($imageData);
    }

    /**
     * Save barcode to file
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
            throw new \Exception('Failed to generate barcode. Check network connection.');
        }

        $directory = dirname($filePath);
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        return file_put_contents($filePath, $imageData) !== false;
    }

    /**
     * Generate barcode for product (EAN13)
     * 
     * @param string $productCode Product code
     * @param string $productName Optional product name
     * @return string HTML img tag with product info
     */
    public function renderProduct($productCode, $productName = null)
    {
        $html = '';
        if ($productName) {
            $html .= '<div style="text-align: center; margin-bottom: 10px;">';
            $html .= '<strong>' . htmlspecialchars($productName) . '</strong>';
            $html .= '</div>';
        }
        
        $barcode = new self('EAN13', 400, 100);
        $html .= $barcode->render($productCode);
        
        return $html;
    }

    /**
     * Generate barcode for shipping (CODE128)
     * 
     * @param string $trackingNumber Tracking number
     * @return string HTML img tag
     */
    public function renderShipping($trackingNumber)
    {
        $barcode = new self('CODE128', 350, 80);
        return $barcode->render($trackingNumber);
    }

    /**
     * Generate barcode for inventory (CODE39)
     * 
     * @param string $inventoryCode Inventory code
     * @return string HTML img tag
     */
    public function renderInventory($inventoryCode)
    {
        $barcode = new self('CODE39', 300, 80);
        return $barcode->render($inventoryCode);
    }

    /**
     * Validate barcode data
     * 
     * @param string $data Data to validate
     * @return bool True if data is valid
     */
    public function validate($data)
    {
        if (empty($data)) {
            return false;
        }

        // Format-specific validation
        switch ($this->format) {
            case 'ean13':
                return $this->validateEAN13($data);
            case 'ean8':
                return $this->validateEAN8($data);
            case 'upca':
                return $this->validateUPCA($data);
            case 'upce':
                return $this->validateUPCE($data);
            default:
                // Most formats support up to 100 characters
                return strlen($data) <= 100;
        }
    }

    /**
     * Validate EAN13 barcode
     * 
     * @param string $data EAN13 data
     * @return bool
     */
    private function validateEAN13($data)
    {
        if (!preg_match('/^\d{12}$/', $data)) {
            return false;
        }
        
        // Calculate check digit
        $sum = 0;
        for ($i = 0; $i < 12; $i++) {
            $sum += (int)$data[$i] * (($i % 2) ? 1 : 3);
        }
        
        $checkDigit = (10 - ($sum % 10)) % 10;
        
        // For full validation, the check digit should be included
        return true; // Simplified validation
    }

    /**
     * Validate EAN8 barcode
     * 
     * @param string $data EAN8 data
     * @return bool
     */
    private function validateEAN8($data)
    {
        return preg_match('/^\d{8}$/', $data) ? true : false;
    }

    /**
     * Validate UPC-A barcode
     * 
     * @param string $data UPC-A data
     * @return bool
     */
    private function validateUPCA($data)
    {
        return preg_match('/^\d{11}$/', $data) ? true : false;
    }

    /**
     * Validate UPC-E barcode
     * 
     * @param string $data UPC-E data
     * @return bool
     */
    private function validateUPCE($data)
    {
        return preg_match('/^\d{6}$/', $data) ? true : false;
    }

    /**
     * Get list of supported formats
     * 
     * @return array Supported barcode formats
     */
    public static function getSupportedFormats()
    {
        return [
            'CODE128' => 'Most flexible, supports all ASCII characters',
            'CODE39' => 'Alphanumeric, commonly used in retail',
            'EAN13' => 'European Article Number, 13 digits',
            'EAN8' => 'Short EAN, 8 digits',
            'UPC-A' => 'Universal Product Code A, 12 digits',
            'UPC-E' => 'Universal Product Code E, 6 digits',
            'Codabar' => 'Used in libraries and logistics',
            'MSI' => 'Modified Plessey, variable length',
            'PZN' => 'German Pharmaceutical barcode'
        ];
    }

    /**
     * Get current barcode format
     * 
     * @return string Current format
     */
    public function getFormat()
    {
        return $this->format;
    }

    /**
     * Get barcode dimensions
     * 
     * @return array Array with 'width' and 'height' keys
     */
    public function getDimensions()
    {
        return [
            'width' => $this->width,
            'height' => $this->height
        ];
    }
}
