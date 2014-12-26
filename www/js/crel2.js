crel2 = function() {
	var args = arguments,
		element = args[0],
		argumentsLength = args.length;

	element = typeof element === 'string' ? document.createElement(element) : element;

	if (argumentsLength === 1) {
		return element;
	}

	var settings_child = args[1],
		childIndex = 2;

	if (settings_child instanceof Array) {
		var s = settings_child.length, action;
		while (s) {
			switch(typeof (action = settings_child[--s])){
				case "string":
				case "number":
					element.setAttribute(settings_child[--s], action);
				break;
				default:
					element[settings_child[--s]] = action;
				break;
			}
		}
	} else {
		--childIndex;
	}

	while (argumentsLength > childIndex) {
		settings_child = args[childIndex++];
		if (typeof settings_child !== 'object') {
			settings_child = document.createTextNode(settings_child);
		}
		element.appendChild(settings_child);
	}

	return element;
}
