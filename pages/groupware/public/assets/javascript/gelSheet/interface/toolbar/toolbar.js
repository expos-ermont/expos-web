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

function ToolBar( name, top, left , width, height ){
	var self = document.createElement("DIV") ;

	self.constructor = function ( name, top, left , width, height ){
		this.items= new Array();
		this.name=name;
		this.className="ToolBar";
		this.style.left= px(left);
		this.style.top = px(top);
		this.style.width = px(width);
		this.style.height = px(height);
		this.style.position = "absolute";
		this.style.zIndex = 0;
		//Items Container (TABLE) is defined here
		var table = document.createElement("TABLE");
		table.style.padding = "0px";
		table.style.margin = "0px";
		table.cellspacing = "0px";
		table.style.border = "none";
		table.cellpadding = "0px";
		table.style.borderCollapse = "collapse";
		var tr = document.createElement("TR");
		table.appendChild(tr);
		this.container = table;
		this.currentRow = tr;
		this.appendChild(table);

	}


	self.addItem = function (item){
		var count = this.items.length;
		//item.setLeft(count*32);
		//item.setTop(0);
		//item.setWidth(18);
		//item.setHeight(18);
		item.setZIndex(count+1);
		this.items.push(item);
		var td = document.createElement("TD");
		td.appendChild(item);
		td.style.padding = "0px";
		td.style.margin = "0px";

		this.currentRow.appendChild(td);
	}


	self.constructor( name, top, left , width, height);
	return self;

}

function CommandButton(name, image, title, callback) {
	var self = document.createElement("IMG") ;

	self.constructor = function (name, image, title, callback ){
		this.name=name;
		this.id=name ; //added by pepe...
		this.className="ToolBarButton";
		this.title=title;
		this.src = window.enviromentPrefix + image;
		this.callback=callback;
		WrapStyle(this);
		this.setWidth(24);//TODO SACARLO DE AFUERA
		this.setHeight(24);
	}

	self.onclick = function(e) {
		this.callback(e);
	}
	self.constructor(name, image, title, callback);
	return self;

}

function CommandCombo(name, image, title, callback, values) {
	var self = document.createElement("SELECT") ;

	self.constructor = function (name, image, title, callback, values){
		this.name=name;
		this.className="ToolBarCombo";
		this.title=title;
		this.src = image;
		this.callback=callback;
		//this.style.fontSize = "10pt";
		WrapStyle(this);
		//this.setWidth(150);
		//this.setHeight(18);
		for(var item in values){
			if(item!="remove"){ //Added by Ext JS as an extension of Array (TODO: remove JS)
				if(values[item]){
					var option = document.createElement("OPTION");
					option.value = item;
					option.text = values[item];
	//				option.style.fontSize = "10pt";
					 try{
					    this.add(option,null); //Standards
					 }catch(e){
					    this.add(option); //IE
					 }
				}// End if values.length >0
			}
		}//End for
	}

	self.addOption = function(option){
		try{
		    this.add(option,null); //Standards
		 }catch(e){
		    this.add(option); //IE
		 }
	}

	self.onchange = function(e){
		this.callback(this.value);
	}

	self.constructor(name, image, title, callback,values);
	return self;

}

function CommandFontCombo(name, image, title, callback, values) {
	var self = CommandCombo(name, image, title, callback, null);
	this.id = "FontCombo";
	self.constructor = function (values){
		for(var i=0;i<values.length;i++){
			if(values[i])
				if(values[i]!="remove"){ //Added by Ext JS as an extension of Array (TODO: remove JS)
					var option = document.createElement("OPTION");
					option.value = i;
					option.text = values[i];
					option.style.fontFamily = values[i];
					option.style.fontSize = "10pt";
					this.addOption(option);
				}// End if values.length >0
		}//End for
	}
	self.constructor(values);
	return self;

}

function CommandFontSizeCombo(name, image, title, callback, values) {
	var self = CommandCombo(name, image, title, callback, null);

	self.constructor = function (values){
		this.id = "FontSizeCombo";
		for(var i=0;i<values.length;i++){
			if(values[i]){
				if(values[i] != "remove"){
					var option = document.createElement("OPTION");
					option.value = i;
					option.text = values[i];
					option.style.fontFamily = "Verdana";
					option.style.fontSize = pt(i);
					this.addOption(option);
				}// End if values.length >0
			}
		}//End for
	}
	self.constructor(values);
	return self;

}