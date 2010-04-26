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
function StyleHandler(){
	var self = this;

	self.loadDefaultFont = function(){
		var defaultFont = new FontStyle(1,10,"#000000",false,false,false);
		this.fontStyles[defaultFont.id] = defaultFont;
		this.fontsIds[0] = defaultFont;
	}

	self.constructor  = function(){
		this.fontStyles = new Array();
		this.fontsIds = new Array();
		this.layers = new Array();
		this.loadDefaultFont();
	}

	self.getFontName = function(fontId){
		return window.Fonts[fontId];
	}

	self.getFontStyle = function(styleId){
		var style = this.fontStyles[styleId];
		if(style == undefined) style = this.fontStyles[0];
		return style;
	}

	self.getFontStyleById = function(index){
		var style = this.fontsIds[index];
		if(style == undefined) style = this.fontsIds[0];
		return style;
	}
	
	self.getFontStyleIdByStyle = function(fontStyle){
		return this.getFontStyleId(fontStyle.font, fontStyle.size, fontStyle.color, fontStyle.bold, fontStyle.italic, fontStyle.underline);
	}
	
	self.changeFontStyleProp = function(fontStyleId,prop,value){
		var fs = this.getFontStyleById(fontStyleId);
		var oldValue = fs[prop];
		fs[prop] = value;
		var newId = this.getFontStyleId(fs.font, fs.size, fs.color, fs.bold, fs.italic, fs.underline);
		fs[prop] = oldValue;
		return newId;
	}


	self.getFontStyleId = function(font, size, color, bold, italic, underline){
		var id = font+"|"+size+"|"+color+"|"+bold+"|"+italic+"|"+underline;
		if(this.fontStyles[id]){
			return this.fontsIds.indexOf(this.fontStyles[id]);
		}else{
			var fstyle = new FontStyle(font, size, color, bold, italic,underline);
			this.fontStyles[id] = fstyle;
			var newId = this.fontsIds.length;
			this.fontsIds[newId] = fstyle;
			return newId;
		}
	}

	self.getAllFontsStyles = function(){
		return this.fontsIds;
	}
	self.constructor();
}

function FontStyle(font, size, color, bold, italic, underline){
	var self = this;

	self.constructor  = function(font, size, color, bold, italic, underline){
		this.id = font+"|"+size+"|"+color+"|"+bold+"|"+italic+"|"+underline ;
		this.font = font 		//Font Name (Familly) Id
		this.size = size;		//Font Size
		this.color = color;		//Font Color
		this.bold = bold;		//Is Bold?
		this.italic = italic;	//Is Italic?
		this.underline = underline;	//Is Underlined?
	}

	self.constructor(font, size, color, bold, italic, underline);
	return self;
}


function LayoutStyle(bgcolor,border){
	self.contructor = function(){

	}

	return self;
}

function BlockStyle(wrap,valign,halign){
	self.contructor = function(){
		this.id = halign+"|"+valign+"|"+wrap;
		this.wrap = wrap	//Font Name (Familly) Id
		this.valign = valign;		//Font Size
		this.halign = halign;		//Font Color
	}
	
	return self;
}











