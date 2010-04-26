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
function addApplicationEvents(app){

	app.cellFocus = function(cell){
		app.activeCell = app.grid.getActiveCell();
		var address = cell.getAddress();
		app.formulaBar.setValue(scGetCell(app.activeSheet,address.row,address.col).getValue());
	};

	app.updateActiveCell = function(newValue){
		var cell = app.grid.getActiveCell();
		var address = cell.getAddress();
		scUpdateCell(this.activeSheet,address.row,address.col,newValue);
		alert(address.row + " " + address.col);
	};
	



}