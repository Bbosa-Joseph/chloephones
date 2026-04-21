<?php $session = session(); ?>
<div class="content-wrapper">
  <section class="content-header">
    <h1>Sales Reports</h1>
    <ol class="breadcrumb">
      <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
      <li class="active">Reports</li>
    </ol>
  </section>

  <section class="content">
    <div class="row">
      <div class="col-md-12">
        <div class="box">
          <div class="box-header with-border">
            <form method="post" action="<?php echo base_url('Reports'); ?>" class="form-inline">
              <?php echo csrf_field(); ?>
              <label for="select_year" style="margin-right:10px;">Select Year</label>
              <select name="select_year" id="select_year" class="form-control" style="margin-right:10px;">
                <?php foreach($report_years as $year): ?>
                  <option value="<?php echo $year; ?>" <?php echo ($selected_year == $year) ? 'selected' : ''; ?>><?php echo $year; ?></option>
                <?php endforeach; ?>
              </select>
              <button type="submit" class="btn btn-primary">Filter</button>
            </form>
          </div>
          <div class="box-body">
            <div class="table-responsive">
              <table class="table table-bordered">
                <thead>
                  <tr>
                    <th>Month</th>
                    <th>Total Sales</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if(!empty($results)): ?>
                    <?php foreach($results as $month => $total): ?>
                      <tr>
                        <td><?php echo $month; ?></td>
                        <td><?php echo $company_currency . ' ' . number_format($total); ?></td>
                      </tr>
                    <?php endforeach; ?>
                  <?php else: ?>
                    <tr><td colspan="2" class="text-center">No data</td></tr>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</div>
