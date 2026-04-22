<?php $session = session(); $validation = \Config\Services::validation(); $request = service('request'); ?>
<?php $prefilledProduct = $prefilled_product ?? null; ?>
<link rel="stylesheet" type="text/css" href="//cdn.datatables.net/1.10.16/css/jquery.dataTables.min.css">
<style>
	.order-form .box-body { padding: 16px; }
	.order-form .control-label { text-align: left; }
	.order-form .form-group { margin-bottom: 12px; }
	.order-form .product-summary { margin-bottom: 12px; }
	.order-form .btn { margin-bottom: 6px; }
	@media (max-width: 767px) {
		.content-wrapper { padding: 0 8px; }
		.order-form .box-body { padding: 16px; }
		.order-form .form-horizontal .control-label { text-align: left; }
		.order-form .col-sm-5,
		.order-form .col-sm-7 { float: none; width: 100%; }
		.order-form .form-control { font-size: 16px; height: 42px; }
		.order-form textarea.form-control { height: 80px; }
		.order-form .product-summary { font-size: 14px; }
		.order-form .box-footer .btn { width: 100%; }
	}
</style>

<div class="content-wrapper">
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

	<section class="content">
		<div class="row">
			<div class="col-md-12 col-xs-12">

				<div id="messages"></div>

				<?php if($session->getFlashdata('success')): ?>
					<div class="alert alert-success alert-dismissible" role="alert">
						<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						<?php echo $session->getFlashdata('success'); ?>
					</div>
				<?php elseif($session->getFlashdata('error')): ?>
					<div class="alert alert-error alert-dismissible" role="alert">
						<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						<?php echo $session->getFlashdata('error'); ?>
					</div>
				<?php endif; ?>

				<div class="box">
					<form role="form" action="<?php echo base_url('Controller_Orders/create'); ?>" method="post" class="form-horizontal order-form">
							<?php echo csrf_field(); ?>
							<div class="box-body">

								<?php echo $validation->listErrors(); ?>

								<div class="form-group">
									<label for="gross_amount" class="col-sm-12 control-label">Date: <?php echo date('Y-m-d'); ?></label>
								</div>
								<div class="form-group">
									<label for="gross_amount" class="col-sm-12 control-label">Date: <?php echo date('h:i a'); ?></label>
								</div>

								<div class="col-md-7 col-sm-12">

									<div class="form-group">
										<label for="gross_amount" class="col-sm-5 col-xs-12 control-label" style="text-align:left;">Client Name</label>
										<div class="col-sm-7 col-xs-12">
											<input type="text" class="form-control" id="customer_name" name="customer_name" placeholder="Enter Client Name" autocomplete="off" />
										</div>
									</div>

									<div class="form-group">
										<label for="gross_amount" class="col-sm-5 col-xs-12 control-label" style="text-align:left;">NIN No</label>
										<div class="col-sm-7 col-xs-12">
											<textarea type="text" class="form-control" id="customer_address" name="customer_address" placeholder="Enter NIN No" autocomplete="off"></textarea>
										</div>
									</div>

									<div class="form-group">
										<label for="gross_amount" class="col-sm-5 col-xs-12 control-label" style="text-align:left;">Client Phone</label>
										<div class="col-sm-7 col-xs-12">
											<input type="text" class="form-control" id="customer_phone" name="customer_phone" placeholder="Enter Client Phone" autocomplete="off">
										</div>
									</div>
								</div>


								<div class="form-group">
									<label for="imei_input" class="col-sm-5 col-xs-12 control-label" style="text-align:left;">IMEI / Serial</label>
									<div class="col-sm-7 col-xs-12">
										<?php if (!empty($prefilledProduct)): ?>
											<input type="text" class="form-control" value="<?php echo htmlspecialchars($prefilledProduct['imei']); ?>" readonly>
										<?php else: ?>
											<input type="text" class="form-control" id="imei_input" name="imei_input" placeholder="Scan or enter IMEI" autocomplete="off">
										<?php endif; ?>
									</div>
								</div>

								<br /> <br/>
								<div class="product-summary" id="product_info_table">
									<div class="form-group">
										<label class="col-sm-5 col-xs-12 control-label" style="text-align:left;">Product</label>
										<div class="col-sm-7 col-xs-12">
											<?php if (!empty($prefilledProduct)): ?>
												<input type="hidden" name="product[]" id="product_1" value="<?php echo (int) $prefilledProduct['id']; ?>">
												<input type="text" class="form-control" value="<?php echo htmlspecialchars($prefilledProduct['name']); ?>" readonly>
											<?php else: ?>
												<select class="form-control select_group product" id="product_1" name="product[]" onchange="getProductData(1)" required>
													<option value=""></option>
													<?php foreach ($products as $k => $v): ?>
														<option value="<?php echo $v['id']; ?>"><?php echo $v['name']; ?></option>
													<?php endforeach; ?>
												</select>
											<?php endif; ?>
											<input type="hidden" name="rate_value[]" id="rate_value_1" class="form-control" autocomplete="off" value="<?php echo !empty($prefilledProduct) ? $prefilledProduct['price'] : ''; ?>">
										</div>
									</div>

									<div class="form-group">
										<label class="col-sm-5 col-xs-12 control-label" style="text-align:left;">Price</label>
										<div class="col-sm-7 col-xs-12">
											<input type="text" name="amount[]" id="amount_1" class="form-control" value="<?php echo !empty($prefilledProduct) ? $prefilledProduct['price'] : ''; ?>" disabled autocomplete="off">
											<input type="hidden" name="amount_value[]" id="amount_value_1" class="form-control" autocomplete="off" value="<?php echo !empty($prefilledProduct) ? $prefilledProduct['price'] : ''; ?>">
										</div>
									</div>
								</div>

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

							<div class="box-footer">
								<input type="hidden" name="service_charge_rate" value="<?php echo $company_data['service_charge_value']; ?>" autocomplete="off">
								<input type="hidden" name="vat_charge_rate" value="<?php echo $company_data['vat_charge_value']; ?>" autocomplete="off">
								<button type="submit" class="btn btn-success">Create Order</button>
								<a href="<?php echo base_url('Controller_Orders'); ?>" class="btn btn-danger">Back</a>
							</div>
						</form>
				</div>
			</div>
		</div>
	</section>
</div>

<script type="text/javascript">
	var base_url = "<?php echo base_url(); ?>";

	$(document).ready(function() {
		$(".select_group").select2();

		$("#mainOrdersNav").addClass('active');
		$("#addOrderNav").addClass('active');
		if ($('#imei_input').length) {
			$('#imei_input').focus();
		}

		var printCompleted = <?php echo json_encode($request->getGet('printed')); ?>;
		if(printCompleted === '1' && typeof showToast === 'function') {
			showToast('Print Completed', 'success');
		}

		<?php if (!empty($prefilledProduct)): ?>
		subAmount();
		<?php endif; ?>
	});

	function getProductData(row_id)
	{
		var product_id = $("#product_"+row_id).val();
		if(product_id == "") {
			$("#rate_value_"+row_id).val(0);
			$("#amount_"+row_id).val(0);
			$("#amount_value_"+row_id).val(0);
		} else {
			$.ajax({
				url: base_url + 'Controller_Orders/getProductValueById',
				type: 'post',
				data: { product_id: product_id },
				dataType: 'json',
				success:function(response) {
					$("#rate_value_"+row_id).val(response.price);
					$("#amount_"+row_id).val(response.price);
					$("#amount_value_"+row_id).val(response.price);
					subAmount();
				}
			});
		}
	}

	function subAmount()
	{
		var totalSubAmount = Number($("#amount_value_1").val() || 0);

		totalSubAmount = totalSubAmount.toFixed(2);
		$("#gross_amount").val(totalSubAmount);
		$("#gross_amount_value").val(totalSubAmount);
		$("#net_amount").val(totalSubAmount);
		$("#net_amount_value").val(totalSubAmount);
	}
</script>
