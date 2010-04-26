/**
 *  TaskManager
 *
 */
og.TasksBottomToolbar = function(config) {
	Ext.applyIf(config,
		{
			id:"tasksPanelBottomToolbarObject",
			renderTo: "tasksPanelBottomToolbar",
			height: 28,
			style:"border:0px none"
		});
		
	og.TasksBottomToolbar.superclass.constructor.call(this, config);
	
    this.groupcombo = new Ext.form.ComboBox({
        store: new Ext.data.SimpleStore({
	        fields: ['value', 'text'],
	        data : [['nothing', '--' + lang('nothing (groups)') + '--']
	        	,['milestone', lang('milestone')]
	        	,['priority',lang('priority')]
	        	,['workspace', lang('workspace')]
	        	,['assigned_to', lang('assigned to')]
	        	,['due_date', lang('due date')]
	        	,['start_date', lang('start date')]
	        	,['created_on', lang('created on')]
	        	,['created_by', lang('created by')]
	        	,['completed_on', lang('completed on')]
	        	,['completed_by', lang('completed by')]
	        	,['status', lang('status')]
	        	,['tag', lang('tag')]]
	    	}),
        displayField:'text',
        typeAhead: true,
        mode: 'local',
        triggerAction: 'all',
        selectOnFocus:true,
        width:160,
        valueField: 'value',
        listeners: {
        	'select' : function(combo, record) {
        		ogTasks.setAllCheckedValue(false);
        		ogTasks.setAllExpandedValue(false);
				ogTasks.draw();
        		var url = og.getUrl('account', 'update_user_preference', {name: 'tasksGroupBy', value:record.data.value});
				og.openLink(url,{hideLoading:true});
        	}
        }
    });
    this.groupcombo.setValue(ogTasks.userPreferences.groupBy);
	
    this.ordercombo = new Ext.form.ComboBox({
        store: new Ext.data.SimpleStore({
	        fields: ['value', 'text'],
	        data : [['priority',lang('priority')]
	        	,['workspace', lang('workspace')]
	        	,['name', lang('task name')]
	        	,['due_date', lang('due date')]
	        	,['created_on', lang('created on')]
	        	,['completed_on', lang('completed on')]
	        	,['assigned_to', lang('assigned to')]
	        	,['start_date', lang('start date')]]
	    	}),
        displayField:'text',
        typeAhead: true,
        mode: 'local',
        triggerAction: 'all',
        selectOnFocus:true,
        width:160,
        valueField: 'value',
        listeners: {
        	'select' : function(combo, record) {
				ogTasks.redrawGroups = false;
				ogTasks.draw();
				ogTasks.redrawGroups = true;
        		var url = og.getUrl('account', 'update_user_preference', {name: 'tasksOrderBy', value:record.data.value});
				og.openLink(url,{hideLoading:true});
        	}
        }
    });
    this.ordercombo.setValue(ogTasks.userPreferences.orderBy);
    
	this.add(lang('group by') + ':');
    this.add(this.groupcombo);
    this.add('&nbsp;&nbsp;&nbsp;' + lang('order by') + ':');
    this.add(this.ordercombo);
	this.addSeparator();
    this.add(lang('show') + ':');
	this.add({
		id:'btnShowWorkspaces',
        text: lang('workspaces'),
        enableToggle: true,
        pressed: (ogTasks.userPreferences.showWorkspaces == 1),
        listeners: {
        	'toggle' : function(button,isPressed){
				ogTasks.redrawGroups = false;
				ogTasks.draw();
				ogTasks.redrawGroups = true;
        		var url = og.getUrl('account', 'update_user_preference', {name: 'tasksShowWorkspaces', value:(isPressed?1:0)});
				og.openLink(url,{hideLoading:true});
        	}
        }
    });
	this.add({
		id:'btnShowTime',
        text: lang('time'),
        enableToggle: true,
        pressed: (ogTasks.userPreferences.showTime == 1),
        listeners: {
        	'toggle' : function(button,isPressed){
				ogTasks.redrawGroups = false;
				ogTasks.draw();
				ogTasks.redrawGroups = true;
        		var url = og.getUrl('account', 'update_user_preference', {name: 'tasksShowTime', value:(isPressed?1:0)});
				og.openLink(url,{hideLoading:true});
        	}
        }
    });
	this.add({
		id:'btnShowTags',
        text: lang('tags'),
        enableToggle: true,
        pressed: (ogTasks.userPreferences.showTags == 1),
        listeners: {
        	'toggle' : function(button,isPressed){
				ogTasks.redrawGroups = false;
				ogTasks.draw();
				ogTasks.redrawGroups = true;
        		var url = og.getUrl('account', 'update_user_preference', {name: 'tasksShowTags', value:(isPressed?1:0)});
				og.openLink(url,{hideLoading:true});
        	}
        }
    });
	this.add({
		id:'btnShowDates',
        text: lang('dates'),
        enableToggle: true,
        pressed: (ogTasks.userPreferences.showDates == 1),
        listeners: {
        	'toggle' : function(button,isPressed){
				ogTasks.redrawGroups = false;
				ogTasks.draw();
				ogTasks.redrawGroups = true;
        		var url = og.getUrl('account', 'update_user_preference', {name: 'tasksShowDates', value:(isPressed?1:0)});
				og.openLink(url,{hideLoading:true});
        	}
        }
    });
	//GetFilterByConditions
	
    this.add('-');
    this.add(new Ext.Action({
			text: lang('print'),
            tooltip: lang('print all groups'),
            iconCls: 'ico-print',
			handler: function() {
				ogTasks.printAllGroups();
			},
			scope: this
		}));
};

Ext.extend(og.TasksBottomToolbar, Ext.Toolbar, {
	getDisplayCriteria : function(){
		return {
			group_by : this.groupcombo.getValue(),
			order_by : this.ordercombo.getValue()
		}
	},
	getDrawOptions : function(){
		return {
			show_workspaces : this.items.item('btnShowWorkspaces').pressed,
			show_time : this.items.item('btnShowTime').pressed,
			show_tags : this.items.item('btnShowTags').pressed,
			show_dates : this.items.item('btnShowDates').pressed
		}
	}
});

Ext.reg("TasksBottomToolbar", og.TasksBottomToolbar);