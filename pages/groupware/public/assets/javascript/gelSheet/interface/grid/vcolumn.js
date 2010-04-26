/*  Gelsheet Project, version 0.0.1 (Pre-alpha)
 *  Copyright (c) 2008 - Ignacio Vazquez, Fernando Rodriguez, Juan Pedro del Campo
 *
 *  Ignacio "Pepe" Vazquez <elpepe22@users.sourceforge.net>
 *  Fernando "Palillo" Rodriguez <fernandor@users.sourceforge.net>
 *  Juan Pedro "Perico" del Campo <pericodc@users.sourceforge.net>
 *
 *  Gelsheet is free distributable under the terms of an GPL license.
 *  For details see: http://www.gnu.org/copyleft/gpl.html
 *
 */
function ColumnReziseArea(){
	var self = document.createElement("TABLE");

	self.constructor = function(){
		var tbody = document.createElement("TBODY");
		self.style.top = "0px";
		self.style.left = "0px";
		self.style.borderCollapse = "collapse";
		self.style.width = "100%";
		self.style.height = "100%";
		var trHeader = document.createElement("TR");
		trHeader.style.height = "100%";
		var tdHeader = document.createElement("TD");

		var tdResizer = document.createElement("TD");
		tdResizer.offset = 0;
		tdResizer.style.width = "5px";
		tdResizer.style.cursor = "e-resize";
		tdResizer.style.backgroundColor = "transparent";

		tdResizer.onmousedown = function(e){
			e ? e : e = window.event; //get event for IE
			this.offset = e.screenX;
			if(self.onresizing) self.onresizing(e); //Ghost function call, to support external resizing from outside
		}

		self.style.backgroundColor = "transparent";

		trHeader.appendChild(tdHeader);
		trHeader.appendChild(tdResizer);
		this.data = tdHeader;
		tbody.appendChild(trHeader);
		self.appendChild(tbody);
	}

	self.setInnerHTML = function(value){
		this.data.innerHTML = value;
	}
	self.constructor();
	return self;
}

function VColumn(index){
	var self = document.createElement("TH");

	self.constructor = function(index){
		this.index = index;
		this.column = new Column(index);
		this.vcells = new Array();
		//this.innerHTML = String.fromCharCode(65+index);
		this.style.textAlign = "center";
		//this.style.position = "absolute";
		this.style.overflow = "hidden";
		this.style.cursor = "url(img/col.cur.ico), default";
		this.className = "ColumnUnselected";

		this.resizeArea = new ColumnReziseArea();

		this.resizeArea.onresizing = function(e){
			//e ? e : e = window.event; //get event for IE
			if(self.onresizing) self.onresizing(e);
		}

		/*this.resizeArea.onmousedown = function(e){ //TODO: Emprolijar no usar acceso atributos
			var handler = self.parentNode.verticalResizer; //parentNode = GRID
			//var pos = (window.Event) ? parseInt(e.pageX): parseInt(event.clientX);
			handler.element = self;
			handler.style.left = px(self.getLeft() + self.getWidth());
			handler.onmousedown(e);
		}*/

		this.resizeArea.setInnerHTML(String.fromCharCode(65+index));
		this.appendChild(this.resizeArea);
		WrapStyle(this);
	}

	self.setInnerHTML = function(value){
		this.resizeArea.setInnerHTML(value);
	}

	self.setTitle = function(value){
		this.resizeArea.setInnerHTML(value);
	}

	self.setColumn = function(column){
		this.column = column;
	}

	self.addCell = function(cell){
		this.vcells.push(cell);
	}

	self.activate = function(){
		this.className = "ColumnFocused";
	}

	self.deactivate = function(){
		this.className = "ColumnUnselected";
	}

	self.select = function(){
		this.className = "ColumnSelected";

		for(var i=0;i<this.vcells.length;i++){
			this.vcells[i].select();
		}
	}

	self.unselect = function(){
		this.className = "ColumnUnselected";
		for(var i=0;i<this.vcells.length;i++){
			this.vcells[i].unselect();
		}
	}

	self.getName = function(){
		return this.innerHTML;
	}

	self.setIndex = function(index){
		this.index = index;
	}

	self.getIndex = function(){
		return this.index;
	}

	self.resize = function(newvalue){
		var width = this.getWidth() - newvalue;
		if (width < 6) width = 0;
		this.setWidth(width);

		/*for(i=0;i<this.vcells.length;i++){
			this.vcells[i].setWidth(newvalue);
		}
		EventManager.fire(EVT_COLUMN_CHANGE,this.vcells[0]);*/
		//fire own event, this  must be implemented by grid
		if(this.columnChanged) this.columnChanged();
	}
	self.addCell = function(cell){
		this.vcells.push(cell);
	}

	self.getSize = function(){
		return this.getWidth();
	}

	self.setSize = function(size){
		return this.setWidth(size);
	}

	self.refresh = function(){
		//this.innerHTML = nameSpace.getColumnName(this.index);
		/*this.appendChild(this.resizeArea); //Restores Resize Area

		if(this.vcells[0].getLeft()!= this.getLeft()){
			for(var i=0;i<this.vcells.length;i++)
				this.vcells[i].setLeft(this.getLeft());
		}*/
	}


	self.constructor(index);

	return self;
}