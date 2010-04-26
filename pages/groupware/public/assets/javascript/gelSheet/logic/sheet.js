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
function SheetSection(name ,top,left, width, height){
	var self = this;
	self.constructor = function(name){
		this.id = name;
		this.style.position = "absolute";
		this.style.top = top;
		this.style.left = left;
		this.style.width = width;
		this.style.height = height;
		this.style.backgroundColor = "#FFFFFF";

	}

	self.constructor(name,top,left,width,height);

	return self;
}

function Sheet(nRows, nColumns){
	var self = this;

	self.getHeight = function(){
		return this.size.height;
	}

	self.getWidth = function(){
		return this.size.width;
	}

	//Pre: Row[index] doesn't exists
	self.addRow = function(index){
		//Updates Sheet Height
		if(index > this.rows.length){ //TODO: check index > maxrange
			var offset = index - this.rows.length;
			this.size.height += this.defaultRowHeight*offset;
		}
		//Create new Row
		this.rows[index] = new Row(index);
		this.cells[index] = new Array();
		//alert("entra " + this.size.height);
		return this.rows[index];
	}

	//Pre: Column[index] doesn't exists
	self.addColumn = function(index){
		//Updates Sheet Height
		if(index > this.cols.length){ //TODO: check index > maxrange
			var offset = index - this.cols.length;
			this.size.width += this.defaultColumnHeight*offset;
		}
		//Create new Column
		this.cols[index] = new Column(index);
		return this.cols[index];
	}

	self.addCell = function(row,col){
		if(this.rows[row]==undefined)
			this.addRow(row);

		if(this.cols[col]==undefined)
			this.addColumn(col);

		this.cells[row][col] = new Cell(row,col);

		return this.cells[row][col];
	}
	
	self.deleteCell = function(row,col){
		if(this.cells[row]!=undefined)
			this.cells[row][col] = undefined;
	}

	self.constructor = function(nRows, nColumns){
		var sheet = document.createElement("DIV");
		this.cells = new Array();
		this.rows = new Array();
		this.cols = new Array();

		//Set default Properties
		this.defaultColumnWidth = 80;
		this.defaultRowHeight = 18;
		this.defaultFontStyleId = 0;

		this.nRows = nRows;
		this.nColumns = nColumns;
		this.namespace = new NameHandler(); //TODO: move to Book
		this.maxRange = {row:65545,col:256};
		this.size = {height:0,width:0};
	}



	/************* ModelData Interface Implementation ********************/
	self.getRowIndexByPosition = function(top){
		return parseInt(top/this.defaultRowHeight);
	}

	self.getRowSize = function(row){
		if(this.rows[row])
			return this.rows[row].getSize();
		else
			return this.defaultRowHeight; //TODO: use default configs
	}

	self.setRowSize = function(row,size){
		var previousSize = 0;
		if(this.rows[row]){
			previousSize = this.rows[row].getSize();
			this.rows[row].setSize(size);
		}else{
			var row = this.addRow(row);
			this.rows[row].setSize(size);
		}
		//Adjust Sheet Height
		this.size.height += size - previousSize;
	}

	self.getColumnSize = function(column){
		if(this.cols[column])
			return this.cols[column].getSize();
		else
			return this.defaultColumnWidth; //TODO: use default configs
	}


	self.setColumnSize = function(column,size){
		if(this.cols[column])
			this.cols[column].setSize(size);
		else{
			this.addColumn(column);//this.cols[column] = new Column(column);
			this.cols[column].setSize(size);
		}

	}

	self.getColumnName = function(column){
		return this.namespace.getColumnName(column);
	}

	self.getRowName = function(row){
		return row+1;
	}


	self.getValue = function(row,column){
		if(this.cells[row])
			if(this.cells[row][column])
				return (this.cells[row][column]).getValue();
			else
				return undefined;
		else
			return undefined;
	}

	self.setValue = function(row,column,value){
		if(this.cells[row]==undefined)
			this.addCell(row,column);
		else
			if(this.cells[row][column] == undefined){
				this.addCell(row,column);
			}

		this.cells[row][column].setValue(value);
	}

	self.calculate = function (formula,row,col) {

		var tokens = parseFormula(formula) ;//
		var result = null ;
		var strtoeval = '' ;

		var current_func = null ;
		while (tokens.moveNext()) {
    		var token = tokens.current();
    		switch (token.type) {
				case 'operator-infix' :
					strtoeval += token.value ;
				case 'operand' :
					switch (token.subtype) {
						case 'number' :
							if (current_func != undefined) {
								strtoeval += calculator.calc(current_func,token.value) ;
							}
							strtoeval += token.value ;
						break ;
						case 'range' :
		    				address = this.namespace.getRangeAddress(token.value) ;
		    				References.addReference(address,{row:row,col:col});
		    				if (address.end == undefined ) {
		    					// single value ! 
			    				var value = this.getValue(address.start.row, address.start.col) ;
			    				if (current_func != undefined) {
			    					
									strtoeval += calculator.calc(current_func,new Array(value) ) ;
									
								}else {
									strtoeval += value ;
								}
		    				}else {
		    					//range ! 
								var values = new Array() ;
								values.pop();
	    						for ( var i = address.start.row ; i <= address.end.row; i++ ) {
	    							for ( var j = address.start.col ; j <= address.end.col; j++ ) {
	    							    var value = this.getValue(i,j) ;
	    								if ( value != undefined ) values.push( value ) ;
	    							}
	    						}
	    						strtoeval += calculator.calc(current_func,values) ;
			    			}
						break ;						
					}				
				break;    		
    		
    			case 'function' :
    				if (token.subtype == 'start') {
    					current_func = token.value.toUpperCase()  ;
    				}else {
    					current_func = '' ;
    				}
    			break ;
    			case 'subexpression' :
    			alert('parettesis');
					if (token.subtype == 'start') {
						strtoeval += "(" ;
					}else {
						strtoeval += ")" ;
					}
				break; 
    		}
		}
		
		
		try {
		 	result = eval(strtoeval);
		}catch ( e) {
			result =  "INVALID";
		}
		
		return result ;
	}

	self.setFormula = function(row,column,value){
		if(this.cells[row]==undefined)
			this.addCell(row,column);
		else
			if(this.cells[row][column] == undefined){
				this.addCell(row,column);
			}
				
		this.cells[row][column].setFormula(value);
		
		if (value != undefined  ) {		
			if (value.length) {
				if  ( (value[0] == '=') || (value[0] == '+') )  {
					this.cells[row][column].setValue(this.calculate(value,row,column));
					this.cells[row][column].setValue(this.calculate(value));
				}
				else {
					this.cells[row][column].setValue(value) ;
				}
			}
		}
		self.updateReferences({row:row,col:column});
	}

	self.updateReferences = function(address){
		var references = References.getReferenced(address);
		//alert(references.toSource());
		for(var ref in references){
			//alert(ref.toSource());
			if(ref!='remove'){
				var c = references[ref];
				//alert(c.toSource()+ " " +c.row + " " + c.col + " " + this.getFormula(c.row,c.col));
				this.setFormula(c.row,c.col,this.getFormula(c.row,c.col));
			}
		}
	}
	

	self.getFormula = function(row,column){
		if(this.cells[row])
			if(this.cells[row][column])
				return (this.cells[row][column]).getFormula();
			else
				return undefined;
		else
			return undefined;
	}


	/**############# END ModelData Interface Implementation ##################*/

    //row must not be null
	self.setRow = function(index,row){
		this.rows[index] = row;
	}

	self.getRow = function(index){
		return this.rows[index];
	}

    //column must not be null
	self.setRow = function(index,column){
		this.cols[index] = column;
	}
	self.getColumn = function(index){
		return this.rows[index];
	}

	self.setCell = function(row,column,formula,style){
		if(this.cells[row] ==undefined)
			this.cells[row] = new Array();

		if(this.cells[row][column] == undefined)
			this.cells[row][column] = new Cell(row,column);

		this.cells[row][column].setFormula(formula);
	}

	self.getCell = function(row,column){
		//if(row >= this.cells.length)
		//	return undefined;
		if(this.cells[row])
			return this.cells[row][column];
		else
			return undefined;
	}

	self.createEmptyCell = function(row,column){
		var cell = new Cell(row,column);
		cell.isEmpty = true;
		return cell;
	}

	self.constructor(nRows, nColumns);
	addSheetStyleOperations(self);

	return self;
}

