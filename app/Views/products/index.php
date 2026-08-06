<?php $session = session(); ?>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script>var $j = jQuery.noConflict(true);</script>
<link rel="stylesheet" type="text/css" href="//cdn.datatables.net/1.10.16/css/jquery.dataTables.min.css">

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

				<?php if($session->getFlashdata('success')): ?>
					<div class="alert alert-success alert-dismissible" role="alert">
						<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						<?php echo $session->getFlashdata('success'); ?>
					</div>
				<?php elseif($session->getFlashdata('error')): ?>
					<div class="alert alert-danger alert-dismissible">
						<button type="button" class="close" data-dismiss="alert">&times;</button>
						<?php echo $session->getFlashdata('error'); ?>
					</div>
				<?php endif; ?>

				<?php if(in_array('createProduct', $user_permission)): ?>
					<a href="<?php echo base_url('Controller_Products/create'); ?>" class="btn btn-primary">Add Product</a>
				<?php endif; ?>
				<?php if(in_array('deleteProduct', $user_permission)): ?>
					<button type="button" id="bulkDeleteBtn" class="btn btn-danger" style="display:none;" data-toggle="modal" data-target="#bulkRemoveModal">
						<i class="fa fa-trash"></i> Delete Selected (<span id="selectedCount">0</span>)
					</button>
				<?php endif; ?>

				<?= view('partials/tables/products-table', [
					'warehouses' => $warehouses,
					'user_permission' => $user_permission,
				]) ?>

			</div>
		</div>
	</section>
</div>

<?php if(in_array('deleteProduct', $user_permission)): ?>
<div class="modal fade" tabindex="-1" role="dialog" id="removeModal">
	<div class="modal-dialog modal-sm" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">&times;</button>
				<h4 class="modal-title">Remove Product</h4>
			</div>
			<form id="removeForm" action="<?php echo base_url('Controller_Products/remove'); ?>" method="post">
				<?php echo csrf_field(); ?>
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

<?php if(in_array('printProduct', $user_permission)): ?>
<div class="modal fade" id="productReceiptModal" tabindex="-1" role="dialog" aria-labelledby="productReceiptModalLabel">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="productReceiptModalLabel">Product Receipt</h4>
			</div>
			<div class="modal-body" style="padding:0;">
				<iframe id="productReceiptFrame" title="Product Receipt" style="width:100%; height:70vh; border:0;"></iframe>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" id="productReceiptShareBtn">Share</button>
				<button type="button" class="btn btn-primary" id="productReceiptPrintBtn">Print</button>
				<a href="#" class="btn btn-success" id="productReceiptSaveBtn" download>Save</a>
				<button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>
<?php endif; ?>

<script>
$(document).ready(function() {
	$("#mainProductNav").addClass('active');

	var hasCheckbox = <?php echo in_array('deleteProduct', $user_permission) ? 'true' : 'false'; ?>;
	var colOffset = hasCheckbox ? 1 : 0;

	var manageTable = $('#manageTable').DataTable({
		dom: 'frtip',
		responsive: true,
		columnDefs: [
			{ targets: 0 + colOffset, width: '260px', render: function(data, type, row) {
					try {
						var name = data || '';
						var added = row.date_added || row.created_at || '';
						var addedDate = added ? new Date(added) : null;
						var today = new Date();
						var diffDays = 0;
						if (addedDate && !isNaN(addedDate.getTime())) {
							diffDays = Math.floor((today - addedDate)/(1000*60*60*24));
						}
						var daysRemaining = 15 - diffDays;

						var color = '#00b606'; // green
						var icon = 'fa-circle';
						var label = daysRemaining > 0 ? daysRemaining : '0';
						var tooltipLabel = '';

						if (diffDays >= 15) {
							color = '#e40c05'; // red
							icon = 'fa-exclamation-triangle';
							label = 'Aged';
							tooltipLabel = '<strong>Aged Stock</strong> &nbsp;<i class="fa fa-exclamation-triangle" style="color:#fff"></i>';
						} else if (daysRemaining >= 11) {
							color = '#0abb0f'; // green
						} else if (daysRemaining >= 6) {
							color = '#ffe600'; // yellow
						} else if (daysRemaining >= 1) {
							color = '#f19304'; // orange
						} else {
							color = '#e40800'; // red
						}

						var displayLabel = (diffDays >= 15) ? 'Aged Stock' : (daysRemaining > 0 ? daysRemaining + 'd' : '0d');

						var formattedDate = '';
						var formattedTime = '';
						if (addedDate && !isNaN(addedDate.getTime())) {
							var y = addedDate.getFullYear();
							var m = ('0' + (addedDate.getMonth()+1)).slice(-2);
							var d = ('0' + addedDate.getDate()).slice(-2);
							var hh = ('0' + addedDate.getHours()).slice(-2);
							var mm = ('0' + addedDate.getMinutes()).slice(-2);
							formattedDate = y + '-' + m + '-' + d;
							formattedTime = hh + ':' + mm;
						}

						var tooltipHtml = '';
						tooltipHtml += '<div style="text-align:left;">';
						tooltipHtml += '<div><strong>Date Added:</strong> ' + (formattedDate || 'N/A') + '</div>';
						tooltipHtml += '<div><strong>Time Added:</strong> ' + (formattedTime || 'N/A') + '</div>';
						tooltipHtml += '<div><strong>Days in Stock:</strong> ' + diffDays + '</div>';
						tooltipHtml += '<div><strong>Days Remaining:</strong> ' + (diffDays >= 15 ? 0 : daysRemaining) + '</div>';
						tooltipHtml += '<div><strong>Inventory Threshold:</strong> 15 Days</div>';
						tooltipHtml += '</div>';

						var indicator = '\n                            <span class="stock-indicator" data-toggle="tooltip" data-html="true" title="' + tooltipHtml.replace(/"/g, '&quot;') + '" style="display:inline-block;margin-left:8px;vertical-align:middle;">'
							+ '<i class="fa ' + (diffDays >= 15 ? 'fa-exclamation-triangle' : 'fa-circle') + '" style="color:' + color + ';font-size:12px;margin-right:4px;vertical-align:middle;"></i>'
							+ '<span style="display:inline-block;background:' + color + ';color:#fff;border-radius:10px;padding:2px 6px;font-size:11px;vertical-align:middle;">' + (diffDays >= 15 ? 'Aged' : (daysRemaining > 0 ? daysRemaining : 0)) + '</span>'
							+ '</span>';

						return '<span class="truncate" title="'+name+'">'+name+'</span>' + indicator;
					} catch (e) {
						return '<span class="truncate" title="'+data+'">'+data+'</span>';
					}
				}
			},
			{ targets: 1 + colOffset, render: function(data) {
					return '<span class="truncate" title="'+data+'">'+data+'</span>';
				}
			},
				// Hide the Price column to free up space (keeps indexing intact)
				{ targets: 2 + colOffset, visible: false },
			{ targets: 3 + colOffset, render: function(data) {
					return '<span class="truncate" title="'+data+'">'+data+'</span>';
				}
			},
			{ targets: 4 + colOffset, visible: false },
			{ targets: 5 + colOffset, visible: false, render: function(data) {
					var addedDate = new Date(data);
					var today = new Date();
					var diffDays = Math.floor((today - addedDate)/(1000*60*60*24));
					return diffDays >= 15 ? 'Aged' : 'Fresh';
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
			{ data: 'name' },
			{ data: 'imei' },
			{ data: 'price' },
			{ data: 'warehouse' },
			{ data: 'availability' },
			{ data: 'date_added' },
			{ data: 'actions', orderable: false, searchable: false }
		]
	});

	$('#filterWarehouse').on('change', function(){ manageTable.column(3 + colOffset).search(this.value).draw(); });
	$('#filterAvailability').on('change', function(){ manageTable.column(4 + colOffset).search(this.value).draw(); });
	$('#filterStockAge').on('change', function(){ manageTable.column(5 + colOffset).search(this.value).draw(); });

	manageTable.on('draw', function(){
		var totalRows = manageTable.rows().data().length;
		var outOfStockRows = manageTable.rows({ search: 'applied' }).data().toArray().filter(function(r){ return r.availability === 'Out of Stock'; }).length;
		if(totalRows > 0 && outOfStockRows === totalRows) {
			$('#outOfStockMsg').show();
		} else {
			$('#outOfStockMsg').hide();
		}
	});

	if(hasCheckbox) {
		$('#selectAll').on('change', function(){
			var checked = this.checked;
			$('#manageTable tbody input.row-check').each(function(){ this.checked = checked; });
			updateBulkBtn();
		});

		// initialize tooltips after each draw (Bootstrap tooltip assumed available)
		manageTable.on('draw', function(){
			$('[data-toggle="tooltip"]').tooltip({container: 'body'});
		});

		// move DataTables built-in filter into our header row placeholder
		var dtFilter = $('#manageTable_filter');
		if (dtFilter.length) {
			dtFilter.appendTo('#filterSearchWrap');
			dtFilter.find('input').addClass('form-control input-sm');
			dtFilter.css({'margin-bottom':'0'});
		}

		$('#manageTable tbody').on('change', 'input.row-check', function(){ updateBulkBtn(); });

		function updateBulkBtn() {
			var count = $('#manageTable tbody input.row-check:checked').length;
			$('#selectedCount').text(count);
			$('.bulk-count').text(count);
			if(count > 0) { $('#bulkDeleteBtn').show(); } else { $('#bulkDeleteBtn').hide(); }
		}

		$('#confirmBulkDelete').on('click', function(){
			var ids = [];
			$('#manageTable tbody input.row-check:checked').each(function(){ ids.push($(this).val()); });
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
					if(typeof showToast === 'function') {
						showToast(response.messages, response.success ? 'success' : 'error');
					}
				}
			});
		});
	}
});

function removeFunc(id) {
	if (id) {
		$('#product_id').val(id);
	}
}

function printProduct(id) {
	if (!id) return;
	if (typeof showToast === 'function') {
		showToast('Opening product receipt...', 'info');
	}
	window.open('<?php echo base_url("Controller_Products/printProduct/"); ?>' + id, '_blank');
}

function openProductReceiptModal(id) {
	if (!id) return;
	var receiptUrl = '<?php echo base_url("Controller_Products/productReceipt/"); ?>' + id;
	$('#productReceiptFrame').attr('src', receiptUrl);
	$('#productReceiptSaveBtn').attr('href', receiptUrl);
	$('#productReceiptShareBtn').data('share-url', receiptUrl);
	$('#productReceiptModal').modal('show');
}

$('#productReceiptPrintBtn').on('click', function() {
	var frame = document.getElementById('productReceiptFrame');
	if (frame && frame.contentWindow) {
		frame.contentWindow.focus();
		frame.contentWindow.print();
	}
});

$('#productReceiptShareBtn').on('click', function() {
	var url = $(this).data('share-url') || '';
	if (!url) return;

	if (navigator.share) {
		navigator.share({
			title: 'Product Receipt',
			text: 'Product receipt',
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
