var og = {};
var ogTasks = {};
var ogTaskEvents = {};
ogTasksTask = {};
ogTasksMilestone = {};
ogTasksCompany = {};
ogTasksUser = {};
var vtindex = 0;
var vtlist = [];

og.eventTimeouts = [];
og.otherData = [];

// default config (to be overridden by server)
og.pageSize = 10;
og.hostName = '';
og.maxFileSize = 1024 * 1024;

og.showMailsTab = 0;

// functions
og.msg =  function(title, text, timeout, classname, sound) {
	if (typeof timeout == 'undefined') timeout = 4;
	if (!classname) classname = "msg";

	var click_to_remove_msg = ''; // only show this message if error
	if (timeout == 0)
		click_to_remove_msg = '<div style="text-align:center; font-size:small; font-style:italic"><a>' + lang('click to remove') + '</a></div>';
			
	var box = ['<div class="' + classname + '" title="' + lang('click to remove') + '">',
			'<div class="x-box-tl"><div class="x-box-tr"><div class="x-box-tc"></div></div></div>',
			'<div class="x-box-ml"><div class="x-box-mr"><div class="x-box-mc"><h3>{0}</h3>{1}',
			click_to_remove_msg,
			'</div></div></div>',
			'<div class="x-box-bl"><div class="x-box-br"><div class="x-box-bc"></div></div></div>',
			'</div>'].join('');
	if( !this.msgCt){
	    this.msgCt = Ext.DomHelper.insertFirst(document.body, {id:'msg-div'}, true);
	}
	this.msgCt.alignTo(document, 't-t');
	var m = Ext.DomHelper.append(this.msgCt, {html:String.format(box, title, text.replace(/([^>]?)\n/g, '$1<br/>\n'))}, true);
	Ext.get(m).on('click', function() {
		if (timeout > 0) {
			this.setStyle('display', 'none');
		} else {
			this.remove();
		}
	});
	if (timeout > 0) {
		m.slideIn('t').pause(timeout).ghost("t", {remove:true});
	} else {
		m.slideIn('t');
	}
	if (sound) {
		og.systemSound.loadSound('public/assets/sounds/' + sound + '.mp3', true);
		og.systemSound.start(0);
	}
};

og.updateClock = function(clockId){
	var clock = og.eventTimeouts[clockId + "clock"];
	if(clock) {
		clearTimeout(clock);
	}

	var startTime = og.otherData[clockId + "starttime"];
	var startSeconds = og.otherData[clockId + "startseconds"];
	var nowTime = new Date();
	var elapsed = ((nowTime.getElapsed(startTime) / 1000) + startSeconds).toFixed(0);
	var seconds = (elapsed % 60) / 1;
	var totalMinutes = (elapsed - seconds) / 60;
	var minutes = totalMinutes % 60;
	var totalHours = (totalMinutes - minutes) / 60;  
	minutes = ( minutes < 10 ? "0" : "" ) + minutes;
  	seconds = ( seconds < 10 ? "0" : "" ) + seconds.toFixed(0);
	

	var ts = document.getElementById(clockId + "timespan");
	if (ts){
		ts.innerHTML = totalHours + ":" + minutes + ":" + seconds;

		og.eventTimeouts[clockId + "clock"] = setTimeout("og.updateClock('" + clockId + "')", 1002);
	} else {
		og.eventTimeouts[clockId + "clock"] = 0;
	}
};

og.startClock = function(clockId, startSeconds){
	og.otherData[clockId + "starttime"] = new Date();
	og.otherData[clockId + "startseconds"] = startSeconds;
	og.updateClock(clockId);
};

og.err = function(text) {
	var errors = Ext.query("div.err");
	var maxErrors = 2;
	for (var i=0; i < errors.length - maxErrors + 1; i++) {
		Ext.fly(errors[i]).remove();
	}
	og.msg(lang("error"), text, 0, "err");
};

og.hideAndShow = function(itemToHide, itemToDisplay){
	Ext.get(itemToHide).setDisplayed('none');
	Ext.get(itemToDisplay).setDisplayed('block');
};

og.hideAndShowByClass = function(itemToHide, classToDisplay, containerItemName){
	Ext.get(itemToHide).setDisplayed('none');
	
	var list;
	var container;
	if (containerItemName != ''){
		container = document.getElementById(containerItemName);
	} else container = document;
	
	list = container.getElementsByTagName("*");
	
	for(var i = 0; i < list.length; i++){
		var obj = list[i];
		if (obj.className != '' && obj.className.indexOf(classToDisplay) >= 0)
			obj.style.display = '';
	}
};


og.selectReportingMenuItem = function(link, divName, tab){
	var table = document.getElementById('reportingMenu');
	
	var list = table.getElementsByTagName('td');
	for(var i = 0; i < list.length; i++)
		if (list[i].className == 'report_selected_menu')
			list[i].className = 'report_unselected_menu';
		
	link.parentNode.className = 'report_selected_menu';
	link.blur();
	
	list = table.getElementsByTagName('div');
	for(var i = 0; i < list.length; i++)
		if (list[i].className == 'inner_report_menu_div')
			list[i].style.display = 'none';
			
	document.getElementById(divName).style.display='block';
	
	var url = og.getUrl('account', 'update_user_preference', {name: 'custom_report_tab', value: tab});
	og.openLink(url,{hideLoading:true});
}

og.dateselectchange = function(select) {
	var list = select.offsetParent.offsetParent.getElementsByTagName('tr');
	for(var i = 0; i < list.length; i++)
		if (list[i].className == 'dateTr')
			list[i].style.display = select.value == '6'? 'table-row':'none';
}

og.timeslotTypeSelectChange = function(select, genid) {
	document.getElementById(genid + 'gbspan').style.display = select.value > 0? 'none':'inline';
	document.getElementById(genid + 'altgbspan').style.display = select.value > 0? 'inline':'none';
}

og.switchToOverview = function(){
	var opanel = Ext.getCmp('overview-panel');
	opanel.defaultContent = {type: 'panel', data: 'overview'};
	opanel.load(opanel.defaultContent);
};

og.switchToDashboard = function(){
	var opanel = Ext.getCmp('overview-panel');
	opanel.defaultContent = {type: "url", data: og.getUrl('dashboard','index')};
	opanel.load(opanel.defaultContent);
};

og.loading = function() {
	if (!this.loadingCt) {
		this.loadingCt = document.createElement('div');
		this.loadingCt.innerHTML = lang('loading');
		this.loadingCt.className = 'loading-indicator';
		this.loadingCt.style.position = 'absolute';
		this.loadingCt.style.left = '45%';
		this.loadingCt.style.zIndex = 1000000;
		this.loadingCt.style.cursor = 'pointer';
		this.loadingCt.onclick = function() {
			this.style.visibility = 'hidden';
			this.instances = 0;
		};
		this.loadingCt.instances = 0;
		document.body.appendChild(this.loadingCt);
	}
	this.loadingCt.instances++;
	this.loadingCt.style.visibility = 'visible';
};

og.hideLoading = function() {
	this.loadingCt.instances--;
	if (this.loadingCt.instances <= 0) {
		this.loadingCt.style.visibility = 'hidden';
	}
};

og.otherMsgCt = null;
og.showOtherMessage = function(msg, left_percent) {
	if (!og.otherMsgCt) {
		og.otherMsgCt = document.createElement('div');
		og.otherMsgCt.innerHTML = msg;
		og.otherMsgCt.className = 'loading-indicator';
		og.otherMsgCt.style.position = 'absolute';
		og.otherMsgCt.style.left = (left_percent != null ? left_percent : '15%');
		og.otherMsgCt.style.zIndex = 1000000;
		og.otherMsgCt.style.cursor = 'pointer';
		og.otherMsgCt.onclick = function() {
			this.style.visibility = 'hidden';
			this.instances = 0;
		};
		og.otherMsgCt.instances = 0;
		document.body.appendChild(og.otherMsgCt);
	}
	og.otherMsgCt.instances++;
	og.otherMsgCt.style.visibility = 'visible';
};

og.hideOtherMessage = function() {
	og.otherMsgCt.instances--;
	if (og.otherMsgCt.instances <= 0) {
		og.otherMsgCt.style.visibility = 'hidden';
	}
};

og.toggle = function(id, btn) {
	var obj = Ext.get(id);
	if (obj.isDisplayed()) {
		obj.slideOut("t", {duration: 0.5, useDisplay: true});
		if (btn) Ext.fly(btn).replaceClass('toggle_expanded', 'toggle_collapsed');
	} else {
		obj.slideIn("t", {duration: 0.5, useDisplay: true});
		if (btn) Ext.fly(btn).replaceClass('toggle_collapsed', 'toggle_expanded');
	}
};

og.toggleAndBolden = function(id, btn) {
	var obj = Ext.get(id);
	if (obj.isDisplayed()) {
		obj.dom.style.display = 'none';
		//obj.slideOut("t", {duration: 0.5, useDisplay: true});
		if (btn) 
			btn.style.fontWeight = 'normal';
	} else {
		obj.dom.style.display = 'block';
		//obj.slideIn("t", {duration: 0.5, useDisplay: true});
		if (btn) 
			btn.style.fontWeight = 'bold';
	}
};

og.showAndHide = function(idToShow, idsToHide, displayType){
	if (!displayType)
		displayType = 'block';
	var show = document.getElementById(idToShow);
	if(show){
		show.style.display = displayType;
		for(var i = 0; i < idsToHide.length; i++){
			var hide = document.getElementById(idsToHide[i]);
			if (hide) hide.style.display = 'none';
		}
	}
};

og.toggleAndHide = function(id, btn) {
	var obj = Ext.getDom(id);
	if (obj.style.display == 'block') {
		obj.style.display = 'none';
		if (btn) 
			btn.style.display = 'none';
	} else {
		obj.style.display = 'block';
		if (btn) 
			btn.style.display = 'none';
	}
};


og.getUrl = function(controller, action, args) {
	var url = og.hostName + "/";
	url += "?c=" + controller;
	url += "&a=" + action;
	for (var key in args) {
		url += "&" + encodeURIComponent(key) + "=" + encodeURIComponent(args[key]);
	}
	return url;
};

og.filesizeFormat = function(fs) {
	if (fs > 1024 * 1024) {
		var total = Math.round(fs / 1024 / 1024 * 10);
		return total / 10 + "." + total % 10 + " MB";
	} else {
		var total = Math.round(fs / 1024 * 10);
		return total / 10 + "." + total % 10 + " KB";
	}
};


og.makeAjaxUrl = function(url, params) {
	var q = url.indexOf('?');
	var n = url.indexOf('#');
	var ap = "";
	if (url.indexOf("active_project=") < 0) {
		if (Ext.getCmp('workspace-panel')) {
			var ap = "active_project=" + Ext.getCmp('workspace-panel').getActiveWorkspace().id;
		}
	}
	var at = "";
	if (url.indexOf("active_tag=") < 0) {
		if (Ext.getCmp('tag-panel') && Ext.getCmp('tag-panel').getSelectedTag().name != '') {
			var at = "&active_tag=" + encodeURIComponent(Ext.getCmp('tag-panel').getSelectedTag().name);
		}
	}
	if (url.indexOf("ajax=true") < 0) {
		var aj = "&ajax=true";
	} else {
		var aj = "";
	}
	var p = "";
	if (params) {
		if (typeof params == 'string') {
			if (params != ''){
				p = "&" + params;
			}
		} else {
			for (var k in params) {
				p += "&" + k + "=" + params[k];
			}
		}
	}
	
	if (q < 0) {
		if (n < 0) {
			return url + "?" + ap + aj + at + p;
		} else {
			return url.substring(0, n) + "?" + ap + aj + at + (url.substring(n) != ''? "&":"") + url.substring(n) + p;
		}
	} else {
		return url.substring(0, q + 1) + ap + aj + at + (url.substring(q + 1) != ''? "&":"") + url.substring(q + 1) + p;
	}
};

og.createHTMLElement = function(config) {
	var tag = config.tag || 'p';
	var attrs = config.attrs || {};
	var content = config.content || {};
	var elem = document.createElement(tag);
	for (var k in attrs) {
		elem[k] = attrs[k];
	}
	if (typeof content == 'string') {
		elem.innerHTML = content;
	} else {
		for (var i=0; i < content.length; i++) {
			elem.appendChild(og.createHTMLElement(content[i]));
		}
	}
	return elem;
};

og.debug = function(obj, level) {
	if (!level) level = 0;
	if (level > 5) return "";
	var pad = "";
	var str = "";
	for (var i=0; i < level; i++) {
		pad += "  ";
	}
	if (!obj) {
		str = "NULL";
	} else if (typeof obj == 'object') {
		str = "";
		for (var k in obj) {
			str += ",\n" + pad + "  ";
			str += k + ": ";
			str += og.debug(obj[k], level + 1);
		}
		str = "{" + str.substring(1) + "\n" + pad + "}";
	} else if (typeof obj == 'string') {
		str = '"' + obj + '"';
	} else {
		str = obj;
	}
	return str;
};

og.captureLinks = function(id, caller) {
	var links = Ext.select((id?"#" + id + " ":"") + "a.internalLink");
	links.each(function() {
		if (this.dom.href.indexOf('javascript:') == 0) {
			return;
		}
		if (caller && !this.dom.target) {
			this.dom.target = caller.id;
		}
		this.dom.onvalidate = this.dom.onclick;
		this.dom.onclick = function(e) {
			if (typeof this.onvalidate != 'function') {
				var p = true;
			} else {
				try {
					var p = this.onvalidate(e);
				} catch (e) {
				}
			}
			if (p || typeof p == 'undefined') {
				og.openLink(this.href, {caller: this.target})
			}
			return false;
		}
	});
	links = Ext.select((id?"#" + id + " ":"") + "form.internalForm");
	links.each(function() {
		var onsubmit = this.dom.onsubmit;
		this.dom.onsubmit = function() {
			if (onsubmit && !onsubmit()) {
				return false;
			} else {
				var params = Ext.Ajax.serializeForm(this);
				var options = {};
				options[this.method.toLowerCase()] = params;
				og.openLink(this.getAttribute('action'), options);
			}
			return false;
		}
	});
};

og.log = function(msg) {
	if (!og._log) og._log = "";
	og._log += msg + "\n";
};

og.openLink = function(url, options) {
	if (!options) options = {};
	if (typeof options.caller == "object") {
		options.caller = options.caller.id;
	}
	if (!options.caller) {
		var tabs = Ext.getCmp('tabs-panel');
		if (tabs) {
			var active = tabs.getActiveTab();
			if (active) options.caller = active.id;
		}
	}
	if (!options.hideLoading) {
		og.loading();
	}
	var params = options.get || {};
	if (typeof params == 'string' && params.indexOf('current=') < 0) {
		params += "&current=" + options.caller;
	} else {
		if (options.caller && ! params.current)
			params.current = options.caller;
	}
	if (url.substring(url.length - 5) != '.html') {
		// don't add params to HTML pages (this prevents 405 errors from apache 1.3)
		url = og.makeAjaxUrl(url, params);
	}
	if (typeof options.timeout != "undefined") {
		var oldTimeout = Ext.Ajax.timeout;
		Ext.Ajax.timeout = options.timeout;
	}
	var startTime = new Date().getTime();
	Ext.Ajax.request({
		url: url,
		params: options.post,
		callback: function(options, success, response) {
			if (!options.options.hideLoading) {
				og.hideLoading();
			}
			if (success) {
				UnTip(); //fixes ws tooltip is displayed some times when changing page
				if (og)
					clearTimeout(og.triggerFPTTO);
				try {
					try {
						var data = Ext.util.JSON.decode(response.responseText);
					} catch (e) {
						// response isn't valid JSON, display it on the caller panel or new tab
						if (!options.preventPanelLoad) {
							var p = Ext.getCmp(options.caller);
							if (p) {
								p.load(response.responseText);
							} else {
								og.newTab(response.responseText);
							}
						}
					}
					og.processResponse(data, options);
				} catch (e) {
					og.err(e.message);
				}
				var ok = typeof data == 'object' && data.errorCode == 0;
				if (options.postProcess) options.postProcess.call(options.scope || this, ok, data || response.responseText, options.options);
				if (ok) {
					if (options.onSuccess) options.onSuccess.call(options.scope || this, data || response.responseText, options.options);
				} else {
					if (options.onError) options.onError.call(options.scope || this, data || response.responseText, options.options);
				}
			} else {
				if (!options.options.hideErrors && response.status > 0) {
					og.err(lang("http error", response.status, response.statusText));
				}
				if (options.postProcess) options.postProcess.call(options.scope || this, false, data || response.responseText, options.options);
				if (options.onError) options.onError.call(options.scope || this, data || response.responseText, options.options);
			}
			var endTime = new Date().getTime();
			og.log(url + ": " + (endTime - startTime) + " ms");
		},
		caller: options.caller,
		postProcess: options.callback || options.postProcess,
		onSuccess: options.onSuccess,
		onError: options.onError,
		scope: options.scope,
		preventPanelLoad: options.preventPanelLoad,
		options: options
	});
	if (typeof oldTimeout != "undefined") {
		Ext.Ajax.timeout = oldTimeout;
	}
};

/**
 *  This function allows to submit a form containing a file upload without
 *  refreshing the whole page by using an iframe. Any content generated by
 *  the server will be put into the iframe and ignored. You can specify in
 *  the options parameter a callback property saying what to do after the
 *  upload. If the callback property is of type function, then the function
 *  is invoked passing it the options parameter; if it is of type string it
 *  is considered to be an URL to open; if it is of type object it is assumed
 *  to be a content description object (like the ones expected on
 *  og.ContentPanel.load()).
 *  Contents and URLs will be loaded in the panel specified in the panel property of
 *  the options parameter or, if missing, the currently active panel.
 *  Unlike the function og.openLink, this function doesn't send a ajax=true
 *  parameter.
 */
og.submit = function(form, options) {
	if (!options) options = {};
	// create an iframe
	var id = Ext.id();
	var frame = document.createElement('iframe');
	frame.id = id;
	frame.name = id;
	frame.className = 'x-hidden';
	if(Ext.isIE){
	    frame.src = Ext.SSL_SECURE_URL;
	}
	document.body.appendChild(frame);
	if(Ext.isIE){
	   document.frames[id].name = id;
	}
	options.panel = options.panel || Ext.getCmp('tabs-panel').getActiveTab().id;
	function endSubmit() {
		og.hideLoading();
		
		if (typeof options.callback == 'function') {
			options.callback(options);
		} else if (typeof options.callback == 'string') {
			og.openLink(options.callback, {caller: options.panel});
		} else if (typeof options.callback == 'object') {
			Ext.getCmp(options.panel).load(options.callback);
		}
		setTimeout(function(){Ext.removeNode(frame);}, 100);
	}
	Ext.EventManager.on(frame, 'load', function() {
			if (frame.submitted) {
				endSubmit();
			}
		}, frame
	);
	
	form.target = frame.name;
	var url = og.makeAjaxUrl(form.getAttribute('action')).replace(/ajax\=true/g, "upload=true");
	form.setAttribute('action', url);
	og.loading();
	frame.submitted = true;
	form.submit();
	return false;
};

og.processResponse = function(data, options, url) {
	if (!data) return;
	
	// first load scripts
	og.loadScripts(data.scripts, {
		callback: function() {
			if (options) var caller = options.caller;
			
			if (data.errorCode == 2009) {
				if (options) {
					og.LoginDialog.show(options.url, options.options);
				} else {
					og.LoginDialog.show();
				}
				return;
			}
			
			//Fire events
			if (data.events) {
				for (var i=0; i < data.events.length; i++) {
					og.eventManager.fireEvent(data.events[i].name, data.events[i].data);
				}
			}
			
			//Load data
			if (!options || !options.preventPanelLoad){
				//Load data into more than one panel
				if (data.contents) {
					for (var k in data.contents) {
						var p = Ext.getCmp(k);
						if (p) {
							p.load(data.contents[k]);
						}
					}
				}
				
				//Loads data into a single panel
				if (data.current) {
					if (data.current.panel || caller) { //Loads data into a specific panel
						var panelName = data.current.panel ? data.current.panel : caller; //sets data into current.panel, otherwise into caller
						var p = Ext.getCmp(panelName);
						if (p) {
							var tp = p.ownerCt;
							if (tp.setActiveTab) {
								if(!(p.initialConfig.refreshOnWorkspaceChange) && !(p.initialConfig.refreshOnTagChange))
									tp.setActiveTab(p);
							}
							p.load(data.current);
						} else {
							og.newTab(data.current, panelName, data); //Creates the panel if it doesn't exist
						}
					} else { //Loads the data into a new tab
						og.newTab(data.current);
					}
				}
				
				//Show help in content panel if help is available
				if (data.help_content){
					Ext.getCmp('help-panel').load(data.help_content);
				}
			}
			//Show messages if any
			if (data.errorCode != 0 && !options.options.hideErrors) {
				og.err(data.errorMessage);
			} else if (data.errorMessage) {
				og.msg(lang("success"), data.errorMessage);
			}
		}
	});
};

og.newTab = function(content, id, data) {
	if (!data) data = {};
	if (!data.title) {
		data.title = id?lang(id):lang('new tab');
	}
	data.tabTip = data.tabTip || data.title;
	if (data.title.length >= 15) data.title = data.title.substring(0,12) + '...';
	data.iconCls = data.iconCls || data.icon || (id ? 'ico-' + id : 'ico-tab');
	var tp = Ext.getCmp('tabs-panel');
	var t = new og.ContentPanel(Ext.apply(data, {
		closable: true,
		id: id || Ext.id(),
		defaultContent: content
	}));
	tp.add(t);
	tp.setActiveTab(t);
};

/**
 *  adds an event handler to an element, keeping the previous handlers for that event.
 *  	elem: element to which to add the event handler (e.g. document)
 *  	ev: event to handle (e.g. mousedown)
 *  	fun: function that will handle the event. Arguments: (event, handler_id)
 *  	scope: (optional) on which object to run the function
 *      returns: id of the event handler
 */
og.addDomEventHandler = function(elem, ev, fun, scope) {
	if (scope) fun = fun.createCallback(scope);
	if (!elem[ev + "Handlers"]) {
		elem[ev + "Handlers"] = {};
		if (typeof elem["on" + ev] == 'function') {
			elem[ev + "Handlers"]['original'] = elem["on" + ev];
		}
		elem["on" + ev] = function(event) {
			for (var id in this[ev + "Handlers"]) {
				this[ev + "Handlers"][id](event, id);
			}
		};
	}
	var id = Ext.id();
	elem[ev + "Handlers"][id] = fun;
};

/**
 *  Removes an event handler for the event that was added
 *  with og.addDomEventHandler.
 *  	elem: dom element
 *  	ev: event
 *  	id: id of the handler that was returned by og.addDomEventHandler.
 * 
 */
og.removeDomEventHandler = function(elem, ev, id) {
	if (!elem || !id || !ev || !elem[ev + "Handlers"]) return;
	delete elem[ev + "Handlers"][id];
};

og.eventManager = {
	events: new Array(),
	eventsById: new Array(),
	addListener: function(event, callback, scope, options) {
		if (!options) options = {};
		if (!this.events[event] || options.replace) {
			this.events[event] = new Array();
		}
		var id = Ext.id();
		var evobj = {
			id: id,
			callback: callback,
			scope: scope,
			options: options
		};
		this.events[event].push(evobj);
		this.eventsById[id] = evobj;
		return id;
	},
	removeListener: function(id) {
		var ev = this.eventsById[id];
		if (!ev) return;
		this.eventsById[id] = null;
	},
	fireEvent: function(event, arguments) {
		var list = this.events[event];
		if (!list) {
			return;
		}
		for (var i=list.length-1; i >= 0; i--) {
			if (!this.eventsById[list[i].id]) {
				list.splice(i, 1);
			}
			var ret = "";
			try {
				ret = list[i].callback.call(list[i].scope, arguments, list[i].id);
			} catch (e) {
				og.err(e.message);
			}
			if (list[i].options.single || ret == 'remove') {
				list.splice(i, 1);;
			}
		}
	}
};

og.showHelp = function() {
	Ext.getCmp('help-panel').toggleCollapse();
};

og.extractScripts = function(html) {
	var startTime = new Date().getTime();
	var id = Ext.id();
	html += '<span id="' + id + '"></span>';
	Ext.lib.Event.onAvailable(id, function() {
		try {
			var re = /(?:<script([^>]*)?>)((\n|\r|.)*?)(?:<\/script>)/ig;
			var match;
			while (match = re.exec(html)) {
				if (match[2] && match[2].length > 0) {
					try {
						if (window.execScript) {
							window.execScript(match[2]);
						} else {
							window.eval(match[2]);
						}
					} catch (e) {
						og.err(e.message);
					}
				}
			}
			var endTime = new Date().getTime();
			og.log("scripts: " + (endTime - startTime) + " ms");
			var el = document.getElementById(id);
			if (el) { Ext.removeNode(el); }
		} catch (e) { alert(e);}
	});
	
	return html.replace(/(?:<script.*?>)((\n|\r|.)*?)(?:<\/script>)/ig, "");
};

og.clone = function(o) {
	if('object' !== typeof o) {
		return o;
	}
	var c = 'function' === typeof o.pop ? [] : {};
	var p, v;
	for(p in o) {
		v = o[p];
		if('object' === typeof v) {
			c[p] = og.clone(v);
		}
		else {
			c[p] = v;
		}
	}
	return c;
};

og.closeView = function(obj){
	var currentPanel = Ext.getCmp('tabs-panel').getActiveTab();
	currentPanel.back();
};

og.slideshow = function(id) {
	var url = og.getUrl('files', 'slideshow', {fileId: id});
	var top = screen.height * 0.1;
	var left = screen.width * 0.1;
	var width = screen.width * 0.8;
	var height = screen.height * 0.8;
	window.open(url, 'slideshow', 'top=' + top + ',left=' + left + ',width=' + width + ',height=' + height + ',status=no,menubar=no,location=no,toolbar=no,scrollbars=no,directories=no,resizable=yes')
};


og.getParentContentPanel = function(dom) {
	return Ext.fly(dom).findParentNode('.og-content-panel', 100);
};

og.getParentContentPanelBody = function(dom) {
	return Ext.fly(dom).findParentNode('.x-panel-body', 100);
};




og.removeLinkedObjectRow = function (r, tblId, confirm_msg){
	if (confirm(confirm_msg)){
		var i=r.parentNode.parentNode.rowIndex;		
		var tbl = document.getElementById(tblId);
		tbl.deleteRow(i);
		tbl.deleteRow(i-1);
	}
};

og.addLinkedObjectRow = function (tblId,obj_type,obj_id,obj_name, obj_manager, confirm_msg, unlink_msg){
	var tbl = document.getElementById(tblId);
	var cantRows = tbl.rows.length / 2;
	var row1=tbl.insertRow(tbl.rows.length);
	row1.className = 'linkedObject';
	row1.className += (cantRows% 2 == 0) ? 'even' : 'odd';
	
	var td1 = row1.insertCell(0);
	td1.rowSpan = 2;
	td1.style.paddingLeft = 1;
	td1.style.verticalAlign = 'middle';
	td1.style.width = '25px'
	td1.innerHTML = "<input type='hidden' value='"+obj_id+"' name='rel_objects[id_"+ cantRows +"]' />";
	td1.innerHTML += "<input type='hidden' value='"+obj_manager+"' name='rel_objects[type_"+ cantRows +"]' />";
	td1.innerHTML += "<div class='db-ico unknown ico-"+obj_type+ "' title='"+obj_type+"'></div>";
	
	var td2 = row1.insertCell(1);
	
	td2.innerHTML = "<b><span>"+obj_name+"</span></b>";
	
	var row2=tbl.insertRow(tbl.rows.length);
	row2.className = row1.className;
	var td2 = row2.insertCell(0);
	td2.innerHTML = '<a class="internalLink" href="#" onclick="og.removeLinkedObjectRow(this,\''+tblId+'\',\''+confirm_msg+'\')" title="' +unlink_msg+ ' object">' +unlink_msg+ '</a>';
};



/***********************************************************************/
/*********** Extending Ext.PagingToolbar  ******************************/
/***********************************************************************/

og.PagingToolbar	=	function (config) {
	og.PagingToolbar.superclass.constructor.call (this, config);
};

Ext.extend (og.PagingToolbar, Ext.PagingToolbar, {
	// override the private function 'getPageData' so that Ext.PagingToolbar 
        // will read the 'start' parameter returned from server, 
	// and set the specified page number, while presume the default behavior 
        // when the server doesn't return the 'start' parameter.
	// (JSON example).
	getPageData : function(){
 		var total = this.store.getTotalCount();
		var	ap	=	Math.ceil((this.cursor+this.pageSize)/this.pageSize);
		if (this.store.reader.jsonData) {
			var start = parseInt(this.store.reader.jsonData.start);
			// go to the specified page
			ap	=	Math.ceil((start + this.pageSize)/this.pageSize);
			// also set the cursor so that 'prev' and 'next' buttons behave correctly
			this.cursor	= start;
		}

		return {
			total : total,
			activePage : ap,
			pages :  total < this.pageSize ? 1 : Math.ceil(total/this.pageSize)
		};
	}
});

og.getGooPlayerPanel = function(callback) {
	var gppanel = Ext.getCmp('gooplayer-panel');
	if (gppanel) {
		callback();
	} else {
		og.loadScripts([
				og.getScriptUrl('og/ObjectPicker.js'),
				og.getScriptUrl('og/GooPlayer.js')
			], {
			callback: function() {
				og.newTab({
						type: "panel",
						data: "gooplayer",
						config: {
							id: 'gooplayer',
							sound: og.musicSound
						}
					},
					'gooplayer-panel', {
						title: 'GooPlayer',
						icon: 'ico-gooplayer'
					}
				);
				gppanel = Ext.getCmp('gooplayer-panel');
				callback();
			}
		});
	}
	return gppanel;
};

og.playMP3 = function(track) {
	var gppanel = og.getGooPlayerPanel(function() {
		Ext.getCmp('tabs-panel').setActiveTab(gppanel);
		var gooplayer = Ext.getCmp('gooplayer');
		gooplayer.loadPlaylist([track]);
		gooplayer.start();
	});
};

og.queueMP3 = function(track) {
	var gppanel = og.getGooPlayerPanel();
	//Ext.getCmp('tabs-panel').setActiveTab(gppanel);
	var gooplayer = Ext.getCmp('gooplayer');
	gooplayer.queueTrack(track);
};

og.playXSPF = function(id) {
	var gppanel = og.getGooPlayerPanel();
	Ext.getCmp('tabs-panel').setActiveTab(gppanel);
	var gooplayer = Ext.getCmp('gooplayer');
	gooplayer.loadPlaylistFromFile(id, true);
};

 
og.xmlFetchTag = function(xml, tag) {
	var i1 = xml.indexOf("<" + tag + ">");
	var i2 = xml.indexOf("</" + tag + ">");
	if (i1 >= 0 && i2 > i1) {
		return {
			found: true,
			value: xml.substring(i1 + tag.length + 2, i2),
			rest: xml.substring(i2 + tag.length + 3)
		};
	} else {
		return {
			found: false,
			value: "",
			rest: xml
		};
	}
};

og.clean = function(text) {
	return Ext.util.Format.htmlEncode(text);
};

og.removeTags = function(text) {
	return Ext.util.Format.stripTags(text);
};

og.displayFileContents = function(genid, isFull){
	var text = document.getElementById(genid + 'file_contents').innerHTML;
	if (text.length > 1000 && !isFull){
		text = text.substring(0,900);
		text += '&hellip;&nbsp;&nbsp;<a href="#" onclick="og.displayFileContents(\'' + genid + '\',true)">[' + lang('show more') + '&hellip;]</a>';
	}
	document.getElementById(genid + 'file_display').innerHTML = text;
};

og.dashExpand = function(genid,widget_name){
	var widget = document.getElementById(genid + 'widget');
	if (widget){
		var setExpanded = widget.style.display == 'none';
		widget.style.display = (setExpanded) ? 'block':'none';
		var expander = document.getElementById(genid + 'expander');
		expander.className = (setExpanded) ? "dash-expander ico-dash-expanded":"dash-expander ico-dash-collapsed";
		var url = og.getUrl('account', 'update_user_preference', {name: widget_name + '_widget_expanded', value:setExpanded?1:0});
		og.openLink(url,{hideLoading:true});
	}
};

og.closeContextHelp = function(genid,option_name){
	var help = document.getElementById(genid + 'help');
	if (help){
		help.style.display = 'none';
		if(option_name != ''){
			var url = og.getUrl('account', 'update_user_preference', {name: 'show_' + option_name + '_context_help', value:0});
			og.openLink(url,{hideLoading:true});
		}
	}
};

og.billingEditValue = function(id){
	document.getElementById(id + 'bv').style.display = 'none';
	document.getElementById(id + 'bvedit').style.display = 'inline';
	document.getElementById(id + 'edclick').value = 1;
	document.getElementById(id + 'text').focus();
};

og.checkDownload = function(url, checkedOutById, checkedOutBy) {
	var options = {};
	options.preventPanelLoad = true;
	var checkOut = function() {
		og.ExtendedDialog.dialog.destroy();
		og.openLink(url + "&checkout=1&validate=1", options);
	};
	var readOnly = function() {
		og.ExtendedDialog.dialog.destroy();
		og.openLink(url + "&checkout=0&validate=1", options);
	}
	var checkedOutByName = checkedOutBy;
	if (checkedOutByName == 'self') {
		checkedOutByName = lang('you');
	}
	if (checkedOutById > 0) {
		var config = {
			title :lang('checkout notification'),
			y :50,
			id :'checkDownloadDialog',
			layout :'border',
			modal :true,
			height :150,
			width :300,
			resizable :false,
			closeAction :'hide',
			iconCls :'op-ico',
			border :false,
			buttons : [ {
				text :lang('download'),
				handler :readOnly,
				id :'download_button',
				scope :this
			} ],
			html :lang('document checked out by', checkedOutByName),
			dialogItems : [ {
				xtype :'label',
				name :'checked_label',
				id :'checkedout',
				hideLabel :true,
				value :false
			} ]
		};
	} else {
		var config = {
			title :lang('checkout confirmation'),
			y :50,
			id :'checkDownloadDialog',
			layout :'border',
			modal :true,
			height :150,
			width :300,
			resizable :false,
			closeAction :'hide',
			iconCls :'op-ico',
			border :false,
			buttons : [ {
				text :lang('checkout and download'),
				handler :checkOut,
				id :'checkOut_button',
				scope :this
			}, {
				text :lang('download only'),
				handler :readOnly,
				id :'readOnly_button',
				scope :this
			} ],
			html :lang('checkout recommendation'),
			dialogItems : {
				xtype :'label',
				name :'checked_label',
				id :'checkedout',
				hideLabel :true,
				value :false
			}
		};
	}
	og.ExtendedDialog.show(config);
};

og.getScriptUrl = function(script) {
	return og.hostName + "/public/assets/javascript/" + script;
};

og.loadScripts = function(urls, config) {
	if (!config) config = {};
	if (typeof urls == "string") urls = [urls];
	
	// first load scripts
	var scriptsLeft = urls.length;
	var scripts = [];
	for (var i=0; i < urls.length; i++) {
		if (og.loadedScripts[urls[i]]) {
			scriptsLeft--;
		} else {
			og.loadedScripts[urls[i]] = true;
			if (!config.hideLoading) og.loading();
			Ext.Ajax.request({
				url: urls[i],
				callback: function(options, success, response) {
					scriptsLeft--;
					if (!config.hideLoading) og.hideLoading();
					if (success) {
						scripts[options.index] = response.responseText;
					}
				},
				index: i
			});
		}
	}
	var success = {};
	var count = 0;
	var postScript = function() {
		// wait for scripts to load
		if (scriptsLeft > 0) {
			setTimeout(postScript, 100);
			return;
		}
		
		// run scripts
		for (var i=0; i < scripts.length; i++) {
			if (scripts[i]) {
				try {
					if (window.execScript) {
						window.execScript(scripts[i]);
					} else {
						window.eval(scripts[i]);
					}
					success[urls[i]] = true;
					count++;
				} catch (e) {
					og.err(e.message);
				}
			}
		}
		if (typeof config.callback == 'function') {
			config.callback.call(config.scope, count, success);
		}
	};
	postScript();
};

og.loadedScripts = {};

og.ToggleTrap = function(trapid, fsid) {
	if (Ext.isIE) {
		if (!Ext.get(fsid).isDisplayed()) {
			Ext.get(fsid).setDisplayed('block');
		} else {
			Ext.get(fsid).setDisplayed('none');
		}
	}
};

og.FileIsZip = function(mimetype, name) {
	var ix = name.lastIndexOf('.');
	var extension = ix >= 0 ? name.substring(ix + 1) : "";
	return (mimetype == 'application/zip' || mimetype == 'application/x-zip-compressed' || 
			(mimetype == 'application/x-compressed' && extension == 'zip'));
};

og.disableEventPropagation = function(event) { 
	if (Ext.isIE) { 
		window.event.cancelBubble = true; 
	} else {
		event.stopPropagation();
	}
};

