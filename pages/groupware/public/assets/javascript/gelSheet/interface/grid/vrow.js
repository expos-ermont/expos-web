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
function RowResizeArea(){
	var self = document.createElement("DIV");

	self.constructor = function(){
		//self.style.position = "relative";
		self.style.left = "0px";
		self.style.width = "100%";
		self.style.top = "90%";
		self.style.height = "15%";
		//self.style.backgroundColor = "transparent";
		self.style.backgroundColor = "#F00";
		self.style.cursor = "s-resize";
	}
	self.constructor();
	return self;
}

function VRow(index){

	var self = document.createElement("TR");

	self.addCell = function(cell){
		this.vcells.push(cell);
		this.appendChild(cell);
	}

	self.activate = function(){
		this.header.className = "RowFocused";
	}

	self.deactivate = function(){
		this.header.className = "RowUnselected";
	}

	self.select = function(){
		this.header.className = "RowSelected";
		for(var i=0;i<this.vcells.length;i++){
			this.vcells[i].select();
		}
	}

	self.unselect = function(){
		this.header.className = "RowUnselected";

		for(var i=0;i<this.vcells.length;i++){
			this.vcells[i].unselect();
		}
	}

	self.getIndex = function(){
		return this.index;
	}

	self.setIndex = function(index){
		this.index = index;
	}

	self.setRow = function(row){
		this.row = row;
	}

	self.getRow = function(){
		return this.row;
	}

	self.setSize = function(size){
		this.setHeight(size);
	}

	self.getSize = function(){
		return this.getHeight();
	}

	self.resize = function(newvalue){
		this.setHeight(newvalue);

		for(i=0;i<this.vcells.length;i++){
			this.vcells[i].setHeight(newvalue);
		}
		//fire own event, this  must be implemented by GRID
		if(this.rowChanged) this.rowChanged();
	}

	self.refresh = function(){
		//if(this.row)
		//	this.innerHTML = this.row.getIndex() +1;

		this.innerHTML = this.index +1;
		this.appendChild(this.resizeArea); //Restores Resize Area

		if(this.vcells[0].getTop()!= this.getTop()){
			for(var i=0;i<this.vcells.length;i++)
				this.vcells[i].setTop(this.getTop());
		}

	}
	self.setInnerHTML = function(value){
		//TODO: fix it, overrides rezise Area
		this.header.innerHTML = value;
	}

	self.setTitle = function(value){
		//TODO: fix it, overrides rezise Area
		this.header.textContent = value;
	}

	self.constructor = function(index){
		//if(this.vcells==undefined)
		this.vcells = new Array();
		this.index = index;
		this.header = document.createElement("TH"); //Row Header, First Cell in Row

		this.header.style.textAlign = "center";
		//this.style.position = "absolute";
		this.header.style.overflow = "hidden";
		this.header.style.cursor = "url(img/row.cur2.ico), default";
		this.header.innerHTML = index+1; //Starts form 1;
		this.header.className = "RowUnselected";
		WrapStyle(this.header);
		WrapStyle(self);

		this.header.onmousedown = function(e){
			//Fire Fake Event that must be overwritten by creator (grid)
			if(self.rowmousedown) self.rowmousedown()
		}

		//Add resize Detection Area
		this.resizeArea = new RowResizeArea();

		this.resizeArea.onmousedown = function(e){ //TODO: Emprolijar no usar acceso atributos
			var handler = self.parentNode.rowHandler; //parentNode = GRID
			handler.element = self;
			handler.style.top = px(self.getTop() + self.getHeight());
			handler.onmousedown(e);
		}
		this.header.appendChild(this.resizeArea);
		this.appendChild(this.header);
	}
	self.constructor(index);
	return self;
}
