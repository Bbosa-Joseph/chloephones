<?php $session = session(); ?>
<div class="content-wrapper">
	<section class="content-header">
		<h1>Manage Warehouse</h1>
		<ol class="breadcrumb">
			<li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
			<li class="active">Warehouse</li>
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

				<?php if(in_array('createStore', $user_permission)): ?>
					<button class="btn btn-primary" data-toggle="modal" data-target="#addModal">Add Warehouse</button>
					<br /> <br />
				<?php endif; ?>

				<div class="box">
					<div class="box-body">
						<div class="table-responsive">
						<table id="manageTable" class="table table-bordered table-hover table-striped">
							<thead>
								<tr>
									<th>Warehouse</th>
									<th>Assigned User</th>
									<th>Total Stock</th>
									<th>Total Value</th>
									<th>Status</th>
									<?php if(in_array('updateStore', $user_permission) || in_array('deleteStore', $user_permission)): ?>
										<th>Action</th>
									<?php endif; ?>
								</tr>
							</thead>

						</table>
						</div>
					</div>
				</div>
			</div>
		</div>
	</section>
</div>

<?php if(in_array('createStore', $user_permission)): ?>
<div class="modal fade" tabindex="-1" role="dialog" id="addModal">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title">Add Warehouse</h4>
			</div>

			<form role="form" action="<?php echo base_url('Controller_Warehouse/create'); ?>" method="post" id="createForm">
				<?php echo csrf_field(); ?>

				<div class="modal-body">

					<div class="form-group">
						<label for="brand_name">Warehouse Name</label>
						<input type="text" class="form-control" id="store_name" name="store_name" placeholder="Enter warehouse name" autocomplete="off">
					</div>
					<div class="form-group">
						<label for="assigned_user_id">Assign to User</label>
						<select class="form-control" id="assigned_user_id" name="assigned_user_id">
							<option value="">-- Select User --</option>
							<?php if(isset($users) && !empty($users)): ?>
								<?php foreach($users as $user): ?>
									<option value="<?php echo $user['id']; ?>"><?php echo htmlspecialchars($user['username'] ?? $user['email']); ?></option>
								<?php endforeach; ?>
							<?php endif; ?>
						</select>
					</div>
					<div class="form-group">
						<label for="active">Status</label>
						<select class="form-control" id="active" name="active">
							<option value="1">Active</option>
							<option value="2">Inactive</option>
						</select>
					</div>
				</div>

				<div class="modal-footer">
					<button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
					<button type="submit" class="btn btn-primary">Save changes</button>
				</div>

			</form>


		</div>
	</div>
</div>
<?php endif; ?>

<?php if(in_array('updateStore', $user_permission)): ?>
<div class="modal fade" tabindex="-1" role="dialog" id="editModal">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title">Edit Warehouse</h4>
			</div>

			<form role="form" action="<?php echo base_url('Controller_Warehouse/update'); ?>" method="post" id="updateForm">
				<?php echo csrf_field(); ?>

				<div class="modal-body">
					<div id="messages"></div>

					<div class="form-group">
						<label for="edit_brand_name">Warehouse Name</label>
						<input type="text" class="form-control" id="edit_store_name" name="edit_store_name" placeholder="Enter warehouse name" autocomplete="off">
					</div>
					<div class="form-group">
						<label for="edit_assigned_user_id">Assign to User</label>
						<select class="form-control" id="edit_assigned_user_id" name="edit_assigned_user_id">
							<option value="">-- Select User --</option>
							<?php if(isset($users) && !empty($users)): ?>
								<?php foreach($users as $user): ?>
									<option value="<?php echo $user['id']; ?>"><?php echo htmlspecialchars($user['username'] ?? $user['email']); ?></option>
								<?php endforeach; ?>
							<?php endif; ?>
						</select>
					</div>
					<div class="form-group">
						<label for="edit_active">Status</label>
						<select class="form-control" id="edit_active" name="edit_active">
							<option value="1">Active</option>
							<option value="2">Inactive</option>
						</select>
					</div>
				</div>

				<div class="modal-footer">
					<button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
					<button type="submit" class="btn btn-primary">Save changes</button>
				</div>

			</form>


		</div>
	</div>
</div>
<?php endif; ?>

<?php if(in_array('deleteStore', $user_permission)): ?>
<div class="modal fade" tabindex="-1" role="dialog" id="removeModal">
	<div class="modal-dialog modal-sm" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title">Remove Warehouse</h4>
			</div>
			<form role="form" action="<?php echo base_url('Controller_Warehouse/remove'); ?>" method="post" id="removeForm">
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

<script type="text/javascript">
var manageTable;
var base_url = "<?php echo base_url(); ?>";

$(document).ready(function() {

	$("#storeNav").addClass('active');

	manageTable = $('#manageTable').DataTable({
		'ajax': base_url + 'Controller_Warehouse/fetchStoresData',
		'order': []
	});

	$('#createForm').unbind('submit').on('submit', function() {
		var form = $(this);
		$.ajax({
			url: form.attr('action'),
			type: form.attr('method'),
			data: form.serialize(),
			dataType: 'json',
			success:function(response) {
				manageTable.ajax.reload(null, false);
				if(response.success) {
					$('#addModal').modal('hide');
					$('#createForm')[0].reset();
				}
				if(typeof showToast === 'function') {
					showToast(response.messages, response.success ? 'success' : 'error');
				}
			}
		});
		return false;
	});
});

function editFunc(id)
{
	if(id) {
		$.ajax({
			url: base_url + 'Controller_Warehouse/fetchStoresDataById/' + id,
			type: 'get',
			dataType: 'json',
			success:function(response) {
				$('#edit_store_name').val(response.name);
				$('#edit_active').val(response.active);
				$('#edit_assigned_user_id').val(response.assigned_user_id);

				$('#updateForm').unbind('submit').on('submit', function() {
					var form = $(this);
					$.ajax({
						url: form.attr('action') + '/' + id,
						type: form.attr('method'),
						data: form.serialize(),
						dataType: 'json',
						success:function(response) {
							manageTable.ajax.reload(null, false);
							if(response.success) {
								$('#editModal').modal('hide');
							}
							if(typeof showToast === 'function') {
								showToast(response.messages, response.success ? 'success' : 'error');
							}
						}
					});

					return false;
				});
			}
		});
	}
}

function removeFunc(id)
{
	if(id) {
		$('#removeForm').on('submit', function() {
			var form = $(this);
			$.ajax({
				url: form.attr('action'),
				type: form.attr('method'),
				data: form.serialize() + '&store_id=' + id,
				dataType: 'json',
				success:function(response) {
					manageTable.ajax.reload(null, false);
					$('#removeModal').modal('hide');
					if(typeof showToast === 'function') {
						showToast(response.messages, response.success ? 'success' : 'error');
					}
				}
			});
			return false;
		});
	}
}
</script>
