<?php
$session = session();
$validation = \Config\Services::validation();
$rawPermissions = !empty($group_data['permission']) ? @unserialize($group_data['permission']) : [];
$serialize_permission = is_array($rawPermissions) ? $rawPermissions : [];
?>
<div class="content-wrapper">
  <section class="content-header">
    <h1>Manage Permission</h1>
    <ol class="breadcrumb">
      <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
      <li><a href="<?php echo base_url('Controller_Permission'); ?>">Permission</a></li>
      <li class="active">Edit</li>
    </ol>
  </section>

  <section class="content">
    <div class="row">
      <div class="col-md-12 col-xs-12">

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
          <div class="box-header"></div>
          <form role="form" action="<?php echo base_url('Controller_Permission/edit/'.$group_data['id']); ?>" method="post">
            <?php echo csrf_field(); ?>
            <div class="box-body">

              <?php echo $validation->listErrors(); ?>

              <div class="form-group">
                <label for="group_name">Permission Name</label>
                <input type="text" class="form-control" id="group_name" name="group_name" placeholder="Enter permission name" value="<?php echo $group_data['group_name']; ?>">
              </div>
              <div class="form-group">
                <label for="permission">Permission</label>

                <table class="table table-responsive">
                  <thead>
                    <tr>
                      <th></th>
                      <th>Create</th>
                      <th>Update</th>
                      <th>View</th>
                      <th>Delete</th>
                      <th>Print</th>
                      <th>Return</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr>
                      <td>Members</td>
                      <td><input type="checkbox" name="permission[]" value="createUser" <?php echo in_array('createUser', $serialize_permission) ? 'checked' : ''; ?>></td>
                      <td><input type="checkbox" name="permission[]" value="updateUser" <?php echo in_array('updateUser', $serialize_permission) ? 'checked' : ''; ?>></td>
                      <td><input type="checkbox" name="permission[]" value="viewUser" <?php echo in_array('viewUser', $serialize_permission) ? 'checked' : ''; ?>></td>
                      <td><input type="checkbox" name="permission[]" value="deleteUser" <?php echo in_array('deleteUser', $serialize_permission) ? 'checked' : ''; ?>></td>
                      <td> - </td>
                      <td> - </td>
                    </tr>
                    <tr>
                      <td>Permission</td>
                      <td><input type="checkbox" name="permission[]" value="createGroup" <?php echo in_array('createGroup', $serialize_permission) ? 'checked' : ''; ?>></td>
                      <td><input type="checkbox" name="permission[]" value="updateGroup" <?php echo in_array('updateGroup', $serialize_permission) ? 'checked' : ''; ?>></td>
                      <td><input type="checkbox" name="permission[]" value="viewGroup" <?php echo in_array('viewGroup', $serialize_permission) ? 'checked' : ''; ?>></td>
                      <td><input type="checkbox" name="permission[]" value="deleteGroup" <?php echo in_array('deleteGroup', $serialize_permission) ? 'checked' : ''; ?>></td>
                      <td> - </td>
                      <td> - </td>
                    </tr>
                    <tr>
                      <td>Warehouse</td>
                      <td><input type="checkbox" name="permission[]" value="createStore" <?php echo in_array('createStore', $serialize_permission) ? 'checked' : ''; ?>></td>
                      <td><input type="checkbox" name="permission[]" value="updateStore" <?php echo in_array('updateStore', $serialize_permission) ? 'checked' : ''; ?>></td>
                      <td><input type="checkbox" name="permission[]" value="viewStore" <?php echo in_array('viewStore', $serialize_permission) ? 'checked' : ''; ?>></td>
                      <td><input type="checkbox" name="permission[]" value="deleteStore" <?php echo in_array('deleteStore', $serialize_permission) ? 'checked' : ''; ?>></td>
                      <td> - </td>
                      <td> - </td>
                    </tr>
                    <tr>
                      <td>Products</td>
                      <td><input type="checkbox" name="permission[]" value="createProduct" <?php echo in_array('createProduct', $serialize_permission) ? 'checked' : ''; ?>></td>
                      <td><input type="checkbox" name="permission[]" value="updateProduct" <?php echo in_array('updateProduct', $serialize_permission) ? 'checked' : ''; ?>></td>
                      <td><input type="checkbox" name="permission[]" value="viewProduct" <?php echo in_array('viewProduct', $serialize_permission) ? 'checked' : ''; ?>></td>
                      <td><input type="checkbox" name="permission[]" value="deleteProduct" <?php echo in_array('deleteProduct', $serialize_permission) ? 'checked' : ''; ?>></td>
                      <td><input type="checkbox" name="permission[]" value="printProduct" <?php echo in_array('printProduct', $serialize_permission) ? 'checked' : ''; ?>></td>
                      <td> - </td>
                    </tr>
                    <tr>
                      <td>Orders</td>
                      <td><input type="checkbox" name="permission[]" value="createOrder" <?php echo in_array('createOrder', $serialize_permission) ? 'checked' : ''; ?>></td>
                      <td><input type="checkbox" name="permission[]" value="updateOrder" <?php echo in_array('updateOrder', $serialize_permission) ? 'checked' : ''; ?>></td>
                      <td><input type="checkbox" name="permission[]" value="viewOrder" <?php echo in_array('viewOrder', $serialize_permission) ? 'checked' : ''; ?>></td>
                      <td><input type="checkbox" name="permission[]" value="deleteOrder" <?php echo in_array('deleteOrder', $serialize_permission) ? 'checked' : ''; ?>></td>
                      <td><input type="checkbox" name="permission[]" value="printOrder" <?php echo in_array('printOrder', $serialize_permission) ? 'checked' : ''; ?>></td>
                      <td><input type="checkbox" name="permission[]" value="returnOrder" <?php echo in_array('returnOrder', $serialize_permission) ? 'checked' : ''; ?>></td>
                    </tr>
                    <tr>
                      <td>Company</td>
                      <td> - </td>
                      <td><input type="checkbox" name="permission[]" value="updateCompany" <?php echo in_array('updateCompany', $serialize_permission) ? 'checked' : ''; ?>></td>
                      <td> - </td>
                      <td> - </td>
                      <td> - </td>
                      <td> - </td>
                    </tr>
                  </tbody>
                </table>

              </div>
            </div>

            <div class="box-footer">
              <button type="submit" class="btn btn-primary">Save & Close</button>
              <a href="<?php echo base_url('Controller_Permission'); ?>" class="btn btn-warning">Back</a>
            </div>
          </form>
        </div>
      </div>
    </div>
  </section>
</div>
