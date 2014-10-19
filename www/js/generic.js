
function generate_widget_element(data) {
	return crel2('div', ['class', 'widget_element'],
		crel2('div', ['class', 'background']),
		crel2('div', ['class', 'image'],
			crel2('div', ['class', 'image_bg']),
			crel2('img', ['class', 'image_front', 'src', data.image])
		),
		crel2('div', ['class', 'name'], data.name),
		crel2('div', ['class', 'description'], data.description, 
			crel2('div', ['class', 'use'], 'Use widget')
		)
	);
}