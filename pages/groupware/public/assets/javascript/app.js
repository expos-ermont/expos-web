// ---------------------------------------------------
//  Namespace
// ---------------------------------------------------

var App = window.App || {};
App.engine  = {}; // engine namspace
App.modules = {}; // modules (such as AddTaskForm, AddMessageForm etc)
App.widgets = {}; // widgets (such as GroupedBlock, UserBoxMenu, PageAction)


App.engine = {
  showStatus: function(message) {
    
  },
  hideStatus: function() {
    
  }
}

// ---------------------------------------------------
// Language
// ---------------------------------------------------
if (typeof _lang != 'object') _lang = {};
function lang(name) {
	var value = _lang[name];
	if (!value) {
		return "Missing lang.js: " + name;
	}
	for (var i=1; i < arguments.length; i++) {
		value = value.replace("{" + (i-1) + "}", arguments[i]);
	}
	return value;
}

function langhtml(name) {
	return '<span name="og-lang" id="og-lang-' + name + '">' + lang(name) + '</span>';
}

function addLangs(langs) {
	for (var k in langs) {
		_lang[k] = langs[k];
	}
}

// -----------------------------
// Cookies
// -----------------------------

var Cookies = {};
Cookies.set = function(name, value, expires, path, domain, secure){
	document.cookie = name + "=" + escape (value) +
		(expires ? "; expires=" + expires.toGMTString() : "") +
		(path ? "; path=" + path : "") +
		(domain ? "; domain=" + domain : "") +
		(secure ? "; secure" : "");
};

Cookies.get = function(name){
	var start = document.cookie.indexOf(name + "=");
	if (start < 0) {
		return "";
	}
	var temp = document.cookie.substring(start + name.length + 1);
	var end = temp.indexOf(';');
	if (end < 0) {
		return unescape(temp);
	} else {
		return unescape(temp.substring(0, end));
	}
};

Cookies.clear = function(name) {
	if (Cookies.get(name)) {
		document.cookie = name + "=" +
		"; expires=Thu, 01-Jan-70 00:00:01 GMT";
	}
};
