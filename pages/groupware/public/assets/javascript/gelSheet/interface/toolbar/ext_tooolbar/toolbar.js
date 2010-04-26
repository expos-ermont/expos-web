var imgpath = 'toolbar/img/' ;
var iconspath = imgpath+'icons/';

function createToolbars(){
	Ext.onReady(function(){
	
	    Ext.QuickTips.init();
	 
	    var tb = new Ext.Toolbar();
	    tb.render('north');

		//----------- SAVE ------------//

	    tb.add('-', {
	        icon: iconspath+'pencil-16x16.png', // icons can also be specified inline
	        cls: 'x-btn-icon',
	        tooltip: '<b>'+lang('Save')+'</b><br/>'+lang('Save the current book'),
	        handler: window.editBook
	    });
	    
		
		//--------- SAVE AS -----------//
		
			tb.add( {
	        icon: iconspath+'saveas-16x16.png', // icons can also be specified inline
	        cls: 'x-btn-icon',
	        tooltip: '<b>'+lang('Save as')+'..</b><br/>'+lang('Save the spreadsheet with a new filename'),
	        handler: saveBookConfirm 
	    },'-');
			
		//----------- EXPORT ------------//
	
	    var exportMenu = new Ext.menu.Menu({
	        id: 'exportMenu',
	        items: [
	            {
	                text: 'PDF',
				    icon: iconspath+'PDF-16x16.png',
	        		tooltip: '<b>'+lang('Export to')+' PDF</b><br/>'+lang('Export to')+' PDF. <br/>',
	        		handler: exportPDF 
	            },
	        	{
	                text: 'XLS',
				    icon: iconspath+'XLS-16x16.png',
	        		tooltip: '<b>'+lang('Export to')+' XLS</b><br/>'+lang('Export to')+' XLS. <br/>',
	        		handler: exportXLS 
	            },            
	        	{
	                text: 'XLSX',
				    icon: iconspath+'XLSX-16x16.png',
	        		tooltip: '<b>'+lang('Export to')+' XLSX</b><br/>'+lang('Export to')+' XLSX. <br/>',
	        		handler: exportXLSX 
	        	},
	           	{
	                text: 'ODS',
				    icon: iconspath+'ODS-16x16.png',
	        		tooltip: '<b>'+lang('Export to')+' ODS</b><br/>'+lang('Export to')+' ODS. <br/>',
	        		handler: exportODS 
	            }
			]
	    });
	
	   tb.add( {
	        icon: iconspath+'export.png', // icons can also be specified inline
	        text: lang('export'),
	        iconCls: 'bmenu', 
	        tooltip: '<b>'+lang('Export')+'</b><br/>'+lang('Export to many formats')+'. <br/>',
	        menu: exportMenu,  
	    },'-');
	

		//----------- FONT BOLD ------------//

	    tb.add({
	        icon: iconspath+'bold-16x16.png', // icons can also be specified inline
	        cls: 'x-btn-icon',
	        tooltip: '<b>'+lang('bold')+'</b>',
	        handler: bold
	    });

	
		//----------- FONT ITALIC ------------//
	
	    tb.add({
	        icon: iconspath+'italic-16x16.png', 
	        cls: 'x-btn-icon',
	        tooltip: '<i>'+lang('italic')+'</i>',
	        handler: italic
	    });

		//----------- FONT UNDERLINE ------------//

	     tb.add({
	        icon: iconspath+'underline-16x16.png', 
	        cls: 'x-btn-icon',
	        tooltip: '<u>'+lang('underline')+'</u>',
	        handler: underline
	    },'-');   
		
		tb.add({
	        icon: iconspath+'unformat-16x16.gif', 
	        cls: 'x-btn-icon',
	        tooltip: '<u>'+lang('Clear format')+'</u>',
	        handler: unformat
	    },'-');   
	
		//----------- FONT COLOR ------------//
	
		var fontColorMenu = new Ext.menu.ColorMenu({});

		fontColorMenu.on('select',function(cm, color){
		    				cmdSetFontColor('#'+color);	
		    			});
	     tb.add({
	        icon: iconspath+'font-color-16x16.png',
	        cls: 'x-btn-icon',
	        tooltip: lang('Font color'),
	        menu: fontColorMenu
	       
	    });

		//----------- BACKGROUND COLOR ------------//

		var bgColorMenu = new Ext.menu.ColorMenu({});
		
		bgColorMenu.on('select',function(cm, color){
					cmdSetBgColor('#'+color);	
				});
		
	     tb.add({
	        icon: iconspath+'bgcolor-16x16.png', // icons can also be specified inline
	        cls: 'x-btn-icon',
	        tooltip: lang('Background color'),
	        menu: bgColorMenu 
	       
	    },'-');  
	    
	    	
		//----------- FONT ------------//

	    var fontMenu = new Ext.menu.Menu({
	        id: 'fontMenu',
	        items: [
	            {
	                text: '<span style="font-family: Arial">Arial</span>',				 
	        		handler: function(){cmdSetFontStyle('0');}
	            },
	           	{
	                text: '<span style="font-family: Times New Roman">Times New Roman</span>',				 
	        		handler: function(){cmdSetFontStyle('1');}
	            },
	           	{
	                text: '<span style="font-family: Verdana">Verdana</span>',				 
	        		handler: function(){cmdSetFontStyle('2');}
	            },
	           	{
	                text: '<span style="font-family: Courier">Courier</span>',				 
	        		handler: function(){cmdSetFontStyle('3');}
	            },
	            {
	                text: '<span style="font-family: Lucida Sans Console">Lucida Sans Console</span>',				 
	        		handler: function(){cmdSetFontStyle('4');}
	            },
	           	{
	                text: '<span style="font-family: Tahoma">Tahoma</span>',				 
	        		handler: function(){cmdSetFontStyle('5');}
	            }
			]
	        
	    });	        
	    tb.add({
	        icon: iconspath+'font-16x16.png', // icons can also be specified inline
	        cls: 'x-btn-icon',
	        tooltip: lang('Select font'),
	        menu: fontMenu 
	    });  
	
	
	
		//----------- FONT SIZE ------------//
	
		var fontSize = new Ext.form.ComboBox({
			store: [
						['6', '6', '6'],
						['7', '7', '7'],
						['8', '8', '8'],
						['9', '9', '9'],
						['10', '10', '10'],
						['11', '11', '11'],
						['12', '12', '12'],
						['14', '14', '14'],
						['18', '18', '18'],
						['24', '24', '24'],
						['36', '36', '36']
					],
	        displayField:'function_name',
	        typeAhead: true,
	        editable:false,
	        mode: 'local',
	        triggerAction: 'all',
	        emptyText:'10',
	        width: 60 ,
	        selectOnFocus:true,
	        tooltip: lang('Font size')
	        
	        
	    });
	    
	    fontSize.on('select',function(combo,record,index){
	        				cmdSetFontSizeStyle(combo.getValue());
	        			});
	        			

		tb.addField(fontSize) ;
		tb.add('-');
		
	    var borderMenu = new Ext.menu.Menu({
	        id: 'borderMenu',
	       
	        items: [
	            {
	            	hideLabel: true ,
	            	disabled: true ,
	            	icon: iconspath+'border_none.png' ,
	            	text: '(Unimplemented)',
	        		handler: function (){setBorderNone() ;} 
	            },
	           	{
	           		disabled: true ,
	           		icon: iconspath+'border_left.png' ,
	           		text: lang('Border left'),
	        		handler: function (){setBorderLeft() ;} 
	            }
				,
	           	{
	           		disabled: true ,
	           		icon: iconspath+'border_bottom.png' ,
	           		text: lang('Border bottom'),
	        		handler: function (){setBorderBottom() ;} 
	            }
				,
	           	{
	           		disabled: true ,
	           		icon: iconspath+'border_right.png' ,
	           		text: lang('Border right'),
	        		handler: function(){setBorderRight();}
	            },	       
				
	           	{
	           		disabled: true ,
	           		icon: iconspath+'border_top.png' ,
	           		text: lang('Border top'),
	        		handler: function(){setBorderTop();}
	            }	                 	            
			]
	        
	    });	        
	    tb.add({
	        icon: iconspath+'border_bottom.png', // icons can also be specified inline
	        cls: 'x-btn-icon',
	        tooltip: lang('Border'),
	        menu: borderMenu 
	    });  
		
		tb.add("-");
		
		tb.add({
			disabled: true ,
	        icon: iconspath+'align_left-16x16.gif', 
	        cls: 'x-btn-icon',
	        tooltip: '<i>'+lang('Align left')+'</i>',
	        handler: function(){}
	    });

		tb.add({
			disabled: true ,
	        icon: iconspath+'align_center-16x16.gif', 
	        cls: 'x-btn-icon',
	        tooltip: '<i>'+lang('Align center')+'</i>',
	        handler: function(){}
	    });		

		tb.add({
			disabled: true ,
	        icon: iconspath+'align_right-16x16.gif', 
	        cls: 'x-btn-icon',
	        tooltip: '<i>'+lang('Align right')+'</i>',
	        handler: function(){}
	    });
	
		tb.add("-");

		tb.add({
			disabled: true ,
	        icon: iconspath+'valign_button-16x16.gif', 
	        cls: 'x-btn-icon',
	        tooltip: '<i>'+lang('Vertical align bottom')+'</i>',
	        handler: function(){}
	    });

		tb.add({
			disabled: true ,
	        icon: iconspath+'valign_center-16x16.gif', 
	        cls: 'x-btn-icon',
	        tooltip: '<i>'+lang('Vertical align center')+'</i>',
	        handler: function(){}
	    });		

		tb.add({
			disabled: true ,
	        icon: iconspath+'valign_top-16x16.gif', 
	        cls: 'x-btn-icon',
	        tooltip: '<i>'+lang('Vertical align top')+'</i>',
	        handler: function(){}
	    });

		tb.add("-") ;		
		/***************** SECOND TOOLBAR *****************/ 
	
	    var tb2 = new Ext.Toolbar();
	    tb2.render('north');
		
		tb2.add('-');
		
		tb2.add('<span style="font-weight: bold; font-style: italic; font-family: Verdana ; color: #0005AA">F(x)=</span>');
		
				
	    var functions = new Ext.data.SimpleStore({
	        fields: ['function_id', 'function_name'],
	        data : Ext.data.functions // from functions.js
	    });

		
		var text = new Ext.form.TextField({
			fieldLabel: 'f(x)',
			width:547,
			id: 'FormulaBar' ,
			enableKeyEvents: true 
			 
		});
		
		/*text.on('keydown', function(object,e) {
				e.stopPropagation();
				alert(e.browserEvent.toSource());
		});*/
		
		text.on('keyup', function(object,e) {
				application.grid.editActiveCell(text.getValue()); //TODO: Desacoplar que acceda a grid
			} 
		);
		
		tb2.addField(text) ;
		tb2.add('-');
		
	    // They can also be referenced by id in or components
	
		
	
	    
	});
}