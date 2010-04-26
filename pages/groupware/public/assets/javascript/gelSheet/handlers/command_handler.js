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
function CommandHandler(){
	var self = this;

	self.constructor = function(){
		self.stack = new Array();

	}

	self.runCmd = function(object,callback,args){
		var action = new Action(object,callback,args);
		self.stack.push();
		action.run();
	}

	self.undo = function(){
		var action = self.stack.pop();
		action.undo();
	}

	self.constructor();
}


function Action(){
	var self = this;

	self.constructor = function(object,callback,newValue){
		self.callback = callback;
		self.oldValue = undefined;
		self.newValue = newValue;
		self.object = object;
	}

	self.run = function(){
		this.oldValue = this.callback(this.object, this.newValue);
	}

	self.undo = function(){
		this.newValue = this.oldValue;
		this.run();
	}

	self.redo = function(){
		this.callback(this.object, this.newValue);
	}

	self.constructor();
}

