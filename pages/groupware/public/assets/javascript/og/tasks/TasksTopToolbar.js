/**
 *  TaskManager
 *
 */
 
og.TasksTopToolbar = function(config) {
	Ext.applyIf(config,{
			id: "tasksPanelTopToolbarObject",
			renderTo: "tasksPanelTopToolbar",
			height: 28,
			style:"border:0px none"
		});
		
	og.TasksTopToolbar.superclass.constructor.call(this, config);
	
	var allTemplates = [];
	var allTemplatesArray = Ext.util.JSON.decode(document.getElementById(config.allTemplatesHfId).value);
	if (allTemplatesArray && allTemplatesArray.length > 0){
		for (var i = 0; i < allTemplatesArray.length; i++){
			allTemplates[allTemplates.length] = {text: allTemplatesArray[i].t,
				iconCls: 'ico-template',
				handler: function() {
					var url = og.getUrl('template', 'instantiate', {id: this.id});
					og.openLink(url);
				},
				scope: allTemplatesArray[i]
			};
		}
	}
	
	var menuItems = [
		{text: lang('new milestone'), iconCls: 'ico-milestone', handler: function() {
			var url = og.getUrl('milestone', 'add');
			og.openLink(url);
		}},
		{text: lang('new task'), iconCls: 'ico-task', handler: function() {
			var additionalParams = {};
			var toolbar = Ext.getCmp('tasksPanelTopToolbarObject');
			if (toolbar.filterNamesCompaniesCombo.isVisible()){
				var value = toolbar.filterNamesCompaniesCombo.getValue();
				if (value){
					var split = value.split(':');
					if (split[0] > 0 || split[1] > 0){
						additionalParams.assigned_to = value;
					}
				}
			}
			var url = og.getUrl('task', 'add_task');
			og.openLink(url, {post:additionalParams});
		}},/*
		{text: lang('new task time report'), iconCls: 'ico-reporting', handler: function() {
			var url = og.getUrl('reporting', 'total_task_times_p');
			og.openLink(url);
		}},*/
		'-'];
	
	var projectTemplates = [];
	var projectTemplatesArray = Ext.util.JSON.decode(document.getElementById(config.projectTemplatesHfId).value);
	if (projectTemplatesArray && projectTemplatesArray.length > 0){
		for (var i = 0; i < projectTemplatesArray.length; i++){
			projectTemplates[projectTemplates.length] = {text: projectTemplatesArray[i].t,
				iconCls: 'ico-template',
				handler: function() {
					var url = og.getUrl('template', 'instantiate', {id: this.id});
					og.openLink(url);
				},
				scope: projectTemplatesArray[i]
			};
		}
		projectTemplates[projectTemplates.length] = '-';
		menuItems = menuItems.concat(projectTemplates);
	}
	
	menuItems = menuItems.concat([{
		text: lang('all'),
		iconCls: 'ico-template',
		cls: 'scrollable-menu',
		menu: {
			items: allTemplates
		}}]);
	
	var butt = new Ext.Button({
		iconCls: 'ico-new',
		text: lang('new'),
		menu: {
			cls:'scrollable-menu',
			items: menuItems
		}
	});
	
	var actions = {
		tag: new Ext.Action({
			text: lang('tag'),
            tooltip: lang('tag selected objects'),
            iconCls: 'ico-tag',
			disabled: true,
			menu: new og.TagMenu({
				listeners: {
					'tagselect': {
						fn: function(tag) {
							ogTasks.executeAction('tag', null, tag);
						},
						scope: this
					}
				}
			})
		}),
		del: new Ext.Action({
			text: lang('move to trash'),
            tooltip: lang('move selected objects to trash'),
            iconCls: 'ico-trash',
			disabled: true,
			handler: function() {
				if (confirm(lang('confirm move to trash'))) {
					ogTasks.executeAction('delete');
				}
			},
			scope: this
		}),
		complete: new Ext.Action({
			text: lang('do complete'),
            tooltip: lang('complete selected tasks'),
            iconCls: 'ico-complete',
			disabled: true,
			handler: function() {
				ogTasks.executeAction('complete');
			},
			scope: this
		})
	};
	this.actions = actions;
	
    

    this.filtercombo = new Ext.form.ComboBox({
        store: new Ext.data.SimpleStore({
	        fields: ['value', 'text'],
	        data : [['no_filter','--' + lang('no filter') + '--']
	        	,['created_by',lang('created by')]
	        	,['completed_by', lang('completed by')]
	        	,['assigned_to', lang('assigned to')]
	        	,['assigned_by', lang('assigned by')]
	        	,['milestone', lang('milestone')]
	        	,['priority', lang('priority')]]
	    }),
        displayField:'text',
        typeAhead: true,
        mode: 'local',
        triggerAction: 'all',
        selectOnFocus:true,
        width:100,
        valueField: 'value',
        listeners: {
        	'select' : function(combo, record) {
        		switch(record.data.value){
        			case 'no_filter':
        				Ext.getCmp('ogTasksFilterNamesCombo').hide();
        				Ext.getCmp('ogTasksFilterNamesCompaniesCombo').hide();
        				Ext.getCmp('ogTasksFilterMilestonesCombo').hide();
        				Ext.getCmp('ogTasksFilterPriorityCombo').hide();
						var toolbar = Ext.getCmp('tasksPanelTopToolbarObject');
        				toolbar.load();
        				break;
        			case 'milestone':
        				Ext.getCmp('ogTasksFilterNamesCombo').hide();
        				Ext.getCmp('ogTasksFilterNamesCompaniesCombo').hide();
        				Ext.getCmp('ogTasksFilterMilestonesCombo').show();
        				Ext.getCmp('ogTasksFilterMilestonesCombo').setValue('');
        				Ext.getCmp('ogTasksFilterPriorityCombo').hide();
        				break;
        			case 'priority':
        				Ext.getCmp('ogTasksFilterNamesCombo').hide();
        				Ext.getCmp('ogTasksFilterNamesCompaniesCombo').hide();
        				Ext.getCmp('ogTasksFilterMilestonesCombo').hide();
        				Ext.getCmp('ogTasksFilterPriorityCombo').show();
        				Ext.getCmp('ogTasksFilterPriorityCombo').setValue('');
        				break;
        			case 'assigned_to':
        				Ext.getCmp('ogTasksFilterNamesCombo').hide();
        				Ext.getCmp('ogTasksFilterNamesCompaniesCombo').show();
        				Ext.getCmp('ogTasksFilterNamesCompaniesCombo').setValue('');
        				Ext.getCmp('ogTasksFilterMilestonesCombo').hide();
        				Ext.getCmp('ogTasksFilterPriorityCombo').hide();
        				break;
        			default:
        				Ext.getCmp('ogTasksFilterNamesCombo').show();
        				Ext.getCmp('ogTasksFilterNamesCombo').setValue('');
        				Ext.getCmp('ogTasksFilterNamesCompaniesCombo').hide();
        				Ext.getCmp('ogTasksFilterMilestonesCombo').hide();
        				Ext.getCmp('ogTasksFilterPriorityCombo').hide();
        				break;
        		}
        	}
        }
    });
    this.filtercombo.setValue(ogTasks.userPreferences.filter);
    
    
    var currentUser = '';
    var usersArray = Ext.util.JSON.decode(document.getElementById(config.usersHfId).value);
    var companiesArray = Ext.util.JSON.decode(document.getElementById(config.companiesHfId).value);
    for (i in usersArray){
		if (usersArray[i].isCurrent)
			currentUser = usersArray[i].cid + ':' + usersArray[i].id;
	}
	var ucsData = [[currentUser, lang('me')],['0:0',lang('everyone')],['-1:-1', lang('unassigned')],['0:0','--']];
	for (i in companiesArray)
		if (companiesArray[i].id) ucsData[ucsData.length] = [(companiesArray[i].id + ':0'), companiesArray[i].name];
	ucsData[ucsData.length] = ['0:0','--'];
	ucsOtherUsers = [];
	for (i in usersArray){
		var companyName = '';
		var j;
		for(j in companiesArray)
			if (companiesArray[j] && companiesArray[j].id == usersArray[i].cid)
				companyName = companiesArray[j].name;
		if (usersArray[i] && usersArray[i].cid) 
			ucsOtherUsers[ucsOtherUsers.length] = [(usersArray[i].cid + ':' + usersArray[i].id), usersArray[i].name + ' : ' + companyName];
		if (usersArray[i].isCurrent)
			currentUser = usersArray[i].cid + ':' + usersArray[i].id;
	}
	ucsData = ucsData.concat(ogTasksOrderUsers(ucsOtherUsers));
    
    this.filterNamesCompaniesCombo = new Ext.form.ComboBox({
    	id: 'ogTasksFilterNamesCompaniesCombo',
        store: new Ext.data.SimpleStore({
	        fields: ['value', 'text'],
	        data : ucsData
	    }),
	    hidden: ogTasks.userPreferences.filter != 'assigned_to',
        displayField:'text',
        typeAhead: true,
        mode: 'local',
        triggerAction: 'all',
        selectOnFocus:true,
        width:160,
        valueField: 'value',
        emptyText: (lang('select user or group') + '...'),
        valueNotFoundText: '',
        listeners: {
        	'select' : function(combo, record) {
				var toolbar = Ext.getCmp('tasksPanelTopToolbarObject');
        		if (toolbar.filterNamesCompaniesCombo == this)
        			toolbar.load();
        		else{
        			if (this.initialConfig.isInternalSelector)
        				ogTasks.UserCompanySelected(this.initialConfig.controlName, record.data.value, this.initialConfig.taskId);
        		}
        	}
        }
    });
    this.filterNamesCompaniesCombo.setValue(ogTasks.userPreferences.filterValue);
    
    for (i in usersArray){
		if (usersArray[i].isCurrent)
			currentUser = usersArray[i].id;
	}
	var uData = [[currentUser, lang('me')],['0',lang('everyone')],['0','--']];
	uDOtherUsers = [];
	for (i in usersArray){
		if (usersArray[i] && !usersArray[i].isCurrent && usersArray[i].id)
			uDOtherUsers[uDOtherUsers.length] = [usersArray[i].id, usersArray[i].name];
	}
	uData = uData.concat(ogTasksOrderUsers(uDOtherUsers));
    this.filterNamesCombo = new Ext.form.ComboBox({
    	id: 'ogTasksFilterNamesCombo',
        store: new Ext.data.SimpleStore({
	        fields: ['value', 'text'],
	        data : uData
	    }),
	    hidden: (ogTasks.userPreferences.filter == 'milestone' || ogTasks.userPreferences.filter == 'priority' || ogTasks.userPreferences.filter == 'assigned_to' || ogTasks.userPreferences.filter == 'no_filter'),
        displayField:'text',
        typeAhead: true,
        mode: 'local',
        triggerAction: 'all',
        selectOnFocus:true,
        width:160,
        valueField: 'value',
        emptyText: (lang('select user or group') + '...'),
        valueNotFoundText: '',
        listeners: {
        	'select' : function(combo, record) {
				var toolbar = Ext.getCmp('tasksPanelTopToolbarObject');
        		toolbar.load();
        	}
		}
	});
    this.filterNamesCombo.setValue(ogTasks.userPreferences.filterValue);
    
    this.filterPriorityCombo = new Ext.form.ComboBox({
    	id: 'ogTasksFilterPriorityCombo',
        store: new Ext.data.SimpleStore({
			fields: ['value', 'text'],
			data : [[100, lang('low')],[200, lang('normal')],[300, lang('high')]]
	    }),
	    hidden: ogTasks.userPreferences.filter != 'priority',
        displayField:'text',
        typeAhead: true,
        mode: 'local',
        triggerAction: 'all',
        selectOnFocus:true,
        width:160,
        valueField: 'value',
        emptyText: (lang('select priority') + '...'),
        valueNotFoundText: '',
        listeners: {
        	'select' : function(combo, record) {
				var toolbar = Ext.getCmp('tasksPanelTopToolbarObject');
        		if (toolbar.filterPriorityCombo == this)
        			toolbar.load();
        	}
        }
    });
    this.filterPriorityCombo.setValue(ogTasks.userPreferences.filterValue);
    
    
    var milestones = Ext.util.JSON.decode(document.getElementById(config.internalMilestonesHfId).value);
    milestones = milestones.concat(Ext.util.JSON.decode(document.getElementById(config.externalMilestonesHfId).value));
    milestonesData = [[0,"--" + lang('none') + "--"]];
    for (i in milestones){
    	if (milestones[i].id)
    		milestonesData[milestonesData.length] = [milestones[i].id, milestones[i].t];
    }
    this.filterMilestonesCombo = new Ext.form.ComboBox({
    	id: 'ogTasksFilterMilestonesCombo',
        store: new Ext.data.SimpleStore({
	        fields: ['value', 'text'],
	        data : milestonesData,
	        sortInfo: {field:'text',direction:'ASC'}
	    }),
	    hidden: (ogTasks.userPreferences.filter != 'milestone'),
        displayField:'text',
        typeAhead: true,
        mode: 'local',
        triggerAction: 'all',
        selectOnFocus:true,
        width:160,
        valueField: 'value',
        emptyText: (lang('select milestone') + '...'),
        valueNotFoundText: '',
        listeners: {
        	'select' : function(combo, record) {
				var toolbar = Ext.getCmp('tasksPanelTopToolbarObject');
        		if (toolbar.filterMilestonesCombo == this)
        			toolbar.load();
        	}
        }
    });
    this.filterMilestonesCombo.setValue(ogTasks.userPreferences.filterValue);
	
	
    this.statusCombo = new Ext.form.ComboBox({
    	id: 'ogTasksStatusCombo',
        store: new Ext.data.SimpleStore({
	        fields: ['value', 'text'],
	        data : [[2, lang('all')],[0, lang('pending')],[1, lang('complete')]]
	    }),
        displayField:'text',
        typeAhead: true,
        mode: 'local',
        triggerAction: 'all',
        selectOnFocus:true,
        width:80,
        valueField: 'value',
        listeners: {
        	'select' : function(combo, record) {
				var toolbar = Ext.getCmp('tasksPanelTopToolbarObject');
        		toolbar.load();
        	}
        }
    });
    this.statusCombo.setValue(ogTasks.userPreferences.status);
    
    //Add stuff to the toolbar
	this.add(butt);
	this.addSeparator();
	this.add(actions.tag);
	this.add(actions.del);
	this.add(actions.complete);
	this.addSeparator();
    this.add(lang('filter') + ':');
    this.add(this.filtercombo);
    this.add(this.filterNamesCombo);
    this.add(this.filterNamesCompaniesCombo);
    this.add(this.filterPriorityCombo);
    this.add(this.filterMilestonesCombo);
    this.add('&nbsp;&nbsp;&nbsp;' + lang('status') + ':');
    this.add(this.statusCombo);
};

function ogTasksLoadFilterValuesCombo(newValue){
	var combo = Ext.getCmp('ogTasksFilterValuesCombo');
}

function ogTasksOrderUsers(usersList){
	for (var i = 0; i < usersList.length - 1; i++)
		for (var j = i+1; j < usersList.length; j++)
			if (usersList[i][1].toUpperCase() > usersList[j][1].toUpperCase()){
				var aux = usersList[i];
				usersList[i] = usersList[j];
				usersList[j] = aux;
			}
	return usersList;
}

Ext.extend(og.TasksTopToolbar, Ext.Toolbar, {
	load: function(params) {
		if (!params) params = {};
		Ext.apply(params,this.getFilters());
		og.openLink(og.getUrl('task','new_list_tasks',params));
	},
	getFilters : function(){
		var filterValue;
		switch(this.filtercombo.getValue()){
			case 'milestone':
				filterValue = this.filterMilestonesCombo.getValue();
				break;
			case 'priority':
				filterValue = this.filterPriorityCombo.getValue();
				break;
			case 'assigned_to':
				filterValue = this.filterNamesCompaniesCombo.getValue();
				break;
			default:
				filterValue = this.filterNamesCombo.getValue();
				break;
		}
		
		return {
			status: this.statusCombo.getValue(),
			filter:this.filtercombo.getValue(),
			fval:filterValue
		}
	},
	cloneUserCompanyCombo : function(newId){
		var clone = this.filterNamesCompaniesCombo.cloneConfig({id:newId});
		
		return clone;
	},
	updateCheckedStatus : function(){
		var checked = false;
		var allIncomplete = true;
		for (var i = 0; i < ogTasks.Tasks.length; i++)
			if (ogTasks.Tasks[i].isChecked){
				checked = true;
				if (ogTasks.Tasks[i].status == 1)
					allIncomplete = false;
			}
		
		if (!checked){
			this.actions.del.disable();
			this.actions.complete.disable();
			this.actions.tag.disable();
		} else {
			this.actions.del.enable();
			this.actions.tag.enable();
			if (allIncomplete)
				this.actions.complete.enable();
			else
				this.actions.complete.disable();
				
		}
		
	}
});

Ext.reg("tasksTopToolbar", og.TasksTopToolbar);