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
			crel2('div', ['class', 'text'],
				data.fulldescription
			),
			imagesFromArray(crel2('div', ['class', 'images']), data.images)
		)
	);
	w.minimized = m;
	w.maximized = M;
	return w;
	
	function imagesFromArray(div, dataImages) {
		for (var i = 0; i < dataImages.length; i++) {
			var url = IPA.widgetImage(data.IDwidget, dataImages[i]);
			crel2(div, imageFromArray(div, url));
		}
		return div;
	}
	
	function imageFromArray(div, url) {
		return crel2('div', [
			'class', 'capture',
			'style', 'background-image: url(' + url + ');',
			'onclick', function(){ maximize(div, url) }
		]);
	}
	
	function maximize(div, url) {
		var pic = crel2('div', ['class', 'big_preview'],
			crel2('div', [
				'class', 'big_preview_image',
				'style', 'background-image: url(' + url + ');'
			])
		);
		pic.onclick = function(){ pic.parentNode.removeChild(pic) };
		crel2(div, pic);
	}
}
