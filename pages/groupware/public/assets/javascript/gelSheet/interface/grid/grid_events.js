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
/**
* This function will create events to be overwritten by creator
* The grid will call them when some of then should be fired
*/
function addGridEvents(grid){
	/*//Selection Events
	grid.onSelectionChange = function(newSelection){}    //Fired after selection is changed
	grid.onMove = function(offsetX, offsetY){}   //Fired after the view port is changed
	//Cell Events
	grid.onCellDataChange = function(involvedRange){} //Fired after column properties are changed
	grid.onCellFontFormatChange = function(involvedRange){} //Fired after column properties are changed
	grid.onCellLayerFormatChange = function(involvedRange){} //Fired after column properties are changed
	grid.onCellLayoutFormatChange = function(involvedRange){} //Fired after column properties are changed
	//Columns Events
	grid.onColumnSizeChange = function(involvedColumns){} //Fired after column size is changed
	grid.onColumnDataChange = function(involvedColumns){} //Fired after column properties are changed
	grid.onColumnSelect 	= function(add, involvedColumn){}  //Fired after column is selected (add to selection or set selection)

	grid.onRowSizeChange = function(involvedRows){}    //Fired after row size is changed
	//Rows Events
	grid.onRowDataChange = function(involvedRows){} //Fired after row properties are changed
	*/

	//grid.selectionChange = function(newSelection){}    //Fired after selection is changed
	grid.viewPortChange = function(viewportProps){}   //Fired after the view port is changed
	//Cell Events
	grid.cellDataChange = function(involvedRange){} 	//Fired after column properties are changed
	grid.cellFontFormatChange = function(involvedRange){} //Fired after column properties are changed
	grid.cellLayerFormatChange = function(involvedRange){} //Fired after column properties are changed
	grid.cellLayoutFormatChange = function(involvedRange){} //Fired after column properties are changed
	//Columns Events
	grid.columnSizeChange = function(involvedColumns){} //Fired after column size is changed
	grid.columnDataChange = function(involvedColumns){} //Fired after column properties are changed
	//Rows Events
	grid.rowSizeChange = function(involvedRows){}    //Fired after row size is changed
	grid.rowDataChange = function(involvedRows){} //Fired after row properties are changed

}

function addGridMethods(grid){
	grid.selectColumn = function(col){
		for(var j=0;j< grid.cols.length;j++)
			grid.cols[j].deactivate();

		grid.selectionManager.setSelection(col);

		for(var i=0;i< grid.rows.length;i++)
			grid.rows[i].activate();
		//Fire Fake Event
		if(grid.onColumnSelect) grid.onColumnSelect(false,col);
		//if(grid.onSelectionChange) grid.onSelectionChange(col);
	}

	grid.selectRow = function(row){
		for(var j=0;j< grid.rows.length;j++)
			grid.rows[j].deactivate();

		grid.selectionManager.setSelection(row);

		for(var i=0;i< grid.cols.length;i++)
			grid.cols[i].activate();
		//Fire Fake Event
		if(grid.onSelectionChange) grid.onSelectionChange(row);
	}

	grid.selectCell = function(cell){
		for(var j=0;j< grid.rows.length;j++)
			grid.rows[j].deactivate();

		for(var i=0;i< grid.cols.length;i++)
			grid.cols[i].deactivate();

		grid.selectionManager.setSelection(cell);
		//Fire Fake Event
		if(grid.onSelectionChange) onSelectionChange(cell);
	}

}


/**
* Adds events to cells where they must see Grid elements
* This function must be defined before contructor, because it uses it
*/
function addGridCellEvents(grid, cell){
	cell.onmousedown = function(e){
	    e ? e : e=window.event; //get event for IE
	    grid.selecting = true;

		//grid.selectionManager.setSelection(cell);
		grid.selectCell(cell);
		grid.setActiveCell(cell);
	}

	cell.onmouseover = function(e){
		e ? e : e =window.event; //get event for IE
		if(grid.selecting)
			grid.selectionManager.addSelection(cell);
		//if(grid.selectionChange) grid.selectionChange();
	}
}

/**
* Adds events to Rows where they must see Grid elements
* This function must be defined before contructor, because it uses it
*/
function addGridRowEvents(grid,row){


	row.rowmousedown = function(e){
		e ? e : e = window.event; //get event for IE
	    if(!grid.rowResizing){
		    grid.selectingRow = true;
			grid.selectRow(this);
			if(grid.selectionChange) grid.selectionChange();
		}

		if(grid.selectionChange) grid.selectionChange();
	}

	row.onmouseover = function(e){
		e ? e : e =window.event; //get event for IE
		if(grid.selectingRow)
			grid.selectionManager.addSelection(row);
		if(grid.selectionChange) grid.selectionChange();
	}
}

/**
* Adds events to Columns where they must see Grid elements
* This function must be defined before contructor, because it uses it
*/
function addGridColumnEvents(grid,col){

	col.onresizing = function(e){//could not use onresize because IE use it
		//e ? e : e = window.event; //get event for IE
		grid.columnUsed = col;
		grid.verticalResizer.setLeft(e.screenX);
		grid.verticalResizer.startResizing();
		grid.columnResizing = true;
		//if(grid.selectionChange) grid.selectionChange();
	}

	col.onmousedown = function(e){
	    e ? e : e = window.event; //get event for IE
	    if(!grid.columnResizing){
		    grid.selectingCol = true;
			grid.selectColumn(this);
			if(grid.onColumnSelect) grid.onColumnSelect(false,col);
			//if(grid.selectionChange) grid.selectionChange();
		}
	}

	col.onmouseover = function(e){
		e ? e : e =window.event; //get event for IE
		if(grid.selectingCol){
			grid.selectionManager.addSelection(col);
			if(grid.onColumnSelect) grid.onColumnSelect(true,col);
		}
		//if(grid.selectionChange) grid.selectionChange();
	}

	grid.scrollbars.onVerticalScroll = function(top){
		//if(grid.cells.[0][0].offsetTop
		if(grid.onVerticalScroll) grid.onVerticalScroll(parseInt(top));//+parseInt(grid.grid.style.height));
	}

	grid.scrollbars.onHorizontalScroll = function(left){
		//if(grid.cells.[0][0].offsetTop
		if(grid.onHorizontalScroll) grid.onHorizontalScroll(parseInt(left));//+parseInt(grid.grid.style.height));
	}

	/*grid.cellEditor.onchange = function(){
		alert("Cambia");
	}*/
}

function addFormulaBarEvents(grid, formulaBar){
    formulaBar.onchange = function(){
        var cell = grid.activeCell;
        scUpdateCell(window.activeSheet,cell.getRow(),cell.getColumn(),this.value);
    }
}

