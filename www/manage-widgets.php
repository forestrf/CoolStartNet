<?php
	require_once __DIR__.'/php/config.php';
	require_once __DIR__.'/php/functions/generic.php';
	require_once __DIR__.'/php/lib/renderer.php';
	
	ob_start();
?>

<script src="//<?=WEB_PATH?>js/ipa.js"></script>
<link href="//<?=WEB_PATH?>css/manage-widgets.css" rel="stylesheet"/>

<script>
	(function(API, IPA){
		var C = crel2;
		var API = API.init();
		var IPA = IPA.init(<?=server_vars_js()?>);
		var div = document.getElementById('widgets0');
		
		
		
		// GUI
		
		var search_txt, search_button, list_available, list_in_use;
		
		C(div,
			C('h1', ['class', 'title'], 'Manage widgets in use'),
			C('div', ['class', 'panel available'],
				C('div', ['class', 'search_bar'],
					search_txt = C('input', ['class', 'search_bar_input', 'type', 'text']),
					search_button = C('input', ['class', 'search_bar_button', 'type', 'button'])
				),
				list_available = C('div', ['class', 'list'])
			),
			C('div', ['class', 'panel in_use'],
				list_in_use = C('div', ['class', 'list'])
			)
		);
		
		
		
		// FUNCTIONS
		
		search_button.onclick = function(){search(search_txt.value);};
		function search(text){
			console.log(text);
		}
		
		// last widget from the list for stacked requests
		function fill_list_available(last){
			if (undefined === last) {last = 0;}
		
			API.xhr(
				'widgets?action=global-list',
				'last=' + last,
				function(data){
					console.log(data);
					data = JSON.parse(data);
					if (data.status === 'OK') {
						list_from_data(list_available, data.response);
					} else {
						setTimeout(fill_list_available, 5000);
					}
				},
				function(){
					setTimeout(fill_list_available, 5000);
				}
			);
		}
		fill_list_available();
		
		function list_from_data(list, data){
			list.innerHTML = '';
			
			console.log(data);
			for(var i = 0; i < data.length; i++){
				list.appendChild(generate_widget_element(data[i]));
			}
		}
		
		function generate_widget_element(data){
			return C('div', ['class', 'widget_element'],
				C('div', ['class', 'body'],
					C('img', ['class', 'image', 'src', IPA.widgetImage(data.ID)]),
					C('div', ['class', 'name'], data.name),
					C('div', ['class', 'description'], data.description)
				),
				C('div', ['class', 'valoration'],
					C('div')
				)
			);
		}
		
	})(API, IPA);
</script>

<?php
	$html = ob_get_contents();
	ob_end_clean();
	
	echo render_wrapper('Manage widgets - CoolStart.net', $html, true);
?>
