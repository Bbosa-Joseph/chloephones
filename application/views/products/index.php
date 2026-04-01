<!-- views/product/index.php -->

<link rel="stylesheet" href="//cdn.datatables.net/1.10.16/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/1.5.1/css/buttons.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.dataTables.min.css">

<div class="content-wrapper">
  <section class="content-header">
    <h1>Manage Products</h1>
    <ol class="breadcrumb">
      <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
      <li class="active">Products</li>
    </ol>
  </section>

  <section class="content">
    <div class="row">
      <div class="col-md-12 col-xs-12">
        <div id="messages"></div>

        <?php if($this->session->flashdata('success')): ?>
          <div class="alert alert-success alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <?php echo $this->session->flashdata('success'); ?>
          </div>
        <?php elseif($this->session->flashdata('error')): ?>
          <div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <?php echo $this->session->flashdata('error'); ?>
          </div>
        <?php endif; ?>

        <?php if(in_array('createProduct', $user_permission)): ?>
          <a href="<?php echo base_url('Controller_Products/create') ?>" class="btn btn-primary">Add Product</a>
        <?php endif; ?>
        <?php if(in_array('deleteProduct', $user_permission)): ?>
          <button type="button" id="bulkDeleteBtn" class="btn btn-danger" style="display:none;" data-toggle="modal" data-target="#bulkRemoveModal">
            <i class="fa fa-trash"></i> Delete Selected (<span id="selectedCount">0</span>)
          </button>
        <?php endif; ?>
        <br><br>

        <div class="box">
          <div class="box-header with-border">
            <div class="row" style="margin-bottom:0;">
              <div class="col-sm-3 col-xs-6" style="margin-bottom:8px;">
                <select id="filterWarehouse" class="form-control input-sm">
                  <option value="">All Warehouses</option>
                  <?php if(!empty($warehouses)): foreach($warehouses as $wh): ?>
                    <option value="<?php echo htmlspecialchars($wh['name']); ?>"><?php echo htmlspecialchars($wh['name']); ?></option>
                  <?php endforeach; endif; ?>
                </select>
              </div>
              <div class="col-sm-3 col-xs-6" style="margin-bottom:8px;">
                <select id="filterAvailability" class="form-control input-sm">
                  <option value="">All Status</option>
                  <option value="In Stock">In Stock</option>
                  <option value="Out of Stock">Out of Stock</option>
                </select>
              </div>
              <div class="col-sm-3 col-xs-6" style="margin-bottom:8px;">
                <select id="filterStockAge" class="form-control input-sm">
                  <option value="">All Ages</option>
                  <option value="Fresh">Fresh</option>
                  <option value="Aged">Aged</option>
                </select>
              </div>
            </div>
          </div>
          <div class="box-body" style="overflow-x:auto;">
            <table id="manageTable" class="table table-bordered table-hover table-striped nowrap" style="width:100%; min-width:700px;">
              <thead>
                <tr>
                  <?php if(in_array('deleteProduct', $user_permission)): ?>
                    <th style="width:30px;"><input type="checkbox" id="selectAll"></th>
                  <?php endif; ?>
                  <th class="hide-mobile" style="width: 50px;">ID</th>
                  <th style="width: 120px;">Product</th>
                  <th class="hide-mobile">IMEI</th>
                  <th>Price</th>
                  <th>Warehouse</th>
                  <th class="hide-mobile">Availability</th>
                  <th>Stock Status</th>
                  <?php if(in_array('updateProduct', $user_permission) || in_array('deleteProduct', $user_permission)): ?>
                    <th>Action</th>
                  <?php endif; ?>
                </tr>
              </thead>
            </table>
          </div>
        </div>

      </div>
    </div>
  </section>
</div>

<?php if(in_array('deleteProduct', $user_permission)): ?>
<div class="modal fade" id="removeModal" tabindex="-1">
  <div class="modal-dialog modal-sm">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Remove Product</h4>
      </div>
      <form id="removeForm" action="<?php echo base_url('Controller_Products/remove') ?>" method="post">
        <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">
        <input type="hidden" name="product_id" id="product_id" value="">
        <div class="modal-body"><p>Do you really want to remove?</p></div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-danger">Delete</button>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="modal fade" id="bulkRemoveModal" tabindex="-1">
  <div class="modal-dialog modal-sm">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Bulk Delete</h4>
      </div>
      <div class="modal-body"><p>Delete <strong><span class="bulk-count">0</span></strong> selected products?</p></div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-danger" id="confirmBulkDelete">Delete All</button>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>

<!-- Scripts -->
<script src="//cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/1.5.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/1.5.1/js/buttons.flash.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.32/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.32/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/1.5.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/1.5.1/js/buttons.print.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>

<script>
$(document).ready(function() {
  $("#mainProductNav").addClass('active');

  var hasCheckbox = <?php echo in_array('deleteProduct', $user_permission) ? 'true' : 'false'; ?>;
  var colOffset = hasCheckbox ? 1 : 0;

  var manageTable = $('#manageTable').DataTable({
    dom: 'Bfrtip',
    buttons: ['copy','csv','excel','print'],
    responsive: false,
    columnDefs: [
      { targets: 0 + colOffset, width: '50px', className: 'hide-mobile', render: function(data, type, row) {
          return '<span class="truncate" title="'+data+'">'+data+'</span>';
        }
      },
      { targets: 1 + colOffset, width: '120px', render: function(data, type, row) {
          return '<span class="truncate" title="'+data+'">'+data+'</span>';
        }
      },
      { targets: 2 + colOffset, render: function(data, type, row) {
          return '<span class="truncate" title="'+data+'">'+data+'</span>';
        }, className: 'hide-mobile' },
      { targets: 4 + colOffset, render: function(data, type, row) {
          return '<span class="truncate" title="'+data+'">'+data+'</span>';
        }
      },
      { targets: 5 + colOffset, className: 'hide-mobile' },
      { targets: 6 + colOffset, render: function(data, type, row) {
          // Show only In Stock or Out of Stock
          if(row.availability && row.availability.indexOf('Active') !== -1) {
            return '<span class="label label-success">In Stock</span>';
          } else {
            return '<span class="label label-danger">Out of Stock</span>';
          }
        }
      }
    ],
    pageLength: 10,
    lengthMenu: [10, 25, 50, 100],
    ajax: {
      url: '<?php echo base_url("Controller_Products/fetchProductData"); ?>',
      type: 'GET'
    },
    columns: [
      <?php if(in_array('deleteProduct', $user_permission)): ?>
      { data: 'id', orderable: false, searchable: false, render: function(data){ return '<input type="checkbox" class="row-check" value="'+data+'">'; } },
      <?php endif; ?>
      { data: 'id' },
      { data: 'name' },
      { data: 'imei' },
      { data: 'price' },
      { 
        data: 'warehouse',
        render: function(data,type,row){
          return data ? data : 'N/A';
        }
      },
      { data: 'availability' },
      { 
        data: 'date_added',
        render: function(data,type,row){
          var addedDate = new Date(data);
          var today = new Date();
          var diffDays = Math.floor((today - addedDate)/(1000*60*60*24));
          return diffDays >= 15 
            ? '<span class="label label-aged">Aged</span>' 
            : '<span class="label label-fresh">Fresh</span>';
        }
      },
      <?php if(in_array('updateProduct', $user_permission) || in_array('deleteProduct', $user_permission)): ?>
      { data: 'actions', orderable: false }
      <?php endif; ?>
    ],
    rowCallback: function(row, data){
      var addedDate = new Date(data.date_added);
      var today = new Date();
      var diffDays = Math.floor((today - addedDate)/(1000*60*60*24));
      if(diffDays >= 15){
        $(row).addClass('aged-row');
      }
    },
    order: []
  });

  // Custom column filters
  $('#filterWarehouse').on('change', function(){
    manageTable.column(4 + colOffset).search(this.value).draw();
  });
  $('#filterAvailability').on('change', function(){
    manageTable.column(5 + colOffset).search(this.value).draw();
  });
  $('#filterStockAge').on('change', function(){
    manageTable.column(6 + colOffset).search(this.value).draw();
  });

  // Bulk selection
  <?php if(in_array('deleteProduct', $user_permission)): ?>
  function updateBulkBtn(){
    var count = $('.row-check:checked').length;
    $('#selectedCount, .bulk-count').text(count);
    $('#bulkDeleteBtn').toggle(count > 0);
  }
  $('#selectAll').on('change', function(){
    $('.row-check').prop('checked', this.checked);
    updateBulkBtn();
  });
  $('#manageTable').on('change', '.row-check', function(){
    updateBulkBtn();
    if(!this.checked) $('#selectAll').prop('checked', false);
  });
  $('#confirmBulkDelete').on('click', function(){
    var ids = [];
    $('.row-check:checked').each(function(){ ids.push($(this).val()); });
    if(ids.length === 0) return;
    $.ajax({
      url: '<?php echo base_url("Controller_Products/bulkRemove"); ?>',
      type: 'POST',
      data: { ids: ids },
      dataType: 'json',
      success: function(response){
        manageTable.ajax.reload(null, false);
        $('#bulkRemoveModal').modal('hide');
        $('#selectAll').prop('checked', false);
        updateBulkBtn();
        showToast(response.messages, response.success ? 'success' : 'error');
      }
    });
  });
  <?php endif; ?>

  <?php if(in_array('deleteProduct', $user_permission)): ?>
  $("#removeForm").on('submit', function(e){
    e.preventDefault();
    var form = $(this);
    $.ajax({
      url: form.attr('action'),
      type: form.attr('method'),
      data: { product_id: $('#removeForm input[name=product_id]').val() },
      dataType: 'json',
      success: function(response){
        manageTable.ajax.reload(null,false);
        $('#removeModal').modal('hide');
        showToast(response.messages, response.success ? 'success' : 'error');
      }
    });
  });
  <?php endif; ?>
});

<?php if(in_array('deleteProduct', $user_permission)): ?>
function removeFunc(id){
  $('#product_id').val(id);
}
<?php endif; ?>
</script>