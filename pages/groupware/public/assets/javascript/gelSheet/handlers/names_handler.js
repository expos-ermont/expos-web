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
function NameHandler(){
	var self = this;
	self.constructor = function(){
		this.names = new Array();
		this.columnSequence = new Array("A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z");
		this.columnIndexes = new Object();
		for(var i=0;i<this.columnSequence.length;i++){
			this.columnIndexes[this.columnSequence[i]] = i;
		}
		//this.columnSequence = new Array("1","2","3");//,"4","5","6","7","8","9");
		//this.columnSequence = new Array("A","B","C");
	}
	
	self.getRangeCells = function(row,col){
		row = (row=="")? undefined:row-1;
		col = (col=="")? undefined:this.getColumnIndex(col);
		
		return {row:row,col:col};
	}
	
	self.getSimpleRangeAddress = function (address){
		var regArray = /^([A-Z]*)(\d*)$/.exec(address);
//		alert(regArray.toSource());
		if(regArray)
			return this.getRangeCells(regArray[2],regArray[1]);
	}
	
	self.getRangeAddress = function(address){
		address = address.toUpperCase();
		var ranges = address.split(":");
		var range = {};
		if(ranges.length >2)
			return undefined;
		
		if(ranges.length){
			range.start = this.getSimpleRangeAddress(ranges[0]);
			if(ranges.length>1)
				range.end = this.getSimpleRangeAddress(ranges[1]);	
		}else{
			range.start = this.getSimpleRangeAddress(address);
		}
		return range;
	}
	
	self.getColumnName = function(index){
		var base = this.columnSequence.length;
		var name = "";

		while(index>=0){
			name = this.columnSequence[parseInt(index)%base]+ name;
			index = parseInt(index /base) -1;
		}

		return name;
	}
	
	self.getColumnIndex = function(name){
		var base = this.columnSequence.length;
		var index = 0;
		len = 0;
		
		while(len<name.length){
			index = index*base +1+parseInt(this.columnIndexes[name[len]]);
			len++;
		}

		return index -1;
	}

	self.constructor();
	return self;
}

