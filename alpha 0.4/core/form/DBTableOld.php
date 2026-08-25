<?php
namespace app\core\form;
use app\core\db\DBModel;

class DBTable{
    public DBModel $_model;         // model instance to load data from
    public int $_page_id;           // current page number (1-based)
    public int $_record_no;         // number of records per page
    public array $_select = [];     // which fields should be selected
    public array $_where = [];      // filter conditions
    public ?string $_orderby = null;
    public string $_update_url = '';
    public string $_delete_url = '';
    public string $_view_url = '';
    public string $_tableUrl = '';

    public function __construct(DBModel $model, int $page_id = 1, int $record_no = 50, array $select = [], array $where = [], string $orderby = null){
        $this->_model = $model;
        $this->_page_id = max(1, $page_id);
        $this->_record_no = max(1, $record_no);
        $this->_select = $select;
        $this->_where = $where;
        $this->_orderby = $orderby;
    }

    public function updateUrl(string $updateUrl = '', string $deleteUrl = '', string $viewUrl = ''){
        $this->_update_url = $updateUrl;
        $this->_delete_url = $deleteUrl;
        $this->_view_url = $viewUrl;
        return $this;
    }

    public function tableUrl(string $tableUrl){
        $this->_tableUrl = $tableUrl;
        return $this;
    }

    public function updateSelect(array $select){
        if(is_array($select)){
            $this->_select = $select;
        }
        return $this;
    }

    public function updateWhere(array $where){
        $this->_where = $where;
        return $this;
    }

    /**
     * Render the table as an HTML string using Bootstrap classes.
     * @return string
     */

    public function renderHtml(): string {
    // Compute total rows for pagination
    $tableName = $this->_model::tableName();
    $whereSql = '';
    $bindings = [];
    if (!empty($this->_where)) {
        $attributes = array_keys($this->_where);
        $whereSql = ' WHERE ' . implode(' AND ', array_map(fn($attr) => "$attr = :$attr", $attributes));
        $bindings = $this->_where;
    }

    $countSql = "SELECT COUNT(*) FROM $tableName" . $whereSql;
    $stmt = \app\core\Application::$app->db->pdo->prepare($countSql);
    foreach ($bindings as $k => $v) {
        $stmt->bindValue(":$k", $v);
    }
    $stmt->execute();
    $totalCount = (int)$stmt->fetchColumn();
    $totalPages = max(1, (int)ceil($totalCount / $this->_record_no));

    // Clamp current page to valid range
    $this->_page_id = max(1, min($this->_page_id, $totalPages));

    // Compute offset for DBModel::findAll (expects offset, row_count)
    $offset = ($this->_page_id - 1) * $this->_record_no;
    $limit = ['offset' => $offset, 'row_count' => $this->_record_no];

    $modelList = $this->_model::findAll($this->_where, $this->_orderby, $limit);
    $attrs = $this->_model->attributes();

    ob_start();
    ?>
    <style>
        /* Modern Data Grid Custom Styles */
        .grid-wrapper {
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
            border: 1px solid rgba(0, 0, 0, 0.05);
            padding: 15px;
            margin-bottom: 25px;
        }
        .custom-grid-table {
            border-collapse: separate;
            border-spacing: 0 8px; /* Gives a separate row feel */
            margin-bottom: 0 !important;
        }
        .custom-grid-table thead th {
            background-color: #0f2043 !important;
            color: #ffffff !important;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 0.5px;
            padding: 16px 20px !important;
            border: none !important;
        }
        .custom-grid-table thead th:first-child { border-radius: 10px 0 0 10px; }
        .custom-grid-table thead th:last-child { border-radius: 0 10px 10px 0; }
        
        .custom-grid-table tbody tr {
            background-color: #ffffff;
            transition: all 0.2s ease;
            box-shadow: 0 2px 5px rgba(0,0,0,0.02);
        }
        .custom-grid-table tbody tr:hover {
            background-color: #f8fafc !important;
            transform: scale(1.002);
            box-shadow: 0 4px 12px rgba(15, 32, 67, 0.06);
        }
        .custom-grid-table tbody td {
            padding: 16px 20px !important;
            vertical-align: middle;
            color: #475569;
            font-size: 14px;
            border-top: 1px solid #e2e8f0 !important;
            border-bottom: 1px solid #e2e8f0 !important;
            border-left: none !important;
            border-right: none !important;
        }
        .custom-grid-table tbody tr td:first-child {
            border-left: 1px solid #e2e8f0 !important;
            border-radius: 10px 0 0 10px;
            font-weight: 600;
            color: #1e293b;
        }
        .custom-grid-table tbody tr td:last-child {
            border-right: 1px solid #e2e8f0 !important;
            border-radius: 0 10px 10px 0;
        }
        .grid-action-btn {
            border-radius: 8px !important;
            padding: 6px 14px !important;
            font-weight: 600;
            font-size: 13px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
    </style>

    <div class="grid-wrapper">
        <div class="table-responsive">
            <table class="table table-hover custom-grid-table align-middle">
                <thead>
                    <tr>
                        <?php foreach($attrs as $field):
                            if(empty($this->_select) || in_array($field, $this->_select)):
                                $label = method_exists($this->_model, 'labels') && ($labels = $this->_model->labels()) && isset($labels[$field]) ? $labels[$field] : $field;
                        ?>
                            <th><?php echo htmlspecialchars($label); ?></th>
                        <?php
                            endif;
                        endforeach;
                        if($this->_update_url || $this->_delete_url || $this->_view_url): ?>
                            <th class="text-end pe-4">Actions</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($modelList as $model): ?>
                        <tr>
                            <?php foreach($attrs as $field):
                                if(empty($this->_select) || in_array($field, $this->_select)):
                                    $value = $model->{$field} ?? '';
                            ?>
                                <td><?php echo htmlspecialchars((string)$value); ?></td>
                            <?php endif; endforeach; ?>
                            
                            <?php if($this->_update_url || $this->_delete_url || $this->_view_url): ?>
                                <td class="text-end text-nowrap pe-4">
                                    <div class="d-inline-flex gap-2" role="group" aria-label="Actions">
                                        <?php if($this->_view_url): ?>
                                            <a class="btn btn-sm btn-info text-white grid-action-btn shadow-sm" href="<?php echo htmlspecialchars(str_replace('{id}', urlencode($model->{$this->_model::primaryKey()}), $this->_view_url)); ?>">
                                                <i class="bi bi-eye-fill"></i> View
                                            </a>
                                        <?php endif; ?>
                                        <?php if($this->_update_url): ?>
                                            <a class="btn btn-sm btn-primary grid-action-btn shadow-sm" href="<?php echo htmlspecialchars(str_replace('{id}', urlencode($model->{$this->_model::primaryKey()}), $this->_update_url)); ?>">
                                                <i class="bi bi-pencil-square"></i> Edit
                                            </a>
                                        <?php endif; ?>
                                        <?php if($this->_delete_url): ?>
                                            <a class="btn btn-sm btn-danger grid-action-btn shadow-sm" href="<?php echo htmlspecialchars(str_replace('{id}', urlencode($model->{$this->_model::primaryKey()}), $this->_delete_url)); ?>" onclick="return confirm('Are you sure you want to permanently delete this record?');">
                                                <i class="bi bi-trash3-fill"></i> Delete
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
        <!-- PREMIUM PAGINATION COMPONENT -->
        <?php if ($totalPages > 1): ?>
        <nav aria-label="Table pagination" class="mt-4 pt-2 border-top border-light">
            <ul class="pagination pagination-sm justify-content-center justify-content-md-end mb-0 gap-1">
                <?php
                $makeUrl = function($p) {
                    if (!$this->_tableUrl) {
                        $base = $_SERVER['REQUEST_URI'] ?? '?';
                        $sep = strpos($base, '?') === false ? '?' : '&';
                        return $base . $sep . 'page=' . $p;
                    }
                    if (strpos($this->_tableUrl, '{page}') !== false) {
                        return str_replace('{page}', $p, $this->_tableUrl);
                    }
                    $sep = strpos($this->_tableUrl, '?') === false ? '?' : '&';
                    return $this->_tableUrl . $sep . 'page=' . $p;
                };

                $prev = $this->_page_id - 1;
                ?>
                
                <!-- Previous Button -->
                <li class="page-item <?php echo $this->_page_id <= 1 ? 'disabled' : ''; ?>">
                    <a class="page-link rounded-pill px-3 fw-medium border-0 bg-light text-dark" href="<?php echo $this->_page_id <= 1 ? '#' : htmlspecialchars($makeUrl($prev)); ?>">
                        <i class="bi bi-chevron-left me-1"></i> Prev
                    </a>
                </li>

                <?php
                // Show first page
                if ($this->_page_id > 3) {
                    ?>
                    <li class="page-item"><a class="page-link rounded-circle border-0 text-dark" href="<?php echo htmlspecialchars($makeUrl(1)); ?>">1</a></li>
                    <?php if ($this->_page_id > 4): ?>
                        <li class="page-item disabled"><span class="page-link border-0 bg-transparent">&hellip;</span></li>
                    <?php endif; ?>
                <?php }

                // Window of pages around current
                $start = max(1, $this->_page_id - 2);
                $end = min($totalPages, $this->_page_id + 2);
                for ($p = $start; $p <= $end; $p++): ?>
                    <li class="page-item <?php echo $p == $this->_page_id ? 'active' : ''; ?>">
                        <a class="page-link rounded-circle border-0 fw-bold px-3 mx-1 <?php echo $p == $this->_page_id ? 'bg-primary text-white shadow-sm' : 'bg-light text-dark'; ?>" href="<?php echo htmlspecialchars($makeUrl($p)); ?>">
                            <?php echo $p; ?>
                        </a>
                    </li>
                <?php endfor; ?>

                <?php if ($this->_page_id < $totalPages - 2) {
                    if ($this->_page_id < $totalPages - 3) {
                        ?>
                        <li class="page-item disabled"><span class="page-link border-0 bg-transparent">&hellip;</span></li>
                        <?php
                    }
                    ?>
                    <li class="page-item"><a class="page-link rounded-circle border-0 text-dark" href="<?php echo htmlspecialchars($makeUrl($totalPages)); ?>"><?php echo $totalPages; ?></a></li>
                <?php } ?>

                <?php $next = $this->_page_id + 1; ?>
                
                <!-- Next Button -->
                <li class="page-item <?php echo $this->_page_id >= $totalPages ? 'disabled' : ''; ?>">
                    <a class="page-link rounded-pill px-3 fw-medium border-0 bg-light text-dark" href="<?php echo $this->_page_id >= $totalPages ? '#' : htmlspecialchars($makeUrl($next)); ?>">
                        Next <i class="bi bi-chevron-right ms-1"></i>
                    </a>
                </li>
            </ul>
        </nav>
        <?php endif; ?>
    </div>
    <?php
    return ob_get_clean();
}

    public function renderHtml_old(): string {
        // compute total rows for pagination
        $tableName = $this->_model::tableName();
        $whereSql = '';
        $bindings = [];
        if (!empty($this->_where)) {
            $attributes = array_keys($this->_where);
            $whereSql = ' WHERE ' . implode(' AND ', array_map(fn($attr) => "$attr = :$attr", $attributes));
            $bindings = $this->_where;
        }

        $countSql = "SELECT COUNT(*) FROM $tableName" . $whereSql;
        $stmt = \app\core\Application::$app->db->pdo->prepare($countSql);
        foreach ($bindings as $k => $v) {
            $stmt->bindValue(":$k", $v);
        }
        $stmt->execute();
        $totalCount = (int)$stmt->fetchColumn();
        $totalPages = max(1, (int)ceil($totalCount / $this->_record_no));

        // clamp current page to valid range
        $this->_page_id = max(1, min($this->_page_id, $totalPages));

        // compute offset for DBModel::findAll (expects offset, row_count)
        $offset = ($this->_page_id - 1) * $this->_record_no;
        $limit = ['offset' => $offset, 'row_count' => $this->_record_no];

        $modelList = $this->_model::findAll($this->_where, $this->_orderby, $limit);

        $attrs = $this->_model->attributes();

        ob_start();
        ?>
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead class="thead-dark">
                    <tr>
                        <?php foreach($attrs as $field):
                            if(empty($this->_select) || in_array($field, $this->_select)):
                                $label = method_exists($this->_model, 'labels') && ($labels = $this->_model->labels()) && isset($labels[$field]) ? $labels[$field] : $field;
                        ?>
                            <th><?php echo htmlspecialchars($label); ?></th>
                        <?php
                            endif;
                        endforeach;
                        // action column if any action URL provided
                        if($this->_update_url || $this->_delete_url || $this->_view_url): ?>
                            <th>Actions</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($modelList as $model): ?>
                        <tr>
                            <?php foreach($attrs as $field):
                                if(empty($this->_select) || in_array($field, $this->_select)):
                                    $value = $model->{$field} ?? '';
                            ?>
                                <td><?php echo htmlspecialchars((string)$value); ?></td>
                            <?php endif; endforeach; ?>
                            <?php if($this->_update_url || $this->_delete_url || $this->_view_url): ?>
                                <td class="text-nowrap">
                                    <div class="btn-group flex-nowrap" role="group" aria-label="Actions">
                                        <?php if($this->_view_url): ?>
                                            <a class="btn btn-sm btn-info text-white" href="<?php echo htmlspecialchars(str_replace('{id}', urlencode($model->{$this->_model::primaryKey()}), $this->_view_url)); ?>">View</a>
                                        <?php endif; ?>
                                        <?php if($this->_update_url): ?>
                                            <a class="btn btn-sm btn-primary" href="<?php echo htmlspecialchars(str_replace('{id}', urlencode($model->{$this->_model::primaryKey()}), $this->_update_url)); ?>">Edit</a>
                                        <?php endif; ?>
                                        <?php if($this->_delete_url): ?>
                                            <a class="btn btn-sm btn-danger" href="<?php echo htmlspecialchars(str_replace('{id}', urlencode($model->{$this->_model::primaryKey()}), $this->_delete_url)); ?>" onclick="return confirm('Delete this record?');">Delete</a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php if ($totalPages > 1): ?>
        <nav aria-label="Table pagination">
            <ul class="pagination">
                <?php
                $makeUrl = function($p) {
                    if (!$this->_tableUrl) {
                        // fallback: use current script and append page param
                        $base = $_SERVER['REQUEST_URI'] ?? '?';
                        $sep = strpos($base, '?') === false ? '?' : '&';
                        return $base . $sep . 'page=' . $p;
                    }
                    if (strpos($this->_tableUrl, '{page}') !== false) {
                        return str_replace('{page}', $p, $this->_tableUrl);
                    }
                    $sep = strpos($this->_tableUrl, '?') === false ? '?' : '&';
                    return $this->_tableUrl . $sep . 'page=' . $p;
                };

                // Previous
                $prev = $this->_page_id - 1;
                ?>
                <li class="page-item <?php echo $this->_page_id <= 1 ? 'disabled' : ''; ?>">
                    <a class="page-link" href="<?php echo $this->_page_id <= 1 ? '#' : htmlspecialchars($makeUrl($prev)); ?>">Previous</a>
                </li>

                <?php
                // show first page
                if ($this->_page_id > 3) {
                    ?>
                    <li class="page-item"><a class="page-link" href="<?php echo htmlspecialchars($makeUrl(1)); ?>">1</a></li>
                    <?php if ($this->_page_id > 4): ?>
                        <li class="page-item disabled"><span class="page-link">&hellip;</span></li>
                    <?php endif; ?>
                <?php }

                // window of pages around current
                $start = max(1, $this->_page_id - 2);
                $end = min($totalPages, $this->_page_id + 2);
                for ($p = $start; $p <= $end; $p++): ?>
                    <li class="page-item <?php echo $p == $this->_page_id ? 'active' : ''; ?>"><a class="page-link" href="<?php echo htmlspecialchars($makeUrl($p)); ?>"><?php echo $p; ?></a></li>
                <?php endfor; ?>

                <?php if ($this->_page_id < $totalPages - 2) {
                    if ($this->_page_id < $totalPages - 3) {
                        ?>
                        <li class="page-item disabled"><span class="page-link">&hellip;</span></li>
                        <?php
                    }
                    ?>
                    <li class="page-item"><a class="page-link" href="<?php echo htmlspecialchars($makeUrl($totalPages)); ?>"><?php echo $totalPages; ?></a></li>
                <?php } ?>

                <?php $next = $this->_page_id + 1; ?>
                <li class="page-item <?php echo $this->_page_id >= $totalPages ? 'disabled' : ''; ?>">
                    <a class="page-link" href="<?php echo $this->_page_id >= $totalPages ? '#' : htmlspecialchars($makeUrl($next)); ?>">Next</a>
                </li>
            </ul>
        </nav>
        <?php endif; ?>
        <?php
        return ob_get_clean();
    }



    public function __tostring(){
        return $this->renderHtml();
    }

}