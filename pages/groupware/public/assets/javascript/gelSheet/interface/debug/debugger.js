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

function Debugger(){
	var self = document.createElement("DIV")
	self.constructor = function(){
		this.style.position = "absolute";
		this.style.overflow = "auto";
		this.style.left= px(400);
		this.style.top= px(150);
		this.style.width= px(500);
		this.style.height= px(300);
		this.style.zIndex = 1000;
		this.style.border = "1px solid #CCC";

		this.style.backgroundColor = "#FCFCFC";
	}

	self.print = function(value){
		this.innerHTML += value;
	}
	self.println = function(value){
		this.innerHTML += value + "<BR>";
	}

	self.clear = function(){
		this.innerHTML = "";
	}

	self.constructor();
	return self;
}