<?php $session = session(); $validation = \Config\Services::validation(); ?>
<div class="content-wrapper">
  <section class="content-header">
    <h1>Update Members</h1>
    <ol class="breadcrumb">
      <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
      <li class="active">Members</li>
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

            <form role="form" action="<?php echo base_url('Controller_Members/edit/'.$user_data['id']); ?>" method="post">
              <?php echo csrf_field(); ?>
              <div class="box-body">

                <?php echo $validation->listErrors(); ?>
                <div class="row">
                <div class="col-md-6">
                <div class="form-group">
                  <label for="groups">Permission</label>
                  <select class="form-control" id="groups" name="groups">
                    <option value="">Select Permission</option>
                    <?php foreach ($group_data as $k => $v): ?>
                      <option value="<?php echo $v['id']; ?>" <?php if($user_group && $user_group['id'] == $v['id']) { echo 'selected'; } ?> ><?php echo $v['group_name']; ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="col-md-6">
                <div class="form-group">
                  <label for="warehouse_id">Assign to Warehouse</label>
                  <select class="form-control" id="warehouse_id" name="warehouse_id">
                    <option value="">-- Select Warehouse --</option>
                    <?php if(isset($warehouses) && !empty($warehouses)): ?>
                      <?php foreach($warehouses as $wh): ?>
                        <option value="<?php echo $wh['id']; ?>" <?php if(isset($user_data['warehouse_id']) && $user_data['warehouse_id'] == $wh['id']) echo 'selected'; ?>><?php echo htmlspecialchars($wh['name']); ?></option>
                      <?php endforeach; ?>
                    <?php endif; ?>
                  </select>
                </div>
                </div>
                </div>

                <div class="col-md-6">
                <div class="form-group">
                  <label for="fname">First name</label>
                  <input type="text" class="form-control" id="fname" name="fname" placeholder="First name" value="<?php echo $user_data['firstname']; ?>" autocomplete="off">
                </div>
                </div>

                 <div class="col-md-6">
                <div class="form-group">
                  <label for="username">Username</label>
                  <input type="text" class="form-control" id="username" name="username" placeholder="Username" value="<?php echo $user_data['username']; ?>" autocomplete="off">
                </div>
                </div>
                 <div class="col-md-6">
                <div class="form-group">
                  <label for="lname">Last name</label>
                  <input type="text" class="form-control" id="lname" name="lname" placeholder="Last name" value="<?php echo $user_data['lastname']; ?>" autocomplete="off">
                </div>
                </div>

                <div class="col-md-6">
                <div class="form-group">
                  <label for="password">Password</label>
                  <input type="password" class="form-control" id="password" name="password" placeholder="Password" autocomplete="off">
                </div>
                </div>

                 <div class="col-md-6">
                <div class="form-group">
                  <label for="email">Email</label>
                  <input type="email" class="form-control" id="email" name="email" placeholder="Email" value="<?php echo $user_data['email']; ?>" autocomplete="off">
                </div>
                </div>
                <div class="col-md-6">
                <div class="form-group">
                  <label for="cpassword">Confirm password</label>
                  <input type="password" class="form-control" id="cpassword" name="cpassword" placeholder="Confirm Password" autocomplete="off">
                </div>
                </div>

                <div class="col-md-6">
                <div class="form-group">
                  <label for="phone">Phone</label>
                  <input type="text" class="form-control" id="phone" name="phone" placeholder="Phone" value="<?php echo $user_data['phone']; ?>" autocomplete="off">
                </div>
                </div>

                <div class="form-group">
                  <label for="gender">Gender</label>
                  <div class="radio">
                    <label>
                      <input type="radio" name="gender" id="male" value="1" <?php if($user_data['gender'] == 1) echo 'checked'; ?>>
                      Male
                    </label>
                    <label>
                      <input type="radio" name="gender" id="female" value="2" <?php if($user_data['gender'] == 2) echo 'checked'; ?>>
                      Female
                    </label>
                  </div>
                </div>

              </div>

              <div class="box-footer">
                <button type="submit" class="btn btn-primary">Save & Close</button>
                <a href="<?php echo base_url('Controller_Members'); ?>" class="btn btn-warning">Back</a>
              </div>
            </form>
          </div>
          </div>
        </div>
      </div>
    </section>
