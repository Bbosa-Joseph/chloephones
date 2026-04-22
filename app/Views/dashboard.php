<?php $session = session(); $db = db_connect(); ?>
<div class="content-wrapper">
  <section class="content-header dashboard-header">
    <h1>Dashboard</h1>
    <ol class="breadcrumb">
      <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
      <li class="active">Dashboard</li>
    </ol>
  </section>

  <section class="content dashboard-page">
    <?php if($is_admin == true): ?>

      <?php
      $totalRevenue = $db->query("SELECT SUM(net_amount) as total FROM orders WHERE paid_status = 1")->getRow()->total ?? 0;
      $unpaidOrders = $db->query("SELECT * FROM orders WHERE paid_status = 2")->getNumRows();
      $sales = $db->query("SELECT * FROM orders ORDER BY id DESC LIMIT 5")->getResult();
      $monthSales = $db->query("SELECT COUNT(*) as total FROM orders WHERE MONTH(FROM_UNIXTIME(date_time))=MONTH(CURRENT_DATE())")->getRow()->total ?? 0;
      $monthRevenue = $db->query("SELECT SUM(net_amount) as total FROM orders WHERE MONTH(FROM_UNIXTIME(date_time))=MONTH(CURRENT_DATE())")->getRow()->total ?? 0;
      ?>

      <div class="dashboard-shell">

        <div class="row dashboard-stats-row">
          <div class="col-lg-3 col-sm-6">
            <a href="<?php echo base_url('Controller_Products'); ?>" class="dashboard-stat-card dashboard-stat-card--primary">
              <span class="dashboard-stat-card__icon"><i class="fa fa-mobile"></i></span>
              <div class="dashboard-stat-card__content">
                <span class="dashboard-stat-card__label">Total Phones</span>
                <h3 class="dashboard-stat-card__value"><?php echo $total_products; ?></h3>
              </div>
            </a>
          </div>

          <div class="col-lg-3 col-sm-6">
            <a href="<?php echo base_url('Controller_Members'); ?>" class="dashboard-stat-card dashboard-stat-card--warning">
              <span class="dashboard-stat-card__icon"><i class="fa fa-users"></i></span>
              <div class="dashboard-stat-card__content">
                <span class="dashboard-stat-card__label">Users</span>
                <h3 class="dashboard-stat-card__value"><?php echo $total_users; ?></h3>
              </div>
            </a>
          </div>

          <div class="col-lg-4 col-sm-12">
            <a href="<?php echo base_url('Controller_Warehouse'); ?>" class="dashboard-stat-card dashboard-stat-card--indigo">
              <span class="dashboard-stat-card__icon"><i class="fa fa-building"></i></span>
              <div class="dashboard-stat-card__content">
                <span class="dashboard-stat-card__label">Branches</span>
                <h3 class="dashboard-stat-card__value"><?php echo $total_stores ?? 0; ?></h3>
              </div>
            </a>
          </div>
        </div>

        <div class="row dashboard-stats-row">
          <div class="col-lg-4 col-sm-6">
            <div class="dashboard-stat-card dashboard-stat-card--success" style="cursor:default;">
              <span class="dashboard-stat-card__icon"><i class="fa fa-money"></i></span>
              <div class="dashboard-stat-card__content">
                <span class="dashboard-stat-card__label">Total Stock Value</span>
                <h3 class="dashboard-stat-card__value">UGX <?php echo number_format($total_stock_value ?? 0); ?></h3>
              </div>
            </div>
          </div>
          <div class="col-lg-4 col-sm-6">
            <a href="<?php echo base_url('Controller_Products'); ?>" class="dashboard-stat-card dashboard-stat-card--warning">
              <span class="dashboard-stat-card__icon"><i class="fa fa-clock-o"></i></span>
              <div class="dashboard-stat-card__content">
                <span class="dashboard-stat-card__label">Aged Items (15+ days)</span>
                <h3 class="dashboard-stat-card__value"><?php echo $aged_products ?? 0; ?></h3>
              </div>
            </a>
          </div>
        </div>

        <div class="row">
          <div class="col-lg-8">
            <div class="dashboard-panel">
              <div class="dashboard-panel__header">
                <div>
                  <h3 class="dashboard-panel__title">Recent Sales</h3>
                  <p class="dashboard-panel__subtitle">Latest orders recorded in the system</p>
                </div>
                <a href="<?php echo base_url('Controller_Orders'); ?>" class="dashboard-panel__action">View All</a>
              </div>

              <div class="dashboard-sales-list">
                <?php if(!empty($sales)): ?>
                  <?php foreach($sales as $sale): ?>
                    <div class="dashboard-sales-item">
                      <div class="dashboard-sales-item__left">
                        <div class="dashboard-sales-item__badge">
                          <i class="fa fa-shopping-bag"></i>
                        </div>
                        <div>
                          <div class="dashboard-sales-item__title">Order #<?php echo $sale->id; ?></div>
                          <div class="dashboard-sales-item__meta"><?php echo date('d M Y', $sale->date_time); ?></div>
                        </div>
                      </div>
                      <div class="dashboard-sales-item__amount"><?php echo number_format($sale->net_amount); ?></div>
                    </div>
                  <?php endforeach; ?>
                <?php else: ?>
                  <div class="dashboard-empty-state">
                    <i class="fa fa-line-chart"></i>
                    <p>No sales recorded</p>
                  </div>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>
      </div>

    <?php else: ?>

      <?php
      $user_id = $session->get('id');
      $warehouses = $db->query("SELECT id, name FROM stores WHERE assigned_user_id = ?", [$user_id])->getResult();
      if (!empty($warehouses)) {
        $warehouse_ids = array();
        foreach($warehouses as $w) { $warehouse_ids[] = (int) $w->id; }
        $ids_placeholder = implode(',', array_fill(0, count($warehouse_ids), '?'));
        $products = $db->query("SELECT * FROM products WHERE warehouse_id IN ($ids_placeholder)", $warehouse_ids)->getResult();
      } else {
        $products = [];
      }
      ?>

      <div class="dashboard-shell">
        <div class="dashboard-hero dashboard-hero--staff">
          <div class="dashboard-hero__content">
            <div class="row">
              <div class="col-sm-6">
                <div class="dashboard-stat-card dashboard-stat-card--primary" style="cursor:default;">
                  <span class="dashboard-stat-card__icon"><i class="fa fa-building"></i></span>
                  <div class="dashboard-stat-card__content">
                    <span class="dashboard-stat-card__label">Assigned Branches</span>
                    <h3 class="dashboard-stat-card__value"><?php echo count($warehouses); ?></h3>
                  </div>
                </div>
              </div>
              <div class="col-sm-6">
                <div class="dashboard-stat-card dashboard-stat-card--success" style="cursor:default;">
                  <span class="dashboard-stat-card__icon"><i class="fa fa-mobile"></i></span>
                  <div class="dashboard-stat-card__content">
                    <span class="dashboard-stat-card__label">Assigned Products</span>
                    <h3 class="dashboard-stat-card__value" id="memberProductCount"><?php echo count($products); ?></h3>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="dashboard-hero__meta">
            <span class="dashboard-hero__eyebrow">Assigned Inventory</span>
            <h2 class="dashboard-hero__title">Products assigned to you</h2>
            <p class="dashboard-hero__text">
              Review the devices currently linked to your assigned branches and track their availability at a glance.
            </p>
          </div>
        </div>

        <div class="dashboard-panel">
          <div class="dashboard-panel__header" style="align-items:flex-end;">
            <div>
              <h3 class="dashboard-panel__title">Assigned Product Inventory</h3>
              <p class="dashboard-panel__subtitle">Fast list of assigned products</p>
            </div>
            <div style="display:flex;gap:8px;align-items:center;">
              <input type="text" id="memberProductSearch" class="form-control" placeholder="Search name or IMEI" style="max-width:220px;">
              <button type="button" class="btn btn-default btn-sm" id="memberProductRefresh">
                <i class="fa fa-refresh"></i>
              </button>
            </div>
          </div>

          <div class="table-responsive">
            <?php $canCreateOrder = in_array('createOrder', $user_permission); ?>
            <table class="table table-hover dashboard-table" id="memberProductTable">
              <thead>
                <tr>
                  <th>Product Name</th>
                  <th>IMEI</th>
                  <th style="width:90px;">Action</th>
                </tr>
              </thead>
              <tbody id="memberProductBody">
                <?php if (!empty($products)): ?>
                  <?php foreach($products as $product): ?>
                    <tr data-name="<?php echo esc($product->name ?? $product->model ?? ''); ?>" data-imei="<?php echo esc($product->imei ?? ''); ?>">
                      <td><?php echo $product->name ?? $product->model ?? 'N/A'; ?></td>
                      <td><?php echo $product->imei ?? 'N/A'; ?></td>
                      <td>
                        <?php if ($canCreateOrder): ?>
                        <a href="<?php echo base_url('Controller_Orders/create?product_id=' . ($product->id ?? 0) . '&imei=' . urlencode($product->imei ?? '')); ?>" class="btn btn-primary btn-xs" title="Print Receipt">
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
                  <tr class="empty-row"><td colspan="3" style="text-align:center;color:#999;padding:20px;">No products assigned to you.</td></tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

    <?php endif; ?>
  </section>
</div>

<script>
$(document).ready(function(){
  $("#dashboardMainMenu").addClass('active');

  <?php if(!$is_admin): ?>
  var refreshUrl = '<?php echo base_url("Dashboard/memberProducts"); ?>';

  function renderMemberProducts(data) {
    $('#memberProductCount').text(data.count);
    var $body = $('#memberProductBody');
    $body.empty();
    if(data.products.length > 0) {
      $.each(data.products, function(i, p) {
        var action = '';
        if (<?php echo $canCreateOrder ? 'true' : 'false'; ?>) {
          var href = '<?php echo base_url('Controller_Orders/create?product_id='); ?>' + (p.id || 0) + '&imei=' + encodeURIComponent(p.imei || '');
          action = '<a href="' + href + '" class="btn btn-primary btn-xs" title="Print Receipt"><i class="fa fa-print"></i></a>';
        } else {
          action = '<button type="button" class="btn btn-default btn-xs" title="No permission" disabled><i class="fa fa-print"></i></button>';
        }
        $body.append('<tr data-name="'+(p.name || '')+'" data-imei="'+(p.imei || '')+'"><td>'+p.name+'</td><td>'+p.imei+'</td><td>'+action+'</td></tr>');
      });
    } else {
      $body.append('<tr class="empty-row"><td colspan="3" style="text-align:center;color:#999;padding:20px;">No products assigned to you.</td></tr>');
    }
  }

  function refreshMemberTable() {
    $.ajax({
      url: refreshUrl, type: 'GET', dataType: 'json',
      success: renderMemberProducts
    });
  }

  $('#memberProductRefresh').on('click', function(){
    refreshMemberTable();
  });

  $('#memberProductSearch').on('input', function(){
    var term = $(this).val().toLowerCase();
    $('#memberProductBody tr').each(function(){
      var name = ($(this).data('name') || '').toString().toLowerCase();
      var imei = ($(this).data('imei') || '').toString().toLowerCase();
      $(this).toggle(name.indexOf(term) !== -1 || imei.indexOf(term) !== -1);
    });
  });
  <?php endif; ?>
});
</script>
