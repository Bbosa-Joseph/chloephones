<?php
$tableId = $tableId ?? 'manageTable';
$selectAllId = $selectAllId ?? 'selectAll';
$tableClass = $tableClass ?? 'table table-hover modern-table';
$tableStyle = $tableStyle ?? '';
?>
<table id="<?php echo esc($tableId); ?>" class="<?php echo esc($tableClass); ?>" style="<?php echo esc($tableStyle); ?>">
    <thead>
        <tr>
            <?php if (in_array('deleteOrder', $user_permission)): ?>
                <th class="dashboard-table-check-col"><input type="checkbox" id="<?php echo esc($selectAllId); ?>"></th>
            <?php endif; ?>
            <th>Bill No.</th>
            <th>Client</th>
            <th>Contact</th>
            <th>Amount</th>
            <?php if (in_array('updateOrder', $user_permission) || in_array('viewOrder', $user_permission) || in_array('deleteOrder', $user_permission) || in_array('printOrder', $user_permission) || in_array('returnOrder', $user_permission)): ?>
                <th>Actions</th>
            <?php endif; ?>
        </tr>
    </thead>
</table>