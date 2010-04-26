

og.TagMenu = function(config, tags) {
	if (!config) config = {};
	
	og.TagMenu.superclass.constructor.call(this, Ext.apply(config, {
		cls: 'scrollable-menu',
		items: [ 
			'-', {
			text: lang('add tag'),
			iconCls: 'ico-addtag',
			handler: function() {
				Ext.Msg.prompt(lang('add tag'),
					lang('enter the desired tag'),
					function (btn, text) {
						if (btn == 'ok' && text) {
							this.fireEvent('tagselect', text);
						}
					},
					this	
				);
			},
			scope: this,
			id: lang('add tag')
		}]
	}));
	
	if (Ext.isIE) { // Add scrollbar in IE
		this.getEl().child('ul.x-menu-list').addClass('iemenulist');
		this.getEl().child('ul.x-menu-list').setWidth(this.getEl().child('ul.x-menu-list').getWidth()+20);
	}
	
	this.addEvents({tagselect: true});
	this.tagnames = {};
	if (tags) {
		this.addTags(tags);
	}

	og.eventManager.addListener('tag added', this.addTag, this);
	og.eventManager.addListener('tag deleted', this.removeTag, this);
	
	this.loadTags();
};

Ext.extend(og.TagMenu, Ext.menu.Menu, {

	removeTag: function(tag) {
		var item = this.tagnames[tag.name];
		if (item) {
			this.remove(item);
		}
	},

	addTag : function(tag){
		var exists = this.tagnames[tag.name];
		if (exists) {
			return;
		}
		var item = new Ext.menu.Item({
			text: og.clean(tag.name),
			handler: function() {
				this.fireEvent('tagselect', tag.name);
			},
			scope: this
		});
		var c = this.items.getCount();
		this.insert(c-2, item);
		this.tagnames[tag.name] = item;
		return item;
	},
	
	exists: function(tagname) {
		return this.tagnames[tagname];
	},
	
	addTags: function(tags) {
		for (var i=0; i < tags.length; i++) {
			this.addTag(tags[i]);
		}
	},

	loadTags: function() {
		var tags = Ext.getCmp('tag-panel').getTags();
		this.addTags(tags);
	}
});