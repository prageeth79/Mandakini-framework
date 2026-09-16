# Mandakini MVC Query Builder

## Overview

The Mandakini MVC Query Builder allows you to build SQL queries using a fluent, chainable PHP syntax.

Basic example:

```php
$students = Student::query()
    ->where('status', 'active')
    ->get();
```

Instead of writing SQL manually:

```sql
SELECT *
FROM students
WHERE status = 'active';
```

The Query Builder uses PDO parameter binding for values, making normal `where()` values safer than manually concatenating them into SQL.

---

# 1. SELECT

## Select all columns

If no `select()` is specified, the Query Builder uses:

```php
Student::query()->get();
```

Equivalent SQL:

```sql
SELECT * FROM students;
```

---

## Select specific columns

```php
$students = Student::query()
    ->select('id', 'name', 'marks')
    ->get();
```

Equivalent SQL:

```sql
SELECT id, name, marks
FROM students;
```

You can also pass an array:

```php
$students = Student::query()
    ->select([
        'id',
        'name',
        'marks'
    ])
    ->get();
```

---

# 2. WHERE

The basic `where()` syntax is:

```php
->where('column', 'operator', 'value')
```

Example:

```php
$students = Student::query()
    ->where('marks', '>', 50)
    ->get();
```

Equivalent SQL:

```sql
WHERE marks > 50
```

---

## WHERE shorthand

For equality, you can omit the operator:

```php
->where('status', 'active')
```

This is equivalent to:

```php
->where('status', '=', 'active')
```

Example:

```php
$students = Student::query()
    ->where('status', 'active')
    ->get();
```

---

# 3. Multiple WHERE Conditions

Multiple `where()` calls are combined using `AND`.

```php
$students = Student::query()
    ->where('status', 'active')
    ->where('marks', '>', 50)
    ->get();
```

Equivalent SQL:

```sql
WHERE status = 'active'
AND marks > 50
```

---

# 4. Using the Same Column Multiple Times

A column **can be used multiple times**.

For example, this is completely valid:

```php
$students = Student::query()
    ->where('marks', '>', 0)
    ->where('marks', '<', 100)
    ->get();
```

This produces a condition equivalent to:

```sql
WHERE marks > 0
AND marks < 100
```

The Query Builder creates separate parameter names for each value.

For example:

```text
marks > :marks_0
AND marks < :marks_1
```

With:

```text
:marks_0 = 0
:marks_1 = 100
```

Therefore, using the same field multiple times is not a problem.

---

# 5. OR WHERE

Use `orWhere()` when two conditions should be connected with `OR`.

```php
$students = Student::query()
    ->where('marks', '<', 20)
    ->orWhere('marks', '>', 80)
    ->get();
```

Equivalent SQL:

```sql
WHERE marks < 20
OR marks > 80
```

---

## OR WHERE shorthand

You can also write:

```php
->orWhere('status', 'active')
```

instead of:

```php
->orWhere('status', '=', 'active')
```

Example:

```php
$students = Student::query()
    ->where('status', 'active')
    ->orWhere('status', 'pending')
    ->get();
```

Equivalent SQL:

```sql
WHERE status = 'active'
OR status = 'pending'
```

---

# 6. Combining AND and OR

You can combine `where()` and `orWhere()`.

```php
$students = Student::query()
    ->where('status', 'active')
    ->where('marks', '>', 50)
    ->orWhere('grade', 'A')
    ->get();
```

Conceptually:

```sql
WHERE status = 'active'
AND marks > 50
OR grade = 'A'
```

When the logic becomes more complicated, use grouped conditions.

---

# 7. Grouped WHERE Conditions

Use `whereNested()` when you need parentheses around conditions.

For example, suppose you want:

```text
status = active
AND
(
    marks > 80
    OR
    grade = A
)
```

Use:

```php
$students = Student::query()
    ->where('status', 'active')
    ->whereNested(function ($query) {

        $query
            ->where('marks', '>', 80)
            ->orWhere('grade', 'A');

    })
    ->get();
```

Equivalent SQL:

```sql
WHERE status = 'active'
AND (
    marks > 80
    OR grade = 'A'
)
```

This is important because SQL operator precedence can produce different results if parentheses are not used.

---

# 8. OR Grouped Conditions

Use `orWhereNested()` when the entire group should be connected with `OR`.

Example:

```php
$students = Student::query()
    ->where('status', 'active')
    ->orWhereNested(function ($query) {

        $query
            ->where('marks', '>', 80)
            ->where('grade', 'A');

    })
    ->get();
```

Equivalent SQL:

```sql
WHERE status = 'active'
OR (
    marks > 80
    AND grade = 'A'
)
```

---

# 9. Practical Example: Marks Range

To find students whose marks are between 0 and 100:

```php
$students = Student::query()
    ->where('marks', '>', 0)
    ->where('marks', '<', 100)
    ->get();
```

Or use `whereBetween()`:

```php
$students = Student::query()
    ->whereBetween('marks', 0, 100)
    ->get();
```

---

# 10. WHERE BETWEEN

`whereBetween()` is useful when a value must be within a range.

```php
$students = Student::query()
    ->whereBetween('marks', 50, 80)
    ->get();
```

Equivalent SQL:

```sql
WHERE marks BETWEEN 50 AND 80
```

This is equivalent to:

```php
$students = Student::query()
    ->where('marks', '>=', 50)
    ->where('marks', '<=', 80)
    ->get();
```

---

# 11. OR WHERE BETWEEN

```php
$students = Student::query()
    ->where('status', 'active')
    ->orWhereBetween('marks', 80, 100)
    ->get();
```

Equivalent SQL:

```sql
WHERE status = 'active'
OR marks BETWEEN 80 AND 100
```

---

# 12. WHERE NOT BETWEEN

```php
$students = Student::query()
    ->whereNotBetween('marks', 0, 49)
    ->get();
```

Equivalent SQL:

```sql
WHERE marks NOT BETWEEN 0 AND 49
```

---

# 13. WHERE IN

Use `whereIn()` when a column can have one of several values.

```php
$students = Student::query()
    ->whereIn('class_id', [1, 2, 3])
    ->get();
```

Equivalent SQL:

```sql
WHERE class_id IN (1, 2, 3)
```

The values are bound individually.

---

# 14. OR WHERE IN

```php
$students = Student::query()
    ->where('status', 'active')
    ->orWhereIn('class_id', [1, 2, 3])
    ->get();
```

Equivalent SQL:

```sql
WHERE status = 'active'
OR class_id IN (1, 2, 3)
```

---

# 15. WHERE NOT IN

```php
$students = Student::query()
    ->whereNotIn('class_id', [4, 5])
    ->get();
```

Equivalent SQL:

```sql
WHERE class_id NOT IN (4, 5)
```

---

# 16. WHERE NULL

To find records where a column is `NULL`:

```php
$students = Student::query()
    ->whereNull('deleted_at')
    ->get();
```

Equivalent SQL:

```sql
WHERE deleted_at IS NULL
```

---

# 17. WHERE NOT NULL

Use:

```php
$students = Student::query()
    ->whereNull('deleted_at', true)
    ->get();
```

Equivalent SQL:

```sql
WHERE deleted_at IS NOT NULL
```

---

# 18. OR WHERE NULL

```php
$students = Student::query()
    ->where('status', 'active')
    ->orWhereNull('deleted_at')
    ->get();
```

Equivalent SQL:

```sql
WHERE status = 'active'
OR deleted_at IS NULL
```

---

# 19. Supported Operators

The Query Builder supports common SQL operators such as:

```text
=
!=
<>
>
<
>=
<=
LIKE
NOT LIKE
IN
NOT IN
IS
IS NOT
```

Examples:

```php
->where('marks', '>=', 50)
```

```php
->where('name', 'LIKE', '%Kamal%')
```

```php
->where('status', '!=', 'inactive')
```

---

# 20. LIKE

Find names containing `"mal"`:

```php
$students = Student::query()
    ->where('name', 'LIKE', '%mal%')
    ->get();
```

Equivalent SQL:

```sql
WHERE name LIKE '%mal%'
```

---

# 21. ORDER BY

Sort ascending:

```php
$students = Student::query()
    ->orderBy('name', 'ASC')
    ->get();
```

Sort descending:

```php
$students = Student::query()
    ->orderBy('marks', 'DESC')
    ->get();
```

The direction defaults to `ASC`:

```php
$students = Student::query()
    ->orderBy('name')
    ->get();
```

---

# 22. LIMIT

Limit the number of records:

```php
$students = Student::query()
    ->limit(10)
    ->get();
```

Equivalent SQL:

```sql
LIMIT 10
```

---

# 23. OFFSET

Skip records:

```php
$students = Student::query()
    ->limit(10)
    ->offset(20)
    ->get();
```

Equivalent SQL:

```sql
LIMIT 10
OFFSET 20
```

This is useful for pagination.

---

# 24. JOIN

Use `join()` to combine records from another table.

```php
$students = Student::query()
    ->join(
        'classes',
        'students.class_id',
        '=',
        'classes.id'
    )
    ->getRaw();
```

Equivalent SQL:

```sql
SELECT *
FROM students
INNER JOIN classes
    ON students.class_id = classes.id
```

---

# 25. LEFT JOIN

```php
$students = Student::query()
    ->leftJoin(
        'classes',
        'students.class_id',
        '=',
        'classes.id'
    )
    ->getRaw();
```

Equivalent SQL:

```sql
SELECT *
FROM students
LEFT JOIN classes
    ON students.class_id = classes.id
```

---

# 26. RIGHT JOIN

```php
$students = Student::query()
    ->rightJoin(
        'classes',
        'students.class_id',
        '=',
        'classes.id'
    )
    ->getRaw();
```

---

# 27. JOIN With SELECT

You can select columns from both tables.

```php
$students = Student::query()
    ->select([
        'students.id',
        'students.name',
        'classes.name AS class_name'
    ])
    ->leftJoin(
        'classes',
        'students.class_id',
        '=',
        'classes.id'
    )
    ->getRaw();
```

---

# 28. GROUP BY

Use `groupBy()` to group records.

```php
$result = Student::query()
    ->select([
        'class_id',
        'COUNT(*) AS total_students'
    ])
    ->groupBy('class_id')
    ->getRaw();
```

Equivalent SQL:

```sql
SELECT
    class_id,
    COUNT(*) AS total_students
FROM students
GROUP BY class_id
```

---

# 29. Multiple GROUP BY Columns

```php
$result = Student::query()
    ->groupBy(
        'class_id',
        'status'
    )
    ->getRaw();
```

Or:

```php
$result = Student::query()
    ->groupBy([
        'class_id',
        'status'
    ])
    ->getRaw();
```

---

# 30. HAVING

`having()` is normally used with `GROUP BY`.

Example:

```php
$result = Student::query()
    ->select([
        'class_id',
        'COUNT(*) AS total_students'
    ])
    ->groupBy('class_id')
    ->having('COUNT(*)', '>', 10)
    ->getRaw();
```

Conceptually:

```sql
GROUP BY class_id
HAVING COUNT(*) > 10
```

---

# 31. OR HAVING

```php
$result = Student::query()
    ->groupBy('class_id')
    ->having('COUNT(*)', '>', 10)
    ->orHaving('COUNT(*)', '=', 5)
    ->getRaw();
```

---

# 32. GET

`get()` executes the query and returns model objects.

```php
$students = Student::query()
    ->where('status', 'active')
    ->get();
```

You can use:

```php
foreach ($students as $student) {
    echo $student->name;
}
```

Use `get()` when you want instances of your model.

---

# 33. GET RAW

`getRaw()` returns associative arrays.

```php
$students = Student::query()
    ->where('status', 'active')
    ->getRaw();
```

Access values using:

```php
foreach ($students as $student) {
    echo $student['name'];
}
```

`getRaw()` is especially useful for:

* JOIN queries
* Aggregate queries
* GROUP BY
* Custom SELECT expressions
* Reports

---

# 34. FIRST

Get the first matching record:

```php
$student = Student::query()
    ->where('id', 10)
    ->first();
```

If a record exists:

```php
echo $student->name;
```

If no record exists:

```php
$student === null
```

---

# 35. VALUE

Get one column from the first matching record.

```php
$name = Student::query()
    ->where('id', 10)
    ->value('name');
```

Example:

```php
echo $name;
```

---

# 36. PLUCK

Get a single column from multiple records.

```php
$names = Student::query()
    ->where('status', 'active')
    ->pluck('name');
```

Result:

```php
[
    'Kamal',
    'Nimal',
    'Sunil',
    'Amal'
]
```

---

# 37. COUNT

Count all records:

```php
$total = Student::query()
    ->count();
```

Count records matching a condition:

```php
$total = Student::query()
    ->where('status', 'active')
    ->count();
```

Count a specific column:

```php
$total = Student::query()
    ->count('id');
```

---

# 38. SUM

Calculate the total of a column:

```php
$totalMarks = Student::query()
    ->sum('marks');
```

With conditions:

```php
$totalMarks = Student::query()
    ->where('class_id', 5)
    ->sum('marks');
```

---

# 39. AVG

Calculate the average:

```php
$average = Student::query()
    ->avg('marks');
```

---

# 40. MIN

Get the smallest value:

```php
$lowest = Student::query()
    ->min('marks');
```

---

# 41. MAX

Get the largest value:

```php
$highest = Student::query()
    ->max('marks');
```

---

# 42. EXISTS

Check whether at least one record exists.

```php
$exists = Student::query()
    ->where('email', $email)
    ->exists();
```

Returns:

```php
true
```

or:

```php
false
```

Example:

```php
if (Student::query()->where('id', $id)->exists()) {
    echo "Student exists";
}
```

---

# 43. INSERT

Insert a new record:

```php
Student::query()
    ->insert([
        'name' => 'Kamal',
        'marks' => 85,
        'status' => 'active'
    ]);
```

The values are passed to PDO as parameters.

---

# 44. UPDATE

Update records matching a condition:

```php
$count = Student::query()
    ->where('id', 10)
    ->update([
        'name' => 'Kamal',
        'marks' => 95
    ]);
```

The return value is the number of affected rows.

```php
echo $count;
```

## Safety

The Query Builder requires a `WHERE` condition for `update()`.

This should not be allowed:

```php
Student::query()->update([
    'status' => 'inactive'
]);
```

This protection prevents accidentally updating every record in the table.

---

# 45. DELETE

Delete matching records:

```php
$count = Student::query()
    ->where('id', 10)
    ->delete();
```

The return value is the number of deleted rows.

The Query Builder should also require a `WHERE` condition for `delete()`.

This prevents accidental:

```sql
DELETE FROM students;
```

---

# 46. Pagination

Use `paginate()` when displaying records page by page.

```php
$result = Student::query()
    ->orderBy('name')
    ->paginate(1, 20);
```

The arguments are:

```text
paginate(page, recordsPerPage)
```

Therefore:

```php
->paginate(2, 20)
```

means:

```text
Page: 2
Records per page: 20
```

The result contains information such as:

```php
[
    'data' => [...],
    'total' => 157,
    'per_page' => 20,
    'current_page' => 2,
    'last_page' => 8,
    'from' => 21,
    'to' => 40
]
```

---

# 47. Complex Query Example

Suppose we need:

```text
status = active

AND

marks between 50 and 100

AND

(class_id = 1 OR class_id = 2)

AND

deleted_at IS NULL
```

The Query Builder can express this as:

```php
$students = Student::query()

    ->where('status', 'active')

    ->whereBetween(
        'marks',
        50,
        100
    )

    ->whereNested(function ($query) {

        $query
            ->where('class_id', 1)
            ->orWhere('class_id', 2);

    })

    ->whereNull('deleted_at')

    ->orderBy('marks', 'DESC')

    ->get();
```

The generated SQL is conceptually:

```sql
SELECT *
FROM students
WHERE status = :status
AND marks BETWEEN :marks_min AND :marks_max
AND (
    class_id = :class_1
    OR class_id = :class_2
)
AND deleted_at IS NULL
ORDER BY marks DESC
```

---

# 48. Another Complex Query

Find students who are:

```text
active

AND

(
    marks >= 80
    OR grade = A
)

AND

class_id is 1, 2, or 3
```

Use:

```php
$students = Student::query()

    ->where('status', 'active')

    ->whereNested(function ($query) {

        $query
            ->where('marks', '>=', 80)
            ->orWhere('grade', 'A');

    })

    ->whereIn(
        'class_id',
        [1, 2, 3]
    )

    ->get();
```

---

# 49. Building Queries Before Executing

A query does not have to execute immediately.

You can build it first:

```php
$query = Student::query();

$query
    ->where('status', 'active')
    ->where('marks', '>', 50)
    ->orderBy('name');
```

Then execute it later:

```php
$students = $query->get();
```

This is useful when conditions are added dynamically.

---

# 50. Dynamic Conditions

For example:

```php
$query = Student::query();

if ($status !== null) {
    $query->where('status', $status);
}

if ($minMarks !== null) {
    $query->where('marks', '>=', $minMarks);
}

if ($maxMarks !== null) {
    $query->where('marks', '<=', $maxMarks);
}

$students = $query->get();
```

This allows controllers to build queries based on user-selected filters.

---

# 51. Dynamic Search Example

```php
$query = Student::query();

if (!empty($search)) {

    $query->whereNested(function ($q) use ($search) {

        $q->where(
            'name',
            'LIKE',
            "%{$search}%"
        );

        $q->orWhere(
            'email',
            'LIKE',
            "%{$search}%"
        );

    });
}

$students = $query->get();
```

The resulting logic is:

```sql
WHERE (
    name LIKE '%search%'
    OR email LIKE '%search%'
)
```

---

# 52. Query Debugging

You can inspect the generated SQL before executing it.

```php
$query = Student::query()
    ->where('marks', '>', 50)
    ->where('marks', '<', 100)
    ->orderBy('name');

echo $query->toSql();
```

You may see something similar to:

```sql
SELECT *
FROM students
WHERE marks > :marks_0
AND marks < :marks_1
ORDER BY name ASC
```

The actual values are stored separately as PDO bindings.

This is better than directly inserting values into the SQL string.

---

# 53. Parameter Binding

When you write:

```php
Student::query()
    ->where('name', $name)
    ->get();
```

The value of `$name` should be passed to PDO as a bound parameter.

Conceptually:

```sql
WHERE name = :name_0
```

with:

```text
:name_0 = $name
```

The value is not directly concatenated into the SQL statement.

---

# 54. Recommended Query Style

### Simple query

```php
$student = Student::query()
    ->where('id', $id)
    ->first();
```

### Multiple conditions

```php
$students = Student::query()
    ->where('status', 'active')
    ->where('marks', '>', 50)
    ->where('marks', '<', 100)
    ->get();
```

### OR conditions

```php
$students = Student::query()
    ->where('grade', 'A')
    ->orWhere('grade', 'B')
    ->get();
```

### Complex conditions

```php
$students = Student::query()
    ->where('status', 'active')
    ->whereNested(function ($q) {

        $q->where('marks', '>=', 80)
          ->orWhere('grade', 'A');

    })
    ->get();
```

### Reporting

```php
$report = Student::query()
    ->select([
        'class_id',
        'COUNT(*) AS students',
        'AVG(marks) AS average_marks',
        'MAX(marks) AS highest_marks',
        'MIN(marks) AS lowest_marks'
    ])
    ->where('status', 'active')
    ->groupBy('class_id')
    ->orderBy('class_id')
    ->getRaw();
```

---

# 55. Query Builder Cheat Sheet

| Method              | Purpose                   |
| ------------------- | ------------------------- |
| `select()`          | Select columns            |
| `addSelect()`       | Add columns               |
| `where()`           | AND condition             |
| `orWhere()`         | OR condition              |
| `whereNested()`     | Group conditions with AND |
| `orWhereNested()`   | Group conditions with OR  |
| `whereNull()`       | IS NULL                   |
| `orWhereNull()`     | OR IS NULL                |
| `whereIn()`         | IN                        |
| `orWhereIn()`       | OR IN                     |
| `whereNotIn()`      | NOT IN                    |
| `orWhereNotIn()`    | OR NOT IN                 |
| `whereBetween()`    | BETWEEN                   |
| `orWhereBetween()`  | OR BETWEEN                |
| `whereNotBetween()` | NOT BETWEEN               |
| `join()`            | INNER JOIN                |
| `leftJoin()`        | LEFT JOIN                 |
| `rightJoin()`       | RIGHT JOIN                |
| `groupBy()`         | GROUP BY                  |
| `having()`          | HAVING                    |
| `orHaving()`        | OR HAVING                 |
| `orderBy()`         | ORDER BY                  |
| `limit()`           | LIMIT                     |
| `offset()`          | OFFSET                    |
| `get()`             | Return model objects      |
| `getRaw()`          | Return arrays             |
| `first()`           | Return first result       |
| `value()`           | Return one value          |
| `pluck()`           | Return one column         |
| `count()`           | Count records             |
| `sum()`             | Calculate SUM             |
| `avg()`             | Calculate AVG             |
| `min()`             | Get minimum               |
| `max()`             | Get maximum               |
| `exists()`          | Check existence           |
| `insert()`          | Insert record             |
| `update()`          | Update records            |
| `delete()`          | Delete records            |
| `paginate()`        | Paginate results          |
| `toSql()`           | Inspect generated SQL     |

---

# 56. Important Concept

The most important rule to remember is:

```php
where()
```

uses `AND`.

```php
orWhere()
```

uses `OR`.

For example:

```php
$query
    ->where('marks', '>', 0)
    ->where('marks', '<', 100);
```

means:

```text
marks > 0 AND marks < 100
```

While:

```php
$query
    ->where('marks', '<', 20)
    ->orWhere('marks', '>', 80);
```

means:

```text
marks < 20 OR marks > 80
```

And for complicated logic:

```php
$query
    ->whereNested(function ($q) {

        $q->where(...)
          ->orWhere(...);

    });
```

should be used to create:

```text
AND (
    condition
    OR
    condition
)
```

This gives the Query Builder enough flexibility to build simple queries as well as complex real-world queries.
