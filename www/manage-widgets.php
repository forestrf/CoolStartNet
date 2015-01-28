<?php
	require_once __DIR__.'/php/defaults.php';
	require_once __DIR__.'/php/config.php';
	require_once __DIR__.'/php/functions/generic.php';
	require_once __DIR__.'/php/lib/renderer.php';
	
	$db = open_db_session();
	
	ob_start();
?>

<script src="//<?=WEB_PATH?>js/ipa.js"></script>
<script src="//<?=WEB_PATH?>js/generic.js"></script>
<script src="//<?=WEB_PATH?>js/scrollto.js"></script>
<link href="//<?=WEB_PATH?>css/widget-box.css" rel="stylesheet"/>
<link href="//<?=WEB_PATH?>css/manage-widgets.css" rel="stylesheet"/>

<script>
	(function(API, IPA){
		var C = crel2;
		API = API.init();
		IPA = IPA.init(<?=server_vars_js()?>);
		var div = document.getElementById('widgets0');
		
		
		
		// GUI
		
		var search_txt, search_button, list, left, right;
		
		C(div,
			left = C('div', ['class', 'left'],
				C('table', ['class', 'panel'],
					C('tr', ['class', 'search_bar'],
						C('td', ['class', 'search_bar_cell'],
							search_txt = C('input', ['class', 'search_bar_input', 'type', 'text', 'placeholder', 'Search...'])
						),
						C('td', ['class', 'search_bar_cell cell_button'],
							search_button = C('input', ['class', 'search_bar_button', 'type', 'button', 'value', 'Search'])
						)
					),
					C('tr',
						C('td', ['colspan', 2],
							C('div', ['class', 'button big', 'onclick', fill_list_global], 'All widgets'),
							C('div', ['class', 'button big', 'onclick', fill_list_mywidgets], 'My widgets'),
							C('div', ['class', 'button', 'onclick', function(){}], 'Category 1'),
							C('div', ['class', 'button', 'onclick', function(){}], 'Category 2')
						)
					)
				)
			),
			right = C('div', ['class', 'right'],
				list = C('div', ['class', 'list'])
			)
		);
		
		
		search_txt.onkeyup = tipying_search_txt;
		function tipying_search_txt() {
			fill_list('global-list-search', 0, 'search=' + encodeURIComponent(search_txt.value));
			//filter_results(search_txt.value);
		}
		
		function filter_results(by) {
			for (var i = 0; i < widgets.length; i++) {
				if (widgets[i].txt.toLowerCase().indexOf(by.toLowerCase()) === -1) {
					widgets[i].style.display = 'none';
				} else {
					widgets[i].style.display = '';
				}
			}
		}
		
		
		// FUNCTIONS
		
		search_button.onclick = function(){search(search_txt.value)};
		function search(text) {
			console.log(text);
		}
		
		// widgets container
		var widgets = [];
		
		// last widget from the list for stacked requests
		function fill_list(action, last, post) {
			if (last === undefined) {
				last = 0;
			}
			if (post === undefined) {
				post = '';
			} else post = '&' + post;
			
			API.xhr(
				'widgets?action=' + action,
				'last=' + last + post,
				function (data) {
					if (data.status === 'OK') {
						widgets = [];
						for (var i = 0; i < data.response.length; i++) {
							widgets.push(generate_widget(data.response[i]));
						}

						if (last === 0) {
							// When no last, delete previous list
							list.innerHTML = '';
						}
						fill_list_with_widgets(list, widgets);
					} else if(data.status !== 'FAIL') {
						setTimeout(fill_list_global, 5000);
					}
				},
				function () {
					setTimeout(fill_list_global, 5000);
				}
			);
		}
		fill_list_global();
		
		function fill_list_global() {
			fill_list('global-list');
		}
		
		function fill_list_mywidgets() {
			fill_list('user-using-list');
		}
		
		function fill_list_with_widgets(list, widgets) {
			list.innerHTML = '';
			
			for (var i = 0; i < widgets.length; i++) {
				list.appendChild(widgets[i]);
			}
		}
		
		function minimize_all_widgets() {
			for (var i = 0; i < widgets.length; i++) {
				if (!widgets[i].minimized) {
					widgets[i].minimize();
				}
			}
		}
		
		function generate_widget(data) {
			var w = generate_widget_element(data, IPA, useFunc, removeFunc);
			API.document.wrapElement(w);
			w.txt = data.name + " " + data.description;
			w.body.onclick = function(){
				var temp = w.minimized;
				minimize_all_widgets();
				
				if (temp) w.maximize();
				
				scrollToX(right, right.scrollTop, w.offsetTop, 0, 1/200, 20);
			};
			return w;
		}
		
		function useFunc(data, w) {
			useremoveFunc(data, w, 'user-using-add');
		}
		
		function removeFunc(data, w) {
			useremoveFunc(data, w, 'user-using-remove');
		}
		
		function useremoveFunc(data2, w, action) {
			API.xhr(
				'widgets?action=' + action,
				'IDwidget=' + data2.IDwidget,
				function (data) {
					if (data.status === 'OK') {
						w.setUsingStatus(!data2.inuse);
					}
				}
			);
		}
		
	})(API, IPA);
</script>

<?php
	$html = ob_get_contents();
	ob_end_clean();
	
	echo render_wrapper('Manage widgets - CoolStart.net', $html, false);
?>
