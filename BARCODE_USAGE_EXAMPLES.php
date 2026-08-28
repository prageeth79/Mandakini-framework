<?php
/**
 * Barcode Generator - Usage Examples
 * 
 * File: core/Barcode.php
 * Namespace: app\core
 */

// ============================================
// BASIC USAGE EXAMPLES
// ============================================

// 1. Simple CODE128 barcode
$barcode = new \app\core\Barcode('CODE128');
echo $barcode->render('123456789ABC');

// 2. Different barcode formats
$barcode = new \app\core\Barcode('CODE39', 300, 80);
echo $barcode->render('ABC123');

$barcode = new \app\core\Barcode('EAN13', 400, 100);
echo $barcode->render('5901234123457');

// 3. Get barcode URL only
$barcode = new \app\core\Barcode('CODE128');
$url = $barcode->getUrl('PRODUCT001');
echo '<img src="' . $url . '" />';

// ============================================
// CUSTOMIZATION EXAMPLES
// ============================================

// 4. Custom dimensions and properties
$barcode = new \app\core\Barcode('CODE128');
$barcode->setWidth(500)
        ->setHeight(100)
        ->showText(false); // Hide text below barcode
echo $barcode->render('123456789');

// 5. Method chaining
$barcode = new \app\core\Barcode();
echo $barcode->setFormat('CODE39')
            ->setWidth(350)
            ->setHeight(90)
            ->showText(true)
            ->render('INV-2026-001');

// ============================================
// SPECIALIZED BARCODES
// ============================================

// 6. Product barcode (EAN13)
$barcode = new \app\core\Barcode();
echo $barcode->renderProduct('5901234123457', 'Product Name');

// 7. Shipping tracking barcode (CODE128)
$barcode = new \app\core\Barcode();
echo $barcode->renderShipping('1Z999AA10123456784');

// 8. Inventory barcode (CODE39)
$barcode = new \app\core\Barcode();
echo $barcode->renderInventory('STOCK-2026-150');

// ============================================
// FILE OPERATIONS
// ============================================

// 9. Save barcode to file
$barcode = new \app\core\Barcode('CODE128');
$barcode->save('INVOICE-001', 'public/barcodes/invoice-001.png');

// 10. Get barcode as base64 (for emails)
$barcode = new \app\core\Barcode('EAN13');
$base64 = $barcode->getBase64('5901234123457');
echo '<img src="' . $base64 . '" />';

// ============================================
// CONTROLLER USAGE
// ============================================

/*
// In controllers/ProductController.php:
class ProductController extends Controller
{
    public function detail($id)
    {
        $product = Product::findById($id);
        
        // Generate product barcode
        $barcode = new Barcode('EAN13', 400, 100);
        $productData = [
            'barcode' => $barcode->render($product->ean),
            'product' => $product
        ];
        
        return $this->view('productDetailView', $productData);
    }
    
    public function downloadBarcode($id)
    {
        $product = Product::findById($id);
        $barcode = new Barcode('CODE128');
        $barcode->save($product->code, 'public/barcodes/' . $product->code . '.png');
        
        return $this->download('public/barcodes/' . $product->code . '.png');
    }
}
*/

// ============================================
// VIEW USAGE
// ============================================

/*
// In views/productDetailView.php:
<?php
    if (isset($barcode)) {
        echo '<div class="barcode-section">';
        echo $barcode;
        echo '</div>';
    }
?>
*/

// ============================================
// SUPPORTED FORMATS
// ============================================

$supportedFormats = \app\core\Barcode::getSupportedFormats();
foreach ($supportedFormats as $format => $description) {
    echo "$format: $description\n";
}

// Outputs:
// CODE128: Most flexible, supports all ASCII characters
// CODE39: Alphanumeric, commonly used in retail
// EAN13: European Article Number, 13 digits
// EAN8: Short EAN, 8 digits
// UPC-A: Universal Product Code A, 12 digits
// UPC-E: Universal Product Code E, 6 digits
// Codabar: Used in libraries and logistics
// MSI: Modified Plessey, variable length
// PZN: German Pharmaceutical barcode

// ============================================
// ERROR HANDLING
// ============================================

// 11. Try-catch for error handling
try {
    $barcode = new \app\core\Barcode('INVALID_FORMAT');
} catch (\InvalidArgumentException $e) {
    echo "Error: " . $e->getMessage();
}

try {
    $barcode = new \app\core\Barcode('EAN13');
    $barcode->render('invalid-data');
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage();
}

// ============================================
// VALIDATION
// ============================================

// 12. Validate barcode data before generating
$barcode = new \app\core\Barcode('EAN13');
$data = '5901234123457';

if ($barcode->validate($data)) {
    echo $barcode->render($data);
} else {
    echo "Invalid barcode data";
}

// ============================================
// BATCH BARCODE GENERATION
// ============================================

// 13. Generate multiple barcodes
$products = [
    ['code' => 'PROD001', 'name' => 'Product 1'],
    ['code' => 'PROD002', 'name' => 'Product 2'],
    ['code' => 'PROD003', 'name' => 'Product 3']
];

$barcode = new \app\core\Barcode('CODE128');
foreach ($products as $product) {
    echo '<div class="product-barcode">';
    echo '<h3>' . $product['name'] . '</h3>';
    echo $barcode->render($product['code']);
    echo '</div>';
}

?>
