<link rel="stylesheet" href="<?php echo base_url('assets/plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.min.css'); ?>">

<div class="content-wrapper">

<section class="content-header">
  <h1>Add New Phone</h1>

  <ol class="breadcrumb">
    <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
    <li class="active">Add Phone</li>
  </ol>
</section>


<section class="content">

<div class="row">
<div class="col-md-8 col-md-offset-2">

<?php if($this->session->flashdata('success')): ?>
<div class="alert alert-success">
<?php echo $this->session->flashdata('success'); ?>
</div>
<?php endif; ?>


<!-- Duplicate IMEI Modal -->
<div class="modal fade" id="duplicateImeiModal" tabindex="-1" role="dialog" aria-labelledby="duplicateImeiModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="duplicateImeiModalLabel">Duplicate IMEI</h4>
      </div>
      <div class="modal-body">
        <?php if($this->session->flashdata('errors') && strpos($this->session->flashdata('errors'), 'IMEI') !== false): ?>
          <?php echo $this->session->flashdata('errors'); ?>
        <?php endif; ?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" data-dismiss="modal">OK</button>
      </div>
    </div>
  </div>
</div>

<?php if($this->session->flashdata('errors') && strpos($this->session->flashdata('errors'), 'IMEI') !== false): ?>
<script>
  $(document).ready(function(){
    $('#duplicateImeiModal').modal('show');
  });
</script>
<?php endif; ?>

<div class="box box-primary">

<div class="box-header with-border">
<h3 class="box-title">Phone Information</h3>
</div>


<form role="form" action="<?php echo base_url('Controller_Products/create'); ?>" method="post">
<input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">

<div class="box-body">

<?php echo validation_errors(); ?>


<div class="form-group">
<label>Phone Model</label>
<input type="text" 
class="form-control" 
name="product_name"
placeholder="Example: Samsung A14"
required>
</div>


<div class="form-group">
<label>IMEI Number</label>
<input type="text"
class="form-control"
name="imei"
placeholder="Enter IMEI"
required>
</div>


<div class="form-group">
<label>Price (UGX)</label>
<input type="number"
class="form-control"
name="price"
placeholder="Enter Price"
required>
</div>



<div class="form-group">
<label>Storage (GB)</label>
<input type="number"
class="form-control"
name="storage"
placeholder="Enter Storage (e.g. 128)"
>
</div>

<div class="form-group">
<label>Assign Warehouse (optional)</label>
<select class="form-control" name="warehouse_id">
  <option value="">-- Select Warehouse --</option>
  <?php if(isset($warehouses) && is_array($warehouses)): ?>
    <?php foreach($warehouses as $wh): ?>
      <option value="<?php echo $wh['id']; ?>"><?php echo htmlspecialchars($wh['name']); ?></option>
    <?php endforeach; ?>
  <?php endif; ?>
</select>
</div>


<div class="form-group">
<textarea
class="form-control"
id="description"
name="description"
rows="3"
placeholder="Optional notes"></textarea>
</div>


<div class="form-group">
<label>Availability</label>

<select class="form-control" name="availability">

<option value="1">Available</option>
<option value="2">Not Available</option>

</select>

</div>


</div>


<div class="box-footer">

<button type="submit" class="btn btn-success">
<i class="fa fa-save"></i> Save Phone
</button>

<a href="<?php echo base_url('Controller_Products'); ?>" class="btn btn-danger">
Back
</a>

</div>

</form>

</div>
</div>
</div>

</section>
</div>


<script src="<?php echo base_url('assets/plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.all.min.js'); ?>"></script>


<script>

$(document).ready(function(){

$("#mainProductNav").addClass('active');
$("#addProductNav").addClass('active');

});



$(document).ready(function(){

  $("#mainProductNav").addClass('active');
  $("#addProductNav").addClass('active');

  $("#description").wysihtml5();

});

</script>