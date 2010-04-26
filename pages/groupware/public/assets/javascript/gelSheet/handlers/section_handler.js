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
function SectionHandler(parent){
	var self = this;

	self.constructor = function(parent){
		this.parent = parent;
		this.sections = new Array();
	}

	self.addSection = function(section,refresh){
		this.sections.push(section);
		if(refresh) this.refresh();
		this.parent.appendChild(section);
	}

	self.refresh = function(){
		if(this.parent==document.body){
		    /*if(window.innerWidth){
			    var xRatio = (parseInt(window.innerWidth)-this.parent.scrollLeft)/100 ;
			    var yRatio = (parseInt(window.innerHeight)-this.parent.scrollTop)/100;
			 }else{ //IS IE
			    var height = parseInt(document.body.clientHeight);
			    var width = parseInt(document.body.clientWidth);
			    if(height<400) height = 400; //Minimun
			    if(width<600) width = 600; //Minimun
			    var xRatio = (height-this.parent.scrollLeft)/100 ;
			    var yRatio = (width-this.parent.scrollTop)/100;
			 }*/
			 var container = document.documentElement ? document.documentElement : document.body;
			 var xRatio = (container.clientWidth-16) /100;
			 var yRatio = (container.clientHeight-16)/100;
		}else{
			var xRatio = parseInt(this.parent.offsetWidth)/100;
			var yRatio = parseInt(this.parent.offsetHeight)/100;
		}

		for(var i=0;i<this.sections.length;i++){
			var section = this.sections[i];
			section.update(xRatio,yRatio);
		}
	}

	self.constructor(parent);
	return self;
}

function Section(top,left,height,width){
	var self = document.createElement("DIV");

	self.constructor = function(top,left,height,width){
		this.top = top;
		this.left = left;
		this.height = height;
		this.width = width;
		//Style Definition
		this.style.position = "absolute";
		this.style.border = "1px solid #000";
		WrapStyle(this);
		this.sections = undefined;
	}

	self.addSection = function(section){
		if(this.sections==undefined)
			this.sections = new SectionHandler(this);

		this.sections.addSection(section);
	}

	self.update = function(xRatio,yRatio){
		this.style.top	  = px(this.top*yRatio);
		this.style.left   = px(this.left*xRatio);
		this.style.height = px(this.height*yRatio);
		this.style.width  = px(this.width*xRatio);
		if(this.sections!=undefined)
			this.sections.refresh();
	}


	self.constructor(top,left,height,width);
	return self;
}
