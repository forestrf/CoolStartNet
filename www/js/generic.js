// Requires API.js
function generate_widget_element(data, API, useFunc, removeFunc) {
	var m, M, buttonuse, w = crel2('div', ['class', 'widget_element'],
		m = crel2('div', ['class', 'minimized'],
			crel2('div', ['class', 'image'],
				crel2('div', ['class', 'image_bg']),
				crel2('img', ['class', 'image_front', 'src', API.url(data.preview, data.IDwidget)])
			),
			crel2('div', ['class', 'name'], data.name),
			crel2('div', ['class', 'description'], data.description),
			buttonuse = crel2('div', ['onclick', useRemove])
		)
	);
	
	w.body = m;
	w.buttonuse = buttonuse;
	
	w.minimized = true;
	
	w.maximize = function() {
		if (M === undefined) crel2(w, M = crel2('div', ['class', 'maximized']));
		
		crel2(M,
			crel2('div', ['class', 'text'],
				data.fulldescription
			),
			imagesFromArray(crel2('div', ['class', 'images']), data.images)
		);
		w.minimized = false;
		w.addClass('full');
	};
	w.minimize = function() {
		M.innerHTML = '';
		w.minimized = true;
		w.removeClass('full');
	};
	
	var canChangeUsingStatus = false;
	w.setUsingStatus = function(inuse) {
		if (inuse) {
			buttonuse.className = 'use_button enabled';
			buttonuse.innerHTML = 'ENABLED';
		} else {
			buttonuse.className = 'use_button disabled';
			buttonuse.innerHTML = 'DISABLED';
		}
		canChangeUsingStatus = true;
		data.inuse = inuse;
	};
	w.setUsingStatus(data.inuse);
	
	return w;
	
	function imagesFromArray(div, dataImages) {
		for (var i = 0; i < dataImages.length; i++) {
			var url = API.url(dataImages[i], data.IDwidget);
			crel2(div, imageFromArray(div, url));
		}
		return div;
	}
	
	function imageFromArray(div, url) {
		return crel2('div', [
			'class', 'capture',
			'style', 'background-image: url(' + url + ');',
			'onclick', function(){ showBig(div, url); }
		]);
	}
	
	function showBig(div, url) {
		var pic = crel2('div', ['class', 'big_preview'],
			crel2('div', [
				'class', 'big_preview_image',
				'style', 'background-image: url(' + url + ');'
			])
		);
		pic.onclick = function(){ pic.parentNode.removeChild(pic); };
		crel2(div, pic);
	}
	
	function useRemove(event) {
		if (canChangeUsingStatus) {
			data.inuse ? removeFunc(data, w) : useFunc(data, w);
		}
		canChangeUsingStatus = false;
		event.stopPropagation();
	}
}
