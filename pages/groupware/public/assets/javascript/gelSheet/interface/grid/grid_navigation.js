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
function addGridNavigation(grid){

	grid.moveUp = function(){
		if(grid.activeCell.getRow()>0)
			grid.setActiveCell(grid.cells[grid.activeCell.getRow()-1][grid.activeCell.getColumn()]);
		else
			if(grid.onMove){
				 grid.onMove(0,-1);
//				 grid.adjustViewPortY();
				 //TODO: the same as refresh
//				 grid.setActiveCell(grid.cells[grid.activeCell.getRow()][grid.activeCell.getColumn()]);
			}
	}

	grid.moveDown = function(){
		if(grid.activeCell.getRow()< grid.viewport.row -1)//grid.cols.length-1)
			grid.setActiveCell(grid.cells[grid.activeCell.getRow()+1][grid.activeCell.getColumn()]);
		else
			if(grid.onMove){
//    			grid.adjustViewPortY();
				grid.onMove(0,1);
				//TODO: the same as refresh
//				grid.setActiveCell(grid.cells[grid.viewport.row -1][grid.activeCell.getColumn()]);
			}

	}

	grid.moveLeft = function(){
		if(grid.activeCell.getColumn()>0)
			grid.setActiveCell(grid.cells[grid.activeCell.getRow()][grid.activeCell.getColumn()-1]);
		else
			if(grid.onMove){
//				 grid.adjustViewPortX();
				 grid.onMove(-1,0);
				 //TODO: the same as refresh
//				 grid.setActiveCell(grid.cells[grid.activeCell.getRow()][grid.activeCell.getColumn()]);
			}
	}

	grid.moveRight = function(){
		if(grid.activeCell.getColumn()< grid.viewport.col -1)//grid.cols.length-1)
		//if(grid.activeCell.offsetLeft + grid.activeCell.offsetWidth < grid.offsetWidth)//grid.cols.length-1)
			grid.setActiveCell(grid.cells[grid.activeCell.getRow()][grid.activeCell.getColumn()+1]);
		else
			if(grid.onMove){
//				 grid.adjustViewPortX();
				 grid.onMove(1,0);
				 //TODO: the same as refresh
//				 grid.setActiveCell(grid.cells[grid.activeCell.getRow()][grid.viewport.col-1]);
			}

	}


	grid.goToHome = function(){
		if(grid.onSpecialMove) grid.onSpecialMove("HOME");
		grid.setActiveCell(grid.cells[0][0]);
	}

	grid.gotoCell = function(i,j){
		if((i>=0)&&(i<grid.rows.length))
			((j>=0)&&(i<grid.cols.length))
				grid.setActiveCell(grid.cells[i][j]);

	}
}