function ReferenceHandler() {
	var self = this ;
	
	self.constructor = function () {
		this.targets = new Array() ; //[row,col,ROW,COL]->array
		this.sources = new Array() ; //[x,y]->
	}
	
	self.clearReference = function(source){
	}
	self.addReference = function (target, source) {
		var end = (target.end)?target.end:target.start; 

		if ( this.sources[source.row] == undefined ) 
			this.sources[source.row] = new Array() ;
		
		if(this.sources[source.row][source.col])
			this.clearReference(source);
		
		this.sources[source.row][source.col] = target;
				
		if ( this.targets[target.start.row]	== undefined ) {
			this.targets[target.start.row] = new Array() ;
		}		
		if ( this.targets[target.start.row][target.start.col]	== undefined ) {
			this.targets[target.start.row][target.start.col] = new Array() ;
		}		
		if ( this.targets[target.start.row][target.start.col][end.row]	== undefined ) {
			this.targets[target.start.row][target.start.col][end.row] = new Array() ;
		}		
		if ( this.targets[target.start.row][target.start.col][end.row][end.col]	== undefined ) {
			this.targets[target.start.row][target.start.col][end.row][end.col] = new Array() ;
		}	
		
		this.targets[target.start.row][target.start.col][end.row][end.col].push(source) ; 		
		
	}	
	
	self.getReferenced = function (source) {
		// a partir de una celda sacar todas la funciones que referencian a rengos que contienen a esa celda
		var references = new Array() ;
		var row = source.row ;
		var col = source.col ;
		
		//alert(this.sources.toSource());
		for ( i in this.sources ) {
			for ( j in this.sources[i] ) {
				if(j != 'remove'){
					var target = this.sources[i][j];
					if(target.start){
					
						//alert(row  + " " + col + " " + target.toSource());
						var end = (target.end)? target.end : target.start; 
						
						if (row <= end.row && row >= target.start.row && col >= end.col  && col <= target.start.col) {
							//alert("entra");
							references.push({row:i,col:j});
						}
					}
				}
			}
		}
		
		//@FIXME - You are killing me ! please change this structure !  ! ! ! ohhhhhhhhh... 
		/*for ( i in this.targets ) {
			for (j in this.targets[i]) {
				for(k in this.targets[i][j]) {
					for (l in this.targets[i][j][k]) {
						this.targets[i][j][k][l];
							if (row <= k && row >=i && col >= j && col <= l) {
								references.push(this.targets[i][j][k][l]);
							}
					}
				}
			}
		}*/
		return references ;
	}
	
	self.constructor() ;
	return self ;
}

window.References = new ReferenceHandler();