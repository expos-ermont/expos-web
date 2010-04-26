og.ExtendedDialog = function(config) {
	og.ExtendedDialog.superclass.constructor.call(this, Ext.applyIf(config, {
		y: 50,
		id: config.genid + 'dialog',
		layout: 'border',
		modal: true,
		height:300,
		width:450,
		resizable: false,
		closeAction: 'hide',
		iconCls: 'op-ico',
		border: false,
		buttons: [{
			text: (config.YESNO ? lang('yes') : lang('ok')),
			handler: this.accept,
			id: config.genid + 'ok_button',
			scope: this
		},{
			text: (config.YESNO ? lang('no') : lang('cancel')),
			handler: this.cancel,
			scope: this
		}],
		items: [
			{
				region: 'center',
				layout: 'fit',
				items: [
					this.form = new Ext.FormPanel({
				        frame:false,
				        url: '',
				        labelWidth: config.labelWidth,
				        bodyStyle:'padding:20px 20px 0',
						border:false,
						bodyBorder: false,
				        items: config.dialogItems,
				        html: config.html
				    })
				]
			}
		]
	}));
}

Ext.extend(og.ExtendedDialog, Ext.Window, {
	accept: function() { this.hide(); },
	cancel: function() { this.hide(); }
});

og.ExtendedDialog.show = function(config) {
	if (!config)
		config = {};
	if (!config.genid)
		config.genid = 'Extended';
	if (this.dialog)
		this.dialog.destroy();
	this.dialog = new og.ExtendedDialog(config);
	
	if (config.ok_fn) {
		Ext.getCmp(config.genid + 'ok_button').setHandler(config.ok_fn);
	}
	
	this.dialog.purgeListeners();
	this.dialog.show();
	var pos = this.dialog.getPosition();
	if (pos[0] < 0) pos[0] = 0;
	if (pos[1] < 0) pos[1] = 0;
	this.dialog.setPosition(pos[0], pos[1]);
}

og.ExtendedDialog.hide = function() {
	if (this.dialog) this.dialog.hide();
}