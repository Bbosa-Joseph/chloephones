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
      $totalRevenue = $this->db->query("SELECT SUM(net_amount) as total FROM orders WHERE paid_status = 1")->row()->total;
      $unpaidOrders = $this->db->query("SELECT * FROM orders WHERE paid_status = 2")->num_rows();
      $sales = $this->db->query("SELECT * FROM orders ORDER BY id DESC LIMIT 5")->result();
      $monthSales = $this->db->query("SELECT COUNT(*) as total FROM orders WHERE MONTH(date_time)=MONTH(CURRENT_DATE())")->row()->total;
      $monthRevenue = $this->db->query("SELECT SUM(net_amount) as total FROM orders WHERE MONTH(date_time)=MONTH(CURRENT_DATE())")->row()->total;
      ?>

      <div class="dashboard-shell">
        <div class="dashboard-hero">
          <div class="dashboard-hero__content">
            <span class="dashboard-hero__eyebrow">Overview</span>
            <h2 class="dashboard-hero__title">Welcome back, <?php echo ucfirst($this->session->userdata('username')); ?></h2>
            <p class="dashboard-hero__text">
              Monitor sales performance, inventory activity, branch coverage, and order status from one clean workspace.
            </p>
          </div>
          <div class="dashboard-hero__meta">
            <div class="dashboard-hero__meta-item">
              <span class="dashboard-hero__meta-label">This Month</span>
              <strong><?php echo (int) $monthSales; ?> Sales</strong>
            </div>
            <div class="dashboard-hero__meta-item">
              <span class="dashboard-hero__meta-label">Revenue</span>
              <strong><?php echo number_format($monthRevenue ?? 0); ?></strong>
            </div>
          </div>
        </div>

        <div class="row dashboard-stats-row">
          <!-- Total Revenue widget removed -->

          <div class="col-lg-3 col-sm-6">
            <a href="<?php echo base_url('Controller_Products/') ?>" class="dashboard-stat-card dashboard-stat-card--primary">
              <span class="dashboard-stat-card__icon"><i class="fa fa-mobile"></i></span>
              <div class="dashboard-stat-card__content">
                <span class="dashboard-stat-card__label">Total Phones</span>
                <h3 class="dashboard-stat-card__value"><?php echo $total_products; ?></h3>
              </div>
            </a>
          </div>

          <!-- Brands widget removed -->

          <div class="col-lg-3 col-sm-6">
            <a href="<?php echo base_url('Controller_Members/') ?>" class="dashboard-stat-card dashboard-stat-card--warning">
              <span class="dashboard-stat-card__icon"><i class="fa fa-users"></i></span>
              <div class="dashboard-stat-card__content">
                <span class="dashboard-stat-card__label">Users</span>
                <h3 class="dashboard-stat-card__value"><?php echo $total_users; ?></h3>
              </div>
            </a>
          </div>

          <!-- Paid Orders widget removed -->

          <!-- Unpaid Orders widget removed -->

          <div class="col-lg-4 col-sm-12">
            <a href="<?php echo base_url('Controller_Warehouse/') ?>" class="dashboard-stat-card dashboard-stat-card--indigo">
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
          <!-- Out of Stock widget removed -->
          <div class="col-lg-4 col-sm-6">
            <a href="<?php echo base_url('Controller_Products/') ?>" class="dashboard-stat-card dashboard-stat-card--warning">
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
                <a href="<?php echo base_url('Controller_Orders/') ?>" class="dashboard-panel__action">View All</a>
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
                          <div class="dashboard-sales-item__meta"><?php echo date('d M Y', strtotime($sale->date_time)); ?></div>
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

          <!-- Monthly Summary widget removed -->
        </div>
      </div>

    <?php else: ?>

      <?php
      $user_id = $this->session->userdata('id');
      $warehouses = $this->db->query("SELECT id, name FROM stores WHERE assigned_user_id = ?", array($user_id))->result();
      if (!empty($warehouses)) {
        $warehouse_ids = array();
        foreach($warehouses as $w) { $warehouse_ids[] = (int) $w->id; }
        $ids_placeholder = implode(',', array_fill(0, count($warehouse_ids), '?'));
        $products = $this->db->query("SELECT * FROM products WHERE warehouse_id IN ($ids_placeholder)", $warehouse_ids)->result();
      } else {
        $products = [];
      }
      ?>

      <div class="dashboard-shell">
        <div class="dashboard-hero dashboard-hero--staff">
          <div class="dashboard-hero__content">
            <span class="dashboard-hero__eyebrow">Assigned Inventory</span>
            <h2 class="dashboard-hero__title">Products assigned to you</h2>
            <p class="dashboard-hero__text">
              Review the devices currently linked to your assigned branches and track their availability at a glance.
            </p>
          </div>
          <div class="dashboard-hero__meta">
            <div class="dashboard-hero__meta-item">
              <span class="dashboard-hero__meta-label">Assigned Branches</span>
              <strong><?php echo count($warehouses); ?></strong>
            </div>
            <div class="dashboard-hero__meta-item">
              <span class="dashboard-hero__meta-label">Assigned Products</span>
              <strong id="memberProductCount"><?php echo count($products); ?></strong>
            </div>
          </div>
        </div>

        <div class="dashboard-panel">
          <div class="dashboard-panel__header">
            <div>
              <h3 class="dashboard-panel__title">Assigned Product Inventory</h3>
              <p class="dashboard-panel__subtitle">Inventory available across your linked branches <span id="lastRefresh" style="color:#aaa;font-size:12px;"></span></p>
            </div>
          </div>

          <div class="table-responsive">
            <table class="table table-hover dashboard-table" id="memberProductTable">
              <thead>
                <tr>
                  <th>Product Name</th>
                  <th>IMEI</th>
                  <th>Warehouse</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody id="memberProductBody">
                <?php if (!empty($products)): ?>
                  <?php foreach($products as $product): ?>
                    <?php
                    $warehouse_name = '';
                    foreach($warehouses as $w) {
                      if ($w->id == $product->warehouse_id) {
                        $warehouse_name = $w->name;
                        break;
                      }
                    }
                    ?>
                    <tr>
                      <td><?php echo $product->name ?? $product->model ?? 'N/A'; ?></td>
                      <td><?php echo $product->imei ?? 'N/A'; ?></td>
                      <td><?php echo $warehouse_name; ?></td>
                      <td>
                        <?php if ($product->availability == 1): ?>
                          <span class="dashboard-status-badge dashboard-status-badge--success">Available</span>
                        <?php else: ?>
                          <span class="dashboard-status-badge dashboard-status-badge--muted">Sold</span>
                        <?php endif; ?>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php else: ?>
                  <tr class="empty-row"><td colspan="4" style="text-align:center;color:#999;padding:20px;">No products assigned to you.</td></tr>
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
  // Auto-refresh member product table
  var refreshUrl = '<?php echo base_url("Dashboard/memberProducts"); ?>';

  function refreshMemberTable() {
    $.ajax({
      url: refreshUrl, type: 'GET', dataType: 'json',
      success: function(data) {
        $('#memberProductCount').text(data.count);
        var $body = $('#memberProductBody');
        $body.empty();
        if(data.products.length > 0) {
          $.each(data.products, function(i, p) {
            var badge = p.status === 'Available'
              ? '<span class="dashboard-status-badge dashboard-status-badge--success">Available</span>'
              : '<span class="dashboard-status-badge dashboard-status-badge--muted">Sold</span>';
            $body.append('<tr><td>'+p.name+'</td><td>'+p.imei+'</td><td>'+p.warehouse+'</td><td>'+badge+'</td></tr>');
          });
        } else {
          $body.append('<tr class="empty-row"><td colspan="4" style="text-align:center;color:#999;padding:20px;">No products assigned to you.</td></tr>');
        }
        var now = new Date();
        $('#lastRefresh').text('(Updated ' + now.toLocaleTimeString() + ')');
      }
    });
  }

  // Refresh every 15 seconds (synced with notification polling)
  setInterval(refreshMemberTable, 15000);
  <?php endif; ?>
});
</script>