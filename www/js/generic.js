// Requires IPA.js

function generate_widget_element(data, IPA) {
	var m, M, w = crel2('div', ['class', 'widget_element'],
		m = crel2('div', ['class', 'minimized'],
			crel2('div', ['class', 'image'],
				crel2('div', ['class', 'image_bg']),
				crel2('img', ['class', 'image_front', 'src', IPA.widgetImage(data.IDwidget, '128.jpg')])
			),
			crel2('div', ['class', 'name'], data.name),
			crel2('div', ['class', 'description'], data.description)
		),
		M = crel2('div', ['class', 'maximized'],
			crel2('div', 'heeeey')
		)
	);
	w.minimized = m;
	w.maximized = M;
	return w;
}