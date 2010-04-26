var DIV_ZERO = '#DIV/0!' ;
var NOT_NUM = '#VALUE!' ;

function isEmpty(variable) {
	if ( variable == undefined ) return true ;
	return  ( variable.length == 0 );
}

function isNumeric(variable) {
	return ( !isEmpty(variable) && !isNaN(variable) ) ;
}

function Calculator(){
	var self = this;
	self.calc = function(func,values){
		callback = func.toLowerCase();
		return eval('this.'+callback+'(values)');
	}


	self.abs = function(value) {
		return Math.abs(value[0]);
	}

	self.average = function(values) {
		var value = 0 ;
		var total = 0 ;
		for (var item in values) {
			if ( item != 'remove' && isNumeric( values[item] ) ) {
				total++ ;
				value += parseFloat(values[item])   ; 
			}
		}
		if (total) value = value / total ;
		else value = DIV_ZERO ;
		return value ;
	} 

	self.count = function(values) {
		var value = 0 ;
		for (var item in values) {
			if ( item != 'remove' && isNumeric( values[item] ) ) { 
				value += 1; 
			}
		}
		return value;
	}

	self.counta = function(values) {
		var value = 0 ;
		for (var item in values) {
			if ( item != 'remove'  &&  !isEmpty( values[item] ) ){ 
				value += 1; 
			}
		}
		return value;
	}


	self.cos = function(value) {
		return Math.cos(value[0]);
	}



	self.max = function(values) {
		var value = values[0] ;
		for (var item in values) {
			values[item] = parseFloat(values[item]);
			if ( item != 'remove' && isNumeric(values[item]) ) { 
				if ( values[item] > value )
					value = values[item] ; 
			}
		}
		return value;
	}

	self.maxa = function(values) {
		var value = values[0] ;
		for (var item in values) {
			if ( item != 'remove' && isNumeric(values[item]) ) { 
				values[item] = parseFloat(values[item]);
				if ( values[item] > value )
					value = values[item] ; 
			}
		}
		return value;
	}


	self.min = function(values) {
		var value = values[0] ;
		for (var item in values) {
			if ( item != 'remove' && isNumeric(values[item]) ) { 
				values[item] = parseFloat(values[item]);
				if ( values[item] < value )
					value = values[item] ; 
			}
		}
		return value;
	}

	self.mina = function(values) {
		var value = values[0] ;
		for (var item in values) {
			if ( item != 'remove'  &&  isNumeric(values[item]) ) { 
				values[item] = parseFloat(values[item]);
				if ( values[item] < value )
					value = values[item] ; 
			}
		}
		return value;
	}

	self.product = function(values) {
		var value = 1 ;
		var valid = false ;
		for (var item in values) {
			if ( item != 'remove' && isNumeric( values[item] )  ){
				valid = true ;	 
				value *= parseFloat(values[item]) ; 
			}
		}
		return (valid) ? value : 0 ;
	}

	
	self.sum = function(values){		
		var value = 0 ;
		for (var item in values) {
			if ( item != 'remove' && isNumeric( values[item] )  ) 
				value += parseFloat(values[item]) ; 
		}
		return value;
	}
	
	self.sin = function(value) {
			return Math.sin(value[0]);	
	}
	
	self.sqrt = function(value) {
		return Math.sqrt(value[0]);
	}
}

window.calculator = new Calculator();
