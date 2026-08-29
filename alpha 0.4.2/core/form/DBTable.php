<?php

namespace app\core\form;

use app\core\db\DBModel;
use app\core\db\QueryBuilder;

class DBTable
{
    protected DBModel $_model;

    protected int $_page_id = 1;

    protected int $_record_no = 50;

    protected array $_select = [];

    protected string $_cssClass = 'custom-grid-table';

    protected bool $_loadCss = true;

    /**
     * WHERE conditions.
     *
     * Preferred:
     *
     * [
     *     ['marks', '>', 0],
     *     ['marks', '<', 100],
     * ]
     *
     * This allows the SAME column to appear multiple times.
     */
    protected array $_where = [];

    protected ?string $_orderby = null;

    protected string $_update_url = '';

    protected string $_delete_url = '';

    protected string $_view_url = '';

    protected string $_tableUrl = '';

    /**
     * Optional QueryBuilder customization callback.
     *
     * Example:
     *
     * ->query(function($query) {
     *     $query->where('status', 'active')
     *           ->orWhere('status', 'pending');
     * })
     */
    protected $queryCallback = null;


    /*
    |--------------------------------------------------------------------------
    | CONSTRUCTOR
    |--------------------------------------------------------------------------
    */

    public function __construct(
        DBModel $model,
        int $page_id = 1,
        int $record_no = 50,
        array $select = [],
        array $where = [],
        ?string $orderby = null
    ) {
        $this->_model = $model;

        $this->_page_id =
            max(1, $page_id);

        $this->_record_no =
            max(1, $record_no);

        $this->_select =
            $select;

        $this->_where =
            $where;

        $this->_orderby =
            $orderby;
    }

    public function cssClass(string $class): self
    {
        $this->_cssClass = $class;

        return $this;
    }

    public function loadCss(bool $load = true): self
    {
        $this->_loadCss = $load;

        return $this;
    }

    /*
    |--------------------------------------------------------------------------
    | QUERY
    |--------------------------------------------------------------------------
    */

    /**
     * Configure the QueryBuilder directly.
     *
     * This is the recommended way to create complex tables.
     *
     * Example:
     *
     * $table->query(function ($query) {
     *     $query
     *         ->whereGroup(function ($q) {
     *             $q->where('marks', '>', 0)
     *               ->where('marks', '<', 100);
     *         })
     *         ->where('status', 'active');
     * });
     */
    public function query(
        callable $callback
    ): self {
        $this->queryCallback =
            $callback;

        return $this;
    }


    /*
    |--------------------------------------------------------------------------
    | SELECT
    |--------------------------------------------------------------------------
    */

    public function select(
        array|string ...$columns
    ): self {
        if (
            count($columns) === 1 &&
            is_array($columns[0])
        ) {
            $columns = $columns[0];
        }

        $this->_select =
            $columns;

        return $this;
    }


    public function updateSelect(
        array $select
    ): self {
        return $this->select($select);
    }


    /*
    |--------------------------------------------------------------------------
    | WHERE
    |--------------------------------------------------------------------------
    */

    /**
     * Add a WHERE condition.
     *
     * Unlike the old associative array format, this allows:
     *
     * ->where('marks', '>', 0)
     * ->where('marks', '<', 100)
     */
    public function where(
        string $column,
        string $operator,
        mixed $value = null
    ): self {
        $this->_where[] = [
            $column,
            $operator,
            $value
        ];

        return $this;
    }


    /**
     * Add OR WHERE.
     *
     * OR conditions are stored separately because the QueryBuilder
     * itself is responsible for deciding how they are combined.
     */
    public function orWhere(
        string $column,
        string $operator,
        mixed $value = null
    ): self {
        $this->_where[] = [
            'OR',
            $column,
            $operator,
            $value
        ];

        return $this;
    }


    /**
     * Set legacy WHERE array.
     */
    public function updateWhere(
        array $where
    ): self {
        $this->_where =
            $where;

        return $this;
    }


    /*
    |--------------------------------------------------------------------------
    | ORDER
    |--------------------------------------------------------------------------
    */

    public function orderBy(
        string $column,
        string $direction = 'ASC'
    ): self {
        $this->_orderby =
            "{$column} {$direction}";

        return $this;
    }


    /*
    |--------------------------------------------------------------------------
    | PAGINATION
    |--------------------------------------------------------------------------
    */

    public function page(
        int $page
    ): self {
        $this->_page_id =
            max(1, $page);

        return $this;
    }


    public function perPage(
        int $records
    ): self {
        $this->_record_no =
            max(1, $records);

        return $this;
    }


    /*
    |--------------------------------------------------------------------------
    | ACTION URLS
    |--------------------------------------------------------------------------
    */

    public function updateUrl(
        string $updateUrl = '',
        string $deleteUrl = '',
        string $viewUrl = ''
    ): self {
        $this->_update_url =
            $updateUrl;

        $this->_delete_url =
            $deleteUrl;

        $this->_view_url =
            $viewUrl;

        return $this;
    }


    public function tableUrl(
        string $tableUrl
    ): self {
        $this->_tableUrl =
            $tableUrl;

        return $this;
    }


    /*
    |--------------------------------------------------------------------------
    | BUILD QUERY
    |--------------------------------------------------------------------------
    */

    /**
     * Build the QueryBuilder used by the table.
     */
    protected function buildQuery(): QueryBuilder
    {
        $query =
            $this->_model::query();


        /*
        |--------------------------------------------------------------------------
        | SELECT
        |--------------------------------------------------------------------------
        */

        if (!empty($this->_select)) {
            $query->select(
                $this->_select
            );
        }


        /*
        |--------------------------------------------------------------------------
        | WHERE
        |--------------------------------------------------------------------------
        */

        $this->applyWhere(
            $query
        );


        /*
        |--------------------------------------------------------------------------
        | ORDER BY
        |--------------------------------------------------------------------------
        */

        if (
            $this->_orderby !== null &&
            trim($this->_orderby) !== ''
        ) {
            $parts = preg_split(
                '/\s+/',
                trim($this->_orderby)
            );

            $column =
                $parts[0];

            $direction =
                strtoupper(
                    $parts[1] ?? 'ASC'
                );

            if (
                !in_array(
                    $direction,
                    ['ASC', 'DESC'],
                    true
                )
            ) {
                throw new \InvalidArgumentException(
                    'Invalid ORDER BY direction.'
                );
            }

            $query->orderBy(
                $column,
                $direction
            );
        }


        /*
        |--------------------------------------------------------------------------
        | CUSTOM QUERY
        |--------------------------------------------------------------------------
        */

        if (
            $this->queryCallback !== null
        ) {
            ($this->queryCallback)(
                $query
            );
        }


        return $query;
    }


    /*
    |--------------------------------------------------------------------------
    | APPLY WHERE
    |--------------------------------------------------------------------------
    */

    protected function applyWhere(
        QueryBuilder $query
    ): void {
        foreach ($this->_where as $condition) {

            /*
             * New format:
             *
             * [
             *     'marks',
             *     '>',
             *     0
             * ]
             */
            if (
                isset($condition[0]) &&
                is_string($condition[0]) &&
                count($condition) >= 3
            ) {
                $query->where(
                    $condition[0],
                    $condition[1],
                    $condition[2]
                );

                continue;
            }


            /*
             * OR format:
             *
             * [
             *     'OR',
             *     'status',
             *     '=',
             *     'pending'
             * ]
             */
            if (
                isset($condition[0]) &&
                strtoupper($condition[0]) === 'OR' &&
                count($condition) >= 4
            ) {
                $query->orWhere(
                    $condition[1],
                    $condition[2],
                    $condition[3]
                );

                continue;
            }


            /*
             * Legacy associative format:
             *
             * [
             *     'status' => ['=', 'active']
             * ]
             */
            if (
                is_array($condition) &&
                !isset($condition[0])
            ) {
                foreach (
                    $condition as $column => $value
                ) {
                    if (
                        is_array($value) &&
                        count($value) >= 2
                    ) {
                        $query->where(
                            $column,
                            $value[0],
                            $value[1]
                        );
                    } else {
                        $query->where(
                            $column,
                            '=',
                            $value
                        );
                    }
                }
            }
        }
    }


    /*
    |--------------------------------------------------------------------------
    | GET DATA
    |--------------------------------------------------------------------------
    */

    /**
     * Get the paginated table data.
     */
    protected function getPagination(): array
    {
        return $this
            ->buildQuery()
            ->paginate(
                $this->_page_id,
                $this->_record_no
            );
    }


    /*
    |--------------------------------------------------------------------------
    | RENDER HTML
    |--------------------------------------------------------------------------
    */

    public function renderHtml(): string
    {
        $pagination =
            $this->getPagination();

        $modelList =
            $pagination['data'];

        $totalCount =
            $pagination['total'];

        $totalPages =
            $pagination['last_page'];

        $currentPage =
            $pagination['current_page'];

        $attrs =
            $this->_model->attributes();


        /*
        |--------------------------------------------------------------------------
        | DISPLAY COLUMNS
        |--------------------------------------------------------------------------
        */

        $attrsToShow =
            empty($this->_select)
                ? $attrs
                : $this->_select;


        ob_start();
        ?>

        <?php if ($this->_loadCss): ?>

            <link rel="stylesheet" href="<?php echo htmlspecialchars(
                $this->assetUrl('css/dbtable.css')
            ) ?>">
        <?php endif; ?>

        <div class="grid-wrapper">

            <div class="table-responsive">

                <table class="table table-hover <?php echo htmlspecialchars($this->_cssClass) ?> align-middle">

                    <thead>
                        <tr>

                            <?php foreach (
                                $attrsToShow as $field
                            ): ?>

                                <?php
                                $label = $field;

                                if (
                                    method_exists(
                                        $this->_model,
                                        'labels'
                                    )
                                ) {
                                    $labels =
                                        $this->_model->labels();

                                    if (
                                        isset(
                                            $labels[$field]
                                        )
                                    ) {
                                        $label =
                                            $labels[$field];
                                    }
                                }
                                ?>

                                <th>
                                    <?= htmlspecialchars(
                                        $label
                                    ) ?>
                                </th>

                            <?php endforeach; ?>


                            <?php if (
                                $this->_update_url ||
                                $this->_delete_url ||
                                $this->_view_url
                            ): ?>

                                <th class="text-end pe-4">
                                    Actions
                                </th>

                            <?php endif; ?>

                        </tr>
                    </thead>


                    <tbody>

                        <?php foreach (
                            $modelList as $model
                        ): ?>

                            <?php
                            if (
                                method_exists(
                                    $model,
                                    'calculate'
                                )
                            ) {
                                $model->calculate();
                            }
                            ?>

                            <tr>

                                <?php foreach (
                                    $attrsToShow as $field
                                ): ?>

                                    <?php
                                    $value =
                                        $model->{$field}
                                        ?? '';
                                    ?>

                                    <td>
                                        <?= htmlspecialchars(
                                            (string) $value
                                        ) ?>
                                    </td>

                                <?php endforeach; ?>


                                <?php if (
                                    $this->_update_url ||
                                    $this->_delete_url ||
                                    $this->_view_url
                                ): ?>

                                    <td class="text-end text-nowrap pe-4">

                                        <div
                                            class="d-inline-flex gap-2"
                                            role="group"
                                            aria-label="Actions"
                                        >

                                            <?php
                                            $primaryKey =
                                                $this->_model::primaryKey();

                                            $id =
                                                $model->{$primaryKey}
                                                ?? null;
                                            ?>


                                            <?php if (
                                                $this->_view_url
                                            ): ?>

                                                <a
                                                    class="btn btn-sm btn-info text-white grid-action-btn shadow-sm"
                                                    href="<?php echo htmlspecialchars(
                                                        $this->actionUrl(
                                                            $this->_view_url,
                                                            $id
                                                        )
                                                    ) ?>"
                                                >
                                                    <i class="bi bi-eye-fill"></i>
                                                    View
                                                </a>

                                            <?php endif; ?>


                                            <?php if (
                                                $this->_update_url
                                            ): ?>

                                                <a
                                                    class="btn btn-sm btn-primary grid-action-btn shadow-sm"
                                                    href="<?php echo htmlspecialchars(
                                                        $this->actionUrl(
                                                            $this->_update_url,
                                                            $id
                                                        )
                                                    ) ?>"
                                                >
                                                    <i class="bi bi-pencil-square"></i>
                                                    Edit
                                                </a>

                                            <?php endif; ?>


                                            <?php if (
                                                $this->_delete_url
                                            ): ?>

                                                <a
                                                    class="btn btn-sm btn-danger grid-action-btn shadow-sm"
                                                    href="<?php echo htmlspecialchars(
                                                        $this->actionUrl(
                                                            $this->_delete_url,
                                                            $id
                                                        )
                                                    ) ?>"
                                                    onclick="return confirm('Are you sure you want to permanently delete this record?');"
                                                >
                                                    <i class="bi bi-trash3-fill"></i>
                                                    Delete
                                                </a>

                                            <?php endif; ?>

                                        </div>

                                    </td>

                                <?php endif; ?>

                            </tr>

                        <?php endforeach; ?>

                    </tbody>

                </table>

            </div>


            <?php if (
                $totalPages > 1
            ): ?>

                <?= $this->renderPagination(
                    $currentPage,
                    $totalPages
                ) ?>

            <?php endif; ?>

        </div>

        <?php

        return ob_get_clean();
    }


    /*
    |--------------------------------------------------------------------------
    | ACTION URL
    |--------------------------------------------------------------------------
    */

    protected function actionUrl(
        string $url,
        mixed $id
    ): string {
        return str_replace(
            '{id}',
            urlencode((string) $id),
            $url
        );
    }


    /*
    |--------------------------------------------------------------------------
    | PAGINATION HTML
    |--------------------------------------------------------------------------
    */

    protected function renderPagination(
        int $currentPage,
        int $totalPages
    ): string {
        ob_start();

        $makeUrl =
            function (int $page): string {

                if (
                    !$this->_tableUrl
                ) {
                    $base =
                        $_SERVER['REQUEST_URI']
                        ?? '?';

                    /*
                     * Remove an existing page parameter
                     * so we don't create:
                     *
                     * ?page=2&page=3
                     */
                    $base =
                        preg_replace(
                            '/([?&])page=\d+(&?)/',
                            '$1',
                            $base
                        );

                    $base =
                        rtrim(
                            $base,
                            '?&'
                        );

                    $separator =
                        str_contains(
                            $base,
                            '?'
                        )
                            ? '&'
                            : '?';

                    return
                        $base .
                        $separator .
                        'page=' .
                        $page;
                }


                if (
                    str_contains(
                        $this->_tableUrl,
                        '{page}'
                    )
                ) {
                    return str_replace(
                        '{page}',
                        (string) $page,
                        $this->_tableUrl
                    );
                }


                $separator =
                    str_contains(
                        $this->_tableUrl,
                        '?'
                    )
                        ? '&'
                        : '?';

                return
                    $this->_tableUrl .
                    $separator .
                    'page=' .
                    $page;
            };


        ?>

        <nav
            aria-label="Table pagination"
            class="mt-4 pt-2 border-top border-light"
        >

            <ul
                class="pagination pagination-sm justify-content-center justify-content-md-end mb-0 gap-1"
            >

                <?php
                $previous =
                    $currentPage - 1;
                ?>

                <li
                    class="page-item <?= $currentPage <= 1
                        ? 'disabled'
                        : '' ?>"
                >

                    <a
                        class="page-link rounded-pill px-3 fw-medium border-0 bg-light text-dark"
                        href="<?= $currentPage <= 1
                            ? '#'
                            : htmlspecialchars(
                                $makeUrl($previous)
                            ) ?>"
                    >
                        <i class="bi bi-chevron-left me-1"></i>
                        Prev
                    </a>

                </li>


                <?php if (
                    $currentPage > 3
                ): ?>

                    <li class="page-item">

                        <a
                            class="page-link rounded-circle border-0 text-dark"
                            href="<?= htmlspecialchars(
                                $makeUrl(1)
                            ) ?>"
                        >
                            1
                        </a>

                    </li>


                    <?php if (
                        $currentPage > 4
                    ): ?>

                        <li class="page-item disabled">
                            <span class="page-link border-0 bg-transparent">
                                &hellip;
                            </span>
                        </li>

                    <?php endif; ?>

                <?php endif; ?>


                <?php
                $start =
                    max(
                        1,
                        $currentPage - 2
                    );

                $end =
                    min(
                        $totalPages,
                        $currentPage + 2
                    );
                ?>


                <?php for (
                    $page = $start;
                    $page <= $end;
                    $page++
                ): ?>

                    <li
                        class="page-item <?= $page === $currentPage
                            ? 'active'
                            : '' ?>"
                    >

                        <a
                            class="page-link rounded-circle border-0 fw-bold px-3 mx-1 <?= $page === $currentPage
                                ? 'bg-primary text-white shadow-sm'
                                : 'bg-light text-dark' ?>"
                            href="<?= htmlspecialchars(
                                $makeUrl($page)
                            ) ?>"
                        >
                            <?= $page ?>
                        </a>

                    </li>

                <?php endfor; ?>


                <?php if (
                    $currentPage <
                    $totalPages - 2
                ): ?>

                    <?php if (
                        $currentPage <
                        $totalPages - 3
                    ): ?>

                        <li class="page-item disabled">

                            <span class="page-link border-0 bg-transparent">
                                &hellip;
                            </span>

                        </li>

                    <?php endif; ?>


                    <li class="page-item">

                        <a
                            class="page-link rounded-circle border-0 text-dark"
                            href="<?= htmlspecialchars(
                                $makeUrl(
                                    $totalPages
                                )
                            ) ?>"
                        >
                            <?= $totalPages ?>
                        </a>

                    </li>

                <?php endif; ?>


                <?php
                $next =
                    $currentPage + 1;
                ?>

                <li
                    class="page-item <?= $currentPage >= $totalPages
                        ? 'disabled'
                        : '' ?>"
                >

                    <a
                        class="page-link rounded-pill px-3 fw-medium border-0 bg-light text-dark"
                        href="<?= $currentPage >= $totalPages
                            ? '#'
                            : htmlspecialchars(
                                $makeUrl($next)
                            ) ?>"
                    >
                        Next
                        <i class="bi bi-chevron-right ms-1"></i>
                    </a>

                </li>

            </ul>

        </nav>

        <?php

        return ob_get_clean();
    }


    /*
    |--------------------------------------------------------------------------
    | STRING CONVERSION
    |--------------------------------------------------------------------------
    */

    public function __toString(): string
    {
        return $this->renderHtml();
    }
}

