/**
 *  TrashCan
 *
 */
og.TrashCan = function() {

	var actions, moreActions;

	this.doNotRemove = true;
	this.needRefresh = false;

	if (!og.TrashCan.store) {
		og.TrashCan.store = new Ext.data.Store({
			proxy: new og.GooProxy({
				url: og.getUrl('object', 'list_objects', {trashed: "true"})
			}),
			reader: new Ext.data.JsonReader({
				root: 'objects',
				totalProperty: 'totalCount',
				id: 'id',
				fields: [
					'name', 'object_id', 'type', 'tags', 'createdBy', 'createdById',
					{name: 'dateCreated', type: 'date', dateFormat: 'timestamp'},
					'updatedBy', 'updatedById',
					{name: 'dateUpdated', type: 'date', dateFormat: 'timestamp'},
					'deletedBy', 'deletedById',
					{name: 'dateDeleted', type: 'date', dateFormat: 'timestamp'},
					'icon', 'wsIds', 'manager', 'mimeType', 'url'
				]
			}),
			remoteSort: true,
			listeners: {
				'load': function() {
					var d = this.reader.jsonData;
					var ws = og.clean(Ext.getCmp('workspace-panel').getActiveWorkspace().name);
					var tag = og.clean(Ext.getCmp('tag-panel').getSelectedTag().name);
					if (d.totalCount == 0) {
						if (tag) {
							this.fireEvent('messageToShow', lang("no deleted objects with tag message", lang("objects"), ws, tag));
						} else {
							this.fireEvent('messageToShow', lang("no deleted objects message", lang("objects"), ws));
						}
					} else {
						this.fireEvent('messageToShow', "");
					}
					og.showWsPaths();
				}
			}
		});
		og.TrashCan.store.setDefaultSort('dateDeleted', 'desc');
	}
	this.store = og.TrashCan.store;
	this.store.addListener({messageToShow: {fn: this.showMessage, scope: this}});

	function renderName(value, p, r) {
		var projectsString = String.format('<span class="project-replace">{0}</span>&nbsp;', r.data.wsIds);

		var viewUrl = r.data.url;
		
		var actions = '';
		var actionStyle= ' style="font-size:90%;color:#777777;padding-top:3px;padding-left:18px;background-repeat:no-repeat" ';
		if (r.data.type == 'webpage') {
			viewUrl = og.getUrl('webpage', 'view', {id:r.data.object_id});
			actions += String.format('<a class="list-action ico-open-link" href="#" onclick="window.open(\'{0}\')" title="{1}" ' + actionStyle + '> </a>',
				r.data.linkurl, lang('open link in new window', value));
		}
		actions = '<span>' + actions + '</span>';
	
		var name = String.format('<a href="#" onclick="og.openLink(\'{1}\')">{0}</a>', og.clean(value), viewUrl);
		
		return projectsString + name + actions;
	}

	function renderType(value, p, r){
		return String.format('<i>' + lang(value) + '</i>')
	}
	
	function renderIcon(value, p, r) {
		var classes = "db-ico ico-unknown ico-" + r.data.type;
		if (r.data.mimeType) {
			var path = r.data.mimeType.replace(/\//ig, "-").split("-");
			var acc = "";
			for (var i=0; i < path.length; i++) {
				acc += path[i];
				classes += " ico-" + acc;
				acc += "-";
			}
		}
		return String.format('<div class="{0}" title="{1}"/>', classes, lang(r.data.type));
	}

	function renderUser(value, p, r) {
		if (r.data.updatedById) {
			return String.format('<a href="#" onclick="og.openLink(\'{1}\')">{0}</a>', og.clean(value), og.getUrl('user', 'card', {id: r.data.updatedById}));
		} else {
			return lang("n/a");
		}
	}

	function renderAuthor(value, p, r) {
		if (r.data.createdById) {
			return String.format('<a href="#" onclick="og.openLink(\'{1}\')">{0}</a>', og.clean(value), og.getUrl('user', 'card', {id: r.data.createdById}));
		} else {
			return lang("n/a");
		}
	}
	
	function renderDeletedBy(value, p, r) {
		if (r.data.deletedById) {
			return String.format('<a href="#" onclick="og.openLink(\'{1}\')">{0}</a>', og.clean(value), og.getUrl('user', 'card', {id: r.data.deletedById}));
		} else {
			return lang("n/a");
		}
	}
	
	function renderDate(value, p, r) {
		if (!value) {
			return "";
		}
		var now = new Date();
		if (now.dateFormat('Y-m-d') > value.dateFormat('Y-m-d')) {
			return value.dateFormat('M j');
		} else {
			return value.dateFormat('h:i a');
		}
	}

	function getSelectedIds() {
		var selections = sm.getSelections();
		if (selections.length <= 0) {
			return '';
		} else {
			var ret = '';
			for (var i=0; i < selections.length; i++) {
				ret += "," + selections[i].data.manager + ":" + selections[i].data.object_id;
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

	var sm = new Ext.grid.CheckboxSelectionModel();
	sm.on('selectionchange',
		function() {
			if (sm.getCount() <= 0) {
				actions.restore.setDisabled(true);
				actions.deletePermanently.setDisabled(true);
			} else {
				actions.restore.setDisabled(false);
				actions.deletePermanently.setDisabled(false);
			}
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
			id: 'type',
			header: lang('type'),
			dataIndex: 'type',
			width: 80,
        	renderer: renderType,
        	fixed:false,
        	resizable: true,
        	hideable:true,
        	menuDisabled: true
		},{
			id: 'name',
			header: lang("name"),
			dataIndex: 'name',
			width: 300,
			renderer: renderName,
			sortable: true
        },{
        	id: 'user',
        	header: lang('user'),
        	dataIndex: 'updatedBy',
        	width: 120,
        	renderer: renderUser,
        	hidden: true
        },{
			id: 'tags',
			header: lang("tags"),
			dataIndex: 'tags',
			width: 120,
			hidden: true
        },{
			id: 'last',
			header: lang("last update"),
			dataIndex: 'dateUpdated',
			width: 80,
			renderer: renderDate,
			hidden: true
        },{
			id: 'created',
			header: lang("created on"),
			dataIndex: 'dateCreated',
			width: 80,
			hidden: true,
			renderer: renderDate
		},{
			id: 'author',
			header: lang("author"),
			dataIndex: 'createdBy',
			width: 120,
			renderer: renderAuthor,
			hidden: true
		},{
			id: 'deleted',
			header: lang("deleted on"),
			dataIndex: 'dateDeleted',
			width: 80,
			renderer: renderDate,
			sortable: true
		},{
			id: 'deletedBy',
			header: lang("deleted by"),
			dataIndex: 'deletedBy',
			width: 120,
			renderer: renderDeletedBy
		}]);
	cm.defaultSortable = false;

	actions = {
		restore: new Ext.Action({
			text: lang('restore'),
            tooltip: lang('restore selected objects'),
            iconCls: 'ico-restore',
			disabled: true,
			handler: function() {
				if (confirm(lang("confirm restore objects"))) {
					this.load({
						action: 'restore',
						objects: getSelectedIds()
					});
					this.getSelectionModel().clearSelections();
				}
			},
			scope: this
		}),
		deletePermanently: new Ext.Action({
			text: lang('delete'),
            tooltip: lang('delete selected objects permanently'),
            iconCls: 'ico-delete',
            disabled: true,
			handler: function() {
				if (confirm(lang('confirm delete objects permanently'))) {
					this.load({
						action: 'delete_permanently',
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
				this.load();
			},
			scope: this
		})
    };
    
	og.TrashCan.superclass.constructor.call(this, {
		//enableDragDrop: true, //(breaks the checkbox selection)
		ddGroup : 'WorkspaceDD',
		store: this.store,
		layout: 'fit',
		autoExpandColumn: 'name',
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
			actions.restore,
			'-',
			actions.deletePermanently,
			/*'-',actions.refresh,*/
			'->', {
				xtype: 'label',
				id: 'trash_warning',
				text: og.daysOnTrash ? lang('trash emptied periodically', og.daysOnTrash) : ''
			}
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
	og.eventManager.addListener('config days_on_trash changed', this.updateTrashWarning, this);
};

Ext.extend(og.TrashCan, Ext.grid.GridPanel, {
	updateTrashWarning: function(days) {
		og.daysOnTrash = days;
		this.getTopToolbar().items.get('trash_warning').setText(og.daysOnTrash ? lang('trash emptied periodically', og.daysOnTrash) : '');
	},
	
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

Ext.reg("trashcan", og.TrashCan);