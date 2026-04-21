<?php $session = session(); $request = service('request'); ?>
<div class="content-wrapper">
	<section class="content-header">
		<h1>Manage Orders</h1>
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

				<?php if(in_array('createOrder', $user_permission)): ?>
					<a href="<?php echo base_url('Controller_Orders/create'); ?>" class="btn btn-primary">Add Order</a>
				<?php endif; ?>

				<?php if(in_array('deleteOrder', $user_permission)): ?>
					<button type="button" class="btn btn-danger" id="bulkDeleteBtn" style="display:none;margin-left:5px;" data-toggle="modal" data-target="#bulkRemoveModal">
						<i class="fa fa-trash"></i> Delete Selected (<span id="selectedCount">0</span>)
					</button>
				<?php endif; ?>
				<br /> <br />

				<div class="box">
					<div class="box-body">
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
								<?php if(in_array('updateOrder', $user_permission) || in_array('viewOrder', $user_permission) || in_array('deleteOrder', $user_permission) || in_array('printOrder', $user_permission) || in_array('returnOrder', $user_permission)): ?>
									<th>Actions</th>
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

	<div class="modal fade" id="receiptModal" tabindex="-1" role="dialog" aria-labelledby="receiptModalLabel">
		<div class="modal-dialog modal-lg" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title" id="receiptModalLabel">Receipt</h4>
				</div>
				<div class="modal-body" style="padding:0;">
					<iframe id="receiptFrame" title="Receipt" style="width:100%; height:70vh; border:0;"></iframe>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" id="receiptShareBtn">Share</button>
					<a href="#" class="btn btn-success" id="receiptDownloadBtn" target="_blank" rel="noopener">Download PDF</a>
					<button type="button" class="btn btn-primary" id="receiptPrintBtn">Print</button>
					<button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
				</div>
			</div>
		</div>
	</div>

<?php if(in_array('deleteOrder', $user_permission)): ?>
<div class="modal fade" tabindex="-1" role="dialog" id="removeModal">
	<div class="modal-dialog modal-sm" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title">Remove Order</h4>
			</div>

			<form role="form" action="<?php echo base_url('Controller_Orders/remove'); ?>" method="post" id="removeForm">
				<?php echo csrf_field(); ?>
				<div class="modal-body">
					<p>Do you really want to remove?</p>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
					<button type="submit" class="btn btn-danger">Delete</button>
				</div>
			</form>
		</div>
	</div>
</div>
<?php endif; ?>

<?php if(in_array('deleteOrder', $user_permission)): ?>
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

<?php if(in_array('returnOrder', $user_permission)): ?>
<div class="modal fade" tabindex="-1" role="dialog" id="returnModal">
	<div class="modal-dialog modal-sm" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title">Return to Stock</h4>
			</div>
			<div class="modal-body">
				<p>Return items from this order back to stock?</p>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
				<button type="button" class="btn btn-success" id="confirmReturn">Return</button>
			</div>
		</div>
	</div>
</div>
<?php endif; ?>

<script type="text/javascript">
var manageTable;
var base_url = "<?php echo base_url(); ?>";
var hasDeletePerm = <?php echo in_array('deleteOrder', $user_permission) ? 'true' : 'false'; ?>;
var hasReturnPerm = <?php echo in_array('returnOrder', $user_permission) ? 'true' : 'false'; ?>;
var autoPrintId = <?php echo json_encode($request->getGet('print')); ?>;
var returnOrderId = null;

$(document).ready(function() {

	$("#mainOrdersNav").addClass('active');
	$("#manageOrdersNav").addClass('active');

	var colOffset = hasDeletePerm ? 1 : 0;

	manageTable = $('#manageTable').DataTable({
		dom: 'frtip',
		responsive: true,
		'ajax': base_url + 'Controller_Orders/fetchOrdersData',
		'order': [],
		'columnDefs': hasDeletePerm ? [
			{ 'orderable': false, 'targets': [0, 4 + colOffset - 1] }
		] : []
	});

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
				if(typeof showToast === 'function') {
					showToast(response.messages, response.success ? 'success' : 'error');
				}
			}
		});
	});

	if (autoPrintId) {
		openReceiptModal(autoPrintId);
	}
});

if (hasReturnPerm) {
	$('#confirmReturn').on('click', function() {
		if (!returnOrderId) return;
		$.ajax({
			url: base_url + 'Controller_Orders/returnToStock',
			type: 'POST',
			data: { order_id: returnOrderId },
			dataType: 'json',
			success: function(response) {
				manageTable.ajax.reload(null, false);
				$('#returnModal').modal('hide');
				returnOrderId = null;
				if(typeof showToast === 'function') {
					showToast(response.messages, response.success ? 'success' : 'error');
				}
			}
		});
	});
}

function removeFunc(id) {
	if(id) {
		$('#removeForm').append('<input type="hidden" name="order_id" value="'+id+'">');
	}
}

function returnFunc(id) {
	if (id) {
		returnOrderId = id;
	}
}

function openReceiptModal(orderId) {
	if (!orderId) return;

	var receiptUrl = base_url + 'Controller_Orders/printDiv/' + orderId + '?embed=1&auto=1';
	var pdfUrl = base_url + 'Controller_Orders/downloadPDF/' + orderId;

	$('#receiptFrame').attr('src', receiptUrl);
	$('#receiptDownloadBtn').attr('href', pdfUrl);
	$('#receiptShareBtn').data('share-url', pdfUrl);
	$('#receiptModal').modal('show');
}

$('#receiptPrintBtn').on('click', function() {
	var frame = document.getElementById('receiptFrame');
	if (frame && frame.contentWindow) {
		frame.contentWindow.focus();
		frame.contentWindow.print();
	}
});

$('#receiptShareBtn').on('click', function() {
	var url = $(this).data('share-url') || '';
	if (!url) return;

	if (navigator.share) {
		navigator.share({
			title: 'Receipt',
			text: 'Receipt PDF',
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
