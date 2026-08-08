<?php
/**
 * Debug view that displays a DBTable instance passed as `$dbtable`.
 */
?>
<div class="mb-3">
    <h2>Database Table: <?php echo htmlspecialchars($dbtable->_model::tableName()); ?></h2>
</div>

<?php
// $dbtable may be an object with renderHtml() or a string
if (is_object($dbtable) && method_exists($dbtable, 'renderHtml')) {
    echo $dbtable->renderHtml();
} else {
    echo (string)$dbtable;
}

?>
