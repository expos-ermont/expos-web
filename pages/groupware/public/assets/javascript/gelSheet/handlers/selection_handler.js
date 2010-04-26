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
/******************* Selection Handler ************************************
**  Handles objects selection.
** 	Objects must implement interface with select() and unselect() methods
***************************************************************************/

function SelectionHandler(){
		var self = this;

		self.constructor = function(){
			this.selection = new Array();
		}

		/*self.setActive = function(range){
			this.unsetSelection();
			this.selection.push(range);
			range.activate();
		}*/

		self.unsetSelection = function(){
			while(this.selection.length>0){
				var item = this.selection.pop();
				item.unselect();
			}
			//window.grid.updateSelection();
		}

		self.setSelection = function(range){
			this.unsetSelection();
			this.selection.push(range);
			range.select();
			EventManager.fire(EVT_SELECTION_CHANGE,range);
			//window.grid.updateSelection(this.selection);
		}

		self.getSelection = function(){
			return this.selection;
		}

		self.addSelection = function(range){
			this.selection.push(range);
			range.select();
		}

		self.constructor();
		return self;
	}