<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h1>
      Manage Orders
     
    </h1>
    <ol class="breadcrumb">
      <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
      <li class="active">Orders</li>
    </ol>
  </section>

  <!-- Main content -->
  <section class="content">
    <!-- Small boxes (Stat box) -->
    <div class="row">
      <div class="col-md-12 col-xs-12">

        <div id="messages"></div>

        <?php if($this->session->flashdata('success')): ?>
          <div class="alert alert-success alert-dismissible" role="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <?php echo $this->session->flashdata('success'); ?>
          </div>
        <?php elseif($this->session->flashdata('error')): ?>
          <div class="alert alert-error alert-dismissible" role="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <?php echo $this->session->flashdata('error'); ?>
          </div>
        <?php endif; ?>

        <?php if(in_array('createOrder', $user_permission)): ?>
          <a href="<?php echo base_url('Controller_Orders/create') ?>" class="btn btn-primary">Add Order</a>
        <?php endif; ?>

        <?php if(in_array('deleteOrder', $user_permission)): ?>
          <button type="button" class="btn btn-danger" id="bulkDeleteBtn" style="display:none;margin-left:5px;" data-toggle="modal" data-target="#bulkRemoveModal">
            <i class="fa fa-trash"></i> Delete Selected (<span id="selectedCount">0</span>)
          </button>
        <?php endif; ?>
        <br /> <br />

        <div class="box">
          
          <!-- /.box-header -->
          <div class="box-body">
              <!-- Responsive Wrapper -->
            <div class="table-responsive">

             <table id="manageTable" class="table table-hover modern-table">
              <thead>
              <tr>
                <?php if(in_array('deleteOrder', $user_permission)): ?>
                  <th style="width:30px;"><input type="checkbox" id="selectAll"></th>
                <?php endif; ?>
                <th>Bill No.</th>
                <th>Client</th>
                <th>Contact</th>
                <th>Amount</th>
                <?php if(in_array('updateOrder', $user_permission) || in_array('viewOrder', $user_permission) || in_array('deleteOrder', $user_permission)): ?>
                  <th>Actions</th>
                <?php endif; ?>
              </tr>
              </thead>

            </table>
          </div>
          <!-- /.table-responsive -->
          <!-- /.box-body -->
        </div>
        <!-- /.box -->
      </div>
      <!-- col-md-12 -->
    </div>
    <!-- /.row -->
    

  </section>
  <!-- /.content -->
</div>
<!-- /.content-wrapper -->

<?php if(in_array('deleteOrder', $user_permission)): ?>
<!-- remove brand modal -->
<div class="modal fade" tabindex="-1" role="dialog" id="removeModal">
  <div class="modal-dialog modal-sm" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">Remove Order</h4>
      </div>

      <form role="form" action="<?php echo base_url('Controller_Orders/remove') ?>" method="post" id="removeForm">
        <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">
        <div class="modal-body">
          <p>Do you really want to remove?</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-danger">Delete</button>
        </div>
      </form>


    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
<?php endif; ?>

<?php if(in_array('deleteOrder', $user_permission)): ?>
<!-- bulk remove modal -->
<div class="modal fade" tabindex="-1" role="dialog" id="bulkRemoveModal">
  <div class="modal-dialog modal-sm" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">Bulk Delete Orders</h4>
      </div>
      <div class="modal-body">
        <p>Are you sure you want to delete <strong id="bulkCountText">0</strong> selected orders?</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-danger" id="confirmBulkDelete">Delete All</button>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>

<script type="text/javascript">
var manageTable;
var base_url = "<?php echo base_url(); ?>";
var hasDeletePerm = <?php echo in_array('deleteOrder', $user_permission) ? 'true' : 'false'; ?>;

$(document).ready(function() {

  $("#mainOrdersNav").addClass('active');
  $("#manageOrdersNav").addClass('active');

  var colOffset = hasDeletePerm ? 1 : 0;

  // initialize the datatable 
  manageTable = $('#manageTable').DataTable({
    dom: 'Bfrtip',
    buttons: [
      'copy', 'csv', 'excel', 'print'
    ],
    'ajax': base_url + 'Controller_Orders/fetchOrdersData',
    'order': [],
    'columnDefs': hasDeletePerm ? [
      { 'orderable': false, 'targets': [0, 4 + colOffset - 1] }
    ] : []
  });

  // Select all checkbox
  if(hasDeletePerm) {
    $('#selectAll').on('change', function() {
      var checked = this.checked;
      $('#manageTable tbody input.order-checkbox').each(function() {
        this.checked = checked;
      });
      updateBulkBtn();
    });

    $('#manageTable tbody').on('change', 'input.order-checkbox', function() {
      updateBulkBtn();
    });
  }

  function updateBulkBtn() {
    var count = $('#manageTable tbody input.order-checkbox:checked').length;
    $('#selectedCount').text(count);
    $('#bulkCountText').text(count);
    if(count > 0) {
      $('#bulkDeleteBtn').show();
    } else {
      $('#bulkDeleteBtn').hide();
    }
  }

  // Bulk delete confirm
  $('#confirmBulkDelete').on('click', function() {
    var ids = [];
    $('#manageTable tbody input.order-checkbox:checked').each(function() {
      ids.push($(this).val());
    });
    if(ids.length === 0) return;

    $.ajax({
      url: base_url + 'Controller_Orders/bulkRemove',
      type: 'POST',
      data: { order_ids: ids },
      dataType: 'json',
      success: function(response) {
        manageTable.ajax.reload(null, false);
        $('#bulkRemoveModal').modal('hide');
        $('#selectAll').prop('checked', false);
        updateBulkBtn();
        if(response.success) {
          showToast(response.messages, 'success');
        } else {
          showToast(response.messages, 'error');
        }
      }
    });
  });

});

// remove functions 
function removeFunc(id)
{
  if(id) {
    $("#removeForm").off('submit').on('submit', function() {

      var form = $(this);

      // remove the text-danger
      $(".text-danger").remove();

      $.ajax({
        url: form.attr('action'),
        type: form.attr('method'),
        data: { order_id:id }, 
        dataType: 'json',
        success:function(response) {

          manageTable.ajax.reload(null, false); 

          if(response.success === true) {
            showToast(response.messages, 'success');

            // hide the modal
            $("#removeModal").modal('hide');

          } else {

            showToast(response.messages, 'warning');
          }
        }
      }); 

      return false;
    });
  }
}


</script>

<script type="text/javascript" src="https://cdn.datatables.net/buttons/1.5.1/js/dataTables.buttons.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/buttons/1.5.1/js/buttons.flash.min.js"></script>

<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.32/pdfmake.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.32/vfs_fonts.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/buttons/1.5.1/js/buttons.html5.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/buttons/1.5.1/js/buttons.print.min.js"></script>
