<?php
$tableId = $tableId ?? 'manageTable';
$selectAllId = $selectAllId ?? 'selectAll';
$warehouseFilterId = $warehouseFilterId ?? 'filterWarehouse';
$availabilityFilterId = $availabilityFilterId ?? 'filterAvailability';
$stockAgeFilterId = $stockAgeFilterId ?? 'filterStockAge';
$searchWrapId = $searchWrapId ?? 'filterSearchWrap';
$outOfStockMessageId = $outOfStockMessageId ?? 'outOfStockMsg';
$tableClass = $tableClass ?? 'table table-bordered table-hover table-striped nowrap';
$tableStyle = $tableStyle ?? 'width:100%;';
$showFilters = $showFilters ?? true;
$showOutOfStockMessage = $showOutOfStockMessage ?? true;
?>
<div class="box">
    <?php if ($showFilters): ?>
        <div class="box-header with-border">
            <div class="row dashboard-filter-row">
                <div class="col-sm-3 col-xs-6 dashboard-filter-cell">
                    <select id="<?php echo esc($warehouseFilterId); ?>" class="form-control input-sm">
                        <option value="">All Warehouses</option>
                        <?php if (!empty($warehouses)): foreach ($warehouses as $warehouse): ?>
                            <option value="<?php echo esc($warehouse['name']); ?>"><?php echo esc($warehouse['name']); ?></option>
                        <?php endforeach; endif; ?>
                    </select>
                </div>
                <div class="col-sm-3 col-xs-6 dashboard-filter-cell">
                    <select id="<?php echo esc($availabilityFilterId); ?>" class="form-control input-sm">
                        <option value="">All Status</option>
                        <option value="In Stock">In Stock</option>
                        <option value="Out of Stock">Out of Stock</option>
                    </select>
                </div>
                <div class="col-sm-3 col-xs-6 dashboard-filter-cell">
                    <select id="<?php echo esc($stockAgeFilterId); ?>" class="form-control input-sm">
                        <option value="">All Ages</option>
                        <option value="Fresh">Fresh</option>
                        <option value="Aged">Aged</option>
                    </select>
                </div>
                <div class="col-sm-3 col-xs-6 dashboard-filter-cell">
                    <div id="<?php echo esc($searchWrapId); ?>"></div>
                </div>
            </div>
        </div>
    <?php endif; ?>
    <div class="box-body dashboard-table-wrap">
        <?php if ($showOutOfStockMessage): ?>
            <div id="<?php echo esc($outOfStockMessageId); ?>" style="display:none;" class="alert alert-warning text-center">
                <strong>All products are out of stock!</strong>
            </div>
        <?php endif; ?>
        <table id="<?php echo esc($tableId); ?>" class="<?php echo esc($tableClass); ?>" style="<?php echo esc($tableStyle); ?>">
            <thead>
                <tr>
                    <?php if (in_array('deleteProduct', $user_permission)): ?>
                        <th class="dashboard-table-check-col"><input type="checkbox" id="<?php echo esc($selectAllId); ?>"></th>
                    <?php endif; ?>
                    <th class="dashboard-table-product-col">Product</th>
                    <th>IMEI</th>
                    <th>Price</th>
                    <th>Warehouse</th>
                    <th class="hide-mobile">Availability</th>
                    <th>Ages</th>
                    <?php if (in_array('updateProduct', $user_permission) || in_array('deleteProduct', $user_permission) || in_array('printProduct', $user_permission) || in_array('createOrder', $user_permission)): ?>
                        <th>Action</th>
                    <?php endif; ?>
                </tr>
            </thead>
        </table>
    </div>
</div>