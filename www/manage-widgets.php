<?php
	require_once __DIR__.'/php/defaults.php';
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
		
		var widgets_in_use = [];
		var widgets_available = [];
		
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
						array_widgets_fill(widgets_available, data.response);
						fill_list_with_widgets(list_available, widgets_available);
					} else if(data.status !== 'FAIL'){
						setTimeout(fill_list_available, 5000);
					}
				},
				function(){
					setTimeout(fill_list_available, 5000);
				}
			);
		}
		
		// Call only one time.
		function fill_list_in_use(){
			API.xhr(
				'widgets?action=user-using-list',
				'',
				function(data){
					data = JSON.parse(data);
					if (data.status === 'OK') {
						array_widgets_fill(widgets_in_use, data.response);
						fill_list_with_widgets(list_in_use, widgets_in_use);
						fill_list_available();
					} else if(data.status !== 'FAIL'){
						setTimeout(fill_list_in_use, 5000);
					}
				},
				function(){
					setTimeout(fill_list_in_use, 5000);
				}
			);
		}
		fill_list_in_use();
		
		function fill_list_with_widgets(list, widgets){
			list.innerHTML = '';
			
			for(var i = 0; i < widgets.length; i++){
				list.appendChild(widgets[i].div);
			}
		}
		
		function detail_widget(ID, div){
			console.log(ID);
			console.log(div);
		}
		
		function array_widgets_fill(array, data){
			for(var i = 0; i < data.length; i++){
				array.push(generate_widget(data[i]));
			}
		}
		
		function generate_widget(data){
			return {
				div: C('div', ['class', 'widget_element'],
					C('div', ['class', 'name'], data.name),
					C('img', ['class', 'image', 'src', 'http://placehold.it/120/'+rnd(3)/*IPA.widgetImage(data.ID)*/]),
					C('div', ['class', 'description'], data.description, 
						C('div', ['class', 'use'], 'Use widget')
					)
				),
				data: data
			};
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
	
	echo render_wrapper('Manage widgets - CoolStart.net', $html, false);
?>
