<aside class="main-sidebar ui-main-sidebar">
	<section class="sidebar">
		<div class="ui-sidebar-panel">
			<div class="ui-sidebar-brand ui-sidebar-brand--mobile-only">
				<div class="ui-sidebar-brand-icon">
					<img src="<?php echo base_url('assets/images/product_image/chloe.png'); ?>" alt="Chloe">
				</div>
				<div class="ui-sidebar-brand-text">
					<span class="ui-sidebar-brand-label">Workspace</span>
					<strong>Chloe Inventory</strong>
				</div>
			</div>

			<form action="#" method="get" class="sidebar-form ui-sidebar-search">
				<div class="input-group">
					<span class="input-group-addon"><i class="fa fa-search"></i></span>
					<input type="text" name="q" class="form-control" placeholder="Search menu">
				</div>
			</form>
		</div>

		<ul class="sidebar-menu ui-sidebar-menu" data-widget="tree">
			<li class="header">Main Navigation</li>

			<li id="dashboardMainMenu">
				<a href="<?php echo base_url('dashboard'); ?>">
					<i class="fa fa-dashboard"></i>
					<span>Dashboard</span>
				</a>
			</li>

			<?php if (!empty($user_permission) && array_intersect(['createStore','updateStore','viewStore','deleteStore'], $user_permission)): ?>
			<li id="storeNav">
				<a href="<?php echo base_url('Controller_Warehouse'); ?>">
					<i class="fa fa-institution"></i>
					<span>Branches</span>
				</a>
			</li>
			<?php endif; ?>

			<?php if (!empty($user_permission) && array_intersect(['createProduct','updateProduct','viewProduct','deleteProduct'], $user_permission)): ?>
			<li class="treeview" id="mainProductNav">
				<a href="#">
					<i class="fa fa-cube"></i>
					<span>Stock</span>
					<span class="pull-right-container"><i class="fa fa-angle-right pull-right"></i></span>
				</a>
				<ul class="treeview-menu">
					<?php if(in_array('createProduct', $user_permission)): ?>
					<li id="addProductNav">
						<a href="<?php echo base_url('Controller_Products/create'); ?>">
							<i class="fa fa-circle-o"></i>
							<span>Add Stock</span>
						</a>
					</li>
					<?php endif; ?>
					<?php if(array_intersect(['updateProduct','viewProduct','deleteProduct'], $user_permission)): ?>
					<li id="manageProductNav">
						<a href="<?php echo base_url('Controller_Products'); ?>">
							<i class="fa fa-circle-o"></i>
							<span>Manage Stock</span>
						</a>
					</li>
					<?php endif; ?>
				</ul>
			</li>
			<?php endif; ?>

			<?php if (!empty($user_permission) && array_intersect(['createOrder','updateOrder','viewOrder','deleteOrder'], $user_permission)): ?>
			<li class="treeview" id="mainOrdersNav">
				<a href="#">
					<i class="fa fa-dollar"></i>
					<span>Receipts</span>
					<span class="pull-right-container"><i class="fa fa-angle-right pull-right"></i></span>
				</a>
				<ul class="treeview-menu">
					<?php if(in_array('createOrder', $user_permission)): ?>
					<li id="addOrderNav">
						<a href="<?php echo base_url('Controller_Orders/create'); ?>">
							<i class="fa fa-circle-o"></i>
							<span>Make Receipt</span>
						</a>
					</li>
					<?php endif; ?>
					<?php if(array_intersect(['updateOrder','viewOrder','deleteOrder'], $user_permission)): ?>
					<li id="manageOrdersNav">
						<a href="<?php echo base_url('Controller_Orders'); ?>">
							<i class="fa fa-circle-o"></i>
							<span>Manage Receipt</span>
						</a>
					</li>
					<?php endif; ?>
				</ul>
			</li>
			<?php endif; ?>

			<?php if (!empty($user_permission) && array_intersect(['createUser','updateUser','viewUser','deleteUser'], $user_permission)): ?>
			<li class="treeview" id="mainUserNav">
				<a href="#">
					<i class="fa fa-users"></i>
					<span>Branch Admin</span>
					<span class="pull-right-container"><i class="fa fa-angle-right pull-right"></i></span>
				</a>
				<ul class="treeview-menu">
					<?php if(in_array('createUser', $user_permission)): ?>
					<li id="createUserNav">
						<a href="<?php echo base_url('Controller_Members/create'); ?>">
							<i class="fa fa-circle-o"></i>
							<span>Add Branch Admin</span>
						</a>
					</li>
					<?php endif; ?>
					<?php if(array_intersect(['updateUser','viewUser','deleteUser'], $user_permission)): ?>
					<li id="manageUserNav">
						<a href="<?php echo base_url('Controller_Members'); ?>">
							<i class="fa fa-circle-o"></i>
							<span>Manage Branch Admin</span>
						</a>
					</li>
					<?php endif; ?>
				</ul>
			</li>
			<?php endif; ?>

			<?php if (!empty($user_permission) && array_intersect(['createGroup','updateGroup','viewGroup','deleteGroup'], $user_permission)): ?>
			<li class="treeview" id="mainGroupNav">
				<a href="#">
					<i class="fa fa-recycle"></i>
					<span>Permission</span>
					<span class="pull-right-container"><i class="fa fa-angle-right pull-right"></i></span>
				</a>
				<ul class="treeview-menu">
					<?php if(in_array('createGroup', $user_permission)): ?>
					<li id="addGroupNav">
						<a href="<?php echo base_url('Controller_Permission/create'); ?>">
							<i class="fa fa-circle-o"></i>
							<span>Add Permission</span>
						</a>
					</li>
					<?php endif; ?>
					<?php if(array_intersect(['updateGroup','viewGroup','deleteGroup'], $user_permission)): ?>
					<li id="manageGroupNav">
						<a href="<?php echo base_url('Controller_Permission'); ?>">
							<i class="fa fa-circle-o"></i>
							<span>Manage Permission</span>
						</a>
					</li>
					<?php endif; ?>
				</ul>
			</li>
			<?php endif; ?>

			<?php if (!empty($user_permission) && in_array('updateCompany', $user_permission)): ?>
			<li id="companyNav">
				<a href="<?php echo base_url('Controller_Company'); ?>">
					<i class="fa fa-bank"></i>
					<span>Company</span>
				</a>
			</li>
			<?php endif; ?>

			<li>
				<a href="<?php echo base_url('auth/logout'); ?>">
					<i class="fa fa-power-off"></i>
					<span>Logout</span>
				</a>
			</li>
		</ul>
	</section>
</aside>

<script>
	$(document).ready(function () {
		$('.treeview').each(function () {
			if ($(this).find('.treeview-menu li.active').length > 0) {
				$(this).addClass('active');
			}
		});

		$('.ui-sidebar-search .form-control').on('keyup', function(){
			var term = $(this).val().toLowerCase();
			$('.ui-sidebar-menu > li:not(.header)').each(function(){
				var text = $(this).text().toLowerCase();
				$(this).toggle(text.indexOf(term) !== -1);
			});
		});

		$('.sidebar-menu a[href]').on('click', function(){
			if($(window).width() <= 992 && $(this).attr('href') !== '#') {
				$('body').removeClass('sidebar-open');
			}
		});
	});
</script>
