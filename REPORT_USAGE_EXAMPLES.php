<?php
/**
 * Report Generator - Usage Examples
 * 
 * File: core/Report.php
 * Namespace: app\core
 */

// ============================================
// BASIC REPORT GENERATION
// ============================================

// 1. Create a simple report
$report = new \app\core\Report('Sales Report');
$report->addColumns(['Product', 'Quantity', 'Price', 'Total']);
$report->addRows([
    ['Laptop', 5, 1200, 6000],
    ['Mouse', 15, 25, 375],
    ['Keyboard', 10, 75, 750],
    ['Monitor', 3, 350, 1050]
]);

// Export to CSV
$report->exportCSV('public/reports/sales_report.csv');

// 2. Export same report to JSON
$report->exportJSON('public/reports/sales_report.json');

// 3. Export to HTML
$report->exportHTML('public/reports/sales_report.html');

// 4. Display report in browser
echo $report->display();

// ============================================
// FILTERED REPORTS
// ============================================

// 5. Create report with filters
$report = new \app\core\Report('Products Over 500');
$report->addColumns(['Product', 'Quantity', 'Price', 'Total']);
$report->addRows([
    ['Laptop', 5, 1200, 6000],
    ['Mouse', 15, 25, 375],
    ['Keyboard', 10, 75, 750],
    ['Monitor', 3, 350, 1050]
]);

// Filter for Total >= 1000
$report->addFilter('Total', '>=', 1000);
$report->exportCSV('public/reports/expensive_products.csv');

// 6. Multiple filters
$report = new \app\core\Report('Quality Products');
$report->addColumns(['Product', 'Quantity', 'Price', 'Total']);
$report->addRows([
    ['Laptop', 5, 1200, 6000],
    ['Mouse', 15, 25, 375],
    ['Keyboard', 10, 75, 750],
    ['Monitor', 3, 350, 1050]
]);

$report->addFilter('Total', '>=', 1000)
       ->addFilter('Quantity', '>=', 3);
echo $report->display();

// ============================================
// SORTED REPORTS
// ============================================

// 7. Sort by column
$report = new \app\core\Report('Top Selling Products');
$report->addColumns(['Product', 'Quantity', 'Price', 'Total']);
$report->addRows([
    ['Laptop', 5, 1200, 6000],
    ['Mouse', 15, 25, 375],
    ['Keyboard', 10, 75, 750],
    ['Monitor', 3, 350, 1050]
]);

// Sort by Total descending
$report->sortBy('Total', 'DESC');
$report->exportHTML('public/reports/top_products.html');

// 8. Sort ascending
$report->sortBy('Price', 'ASC');
echo $report->display();

// ============================================
// SUMMARY AND CALCULATIONS
// ============================================

// 9. Add summary information
$report = new \app\core\Report('Quarterly Sales Report');
$report->addColumns(['Month', 'Sales', 'Expenses', 'Profit']);
$report->addRows([
    ['January', 50000, 20000, 30000],
    ['February', 55000, 22000, 33000],
    ['March', 60000, 25000, 35000]
]);

$report->addSummary('Period', 'Q1 2026');
$report->addSummary('Department', 'Sales');
$report->addSummary('Prepared By', 'John Doe');

$report->exportHTML('public/reports/quarterly.html');

// 10. Add calculations (aggregates)
$report = new \app\core\Report('Product Sales Analysis');
$report->addColumns(['Product', 'Units Sold', 'Unit Price', 'Total Revenue']);
$report->addRows([
    ['Laptop', 5, 1200, 6000],
    ['Mouse', 15, 25, 375],
    ['Keyboard', 10, 75, 750],
    ['Monitor', 3, 350, 1050],
    ['Printer', 2, 400, 800]
]);

// Add calculations
$report->addCalculation('Total Units', 'Units Sold', 'SUM');
$report->addCalculation('Total Revenue', 'Total Revenue', 'SUM');
$report->addCalculation('Average Price', 'Unit Price', 'AVG');
$report->addCalculation('Max Price', 'Unit Price', 'MAX');
$report->addCalculation('Min Price', 'Unit Price', 'MIN');

$report->exportHTML('public/reports/sales_analysis.html');

// ============================================
// ADVANCED FILTERING
// ============================================

// 11. LIKE filter for text search
$report = new \app\core\Report('IT Products');
$report->addColumns(['Product', 'Category', 'Price']);
$report->addRows([
    ['Laptop HP', 'Computers', 1200],
    ['Mouse Logitech', 'Accessories', 25],
    ['Keyboard', 'Accessories', 75],
    ['Keyboard HP', 'Accessories', 150],
    ['Printer HP', 'Devices', 400]
]);

$report->addFilter('Product', 'LIKE', 'HP');
echo $report->display();

// 12. IN filter for multiple values
$report = new \app\core\Report('Selected Categories');
$report->addColumns(['Product', 'Category', 'Price']);
$report->addRows([
    ['Laptop HP', 'Computers', 1200],
    ['Mouse Logitech', 'Accessories', 25],
    ['Keyboard', 'Accessories', 75],
    ['Printer HP', 'Devices', 400]
]);

$report->addFilter('Category', 'IN', ['Computers', 'Devices']);
echo $report->display();

// ============================================
// METADATA AND HEADERS
// ============================================

// 13. Add metadata
$report = new \app\core\Report('Employee Payroll');
$report->addColumns(['Name', 'Position', 'Salary', 'Department']);
$report->addRows([
    ['John Doe', 'Developer', 65000, 'IT'],
    ['Jane Smith', 'Manager', 75000, 'Sales'],
    ['Bob Wilson', 'Designer', 55000, 'IT']
]);

$report->addMetadata('Company', 'Acme Corp');
$report->addMetadata('Fiscal Year', '2026');
$report->addMetadata('Currency', 'USD');

$report->setHeader('This is the monthly payroll report for Q1 2026.');
$report->setFooter('Confidential - For authorized personnel only.');

$report->exportHTML('public/reports/payroll.html');

// ============================================
// CONTROLLER USAGE
// ============================================

/*
// In controllers/ReportController.php:
class ReportController extends Controller
{
    public function sales()
    {
        // Get data from model
        $sales = SalesModel::all();
        
        // Create report
        $report = new Report('Monthly Sales Report');
        $report->addColumns(['Date', 'Product', 'Amount', 'Customer']);
        
        // Format data for report
        $rows = [];
        foreach ($sales as $sale) {
            $rows[] = [
                $sale->date,
                $sale->product_name,
                $sale->amount,
                $sale->customer_name
            ];
        }
        
        $report->addRows($rows);
        $report->sortBy('Amount', 'DESC');
        $report->addCalculation('Total Sales', 'Amount', 'SUM');
        
        return $this->view('reportView', ['html' => $report->display()]);
    }
    
    public function exportSalesCSV()
    {
        $sales = SalesModel::all();
        
        $report = new Report('Sales Export');
        $report->addColumns(['Date', 'Product', 'Amount']);
        
        $rows = [];
        foreach ($sales as $sale) {
            $rows[] = [$sale->date, $sale->product_name, $sale->amount];
        }
        
        $report->addRows($rows);
        $report->exportCSV('public/reports/sales_' . date('Y-m-d') . '.csv');
        
        return 'Report exported successfully';
    }
}
*/

// ============================================
// CHAINING OPERATIONS
// ============================================

// 14. Method chaining
$report = new \app\core\Report('Chained Report')->addColumns(['Name', 'Score', 'Grade'])->addRows([
        ['Student A', 85, 'B'],
        ['Student B', 92, 'A'],
        ['Student C', 78, 'C'],
        ['Student D', 88, 'B']
    ])
    ->addFilter('Score', '>=', 80)
    ->sortBy('Score', 'DESC')
    ->addCalculation('Average Score', 'Score', 'AVG')
    ->addSummary('Class', 'CS-101')
    ->setHeader('Student Performance Report');

echo $report->display();
$report->exportHTML('public/reports/chained.html');

// ============================================
// STATISTICS AND INFO
// ============================================

// 15. Get report statistics
$report = new \app\core\Report('Statistics Demo');
$report->addColumns(['Item', 'Value']);
$report->addRows([
    ['Item 1', 100],
    ['Item 2', 200],
    ['Item 3', 150]
]);

$stats = $report->getStatistics();
// Returns: [
//     'total_rows' => 3,
//     'total_columns' => 2,
//     'generated' => '2026-08-28 12:00:00',
//     'title' => 'Statistics Demo'
// ]

// 16. Get as array
$reportArray = $report->toArray();
// Returns complete report as associative array

// ============================================
// BATCH REPORT GENERATION
// ============================================

// 17. Generate multiple reports from same data
$data = [
    ['John', 'Dept A', 50000],
    ['Jane', 'Dept B', 60000],
    ['Bob', 'Dept A', 55000],
    ['Alice', 'Dept B', 65000]
];

// Report 1: All employees
$report1 = new \app\core\Report('Employee Roster');
$report1->addColumns(['Name', 'Department', 'Salary'])
        ->addRows($data)
        ->exportCSV('public/reports/all_employees.csv');

// Report 2: Dept A only
$report2 = new \app\core\Report('Department A Roster');
$report2->addColumns(['Name', 'Department', 'Salary'])
        ->addRows($data)
        ->addFilter('Department', '=', 'Dept A')
        ->exportCSV('public/reports/dept_a.csv');

// Report 3: High earners only
$report3 = new \app\core\Report('High Earners');
$report3->addColumns(['Name', 'Department', 'Salary'])
        ->addRows($data)
        ->addFilter('Salary', '>=', 60000)
        ->exportCSV('public/reports/high_earners.csv');

// ============================================
// CLONING REPORTS
// ============================================

// 18. Clone report with new title
$baseReport = new \app\core\Report('Base Report');
$baseReport->addColumns(['ID', 'Name', 'Value'])
           ->addRows([
               [1, 'Item A', 100],
               [2, 'Item B', 200],
               [3, 'Item C', 150]
           ]);

$clonedReport = $baseReport->cloneAs('Cloned Report');
$clonedReport->exportHTML('public/reports/cloned.html');

// ============================================
// CLEAR AND RESET
// ============================================

// 19. Clear report data
$report = new \app\core\Report('Test Report');
$report->addColumns(['A', 'B'])
       ->addRows([[1, 2], [3, 4]]);

$report->clear(); // Clear all data
$report->addColumns(['X', 'Y'])
       ->addRows([[10, 20]])
       ->exportCSV('public/reports/reset.csv');

// ============================================
// ERROR HANDLING
// ============================================

// 20. Validate and handle errors
$report = new \app\core\Report('Error Handling Example');
$report->addColumns(['Column A', 'Column B']);

try {
    if (!$report->exportCSV('public/reports/test.csv')) {
        throw new Exception('Failed to export report');
    }
    echo "Report exported successfully";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

?>
