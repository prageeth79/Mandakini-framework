# Mandakini MVC Framework
# Report V3 — User Manual

## Table of Contents

1. [Introduction](#1-introduction)
2. [Creating a Report](#2-creating-a-report)
3. [Report Titles](#3-report-titles)
4. [Adding Columns](#4-adding-columns)
5. [Adding Rows](#5-adding-rows)
6. [Using QueryBuilder](#6-using-querybuilder)
7. [Filtering](#7-filtering)
8. [Sorting](#8-sorting)
9. [Grouping](#9-grouping)
10. [Calculations](#10-calculations)
11. [Group Calculations](#11-group-calculations)
12. [Value Formatting](#12-value-formatting)
13. [Custom Formatters](#13-custom-formatters)
14. [Conditional Formatting](#14-conditional-formatting)
15. [Styles](#15-styles)
16. [Headers and Footers](#16-headers-and-footers)
17. [Logos](#17-logos)
18. [Generated Metadata](#18-generated-metadata)
19. [Page Numbers](#19-page-numbers)
20. [Repeating Table Headers](#20-repeating-table-headers)
21. [Striped Rows](#21-striped-rows)
22. [Page Size and Orientation](#22-page-size-and-orientation)
23. [Report Templates](#23-report-templates)
24. [Template Locations](#24-template-locations)
25. [Custom Template Path](#25-custom-template-path)
26. [Template API](#26-template-api)
27. [Parameters](#27-parameters)
28. [Summary Information](#28-summary-information)
29. [Metadata](#29-metadata)
30. [Raw and Processed Rows](#30-raw-and-processed-rows)
31. [Reading Row Values](#31-reading-row-values)
32. [Statistics](#32-statistics)
33. [Convert Report to Array](#33-convert-report-to-array)
34. [Output Formats](#34-output-formats)
35. [Saving Reports](#35-saving-reports)
36. [Downloading Reports](#36-downloading-reports)
37. [Clearing a Report](#37-clearing-a-report)
38. [Cloning a Report](#38-cloning-a-report)
39. [QueryBuilder vs Report Filtering](#39-querybuilder-vs-report-filtering)
40. [Recommended Architecture](#40-recommended-architecture)
41. [Complete Example](#41-complete-example)
42. [Troubleshooting](#42-troubleshooting)
43. [Quick Reference](#43-quick-reference)

---

# 1. Introduction

`Report V3` is the reporting engine of the Mandakini MVC Framework.

It provides a common API for creating reports and exporting them into different formats.

Supported output formats:

- HTML
- PDF
- CSV
- JSON
- Excel (`.xlsx`)

Report V3 supports:

- Manual data
- QueryBuilder data
- Columns
- Column formatting
- Filtering
- Sorting
- Grouping
- Calculations
- Group calculations
- Conditional formatting
- Custom styles
- Headers
- Footers
- Logos
- Page size
- Page orientation
- Page numbers
- Repeated table headers
- Templates
- Parameters
- Metadata
- Multiple renderers

The basic architecture is:

```text
Data
  |
  v
QueryBuilder / Manual Rows
  |
  v
Report V3
  |
  +---- Filtering
  |
  +---- Sorting
  |
  +---- Grouping
  |
  +---- Calculations
  |
  +---- Formatting
  |
  v
Renderer
  |
  +---- HTML
  +---- PDF
  +---- CSV
  +---- JSON
  +---- Excel
```

---

# 2. Creating a Report

The recommended way to create a report is:

```php
use app\core\util\Report;

$report = Report::make(
    'Sales Report',
    'Admin'
);
```

The first parameter is the report title.

The second parameter is the person or system that generated the report.

You can also use:

```php
$report = new Report(
    'Sales Report',
    'Admin'
);
```

However, `Report::make()` is usually cleaner when using method chaining.

---

# 3. Report Titles

Set the title:

```php
$report->setTitle(
    'Monthly Sales Report'
);
```

Set who generated it:

```php
$report->setGeneratedBy(
    'Admin'
);
```

Both methods return `$this`, so they can be chained:

```php
$report
    ->setTitle('Monthly Sales Report')
    ->setGeneratedBy('Admin');
```

---

# 4. Adding Columns

## Simple Columns

The easiest way to add columns is:

```php
$report->addColumns([
    'name'   => 'Name',
    'amount' => 'Amount',
    'date'   => 'Date',
]);
```

The array key is the field name.

The array value is the displayed column heading.

For example:

```php
'amount' => 'Amount'
```

means:

```text
Data field: amount
Displayed heading: Amount
```

---

## Numeric Column Definition

You can also use:

```php
$report->addColumns([
    'name',
    'amount',
    'date'
]);
```

In this case the field name and label are the same.

---

# 5. Adding Rows

## Add One Row

```php
$report->addRow([
    'name' => 'Kamal',
    'amount' => 1250.50,
    'date' => '2026-08-29',
]);
```

---

## Add Multiple Rows

```php
$report->addRows([
    [
        'name' => 'Kamal',
        'amount' => 1250.50,
        'date' => '2026-08-29',
    ],
    [
        'name' => 'Nimal',
        'amount' => 900.00,
        'date' => '2026-08-28',
    ],
]);
```

Rows can be:

- Arrays
- Objects

Example object:

```php
$user = new stdClass();

$user->name = 'Kamal';
$user->amount = 1250;
$user->date = '2026-08-29';

$report->addRow($user);
```

---

# 6. Using QueryBuilder

Report V3 supports the Mandakini `QueryBuilder`.

Import it if necessary:

```php
use app\core\db\QueryBuilder;
```

Then create your query:

```php
$query = User::query()
    ->where(
        'status',
        '=',
        'active'
    );
```

Attach it to the report:

```php
$report = Report::make(
    'Active Users',
    'Admin'
)
    ->query($query)
    ->addColumns([
        'name' => 'Name',
        'email' => 'Email',
    ]);
```

When Report V3 needs the rows, it checks whether the QueryBuilder provides:

```php
getRaw()
```

If available:

```php
$query->getRaw()
```

is used.

Otherwise:

```php
$query->get()
```

is used.

This allows Report V3 to work with both versions of the QueryBuilder.

---

# 7. Filtering

Report-level filtering is performed using:

```php
addFilter()
```

Example:

```php
$report->addFilter(
    'amount',
    '>',
    1000
);
```

Supported operators:

```text
=
!=
<>
>
<
>=
<=
LIKE
IN
NOT IN
IS NULL
IS NOT NULL
```

---

## Equal

```php
$report->addFilter(
    'status',
    '=',
    'active'
);
```

---

## Greater Than

```php
$report->addFilter(
    'amount',
    '>',
    1000
);
```

---

## Less Than

```php
$report->addFilter(
    'amount',
    '<',
    5000
);
```

---

## Greater Than or Equal

```php
$report->addFilter(
    'amount',
    '>=',
    1000
);
```

---

## Less Than or Equal

```php
$report->addFilter(
    'amount',
    '<=',
    5000
);
```

---

## LIKE

```php
$report->addFilter(
    'name',
    'LIKE',
    'kam'
);
```

The comparison is case-insensitive.

---

## IN

```php
$report->addFilter(
    'status',
    'IN',
    [
        'active',
        'pending'
    ]
);
```

---

## NOT IN

```php
$report->addFilter(
    'status',
    'NOT IN',
    [
        'deleted',
        'blocked'
    ]
);
```

---

## IS NULL

```php
$report->addFilter(
    'email',
    'IS NULL',
    null
);
```

---

## IS NOT NULL

```php
$report->addFilter(
    'email',
    'IS NOT NULL',
    null
);
```

---

## Multiple Filters

Multiple filters are combined using `AND`.

Example:

```php
$report
    ->addFilter(
        'marks',
        '>',
        0
    )
    ->addFilter(
        'marks',
        '<',
        100
    );
```

This means:

```sql
marks > 0 AND marks < 100
```

This is useful when the same field must be compared more than once.

---

# 8. Sorting

Sort the processed report rows:

```php
$report->sortBy(
    'amount',
    'DESC'
);
```

Ascending:

```php
$report->sortBy(
    'name',
    'ASC'
);
```

Only these directions are accepted:

```text
ASC
DESC
```

Invalid directions generate an exception.

---

# 9. Grouping

Group the report by a field:

```php
$report->groupBy(
    'department'
);
```

Get the resulting groups:

```php
$groups = $report->getGroups();
```

Each group contains:

```php
[
    'key' => ...,
    'rows' => [...],
    'calculations' => [...]
]
```

Example:

```php
foreach ($report->getGroups() as $group) {

    echo $group['key'];

    foreach ($group['rows'] as $row) {
        // process row
    }

}
```

Grouping is primarily presentation-oriented in Report V3.

---

# 10. Calculations

Report V3 supports:

```text
SUM
AVG
MIN
MAX
COUNT
```

---

## SUM

```php
$report->addCalculation(
    'Total Sales',
    'amount',
    'SUM'
);
```

---

## AVG

```php
$report->addCalculation(
    'Average Sale',
    'amount',
    'AVG'
);
```

---

## MIN

```php
$report->addCalculation(
    'Minimum Sale',
    'amount',
    'MIN'
);
```

---

## MAX

```php
$report->addCalculation(
    'Maximum Sale',
    'amount',
    'MAX'
);
```

---

## COUNT

```php
$report->addCalculation(
    'Number of Sales',
    'amount',
    'COUNT'
);
```

---

## Getting Calculations

```php
$results = $report->calculateAggregates();
```

Example result:

```php
[
    'Total Sales' => 2150.50,
    'Average Sale' => 1075.25,
]
```

For numeric calculations, Report V3 only includes numeric values.

---

# 11. Group Calculations

Group calculations allow each group to have its own aggregate.

Example:

```php
$report
    ->groupBy('department')
    ->addGroupCalculation(
        'Department Total',
        'amount',
        'SUM'
    );
```

Then:

```php
$groups = $report->getGroups();
```

Each group will contain:

```php
[
    'key' => 'Sales',

    'rows' => [...],

    'calculations' => [
        'Department Total' => 25000
    ]
]
```

---

# 12. Value Formatting

Report V3 provides built-in formatting.

Supported formats include:

```text
integer
number
decimal
currency
percent
percentage
date
datetime
boolean
```

---

## Integer

```php
$report->addColumn(
    'quantity',
    'Quantity',
    [
        'format' => 'integer'
    ]
);
```

---

## Number

```php
$report->addColumn(
    'amount',
    'Amount',
    [
        'format' => 'number',
        'decimals' => 2
    ]
);
```

Example:

```text
1250.5
```

becomes:

```text
1,250.50
```

---

## Currency

```php
$report->addColumn(
    'amount',
    'Amount',
    [
        'format' => 'currency',
        'currency_symbol' => 'Rs. ',
        'decimals' => 2
    ]
);
```

Example:

```text
Rs. 1,250.50
```

---

## Percentage

```php
$report->addColumn(
    'rate',
    'Rate',
    [
        'format' => 'percentage',
        'decimals' => 2
    ]
);
```

---

## Date

```php
$report->addColumn(
    'date',
    'Date',
    [
        'format' => 'date',
        'date_format' => 'd/m/Y'
    ]
);
```

---

## DateTime

```php
$report->addColumn(
    'created_at',
    'Created',
    [
        'format' => 'datetime',
        'date_format' => 'd/m/Y H:i'
    ]
);
```

---

## Boolean

```php
$report->addColumn(
    'active',
    'Active',
    [
        'format' => 'boolean',
        'true_text' => 'Yes',
        'false_text' => 'No'
    ]
);
```

---

## NULL Values

You can specify what should be displayed for `null`:

```php
$report->addColumn(
    'phone',
    'Phone',
    [
        'null_text' => 'Not Available'
    ]
);
```

---

# 13. Custom Formatters

Columns can use a custom PHP closure.

Example:

```php
$report->addColumn(
    'amount',
    'Amount',
    [
        'formatter' => function (
            $value,
            $column,
            $report
        ) {
            return 'Rs. ' .
                number_format($value, 2);
        }
    ]
);
```

The formatter receives:

```text
$value
$column
$report
```

This allows custom formatting that is not covered by the built-in formats.

---

# 14. Conditional Formatting

Conditional formatting allows a style to be applied when a value meets a condition.

Example:

```php
$report->addConditionalFormat(
    'amount',
    '>',
    10000,
    [
        'font-weight' => 'bold'
    ]
);
```

Another example:

```php
$report->addConditionalFormat(
    'amount',
    '<',
    1000,
    [
        'color' => '#cc0000',
        'font-weight' => 'bold'
    ]
);
```

Supported operators:

```text
=
!=
<>
>
<
>=
<=
LIKE
IN
```

Conditional formatting is stored by the Report object and interpreted by the renderer/template.

---

# 15. Styles

Set an individual style:

```php
$report->setStyle(
    'header_background',
    '#1f4e78'
);
```

Set multiple styles:

```php
$report->setStyles([
    'header_background' => '#1f4e78',
    'header_color' => '#ffffff',
    'font_size' => 10,
]);
```

The exact style names supported visually depend on your renderer and template.

The Report object stores the style information.

---

# 16. Headers and Footers

Set a report header:

```php
$report->setHeader(
    'ABC Company'
);
```

Set a footer:

```php
$report->setFooter(
    'Generated by Mandakini MVC'
);
```

Both methods return the report object.

---

# 17. Logos

Report V3 supports a local logo path:

```php
$report->setLogoPath(
    __DIR__ . '/logo.png'
);
```

It also supports a URL:

```php
$report->setLogoUrl(
    'https://example.com/logo.png'
);
```

For local images, Report V3 provides:

```php
$report->assetToDataUri(
    __DIR__ . '/logo.png'
);
```

This converts a local image into a data URI.

This is particularly useful for PDF rendering where local asset access may be restricted.

---

# 18. Generated Metadata

Generated metadata can be shown or hidden.

Show:

```php
$report->showGeneratedMeta(
    true
);
```

Hide:

```php
$report->showGeneratedMeta(
    false
);
```

The report automatically records its creation date/time.

Retrieve it:

```php
$report->getDateGenerated();
```

---

# 19. Page Numbers

Enable:

```php
$report->showPageNumbers(
    true
);
```

Disable:

```php
$report->showPageNumbers(
    false
);
```

Whether page numbers actually appear depends on the renderer/template implementation.

---

# 20. Repeating Table Headers

For multi-page reports:

```php
$report->repeatTableHeader(
    true
);
```

Disable:

```php
$report->repeatTableHeader(
    false
);
```

The renderer/template must support this feature.

---

# 21. Striped Rows

Enable:

```php
$report->stripedRows(
    true
);
```

Disable:

```php
$report->stripedRows(
    false
);
```

The visual appearance is ultimately controlled by the renderer/template.

---

# 22. Page Size and Orientation

Set page size:

```php
$report->setPageSize(
    'A4'
);
```

Example:

```php
$report->setPageSize(
    'Letter'
);
```

The actual supported sizes depend on the PDF renderer.

---

## Portrait

```php
$report->setPageOrientation(
    'portrait'
);
```

---

## Landscape

```php
$report->setPageOrientation(
    'landscape'
);
```

Landscape is especially useful for reports containing many columns.

---

# 23. Report Templates

One of the major features of Report V3 is template support.

A report can select a template:

```php
$report->template(
    'modern'
);
```

Or:

```php
$report->setTemplate(
    'modern'
);
```

Both select the same template.

Example:

```php
$report = Report::make(
    'Sales Report',
    'Admin'
)
    ->template('modern');
```

This allows the report data and the report appearance to be separated.

For example:

```text
Sales Report
    |
    +---- Default template
    |
    +---- Modern template
    |
    +---- Financial template
    |
    +---- Invoice template
```

The same report data can therefore be displayed using different designs.

---

# 24. Template Locations

Report V3 searches for templates in the following locations.

## Application Templates

```text
views/reports/{template}.php
```

For example:

```text
views/
└── reports/
    ├── default.php
    ├── modern.php
    ├── invoice.php
    └── financial.php
```

Then:

```php
$report->template(
    'invoice'
);
```

selects:

```text
views/reports/invoice.php
```

---

## Framework Templates

Report V3 also searches:

```text
core/util/Report/Templates/{template}/report.php
```

For example:

```text
core/
└── util/
    └── Report/
        └── Templates/
            └── modern/
                └── report.php
```

---

# 25. Custom Template Path

You can bypass normal template searching and specify a template directly:

```php
$report->setTemplatePath(
    __DIR__ . '/reports/company.php'
);
```

When `templatePath` is set, it takes priority over the named template.

Retrieve it:

```php
$path = $report->getTemplatePath();
```

---

# 26. Template API

A template can obtain information from the Report object.

## Basic Information

```php
$report->getTitle();

$report->getGeneratedBy();

$report->getDateGenerated();

$report->getHeader();

$report->getFooter();
```

---

## Columns

```php
$columns = $report->getColumns();
```

---

## Rows

```php
$rows = $report->getProcessedRows();
```

---

## Summary

```php
$summary = $report->getSummary();
```

---

## Calculations

```php
$calculations =
    $report->getCalculations();
```

---

## Groups

```php
$groups =
    $report->getGroups();
```

---

## Styles

```php
$styles =
    $report->getStyles();
```

---

## Logo

```php
$logoPath =
    $report->getLogoPath();

$logoUrl =
    $report->getLogoUrl();
```

---

## Formatting

A template can format a value:

```php
$value =
    $report->formatValue(
        $value,
        $column
    );
```

---

## Row Values

```php
$value =
    $report->getRowValue(
        $row,
        $column['key']
    );
```

---

## Conditional Styles

```php
$styles =
    $report->getCellStyles(
        $row,
        $column
    );
```

---

# 27. Parameters

Parameters allow additional information to be attached to the report.

Set one parameter:

```php
$report->setParameter(
    'company_id',
    25
);
```

Set multiple parameters:

```php
$report->setParameters([
    'company_id' => 25,
    'branch' => 'Colombo',
    'period' => 'August 2026',
]);
```

Retrieve parameters:

```php
$parameters =
    $report->getParameters();
```

Parameters are also included by `toArray()`.

---

# 28. Summary Information

Add summary information:

```php
$report->addSummary(
    'Company',
    'ABC Company'
);
```

Another:

```php
$report->addSummary(
    'Period',
    'August 2026'
);
```

Retrieve:

```php
$summary =
    $report->getSummary();
```

Summary data is also available to renderers.

---

# 29. Metadata

Add metadata:

```php
$report->addMetadata(
    'department',
    'Sales'
);
```

Another:

```php
$report->addMetadata(
    'report_version',
    '3.0'
);
```

Retrieve:

```php
$metadata =
    $report->getMetadata();
```

Metadata is included in `toArray()`.

---

# 30. Raw and Processed Rows

Report V3 provides two important methods.

## Raw Rows

```php
$rows =
    $report->getRawRows();
```

These represent the original rows from:

- `addRow()`
- `addRows()`
- QueryBuilder

---

## Processed Rows

```php
$rows =
    $report->getProcessedRows();
```

Processed rows have report-level processing applied.

Conceptually:

```text
Raw Rows
   |
   v
Filters
   |
   v
Sorting
   |
   v
Processed Rows
```

---

# 31. Reading Row Values

Simple value:

```php
$value =
    $report->getRowValue(
        $row,
        'amount'
    );
```

Nested values are supported.

Example:

```php
$value =
    $report->getRowValue(
        $row,
        'customer.name'
    );
```

This works with both arrays and objects.

Example array:

```php
[
    'customer' => [
        'name' => 'Kamal'
    ]
]
```

Then:

```php
getRowValue(
    $row,
    'customer.name'
);
```

returns:

```text
Kamal
```

---

# 32. Statistics

Get report statistics:

```php
$stats =
    $report->statistics();
```

Equivalent method:

```php
$stats =
    $report->getStatistics();
```

Typical result:

```php
[
    'total_rows' => 100,
    'total_columns' => 5,
    'generated' => '2026-08-30 07:00:00',
    'title' => 'Sales Report',
    'calculations' => [...]
]
```

---

# 33. Convert Report to Array

Use:

```php
$data =
    $report->toArray();
```

The result contains:

```text
title
generated
generated_by
columns
rows
summary
metadata
calculations
parameters
template
```

This is useful for:

- APIs
- Debugging
- Custom renderers
- Logging
- Application processing

---

# 34. Output Formats

Report V3 supports five output methods.

## HTML

```php
$html =
    $report->html();
```

Aliases:

```php
$report->generateHTML();
```

and:

```php
$report->display();
```

---

## PDF

```php
$pdf =
    $report->pdf();
```

---

## CSV

```php
$csv =
    $report->csv();
```

---

## JSON

```php
$json =
    $report->json();
```

---

## Excel

```php
$excel =
    $report->excel();
```

---

# 35. Saving Reports

## Save HTML

```php
$report->exportHTML(
    __DIR__ . '/report.html'
);
```

---

## Save PDF

```php
$report->exportPDF(
    __DIR__ . '/report.pdf'
);
```

---

## Save CSV

```php
$report->exportCSV(
    __DIR__ . '/report.csv'
);
```

---

## Save JSON

```php
$report->exportJSON(
    __DIR__ . '/report.json'
);
```

---

## Save Excel

```php
$report->exportExcel(
    __DIR__ . '/report.xlsx'
);
```

All export methods return:

```text
true
```

on success and:

```text
false
```

if `file_put_contents()` fails.

---

# 36. Downloading Reports

The `download()` method sends the report directly to the browser.

Supported formats:

```text
pdf
html
csv
json
excel
xlsx
```

Example:

```php
$report->download(
    'pdf'
);
```

---

## Custom Filename

```php
$report->download(
    'pdf',
    'sales-report.pdf'
);
```

CSV:

```php
$report->download(
    'csv',
    'sales-report.csv'
);
```

Excel:

```php
$report->download(
    'xlsx',
    'sales-report.xlsx'
);
```

Important:

Do not send output before calling `download()`.

Avoid:

```php
echo 'debug';

$report->download('pdf');
```

Instead:

```php
$report->download('pdf');
```

The download method sends HTTP headers and terminates the request.

---

# 37. Clearing a Report

Clear report data:

```php
$report->clear();
```

This clears:

- Rows
- Query
- Summary
- Filters
- Calculations

It does not reset every property of the report.

For example, the title and columns remain.

---

# 38. Cloning a Report

Clone an existing report with a new title:

```php
$newReport =
    $report->cloneAs(
        'Annual Sales Report'
    );
```

The clone keeps the existing report configuration but receives:

- A new title
- A new generation timestamp

This is useful when several reports share the same configuration.

---

# 39. QueryBuilder vs Report Filtering

There are two places where filtering can happen.

## QueryBuilder Filtering

```php
$query->where(
    'amount',
    '>',
    1000
);
```

This filtering occurs at the database/query level.

---

## Report Filtering

```php
$report->addFilter(
    'amount',
    '>',
    1000
);
```

This occurs after the data has been loaded into the report.

---

## Recommended Usage

For large datasets, prefer database filtering:

```php
$query
    ->where(
        'amount',
        '>',
        1000
    );
```

This allows the database to return fewer rows.

For presentation-level filtering, use:

```php
$report->addFilter(
    'amount',
    '>',
    1000
);
```

The same principle applies to sorting.

Database:

```php
$query->orderBy(
    'amount',
    'DESC'
);
```

Report:

```php
$report->sortBy(
    'amount',
    'DESC'
);
```

For large datasets, database-level processing is generally preferable.

---

# 40. Recommended Architecture

A clean Mandakini application can organize reporting like this:

```text
Controller
    |
    v
QueryBuilder
    |
    v
Report
    |
    +---- Columns
    +---- Filters
    +---- Sorting
    +---- Grouping
    +---- Calculations
    +---- Formatting
    |
    v
Template / Renderer
    |
    v
Output
```

The responsibilities are:

### QueryBuilder

Responsible for:

- Database queries
- Database filtering
- Database sorting
- Joins
- Database-level operations

### Report

Responsible for:

- Report definition
- Report-level filtering
- Report-level sorting
- Grouping
- Calculations
- Formatting metadata
- Parameters
- Summary
- Metadata

### Template

Responsible for:

- Visual design
- Layout
- HTML structure
- Branding

### Renderer

Responsible for:

- HTML output
- PDF output
- CSV output
- JSON output
- Excel output

This separation makes the reporting system easier to maintain.

---

# 41. Complete Example

The following is a complete example using manually supplied data.

```php
<?php

require __DIR__ . '/../vendor/autoload.php';

use app\core\util\Report;

$report = Report::make(
    'Monthly Sales Report',
    'Admin'
)

    // Template
    ->template('modern')

    // Header and footer
    ->setHeader(
        'ABC Company'
    )

    ->setFooter(
        'Generated by Mandakini MVC'
    )

    // Page configuration
    ->setPageSize('A4')
    ->setPageOrientation('landscape')

    // Display options
    ->showGeneratedMeta(true)
    ->showPageNumbers(true)
    ->repeatTableHeader(true)
    ->stripedRows(true)

    // Customer column
    ->addColumn(
        'name',
        'Customer',
        [
            'align' => 'left'
        ]
    )

    // Amount column
    ->addColumn(
        'amount',
        'Amount',
        [
            'align' => 'right',
            'format' => 'currency',
            'currency_symbol' => 'Rs. ',
            'decimals' => 2,
        ]
    )

    // Date column
    ->addColumn(
        'date',
        'Date',
        [
            'format' => 'date',
            'date_format' => 'd/m/Y',
        ]
    )

    // Data
    ->addRows([
        [
            'name' => 'Kamal',
            'amount' => 12500,
            'date' => '2026-08-29',
        ],
        [
            'name' => 'Nimal',
            'amount' => 7500,
            'date' => '2026-08-28',
        ],
        [
            'name' => 'Sunil',
            'amount' => 15000,
            'date' => '2026-08-27',
        ],
    ])

    // Filtering
    ->addFilter(
        'amount',
        '>',
        5000
    )

    // Sorting
    ->sortBy(
        'amount',
        'DESC'
    )

    // Calculations
    ->addCalculation(
        'Total Sales',
        'amount',
        'SUM'
    )

    ->addCalculation(
        'Average Sales',
        'amount',
        'AVG'
    )

    // Summary
    ->addSummary(
        'Period',
        'August 2026'
    )

    ->addSummary(
        'Company',
        'ABC Company'
    )

    // Metadata
    ->addMetadata(
        'department',
        'Sales'
    )

    ->addMetadata(
        'report_version',
        '3.0'
    )

    // Conditional formatting
    ->addConditionalFormat(
        'amount',
        '>',
        10000,
        [
            'font-weight' => 'bold'
        ]
    );

// Save PDF
$report->exportPDF(
    __DIR__ . '/monthly-sales.pdf'
);
```

---

# 42. Troubleshooting

## Template Not Found

If you receive:

```text
Report template not found: modern
```

Check that:

```text
views/reports/modern.php
```

exists.

Or use:

```php
$report->setTemplatePath(
    __DIR__ . '/reports/modern.php'
);
```

---

## Blank Column Values

Make sure the column key matches the row key.

Correct:

```php
$report->addColumn(
    'amount',
    'Amount'
);
```

Data:

```php
[
    'amount' => 1000
]
```

Incorrect:

```php
$report->addColumn(
    'amount',
    'Amount'
);
```

Data:

```php
[
    'total' => 1000
]
```

The report is looking for:

```text
amount
```

but the row contains:

```text
total
```

---

## QueryBuilder Data Not Loading

Make sure the object passed to:

```php
$report->query(
    $query
);
```

is a:

```php
app\core\db\QueryBuilder
```

Report V3 checks:

```php
method_exists(
    $this->query,
    'getRaw'
)
```

If `getRaw()` exists, it is used.

Otherwise:

```php
$this->query->get()
```

is used.

---

## PDF Looks Different From HTML

HTML and PDF are rendered by different renderer implementations.

The PDF renderer may have different support for:

- CSS
- Fonts
- Images
- Page breaks
- Headers
- Footers
- Page numbers

Therefore, always test the actual output format you intend to use.

---

## Download Error: Headers Already Sent

If this happens:

```text
Cannot download report because HTTP headers were already sent.
```

check for output before:

```php
$report->download('pdf');
```

For example, remove:

```php
echo 'test';
```

or accidental output from included PHP files.

---

# 43. Quick Reference

## Create

```php
Report::make(
    'Report Title',
    'Admin'
);
```

---

## Columns

```php
->addColumn(...)
```

```php
->addColumns(...)
```

```php
->setColumnWidth(...)
```

---

## Rows

```php
->addRow(...)
```

```php
->addRows(...)
```

---

## Query

```php
->query($query)
```

---

## Filters

```php
->addFilter(...)
```

Operators:

```text
=
!=
<>
>
<
>=
<=
LIKE
IN
NOT IN
IS NULL
IS NOT NULL
```

---

## Sorting

```php
->sortBy(
    'amount',
    'DESC'
);
```

---

## Grouping

```php
->groupBy(
    'department'
);
```

```php
->getGroups();
```

---

## Calculations

```php
->addCalculation(
    'Total',
    'amount',
    'SUM'
);
```

Functions:

```text
SUM
AVG
MIN
MAX
COUNT
```

---

## Group Calculations

```php
->addGroupCalculation(
    'Department Total',
    'amount',
    'SUM'
);
```

---

## Formatting

```php
->addColumn(
    'amount',
    'Amount',
    [
        'format' => 'currency'
    ]
);
```

Supported formats:

```text
integer
number
decimal
currency
percent
percentage
date
datetime
boolean
```

---

## Conditional Formatting

```php
->addConditionalFormat(
    'amount',
    '>',
    10000,
    [
        'font-weight' => 'bold'
    ]
);
```

---

## Styles

```php
->setStyle(...)
```

```php
->setStyles(...)
```

---

## Header

```php
->setHeader(...)
```

---

## Footer

```php
->setFooter(...)
```

---

## Logo

```php
->setLogoPath(...)
```

```php
->setLogoUrl(...)
```

---

## Template

```php
->template('modern')
```

or:

```php
->setTemplate('modern')
```

---

## Custom Template Path

```php
->setTemplatePath(...)
```

---

## Parameters

```php
->setParameter(...)
```

```php
->setParameters(...)
```

---

## Summary

```php
->addSummary(...)
```

---

## Metadata

```php
->addMetadata(...)
```

---

## Page

```php
->setPageSize('A4')
```

```php
->setPageOrientation('landscape')
```

---

## Output

```php
$report->html();
```

```php
$report->pdf();
```

```php
$report->csv();
```

```php
$report->json();
```

```php
$report->excel();
```

---

## Export

```php
$report->exportHTML(...);
```

```php
$report->exportPDF(...);
```

```php
$report->exportCSV(...);
```

```php
$report->exportJSON(...);
```

```php
$report->exportExcel(...);
```

---

## Download

```php
$report->download('pdf');
```

```php
$report->download('csv');
```

```php
$report->download('xlsx');
```

---

## Data

```php
$report->getRawRows();
```

```php
$report->getProcessedRows();
```

```php
$report->getRowValue(...);
```

---

## Calculations

```php
$report->calculateAggregates();
```

---

## Statistics

```php
$report->statistics();
```

or:

```php
$report->getStatistics();
```

---

## Array

```php
$report->toArray();
```

---

## Clear

```php
$report->clear();
```

---

## Clone

```php
$report->cloneAs(
    'New Report'
);
```

---

# Final Example — Minimal Report

For a simple report, this is all you need:

```php
<?php

require __DIR__ . '/../vendor/autoload.php';

use app\core\util\Report;

$report = Report::make(
    'Demo Report',
    'Admin'
)
    ->addColumns([
        'name' => 'Name',
        'amount' => 'Amount',
        'date' => 'Date',
    ])
    ->addRows([
        [
            'name' => 'Kamal',
            'amount' => 1250.50,
            'date' => '2026-08-29',
        ],
        [
            'name' => 'Nimal',
            'amount' => 900.00,
            'date' => '2026-08-28',
        ],
    ])
    ->addCalculation(
        'Total',
        'amount',
        'SUM'
    )
    ->addCalculation(
        'Average',
        'amount',
        'AVG'
    )
    ->setHeader(
        'Demo Company'
    )
    ->setFooter(
        'Generated by Mandakini MVC'
    );

file_put_contents(
    __DIR__ . '/demo.pdf',
    $report->pdf()
);
```

---

# End of Report V3 User Manual

**Mandakini MVC Framework**

Report V3 separates:

```text
Data
    ↓
Report Logic
    ↓
Template
    ↓
Renderer
    ↓
Output
```

This allows the same report data to be presented using different templates and exported into different formats.