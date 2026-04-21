<?php $session = session(); $validation = \Config\Services::validation(); ?>
<div class="content-wrapper">

<section class="content-header">
	<h1>Edit Phone</h1>
	<ol class="breadcrumb">
		<li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
		<li class="active">Edit Phone</li>
	</ol>
</section>

<section class="content">
<div class="row">
	<div class="col-md-8 col-md-offset-2">

		<?php if($session->getFlashdata('success')): ?>
			<div class="alert alert-success"><?php echo $session->getFlashdata('success'); ?></div>
		<?php endif; ?>

		<?php if($session->getFlashdata('error')): ?>
			<div class="alert alert-danger"><?php echo $session->getFlashdata('error'); ?></div>
		<?php endif; ?>

		<div class="box box-primary">
			<div class="box-header with-border">
				<h3 class="box-title">Edit Phone Details</h3>

				<?php
					if (isset($product_data['date_added']) && $product_data['date_added']) {
						$created_date = strtotime($product_data['date_added']);
						$today = strtotime(date('Y-m-d'));
						$diff_days = ($today - $created_date) / (60*60*24);
						if($diff_days > 15):
				?>
							<span class="label label-danger" style="margin-left:15px;">Aged Stock</span>
				<?php
						endif;
					}
				?>
			</div>

			<form role="form" action="<?php echo base_url('Controller_Products/update/'.$product_data['id']); ?>" method="post">
				<?php echo csrf_field(); ?>
				<div class="box-body">
					<?php echo $validation->listErrors(); ?>

					<div class="row">
						<div class="col-md-6">
							<div class="form-group">
								<label>Phone Model</label>
								<input type="text" class="form-control" name="product_name" value="<?php echo $product_data['name']; ?>" required>
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group">
								<label>IMEI Number</label>
								<input type="text" class="form-control" name="imei" value="<?php echo $product_data['imei']; ?>" required>
							</div>
						</div>
					</div>

					<div class="row">
						<div class="col-md-6">
							<div class="form-group">
								<label>Storage (GB)</label>
								<input type="number" class="form-control" name="storage" value="<?php echo isset($product_data['storage']) ? $product_data['storage'] : ''; ?>" required>
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group">
								<label>RAM (GB)</label>
								<input type="number" class="form-control" name="ram" value="<?php echo isset($product_data['ram']) ? $product_data['ram'] : ''; ?>">
							</div>
						</div>
					</div>

					<div class="row">
						<div class="col-md-6">
							<div class="form-group">
								<label>Price (UGX)</label>
								<input type="number" class="form-control" name="price" value="<?php echo $product_data['price']; ?>" required>
							</div>
						</div>
					</div>

					<div class="row">
						<div class="col-md-6">
							<div class="form-group">
								<label>Assign Warehouse</label>
								<select class="form-control" name="warehouse_id" required>
									<option value="">-- Select Warehouse --</option>
									<?php foreach($warehouses as $wh): ?>
										<option value="<?php echo $wh['id']; ?>" <?php if($product_data['warehouse_id']==$wh['id']) echo 'selected'; ?>>
											<?php echo htmlspecialchars($wh['name']); ?>
										</option>
									<?php endforeach; ?>
								</select>
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group">
								<label>Availability</label>
								<select class="form-control" name="availability">
									<option value="1" <?php if($product_data['availability']==1){echo "selected";} ?>>Available</option>
									<option value="2" <?php if($product_data['availability']==2){echo "selected";} ?>>Not Available</option>
								</select>
							</div>
						</div>
					</div>

					<div class="form-group">
						<label>Description</label>
						<textarea class="form-control" id="description" name="description" rows="3"><?php echo $product_data['description']; ?></textarea>
					</div>

				</div>

				<div class="box-footer">
					<button type="submit" class="btn btn-success"><i class="fa fa-save"></i> Update Phone</button>
					<a href="<?php echo base_url('Controller_Products'); ?>" class="btn btn-danger">Back</a>
				</div>

			</form>
		</div>
	</div>
</div>
</section>
</div>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
<script>
$(document).ready(function(){
	$("#mainProductNav").addClass('active');
	$("#manageProductNav").addClass('active');
	$("#description").wysihtml5();
});
</script>
