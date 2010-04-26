/**
 *  ContactManager
 */
og.ContactManager = function() {
	var actions;
	this.viewType = "all";
	this.doNotRemove = true;
	this.needRefresh = false;
	
	if (!og.ContactManager.store) {
		og.ContactManager.store = new Ext.data.Store({
	        proxy: new og.GooProxy({
	            url: og.getUrl('contact', 'list_all')
	        }),
	        reader: new Ext.data.JsonReader({
	            root: 'contacts',
	            totalProperty: 'totalCount',
	            id: 'id',
	            fields: [
	                'object_id', 'type', 'name', 'companyId', 'companyName', 'email', 'website', 'jobTitle', 'createdBy', 'createdById', {name: 'createdOn', type: 'date', dateFormat: 'timestamp'}, 'role', 'tags',
	                'department', 'email2', 'email3', 'workWebsite', 'workAddress', 'workPhone1', 'workPhone2', 
	                'homeWebsite', 'homeAddress', 'homePhone1', 'homePhone2', 'mobilePhone','wsIds','workspaceColors','updatedBy','updatedById', {name: 'updatedOn', type: 'date', dateFormat: 'timestamp'}
	            ]
	        }),
	        remoteSort: true,
			listeners: {
				'load': function(result) {
					var d = this.reader.jsonData;
					var ws = og.clean(Ext.getCmp('workspace-panel').getActiveWorkspace().name);
					var tag = og.clean(Ext.getCmp('tag-panel').getSelectedTag().name);
					if (d.totalCount == 0) {
						if (tag) {
							this.fireEvent('messageToShow', lang("no objects with tag message", lang("contacts"), ws, tag));
						} else {
							this.fireEvent('messageToShow', lang("no objects message", lang("contacts"), ws));
						}
					} else {
						this.fireEvent('messageToShow', "");
					}
					og.showWsPaths();
					cm.setHidden(cm.getIndexById('role'), Ext.getCmp('workspace-panel').getActiveWorkspace().id == 0);
				}
			}
	    });
	    og.ContactManager.store.setDefaultSort('name', 'asc');
	}
	this.store = og.ContactManager.store;
	this.store.addListener({messageToShow: {fn: this.showMessage, scope: this}});
    
    //--------------------------------------------
    // Renderers
    //--------------------------------------------

    function renderContactName(value, p, r) {
		if (r.data.type == 'company'){
			name = String.format(
					'<a style="font-size:120%" href="#" onclick="og.openLink(\'{1}\')" title="{2}">{0}</a>',
					og.clean(value), og.getUrl('company', 'view_client', {id: r.data.object_id}), og.clean(r.data.name));
		}
		else{
			name = String.format(
					'<a style="font-size:120%" href="#" onclick="og.openLink(\'{1}\')" title="{2}">{0}</a>',
					og.clean(value), og.getUrl('contact', 'card', {id: r.data.object_id}), og.clean(r.data.name));
			
			if(r.data.companyId != null && r.data.companyId != 0 && r.data.companyName.trim()!=''){
				name += String.format(
					' (<a style="font-size:80%" href="#" onclick="og.openLink(\'{1}\')" title="{2}">{0}</a>)',
					og.clean(r.data.companyName), og.getUrl('company', 'view_client', {id: r.data.companyId}), og.clean(r.data.companyName));
			} //end else
		}// end else
		return String.format('<span class="project-replace">{0}</span>&nbsp;', r.data.wsIds) + name;
    }
    function renderCompany(value, p, r) {
    	return String.format('<a href="#" onclick="og.openLink(\'{1}\', null)">{0}</a>', og.clean(value), og.getUrl('company', 'card', {id: r.data.companyId}));
    }
    function renderEmail(value, p, r) {
    	return String.format('<a href="mailto:{0}">{0}</a>', og.clean(value));
    }
    function renderWebsite(value, p, r) {
    	return String.format('<a href="" onclick="window.open(\'{0}\'); return false">{0}</a>', og.clean(value));
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
	
	function getSelectedIds() {
		var selections = sm.getSelections();
		if (selections.length <= 0) {
			return '';
		} else {
			var ret = '';
			for (var i=0; i < selections.length; i++) {
				ret += "," + selections[i].data.object_id;
//				ret += "," + selections[i].id;
			}	
			return ret.substring(1);
		}
	}
	
	function getSelectedTypes() {
		var selections = sm.getSelections();
		if (selections.length <= 0) {
			return '';
		} else {
			var ret = '';
			for (var i=0; i < selections.length; i++) {
				ret += "," + selections[i].data.type;
			}	
			return ret.substring(1);
		}
	}
	
	function getFirstSelectedType() {
		if (sm.hasSelection()) {
			return sm.getSelected().data.type;
		}
		return '';
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
				actions.tag.setDisabled(true);
				actions.delContact.setDisabled(true);
				actions.editContact.setDisabled(true);
				actions.assignContact.setDisabled(true);
			} else {
				actions.editContact.setDisabled(sm.getCount() != 1);
				if(getFirstSelectedType() == 'contact')
					actions.assignContact.setDisabled(sm.getCount() != 1);
				actions.tag.setDisabled(false);
				actions.delContact.setDisabled(false);
			}
		});
    var cm = new Ext.grid.ColumnModel([
		sm,
		{
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
			width: 200,
			renderer: renderContactName,
			sortable:true
        },
		{
			id: 'role',
			header: lang("role"),
			dataIndex: 'role',
			width: 60,
			renderer: og.clean,
			sortable:false
        },{
			id: 'email',
			header: lang("email"),
			dataIndex: 'email',
			width: 120,
			renderer: renderEmail
		},{
			id: 'tags',
			header: lang("tags"),
			dataIndex: 'tags',
			hidden: true,
			width: 120
        },{
			id: 'department',
			header: lang("department"),
			dataIndex: 'department',
			width: 120,
			hidden: true,
			renderer: og.clean
        },{
			id: 'email2',
			header: lang("email2"),
			dataIndex: 'email2',
			width: 120,
			hidden: true,
			renderer: renderEmail
        },{
			id: 'email3',
			header: lang("email3"),
			dataIndex: 'email3',
			width: 120,
			hidden: true,
			renderer: renderEmail
        },{
			id: 'workWebsite',
			header: lang("workWebsite"),
			dataIndex: 'workWebsite',
			width: 120,
			hidden: true,
			renderer: renderWebsite
        },{
			id: 'workPhone1',
			header: lang("workPhone1"),
			dataIndex: 'workPhone1',
			width: 120,
			hidden: true,
			renderer: og.clean
        },{
			id: 'workPhone2',
			header: lang("workPhone2"),
			dataIndex: 'workPhone2',
			width: 120,
			hidden: true,
			renderer: og.clean
        },{
			id: 'workAddress',
			header: lang("workAddress"),
			dataIndex: 'workAddress',
			width: 120,
			hidden: true,
			renderer: og.clean
        },{
			id: 'homeWebsite',
			header: lang("homeWebsite"),
			dataIndex: 'homeWebsite',
			width: 120,
			hidden: true,
			renderer: renderWebsite
        },{
			id: 'homePhone1',
			header: lang("homePhone1"),
			dataIndex: 'homePhone1',
			width: 120,
			hidden: true,
			renderer: og.clean
        },{
			id: 'homePhone2',
			header: lang("homePhone2"),
			dataIndex: 'homePhone2',
			width: 120,
			hidden: true,
			renderer: og.clean
        },{
			id: 'homeAddress',
			header: lang("homeAddress"),
			dataIndex: 'homeAddress',
			width: 120,
			hidden: true,
			renderer: og.clean
        },{
			id: 'mobilePhone',
			header: lang("mobilePhone"),
			dataIndex: 'mobilePhone',
			width: 120,
			hidden: true,
			renderer: og.clean
        },{
			id: 'updated',
			header: lang("last updated by"),
			dataIndex: 'updatedOn',
			width: 120,
			hidden: true,
			renderer: renderDateUpdated,
			sortable: true
        },{
			id: 'created',
			header: lang("created by"),
			dataIndex: 'createdOn',
			width: 120,
			hidden: true,
			renderer: renderDateCreated,
			sortable: true
		}]);
    cm.defaultSortable = false;

	viewActions = {
			all: new Ext.Action({
				text: lang('view all'),
				handler: function() {
					this.viewType = "all";
					this.load();
				},
				scope: this
			}),
			contacts: new Ext.Action({
				text: lang('contacts'),
				iconCls: "ico-contacts",
				handler: function() {
					this.viewType = "contacts";
					this.load();
				},
				scope: this
			}),
			companies: new Ext.Action({
				text: lang('companies'),
				iconCls: "ico-company",
				handler: function() {
					this.viewType = "companies";
					this.load();
				},
				scope: this
			})
	}	
	actions = {
		newContact: new Ext.Action({
			text: lang('new'),
            tooltip: lang('create contact or client company'),
            iconCls: 'ico-new',
			menu: {items: [
				{text: lang('contact'), iconCls: 'ico-contact', handler: function() {
					var url = og.getUrl('contact', 'add');
					og.openLink(url);
				}},
				{text: lang('company'), iconCls: 'ico-company', handler: function() {
					var url = og.getUrl('company', 'add_client');
					og.openLink(url);
				}}				
			]}
		}),
		delContact: new Ext.Action({
			text: lang('move to trash'),
            tooltip: lang('move selected objects to trash'),
            iconCls: 'ico-trash',
			disabled: true,
			handler: function() {
				if (confirm(lang('confirm move to trash'))) {
					this.load({
						action: 'delete',
						ids: getSelectedIds(),
						types: getSelectedTypes()
					});
					this.getSelectionModel().clearSelections();
				}
			},
			scope: this
		}),
		editContact: new Ext.Action({
			text: lang('edit'),
            tooltip: lang('edit selected object'),
            iconCls: 'ico-new',
			disabled: true,
			handler: function() {
				var url = '';
				if (getFirstSelectedType() == 'contact')
					url = og.getUrl('contact', 'edit', {id:getFirstSelectedId()});
				else
					url = og.getUrl('company', 'edit_client', {id:getFirstSelectedId()});
				og.openLink(url, null);
			},
			scope: this
		}),
		assignContact: new Ext.Action({
			text: lang('assign to project'),
            tooltip: lang('assign contact to project'),
            iconCls: 'ico-workspaces',
			disabled: true,
			handler: function() {
				var url = og.getUrl('contact', 'assign_to_project', {id:getFirstSelectedId()});
				og.openLink(url, null);
			},
			scope: this
		}),
		refresh: new Ext.Action({
			text: lang('refresh'),
            tooltip: lang('refresh desc'),
            iconCls: 'ico-refresh',
			handler: function() {
				og.ContactManager.store.reload();
			},
			scope: this
		}),
		view: new Ext.Action({
			text: lang('view'),
            iconCls: 'ico-view_options',
			disabled: false,
			menu: {items: [
				viewActions.all,
				'-',
				viewActions.contacts,
				viewActions.companies
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
								ids: getSelectedIds(),
								types: getSelectedTypes(),
								tagTag: tag
							});
						},
						scope: this
					}
				}
			})
		}),
		imp_exp: new Ext.Action({
			text: lang('import/export'),
            tooltip: lang('contact import - export'),
            menu: { items: [
            	new Ext.Action({
		            text: lang('contacts'),
		            iconCls: 'ico-contact',
		            menu: { items: [
						{ text: lang('import'), iconCls: 'ico-upload', handler: function() {
							var url = og.getUrl('contact', 'import_from_csv_file', {type:'contact', from_menu:1});
							og.openLink(url);
						}},
						{ text: lang('export'), iconCls: 'ico-download', handler: function() {
							var url = og.getUrl('contact', 'export_to_csv_file', {type:'contact'});
							og.openLink(url);
						}}
					]}
				}),
				new Ext.Action({
					text: lang('companies'),
					iconCls: 'ico-company',
		            menu: { items: [
						{ text: lang('import'), iconCls: 'ico-upload', handler: function() {
							var url = og.getUrl('contact', 'import_from_csv_file', {type:'company', from_menu:1});
							og.openLink(url);
						}},
						{ text: lang('export'), iconCls: 'ico-download', handler: function() {
							var url = og.getUrl('contact', 'export_to_csv_file', {type:'company'});
							og.openLink(url);
						}}
					]}
				})
			]}
		})
    };
    
	og.ContactManager.superclass.constructor.call(this, {
        store: this.store,
		layout: 'fit',
        cm: cm,
        closable: true,
		stripeRows: true,
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
			actions.newContact,
			'-',
			actions.tag,
			actions.delContact,
			actions.editContact,
			actions.assignContact,
			actions.view,
			'-',
			actions.imp_exp
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
};

Ext.extend(og.ContactManager, Ext.grid.GridPanel, {
	load: function(params) {
		if (!params) params = {};
		if (typeof params.start == 'undefined') {
			var start = (this.getBottomToolbar().getPageData().activePage - 1) * og.pageSize;
		} else {
			var start = 0;
		}
		Ext.apply(this.store.baseParams, {
			tag: Ext.getCmp('tag-panel').getSelectedTag().name,
			view_type: this.viewType,
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
		this.innerMessage.innerHTML = text;
	}
});

Ext.reg("contacts", og.ContactManager);