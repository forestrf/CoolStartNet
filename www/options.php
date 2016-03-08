<?php
	require_once __DIR__.'/php/defaults.php';
	require_once __DIR__.'/php/config.php';
	require_once __DIR__.'/php/functions/generic.php';
	require_once __DIR__.'/php/lib/renderer.php';
	
	$db = open_db_session();
	
	ob_start();
?>

<script src="//<?=WEB_PATH?>js/generic.js"></script>
<link href="//<?=WEB_PATH?>css/widget-box.css" rel="stylesheet"/>
<link href="//<?=WEB_PATH?>css/options.css" rel="stylesheet"/>

<script>
	(function(API){
		var C = crel2;
		var API = API.init(0,'',<?=server_vars_js()?>);
		var div = document.getElementById('widgets0');
		
		/*
		Change password
		Change email
		Space used (total and by widget. Can see the variables and its content)
		Delete account
		*/
		
		div.appendChild(C('div',
			C('form', ['method', 'post', 'action', '//<?=WEB_PATH?>user?action=update-password'],
				C('input', ['type', 'password', 'name', 'current-password', 'placeholder', 'Current password']),
				C('input', ['type', 'password', 'name', 'new-password', 'placeholder', 'New password']),
				C('input', ['type', 'password', 'name', 'new-password-2', 'placeholder', 'Repeat new password']),
				C('input', ['type', 'submit', 'value', 'Change password'])
			),
			// Requires validate the new mail
			C('form', ['method', 'post', 'action', '//<?=WEB_PATH?>user?action=update-email'],
				C('input', ['type', 'password', 'name', 'current-password', 'placeholder', 'Current password']),
				C('input', ['type', 'text', 'name', 'new-email', 'placeholder', 'New email']),
				C('input', ['type', 'text', 'name', 'new-email-2', 'placeholder', 'Repeat new email']),
				C('input', ['type', 'submit', 'value', 'Change email'])
			),
			// requires email validation
			C('form', ['method', 'post', 'action', '//<?=WEB_PATH?>user?action=delete-account'],
				C('input', ['type', 'password', 'name', 'current-password', 'placeholder', 'Current password']),
				C('input', ['type', 'submit', 'value', 'Delete account'])
			),
			'Used space',
			'Download backup of user data',
			'Upload backup of user data',
			C('a', ['href', '//<?=WEB_PATH?>external-web-files/dropbox-request'], 'link with dropbox')
		));
		
		
	})(API);
</script>

<?php
	$html = ob_get_contents();
	ob_end_clean();
	
	echo render_wrapper('Options - CoolStart.net', $html, false);
?>
