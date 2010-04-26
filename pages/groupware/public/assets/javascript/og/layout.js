Ext.onReady(function(){
	Ext.get("loading").hide();
		
	// fix cursor not showing on message boxs
	Ext.MessageBox.getDialog().on("show", function(d) {
		var div = Ext.get(d.el);
		div.setStyle("overflow", "auto");
		var text = div.select(".ext-mb-textarea", true);
		if (!text.item(0))
			text = div.select(".ext-mb-text", true);
		if (text.item(0))
			text.item(0).dom.select();
	});

	if (og.rememberGUIState) {
		Ext.state.Manager.setProvider(new og.HttpProvider({
			saveUrl: og.getUrl('gui', 'save_state'),
			readUrl: og.getUrl('gui', 'read_state'),
			autoRead: false
		}));
		Ext.state.Manager.getProvider().initState(og.initialGUIState);
	}
	
	today_date = new Date();
	Ext.QuickTips.init();

	// SETUP PANEL LAYOUT
	og.panels = {};
	var panels = [
		og.panels.overview = new og.ContentPanel({
			title: langhtml('overview'),
			id: 'overview-panel',
			iconCls: 'ico-overview',
			refreshOnWorkspaceChange: true,
			refreshOnTagChange: true,
			defaultContent: {
				type: "url",
				data: og.getUrl('dashboard','index')
			},
			initialContent: {
				type: "url",
				data: og.initialURL
			}
		}),
		og.panels.notes = new og.ContentPanel({
			title: lang('messages'),
			id: 'messages-panel',
			iconCls: 'ico-messages',
			refreshOnWorkspaceChange: true,
			defaultContent: {
				type: "panel",
				data: "messages"
			}
		}),
		og.panels.email = new og.ContentPanel({
			title: lang('email tab'),
			id: 'mails-panel',
			iconCls: 'ico-email',
			refreshOnWorkspaceChange: true,
			defaultContent: {
				type: "panel",
				data: "mails"
			}
		}),
		og.panels.contacts = new og.ContentPanel({
			title: lang('contacts'),
			id: 'contacts-panel',
			iconCls: 'ico-contacts',
			refreshOnWorkspaceChange: true,
			defaultContent: {
				type: "panel",
				data: "contacts"
			}
		}),
		og.panels.calendar = new og.ContentPanel({
			title: lang('calendar'),
			id: 'calendar-panel',
			iconCls: 'ico-calendar',
			refreshOnWorkspaceChange: true,
			defaultContent: {
				type: "panel",
				data: "events"
			}
		}),
		og.panels.documents = new og.ContentPanel({
			title: lang('documents'),
			id: 'documents-panel',
			iconCls: 'ico-documents',
			refreshOnWorkspaceChange: true,
			defaultContent: {
				type: "panel",
				data: "files"
			}
		}),
		og.panels.tasks = new og.ContentPanel({
			title: lang('tasks'),
			id: 'tasks-panel',
			iconCls: 'ico-tasks',
			refreshOnWorkspaceChange: true,
			defaultContent: {
				type: "url",
				data: og.getUrl('task','new_list_tasks')
			}
		}),
		og.panels.weblinks = new og.ContentPanel({
			title: lang('web pages'),
			id: 'webpages-panel',
			iconCls: 'ico-webpages',
			refreshOnWorkspaceChange: true,
			defaultContent: {
				type: "panel",
				data: "webpages"
			}
		}),
		og.panels.time = new og.ContentPanel({
			title: lang('time'),
			id: 'time-panel',
			iconCls: 'ico-time',
			refreshOnWorkspaceChange: true,
			defaultContent: {
				type: "url",
				data: og.getUrl('time','index')
			}
		}),
		og.panels.reporting = new og.ContentPanel({
			title: lang('reporting'),
			id: 'reporting-panel',
			iconCls: 'ico-reporting',
			refreshOnWorkspaceChange: true,
			defaultContent: {
				type: "url",
				data: og.getUrl('reporting','index')
			}
		})
	];
	var tab_panel = new Ext.TabPanel({
		id: 'tabs-panel',
		region:'center',
		activeTab: 0,
		enableTabScroll: true,
		items: panels,
		listeners: {
			'render': function() {
				// hide disabled modules
				if (!og.enableNotesModule) Ext.get('tabs-panel__messages-panel').setDisplayed(false);
				if (!og.enableEmailModule) Ext.get('tabs-panel__mails-panel').setDisplayed(false);
				if (!og.enableContactsModule) Ext.get('tabs-panel__contacts-panel').setDisplayed(false);
				if (!og.enableCalendarModule) Ext.get('tabs-panel__calendar-panel').setDisplayed(false);
				if (!og.enableDocumentsModule) Ext.get('tabs-panel__documents-panel').setDisplayed(false);
				if (!og.enableTasksModule) Ext.get('tabs-panel__tasks-panel').setDisplayed(false);
				if (!og.enableWeblinksModule) Ext.get('tabs-panel__webpages-panel').setDisplayed(false);
				if (!og.enableTimeModule) Ext.get('tabs-panel__time-panel').setDisplayed(false);
				if (!og.enableReportingModule) Ext.get('tabs-panel__reporting-panel').setDisplayed(false);
			}
		}
	});

	// ENABLE / DISABLE MODULES	
	og.eventManager.addListener('config enable_notes_module changed', function(val) {
		if (val == 1) {
			Ext.get('tabs-panel__messages-panel').setDisplayed(true);
		} else {
			Ext.get('tabs-panel__messages-panel').setDisplayed(false);
		}
	});
	og.eventManager.addListener('config enable_email_module changed', function(val) {
		if (val == 1) {
			Ext.get('tabs-panel__mails-panel').setDisplayed(true);
		} else {
			Ext.get('tabs-panel__mails-panel').setDisplayed(false);
		}
	});
	og.eventManager.addListener('config enable_contacts_module changed', function(val) {
		if (val == 1) {
			Ext.get('tabs-panel__contacts-panel').setDisplayed(true);
		} else {
			Ext.get('tabs-panel__contacts-panel').setDisplayed(false);
		}
	});
	og.eventManager.addListener('config enable_calendar_module changed', function(val) {
		if (val == 1) {
			Ext.get('tabs-panel__calendar-panel').setDisplayed(true);
		} else {
			Ext.get('tabs-panel__calendar-panel').setDisplayed(false);
		}
	});
	og.eventManager.addListener('config enable_documents_module changed', function(val) {
		if (val == 1) {
			Ext.get('tabs-panel__documents-panel').setDisplayed(true);
		} else {
			Ext.get('tabs-panel__documents-panel').setDisplayed(false);
		}
	});
	og.eventManager.addListener('config enable_tasks_module changed', function(val) {
		if (val == 1) {
			Ext.get('tabs-panel__tasks-panel').setDisplayed(true);
		} else {
			Ext.get('tabs-panel__tasks-panel').setDisplayed(false);
		}
	});
	og.eventManager.addListener('config enable_weblinks_module changed', function(val) {
		if (val == 1) {
			Ext.get('tabs-panel__webpages-panel').setDisplayed(true);
		} else {
			Ext.get('tabs-panel__webpages-panel').setDisplayed(false);
		}
	});
	og.eventManager.addListener('config enable_time_module changed', function(val) {
		if (val == 1) {
			Ext.get('tabs-panel__time-panel').setDisplayed(true);
		} else {
			Ext.get('tabs-panel__time-panel').setDisplayed(false);
		}
	});
	og.eventManager.addListener('config enable_reporting_module changed', function(val) {
		if (val == 1) {
			Ext.get('tabs-panel__reporting-panel').setDisplayed(true);
		} else {
			Ext.get('tabs-panel__reporting-panel').setDisplayed(false);
		}
	});
	
	// BUILD VIEWPORT
	var viewport = new Ext.Viewport({
		layout: 'border',
		stateful: og.rememberGUIState,
		items: [
			new Ext.BoxComponent({
				region: 'north',
				el: 'header'
			}),
			new Ext.BoxComponent({
				region: 'south',
				el: 'footer'
			}),
			/*helpPanel = new og.HelpPanel({
				region: 'east',
				collapsible: true,
				collapsed: true,
				split: true,
				width: 225,
				minSize: 175,
				maxSize: 400,
				id: 'help-panel',
				title: lang('help'),
				iconCls: 'ico-help'
			 }),*/
			 {
				region: 'west',
				id: 'menu-panel',
				title: lang('workspaces'),
				iconCls: 'ico-workspaces',
				split: true,
				width: 200,
				bodyBorder: false,
				minSize: 175,
				maxSize: 400,
				collapsible: true,
				margins: '0 0 0 0',
				layout: 'border',
				stateful: og.rememberGUIState,
				items: [{
					id: 'workspaces-tree',
					xtype: 'wspanel',
					wstree: {
						listeners: {
							workspaceselect: function(ws) {
								og.eventManager.fireEvent('workspace changed', ws);
								og.updateWsCrumbs(ws);
							}
						},
						autoLoadWorkspaces: true
					},
					listeners: {
						render: function() {
							this.getTopToolbar().setHeight(25);
						}
					}
				},{
					xtype: 'tagpanel',
					tagtree: {
						listeners: {
							tagselect: function(tag) {
								og.eventManager.fireEvent('tag changed', tag);
								og.updateWsCrumbsTag(tag);
							}
						},
						autoLoadTags: true
					}
				}]
			},
			Ext.getCmp('tabs-panel')
		 ]
	});
	
    og.captureLinks();
    
    if (og.hasNewVersions) {
    	og.msg(lang('new version notification title'), og.hasNewVersions, 0);
    }
});