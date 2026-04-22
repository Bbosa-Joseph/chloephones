<?php $session = session(); $validation = \Config\Services::validation(); ?>
<link rel="stylesheet" type="text/css" href="//cdn.datatables.net/1.10.16/css/jquery.dataTables.min.css">

<div class="content-wrapper">
	<section class="content-header">
		<h1>
			Edit Orders
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
					<form role="form" action="<?php echo base_url('Controller_Orders/update/'.($order_data['order']['id'] ?? '')); ?>" method="post" class="form-horizontal">
							<?php echo csrf_field(); ?>
							<div class="box-body">

								<?php echo $validation->listErrors(); ?>

								<div class="form-group">
									<label for="date" class="col-sm-12 control-label">Date: <?php echo date('Y-m-d'); ?></label>
								</div>
								<div class="form-group">
									<label for="time" class="col-sm-12 control-label">Date: <?php echo date('h:i a'); ?></label>
								</div>

								<div class="col-md-7 col-xs-12 pull pull-left">

									<div class="form-group">
										<label for="gross_amount" class="col-sm-5 control-label" style="text-align:left;">Client Name</label>
										<div class="col-sm-7">
											<input type="text" class="form-control" id="customer_name" name="customer_name" placeholder="Enter Client Name" value="<?php echo isset($order_data['order']['customer_name']) ? $order_data['order']['customer_name'] : ''; ?>" autocomplete="off"/>
										</div>
									</div>

									<div class="form-group">
										<label for="gross_amount" class="col-sm-5 control-label" style="text-align:left;">Client Address</label>
										<div class="col-sm-7">
											<textarea type="text" class="form-control" id="customer_address" name="customer_address" placeholder="Enter Client Address" autocomplete="off"><?php echo isset($order_data['order']['customer_address']) ? $order_data['order']['customer_address'] : ''; ?></textarea>
										</div>
									</div>

									<div class="form-group">
										<label for="gross_amount" class="col-sm-5 control-label" style="text-align:left;">Client Phone</label>
										<div class="col-sm-7">
											<input type="text" class="form-control" id="customer_phone" name="customer_phone" placeholder="Enter Client Phone" value="<?php echo isset($order_data['order']['customer_phone']) ? $order_data['order']['customer_phone'] : ''; ?>" autocomplete="off">
										</div>
									</div>
								</div>

								<br /> <br/>
								<table class="table table-bordered" id="product_info_table">
									<thead>
										<tr>
											<th style="width:60%">Product</th>
											<th style="width:10%">Rate</th>
											<th style="width:20%">Amount</th>
											<th style="width:10%"><button type="button" id="add_row" class="btn btn-primary btn-sm"><i class="fa fa-plus"></i></button></th>
										</tr>
									</thead>

									 <tbody>
										<?php if(isset($order_data['order_item'])): ?>
											<?php $x = 1; ?>
											<?php foreach ($order_data['order_item'] as $key => $val): ?>
											 <tr id="row_<?php echo $x; ?>">
												 <td>
													<select class="form-control select_group product" data-row-id="row_<?php echo $x; ?>" id="product_<?php echo $x; ?>" name="product[]" style="width:100%;" onchange="getProductData(<?php echo $x; ?>)" required>
															<option value=""></option>
															<?php foreach ($products as $k => $v): ?>
																<option value="<?php echo $v['id']; ?>" <?php if($val['product_id'] == $v['id']) { echo "selected='selected'"; } ?>><?php echo $v['name']; ?></option>
															<?php endforeach; ?>
														</select>
													</td>
													<td>
														<input type="text" name="rate[]" id="rate_<?php echo $x; ?>" class="form-control" disabled value="<?php echo $val['rate']; ?>" autocomplete="off">
														<input type="hidden" name="rate_value[]" id="rate_value_<?php echo $x; ?>" class="form-control" value="<?php echo $val['rate']; ?>" autocomplete="off">
													</td>
													<td>
														<input type="text" name="amount[]" id="amount_<?php echo $x; ?>" class="form-control" disabled value="<?php echo $val['amount']; ?>" autocomplete="off">
														<input type="hidden" name="amount_value[]" id="amount_value_<?php echo $x; ?>" class="form-control" value="<?php echo $val['amount']; ?>" autocomplete="off">
													</td>
													<td><button type="button" class="btn btn-danger btn-sm" onclick="removeRow('<?php echo $x; ?>')"><i class="fa fa-close"></i></button></td>
											 </tr>
											 <?php $x++; ?>
										 <?php endforeach; ?>
									 <?php endif; ?>
									 </tbody>
								</table>

								<br /> <br/>

								<div class="col-md-6 col-xs-12 pull pull-left">

									<div class="form-group">
										<label for="gross_amount" class="col-sm-5 control-label">Gross Amount</label>
										<div class="col-sm-7">
											<input type="text" class="form-control" id="gross_amount" name="gross_amount" disabled value="<?php echo isset($order_data['order']['gross_amount']) ? $order_data['order']['gross_amount'] : ''; ?>" autocomplete="off">
											<input type="hidden" class="form-control" id="gross_amount_value" name="gross_amount_value" value="<?php echo isset($order_data['order']['gross_amount']) ? $order_data['order']['gross_amount'] : ''; ?>" autocomplete="off">
										</div>
									</div>
									<?php if($is_service_enabled == true): ?>
									<div class="form-group">
										<label for="service_charge" class="col-sm-5 control-label">S-Charge <?php echo $company_data['service_charge_value']; ?> %</label>
										<div class="col-sm-7">
											<input type="text" class="form-control" id="service_charge" name="service_charge" disabled value="<?php echo isset($order_data['order']['service_charge']) ? $order_data['order']['service_charge'] : ''; ?>" autocomplete="off">
											<input type="hidden" class="form-control" id="service_charge_value" name="service_charge_value" value="<?php echo isset($order_data['order']['service_charge']) ? $order_data['order']['service_charge'] : ''; ?>" autocomplete="off">
										</div>
									</div>
									<?php endif; ?>
									<?php if($is_vat_enabled == true): ?>
									<div class="form-group">
										<label for="vat_charge" class="col-sm-5 control-label">Vat <?php echo $company_data['vat_charge_value']; ?> %</label>
										<div class="col-sm-7">
											<input type="text" class="form-control" id="vat_charge" name="vat_charge" disabled value="<?php echo $order_data['order']['vat_charge']; ?>" autocomplete="off">
											<input type="hidden" class="form-control" id="vat_charge_value" name="vat_charge_value" value="<?php echo $order_data['order']['vat_charge']; ?>" autocomplete="off">
										</div>
									</div>
									<?php endif; ?>
									<div class="form-group">
										<label for="discount" class="col-sm-5 control-label">Discount</label>
										<div class="col-sm-7">
											<input type="text" class="form-control" id="discount" name="discount" placeholder="Discount" onkeyup="subAmount()" value="<?php echo isset($order_data['order']['discount']) ? $order_data['order']['discount'] : ''; ?>" autocomplete="off">
										</div>
									</div>
									<div class="form-group">
										<label for="net_amount" class="col-sm-5 control-label">Net Amount</label>
										<div class="col-sm-7">
											<input type="text" class="form-control" id="net_amount" name="net_amount" disabled value="<?php echo isset($order_data['order']['net_amount']) ? $order_data['order']['net_amount'] : ''; ?>" autocomplete="off">
											<input type="hidden" class="form-control" id="net_amount_value" name="net_amount_value" value="<?php echo isset($order_data['order']['net_amount']) ? $order_data['order']['net_amount'] : ''; ?>" autocomplete="off">
										</div>
									</div>
								</div>
							</div>

							<div class="box-footer">
								<input type="hidden" name="service_charge_rate" value="<?php echo $company_data['service_charge_value']; ?>" autocomplete="off">
								<input type="hidden" name="vat_charge_rate" value="<?php echo $company_data['vat_charge_value']; ?>" autocomplete="off">

								<?php if(in_array('printOrder', $user_permission)): ?>
								<button type="button" class="btn bg-blue" onclick="openReceiptModal(<?php echo (int) ($order_data['order']['id'] ?? 0); ?>)">Print</button>
								<?php endif; ?>
								<button type="submit" class="btn btn-primary">Save Changes</button>
								<a href="<?php echo base_url('Controller_Orders'); ?>" class="btn btn-danger">Back</a>
							</div>
						</form>
				</div>
			</div>
		</div>
	</section>
</div>

		<style>
			.receipt-modal .modal-dialog { width: 560px; max-width: 96vw; margin: 10px auto; }
			.receipt-modal .modal-content { border-radius: 10px; }
			.receipt-modal .modal-header,
			.receipt-modal .modal-footer { padding: 8px 12px; }
			.receipt-modal .modal-body { padding: 0; overflow: hidden; }
			.receipt-modal #receiptFrame { width: 100%; border: 0; display: block; }
			@media (max-width: 640px) {
				.receipt-modal .modal-dialog { width: auto; margin: 8px; }
			}
		</style>
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

<script type="text/javascript">
	var base_url = "<?php echo base_url(); ?>";

	$(document).ready(function() {
		$(".select_group").select2();
		$("#mainOrdersNav").addClass('active');
		$("#manageOrdersNav").addClass('active');
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

	function removeRow(tr_id)
	{
		$("#product_info_table tbody tr#row_"+tr_id).remove();
		subAmount();
	}

	function subAmount()
	{
		var tableProductLength = $("#product_info_table tbody tr").length;
		var totalSubAmount = 0;
		for(x = 0; x < tableProductLength; x++) {
			var tr = $("#product_info_table tbody tr")[x];
			var count = $(tr).attr('id');
			count = count.substring(4);
			totalSubAmount = Number(totalSubAmount) + Number($("#amount_value_"+count).val());
		}

		totalSubAmount = totalSubAmount.toFixed(2);
		$("#gross_amount").val(totalSubAmount);
		$("#gross_amount_value").val(totalSubAmount);
		$("#net_amount").val(totalSubAmount);
		$("#net_amount_value").val(totalSubAmount);
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
				alert('Receipt link copied.');
			});
		} else {
			prompt('Copy receipt link:', url);
		}
	});
</script>
