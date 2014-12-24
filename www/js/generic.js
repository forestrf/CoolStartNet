// Requires IPA.js

function generate_widget_element(data, IPA) {
	console.log(data);
	return crel2('div', ['class', 'widget_element'],
		crel2('div', ['class', 'image'],
			crel2('div', ['class', 'image_bg']),
			crel2('img', ['class', 'image_front', 'src', IPA.widgetImage(data.IDwidget, '128.jpg')])
		),
		crel2('div', ['class', 'name'], data.name),
		crel2('div', ['class', 'description'], data.description, 
			crel2('div', ['class', 'use'], 'Use widget')
		)
	);
}