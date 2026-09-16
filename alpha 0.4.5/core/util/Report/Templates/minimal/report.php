<!doctype html>
<html>
<head>
<meta charset="UTF-8">
<title><?= $escape($title) ?></title>
<style>
@page { margin: 24px 25px 30px; }
body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 9px; color: #222; }
.title { font-size: 18px; font-weight: bold; margin-bottom: 4px; }
.meta { font-size: 8px; color: #666; margin-bottom: 12px; }
table { width: 100%; border-collapse: collapse; }
thead { display: table-header-group; }
th, td { padding: 5px 4px; border-bottom: 1px solid #ccc; }
th { text-align: left; border-bottom: 2px solid #222; }
.group-row td { font-weight: bold; border-bottom: 1px solid #222; padding-top: 10px; }
.group-total td { font-weight: bold; }
.summary { margin-top: 12px; }
.footer { margin-top: 15px; padding-top: 6px; border-top: 1px solid #ccc; font-size: 8px; }
</style>
</head>
<body>
<div class="title"><?= $escape($title) ?></div>
<?php if ($header): ?><div><?= nl2br($escape($header)) ?></div><?php endif; ?>
<?php if ($showGeneratedMeta): ?><div class="meta"><?= $escape($generated) ?> · <?= $escape($generatedBy) ?></div><?php endif; ?>
<table>
<thead><tr>
<?php foreach ($columns as $c): ?><th style="text-align:<?= $escape($c['align'] ?? 'left') ?>"><?= $escape($c['label']) ?></th><?php endforeach; ?>
</tr></thead>
<tbody>
<?php if ($hasGrouping): ?>
    <?php foreach ($groups as $g): ?>
        <tr class="group-row"><td colspan="<?= count($columns) ?>"><?= $escape($g['key']) ?></td></tr>
        <?php foreach ($g['rows'] as $r): ?>
            <tr>
            <?php foreach ($columns as $c): ?>
                <td style="text-align:<?= $escape($c['align'] ?? 'left') ?>"><?= $escape($format($value($r, $c['key']), $c)) ?></td>
            <?php endforeach; ?>
            </tr>
        <?php endforeach; ?>
        <?php if ($g['calculations']): ?><tr class="group-total"><td colspan="<?= max(1, count($columns)-1) ?>">Group totals</td><td><?= $escape(json_encode($g['calculations'], JSON_UNESCAPED_UNICODE)) ?></td></tr><?php endif; ?>
    <?php endforeach; ?>
<?php else: ?>
    <?php foreach ($rows as $r): ?>
        <tr>
        <?php foreach ($columns as $c): ?>
            <td style="text-align:<?= $escape($c['align'] ?? 'left') ?>"><?= $escape($format($value($r, $c['key']), $c)) ?></td>
        <?php endforeach; ?>
        </tr>
    <?php endforeach; ?>
<?php endif; ?>
</tbody>
</table>
<?php if ($summary || $calculations): ?>
<div class="summary">
<?php foreach ($summary as $k=>$v): ?><div><strong><?= $escape($k) ?>:</strong> <?= $escape($v) ?></div><?php endforeach; ?>
<?php foreach ($calculations as $k=>$v): ?><div><strong><?= $escape($k) ?>:</strong> <?= $escape($v) ?></div><?php endforeach; ?>
</div>
<?php endif; ?>
<?php if ($footer): ?><div class="footer"><?= nl2br($escape($footer)) ?></div><?php endif; ?>
</body>
</html>
