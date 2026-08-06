<?php
$tableId = $tableId ?? 'manageTable';
$tableClass = $tableClass ?? 'table table-bordered table-hover table-striped';
$tableStyle = $tableStyle ?? '';
$summaryOnly = $summaryOnly ?? false;
?>
<table id="<?php echo esc($tableId); ?>" class="<?php echo esc($tableClass); ?>" style="<?php echo esc($tableStyle); ?>">
    <thead>
        <tr>
            <th>Warehouse</th>
            <?php if ($summaryOnly): ?>
                <th>Stock Available</th>
            <?php else: ?>
                <th>Assigned User</th>
                <th>Total Stock</th>
                <th>Total Value</th>
                <th>Status</th>
                <?php if (in_array('updateStore', $user_permission) || in_array('deleteStore', $user_permission)): ?>
                    <th>Action</th>
                <?php endif; ?>
            <?php endif; ?>
        </tr>
    </thead>
</table>