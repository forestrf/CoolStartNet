//Copyright (C) 2012 Kory Nunn

//Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

//The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

//THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

/*

	This code is not formatted for readability, but rather run-speed and to assist compilers.

	However, the code's intention should be transparent.

*/

// "crel2" by Forestrf, modification of "crel" by Kory Nunn
// https://github.com/forestrf/crel2

(function (root, factory) {
	if (typeof exports === 'object') {
		module.exports = factory();
	} else if (typeof define === 'function' && define.amd) {
		define(factory);
	} else {
		root.crel2 = factory();
	}
})(this, function () {
	var document = window.document;

	var f = function() {
		var args = arguments, //Note: assigned to a variable to assist compilers. Saves about 40 bytes in closure compiler. Has negligable effect on performance.
			element = args[0],
			argumentsLength = args.length;

		element = typeof element === 'string' ? document.createElement(element) : element;
		// shortcut
		if (argumentsLength === 1) {
			return element;
		}

		// Reuse variable. settins inside the if, child inside the while
		var settings_child = args[1],
			childIndex = 2;

		// settings_child is defined
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

		// redefine settings_child
		while (argumentsLength > childIndex) {
			settings_child = args[childIndex++];
			if (typeof settings_child !== 'object') {
				settings_child = document.createTextNode(settings_child);
			}
			element.appendChild(settings_child);
		}

		return element;
	}

	return f;
});
