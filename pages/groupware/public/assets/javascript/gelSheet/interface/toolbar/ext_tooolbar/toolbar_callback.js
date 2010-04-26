/************* auxiliary functions **************/ 

// Generic function to save/export into differents formts including Database
function export (format){
	window.saveBook('',format);
}


/************* callback functions ***************/



//SAVE AS
function saveBookConfirm() {
	var valid_name = /([a-zA-Z0-9_-]+)$/;
	Ext.MessageBox.prompt('Save As..', 'Enter a file name', showResultText);

	function showResultText(btn, text){
        //Ext.example.msg('Forma prolija', 'Has hecho clikc en  {0} y vas a guardar el excel : "{1}".', btn, text);
        if (btn == 'ok') {
        	if ( valid_name.test(text) ) {
        		if (text.substring(text.length - 4) != ".gel") {
					text += ".gel";
				}
        		window.saveBook(text);
        	}else {
        		Ext.MessageBox.prompt('Save As..', 'Enter a valid file name', showResultText);
        	}
        }else {

        }
    };
}

function exportPDF() {
	window.export('pdf');
}

function exportXLS() {
	window.export('xls');
}

function exportXLSX() {
	window.export('xlsx');
}

function exportHTML() {
	window.export('html');
}

function exportODS() {
	Ext.Msg.alert("Warning", "Unimplemented");
}

function bold() {
	cmdSetBoldStyle() ;
}

function italic() {
	cmdSetItalicStyle() ;
}

function underline() {
	cmdSetUnderlineStyle() ;
}

function unformat() {
	cmdSetFontStyleId(0) ;
}

function setFont(p1,p2) {
	alert('setFont'); 
	alert(p1.toSource()); 
	alert(p2.toSource()); 
}

function setBorderLeft() {
	var cell = application.grid.activeCell ;
	cell.style.borderLeft = "5px solid #000000" ; 
	
	alert(cell.style.borderLeft);
 	//alert('bprderLeft');
}

function setBorderRight() {
 	alert('bprderRight');
}

function setBorderTop() {
 	alert('bprderTop');
}
function setBorderNone() {
 	alert('bprderNone');
}
