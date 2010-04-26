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
function GridModel(grid){
	var self = this;

	self.constructor = function(){
		this.viewport = {start:{row:0,col:0},end:{row:grid.getRowCount(),col:grid.getColumnCount()}};
		this.gridPosition = {x:grid.getVisibleWidth(),y:grid.getVisibleHeight()};
		this.scrollPageOffset = {x:800,y:1500};
		this.selection = new DataSelectionHandler();
		addModelStyleOperations(this);
		//alert(this.gridPosition.x + " " + this.gridPosition.y);
	}

	self.updateGridHeight = function(){
		if(grid.getMinHeight() < this.model.getHeight())
			grid.setHeight(this.model.getHeight());
	}

	self.updateGridWidth = function(){
		if(grid.getMinWidth() < this.model.getWidth())
			grid.setWidth(this.model.getWidth());
	}

	self.setDataModel = function(model){
		this.model = model;
		this.updateGridHeight();
		this.updateGridWidth();
		this.refresh();
	}

	self.refresh = function(){
		//refresh Columns
		for(var j = 0; j< (this.viewport.end.col - this.viewport.start.col +1);j++){
			grid.getColumn(j).setTitle(this.model.getColumnName(this.viewport.start.col + j));
			grid.getColumn(j).setSize(this.model.getColumnSize(this.viewport.start.col + j));
		}

		//alert(this.viewport.start.row + " " + this.viewport.start.col + "\n" + this.viewport.end.row + " " + this.viewport.end.col);
		for(var i =0; i< (this.viewport.end.row - this.viewport.start.row +1);i++){
			grid.getRow(i).setTitle(this.model.getRowName(this.viewport.start.row + i));
			grid.getRow(i).setSize(this.model.getRowSize(this.viewport.start.row + i));

			for(var j = 0; j< (this.viewport.end.col - this.viewport.start.col);j++){
				var cell = this.model.getCell(this.viewport.start.row + i, this.viewport.start.col+ j);
				//var value = this.model.getValue(this.viewport.start.row + i, this.viewport.start.col+ j);
				
				if(cell){
					var value = cell.getValue();
					if (value ==undefined) value = "";
					grid.setCell(i,j,value,cell.getFontStyleId());
//					grid.setCell(i,j,cell.getFontStyleId(),cell.getFontStyleId());
				}else
					grid.setCell(i,j,"",0);
			}
		}
		grid.adjustViewPort();
		grid.focusActiveCell();
	}

	self.refreshValues = function(){
		for(var i =0; i< (this.viewport.end.row - this.viewport.start.row +1);i++){
			for(var j = 0; j< (this.viewport.end.col - this.viewport.start.col);j++){
				var cell = this.model.getCell(this.viewport.start.row + i, this.viewport.start.col+ j);
				
				if(cell){
					var value = cell.getValue();
					if (value ==undefined) value = "";
					grid.setValue(i,j,value);
				}
			}
		}
	}
	
	//Capture and Overwrite Grid Events
	grid.onCellValueChange = function(i,j,value){
		if( value != undefined ){
			self.model.setFormula(self.viewport.start.row + i, self.viewport.start.col+ j,value);
			grid.setValue(i,j,self.model.getValue(self.viewport.start.row + i, self.viewport.start.col + j));
		}else{
			if(self.model.getCellFontStyleId(i,j)!=0)
				self.model.deleteCell(i,j);
		}
		self.refreshValues();
	}
	
	//Capture and Overwrite Grid Events
	grid.onCellFontStyleChange = function(i,j,fsId){
		if(fsId!=undefined){
			if(fsId!=self.model.getCellFontStyleId(self.viewport.start.row + i, self.viewport.start.col+ j)){
				self.model.setCellFontStyleId(self.viewport.start.row + i, self.viewport.start.col+ j,fsId);
				grid.setFontStyle(i,j,fsId);
			}
		}
	}

	grid.onActiveCellChange = function(activeCell){
		var row = self.viewport.start.row + activeCell.getRow();
		var col = self.viewport.start.col+ activeCell.getColumn();
		activeCell.setValue(self.model.getFormula(row, col));
		WrapFontStyle(activeCell,self.model.getCellFontStyleId(row,col));
		self.selection.setSelection(new Address(row, col));
		document.getElementById("FormulaBar").value=activeCell.getValue(); //TODO:Desacoplar!!!!
		//self.refresh();
	}

	grid.onMove = function(offsetX, offsetY){
		if(offsetY < 0){
			if((self.viewport.start.row + offsetY) >= 0){
				self.viewport.start.row += offsetY;
				self.viewport.end.row += offsetY;
				self.gridPosition.y +=offsetY*18;
			}
		}else{
			if(offsetY > 0){
				self.viewport.start.row += offsetY;
				self.viewport.end.row += offsetY;
				self.gridPosition.y += offsetY*18;
			}
		}

		if(offsetX < 0){
			if((self.viewport.start.col + offsetX) >= 0){
				self.viewport.start.col += offsetX;
				self.viewport.end.col += offsetX;
			}
		}else{
			if(offsetX > 0){
				self.viewport.start.col += offsetX;
				self.viewport.end.col += offsetX;
			}
		}

		self.refresh();
	}
	
	grid.onSpecialMove = function(moveType){
		var offsetX = self.viewport.end.col - self.viewport.start.col;
		var offsetY = self.viewport.end.row - self.viewport.start.row;

		if(moveType == "HOME"){
			self.viewport.start.col = 0;
			self.viewport.end.col   = offsetX;
			self.viewport.start.row = 0;
			self.viewport.end.row   = offsetY;
		}

		self.refresh();
	}
	
	grid.onColumnSizeChange = function (column){
		self.model.setColumnSize(column.getIndex()+self.viewport.start.col, column.getSize());
	}

	grid.onColumnSelect = function(add,involvedColumn){
		var column = self.model.getColumn();
		if(add)
			self.selection.addSelection(new Address(undefined,self.viewport.start.col + involvedColumn.getIndex()));
		else
			self.selection.setSelection(new Address(undefined,self.viewport.start.col + involvedColumn.getIndex()));
	}

	grid.onHorizontalScroll = function (left){
		var offset = self.viewport.end.col- self.viewport.start.col;
		self.viewport.start.col = parseInt(left/80);
		self.viewport.end.col = parseInt(left/80) + offset;

		if(self.viewport.end.col*80 + self.scrollPageOffset.x > grid.getWidth()){
			grid.setWidth(grid.getWidth() + self.scrollPageOffset.x);
		}

		self.refresh();
	}

	grid.onVerticalScroll = function (top){
		var offset = self.viewport.end.row- self.viewport.start.row;
		self.viewport.start.row = parseInt(top/18);
		self.viewport.end.row = parseInt(top/18) + offset;

		if(self.viewport.end.row*18 + self.scrollPageOffset.y > grid.getHeight()){
			grid.setHeight(grid.getHeight() + self.scrollPageOffset.y);
		}

		self.refresh();
	}
	
	grid.scrollDown = function (offset){
		var delta = self.viewport.start.row - offset;
		if(delta<0)
			offset = offset+delta;
		
		self.viewport.start.row = self.viewport.start.row + offset;
		self.viewport.end.row =  self.viewport.end.row + offset;

		if(self.viewport.end.row*18 + self.scrollPageOffset.y > grid.getHeight()){
			grid.setHeight(grid.getHeight() + self.scrollPageOffset.y);
		}

		self.refresh();
	}

	self.constructor();
	return self;
}