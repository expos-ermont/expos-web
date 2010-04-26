og.pickObjectForTemplate = function(before) {
	og.ObjectPicker.show(function (objs) {
		if (objs) {
			for (var i=0; i < objs.length; i++) {
				var obj = objs[i].data;
				if (obj.type != 'task' && obj.type != 'milestone') {
					og.msg(lang("error"), lang("object type not supported"), 4, "err");
				} else {
					og.addObjectToTemplate(this, obj);
				}
			}
		}
	}, before, {
		types: {
			'Tasks': true,
			'Milestones': true
		}
	});
};

og.addObjectToTemplate = function(before, obj) {
	var parent = before.parentNode;
	var count = parent.getElementsByTagName('input').length;
	var div = document.createElement('div');
	div.className = "og-add-template-object ico-" + obj.type + (count % 2 ? " odd" : "");
	div.onmouseover = og.templateObjectMouseOver;
	div.onmouseout = og.templateObjectMouseOut;
	div.innerHTML =
		'<input type="hidden" name="objects[' + count++ + ']" value="' + obj.manager + ":" + obj.object_id + '" />' +
		'<span class="name">' + og.clean(obj.name) + '</span>' +
		'<a href="#" onclick="og.removeObjectFromTemplate(this.parentNode)" class="removeDiv" style="display: none;">'+lang('remove')+'</div>';
	parent.insertBefore(div, before);
};


og.removeObjectFromTemplate = function(div) {
	var parent = div.parentNode;
	parent.removeChild(div);
	var inputs = parent.getElementsByTagName('input');
	for (var i=0; i < inputs.length; i++) {
		inputs[i].name = 'objects[' + i + ']';
	}
	var d = parent.firstChild;
	var i=0;
	while (d != null) {
		if (d.tagName == 'DIV') {
			Ext.fly(d).removeClass("odd");
			if (i % 2) {
				Ext.fly(d).addClass("odd");
			}
			i++;
		}
		d = d.nextSibling;
	}
};

og.templateObjectMouseOver = function() {
	var close = this.firstChild;
	while (close && close.className != 'removeDiv') {
		close = close.nextSibling;
	}
	if (close) {
		close.style.display = 'block';
	}
};

og.templateObjectMouseOut = function() {
	var close = this.firstChild;
	while (close && close.className != 'removeDiv') {
		close = close.nextSibling;
	}
	if (close) {
		close.style.display = 'none';
	}
};

og.templateConfirmSubmit = function(genid) {
	var div = document.getElementById(genid + "add_template_objects_div");
	var count = div.getElementsByTagName('input').length;
	if (count == 0) {
		return confirm(lang('confirm template with no objects'));
	}
	return true;
};
