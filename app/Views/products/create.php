<?php $session = session(); $validation = \Config\Services::validation(); ?>
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

<?php if($session->getFlashdata('success')): ?>
<div class="alert alert-success">
<?php echo $session->getFlashdata('success'); ?>
</div>
<?php endif; ?>

<div class="modal fade" id="duplicateImeiModal" tabindex="-1" role="dialog" aria-labelledby="duplicateImeiModalLabel">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="duplicateImeiModalLabel">Duplicate IMEI</h4>
			</div>
			<div class="modal-body">
				<?php if($session->getFlashdata('errors') && strpos($session->getFlashdata('errors'), 'IMEI') !== false): ?>
					<?php echo $session->getFlashdata('errors'); ?>
				<?php endif; ?>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-primary" data-dismiss="modal">OK</button>
			</div>
		</div>
	</div>
</div>

<?php if($session->getFlashdata('errors') && strpos($session->getFlashdata('errors'), 'IMEI') !== false): ?>
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
<?php echo csrf_field(); ?>

<div class="box-body">

<?php echo $validation->listErrors(); ?>

<div class="row">
	<div class="col-md-6">
		<div class="form-group">
			<label>Phone Model</label>
			<input type="text" class="form-control" name="product_name" placeholder="Example: Samsung A14" required>
		</div>
	</div>

        <div class="form-group">
            <label>IMEI Numbers (Manual Entry)</label>
            <div id="imei-wrapper">
                <div class="input-group" style="margin-bottom:5px;">
                    <input type="text" name="imei_list[]" class="form-control" placeholder="Enter IMEI">
                    <span class="input-group-btn">
                        <button type="button" class="btn btn-success add-imei"><i class="fa fa-plus"></i></button>
                    </span>
                </div>
            </div>
        </div>

        <div class="form-group">
            <label>OR Paste Multiple IMEIs</label>
            <textarea 
                class="form-control" 
                name="imei_bulk" 
                rows="4"
                placeholder="Paste IMEIs (comma, space, or new line separated)"></textarea>
        </div>
</div>

<div class="row">
	<div class="col-md-6">
		<div class="form-group">
			<label>Storage (GB)</label>
			<input type="number" class="form-control" name="storage" placeholder="e.g. 128">
		</div>
	</div>
	<div class="col-md-6">
		<div class="form-group">
			<label>RAM (GB)</label>
			<input type="number" class="form-control" name="ram" placeholder="e.g. 4">
		</div>
	</div>
</div>

<div class="row">
	<div class="col-md-6">
		<div class="form-group">
			<label>Price (UGX)</label>
			<input type="number" class="form-control" name="price" placeholder="Enter Price" required>
		</div>
	</div>
	<div class="col-md-6">
		<div class="form-group">
			<label>Assign Warehouse</label>
			<select class="form-control" name="warehouse_id">
				<option value="">-- Select Warehouse --</option>
				<?php if(isset($warehouses) && is_array($warehouses)): ?>
					<?php foreach($warehouses as $wh): ?>
						<option value="<?php echo $wh['id']; ?>"><?php echo htmlspecialchars($wh['name']); ?></option>
					<?php endforeach; ?>
				<?php endif; ?>
			</select>
		</div>
	</div>
</div>

<div class="row">
	<div class="col-md-6">
		<div class="form-group">
			<label>Availability</label>
			<select class="form-control" name="availability">
				<option value="1">Available</option>
				<option value="2">Not Available</option>
			</select>
		</div>
	</div>
</div>

<div class="form-group">
	<label>Description</label>
	<textarea class="form-control" id="description" name="description" rows="3" placeholder="Optional notes"></textarea>
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
	$("#description").wysihtml5();

});


        $(document).ready(function(){

            // Add new IMEI field
            $(document).on('click', '.add-imei', function(){
                var html = `
                <div class="input-group" style="margin-bottom:5px;">
                    <input type="text" name="imei_list[]" class="form-control" placeholder="Enter IMEI">
                    <span class="input-group-btn">
                        <button type="button" class="btn btn-danger remove-imei"><i class="fa fa-minus"></i></button>
                    </span>
                </div>`;
                $('#imei-wrapper').append(html);
            });

            // Remove field
            $(document).on('click', '.remove-imei', function(){
                $(this).closest('.input-group').remove();
            });

        });

</script>
