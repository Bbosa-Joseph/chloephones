<?php
$session = session();
$db = db_connect();

$branchSalesTotalPhones = 0;
$branchSalesTotalValue = 0;
$topBranchName = 'No branch data yet';
$branchSalesCount = !empty($branch_phone_sales) ? count($branch_phone_sales) : 0;
$maxBranchPhones = 0;

if (!empty($branch_phone_sales)) {
    foreach ($branch_phone_sales as $index => $branchSale) {
        $branchPhones = (int) ($branchSale['phones_sold'] ?? 0);
        $branchSalesTotalPhones += $branchPhones;
        $branchSalesTotalValue += (float) ($branchSale['sales_value'] ?? 0);
        $maxBranchPhones = max($maxBranchPhones, $branchPhones);

        if ($index === 0 && !empty($branchSale['name'])) {
            $topBranchName = $branchSale['name'];
        }
    }
}

$canCreateOrder = !empty($user_permission) && in_array('createOrder', (array) $user_permission, true);
$paidOrders = (int) ($total_paid_orders ?? 0);
$unpaidOrders = (int) ($total_unpaid_orders ?? 0);
$totalProducts = (int) ($total_products ?? 0);
$totalStoresCount = (int) ($total_stores ?? 0);
$totalUsersCount = (int) ($total_users ?? 0);
$agedProductsCount = (int) ($aged_products ?? 0);
$outOfStockCount = (int) ($out_of_stock ?? 0);
$salesRangeText = (string) ($sales_range_label ?? 'This Month');
$recentSalesItems = is_array($recent_sales ?? null) ? $recent_sales : [];
$recentReceiptPreview = array_slice($recentSalesItems, 0, 3);
$todayLabel = date('l, d M Y');

$memberWarehouses = [];
$memberProducts = [];

if (!$is_admin) {
    $userId = (int) $session->get('user_id');
    $memberWarehouses = $db->query(
        'SELECT id, name FROM stores WHERE assigned_user_id = ? ORDER BY name ASC',
        [$userId]
    )->getResult();
    $memberProducts = $db->query(
        'SELECT p.id, p.name, p.imei, p.availability, s.name AS warehouse_name
         FROM products p
         INNER JOIN stores s ON s.id = p.warehouse_id
         WHERE s.assigned_user_id = ?
         ORDER BY p.name ASC',
        [$userId]
    )->getResult();
}

$memberWarehouseCount = count($memberWarehouses);
$memberProductCount = count($memberProducts);

// fetch all warehouses for the warehouses modal
$allWarehouses = $db->query('SELECT id, name FROM stores ORDER BY name ASC')->getResultArray();

if (!function_exists('dashboard_format_datetime')) {
    function dashboard_format_datetime($value): string
    {
        if (empty($value)) {
            return 'No date available';
        }

        $timestamp = is_numeric($value) ? (int) $value : strtotime((string) $value);
        if (!$timestamp) {
            return 'No date available';
        }

        return date('d M Y, h:i A', $timestamp);
    }
}
?>

<!-- Dashboard theme -->
<link rel="stylesheet" href="<?php echo base_url('assets/css/dashboard.css'); ?>">

<div class="content-wrapper">
    <section class="content-header dashboard-header">
        <h1>Dashboard</h1>
        <ol class="breadcrumb">
            <li>
                <a href="<?php echo base_url('dashboard'); ?>" class="dashboard-breadcrumb-link">
                    <i class="fa fa-dashboard"></i> Home
                </a>
            </li>
            <li class="active">Dashboard</li>
        </ol>
    </section>

    <div class="dashboard-shell">
        <?php if ($is_admin): ?>

                <!-- ===============================
                     PREMIUM HERO
                ================================ -->
                <div class="dashboard-hero">
                    <div class="hero-content">
                        <div>
                            <span class="dashboard-hero__eyebrow">Inventory Management Platform</span>
                            <h2 class="dashboard-hero__title">
                                Welcome back,
                                <?php echo esc($session->get('username')); ?> 👋
                            </h2>
                            <p class="dashboard-hero__text">
                                Monitor products, warehouses, sales and business performance
                                from one beautiful dashboard.
                            </p>
                        </div>
                        <div class="hero-actions">
                            <a href="<?php echo base_url('Controller_Orders/create'); ?>" class="btn btn-primary">
                                <i class="fa fa-plus"></i> New Receipt
                            </a>
                            <a href="<?php echo base_url('Controller_Products/create'); ?>" class="btn btn-default">
                                <i class="fa fa-cube"></i> Add Product
                            </a>
                        </div>
                    </div>
                </div>

                <!-- ===============================
                     KPI CARDS
                ================================ -->

                <!-- Compact widgets: click to open modals or actions -->
                <div class="dashboard-quick-actions">
                    <div class="dashboard-widget dashboard-quick-card glass-card glass-card--blue" id="widget-manage-stock">
                        <div>
                            <div class="title">Manage Stock</div>
                            <div class="value"><?php echo number_format($totalProducts); ?></div>
                        </div>
                        <div class="dashboard-quick-label">Open</div>
                    </div>

                    <div class="dashboard-widget dashboard-quick-card glass-card glass-card--teal" id="widget-receipts">
                        <div>
                            <div class="title">Receipts</div>
                            <div class="value"><?php echo number_format(count($recentSalesItems ?? [])); ?></div>
                            <div class="dashboard-widget-list">
                                <?php if (!empty($recentReceiptPreview)): ?>
                                    <?php foreach ($recentReceiptPreview as $sale): ?>
                                        <a
                                            href="<?php echo base_url('Controller_Orders/printDiv/' . (int) ($sale['id'] ?? 0)); ?>?embed=1&auto=1"
                                            class="dashboard-widget-list__item dashboard-widget-list__link"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                        >
                                            <span class="dashboard-widget-list__meta">#<?php echo esc($sale['bill_no'] ?? 'N/A'); ?></span>
                                            <strong>UGX <?php echo number_format((float) ($sale['net_amount'] ?? 0)); ?></strong>
                                        </a>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="dashboard-widget-list__item dashboard-widget-list__item--empty">
                                        <span class="dashboard-widget-list__meta">No recent receipts</span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="dashboard-quick-label">Latest 3</div>
                    </div>

                    <div class="dashboard-widget dashboard-quick-card glass-card glass-card--indigo" id="widget-warehouses">
                        <div>
                            <div class="title">Warehouses</div>
                            <div class="value"><?php echo number_format($totalStoresCount); ?></div>
                        </div>
                        <div class="dashboard-quick-label">Open</div>
                    </div>
                </div>

                <!-- Featured inline tables (hidden until a widget is clicked) -->
                <div id="featuredArea" class="glass-card" style="display:none;">
                    <div id="featured-products" class="featured-area" style="display:none;">

                        <div class="dashboard-featured-header">
                            <h4 class="dashboard-section-title">Manage Stock</h4>
                            <div>
                                <button id="closeFeatured" class="btn btn-danger btn-sm">Close</button>
                            </div>
                        </div>

                        <?= view('partials/tables/products-table', [
                            'tableId' => 'manageProductsFeatured',
                            'selectAllId' => 'selectAllFeaturedProducts',
                            'warehouseFilterId' => 'filterWarehouseFeatured',
                            'availabilityFilterId' => 'filterAvailabilityFeatured',
                            'stockAgeFilterId' => 'filterStockAgeFeatured',
                            'searchWrapId' => 'filterSearchWrapFeatured',
                            'outOfStockMessageId' => 'outOfStockMsgFeatured',
                            'tableClass' => 'table table-bordered table-hover table-striped nowrap dashboard-table-full',
                            'tableStyle' => '',
                            'warehouses' => $allWarehouses,
                            'user_permission' => $user_permission,
                        ]) ?>

                    </div>

                    <div id="featured-aged" class="featured-area" style="display:none;">
                        <div class="dashboard-featured-header">
                            <h4 class="dashboard-section-title">Aged Stock</h4>
                            <div>
                                <button id="closeFeaturedAged" class="btn btn-danger btn-sm">Close</button>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table id="featuredAgedTable" class="table table-bordered table-striped dashboard-table-full">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>IMEI</th>
                                        <th>Warehouse</th>
                                        <th>Days In Stock</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>

                    <div id="featured-warehouses" class="featured-area" style="display:none;">
                        <div class="dashboard-featured-header">
                            <h4 class="dashboard-section-title">Warehouses</h4>
                            <div>
                                <button id="closeFeaturedWarehouses" class="btn btn-danger btn-sm">Close</button>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <?= view('partials/tables/warehouses-table', [
                                'tableId' => 'featuredWarehousesTable',
                                'tableClass' => 'table table-bordered table-striped dashboard-table-full',
                                'summaryOnly' => true,
                                'user_permission' => $user_permission,
                            ]) ?>
                        </div>
                    </div>
                    <div id="featured-receipts" class="featured-area" style="display:none;">
                        <div class="dashboard-featured-header">
                            <h4 class="dashboard-section-title">Receipts</h4>
                            <div>
                                <button id="closeFeaturedReceipts" class="btn btn-danger btn-sm">Close</button>
                            </div>
                        </div>
                        <div class="box">
                            <div class="box-body">
                                <div class="table-responsive">
                                    <?= view('partials/tables/orders-table', [
                                        'tableId' => 'manageTableFeatured',
                                        'selectAllId' => 'selectAllFeatured',
                                        'user_permission' => $user_permission,
                                    ]) ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

        <?php else: ?>

                <div class="dashboard-hero">
                    <div class="hero-content">
                        <div>
                            <span class="dashboard-hero__eyebrow">Assigned Inventory</span>
                            <h2 class="dashboard-hero__title">
                                Welcome back,
                                <?php echo esc($session->get('username')); ?>
                            </h2>
                            <p class="dashboard-hero__text">
                                Review products linked to your warehouses, search inventory quickly, and open receipt creation from one place.
                            </p>
                        </div>
                        <div class="hero-actions">
                            <?php if ($canCreateOrder): ?>
                                <a href="<?php echo base_url('Controller_Orders/create'); ?>" class="btn btn-primary">
                                    <i class="fa fa-plus"></i> New Receipt
                                </a>
                            <?php endif; ?>
                            <button type="button" id="memberProductRefresh" class="btn btn-default">
                                <i class="fa fa-refresh"></i> Refresh
                            </button>
                        </div>
                    </div>
                </div>

                <div class="dashboard-member-stats">
                    <button type="button" class="dashboard-widget dashboard-member-card glass-card glass-card--teal" id="memberWarehousesCard" data-toggle="modal" data-target="#memberWarehousesModal">
                        <div>
                            <div class="title">Assigned Warehouses</div>
                            <div class="value"><?php echo number_format($memberWarehouseCount); ?></div>
                            <small>Stores under your responsibility</small>
                        </div>
                        <span class="dashboard-quick-label">View</span>
                    </button>

                    <a class="dashboard-widget dashboard-member-card glass-card glass-card--blue" id="memberProductsCard" href="#memberProductsPanel">
                        <div>
                            <div class="title">Assigned Products</div>
                            <div class="value" id="memberProductCount"><?php echo number_format($memberProductCount); ?></div>
                            <small>Products available for receipt creation</small>
                        </div>
                        <span class="dashboard-quick-label">View</span>
                    </a>

                    <button type="button" class="dashboard-widget dashboard-member-card glass-card glass-card--indigo" id="memberWarehouseListCard" data-toggle="modal" data-target="#memberWarehousesModal">
                        <div>
                            <div class="title">Warehouse List</div>
                            <small>Current assigned locations</small>
                            <ul class="dashboard-member-warehouse-list" id="memberWarehouseList">
                                <?php if (!empty($memberWarehouses)): ?>
                                    <?php foreach ($memberWarehouses as $warehouse): ?>
                                        <li><?php echo esc($warehouse->name ?? 'Unnamed Warehouse'); ?></li>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <li>No warehouses assigned.</li>
                                <?php endif; ?>
                            </ul>
                        </div>
                        <span class="dashboard-quick-label">View</span>
                    </button>
                </div>

                <div class="glass-card dashboard-member-panel" id="memberProductsPanel">
                    <div class="dashboard-panel-header">
                        <div>
                            <h4 class="dashboard-section-title">Assigned Products</h4>
                            <p class="dashboard-section-copy">Search by product name or IMEI to locate stock quickly.</p>
                        </div>
                        <div class="dashboard-panel-controls">
                            <input type="text" id="memberProductSearch" class="form-control dashboard-search-input" placeholder="Search name or IMEI">
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover dashboard-table-full">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>IMEI</th>
                                    <th>Warehouse</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="memberProductBody">
                                <?php if (!empty($memberProducts)): ?>
                                    <?php foreach ($memberProducts as $product): ?>
                                        <?php
                                        $isAvailable = (int) ($product->availability ?? 0) === 1;
                                        $receiptUrl = base_url('Controller_Orders/create?product_id=' . (int) ($product->id ?? 0) . '&imei=' . urlencode((string) ($product->imei ?? '')));
                                        ?>
                                        <tr data-name="<?php echo esc($product->name ?? ''); ?>" data-imei="<?php echo esc($product->imei ?? ''); ?>">
                                            <td><?php echo esc($product->name ?? 'N/A'); ?></td>
                                            <td><?php echo esc($product->imei ?? 'N/A'); ?></td>
                                            <td><?php echo esc($product->warehouse_name ?? $product->warehouse ?? 'N/A'); ?></td>
                                            <td><?php echo $isAvailable ? 'Available' : 'Sold'; ?></td>
                                            <td>
                                                <?php if ($canCreateOrder): ?>
                                                    <a href="<?php echo $receiptUrl; ?>" class="btn btn-primary btn-xs" title="Print Receipt">
                                                        <i class="fa fa-print"></i>
                                                    </a>
                                                <?php else: ?>
                                                    <button type="button" class="btn btn-default btn-xs" title="No permission" disabled>
                                                        <i class="fa fa-print"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr class="dashboard-empty-row empty-row">
                                        <td colspan="5">No products assigned to you.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

        <?php endif; ?>
    </div>

    <?php if (!$is_admin): ?>
        <div class="modal fade" id="memberWarehousesModal" tabindex="-1" role="dialog" aria-labelledby="memberWarehousesModalLabel">
            <div class="modal-dialog modal-sm" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title" id="memberWarehousesModalLabel">Assigned Warehouses</h4>
                    </div>
                    <div class="modal-body">
                        <ul class="dashboard-member-modal-list">
                            <?php if (!empty($memberWarehouses)): ?>
                                <?php foreach ($memberWarehouses as $warehouse): ?>
                                    <li><?php echo esc($warehouse->name ?? 'Unnamed Warehouse'); ?></li>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <li>No warehouses assigned.</li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

                <!-- Receipts quick-modal -->
                <div class="modal fade" id="receiptsModal" tabindex="-1" role="dialog" aria-labelledby="receiptsModalLabel">
                    <div class="modal-dialog modal-md" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                                <h4 class="modal-title" id="receiptsModalLabel">Receipts</h4>
                            </div>
                            <div class="modal-body">
                                <div class="dashboard-modal-actions">
                                    <a href="<?php echo base_url('Controller_Orders/create'); ?>" class="btn btn-primary btn-sm" target="_blank"><i class="fa fa-plus"></i> Make Receipt</a>
                                    <a href="<?php echo base_url('Controller_Orders'); ?>" class="btn btn-default btn-sm"><i class="fa fa-list"></i> Manage Receipts</a>
                                </div>
                                <?php if (!empty($recentSalesItems)): ?>
                                    <table class="table table-condensed">
                                        <?php foreach ($recentSalesItems as $sale): ?>
                                            <tr>
                                                <td>Receipt #<?php echo esc($sale['bill_no'] ?? 'N/A'); ?></td>
                                                <td class="text-right">UGX <?php echo number_format($sale['net_amount'] ?? 0); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </table>
                                <?php else: ?>
                                    <div class="dashboard-empty-state"><p>No recent receipts.</p></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal fade receipt-modal" id="receiptModal" tabindex="-1" role="dialog" aria-labelledby="receiptModalLabel">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                <h4 class="modal-title" id="receiptModalLabel">Receipt</h4>
                            </div>
                            <div class="modal-body">
                                <iframe id="receiptFrame" title="Receipt"></iframe>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-default" id="receiptShareBtn" title="Share" aria-label="Share">
                                    <i class="fa fa-share-alt" aria-hidden="true"></i>
                                </button>
                                <button type="button" class="btn btn-success" id="receiptSaveBtn" title="Save image" aria-label="Save image">
                                    <i class="fa fa-save" aria-hidden="true"></i>
                                </button>
                                <button type="button" class="btn btn-primary" id="receiptPrintBtn" title="Print" aria-label="Print">
                                    <i class="fa fa-print" aria-hidden="true"></i>
                                </button>
                                <button type="button" class="btn btn-danger" data-dismiss="modal" title="Close" aria-label="Close">
                                    <i class="fa fa-times" aria-hidden="true"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Keep modals for products/aged/warehouses available -->
                <!-- Products Modal -->
                <div class="modal fade" id="productsModal" tabindex="-1" role="dialog" aria-labelledby="productsModalLabel">
                    <div class="modal-dialog modal-lg" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                                <h4 class="modal-title" id="productsModalLabel">Products / Stock</h4>
                            </div>
                            <div class="modal-body">
                                <div class="dashboard-modal-actions">
                                    <?php if(in_array('createProduct', $user_permission)): ?>
                                        <a href="<?php echo base_url('Controller_Products/create'); ?>" class="btn btn-primary btn-sm" id="addProductBtn"><i class="fa fa-plus"></i> Add Product</a>
                                    <?php endif; ?>
                                </div>
                                <table id="productsTable" class="table table-bordered table-striped dashboard-table-full">
                                    <thead>
                                        <tr>
                                            <th>Product</th>
                                            <th>IMEI</th>
                                            <th>Warehouse</th>
                                            <th>Days In Stock</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Aged Stock Modal -->
                <div class="modal fade" id="agedModal" tabindex="-1" role="dialog" aria-labelledby="agedModalLabel">
                    <div class="modal-dialog modal-lg" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                                <h4 class="modal-title" id="agedModalLabel">Aged Stock (>= 15 days)</h4>
                            </div>
                            <div class="modal-body">
                                <table id="agedTable" class="table table-bordered table-striped dashboard-table-full">
                                    <thead>
                                        <tr>
                                            <th>Product</th>
                                            <th>IMEI</th>
                                            <th>Warehouse</th>
                                            <th>Days In Stock</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Warehouses Modal -->
                <div class="modal fade" id="warehousesModal" tabindex="-1" role="dialog" aria-labelledby="warehousesModalLabel">
                    <div class="modal-dialog modal-lg" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                                <h4 class="modal-title" id="warehousesModalLabel">Warehouses</h4>
                            </div>
                            <div class="modal-body">
                                <div class="dashboard-modal-actions">
                                    <select id="warehouseSelect" class="form-control input-sm dashboard-inline-select dashboard-inline-select--narrow">
                                        <option value="">All Warehouses</option>
                                        <?php foreach ($allWarehouses as $wh): ?>
                                            <option value="<?php echo esc($wh['name']); ?>"><?php echo esc($wh['name']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <table id="warehousesTable" class="table table-bordered table-striped dashboard-table-full">
                                    <thead>
                                        <tr>
                                            <th>Product</th>
                                            <th>IMEI</th>
                                            <th>Warehouse</th>
                                            <th>Availability</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
</div>
<!-- DataTables CSS/JS for dashboard modals -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<script>
$(document).ready(function(){
  $('#dashboardMainMenu').addClass('active');

  <?php if (!$is_admin): ?>
  var refreshUrl = '<?php echo base_url('Dashboard/memberProducts'); ?>';

    function escapeMemberTableValue(value) {
        return $('<div>').text(value || '').html();
    }

    function focusMemberProducts() {
        var $panel = $('.dashboard-member-panel');
        $('html,body').animate({scrollTop: $panel.offset().top - 80}, 300);
        $('#memberProductSearch').focus();
    }

  function renderMemberProducts(data) {
    $('#memberProductCount').text(data.count);
    var $body = $('#memberProductBody');
    $body.empty();

    if (data.products.length > 0) {
      $.each(data.products, function(i, p) {
        var action = '';

        if (<?php echo $canCreateOrder ? 'true' : 'false'; ?>) {
          var href = '<?php echo base_url('Controller_Orders/create?product_id='); ?>' + (p.id || 0) + '&imei=' + encodeURIComponent(p.imei || '');
          action = '<a href="' + href + '" class="btn btn-primary btn-xs" title="Print Receipt"><i class="fa fa-print"></i></a>';
        } else {
          action = '<button type="button" class="btn btn-default btn-xs" title="No permission" disabled><i class="fa fa-print"></i></button>';
        }

                var name = escapeMemberTableValue(p.name || 'N/A');
                var imei = escapeMemberTableValue(p.imei || 'N/A');
                var warehouse = escapeMemberTableValue(p.warehouse || 'N/A');
                var status = escapeMemberTableValue(p.status || 'N/A');
                $body.append('<tr data-name="' + name + '" data-imei="' + imei + '"><td>' + name + '</td><td>' + imei + '</td><td>' + warehouse + '</td><td>' + status + '</td><td>' + action + '</td></tr>');
      });
    } else {
            $body.append('<tr class="dashboard-empty-row empty-row"><td colspan="5">No products assigned to you.</td></tr>');
    }
  }

  function refreshMemberTable() {
    $.ajax({
      url: refreshUrl,
      type: 'GET',
      dataType: 'json',
      success: renderMemberProducts
    });
  }

  $('#memberProductRefresh').on('click', function(){
    refreshMemberTable();
  });

    $('#memberProductsCard').on('click', focusMemberProducts);

  $('#memberProductSearch').on('input', function(){
    var term = $(this).val().toLowerCase();
    $('#memberProductBody tr').each(function(){
      var name = ($(this).data('name') || '').toString().toLowerCase();
      var imei = ($(this).data('imei') || '').toString().toLowerCase();
      var visible = name.indexOf(term) !== -1 || imei.indexOf(term) !== -1;

      if ($(this).hasClass('empty-row')) {
        visible = term === '';
      }

      $(this).toggle(visible);
    });
  });
  <?php endif; ?>

    // Dashboard modals: initialize DataTables when modals are shown
    var productsTable, agedTable, warehousesTable, manageTableFeatured;
    var base_url = '<?php echo base_url(); ?>';

    function daysInStockFrom(row) {
        var added = row.date_added || row.created_at || '';
        var addedDate = added ? new Date(added) : null;
        if (!addedDate || isNaN(addedDate.getTime())) return 0;
        var today = new Date();
        return Math.floor((today - addedDate)/(1000*60*60*24));
    }

    function notifyDashboard(message, type) {
        if (typeof showToast === 'function') {
            showToast(message, type || 'info');
            return;
        }
        console.log(message);
    }

    function openReceiptModal(orderId) {
        if (!orderId) return;

        var receiptUrl = base_url + 'Controller_Orders/printDiv/' + orderId + '?embed=1&auto=1';

        $('#receiptFrame').attr('src', receiptUrl);
        $('#receiptShareBtn').data('share-url', receiptUrl);
        $('#receiptModal').modal('show');
    }

    function resizeReceiptModal() {
        var frame = document.getElementById('receiptFrame');
        if (!frame || !frame.contentWindow) return;

        var doc = frame.contentWindow.document;
        if (!doc || !doc.body) return;

        var contentHeight = Math.max(doc.body.scrollHeight, doc.documentElement.scrollHeight);
        var contentWidth = Math.max(doc.body.scrollWidth, doc.documentElement.scrollWidth);
        var maxHeight = Math.max(320, window.innerHeight - 180);
        var maxWidth = Math.max(320, window.innerWidth - 40);
        var targetHeight = Math.min(contentHeight, maxHeight);
        var targetWidth = Math.min(Math.max(420, contentWidth), maxWidth);

        frame.style.height = targetHeight + 'px';
        var dialog = document.querySelector('#receiptModal .modal-dialog');
        if (dialog) {
            dialog.style.width = targetWidth + 'px';
        }
    }

    $('#productsModal').on('shown.bs.modal', function(){
        if ($.fn.DataTable && !productsTable) {
            productsTable = $('#productsTable').DataTable({
                ajax: {
                    url: '<?php echo base_url("Controller_Products/fetchProductData"); ?>',
                    dataSrc: function(json){ return json.data || json; }
                },
                columns: [
                    { data: 'name', render: function(data, type, row){
                            var d = daysInStockFrom(row);
                            var rem = 15 - d;
                            var badge = (d >= 15) ? '<span class="label label-danger">Aged</span>' : '<span class="label label-default">'+rem+'d</span>';
                            var editUrl = '<?php echo base_url("Controller_Products/update/"); ?>' + (row.id || '');
                            return '<a href="' + editUrl + '"><strong>'+ (data||'') +'</strong></a> '+ badge;
                        }
                    },
                    { data: 'imei' },
                    { data: 'warehouse' },
                    { data: null, orderable:false, render: function(_,__,row){ return daysInStockFrom(row); } },
                    { data: 'actions', orderable:false, searchable:false }
                ],
                pageLength: 10
            });
            // make row clickable to open edit page (but ignore clicks on action buttons/links)
            $('#productsTable tbody').on('click', 'tr', function(e){
                var $target = $(e.target);
                if ($target.closest('a,button,input').length) return; // allow normal link/button behavior
                var data = productsTable.row(this).data();
                if (data && data.id) {
                    window.location = '<?php echo base_url("Controller_Products/update/"); ?>' + data.id;
                }
            });
        }
    });

    $('#agedModal').on('shown.bs.modal', function(){
        if ($.fn.DataTable && !agedTable) {
            agedTable = $('#agedTable').DataTable({
                ajax: {
                    url: '<?php echo site_url("Controller_Products/fetchAgedProducts"); ?>',
                    dataSrc: function(json){ return json.data || json; },
                    error: function(xhr, status, error){
                        console.error('fetchAgedProducts error', status, error, xhr.responseText, 'url:', '<?php echo site_url("Controller_Products/fetchAgedProducts"); ?>');
                        notifyDashboard('Failed to load aged stock (status ' + xhr.status + ').', 'error');
                    }
                },
                columns: [
                    { data: 'name' },
                    { data: 'imei' },
                    { data: 'warehouse' },
                    { data: null, render: function(_,__,row){ return daysInStockFrom(row); } }
                ],
                pageLength: 10
            });
        }
    });

    $('#warehousesModal').on('shown.bs.modal', function(){
        if ($.fn.DataTable && !warehousesTable) {
            warehousesTable = $('#warehousesTable').DataTable({
                ajax: {
                    url: '<?php echo base_url("Controller_Products/fetchProductData"); ?>',
                    dataSrc: function(json){ return json.data || json; }
                },
                columns: [
                    { data: 'name' },
                    { data: 'imei' },
                    { data: 'warehouse' },
                    { data: 'availability' }
                ],
                pageLength: 10
            });

            $('#warehouseSelect').on('change', function(){
                var val = $(this).val();
                warehousesTable.column(2).search(val).draw();
            });
        }
    });

    // Featured table handling: show inline tables when widgets are clicked.
    var productsFeaturedTable, agedFeaturedTable, warehousesFeaturedTable;
    function showFeatured(type){
        $('#productsModal,#agedModal,#warehousesModal,#receiptsModal').modal('hide');
        $('#featuredArea').show();
        $('.featured-area').hide();
        $('.content-wrapper').addClass('dashboard-table-open');

        if (type === 'products'){
            $('#featured-products').show();
            if (!productsFeaturedTable && $.fn.DataTable){
                var hasCheckbox = <?php echo in_array('deleteProduct', $user_permission) ? 'true' : 'false'; ?>;
                var colOffset = hasCheckbox ? 1 : 0;

                productsFeaturedTable = $('#manageProductsFeatured').DataTable({
                    dom: 'frtip',
                    responsive: true,
                    columnDefs: [
                        { targets: 0 + colOffset, width: '260px', render: function(data, type, row) {
                                try {
                                    var name = data || '';
                                    var added = row.date_added || row.created_at || '';
                                    var addedDate = added ? new Date(added) : null;
                                    var today = new Date();
                                    var diffDays = 0;
                                    if (addedDate && !isNaN(addedDate.getTime())) {
                                        diffDays = Math.floor((today - addedDate)/(1000*60*60*24));
                                    }
                                    var daysRemaining = 15 - diffDays;

                                    var color = '#00b606';
                                    if (diffDays >= 15) { color = '#e40c05'; }
                                    else if (daysRemaining >= 11) { color = '#0abb0f'; }
                                    else if (daysRemaining >= 6) { color = '#ffe600'; }
                                    else if (daysRemaining >= 1) { color = '#f19304'; }
                                    else { color = '#e40800'; }

                                    var formattedDate = '';
                                    var formattedTime = '';
                                    if (addedDate && !isNaN(addedDate.getTime())) {
                                        var y = addedDate.getFullYear();
                                        var m = ('0' + (addedDate.getMonth()+1)).slice(-2);
                                        var d = ('0' + addedDate.getDate()).slice(-2);
                                        var hh = ('0' + addedDate.getHours()).slice(-2);
                                        var mm = ('0' + addedDate.getMinutes()).slice(-2);
                                        formattedDate = y + '-' + m + '-' + d;
                                        formattedTime = hh + ':' + mm;
                                    }

                                    var tooltipHtml = '';
                                    tooltipHtml += '<div style="text-align:left;">';
                                    tooltipHtml += '<div><strong>Date Added:</strong> ' + (formattedDate || 'N/A') + '</div>';
                                    tooltipHtml += '<div><strong>Time Added:</strong> ' + (formattedTime || 'N/A') + '</div>';
                                    tooltipHtml += '<div><strong>Days in Stock:</strong> ' + diffDays + '</div>';
                                    tooltipHtml += '<div><strong>Days Remaining:</strong> ' + (diffDays >= 15 ? 0 : daysRemaining) + '</div>';
                                    tooltipHtml += '<div><strong>Inventory Threshold:</strong> 15 Days</div>';
                                    tooltipHtml += '</div>';

                                    var indicator = '\n                            <span class="stock-indicator" data-toggle="tooltip" data-html="true" title="' + tooltipHtml.replace(/"/g, '&quot;') + '" style="display:inline-block;margin-left:8px;vertical-align:middle;">'
                                        + '<i class="fa ' + (diffDays >= 15 ? 'fa-exclamation-triangle' : 'fa-circle') + '" style="color:' + color + ';font-size:12px;margin-right:4px;vertical-align:middle;"></i>'
                                        + '<span style="display:inline-block;background:' + color + ';color:#fff;border-radius:10px;padding:2px 6px;font-size:11px;vertical-align:middle;">' + (diffDays >= 15 ? 'Aged' : (daysRemaining > 0 ? daysRemaining : 0)) + '</span>'
                                        + '</span>';

                                    return '<span class="truncate" title="'+name+'">'+name+'</span>' + indicator;
                                } catch (e) {
                                    return '<span class="truncate" title="'+data+'">'+data+'</span>';
                                }
                            }
                        },
                        { targets: 1 + colOffset, render: function(data) { return '<span class="truncate" title="'+data+'">'+data+'</span>'; } },
                        { targets: 2 + colOffset, visible: false },
                        { targets: 3 + colOffset, render: function(data) { return '<span class="truncate" title="'+data+'">'+data+'</span>'; } },
                        { targets: 4 + colOffset, visible: false },
                        { targets: 5 + colOffset, visible: false, render: function(data) { var addedDate = new Date(data); var today = new Date(); var diffDays = Math.floor((today - addedDate)/(1000*60*60*24)); return diffDays >= 15 ? 'Aged' : 'Fresh'; } }
                    ],
                    pageLength: 10,
                    lengthMenu: [10,25,50,100],
                    ajax: { url: base_url + 'Controller_Products/fetchProductData', type: 'GET' },
                    columns: [
                        <?php if(in_array('deleteProduct', $user_permission)): ?>
                        { data: 'id', orderable: false, searchable: false, render: function(data){ return '<input type="checkbox" class="row-check" value="'+data+'">'; } },
                        <?php endif; ?>
                        { data: 'name' },
                        { data: 'imei' },
                        { data: 'price' },
                        { data: 'warehouse' },
                        { data: 'availability' },
                        { data: 'date_added' },
                        { data: 'actions', orderable: false, searchable: false }
                    ]
                });

                // Filters
                $('#filterWarehouseFeatured').on('change', function(){ productsFeaturedTable.column(3 + colOffset).search(this.value).draw(); });
                $('#filterAvailabilityFeatured').on('change', function(){ productsFeaturedTable.column(4 + colOffset).search(this.value).draw(); });
                $('#filterStockAgeFeatured').on('change', function(){ productsFeaturedTable.column(5 + colOffset).search(this.value).draw(); });

                productsFeaturedTable.on('draw', function(){
                    var totalRows = productsFeaturedTable.rows().data().length;
                    var outOfStockRows = productsFeaturedTable.rows({ search: 'applied' }).data().toArray().filter(function(r){ return r.availability === 'Out of Stock'; }).length;
                    if(totalRows > 0 && outOfStockRows === totalRows) { $('#outOfStockMsgFeatured').show(); } else { $('#outOfStockMsgFeatured').hide(); }
                });

                if(<?php echo in_array('deleteProduct', $user_permission) ? 'true' : 'false'; ?>) {
                    $('#selectAllFeaturedProducts').on('change', function(){ var checked=this.checked; $('#manageProductsFeatured tbody input.row-check').each(function(){ this.checked = checked; }); });
                    productsFeaturedTable.on('draw', function(){ $('[data-toggle="tooltip"]').tooltip({container: 'body'}); });
                }
            }
        } else if (type === 'aged'){
            $('#featured-aged').show();
            if (!agedFeaturedTable && $.fn.DataTable){
                agedFeaturedTable = $('#featuredAgedTable').DataTable({
                    ajax: { url: '<?php echo site_url("Controller_Products/fetchAgedProducts"); ?>', dataSrc: function(json){ return json.data || json; }, error: function(xhr, status, error){ console.error('featured fetchAgedProducts error', status, error, xhr.responseText, 'url:', '<?php echo site_url("Controller_Products/fetchAgedProducts"); ?>'); notifyDashboard('Failed to load aged stock (status ' + xhr.status + ').', 'error'); } },
                    columns: [ { data: 'name'}, { data: 'imei' }, { data: 'warehouse' }, { data: null, render: function(_,__,row){ return daysInStockFrom(row); } } ],
                    pageLength: 10
                });
            }
        } else if (type === 'warehouses'){
            $('#featured-warehouses').show();
            if (!warehousesFeaturedTable && $.fn.DataTable){
                // Controller_Warehouse returns rows like [ name, assignedUser, total_stock, total_value, status, buttons ]
                warehousesFeaturedTable = $('#featuredWarehousesTable').DataTable({
                    ajax: { url: '<?php echo base_url("Controller_Warehouse/fetchStoresData"); ?>', dataSrc: function(json){ return json.data || json; } },
                    columns: [
                        { data: 0, title: 'Warehouse' },
                        { data: 2, title: 'Stock Available' }
                    ],
                    pageLength: 10
                });
            }
        } else if (type === 'receipts'){
            $('#featured-receipts').show();
            if (!manageTableFeatured && $.fn.DataTable){
                var hasDeletePerm = <?php echo in_array('deleteOrder', $user_permission) ? 'true' : 'false'; ?>;
                var colOffset = hasDeletePerm ? 1 : 0;
                manageTableFeatured = $('#manageTableFeatured').DataTable({
                    dom: 'frtip',
                    responsive: true,
                    ajax: base_url + 'Controller_Orders/fetchOrdersData',
                    order: [],
                    columnDefs: hasDeletePerm ? [ { 'orderable': false, 'targets': [0, (hasDeletePerm ? 5 : 4)] } ] : []
                });

                if (hasDeletePerm) {
                    $('#selectAllFeatured').on('change', function() {
                        var checked = this.checked;
                        $('#manageTableFeatured tbody input.order-checkbox').each(function() { this.checked = checked; });
                    });
                    $('#manageTableFeatured tbody').on('change', 'input.order-checkbox', function() {
                        // no-op for now; could update bulk UI
                    });
                }
            }
        }
        // scroll to featured area
        var $fa = $('#featuredArea'); if ($fa.is(':visible')) $('html,body').animate({scrollTop: $fa.offset().top - 80}, 300);
    }

    function toggleFeatured(type){
        var map = {
            'products': 'featured-products',
            'aged': 'featured-aged',
            'warehouses': 'featured-warehouses',
            'receipts': 'featured-receipts'
        };
        var id = map[type];
        if (!id) return;
        var $target = $('#' + id);
        // if already visible, hide everything
        if ($target.is(':visible') && $('#featuredArea').is(':visible')){
            $('#featuredArea').hide();
            $('.featured-area').hide();
            $('.content-wrapper').removeClass('dashboard-table-open');
            // optional: scroll back to top of dashboard
            var $top = $('.dashboard-hero').first();
            if ($top.length) $('html,body').animate({scrollTop: $top.offset().top - 20}, 300);
            return;
        }
        // otherwise show the requested panel
        showFeatured(type);
    }

    $('#widget-manage-stock').on('click', function(){ toggleFeatured('products'); });
    $('#widget-warehouses').on('click', function(){ toggleFeatured('warehouses'); });
    $('#widget-receipts').on('click', function(){ toggleFeatured('receipts'); });
    $('#widget-receipts').on('click', '.dashboard-widget-list__link', function(e){ e.stopPropagation(); });

    $('#receiptFrame').on('load', function() {
        resizeReceiptModal();
    });

    $(window).on('resize', function() {
        resizeReceiptModal();
    });

    $('#receiptPrintBtn').on('click', function() {
        var frame = document.getElementById('receiptFrame');
        if (frame && frame.contentWindow) {
            frame.contentWindow.focus();
            frame.contentWindow.print();
        }
    });

    $('#receiptSaveBtn').on('click', function() {
        var frame = document.getElementById('receiptFrame');
        if (frame && frame.contentWindow && typeof frame.contentWindow.saveReceiptImage === 'function') {
            frame.contentWindow.saveReceiptImage();
        }
    });

    $('#receiptShareBtn').on('click', function() {
        var url = $(this).data('share-url') || '';
        if (!url) return;

        if (navigator.share) {
            navigator.share({
                title: 'Receipt',
                text: 'Receipt',
                url: url
            });
        } else if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(url).then(function() {
                notifyDashboard('Receipt link copied.', 'success');
            });
        } else {
            prompt('Copy receipt link:', url);
        }
    });

    // close buttons
    $('#closeFeatured,#closeFeaturedAged,#closeFeaturedWarehouses,#closeFeaturedReceipts').on('click', function(){
        $('#featuredArea').hide();
        $('.featured-area').hide();
        $('.content-wrapper').removeClass('dashboard-table-open');
    });

});
</script>
