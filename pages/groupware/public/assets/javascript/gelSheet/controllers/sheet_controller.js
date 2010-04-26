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
function scGetRow(sheet,index){
	var row = sheet.getRow(index);
	if(row==undefined)
		row = new Row(index);
    return row;
}

function scGetColumn(sheet,index){
	var col = sheet.getColumn(index);
	if(col==undefined)
		col = new Column(index);
    return col;
}

function scSetRow(sheet,row){
    sheet.setRow(row);
}

function scSetColumn(sheet,column){
    sheet.setColumn(column);
}

function scUpdateCell(sheet,row,col,formula,style){
	var cell = sheet.getCell(row,col);
	if(cell==undefined)
		cell = new Cell(row,col);
	cell.setFormula(formula);
	if(style) cell.setStyle(style);
	scSetCell(sheet,cell);
    return cell;
}

function scGetCell(sheet,row,col){
	var cell = sheet.getCell(row,col);
	if(cell==undefined)
		cell = new Cell(row,col);
    return cell;
}

function scSetCell(sheet,cell){
    if(sheet.cells[cell.row])
       sheet.cells[cell.row] = new Array();

	sheet.cells[cell.row][cell.column] = cell;
}

function scLoadSheet(sheet, book){
//	var book = response.data;
//	alert(book.toSource());
	application.activeBook = new Book(book.name);
	application.activeBook.setId(book.id);
	application.setBookName(book.name);
	var values = book.sheets[0].cells;
	//alert(values.length + "   " + data.sheets[0].cells.toSource());

	for(var i=0;i<values.length;i++){
		sheet.setFormula(values[i].r,values[i].c,values[i].f);
	}
}

function scSheetToJSON(sheet){
    sheet = window.activeSheet; //TODO: sacar

    var json = '{"sheetId":null,"cells":[';
    var cells = "";
    for(var i=0;i<sheet.cells.length;i++){
    	if(sheet.cells[i])
        	for(var j=0;j<sheet.cells[i].length;j++){
                if(sheet.cells[i][j])
               	 	cells+=',{"dataRow":"' + i +'","dataColumn":"'+j+'","cellFormula":"'+ sheet.cells[i][j].getFormula()+'","fontStyleId":"'+ sheet.cells[i][j].getFontStyleId() + '","layoutStyleId":"0"}';
        	}
    }
    json += cells.substr(1);
    json += "]"; //End of Cells
    json += "}"; //End of Sheet

    //errorConsole.clear();
    //errorConsole.println(json);
    return json;
}

function scGetEnd(){
    var sheet = window.activeSheet;
    var count=0;

    for(var idx in sheet.rows)
        count++;

    alert(count);
}

function scPrintCell(cell){
	return cell.formula + " fsId: " + cell.fontStyleId;
}

function scPrintSheet(sheet){
	var ret = "Filas: " + sheet.cells.length + "<br>";
	//ret = "Cols: " + sheet.cells[0].length + "\n";
	for(var i=0;i<sheet.cells.length;i++){
		if(sheet.cells[i]){
			for(var j=0;j<sheet.cells[i].length;j++){
				if(sheet.cells[i][j])
					ret += "("+ i + "," + j + ")" + scPrintCell(sheet.cells[i][j])+ "<br>";
			}
		}
	}
	return ret;
}


