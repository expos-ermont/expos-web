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
function Cell(row, column){
	var self = this;

	self.constructor = function(row, column){
		this.formula = undefined;
		this.dependencies = new Array();
		this.row = row;
		this.column = column;
		//Set default Styles
		this.fontStyleId = 0;
		this.layerStyleId = 0;
	}

	self.calculate = function(){
		if(this.formula!=undefined)
			if(this.formula.charAt(0)=="="){
				var ref = this.formula.substr(1);
				//this.value =(document.getElementById(ref)).value;
				this.value = ref;
			}else{
				this.value = this.formula;
			}
	}

	//Style Functions
	self.getFontStyleId = function(){
		return this.fontStyleId;
	}

	self.setFontStyleId = function(fontStyleId){
		this.fontStyleId = fontStyleId;
		//alert ("en cel" + this.fontStyleId);
	}

	self.getLayerStyleId = function(){
		return this.layerStyleId;
	}

	self.setLayerStyleId = function(layerStyleId){
		this.layerStyleId = layerStyleId;
	}


	self.getAddress = function(){
		return {row:this.row,col:this.column};
	}
	//Contents Functions
	self.getValue = function(){
		return this.value;
	}

	self.getFormula = function(){
		return this.formula;
	}

	self.setFormula = function(value){
		this.formula = value;
		this.calculate();
	}

	self.setValue = function(value){
		this.value = value;
		//this.calculate;
	}

	self.getRow = function(){
		return this.row;
	}

	self.getColumn = function(){
		return this.column;
	}

	self.constructor(row, column);

	return self;
}

