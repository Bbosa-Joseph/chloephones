<?php $session = session(); $validation = \Config\Services::validation(); ?>
<div class="content-wrapper">
    <section class="content-header">
        <h1>Company Settings</h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="active">Company</li>
        </ol>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <?php if($session->getFlashdata('success')): ?>
                    <div class="alert alert-success">
                        <?php echo $session->getFlashdata('success'); ?>
                    </div>
                <?php endif; ?>

                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Company Details</h3>
                    </div>

                    <form role="form" action="<?php echo base_url('Controller_Company'); ?>" method="post">
                        <?php echo csrf_field(); ?>
                        <div class="box-body">
                            <?php echo $validation->listErrors(); ?>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Company Name</label>
                                        <input type="text" class="form-control" name="company_name" value="<?php echo esc($company_data['company_name'] ?? ''); ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Country</label>
                                        <input type="text" class="form-control" name="country" value="<?php echo esc($company_data['country'] ?? ''); ?>">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Address</label>
                                        <input type="text" class="form-control" name="address" value="<?php echo esc($company_data['address'] ?? ''); ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Phone</label>
                                        <input type="text" class="form-control" name="phone" value="<?php echo esc($company_data['phone'] ?? ''); ?>">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Email</label>
                                        <input type="email" class="form-control" name="email" value="<?php echo esc($company_data['email'] ?? ''); ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Website</label>
                                        <input type="text" class="form-control" name="website" value="<?php echo esc($company_data['website'] ?? ''); ?>">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Currency</label>
                                        <input type="text" class="form-control" name="currency" value="<?php echo esc($company_data['currency'] ?? ''); ?>">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Currency Symbol</label>
                                        <input type="text" class="form-control" name="currency_symbol" value="<?php echo esc($company_data['currency_symbol'] ?? ''); ?>">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Tax Rate (%)</label>
                                        <input type="number" step="0.01" class="form-control" name="tax_rate" value="<?php echo esc($company_data['tax_rate'] ?? 0); ?>">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Service Charge (%)</label>
                                        <input type="number" step="0.01" class="form-control" name="service_charge_rate" value="<?php echo esc($company_data['service_charge_rate'] ?? 0); ?>">
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <div class="form-group">
                                        <label>Footer Message</label>
                                        <input type="text" class="form-control" name="footer_message" value="<?php echo esc($company_data['footer_message'] ?? ''); ?>">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="box-footer">
                            <button type="submit" class="btn btn-success"><i class="fa fa-save"></i> Save Settings</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
$(document).ready(function(){
    $('#companyNav').addClass('active');
});
</script>
