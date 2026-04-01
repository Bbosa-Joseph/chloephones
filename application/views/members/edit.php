 
 
 <style>
    /* GENERAL LAYOUT */
    .content-wrapper {
      background: #f4f6f9;
      padding: 20px 30px;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    .content-header h1 {
      font-weight: 600;
      font-size: 28px;
      color: #111827;
    }

    .breadcrumb {
      background: transparent;
      margin-bottom: 20px;
      padding: 0;
    }

    /* ALERTS */
    .alert {
      border-radius: 8px;
      padding: 12px 20px;
      font-size: 14px;
    }

    /* BOX */
    .box {
      background: #ffffff;
      border-radius: 12px;
      box-shadow: 0 6px 20px rgba(0,0,0,0.05);
      border: none;
      padding: 20px;
      margin-bottom: 20px;
    }

    /* BOX HEADER */
    .box-header .box-tools .btn {
      border-radius: 8px;
      font-size: 14px;
    }

    /* FORM GROUP */
    .form-group {
      margin-bottom: 15px;
    }

    .form-group label {
      font-weight: 500;
      color: #374151;
    }

    /* INPUTS */
    input.form-control, select.form-control {
      border-radius: 8px;
      border: 1px solid #d1d5db;
      padding: 10px 12px;
      font-size: 14px;
      transition: border 0.2s ease, box-shadow 0.2s ease;
    }

    input.form-control:focus, select.form-control:focus {
      border-color: #3b82f6;
      box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2);
    }

    /* RADIO BUTTONS */
    .radio label {
      margin-right: 15px;
      font-weight: 500;
      color: #374151;
    }

    /* BUTTONS */
    .btn-primary, .btn-warning {
      border-radius: 8px;
      font-size: 14px;
      padding: 8px 16px;
      transition: all 0.2s ease;
    }

    .btn-primary:hover { background-color: #2563eb; }
    .btn-warning:hover { background-color: #d97706; }

    /* RESPONSIVE */
    @media (max-width: 768px){
      .form-group label, input.form-control, select.form-control {
        font-size: 13px;
      }

      .btn {
        padding: 6px 12px;
        font-size: 13px;
      }
    }
</style>
 <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        Update Members
        
      </h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Members</li>
      </ol>
    </section>
<section class="content">

      <!-- SELECT2 EXAMPLE -->
      <div class="box box-default">
        <div class="box-header with-border">
    

          <div class="box-tools pull-right">
            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
            <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-remove"></i></button>
          </div>
        </div>
        <!-- /.box-header -->
 
          <div class="row">
        <div class="col-md-12 col-xs-12">
          
          <?php if($this->session->flashdata('success')): ?>
            <div class="alert alert-success alert-dismissible" role="alert">
              <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
              <?php echo $this->session->flashdata('success'); ?>
            </div>
          <?php elseif($this->session->flashdata('error')): ?>
            <div class="alert alert-error alert-dismissible" role="alert">
              <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
              <?php echo $this->session->flashdata('error'); ?>
            </div>
          <?php endif; ?>

        
            
            <form role="form" action="<?php base_url('Controller_Members/create') ?>" method="post">
              <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">
              <div class="box-body">

                <?php echo validation_errors(); ?>
                <div class="row">
                <div class="col-md-6">
                <div class="form-group">
                  <label for="groups">Permission</label>
                  <select class="form-control" id="groups" name="groups">
                    <option value="">Select Permission</option>
                    <?php foreach ($group_data as $k => $v): ?>
                      <option value="<?php echo $v['id'] ?>" <?php if($user_group['id'] == $v['id']) { echo 'selected'; } ?> ><?php echo $v['group_name'] ?></option> 
                    <?php endforeach ?>
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
                  <input type="text" class="form-control" id="fname" name="fname" placeholder="First name" value="<?php echo $user_data['firstname'] ?>" autocomplete="off">
                </div>
                </div>

                 <div class="col-md-6">
                <div class="form-group">
                  <label for="username">Username</label>
                  <input type="text" class="form-control" id="username" name="username" placeholder="Username" value="<?php echo $user_data['username'] ?>" autocomplete="off">
                </div>
                </div>
                 <div class="col-md-6">
                <div class="form-group">
                  <label for="lname">Last name</label>
                  <input type="text" class="form-control" id="lname" name="lname" placeholder="Last name" value="<?php echo $user_data['lastname'] ?>" autocomplete="off">
                </div>
                </div>

                <div class="col-md-6">

                <div class="form-group">
                  <label for="password">Password</label>
                  <input type="password" class="form-control" id="password" name="password" placeholder="Password"  autocomplete="off">
                </div>
                </div>
                <div class="col-md-6">
                <div class="form-group">
                  <label for="email">Email</label>
                  <input type="email" class="form-control" id="email" name="email" placeholder="Email" value="<?php echo $user_data['email'] ?>" autocomplete="off">
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
                  <input type="text" class="form-control" id="phone" name="phone" placeholder="Phone" value="<?php echo $user_data['phone'] ?>" autocomplete="off">
                </div>
                </div>
                </div>

                <div class="form-group">
                  <label for="gender">Gender</label>
                  <div class="radio">
                    <label>
                      <input type="radio" name="gender" id="male" value="1" <?php if($user_data['gender'] == 1) {
                        echo "checked";
                      } ?>>
                      Male
                    </label>
                    <label>
                      <input type="radio" name="gender" id="female" value="2" <?php if($user_data['gender'] == 2) {
                        echo "checked";
                      } ?>>
                      Female
                    </label>
                  </div>
                
                
                  
                </div>

              </div>
              <!-- /.box-body -->

              <div class="box-footer">
                <button type="submit" class="btn btn-primary">Update</button>
                <a href="<?php echo base_url('Controller_Members/') ?>" class="btn btn-warning">Back</a>
              </div>
            </form>
          </div>
          <!-- /.box -->
        </div>
        <!-- col-md-12 -->
      </div>
      <!-- /.row -->
        </div>
          <!-- /.row -->
        </div>
        <!-- /.box-body -->
       
      </div>
      <!-- /.box -->

 

    </section>

<!-- <script type="text/javascript">
  $(document).ready(function() {
    $("#groups").select2();

    $("#mainUserNav").addClass('active');
    $("#manageUserNav").addClass('active');
  });
</script> -->

<script type="text/javascript">
$(document).ready(function() {
    // Activate Select2
    $("#groups").select2({
        placeholder: "Select Permission",
        width: '100%'
    });

    $("#warehouse_id").select2({
        placeholder: "Select Warehouse",
        width: '100%'
    });

    // Highlight current menu
    $("#mainUserNav").addClass('active');
    $("#manageUserNav").addClass('active');
});
</script>
