/**
 *  QuickAdd
 *
 */
og.QuickAdd = function(config) {
	og.QuickAdd.superclass.constructor.call(this, Ext.applyIf(config || {}, {
		text: lang('new'),
        tooltip: lang('create an object'),
        iconCls: 'ico-quick-add',
		menu: {items: [
			{id: 'quick-contact', text: lang('contact'), iconCls: 'ico-contact', handler: function() {
				var url = og.getUrl('contact', 'add');
				og.openLink(url/*, {caller: 'contacts-panel'}*/);
			}},
			{id: 'quick-company', text: lang('company'), iconCls: 'ico-company', handler: function() {
				var url = og.getUrl('company', 'add_client');
				og.openLink(url/*, {caller: 'contacts-panel'}*/);
			}},
			{id: 'quick-event', text: lang('event'), iconCls: 'ico-event', handler: function() {
				var url = og.getUrl('event', 'add');
				og.openLink(url/*, {caller: 'calendar-panel'}*/);
			}},
			{id: 'quick-task', text: lang('task'), iconCls: 'ico-task', handler: function() {
				var url = og.getUrl('task', 'add_task');
				og.openLink(url/*, {caller: 'tasks-panel'}*/);
			}},
			{id: 'quick-milestone', text: lang('milestone'), iconCls: 'ico-milestone', handler: function() {
				var url = og.getUrl('milestone', 'add');
				og.openLink(url/*, {caller: 'tasks-panel'}*/);
			}},
			{id: 'quick-weblink', text: lang('webpage'), iconCls: 'ico-webpages', handler: function() {
				var url = og.getUrl('webpage', 'add');
				og.openLink(url/*, {caller: 'webpages-panel'}*/);
			}},
			{id: 'quick-note', text: lang('message'), iconCls: 'ico-message', handler: function() {
				var url = og.getUrl('message', 'add');
				og.openLink(url/*, {caller: 'messages-panel'}*/);
			}},
			{id: 'quick-document', text: lang('document'), iconCls: 'ico-doc', handler: function() {
				var url = og.getUrl('files', 'add_document');
				og.openLink(url/*, {caller: 'documents-panel'}*/);
			}},
			/*{id: 'quick-spreadsheet', text: lang('spreadsheet'), iconCls: 'ico-sprd', handler: function() {
				var url = og.getUrl('files', 'add_spreadsheet');
				og.openLink(url);//, {caller: 'documents-panel'});
			}},*/
			{id: 'quick-presentation', text: lang('presentation'), iconCls: 'ico-prsn', handler: function() {
				var url = og.getUrl('files', 'add_presentation');
				og.openLink(url/*, {caller: 'documents-panel'}*/);
			}},
			{id: 'quick-file', text: lang('upload file'), iconCls: 'ico-upload', handler: function() {
				var url = og.getUrl('files', 'add_file');
				og.openLink(url/*, {caller: 'documents-panel'}*/);
			}},
			{id: 'quick-email', text: lang('email'), iconCls: 'ico-email', handler: function() {
				var url = og.getUrl('mail', 'add_mail');
				og.openLink(url/*, {caller: 'mails-panel'}*/);
			}}
		]}
	}));
	
	// ENABLE / DISABLE MODULES	
	og.eventManager.addListener('config enable_notes_module changed', function(val) {
		if (val == 1) {
			this.menu.items.get('quick-note').show();
		} else {
			this.menu.items.get('quick-note').hide();
		}
	}, this);
	og.eventManager.addListener('config enable_email_module changed', function(val) {
		if (val == 1) {
			this.menu.items.get('quick-email').show();
		} else {
			this.menu.items.get('quick-email').hide();
		}
	}, this);
	og.eventManager.addListener('config enable_contacts_module changed', function(val) {
		if (val == 1) {
			this.menu.items.get('quick-contact').show();
			this.menu.items.get('quick-company').show();
		} else {
			this.menu.items.get('quick-contact').hide();
			this.menu.items.get('quick-company').hide();
		}
	}, this);
	og.eventManager.addListener('config enable_calendar_module changed', function(val) {
		if (val == 1) {
			this.menu.items.get('quick-event').show();
		} else {
			this.menu.items.get('quick-event').hide();
		}
	}, this);
	og.eventManager.addListener('config enable_documents_module changed', function(val) {
		if (val == 1) {
			this.menu.items.get('quick-document').show();
			this.menu.items.get('quick-presentation').show();
			//this.menu.items.get('quick-spreadsheet').show();
			this.menu.items.get('quick-file').show();
		} else {
			this.menu.items.get('quick-document').hide();
			this.menu.items.get('quick-presentation').hide();
			//this.menu.items.get('quick-spreadsheet').hide();
			this.menu.items.get('quick-file').hide();
		}
	}, this);
	og.eventManager.addListener('config enable_tasks_module changed', function(val) {
		if (val == 1) {
			this.menu.items.get('quick-task').show();
			this.menu.items.get('quick-milestone').show();
		} else {
			this.menu.items.get('quick-task').hide();
			this.menu.items.get('quick-milestone').hide();
		}
	}, this);
	og.eventManager.addListener('config enable_weblinks_module changed', function(val) {
		if (val == 1) {
			this.menu.items.get('quick-weblink').show();
		} else {
			this.menu.items.get('quick-weblink').hide();
		}
	}, this);
	/*og.eventManager.addListener('config enable_time_module changed', function(val) {
		if (val == 1) {
			this.menu.items.get('timeslot').show();
		} else {
			this.menu.items.get('timeslot').hide();
		}
	}, this);
	og.eventManager.addListener('config enable_reporting_module changed', function(val) {
		if (val == 1) {
			this.menu.items.get('report').show();
		} else {
			this.menu.items.get('report').hide();
		}
	}, this);*/
};

Ext.extend(og.QuickAdd, Ext.Button, {});