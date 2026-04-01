<?php
  $username = $this->session->userdata('username');
  $email = $this->session->userdata('email');
  $display_name = !empty($username) ? $username : 'System User';
  $display_email = !empty($email) ? $email : 'No email available';
  $first_letter = strtoupper(substr($display_name, 0, 1));
  $page_heading = !empty($page_title) ? $page_title : 'Dashboard';
?>
<header class="main-header">
  <a href="<?php echo base_url('dashboard'); ?>" class="logo ui-app-logo">
    <span class="logo-mini">
      <img src="<?php echo base_url('assets/images/product_image/chloe.png'); ?>" alt="Chloe">
    </span>
    <span class="logo-lg">
      <span class="ui-app-logo-mark">
        <img src="<?php echo base_url('assets/images/product_image/chloe2.png'); ?>" alt="Chloe">
      </span>
      <span class="ui-app-logo-text">Chloe Inventory</span>
    </span>
  </a>

  <nav class="navbar navbar-static-top ui-topbar" role="navigation">
    <a href="#" class="sidebar-toggle ui-sidebar-toggle" role="button" aria-label="Toggle navigation">
      <i class="fa fa-bars"></i>
      <span class="sr-only">Toggle navigation</span>
    </a>

    <div class="navbar-custom-menu">
      <ul class="nav navbar-nav ui-topbar-nav">
        <li class="ui-topbar-title hidden-xs">
          <div class="ui-topbar-title-wrap">
            <span class="ui-topbar-kicker">Chloe Inventory</span>
            <span class="ui-topbar-heading"><?php echo html_escape($page_heading); ?></span>
          </div>
        </li>

        <!-- Notification Bell -->
        <li class="dropdown notifications-menu" id="notifBell">
          <a href="#" class="dropdown-toggle" data-toggle="dropdown" style="position:relative;">
            <i class="fa fa-bell-o"></i>
            <span class="label label-danger notif-badge" style="display:none;position:absolute;top:6px;right:4px;font-size:10px;padding:2px 5px;border-radius:50%;">0</span>
          </a>
          <ul class="dropdown-menu" style="width:320px;max-height:400px;overflow-y:auto;">
            <li class="header" style="padding:10px 15px;font-weight:600;border-bottom:1px solid #eee;">
              Notifications <span class="notif-badge-text" style="color:#999;font-weight:400;"></span>
            </li>
            <li>
              <ul class="menu notif-list" style="list-style:none;padding:0;margin:0;">
                <li style="padding:12px 15px;color:#999;text-align:center;" class="notif-empty">No new notifications</li>
              </ul>
            </li>
            <li class="footer" style="padding:8px 15px;text-align:center;border-top:1px solid #eee;">
              <a href="#" id="markAllRead" style="font-size:13px;">Mark all as read</a>
            </li>
          </ul>
        </li>

        <li class="dropdown user user-menu">
          <a href="#" class="dropdown-toggle ui-user-toggle" data-toggle="dropdown" aria-expanded="false">
            <span class="ui-user-avatar"><?php echo html_escape($first_letter); ?></span>
            <span class="hidden-xs ui-user-meta">
              <strong><?php echo html_escape($display_name); ?></strong>
              <small><?php echo html_escape($display_email); ?></small>
            </span>
            <i class="fa fa-angle-down ui-user-caret hidden-xs"></i>
          </a>
          <ul class="dropdown-menu ui-user-dropdown" id="userMenu">
            <li class="user-header ui-user-dropdown-header">
              <span class="ui-user-avatar ui-user-avatar-lg"><?php echo html_escape($first_letter); ?></span>
              <p>
                <?php echo html_escape($display_name); ?>
                <small><?php echo html_escape($display_email); ?></small>
              </p>
            </li>
            <li class="user-footer ui-user-dropdown-footer">
              <div class="pull-left">
                <span class="ui-user-status"><i class="fa fa-circle"></i> Active Session</span>
              </div>
              <div class="pull-right">
                <a href="<?php echo base_url('auth/logout'); ?>" class="btn btn-default btn-flat ui-logout-btn">
                  <i class="fa fa-sign-out"></i> Logout
                </a>
              </div>
            </li>
          </ul>
        </li>
      </ul>
    </div>
  </nav>
</header>
<div class="ci-sidebar-overlay" id="ciSidebarOverlay"></div>
<script>
$(document).ready(function(){
  var $body = $('body');

  // Disable AdminLTE push-menu so we handle toggle ourselves
  $(document).off('click', '[data-toggle="push-menu"]');

  // Sidebar toggle — desktop: collapse/mini, mobile: slide overlay
  $(document).on('click', '.sidebar-toggle, .ui-sidebar-toggle', function(e){
    e.preventDefault();
    e.stopPropagation();
    if($(window).width() > 992) {
      $body.toggleClass('sidebar-collapse');
    } else {
      $body.toggleClass('sidebar-open');
    }
  });

  // Close sidebar on overlay click (mobile)
  $('#ciSidebarOverlay').on('click touchstart', function(e){
    e.preventDefault();
    $body.removeClass('sidebar-open');
  });

  // On resize: clean up classes when crossing breakpoint
  $(window).on('resize', function(){
    if($(window).width() > 992) {
      $body.removeClass('sidebar-open');
    }
  });

  // Close sidebar when pressing Escape
  $(document).on('keydown', function(e){
    if(e.keyCode === 27) {
      $body.removeClass('sidebar-open');
    }
  });

  // --- Notification Polling ---
  var lastPoll = new Date().toISOString().slice(0,19).replace('T',' ');
  var pollUrl = '<?php echo base_url("Controller_Notifications/poll"); ?>';
  var markReadUrl = '<?php echo base_url("Controller_Notifications/markRead"); ?>';

  function updateNotifUI(data) {
    var count = data.count || 0;
    var $badge = $('.notif-badge');
    var $list = $('.notif-list');

    if(count > 0) {
      $badge.text(count).show();
      $('.notif-badge-text').text('(' + count + ' unread)');
    } else {
      $badge.hide();
      $('.notif-badge-text').text('');
    }

    if(data.notifications && data.notifications.length > 0) {
      $list.find('.notif-empty').remove();
      $.each(data.notifications, function(i, n){
        var icon = n.type === 'warning' ? 'fa-exclamation-circle text-yellow' : 'fa-info-circle text-blue';
        var time = n.created_at;
        var exists = $list.find('[data-nid="'+n.id+'"]').length;
        if(!exists) {
          $list.prepend(
            '<li data-nid="'+n.id+'" style="padding:10px 15px;border-bottom:1px solid #f4f4f4;">'+
              '<i class="fa '+icon+'" style="margin-right:8px;"></i>'+
              '<span style="font-size:13px;">'+n.message+'</span>'+
              '<br><small style="color:#aaa;">'+time+'</small>'+
            '</li>'
          );
          // Show toast for new ones
          if(typeof showToast === 'function') {
            showToast(n.message, n.type || 'info');
          }
        }
      });
    }
  }

  function pollNotifications() {
    $.ajax({
      url: pollUrl,
      type: 'GET',
      data: { since: lastPoll },
      dataType: 'json',
      success: function(data) {
        updateNotifUI(data);
        lastPoll = new Date().toISOString().slice(0,19).replace('T',' ');
      }
    });
  }

  // Initial load (all unread)
  $.ajax({
    url: pollUrl, type: 'GET', dataType: 'json',
    success: function(data) { updateNotifUI(data); }
  });

  // Poll every 15 seconds
  setInterval(pollNotifications, 15000);

  // Mark all as read
  $('#markAllRead').on('click', function(e){
    e.preventDefault();
    $.ajax({
      url: markReadUrl, type: 'POST', dataType: 'json',
      success: function(){
        $('.notif-badge').hide();
        $('.notif-badge-text').text('');
        $('.notif-list').html('<li style="padding:12px 15px;color:#999;text-align:center;" class="notif-empty">No new notifications</li>');
      }
    });
  });
});
</script>