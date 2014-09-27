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
			C('div', ['class', 'panel available'],
				C('div', ['class', 'search_bar'],
					search_txt = C('input', ['class', 'search_bar_input', 'type', 'text']),
					search_button = C('input', ['class', 'search_bar_button', 'type', 'button', 'value', 'Search'])
				),
				list_available = C('div', ['class', 'list'])
			),
			C('div', ['class', 'panel in_use'],
				C('div', ['class', 'title'], 'Widgets in use'),
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
			
			for(var i = 0; i < data.length; i++){
				list.appendChild(generate_widget_element(data[i], 'Use widget', use_callback));
			}
		}
		
		function use_callback(ID, div){
			console.log(ID);
			console.log(div);
		}
		
		// use_callback(widgetID, div);
		function generate_widget_element(data, use_text, use_callback){
			var div = C('div', ['class', 'widget_element'],
				C('div', ['class', 'body'],
					C('img', ['class', 'image', 'src', 'http://placehold.it/80x80/'+rnd(3)/*IPA.widgetImage(data.ID)*/]),
					C('div', ['class', 'txt name'], data.name),
					C('div', ['class', 'txt description'], data.description),
					C('div', ['class', 'txt autor'], 'Autor name and link - forum thread'),
					C('div', ['class', 'use', 'onclick', function(){use_callback(data.ID, div)}],
						C('span', use_text)
					)
				),
				C('div', ['class', 'valoration'],
					C('div')
				)
			);
			
			return div;
		}
		
		// borrar
		function rnd(loops){
			return (Math.random()*10).toFixed() + (loops > 1 ? rnd(loops -1) : '');
		}
		
	})(API, IPA);
</script>

<?php
	$html = ob_get_contents();
	ob_end_clean();
	
	echo render_wrapper('Manage widgets - CoolStart.net', $html, true);
?>
