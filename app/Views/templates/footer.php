	</div><!-- /.wrapper -->

	<div id="toast-container" style="position:fixed;top:20px;right:20px;z-index:99999;max-width:380px;"></div>
	<script>
	function showToast(message, type) {
		type = type || 'success';
		var bgColor = type === 'success' ? '#059669' : type === 'error' ? '#dc2626' : type === 'warning' ? '#d97706' : '#4f46e5';
		var icon = type === 'success' ? 'fa-check-circle' : type === 'error' ? 'fa-times-circle' : type === 'warning' ? 'fa-exclamation-triangle' : 'fa-info-circle';
		var toast = $('<div class="ci-toast" style="display:flex;align-items:center;gap:10px;background:'+bgColor+';color:#fff;padding:14px 20px;border-radius:10px;margin-bottom:10px;box-shadow:0 8px 24px rgba(0,0,0,0.18);font-size:14px;font-weight:500;opacity:0;transform:translateX(40px);transition:all 0.3s ease;">' +
			'<i class="fa '+icon+'" style="font-size:18px;"></i>' +
			'<span style="flex:1;">'+message+'</span>' +
			'<i class="fa fa-times" style="cursor:pointer;opacity:0.7;" onclick="$(this).parent().remove();"></i>' +
		'</div>');
		$('#toast-container').append(toast);
		setTimeout(function(){ toast.css({opacity:1,transform:'translateX(0)'}); }, 10);
		setTimeout(function(){ toast.css({opacity:0,transform:'translateX(40px)'}); setTimeout(function(){ toast.remove(); }, 300); }, 4000);
	}
	$(document).ready(function(){
		$('.alert-success.alert-dismissible').each(function(){
			showToast($(this).text().trim(), 'success');
			$(this).remove();
		});
		$('.alert-danger.alert-dismissible, .alert-error.alert-dismissible').each(function(){
			showToast($(this).text().trim(), 'error');
			$(this).remove();
		});
		$('.alert-warning.alert-dismissible').each(function(){
			showToast($(this).text().trim(), 'warning');
			$(this).remove();
		});
	});
	</script>

	<footer class="ci-footer">
		<span>&copy; <?php echo date('Y'); ?> Chloe Inventory Management System</span>
		<span>Version 1.0</span>
	</footer>
</body>
</html>
