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
function addModelStyleOperations(model){
	
	model.getActiveFontStyleId = function(){
		return (model.selection.getSelection())[0].getFontStyleId();
	}

	model.setSelectionFontStyleId = function(fsId){
		var selection = model.selection.getSelection();
		for(var i=0;i<selection.length;i++){
			this.setRangeFontStyleId(selection[i].row, selection[i].col, fsId);
		}
		model.refresh(); //TODO: Remove, this should refresh only changed cells
	}
	
	//Should be private
	model.getRangeFontStyleId = function(rowIndex, colIndex){
		var fontStyleId = 0; //default
		if(rowIndex!=undefined)
			if(colIndex!=undefined)
				fontStyleId = this.model.getCellFontStyleId(rowIndex, colIndex);
			else
				fontStyleId = this.model.getRowFontStyleId(rowIndex);
		else
			fontStyleId = this.model.getColumnFontStyleId(colIndex);

		return fontStyleId;
	}

	//Should be private
	model.setRangeFontStyleId = function(rowIndex, colIndex,fontStyleId){
		if(rowIndex!=undefined)
			if(colIndex!=undefined)//Its a Cell
				this.model.setCellFontStyleId(rowIndex, colIndex, fontStyleId);
			else//Its a Row
				this.model.setRowFontStyleId(rowIndex,fontStyleId);
		else{ //Its a Column
			this.model.setColumnFontStyleId(colIndex,fontStyleId);
		}
	}
	
	/** pepe **/
	model.changeBgColorToSelection = function(color) {
		var selection = model.selection.getSelection();
		var range = undefined;
		if(selection.length)
			this.setRangeBgColor(selection[0].row, selection[0].col, color);
			//selection[0].row, selection[0].col;
		
		for(var i=1;i<selection.length;i++){
			if (selection[i].row == undefined) 
				this.setRangeBgColor(0, selection[i].col, color);
			else	
				this.setRangeBgColor(selection[i].row, selection[i].col, color);
		}

		model.refresh(); //TODO: Remove, this should refresh only changed cells
	} 
	
	
	/** pepe **/
	model.setRangeBgColor = function (rowIndex, colIndex, color) {
		this.model.changeColumnFontStyleProp(colIndex,"bold",true);
		if(rowIndex!=undefined)
			if(colIndex!=undefined)//Its a Cell
				application.grid.cells[rowIndex][colIndex].style.background = color ;
			else//Its a Row
				this.model.setRowBgColor(rowIndex,color);
		else //Its a Column
			this.model.setColumnBgColor(colIndex,color);
	}
		
	
	//Should be private
	model.setRangeFontStyleProperty = function(rowIndex, colIndex,property,value){
		if(rowIndex!=undefined)
			if(colIndex!=undefined)//Its a Cell
				this.model.changeCellFontStyleProp(rowIndex, colIndex, property,value);
			else//Its a Row
				this.model.setRowFontStyleId(rowIndex,fontStyleId);
		else{ //Its a Column
			this.model.changeColumnFontStyleProp(colIndex,property,value);
		}
	}
	
	model.changeFontStylePropertyToSelection = function(property,value){
		var selection = model.selection.getSelection();
		var range = undefined;
		
		if(value==undefined)
			if(selection.length){
				var fstyle = Styler.getFontStyleById(this.getRangeFontStyleId(selection[0].row,selection[0].col));
				value =	!fstyle[property];
			}

		for(var i=0;i<selection.length;i++){
			var fstyle = Styler.getFontStyleById(this.getRangeFontStyleId(selection[i].row, selection[i].col));
			if(value!=fstyle[property])
				this.setRangeFontStyleProperty(selection[i].row, selection[i].col, property,value);
		}
		model.refresh(); //TODO: Remove, this should refresh only changed cells
	}
	
	/*############## Font Style Operations####################*/
	
	model.changeBoldToSelection = function(){
		this.changeFontStylePropertyToSelection("bold")
	}
	
	model.changeUnderlineToSelection = function(){
		this.changeFontStylePropertyToSelection("underline");
	}
	
	model.changeItalicToSelection = function(){
		this.changeFontStylePropertyToSelection("italic");
	}
	
	model.changeFontSizeToSelection = function(size){
		this.changeFontStylePropertyToSelection("size",size);
	}
		
	model.changeFontToSelection = function(font){
		this.changeFontStylePropertyToSelection("font",font)
	}
	
	model.changeFontColorToSelection = function(color) {
		this.changeFontStylePropertyToSelection("color",color);
	} 
}