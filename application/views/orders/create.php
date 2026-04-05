
<link rel="stylesheet" type="text/css" href="//cdn.datatables.net/1.10.16/css/jquery.dataTables.min.css">


<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h1>
      Add New Orders
      <small></small>
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


        <div class="box">
          
          <!-- /.box-header -->
          <form role="form" action="<?php base_url('Controller_Orders/create') ?>" method="post" class="form-horizontal">
              <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">
              <div class="box-body">

                <?php echo validation_errors(); ?>

                <div class="form-group">
                  <label for="gross_amount" class="col-sm-12 control-label">Date: <?php echo date('Y-m-d') ?></label>
                </div>
                <div class="form-group">
                  <label for="gross_amount" class="col-sm-12 control-label">Date: <?php echo date('h:i a') ?></label>
                </div>

                <div class="col-md-7 col-xs-12 pull pull-left">

                  <div class="form-group">
                    <label for="gross_amount" class="col-sm-5 control-label" style="text-align:left;">Client Name</label>
                    <div class="col-sm-7">
                      <input type="text" class="form-control" id="customer_name" name="customer_name" placeholder="Enter Client Name" autocomplete="off" />
                    </div>
                  </div>

                  <div class="form-group">
                    <label for="gross_amount" class="col-sm-5 control-label" style="text-align:left;">NIN No</label>
                    <div class="col-sm-7">
                      <textarea type="text" class="form-control" id="customer_address" name="customer_address" placeholder="Enter NIN No" autocomplete="off"></textarea>
                    </div>
                  </div>

                  <div class="form-group">
                    <label for="gross_amount" class="col-sm-5 control-label" style="text-align:left;">Client Phone</label>
                    <div class="col-sm-7">
                      <input type="text" class="form-control" id="customer_phone" name="customer_phone" placeholder="Enter Client Phone" autocomplete="off">
                    </div>
                  </div>
                </div>


                <div class="form-group">
                  <label for="imei_input" class="col-sm-5 control-label" style="text-align:left;">IMEI / Serial</label>
                  <div class="col-sm-7">
                    <input type="text" class="form-control" id="imei_input" name="imei_input" placeholder="Scan or enter IMEI" autocomplete="off">
                  </div>
                </div>
                
                
                <br /> <br/>
                <table class="table table-bordered" id="product_info_table">
                  <thead>
                    <tr>
                      <th style="width:70%">Product</th>
                      <th style="width:20%">Amount</th>
                      <th style="width:10%"></th>
                    </tr>
                  </thead>

                   <tbody>
                     <tr id="row_1">
                       <td>
                        <select class="form-control select_group product" data-row-id="row_1" id="product_1" name="product[]" style="width:100%;" onchange="getProductData(1)" required>
                            <option value=""></option>
                            <?php foreach ($products as $k => $v): ?>
                              <option value="<?php echo $v['id'] ?>"><?php echo $v['name'] ?></option>
                            <?php endforeach ?>
                          </select>
                          <input type="hidden" name="rate_value[]" id="rate_value_1" class="form-control" autocomplete="off">
                        </td>
                        <td>
                          <input type="text" name="amount[]" id="amount_1" class="form-control" disabled autocomplete="off">
                          <input type="hidden" name="amount_value[]" id="amount_value_1" class="form-control" autocomplete="off">
                        </td>
                        <td><button type="button" class="btn btn-danger btn-sm" onclick="removeRow('1')"><i class="fa fa-close"></i></button></td>
                     </tr>
                   </tbody>
                </table>

                <br /> <br/>

                <input type="hidden" id="gross_amount" name="gross_amount" autocomplete="off">
                <input type="hidden" id="gross_amount_value" name="gross_amount_value" autocomplete="off">
                <input type="hidden" id="service_charge" name="service_charge" autocomplete="off">
                <input type="hidden" id="service_charge_value" name="service_charge_value" autocomplete="off">
                <input type="hidden" id="vat_charge" name="vat_charge" autocomplete="off">
                <input type="hidden" id="vat_charge_value" name="vat_charge_value" autocomplete="off">
                <input type="hidden" id="discount" name="discount" value="0" autocomplete="off">
                <input type="hidden" id="net_amount" name="net_amount" autocomplete="off">
                <input type="hidden" id="net_amount_value" name="net_amount_value" autocomplete="off">
              </div>
              <!-- /.box-body -->

              <div class="box-footer">
                <input type="hidden" name="service_charge_rate" value="<?php echo $company_data['service_charge_value'] ?>" autocomplete="off">
                <input type="hidden" name="vat_charge_rate" value="<?php echo $company_data['vat_charge_value'] ?>" autocomplete="off">
                <button type="submit" class="btn btn-success">Create Order</button>
                <a href="<?php echo base_url('Controller_Orders/') ?>" class="btn btn-danger">Back</a>
              </div>
            </form>
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

<script type="text/javascript">
  var base_url = "<?php echo base_url(); ?>";

  $(document).ready(function() {
    $(".select_group").select2();
    // $("#description").wysihtml5();

    $("#mainOrdersNav").addClass('active');
    $("#addOrderNav").addClass('active');
    $('#imei_input').focus();

    var printCompleted = <?php echo json_encode($this->input->get('printed', true)); ?>;
    if(printCompleted === '1' && typeof showToast === 'function') {
      showToast('Print Completed', 'success');
    }
    
    var btnCust = '<button type="button" class="btn btn-secondary" title="Add picture tags" ' + 
        'onclick="alert(\'Call your custom code here.\')">' +
        '<i class="glyphicon glyphicon-tag"></i>' +
        '</button>'; 
  
    // Add new row in the table 
    $("#add_row").unbind('click').bind('click', function() {
      var table = $("#product_info_table");
      var count_table_tbody_tr = $("#product_info_table tbody tr").length;
      var row_id = count_table_tbody_tr + 1;

      $.ajax({
          url: base_url + '/Controller_Orders/getTableProductRow/',
          type: 'post',
          dataType: 'json',
          success:function(response) {
            
              // console.log(reponse.x);
               var html = '<tr id="row_'+row_id+'">'+
                   '<td>'+ 
                    '<select class="form-control select_group product" data-row-id="'+row_id+'" id="product_'+row_id+'" name="product[]" style="width:100%;" onchange="getProductData('+row_id+')">'+
                        '<option value=""></option>';
                        $.each(response, function(index, value) {
                          html += '<option value="'+value.id+'">'+value.name+'</option>';             
                        });
                        
                      html += '</select>'+
                    '<input type="hidden" name="rate_value[]" id="rate_value_'+row_id+'" class="form-control">'+
                    '</td>'+ 
                    '<td><input type="text" name="amount[]" id="amount_'+row_id+'" class="form-control" disabled><input type="hidden" name="amount_value[]" id="amount_value_'+row_id+'" class="form-control"></td>'+
                    '<td><button type="button" class="btn btn-danger btn-sm" onclick="removeRow(\''+row_id+'\')"><i class="fa fa-close"></i></button></td>'+
                    '</tr>';

                if(count_table_tbody_tr >= 1) {
                $("#product_info_table tbody tr:last").after(html);  
              }
              else {
                $("#product_info_table tbody").html(html);
              }

              $(".product").select2();

          }
        });

      return false;
    });

  }); // /document

  function getTotal(row = null) {
    if(row) {
      var total = Number($("#rate_value_"+row).val());
      total = total.toFixed(2);
      $("#amount_"+row).val(total);
      $("#amount_value_"+row).val(total);
      
      subAmount();

    } else {
      alert('no row !! please refresh the page');
    }
  }

  function isProductAlreadyInOrder(productId, excludeRowId) {
    var exists = false;
    var normalizedProductId = String(productId);

    $("#product_info_table .product").each(function() {
      var currentId = $(this).val();
      if(!currentId) return;

      var currentRowId = $(this).attr('id').split('_')[1];
      if(excludeRowId && String(currentRowId) === String(excludeRowId)) {
        return;
      }

      if(String(currentId) === normalizedProductId) {
        exists = true;
      }
    });

    return exists;
  }

  function showDuplicateImeiWarning() {
    var message = 'IMEI already added in this order.';
    if(typeof showToast === 'function') {
      showToast(message, 'warning');
    } else {
      alert(message);
    }
  }

  // get the product information from the server
  function getProductData(row_id)
  {
    var product_id = $("#product_"+row_id).val();    
    if(product_id == "") {
      $("#rate_value_"+row_id).val("");

      $("#amount_"+row_id).val("");
      $("#amount_value_"+row_id).val("");

    } else {
      if(isProductAlreadyInOrder(product_id, row_id)) {
        $("#product_"+row_id).val("").trigger('change.select2');
        $("#rate_value_"+row_id).val("");
        $("#amount_"+row_id).val("");
        $("#amount_value_"+row_id).val("");
        showDuplicateImeiWarning();
        subAmount();
        return;
      }

      $.ajax({
        url: base_url + 'Controller_Orders/getProductValueById',
        type: 'post',
      // Removed add_row button and JS for single product only
    $("#vat_charge").val(vat);
    $("#vat_charge_value").val(vat);

    // service
    var service = (Number($("#gross_amount").val())/100) * service_charge;
    service = service.toFixed(2);
    $("#service_charge").val(service);
    $("#service_charge_value").val(service);
    
    // total amount
    var totalAmount = (Number(totalSubAmount) + Number(vat) + Number(service));
    totalAmount = totalAmount.toFixed(2);
    $("#net_amount").val(totalAmount);
    $("#net_amount_value").val(totalAmount);

  } // /sub total amount

// auto add product row function

  function addProductToOrder(product) {
    if(isProductAlreadyInOrder(product.id, null)) {
      showDuplicateImeiWarning();
      return;
    }

    var table = $("#product_info_table");
    var count_table_tbody_tr = $("#product_info_table tbody tr").length;
    var row_id = count_table_tbody_tr + 1;

    var html = '<tr id="row_'+row_id+'">'+
        '<td>'+ 
        '<select class="form-control select_group product" data-row-id="'+row_id+'" id="product_'+row_id+'" name="product[]" style="width:100%;" onchange="getProductData('+row_id+')">'+
            '<option value="'+product.id+'" selected>'+product.name+'</option>'+
        '</select>'+
      '<input type="hidden" name="rate_value[]" id="rate_value_'+row_id+'" class="form-control" value="'+product.price+'">'+
        '</td>'+ 
        '<td><input type="text" name="amount[]" id="amount_'+row_id+'" class="form-control" disabled value="'+product.price+'"><input type="hidden" name="amount_value[]" id="amount_value_'+row_id+'" class="form-control" value="'+product.price+'"></td>'+
        '<td><button type="button" class="btn btn-danger btn-sm" onclick="removeRow(\''+row_id+'\')"><i class="fa fa-close"></i></button></td>'+
        '</tr>';

    if(count_table_tbody_tr >= 1) {
        $("#product_info_table tbody tr:last").after(html);  
    } else {
        $("#product_info_table tbody").html(html);
    }

    $(".product").select2();
    subAmount();
}



  function removeRow(tr_id)
  {
    $("#product_info_table tbody tr#row_"+tr_id).remove();
    subAmount();
  }

  function loadProductByImei(imei) {
    if(!imei) return;

    $.post('<?=base_url("Controller_Orders/getProductByIMEI")?>', {imei: imei}, function(res){
      res = JSON.parse(res);
      if(res.status == 'success') {
        addProductToOrder(res.data);
        $('#imei_input').val('').focus();
      } else {
        alert(res.message);
      }
    });
  }

  $('#imei_input').off('change').on('change', function() {
    loadProductByImei($(this).val());
  });

  var quickReceiptImei = <?php echo json_encode($this->input->get('imei', true)); ?>;
  if(quickReceiptImei) {
    $('#imei_input').val(quickReceiptImei);
    loadProductByImei(quickReceiptImei);
  }

</script>
