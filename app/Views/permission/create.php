<?php $session = session(); $validation = \Config\Services::validation(); ?>
<div class="content-wrapper">
  <section class="content-header">
    <h1>User Permission</h1>
    <ol class="breadcrumb">
      <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
      <li class="active">Permission</li>
    </ol>
  </section>
<section class="content">

      <div class="box box-default">
        <div class="box-header with-border">
          <div class="box-tools pull-right">
            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
            <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-remove"></i></button>
          </div>
        </div>
        <div class="box-body">
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

            <form role="form" action="<?php echo base_url('Controller_Permission/create'); ?>" method="post">
              <?php echo csrf_field(); ?>
              <div class="box-body">

                <?php echo $validation->listErrors(); ?>

                <div class="form-group">
                  <label for="group_name">Permission Name</label>
                  <input type="text" class="form-control" id="group_name" name="group_name" placeholder="Enter group name">
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
                        <td><input type="checkbox" name="permission[]" value="createUser" class="minimal"></td>
                        <td><input type="checkbox" name="permission[]" value="updateUser" class="minimal"></td>
                        <td><input type="checkbox" name="permission[]" value="viewUser" class="minimal"></td>
                        <td><input type="checkbox" name="permission[]" value="deleteUser" class="minimal"></td>
                        <td> - </td>
                        <td> - </td>
                      </tr>
                      <tr>
                        <td>Permission</td>
                        <td><input type="checkbox" name="permission[]" value="createGroup" class="minimal"></td>
                        <td><input type="checkbox" name="permission[]" value="updateGroup" class="minimal"></td>
                        <td><input type="checkbox" name="permission[]" value="viewGroup" class="minimal"></td>
                        <td><input type="checkbox" name="permission[]" value="deleteGroup" class="minimal"></td>
                        <td> - </td>
                        <td> - </td>
                      </tr>
                      <tr>
                        <td>Warehouse</td>
                        <td><input type="checkbox" name="permission[]" value="createStore" class="minimal"></td>
                        <td><input type="checkbox" name="permission[]" value="updateStore" class="minimal"></td>
                        <td><input type="checkbox" name="permission[]" value="viewStore" class="minimal"></td>
                        <td><input type="checkbox" name="permission[]" value="deleteStore" class="minimal"></td>
                        <td> - </td>
                        <td> - </td>
                      </tr>
                      <tr>
                        <td>Products</td>
                        <td><input type="checkbox" name="permission[]" value="createProduct" class="minimal"></td>
                        <td><input type="checkbox" name="permission[]" value="updateProduct" class="minimal"></td>
                        <td><input type="checkbox" name="permission[]" value="viewProduct" class="minimal"></td>
                        <td><input type="checkbox" name="permission[]" value="deleteProduct" class="minimal"></td>
                        <td><input type="checkbox" name="permission[]" value="printProduct" class="minimal"></td>
                        <td> - </td>
                      </tr>
                      <tr>
                        <td>Orders</td>
                        <td><input type="checkbox" name="permission[]" value="createOrder" class="minimal"></td>
                        <td><input type="checkbox" name="permission[]" value="updateOrder" class="minimal"></td>
                        <td><input type="checkbox" name="permission[]" value="viewOrder" class="minimal"></td>
                        <td><input type="checkbox" name="permission[]" value="deleteOrder" class="minimal"></td>
                        <td><input type="checkbox" name="permission[]" value="printOrder" class="minimal"></td>
                        <td><input type="checkbox" name="permission[]" value="returnOrder" class="minimal"></td>
                      </tr>
                      <tr>
                        <td>Company</td>
                        <td> - </td>
                        <td><input type="checkbox" name="permission[]" value="updateCompany" class="minimal"></td>
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
      </div>
    </section>
