
function CommHandler(){	
	var self = this;
	
	self.constructor = function(){
		Ext.Ajax.on({
				'beforerequest': function(){
				Ext.getBody().mask('Loading...');
//				Ext.get('spinner').show();
			}});
	
		Ext.Ajax.on({
			'requestcomplete': function(){
			Ext.getBody().unmask();
		}});
	}
	self.recieveRequest = function(response,param,callback){
		try{
			var data = Ext.util.JSON.decode(response.responseText);
			if(data.success){
				if(callback)
					callback(data.data);
			}else{
				 Ext.MessageBox.show({
	                 title: data.type,
	                 msg: data.description,
	                 buttons: Ext.MessageBox.OK,
	                 icon: Ext.MessageBox.ERROR
	             });
			}
		}catch(e){
			 Ext.MessageBox.show({
                 title: 'Communication Error',
                 msg: 'Bad response format.'+e.toSource(),
                 buttons: Ext.MessageBox.OK,
                 icon: Ext.MessageBox.ERROR
             });
		}
	}
	
	
	self.requestFailed = function(response){
		alert("FALLA " + response.toSource());
	}
	
	self.sendRequest = function(parameters,callback){
		
//		Ext.Ajax.on('beforerequest', msgbox.showSpinner, msgbox);
		
		Ext.Ajax.request({
			method:"POST",
			waitMsg : 'Salvando datos...',
			url: '../php/index.php',
			success: function(response,param){
				self.recieveRequest(response,param,callback);
			},
			failure: self.requestFailed,
			params: parameters
		});
		
	}
	
	self.loadBook = function(bookId){
		self.sendRequest({c:'Spreadsheet',m:'loadBook',param1:1},application.bookLoaded);
	}
	
	self.constructor();
	return self;
}