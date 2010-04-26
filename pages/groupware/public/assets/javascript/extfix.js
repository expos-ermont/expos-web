Ext.BLANK_IMAGE_URL = "s.gif";

Ext.override(Ext.tree.TreeEventModel, {
	initEvents : function(){
		var el = this.tree.getTreeEl();
		el.on('click', this.delegateClick, this);
		if(this.tree.trackMouseOver !== false){
			var innerCt = Ext.fly(el.dom.firstChild);
			innerCt.on('mouseover', this.delegateOver, this);
			innerCt.on('mouseout', this.delegateOut, this);
		}
		el.on('dblclick', this.delegateDblClick, this);
		el.on('contextmenu', this.delegateContextMenu, this);
	}
});