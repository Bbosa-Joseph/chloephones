<?php $session = session(); ?>
<div class="content-wrapper">
  <section class="content-header">
    <h1>Manage Members</h1>
    <ol class="breadcrumb">
      <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
      <li><a href="<?php echo base_url('Controller_Members'); ?>">Members</a></li>
      <li class="active">Delete</li>
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

        <h1>Do you really want to remove ?</h1>

        <form action="<?php echo base_url('Controller_Members/delete/'.$id); ?>" method="post">
          <?php echo csrf_field(); ?>
          <input type="submit" class="btn btn-primary" name="confirm" value="Confirm">
          <a href="<?php echo base_url('Controller_Members'); ?>" class="btn btn-warning">Cancel</a>
        </form>

      </div>
    </div>
  </section>
</div>

<script type="text/javascript">
  $(document).ready(function() {
    $("#mainUserNav").addClass('active');
    $("#manageUserNav").addClass('active');
  });
</script>
