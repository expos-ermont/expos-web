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

//TODO: remove section parameter, pass height and width
function Grid(width,height){

    var self = document.createElement("DIV");
    self.cfgs = new Array();
	self.cfgs["defaultRowHeight"]	 	= 18;
	self.cfgs["defaultRowWidth"] 		= 20;
	self.cfgs["defaultColWidth"] 		= 80;
	self.cfgs["defaultHandlerWidth"] 	= 5;
	self.cfgs["scrollbarWidth"] 		= 17;
	self.cfgs["scrollbarHeight"]		= 16;

	self.focusActiveCell = function(){
		this.cellEditor.fitToCell(this.activeCell);
		this.selectorBox.fitToRange(this.activeCell);
	}
	
	self.setActiveCell = function(cell){
		//Deactivate all selected objects
		/*var selection = self.selectionManager.getSelection();
		for(var i=0; i<selection.length;i++){
			selection[i].deactivate();
		}*/

		if(this.onCellValueChange) this.onCellValueChange(this.activeCell.getRow(),this.activeCell.getColumn(),this.cellEditor.getValue());
		if(this.onCellFontStyleChange) this.onCellFontStyleChange(this.activeCell.getRow(),this.activeCell.getColumn(),this.cellEditor.getFontStyleId());
		this.rows[this.activeCell.getRow()].deactivate();
		this.cols[this.activeCell.getColumn()].deactivate();

		this.selectionManager.setSelection(cell);
		//this.activeCell.deactivate();
		this.rows[cell.row].activate();
		this.cols[cell.column].activate();
		this.activeCell = cell;
		//alert(self.cellEditor.fitToCell.toSource());
		this.focusActiveCell();
//		this.cellEditor.fitToCell(cell);
//		this.selectorBox.fitToRange(cell);
		if(this.onActiveCellChange) this.onActiveCellChange(this.cellEditor);
	}
	
	self.editActiveCell = function(newValue){
		this.cellEditor.updateValue(newValue);
	}
	
	self.onmousedown = function(e){
//		e ? e : e =window.event; //get event for IE
//		//this.selecting = true;
//		if(this.onActiveCellChange) this.onActiveCellChange(this.cellEditor);
		return false; 			//Disables Text Selection
	}

	self.onmouseup = function(e){
		e ? e : e =window.event; //get event for IE
		this.selecting = false;
		this.selectingRow = false;
		this.selectingCol = false;

		if(this.columnResizing){
			this.resizeColumn();
		}

		this.columnResizing = false;
	}

	self.onmousemove = function(e){
		e ? e : e = window.event; //get event for IE
		if(self.columnResizing){
			self.verticalResizer.setLeft(e.screenX);
		}
	}

	self.addRow = function(){
		//Creates Visual Row
		var row = new VRow(this.rows.length);
		row.setHeight(this.cfgs["defaultRowHeight"]);
		//Adds VRow to grids Rows
		var i = this.rows.push(row)-1;
		this.cells[i] = new Array();

		//Adds a new VCell for each column in Grid
		for(var j=0;j<this.cols.length;j++){
        	var cell = new VCell(i,j);
        	//cell.innerHTML = i + ", " + j;
   			this.cols[j].addCell(cell);
        	row.addCell(cell);
        	this.cells[i][j] = cell;
        	//Add events to new VCell
        	addGridCellEvents(self,cell);
        }
        //Overwrites Row events
		addGridRowEvents(self,row);
		//Finally add Row to Browser
   		this.body.appendChild(row);
	}


	self.addColumn = function(){
		var column = new VColumn(this.cols.length);
		column.setHeight(this.cfgs["defaultRowHeight"]);
		column.setWidth(this.cfgs["defaultColWidth"]);
		var idx = this.cols.push(column);
		addGridColumnEvents(self,column);

		for(var i=0;i<this.rows.length;i++){
        	var cell = new VCell(i,idx);
        	//cell.innerHTML = i;
        	this.rows[i].addCell(cell);
        	this.cells[i].push(cell);
        	column.addCell(cell);
        	addGridCellEvents(self,cell);
        }
   		this.colHeader.appendChild(column);
	}

	self.adjustViewPortX = function(){
		if(this.viewport.col >= this.cols.length)
			this.viewport.col = this.cols.length -1;

		var width = parseInt(this.offsetWidth);
		if(this.cols[this.viewport.col].offsetLeft >= width){
			//alert("Mayor " + this.viewport.col +  "  " + this.cols[this.viewport.col].offsetLeft  +" / " + width);
			for(var j = this.viewport.col; this.cols[j].offsetLeft > width; j--)
				this.viewport.col = j-1;

		}else{
			//alert("Menor");
			for(var j = this.viewport.col-1;(this.cols[j].offsetLeft + this.cols[j].offsetWidth) < width;j++)
				this.viewport.col = j + 1;
		}

		//alert(this.viewport.col);
	}

	self.adjustViewPortY = function(){
		if(this.viewport.row >= this.rows.length)
			this.viewport.row = this.rows.length -1;

		var height = parseInt(this.style.height);

		if(this.rows[this.viewport.row].offsetTop > height){
			//alert("Mayor");
			for(var i = this.viewport.row; this.rows[i].offsetTop >= height; i--)
				this.viewport.row = i-1;
		}else{
			//alert("Menor");
			//var i =this.viewport.row;
			//alert(this.rows.length + " antes " + j + " " + (this.rows[j].offsetTop + this.rows[j].offsetHeight) + " / " +height);
			try{for(var i = this.viewport.row; (i<this.rows.length) && (this.rows[i].offsetTop + this.rows[i].offsetHeight) <= height;i++)
				this.viewport.row = i;
				}catch(e){}
		}
	}
	self.adjustViewPort = function(){
		self.adjustViewPortX();
		self.adjustViewPortY();
	}

	self.setDimensions = function(width,height){
		this.scrollbars.setHeight(height);
		this.scrollbars.setWidth(width);
	}

	self.getMinHeight = function(){
		return this.minDimension.height;
	}

	self.getHeight = function(){
		return this.scrollbars.getHeight();
	}

	self.getVisibleHeight = function(){
		return parseInt(this.grid.style.height);
	}

	self.setHeight = function(height){
		this.scrollbars.setHeight(height);
	}

	self.getVisibleWidth = function(){
		return parseInt(this.grid.style.width);
	}

	self.getMinWidth = function(){
		return this.minDimension.width;
	}

	self.getWidth = function(){
		return this.scrollbars.getWidth();
	}

	self.setWidth = function(width){
		this.scrollbars.setWidth(width);
	}

    self.constructor = function(width,height){
	//Private Attributes definitions
		//Data Attributes
    	this.cols = new Array();
        this.rows = new Array();
        this.cells = new Array();

		//Events Handling Flags
		this.selecting = false;
		this.selectingRow = false;
		this.selectingCol = false;
		this.columnResizing = false;

    	//Temporal References
		this.columnUsed = undefined; // used for mantain a reference for resizing

	//Main Properties Setup
    	var gridHeight = height - this.cfgs["scrollbarHeight"];
    	var gridWidth = width - this.cfgs["scrollbarWidth"];

    	var ncols = (gridWidth - this.cfgs["defaultRowWidth"])/ this.cfgs["defaultColWidth"];
    	var nrows = (gridHeight - this.cfgs["defaultRowHeight"])/ this.cfgs["defaultRowHeight"];

		//Visual Properties
    	this.viewport = {row:parseInt(nrows),col:parseInt(ncols)};
		this.minDimension = {width:width*2 , height:height*2};

		createGridGui(self,width,height);

		//Disable Text Selection
		document.onselectstart = function() {return false;} // For IE

		//Grid Columns and Rows Creation
		for(var j=0;j<ncols;j++){
			self.addColumn();
		}

		for(var i=0;i<nrows;i++){
			self.addRow();
		}

		//Add Managers
		this.selectionManager = new SelectionHandler();
    }

	self.inicialize = function(){
		this.activeCell = this.cells[0][0];
		this.setActiveCell(this.cells[0][0]);
		this.adjustViewPort();
	}

    self.constructor(width,height);

	addGridOperations(self);
	addGridNavigation(self);
	addGridMethods(self);
    return self;
}

