/**
 *  WebpageManager
 */
og.WebpageManager = function() {
	var actions;
	this.doNotRemove = true;
	this.needRefresh = false;
	
	if (!og.WebpageManager.store) {
		og.WebpageManager.store = new Ext.data.Store({
	        proxy: new og.GooProxy({
	            url: og.getUrl('webpage', 'list_all')
	        }),
	        reader: new Ext.data.JsonReader({
	            root: 'webpages',
	            totalProperty: 'totalCount',
	            id: 'id',
	            fields: [
	                'title', 'description', 'url', 'tags', 'wsIds', 'updatedBy', 'updatedById',
	                {name: 'updatedOn', type: 'date', dateFormat: 'timestamp'}
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
							this.fireEvent('messageToShow', lang("no objects with tag message", lang("web pages"), ws, tag));
						} else {
							this.fireEvent('messageToShow', lang("no objects message", lang("web pages"), ws));
						}
					} else {
						this.fireEvent('messageToShow', "");
					}
					og.showWsPaths();
				}
			}
	    });
	    og.WebpageManager.store.setDefaultSort('updated', 'desc');
    }
    this.store = og.WebpageManager.store;
    this.store.addListener({messageToShow: {fn: this.showMessage, scope: this}});
    //--------------------------------------------
    // Renderers
    //--------------------------------------------

    
    function renderName(value, p, r) {
		var name = String.format(
			'<a style="font-size:120%" title="{2}" href="#" onclick="og.openLink(\'{1}\')">{0}</a>',
			og.clean(value), og.getUrl('webpage', 'view', {id: r.id}), lang('view weblink'));
		
		var actions = '';
		var actionStyle= ' style="font-size:90%;color:#777777;padding-top:3px;padding-left:18px;background-repeat:no-repeat" '; 
		actions += String.format('<a class="list-action ico-open-link" href="{0}" target="_blank" title="{1}" ' + actionStyle + '> </a>',
			r.data.url.replace(/\"/g, escape("\"")).replace(/\'/g, escape("'")), lang('open link in new window', og.clean(value)));
		actions = '<span>' + actions + '</span>';
		
		var projectsString = String.format('<span class="project-replace">{0}</span>&nbsp;', r.data.wsIds);
	    
		return projectsString + name + actions;
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
    
	function getSelectedIds() {
		var selections = sm.getSelections();
		if (selections.length <= 0) {
			return '';
		} else {
			var ret = '';
			for (var i=0; i < selections.length; i++) {
				ret += "," + selections[i].id;
			}	
			return ret.substring(1);
		}
	}
	
	function getFirstSelectedId() {
		if (sm.hasSelection()) {
			return sm.getSelected().id;
		}
		return '';
	}

	var sm = new Ext.grid.CheckboxSelectionModel();
	sm.on('selectionchange',
		function() {
			if (sm.getCount() <= 0) {
				actions.tag.setDisabled(true);
				actions.delWebpage.setDisabled(true);
				actions.editWebpage.setDisabled(true);
			} else {
				actions.editWebpage.setDisabled(sm.getCount() != 1);
				actions.tag.setDisabled(false);
				actions.delWebpage.setDisabled(false);
			}
		});
    var cm = new Ext.grid.ColumnModel([
		sm,{
			id: 'title',
			header: lang("title"),
			dataIndex: 'title',
			width: 120,
			sortable: true,
			renderer: renderName
        },{
			id: 'description',
			header: lang("description"),
			dataIndex: 'description',
			width: 120,
			renderer: og.clean
		},{
			id: 'tags',
			header: lang("tags"),
			dataIndex: 'tags',
			width: 120
        },{
			id: 'updated',
			header: lang("last updated by"),
			dataIndex: 'updatedOn',
			width: 120,
			renderer: renderDateUpdated,
			sortable: true
        }]);
    cm.defaultSortable = false;
	
	actions = {
		newWebpage: new Ext.Action({
			text: lang('new'),
            tooltip: lang('add new webpage'),
            iconCls: 'ico-webpages',
            handler: function() {
				var url = og.getUrl('webpage', 'add');
				og.openLink(url, null);
			}
		}),
		delWebpage: new Ext.Action({
			text: lang('move to trash'),
            tooltip: lang('move selected objects to trash'),
            iconCls: 'ico-trash',
			disabled: true,
			handler: function() {
				if (confirm(lang('confirm move to trash'))) {
					this.load({
						action: 'delete',
						webpages: getSelectedIds()
					});
				}
			},
			scope: this
		}),
		editWebpage: new Ext.Action({
			text: lang('edit'),
            tooltip: lang('edit selected webpage'),
            iconCls: 'ico-new',
			disabled: true,
			handler: function() {
				var url = og.getUrl('webpage', 'edit', {id:getFirstSelectedId()});
				og.openLink(url, null);
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
		}),
		tag: new Ext.Action({
			text: lang('tag'),
	        tooltip: lang('tag selected webpages'),
	        iconCls: 'ico-tag',
			disabled: true,
			menu: new og.TagMenu({
				listeners: {
					'tagselect': {
						fn: function(tag) {
							this.load({
								action: 'tag',
								webpages: getSelectedIds(),
								tagTag: tag
							});
						},
						scope: this
					}
				}
			})
		})
    };
    
	og.WebpageManager.superclass.constructor.call(this, {
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
            displayMsg: lang('displaying webpages of'),
            emptyMsg: lang("no webpages to display")
        }),
		viewConfig: {
            forceFit: true
        },
		sm: sm,
		tbar:[
			actions.newWebpage,
			'-',
			actions.tag,
			actions.delWebpage,
			actions.editWebpage/*,
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

Ext.extend(og.WebpageManager, Ext.grid.GridPanel, {
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
			params: Ext.apply(params, {
				start: 0,
				limit: og.pageSize,
				tag: Ext.getCmp('tag-panel').getSelectedTag().name,
				active_project: Ext.getCmp('workspace-panel').getActiveWorkspace().id
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

Ext.reg("webpages", og.WebpageManager);