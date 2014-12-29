// http://jacwright.com/projects/javascript/date_format/
// Simulates PHP's date function
Date.prototype.format = function(format) {
	var replace = Date.replaceChars;
	
	for (var i in replace) {
		if (format.indexOf(i) !== -1) {
			format = format.replace(i, replace[i].call(this));
		}
	}
	return format;
};

Date.replaceChars = {
	shortMonths: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
	longMonths: ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
	shortDays: ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
	longDays: ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'],

	// Day
	'%d':  function() { return (this.getDate() < 10 ? '0' : '') + this.getDate(); },
	'%#d': function() { return this.getDate(); },
	'%a':  function() { return Date.replaceChars.shortDays[this.getDay()]; },
	'%A':  function() { return Date.replaceChars.longDays[this.getDay()]; },
	'%w':  function() { return this.getDay(); },
	// Week
	'%U':  function() { var d = new Date(this.getFullYear(), 0, 1); return Math.ceil((((this - d) / 86400000) + d.getDay()) / 7); }, // Fixed now
	'%#U': function() { var d = new Date(this.getFullYear(), 0, 1); return Math.ceil((((this - d) / 86400000) + d.getDay()) / 7); }, // Fixed now
	'%W':  function() { var d = new Date(this.getFullYear(), 0, 1); return Math.ceil((((this - d) / 86400000) + d.getDay() + 1) / 7); }, // Fixed now
	'%#W': function() { var d = new Date(this.getFullYear(), 0, 1); return Math.ceil((((this - d) / 86400000) + d.getDay() + 1) / 7); }, // Fixed now
	// Month
	'%B':  function() { return Date.replaceChars.longMonths[this.getMonth()]; },
	'%b':  function() { return Date.replaceChars.shortMonths[this.getMonth()]; },
	'%m':  function() { return (this.getMonth() < 9 ? '0' : '') + (this.getMonth() + 1); },
	'%#m': function() { return this.getMonth() + 1; },
	// Year
	'%Y':  function() { return this.getFullYear(); },
	'%y':  function() { return ('' + this.getFullYear()).substr(2); },
	// Time
	'%p':  function() { return this.getHours() < 12 ? 'AM' : 'PM'; },
	'%I':  function() { return ((this.getHours() % 12 || 12) < 10 ? '0' : '') + (this.getHours() % 12 || 12); },
	'%#I': function() { return this.getHours() % 12 || 12; },
	'%H':  function() { return (this.getHours() < 10 ? '0' : '') + this.getHours(); },
	'%#H': function() { return this.getHours(); },
	'%M':  function() { return (this.getMinutes() < 10 ? '0' : '') + this.getMinutes(); },
	'%#M': function() { return (this.getMinutes() < 10 ? '0' : '') + this.getMinutes(); },
	'%S':  function() { return (this.getSeconds() < 10 ? '0' : '') + this.getSeconds(); },
	'%#S': function() { return (this.getSeconds() < 10 ? '0' : '') + this.getSeconds(); },
	// Timezone
	I: function() {
		var DST = null;
			for (var i = 0; i < 12; ++i) {
					var d = new Date(this.getFullYear(), i, 1);
					var offset = d.getTimezoneOffset();

					if (DST === null) DST = offset;
					else if (offset < DST) { DST = offset; break; }                     else if (offset > DST) break;
			}
			return (this.getTimezoneOffset() == DST) | 0;
		},
	'%z':  function() { var m = this.getMonth(); this.setMonth(0); var result = this.toTimeString().replace(/^.+ \(?([^\)]+)\)?$/, '$1'); this.setMonth(m); return result;},
	'%Z':  function() { var m = this.getMonth(); this.setMonth(0); var result = this.toTimeString().replace(/^.+ \(?([^\)]+)\)?$/, '$1'); this.setMonth(m); return result;},
	'%j':  function() { return -this.getTimezoneOffset() * 60; },
	'%#j': function() { return -this.getTimezoneOffset() * 60; },
	// Full Date/Time
	'%#c': function() { return this.format('%A, $B %#d, %Y, %#H:%M:%S'); },
	'%#x': function() { return this.format('%A, $B %#d, %Y'); },
	'%c':  function() { return this.toString(); },
	'%x':  function() { return this.toString(); },
	'%X':  function() { return this.toString(); },
	'%%':  '%'
};

function extract_numbers(from) {
	if (from === undefined) return '0';
	if (from.number !== undefined) return from.number;
	var n = /^[0-9]*/.exec(from.txt);
	n = n[0];
	if (n === '') n = '0';
	return n;
}

function number_to_size(number, size) {
	var n = "" + number;
	var l = n.length;
	if (size > l) {
		n = "0".repeat(size - l) + n;
	} else {
		n = n.substr(l - size); 
	}

	if (n === '') n = '0';
	  
	return n;
}

function local_timestamp() {
	var t = new Date();
	return (t.getTime() - t.getTimezoneOffset()*60*1000)/1000;
}
