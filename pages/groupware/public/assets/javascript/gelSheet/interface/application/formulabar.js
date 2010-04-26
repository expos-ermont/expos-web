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
function FormulaBar(){
	var self = document.createElement("TEXTAREA");
	//var self = document.getElementById("FormulaBar");
	
	self.constructor = function(){
		this.id = "FormulaBar";
		this.style.position = "absolute";
		this.style.top = "30px";
		this.style.left = "50px";
		this.style.width = "80%";
		this.style.height = "20px";
		this.style.borderWidth = "1px";

		WrapStyle(this);
	}

	self.setValue = function(value){
		if(value)
		    this.value = value;
		 else
		 	this.value = "";
	    if(this.onchange) this.onchange(); //event defined by Grid
	    errorConsole.println("VALOR CAMBIADO: " + this.value);
	}

	self.getValue = function(){
	    return this.value;
	}

	self.refresh = function(formula){
		self.setValue(formula);
	}

	//EVENTS ------------
	self.onkeyup = function(){
		//TODO: unregister EVT_CELL_EDITING because it makes call to setValue
        EventManager.fire(EVT_CELL_EDITING,this.value);
	}

	self.onblur = function(e){
		application.updateActiveCell(this.value);
		EventManager.fire(EVT_CELL_CHANGE,this.value);
	}
	self.constructor();
	//Register Fake Events
	EventManager.register(EVT_CELL_EDITING,self.refresh,true);

	return self;
}

