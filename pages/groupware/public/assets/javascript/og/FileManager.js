/**
 *  FileManager
 *
 */
og.FileManager = function() {
	var actions;

	this.doNotRemove = true;
	this.needRefresh = false;
	
	this.fields = [
		'name', 'object_id', 'type', 'tags', 'createdBy', 'createdById',
		{name: 'dateCreated', type: 'date', dateFormat: 'timestamp'},
		'updatedBy', 'updatedById',
		{name: 'dateUpdated', type: 'date', dateFormat: 'timestamp'},
		'icon', 'wsIds', 'manager', 'checkedOutById',
		'checkedOutByName', 'mimeType', 'isModifiable',
		'modifyUrl', 'songInfo', 'ftype', 'url'
	];

	og.eventManager.fireEvent('hook_document_classification', this.fields);	
	
	if (!og.FileManager.store) {
		og.FileManager.store = new Ext.data.Store({
			proxy: new og.GooProxy({
				url: og.getUrl('files', 'list_files')
			}),
			reader: new Ext.data.JsonReader({
				root: 'files',
				totalProperty: 'totalCount',
				id: 'id',
				fields:this.fields 
			}),
			remoteSort: true,
			listeners: {
				'load': function(result) {
					var d = this.reader.jsonData;
					var ws = og.clean(Ext.getCmp('workspace-panel').getActiveWorkspace().name);
					var tag = og.clean(Ext.getCmp('tag-panel').getSelectedTag().name);
					if (d.totalCount == 0) {
						if (tag) {
							this.fireEvent('messageToShow', lang("no objects with tag message", lang("documents"), ws, tag));
						} else {
							this.fireEvent('messageToShow', lang("no objects message", lang("documents"), ws));
						}
					} else {
						this.fireEvent('messageToShow', "");
					}
					og.showWsPaths();
				}
			}
		});
		og.FileManager.store.setDefaultSort('dateUpdated', 'desc');
	}
	this.store = og.FileManager.store;
	this.store.addListener({messageToShow: {fn: this.showMessage, scope: this}});
	
	function renderName(value, p, r) {
		var result = '';
		var name = String.format(
			'<a style="font-size:120%" href="#" onclick="og.openLink(\'{2}\')">{0}</a>',
			og.clean(value), r.data.name, og.getUrl('files', 'file_details', {id: r.data.object_id}));
		
		return String.format('<span class="project-replace">{0}</span>&nbsp;', r.data.wsIds) + name;
	}

	function renderIcon(value, p, r) {
		var classes = "db-ico ico-unknown ico-" + r.data.type;
		if (r.data.name.indexOf(".") >= 0) {
			var extension = r.data.name.substring(r.data.name.indexOf(".") + 1);
			classes += " ico-ext-" + extension;
		}
		if (r.data.ftype == 1){
			classes += ' ico-webfile';
		}
		if (r.data.mimeType) {
			var path = r.data.mimeType.replace(/\//g, "-").split("-");
			var acc = "";
			for (var i=0; i < path.length; i++) {
				acc += path[i];
				classes += " ico-" + acc.replace(/\./g, "_");
				acc += "-";
			}
		}
		return String.format('<div class="{0}" />', classes);
	}

	function renderDateUpdated(value, p, r) {
		if (!value) {
			return "";
		}
		var userString = String.format('<a href="#" onclick="og.openLink(\'{1}\')">{0}</a>', r.data.updatedBy, og.getUrl('user', 'card', {id: r.data.updatedById}));
	
		var now = new Date();
		var dateString = '';
		if (now.dateFormat('Y-m-d') > value.dateFormat('Y-m-d')) {
			return lang('last updated by on', userString, value.dateFormat(og.date_format));
		} else {
			return lang('last updated by at', userString, value.dateFormat('h:i a'));
		}
	}
	
	function renderDateCreated(value, p, r) {
		if (!value) {
			return "";
		}
		var userString = String.format('<a href="#" onclick="og.openLink(\'{1}\')">{0}</a>', r.data.createdBy, og.getUrl('user', 'card', {id: r.data.createdById}));
	
		var now = new Date();
		var dateString = '';
		if (now.dateFormat('Y-m-d') > value.dateFormat('Y-m-d')) {
			return lang('last updated by on', userString, value.dateFormat(og.date_format));
		} else {
			return lang('last updated by at', userString, value.dateFormat('h:i a'));
		}
	}

	function renderCheckout(value, p, r) {
		if(r.data.ftype == 0){
			if (value =='')
				return String.format('<div class="ico-unlocked" style="display:block;height:16px;background-repeat:no-repeat;padding-left:18px">'
				+ '<a href="#" onclick="og.openLink(\'{1}\')" title="{2}">{0}</a>', lang('lock'), og.getUrl('files', 'checkout_file', {id: r.id}), lang('checkout description'));
			else if (r.data.checkedOutById == og.loggedUser.id){
				return String.format('<div class="ico-locked" style="display:block;height:16px;background-repeat:no-repeat;padding-left:18px">' +
					'<a href="#" onclick="og.openLink(\'{1}\')">{0}</a>', 
					lang('unlock'), og.getUrl('files', 'undo_checkout', {id: r.id})) + ', ' +
					String.format('<a href="#" onclick="og.openLink(\'{1}\')" title="{2}">{0}</a>', 
					lang('checkin'), og.getUrl('files', 'checkin_file', {id: r.id}), lang('checkin description'))
					 + '</div>';
				}
			else
				return '<div class="ico-locked" style="display:block;height:16px;background-repeat:no-repeat;padding-left:18px">' +
					lang('checked out by', String.format('<a href="#" onclick="og.openLink(\'{1}\')">{0}</a>', 
					r.data.checkedOutByName, og.getUrl('user', 'card', {id: r.data.checkedOutById}))) + '</div>';
		} else {
			return "--";
		}
	}
	
	function renderActions(value, p, r) {
		var actions = '';
		var actionStyle= ' style="font-size:105%;color:#FFFFFF;padding-top:2px;padding-bottom:3px;padding-left:18px;background-repeat:no-repeat;" '; 
		
		if(r.data.ftype == 0){
			if(og.showCheckoutNotification == 0){
				actions += String.format('<a class="list-action ico-download" href="#" onclick="window.open(\'{0}\');" title="{1}" ' + actionStyle + '>.</a>',
						og.getUrl('files', 'download_file', {id: r.id}),lang('download'));
			}else{
				actions += String.format('<a class="list-action ico-download" href="#" onclick="og.checkDownload(\'{0}\', \'{1}\', \'{2}\');" title="{3}" ' + actionStyle + '>.</a>',
				og.getUrl('files', 'download_file', {id: r.id}), r.data.checkedOutById, r.data.checkedOutByName, lang('download'));
			}			
		}else{
			actions += String.format("<a href='{0}' class='list-action ico-open-link' target='_blank'" + actionStyle + "></a>&nbsp;", r.data.url, 'public/assets/themes/default/images/16x16/openlink.png');
		}
		
		if (r.data.isModifiable) {
			actions += String.format(
			'<a class="list-action ico-edit" href="#" onclick="og.openLink(\'{0}\')" title="{1}" ' + actionStyle + '>.</a>',
			r.data.modifyUrl,lang('edit this document'));
		}
		
		if (r.data.mimeType == "audio/mpeg") {
			actions += String.format(
			'<a class="list-action ico-play" href="#" onclick="og.playMP3({0})" title="{1}" ' + actionStyle + '> </a>',
					r.data.songInfo.replace(/'/g, "\\'").replace(/"/g, "'"), lang('play this file'));
			actions += String.format(
			'<a class="list-action ico-queue" href="#" onclick="og.queueMP3({0})" title="{1}" ' + actionStyle + '> </a>',
					r.data.songInfo.replace(/'/g, "\\'").replace(/"/g, "'"), lang('queue this file'));
		} else if (r.data.mimeType == 'application/xspf+xml') {
			actions += String.format(
			'<a class="list-action ico-play" href="#" onclick="og.playXSPF({0})" title="{1}" ' + actionStyle + '> </a>',
					r.id, lang('play this file'));
		} else if (r.data.mimeType == 'prsn') {
			actions += String.format(
			'<a class="list-action ico-slideshow" href="#" onclick="og.slideshow({0})" title="{1}" ' + actionStyle + '></a>',
					r.id, lang('view slideshow'));
		}
		
		if (og.FileIsZip(r.data.mimeType, r.data.name)) {
			actions += String.format(
			'<a class="list-action ico-zip-extract" href="#" onclick="og.openLink(og.getUrl(\'files\', \'zip_extract\', {id:{0}}))" title="{1}" ' + actionStyle + '>.</a>',
			r.data.object_id,lang('extract files'));
		}
		
		if (actions != '')
			actions = '<span>' + actions + '</span>';
			
		return actions;
	}

	function getSelectedIds() {
		var selections = sm.getSelections();
		if (selections.length <= 0) {
			return '';
		} else {
			var ret = '';
			for (var i=0; i < selections.length; i++) {
				ret += "," + selections[i].data.object_id;
			}	
			return ret.substring(1);
		}
	}
	
	function getFirstSelectedId() {
		if (sm.hasSelection()) {
			return sm.getSelected().data.object_id;
		}
		return '';
	}
	
	function zipFiles(zipFileName) {
		if (zipFileName.length == 0) zipFileName = 'new compressed file.zip';
		
		if (zipFileName.lastIndexOf('.zip') == -1 || zipFileName.lastIndexOf('.zip') != zipFileName.length - 4 ) 
			zipFileName += '.zip';
		
		og.openLink(og.getUrl('files', 'list_files', {
			action: 'zip_add',
			filename: zipFileName,
			objects: getSelectedIds()
		}));
		sm.clearSelections();
	}

	var sm = new Ext.grid.CheckboxSelectionModel();
	sm.on('selectionchange',
		function() {
			if (sm.getCount() <= 0) {
				actions.tag.setDisabled(true);
				actions.properties.setDisabled(true);
				actions.zip_add.setDisabled(true);
				actions.del.setDisabled(true);
			} else {
				actions.tag.setDisabled(false);
				actions.properties.setDisabled(sm.getCount() != 1);
				actions.zip_add.setDisabled(false);
				actions.del.setDisabled(false);
			}
			
			args = {};
			args.sm = sm;
			args.actions = actions;
			og.eventManager.fireEvent('hook_classification_enable', args);		
			
	});
	var cm = new Ext.grid.ColumnModel([
		sm,{
        	id: 'icon',
        	header: '&nbsp;',
        	dataIndex: 'icon',
        	width: 28,
        	renderer: renderIcon,
        	fixed:true,
        	resizable: false,
        	hideable:false,
        	menuDisabled: true
        },{
			id: 'name',
			header: lang("name"),
			dataIndex: 'name',
			width: 300,
			renderer: renderName,
			sortable: true
        },{
			id: 'type',
			header: lang('type'),
			dataIndex: 'type',
			width: 120,
			hidden: true
		},{
			id: 'tags',
			header: lang("tags"),
			dataIndex: 'tags',
			width: 120,
			hidden: true
        },{
			id: 'updated',
			header: lang("last updated by"),
			dataIndex: 'dateUpdated',
			width: 120,
			renderer: renderDateUpdated,
			sortable: true
        },{
			id: 'created',
			header: lang("created by"),
			dataIndex: 'dateCreated',
			width: 120,
			hidden: true,
			renderer: renderDateCreated,
			sortable: true
		},{
			id: 'status',
			header: lang("status"),
			dataIndex: 'checkedOutByName',
			width: 120,
			renderer: renderCheckout
		},{
			id: 'actions',
			header: lang("actions"),
			width: 50,
			renderer: renderActions,
			sortable: false
		}]);
	cm.defaultSortable = false;

	og.eventManager.fireEvent('hook_filemanager_columns', cm.config);
	
	actions = {
		newCO: new Ext.Action({
			text: lang('new'),
            tooltip: lang('create an object'),
            iconCls: 'ico-new',
			menu: {items: [
				{text: lang('upload file'), iconCls: 'ico-upload', handler: function() {
					var url = og.getUrl('files', 'add_file');
					og.openLink(url);
				}},'-',
				{text: lang('document'), iconCls: 'ico-doc', handler: function() {
					var url = og.getUrl('files', 'add_document');
					og.openLink(url);
				}},
				{text: lang('presentation'), iconCls: 'ico-prsn', handler: function() {
					var url = og.getUrl('files', 'add_presentation');
					og.openLink(url);
				}}/*,
				{text: lang('spreadsheet') + ' (ALPHA)', iconCls: 'ico-sprd', handler: function() {
					var url = og.getUrl('files', 'add_spreadsheet');
					og.openLink(url);
				}}*/
			]}
		}),
		tag: new Ext.Action({
			text: lang('tag'),
            tooltip: lang('tag selected objects'),
            iconCls: 'ico-tag',
			disabled: true,
			menu: new og.TagMenu({
				listeners: {
					'tagselect': {
						fn: function(tag) {
							this.load({
								action: 'tag',
								objects: getSelectedIds(),
								tagTag: tag
							});
						},
						scope: this
					}
				}
			})
		}),
		properties: new Ext.Action({
			text: lang('properties'),
			tooltip: lang('edit selected file properties'),
			iconCls: 'ico-properties',
			disabled: true,
			handler: function(e) {
				var o = sm.getSelected();
				var url = og.getUrl('files', 'edit_file', {id: o.data.object_id, manager: o.data.manager});
				og.openLink(url);
			}
		}),
		zip_add: new Ext.Action({
			text: lang('compress'),
            tooltip: lang('compress selected files'),
            iconCls: 'ico-zip-add',
			disabled: true,
			handler: function() {
				Ext.Msg.prompt(lang('new compressed file'),
					lang('name'),
					function (btn, text) {
						if (btn == 'ok' && text) {
							zipFiles(text);
						}
					},
					this	
				);
			},
			scope: this
		}),
		del: new Ext.Action({
			text: lang('move to trash'),
            tooltip: lang('move selected objects to trash'),
            iconCls: 'ico-trash',
			disabled: true,
			handler: function() {
				if (confirm(lang('confirm move to trash'))) {
					this.load({
						action: 'delete',
						objects: getSelectedIds()
					});
					this.getSelectionModel().clearSelections();
				}
			},
			scope: this
		}),
		refresh: new Ext.Action({
			text: lang('refresh'),
            tooltip: lang('refresh desc'),
            iconCls: 'ico-refresh',
			handler: function() {
				this.store.reload();
			},
			scope: this
		})
    };
    
	og.FileManager.superclass.constructor.call(this, {
		store: this.store,
		layout: 'fit',
		cm: cm,
		stripeRows: true,
		closable: true,
		/*style: "padding:7px;",*/
		bbar: new og.PagingToolbar({
			pageSize: og.pageSize,
			store: this.store,
			displayInfo: true,
			displayMsg: lang('displaying objects of'),
			emptyMsg: lang("no objects to display")
		}),
		viewConfig: {
			forceFit: true
		},
		sm: sm,
		tbar:[
			actions.newCO,
			'-',
			actions.tag,
			actions.properties,
			actions.zip_add,
			actions.del/*,
			'-',
			actions.refresh*/
		],
		listeners: {
			'render': {
				fn: function() {
					this.innerMessage = document.createElement('div');
					this.innerMessage.className = 'inner-message';
					var msg = this.innerMessage;
					var elem = Ext.get(this.getEl());
					var scroller = elem.select('.x-grid3-scroller');
					scroller.each(function() {
						this.dom.appendChild(msg);
					});
				},
				scope: this
			}
		}
	});
	
	args = {};
	args.actions = actions;
	args.fm = this;
	args.sm = sm;
	og.eventManager.fireEvent('hook_filemanager_actions', args);	

	var tagevid = og.eventManager.addListener("tag changed", function(tag) {
		if (!this.ownerCt) {
			og.eventManager.removeListener(tagevid);
			return;
		}
		if (this.ownerCt.active) {
			this.load({start:0});
		} else {
    		this.needRefresh = true;
    	}
	}, this);
};

Ext.extend(og.FileManager, Ext.grid.GridPanel, {
	load: function(params) {
		if (!params) params = {};
		if (typeof params.start == 'undefined') {
			var start = (this.getBottomToolbar().getPageData().activePage - 1) * og.pageSize;
		} else {
			var start = 0;
		}
		Ext.apply(this.store.baseParams, {
			tag: Ext.getCmp('tag-panel').getSelectedTag().name,
			active_project: Ext.getCmp('workspace-panel').getActiveWorkspace().id
		});
		this.store.load({
			params: Ext.applyIf(params, {
				start: start,
				limit: og.pageSize
			})
		});
		this.needRefresh = false;
	},
	
	activate: function() {
		if (this.needRefresh) {
			this.load({start: 0});
		}
	},
	
	reset: function() {
		this.load({start:0});
	},
	
	showMessage: function(text) {
		if (this.innerMessage) {
			this.innerMessage.innerHTML = text;
		}
	}
});

Ext.reg("files", og.FileManager);


