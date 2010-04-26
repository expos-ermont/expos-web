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
 */function VCell(row, column){
	var self = document.createElement("TD");

	self.constructor = function(row, column){
		this.className = "CellUnselected";
		this.row = row;
		this.column = column;
		this.address = {"row":row,"col":column}; //Reference Address
		this.style.borderWidth = "1px";
		this.style.overflow = "hidden";
		this.style.whiteSpace = "nowrap";
		this.style.padding = "0px";
		this.style.margin = '0px';
		this.style.cursor = "url(img/cell.cur.ico), default";
		this.value = undefined;
		this.fontStyleId =0;
		WrapStyle(this);
	}

	self.getFontStyleId = function(){
		return this.fontStyleId;
	}

	self.getValue = function(){
		return this.value;
	}

	self.setAddress = function(row,column){
		this.address.row = row;
		this.address.col = column;
	}

	self.getAddress = function(){
		return this.address;
	}

	self.getColumn = function(){
		return this.column;
	}

	self.getRow = function(){
		return this.row;
	}
	self.setValue = function(value){
		this.value = value;
		this.innerHTML = value;
	}

	self.setCell = function(cell){
		this.cell = cell;
	}

	self.activate = function(){
		this.className = "CellFocused";
	}

	self.deactivate = function(){
		this.className = "CellUnselected";
	}

	self.select = function(){
		this.className = "CellSelected";
	}

	self.unselect = function(){
		this.className = "CellUnselected";
	}

	self.setInnerHTML = function(value){
	    if(value)
		    this.innerHTML = value;
		else
		    this.innerHTML = "";
	}

	self.updateFontStyle = function(newFontStyleId){
		if(this.fontStyleId != newFontStyleId){
			WrapFontStyle(this,newFontStyleId);
			this.fontStyleId = newFontStyleId;
		}
	}

	self.refresh = function(){
		if(this.cell!=undefined)
			if(this.cell.value!=undefined) //IE doesnt support AND with short circuit
				this.innerHTML = this.cell.value;
			else
				this.innerHTML = "";
		else
			this.innerHTML = "";
	}

	self.constructor(row, column);
	return self;
}

