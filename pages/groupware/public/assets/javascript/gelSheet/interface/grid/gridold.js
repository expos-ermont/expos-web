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
	var cfgs = new Array();
	cfgs["defaultRowHeight"]	 	= 18;
	cfgs["defaultRowWidth"] 		= 20;
	cfgs["defaultColWidth"] 		= 80;
	cfgs["defaultHandlerWidth"] 	= 5;

    var self = document.createElement("DIV");

	self.setActiveCell = function(cell){
		//this.rows[this.activeCell.row].deactivate();
		//this.cols[this.activeCell.column].deactivate();
		//alert(this.activeCell.toSource());
		alert(self.activeCell.toSource());
		this.activeCell.deactivate();
		this.rows[cell.row].activate();
		this.cols[cell.column].activate();
		cell.activate();
		this.activeCell = cell;
	}

	self.addRow = function(){
		//Creates Visual Row
		var row = new VRow(this.rows.length+1);
		row.setHeight(cfgs["defaultRowHeight"]);
		//Adds VRow to grids Rows
		var i = this.rows.push(row);
		this.cells[i] = new Array();

		//Adds a new VCell for each column in Grid
		var counter =  self.colHeader.length;
		//alert(counter);

		for(var j=0;j<counter;j++){
        	var cell = new VCell(i,j);
        	cell.innerHTML = i + ", " + j;
   			this.cols[i].addCell(cell);
        	row.addCell(cell);
        	this.cells[i][j] = cell;
        	//Add events to new VCell
        	//addGridCellEvents(self,cell);
        }
        //Overwrites Row events
		addGridRowEvents(self,row);
		//Finally add Row to Browser
   		this.body.appendChild(row);
	}


	self.addColumn = function(){
		try{
			var index = this.colHeader.childNodes[0].length;
		}catch(e){
			var index =0;
		}
		var column = new VColumn(index);
		column.setHeight(cfgs["defaultRowHeight"]);
		column.setWidth(cfgs["defaultColWidth"]);
		this.cols.push(column);
		addGridColumnEvents(self,column);
		this.colHeader.appendChild(column);

		for(var i=0;i<this.rows.length;i++){
        	var cell = new VCell(i,this.body.childNodes[i].length+1);
        	cell.innerHTML = i;
        	this.rows[i].addCell(cell);
        	this.cells[i].push(cell);
        	column.addCell(cell);
        	addGridCellEvents(self,cell);
        }
	}


    self.constructor = function(width,height){
    	//TODO: move to defaults module
    	//Private vars are created here
    	var nrows = (height - cfgs["defaultRowHeight"])/ cfgs["defaultRowHeight"];
    	var ncols = (width - cfgs["defaultRowHeight"])/ cfgs["defaultColWidth"] ;
    	if(nrows <= 0) nrows =10;
    	if(ncols <= 0) ncols =10;

        this.id = "Grid";
        this.style.left = "0px";
        this.style.top = "0px";
        this.style.height = px(height);
        this.style.width = px(width);
        this.style.position = "absolute";
        this.style.overflow = "hidden";
        this.style.border = "2px solid #000000";

        this.cols = new Array();
        self.rows = new Array();
        self.cells = new Array();
        self.startRange = {row:0,col:0};
        self.endRange = {row:nrows,col:ncols};

		//sets de default style of the grid
		this.grid = document.createElement("TABLE");
        this.grid.style.left = "0px";
        this.grid.style.top = "0px";
        this.grid.style.height = "100%";
        this.grid.style.width = "100%";
        this.grid.style.position = "absolute";
        this.grid.style.borderCollapse= "collapse";
        //this.grid.style.overflow = "hidden";
        this.grid.style.backgroundColor ="#0F0";

		this.head = document.createElement("THEAD");
		this.colHeader = new VRow(0);//document.createElement("TR");
		this.head.appendChild(this.colHeader);
		this.body = document.createElement("TBODY");


		for(var j=0;j<ncols;j++){
			self.addColumn();
		}

		for(var i=0;i<nrows;i++){
			self.addRow();
		}


		this.grid.appendChild(this.head);
		this.grid.appendChild(this.body);
		this.appendChild(this.grid);
		//alert(this.cells[1].toSource());
		//this.activeCell = this.cells[1];
    }

    self.constructor(width,height);

    return self;
}

