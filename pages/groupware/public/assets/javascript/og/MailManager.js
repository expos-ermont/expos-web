/**
 *  MailManager
 *
 */
og.MailManager = function() {
	var actions, moreActions;
	this.accountId = 0;
	this.viewType = "emails";
	this.readType = "all";
	this.stateType = "received";
	this.doNotRemove = true;
	this.needRefresh = false;
	showLoading = true;

	if (!og.MailManager.store) {
		og.MailManager.store = new Ext.data.Store({
			proxy: new og.GooProxy({
				url: og.getUrl('mail', 'list_all'),
				timeout: 0//Ext.Ajax.timeout
			}),
			reader: new Ext.data.JsonReader({
				root: 'messages',
				totalProperty: 'totalCount',
				id: 'id',
				fields: [
					'object_id', 'type', 'accountId', 'accountName', 'hasAttachment', 'title', 'text', {name: 'date', type: 'date', dateFormat: 'timestamp'},
					'projectId', 'projectName', 'userId', 'userName', 'tags', 'workspaceColors','isRead','from','from_email','isDraft','isSent','folder'
				]
			}),
			remoteSort: true,
			listeners: {
				'load': function() {
					var d = this.reader.jsonData;
					var ws = og.clean(Ext.getCmp('workspace-panel').getActiveWorkspace().name);
					var tag = og.clean(Ext.getCmp('tag-panel').getSelectedTag().name);
					if (d.totalCount === 0) {
						if (tag) {
							this.fireEvent('messageToShow', lang("no objects with tag message", lang("emails"), ws, tag));
						} else {
							this.fireEvent('messageToShow', lang("no objects message", lang("emails"), ws));
						}
					} else {
						this.fireEvent('messageToShow', "");
					}
					og.showWsPaths();
				}
			}
		});
		og.MailManager.store.setDefaultSort('date', 'desc');
	}
	this.store = og.MailManager.store;
	this.store.addListener({messageToShow: {fn: this.showMessage, scope: this}});

	function renderName(value, p, r) {
		var name = '';
		var bold = 'font-weight:normal;';
		if (!r.data.isRead) {bold = 'font-weight:600;';}
		var strAction = 'view';
		
		if (r.data.isDraft) {
			strDraft = "<span style='font-size:80%;color:red'>"+lang('draft')+"&nbsp;</style>";			
			strAction = 'edit_mail';
		}
		else { strDraft = ''; }
		
		var subject = og.clean(value.trim()) || '<i>' + lang("no subject") + '</i>';
		
		name = String.format(
				'{4}<a style="font-size:120%;{3}" href="#" onclick="og.openLink(\'{1}\')" title="{2}">{0}</a>',
				subject, og.getUrl('mail', strAction, {id: r.data.object_id}), og.clean(r.data.text),bold,strDraft);
				
		if (r.data.isSent) {
			name = String.format('<span class="db-ico ico-sent" style="padding-left:18px" title="{1}">{0}</span>',name,lang("mail sent"));
		}
		
		var projectstring = String.format('<span class="project-replace">{0}</span>&nbsp;', r.data.projectId);
		
		var text = '';
		if (r.data.text != ''){
			text = '&nbsp;-&nbsp;<span style="color:#888888;white-space:nowrap">';
			text += og.clean(r.data.text) + "</span></i>";
		}
		
		return projectstring + name + text;
	}

		
	function renderFrom(value, p, r){
		var bold = 'font-weight:normal;';
		var strAction = 'view';
		
		if (r.data.isDraft) strAction = 'edit_mail';
		if (!r.data.isRead) bold = 'font-weight:600;';
		
		var sender = og.clean(value.trim()) || '<i>' + lang("no sender") + '</i>';
		
		name = String.format(
				'<a style="font-size:120%;{3}" href="#" onclick="og.openLink(\'{1}\')" title="{2}">{0}</a>',
				sender, og.getUrl('mail', strAction, {id: r.data.object_id}), og.clean(r.data.from_email),bold);
		return name;
	}
	
	
	function renderIcon(value, p, r) {
		if (r.data.projectId.length > 0)
			return '<div class="db-ico ico-email"></div>';
		else
			return String.format('<a href="#" onclick="og.openLink(\'{0}\')" title={1}><div class="db-ico ico-classify"></div></a>', og.getUrl('mail', 'classify', {id: r.data.object_id}), lang('classify'));
	}

	function renderAttachment(value, p, r){
		if (value)
			return '<div class="db-ico ico-attachment"></div>';
		else
			return '';
	}

	function renderAccount(value, p, r) {
		return String.format('<a href="#" onclick="og.eventManager.fireEvent(\'mail account selected\',\'{1}\')">{0}</a>', og.clean(value), r.data.accountId);
	}
	
	function renderFolder(value, p, r) {
		if (r.data.folder != 'undefined')
			return r.data.folder;
		else
			return '';
	}
	
	function renderDate(value, p, r) {
		if (!value) {
			return "";
		}

		var now = new Date();
		if (now.dateFormat('Y-m-d') > value.dateFormat('Y-m-d')) {
			return value.dateFormat(og.date_format + ' h:i a');
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
				ret += "," + selections[i].data.object_id;
			}	
			return ret.substring(1);
		}
	}
	
	function getSelectedReadTypes() {
		var selections = sm.getSelections();
		if (selections.length <= 0) {
			return '';
		} else {
			var read = false;
			var unread = false;
			for (var i=0; i < selections.length; i++) {
				if (selections[i].data.isRead) read = true;
				if (!selections[i].data.isRead) unread = true;
				if (read && unread) return 'all';
			}	
			if (read) return 'read';
			else return 'unread';
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
				actions.del.setDisabled(true);
				emailActions.markAsRead.setDisabled(true);				
				emailActions.markAsUnRead.setDisabled(true);				
			} else {
				actions.tag.setDisabled(false);
				actions.del.setDisabled(false);
				
				var selTypes = getSelectedTypes();
				if (/message/.test(selTypes)){//read/unread functionality not yet implemented in messages
					emailActions.markAsRead.setDisabled(true);
					emailActions.markAsUnRead.setDisabled(true);
				}else {								
					emailActions.markAsRead.setDisabled(false);
					emailActions.markAsUnRead.setDisabled(false);				
					var selReadTypes = getSelectedReadTypes();
					
					if (selReadTypes == 'read') emailActions.markAsRead.setDisabled(true);
					else if (selReadTypes == 'unread') emailActions.markAsUnRead.setDisabled(true);	
				}
				
			}
		});
	
	var cm = new Ext.grid.ColumnModel([
		sm,{
			id: 'icon',
			header: '&nbsp;',
			dataIndex: 'type',
			width: 28,
        	renderer: renderIcon,
        	fixed:true,
        	resizable: false,
        	hideable:false,
        	menuDisabled: true
		},{
			id: 'hasAttachment',
			header: '&nbsp;',
			dataIndex: 'hasAttachment',
			width: 24,
        	renderer: renderAttachment,
        	fixed:true,
        	resizable: false,
        	hideable:false,
        	menuDisabled: true
		},{
			id: 'from',
			header: lang("from"),
			dataIndex: 'from',
			width: 120,
			renderer: renderFrom
        },{
			id: 'title',
			header: lang("title"),
			dataIndex: 'title',
			width: 250,
			renderer: renderName
        },{
			id: 'account',
			header: lang("account"),
			dataIndex: 'accountName',
			width: 60,
			renderer: renderAccount
        },{
			id: 'tags',
			header: lang("tags"),
			dataIndex: 'tags',
			width: 60
        },{
			id: 'date',
			header: lang("date"),
			dataIndex: 'date',
			width: 60,
			sortable: true,
			renderer: renderDate
        },{
			id: 'folder',
			header: lang("folder"),
			dataIndex: 'folderName',
			width: 60,
			sortable: true,
			renderer: renderFolder
        }]);
	cm.defaultSortable = false;

	moreActions = {};
	
	viewActions = {
			all: new Ext.Action({
				text: lang('view all'),
				handler: function() {
					this.viewType = "emails";
					this.readType = "all";
					this.stateType = "all";
					toggleButtons(false, false, false, false)
					
        			this.store.baseParams = {
					      read_type: this.readType,
					      view_type: this.viewType,
					      state_type : this.stateType,
					      tag: Ext.getCmp('tag-panel').getSelectedTag().name,
						  active_project: Ext.getCmp('workspace-panel').getActiveWorkspace().id
					    };
					this.load();
				},
				scope: this
			}),
			emails: new Ext.Action({
				text: lang('all emails'),
				iconCls: "ico-email",
				handler: function() {
					this.viewType = "emails";
					this.readType = "all";
					this.stateType = "all";
					this.accountId = 0;
					
        			this.store.baseParams = {
					      read_type: this.readType,
					      view_type: this.viewType,
					      state_type : this.stateType,
					      tag: Ext.getCmp('tag-panel').getSelectedTag().name,
						  active_project: Ext.getCmp('workspace-panel').getActiveWorkspace().id,
						  account_id: this.accountId
					    };
					this.load();
				},
				scope: this,
				menu: new og.EmailAccountMenu({
					listeners: {
						'accountselect': {
							fn: function(account) {
								this.viewType = "emails";
								this.readType = "all";
								this.stateType = "all";
								this.accountId = account;
								
			        			this.store.baseParams = {
								      read_type: this.readType,
								      view_type: this.viewType,
								      state_type : this.stateType,
								      tag: Ext.getCmp('tag-panel').getSelectedTag().name,
									  active_project: Ext.getCmp('workspace-panel').getActiveWorkspace().id,
									  account_id: this.accountId
								    };
								this.load();
							},
							scope: this
						}
					}
				},{},"view")
			}),
			unreademails: new Ext.Action({
				text: lang('unread emails'),
				iconCls: "ico-email",
				handler: function() {
					this.readType = "unreaded";
					this.viewType = "emails";
					this.stateType = "all";
					this.accountId = 0;
					
        			this.store.baseParams = {
					      read_type: this.readType,
					      view_type: this.viewType,
					      state_type : this.stateType,
					      tag: Ext.getCmp('tag-panel').getSelectedTag().name,
						  active_project: Ext.getCmp('workspace-panel').getActiveWorkspace().id,
						  account_id: this.accountId
					    };
					this.load();
				},
				scope: this,
				menu: new og.EmailAccountMenu({
					listeners: {
						'accountselect': {
							fn: function(account) {
								this.viewType = "emails";
								this.readType = "unreaded";
								this.stateType = "all";
								this.accountId = account;
								
			        			this.store.baseParams = {
								      read_type: this.readType,
								      view_type: this.viewType,
								      state_type : this.stateType,
								      tag: Ext.getCmp('tag-panel').getSelectedTag().name,
									  active_project: Ext.getCmp('workspace-panel').getActiveWorkspace().id,
									  account_id: this.accountId
								    };
								this.load();
							},
							scope: this
						}
					}
				},{},"view")
			}),
			drafts: new Ext.Action({
				text: lang('draft'),
				iconCls: "ico-email",
				handler: function() {
					this.readType = "all";
					this.stateType = "draft";
					this.viewType = "emails";
					this.accountId = 0;
					
        			this.store.baseParams = {
					      read_type: this.readType,
					      view_type: this.viewType,
					      state_type : this.stateType,
					      tag: Ext.getCmp('tag-panel').getSelectedTag().name,
						  active_project: Ext.getCmp('workspace-panel').getActiveWorkspace().id,
						  account_id: this.accountId
					    };
					this.load();
				},
				scope: this,
				menu: new og.EmailAccountMenu({
					listeners: {
						'accountselect': {
							fn: function(account) {
								this.viewType = "emails";
								this.stateType = "draft";
								this.readType = "all";
								this.accountId = account;
								
			        			this.store.baseParams = {
								      read_type: this.readType,
								      view_type: this.viewType,
								      state_type : this.stateType,
								      tag: Ext.getCmp('tag-panel').getSelectedTag().name,
									  active_project: Ext.getCmp('workspace-panel').getActiveWorkspace().id,
									  account_id: this.accountId
								    };
								this.load();
							},
							scope: this
						}
					}
				},{},"view")
			}),
			unclassified: new Ext.Action({
				text: lang('unclassified emails'),
				iconCls: "ico-classify",
				handler: function() {
					this.viewType = "unclassified";
					this.accountId = 0;
					
        			this.store.baseParams = {
					      read_type: this.readType,
					      view_type: this.viewType,
					      state_type : this.stateType,
					      tag: Ext.getCmp('tag-panel').getSelectedTag().name,
						  active_project: Ext.getCmp('workspace-panel').getActiveWorkspace().id,
						  account_id: this.accountId
					    };
					this.load();
				},
				scope: this,
				menu: new og.EmailAccountMenu({
					listeners: {
						'accountselect': {
							fn: function(account) {
								this.viewType = "unclassified";
								this.accountId = account;
								
			        			this.store.baseParams = {
								      read_type: this.readType,
								      view_type: this.viewType,
								      state_type : this.stateType,
								      tag: Ext.getCmp('tag-panel').getSelectedTag().name,
									  active_project: Ext.getCmp('workspace-panel').getActiveWorkspace().id,
									  account_id: this.accountId
								    };
								this.load();
							},
							scope: this
						}
					}
				},{},"view")
			})
		};
	
	emailActions = {
			addAccount: new Ext.Action({
				text: lang('add mail account'),
				handler: function(e) {
					var url = og.getUrl('mail', 'add_account');
					og.openLink(url);
				}
			}),			
			markAsRead: new Ext.Action({
				text: lang('mark read'),
	            tooltip: lang('mark read'),
	            iconCls: 'ico-mail-mark-read',
				disabled: true,
				handler: function() {
					this.load({
						action: 'markAsRead',
						ids: getSelectedIds(),
						types: getSelectedTypes()
					});
				},
				scope: this
			}),
			markAsUnRead: new Ext.Action({
				text: lang('mark unread'),
	            tooltip: lang('mark unread'),
	            iconCls: 'ico-mail-mark-unread',
				disabled: true,
				handler: function() {
					this.load({
						action: 'markAsUnRead',
						ids: getSelectedIds(),
						types: getSelectedTypes()
					});
				},
				scope: this
			}),
			editAccount: new Ext.Action({
				text: lang('edit account'),
	            tooltip: lang('edit email account'),
				disabled: false,
				menu: new og.EmailAccountMenu({
					listeners: {
						'accountselect': {
							fn: function(account) {
								var url = og.getUrl('mail', 'edit_account', {id: account});
								og.openLink(url);
							},
							scope: this
						}
					}
				},{},"edit")
			})
		};
	
	actions = {
		newCO: new Ext.Action({
			text: lang('new'),
            tooltip: lang('create an email'),
            iconCls: 'ico-new',
            handler: function() {
            	var url = og.getUrl('mail', 'add_mail');
            	og.openLink(url, null);
            }
		}),
		inbox_email: new Ext.Action({
	        text: lang('inbox'),
	        toggleGroup : 'filter_option',
	        enableToggle: true,
	        pressed: true,
	        id: 'inbox_btn',
	        toggleHandler: function(item, pressed) {
	        			if(pressed){
							this.stateType = "received";	
							this.viewType = "emails";
		        			this.accountId = 0;
		        			this.store.baseParams = {
							      read_type: this.readType,
							      view_type: this.viewType,
							      state_type : this.stateType,
							      tag: Ext.getCmp('tag-panel').getSelectedTag().name,
								  active_project: Ext.getCmp('workspace-panel').getActiveWorkspace().id,
								  account_id: this.accountId
							    };
							this.load();						
	        			} 
					},			
			scope: this
    	}),
		sent_email: new Ext.Action({
	        text: lang('sent'),
	        toggleGroup : 'filter_option',
	        enableToggle: true,
	        pressed: false,
	        id: 'sent_btn',
	        toggleHandler: function(item, pressed) {
        		if(pressed){
					this.stateType = "sent";
					this.viewType = "emails";
        			this.accountId = 0;
        			this.store.baseParams = {
					      read_type: this.readType,
					      view_type: this.viewType,
					      state_type : this.stateType,
					      tag: Ext.getCmp('tag-panel').getSelectedTag().name,
						  active_project: Ext.getCmp('workspace-panel').getActiveWorkspace().id,
						  account_id: this.accountId
					    };
					this.load();
        		} 
			},
			scope: this
    	}),
    	
		draft_email: new Ext.Action({
	        text: lang('draft'),
	        toggleGroup : 'filter_option',
	        enableToggle: true,
	        pressed: false,
	        id: 'draft_btn',
			toggleHandler: function(item, pressed) {
				if(pressed){
					this.stateType = "draft";
					this.viewType = "emails";
        			this.accountId = 0;
        			this.store.baseParams = {
					      read_type: this.readType,
					      view_type: this.viewType,
					      state_type : this.stateType,
					      tag: Ext.getCmp('tag-panel').getSelectedTag().name,
						  active_project: Ext.getCmp('workspace-panel').getActiveWorkspace().id,
						  account_id: this.accountId
					    };
					this.load();
        		} 
			},
			scope: this
    	}),
	
		unread_email: new Ext.Action({
	        text: lang('unread'),
	        enableToggle: true,
	        pressed: false,
	        id: 'unread_btn',
	        toggleHandler: function(item, pressed) {
	        			if(pressed){							
							this.readType = "unreaded";						
	        			} else {
	        				this.readType = "all";
	        			}
	        			this.viewType = "emails";
	        			this.accountId = 0;
	        			
	        			this.store.baseParams = {
					      read_type: this.readType,
					      view_type: this.viewType,
					      state_type : this.stateType,
					      tag: Ext.getCmp('tag-panel').getSelectedTag().name,
						  active_project: Ext.getCmp('workspace-panel').getActiveWorkspace().id,
						  account_id: this.accountId
					    };
						
	        			this.load();
					},
			scope: this
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
		checkMails: new Ext.Action({
				text: lang('check mails'),
				iconCls: 'ico-check_mails',
				handler: function() {
					this.load({
						action: "checkmail"
					});
					this.action = "";
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
		view: new Ext.Action({
			text: lang('view'),
            iconCls: 'ico-view_options',
			disabled: false,
			menu: {items: [
				viewActions.all,
				'-',
				//viewActions.messages,
				viewActions.unreademails,
				//viewActions.drafts,
				viewActions.emails,
				viewActions.unclassified
			]}
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
						ids: getSelectedIds(),
						types: getSelectedTypes()
					});
					this.getSelectionModel().clearSelections();
				}
			},
			scope: this
		}),
		email: new Ext.Action({
			text: lang('email actions'),
            tooltip: lang('more email actions'),
            iconCls: 'ico-email_menu',
			disabled: false,
			menu: {items: [
				emailActions.markAsRead,
				emailActions.markAsUnRead,
				emailActions.addAccount,
				emailActions.editAccount
			]}
		})
    };
	this.actionRep = actions;
    
	og.MailManager.superclass.constructor.call(this, {
		store: this.store,
		layout: 'fit',
		cm: cm,
		//id: 'message-manager',
		stripeRows: true,
		closable: true,
		loadMask: false,
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
			actions.inbox_email,
			actions.sent_email,
			actions.draft_email,
			actions.unread_email,
			
			'-',
			/*actions.refresh,
			'-',*/
			actions.view,
			actions.email,
			'-',
			actions.tag,
			actions.del,
			'-',
			actions.checkMails

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
	
	function toggleButtons(inb, sent, dra, unr) {
		Ext.getCmp('inbox_btn').toggle(inb);
		Ext.getCmp('sent_btn').toggle(sent);
		Ext.getCmp('draft_btn').toggle(dra);
		Ext.getCmp('unread_btn').toggle(unr);
	}
	
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


Ext.extend(og.MailManager, Ext.grid.GridPanel, {
	load: function(params) {
		if (!params) params = {};
		var start;
		if (typeof params.start == 'undefined') {
			start = (this.getBottomToolbar().getPageData().activePage - 1) * og.pageSize;
		} else {
			start = 0;
		}
		this.store.baseParams = {
					      read_type: this.readType,
					      view_type: this.viewType,
					      state_type : this.stateType,
					      tag: Ext.getCmp('tag-panel').getSelectedTag().name,
						  active_project: Ext.getCmp('workspace-panel').getActiveWorkspace().id,
						  account_id: this.accountId
					    };
		this.store.load({
			params: Ext.apply(params, {
				start: start,
				limit: og.pageSize				
			})
		});
	},
	resetVars: function(){
		this.viewUnclassified = false;
		this.accountId = 0;
		this.viewType = "emails";
		this.readType = "all";
		this.stateType = "received";
	},
	
	activate: function() {
		if (this.needRefresh) {
			this.load({start:0});
		}
	},
	
	reset: function() {
		this.load({start:0});
	},
	
	showMessage: function(text) {
		this.innerMessage.innerHTML = text;
	}
});


Ext.reg("mails", og.MailManager);