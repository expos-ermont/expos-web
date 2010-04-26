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

function Ajax(){
   try{
       req = new XMLHttpRequest();
   }
   catch(err1){
      try{
          req = new ActiveXObject("Msxml2.XMLHTTP");
      }
      catch(err2){
          try{
              req = new ActiveXObject("Microsoft.XMLHTTP");
          }
          catch(err3){
              return false;
          }
      }
   }
   return req;
}


var dataRequest = new Ajax();

function getServerData(callback,url,params){

	dataRequest.open("GET", window.enviromentAjaxPrefix+url+"?"+params, true);
	dataRequest.onreadystatechange = function(){
		if (dataRequest.readyState == 4){
			if (dataRequest.status == 200){
			    try{
				    var data = eval("("+dataRequest.responseText+")");
				    callback(data);
				    //application.loadSheet(data);
				}catch(e){
					alert("ajax.js:getServerData =>"+e.toSource());
				}

			}
    	}
    }
    dataRequest.send(null);
}


function loadData(bookId){
	getServerData(application.loadSheet,"","c=Spreadsheet&m=loadBook&param1="+bookId);
}

function loadUserBooks(){
	getServerData(application.openFiles,"","c=User&m=getUserBooks&param1="+null);
}

function bookSaveServerResponse(data){
	if(data.Error)
		alert(data.Message);
	else{
		application.activeBook.setId(data.Data.BookId);
		bookId = application.activeBook.getId();
		parent.og.openLink(parent.og.getUrl('files', 'save_spreadsheet', {
				id: window.ogID || 0,
				book: bookId,
				name: application.activeBook.getName()
			}), {
			onSuccess: function(data) {
				window.ogID = data.sprdID
			},
			onError: function(data) {
				deleteBook(bookId);
			}
		});
	}
}

function sendBook(data, format){

	if( (format == 'pdf')  ||
		(format == 'xls') ||
		(format == 'xlsx') ||
		(format == 'ods') ||
		(format == 'xml') ||
		(format == 'csv') ||
		(format == 'html')
	){
		var url = window.enviromentAjaxPrefix + "index.php?c=Spreadsheet&m=saveBook&param1="+escape(data)+"&param2=json"+"&param3="+format ;
		//link.href = "../php/index.php?c=Spreadsheet&m=saveBook&param1="+escape(data)+"&param2=json"+"&param3="+format;
		//document.body.appendChild(link);
		window.open(url);
		//var window1 = window.open(url,'mywindow','width=600,height=500,screenX=0,screenY=0,left=0,top=0,toolbar=ye s,location=yes,directories=yes,status=yes,menubar= yes,scrollbars=yes,copyhistory=yes,resizable=yes')
		//window1.document.execCommand("SaveAs","1","");
		//link.click();
	}
	var callback;
	if(format == undefined ) {
		callback = bookSaveServerResponse;
		sendServerData("","c=Spreadsheet&m=saveBook&param1="+escape(data)+"&param2=json"+"&param3="+format,callback);
	}
}

var dataServerSend = new Ajax();

function sendServerData(url,params,callback){
	dataServerSend.open("GET",window.enviromentAjaxPrefix+url+"?"+params, true);
	dataServerSend.onreadystatechange = function(){
		if (dataServerSend.readyState == 4){
			if (dataServerSend.status == 200){
			    //try{
			    	//alert("#####"+dataServerSend.responseText);
				    var data = eval("("+dataServerSend.responseText+")");
					if(callback)
						callback(data);
				    //application.loadSheet(data);
				/*}catch(e){
					alert("ajax.js:getServerData =>"+e);
				}*/

			}
    	}
    }
    dataServerSend.send(null);
}

var dataOpengooServerSend = new Ajax();
function sendOpengooData(url,params){
	dataOpengooServerSend.open("GET",url+"?"+params, true);
	dataOpengooServerSend.onreadystatechange = function(){
		if (dataOpengooServerSend.readyState == 4){
			if (dataOpengooServerSend.status == 200){
			    //try{
			    	//alert("#####"+dataServerSend.responseText);
				    var data = eval("("+dataOpengooServerSend.responseText+")");
				    og.processResponse(data);
//					if(callback)
//						callback(data);
//				    //application.loadSheet(data);
				/*}catch(e){
					alert("ajax.js:getServerData =>"+e);
				}*/

			}
    	}
    }
	dataOpengooServerSend.send(null);
}

function deleteBook(id, callback) {
	ajx = new Ajax();
	ajx.open("GET", window.enviromentAjaxPrefix + "?c=Spreadsheet&m=deleteBook&param1=" + id, true);
	ajx.onreadystatechange = function() {
		if (ajx.readyState == 4){
			if (ajx.status == 200){
				var data = eval("(" + ajx.responseText + ")");
				if (callback) {
					callback(data);
				}
			}
    	}
    }
    ajx.send(null);
}