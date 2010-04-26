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

function OpenFileDialog(top,left,height,width){
	var self = document.createElement("DIV");
	self.addHeader = function(file){
		var row = document.createElement("TR");
		var td = document.createElement("TH");
		var td2 = document.createElement("TH");
		var td3 = document.createElement("TH");
		td.innerHTML = "Filename";
		td.style.width = "34%";
		td2.innerHTML = "Created On";
		td2.style.width = "33%";
		td3.innerHTML = "Creator";
		td3.style.width = "33%";

		row.appendChild(td);
		row.appendChild(td2);
		row.appendChild(td3);
		this.fileHeader.appendChild(row);
	}
	self.show = function(){
		this.style.visibility = "visible";
	}

	self.hide = function(){
		this.style.visibility = "hidden";
	}

	self.addFile = function(file){
		alert(file.toSource());
		var row = document.createElement("TR");
		row.className = "OpenFileDialog";
		row.onclick = function(){
			alert(file.id);
			self.hide();
		}

		var td = document.createElement("TD");
		var td2 = document.createElement("TD");
		var td3 = document.createElement("TD");
		td.innerHTML = file.name;
		td2.innerHTML = file.date;
		td3.innerHTML = file.user;
		row.appendChild(td);
		row.appendChild(td2);
		row.appendChild(td3);
		this.fileBody.appendChild(row);
	}


	self.constructor = function(top,left,height,width){
		this.className = "OpenFileDialog";
		this.style.position = "absolute";
		this.style.top = px(top);
		this.style.left = px(left);
		this.style.height = px(height);
		this.style.width = px(width);

		this.fileList = document.createElement("TABLE");
		this.fileList.style.width = "100%";
		this.fileList.style.height = "100%";
		this.fileHeader = document.createElement("THEAD");

		this.fileBody = document.createElement("TBODY");
		this.addHeader();

		this.appendChild(this.fileHeader);
		this.appendChild(this.fileBody);

	}

	self.constructor(top,left,height,width);
	return self;
}