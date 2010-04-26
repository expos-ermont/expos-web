 <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" >
<head>
    <style type="text/css" media="screen">
	    @import url("./style.css");
	    @import url("./toolbar/toolbar.css");
	    
	    @import url("../../../themes/default/extjs/css/ext-all.css");

    </style>

    <title>OpenGoo Gel SpreadSheet</title>
    
	<script type="text/javascript" src="../php/?c=Language&m=getLanguages"></script>
	
	
    <!--******************* External Libraries *********************-->
    <script type="text/javascript" src="../../extjs/adapter/ext/ext-base.js"></script>
    <script type="text/javascript" src="../../extjs/ext-all.js"></script>
    
    <!--******************* Server Side Scripts *********************-->
	<script type="text/javascript" src="../interface/fonts.js"></script>
	<script type="text/javascript" src="../interface/toolbar/ext_tooolbar/combos/functions.js"></script>
	 
    <!--******************* Auxiliary Functions *********************-->
	<script type="text/javascript" src="./debug/debugger.js"></script>
	
	<!--******************* Handlers/Managers *********************-->
	<script type="text/javascript" src="../handlers/command_handler.js"></script>
	<script type="text/javascript" src="../handlers/section_handler.js"></script>
	<script type="text/javascript" src="../handlers/event_handler.js"></script>
	<script type="text/javascript" src="../handlers/key_handler.js"></script>
	<script type="text/javascript" src="../handlers/resize_handler.js"></script>
	<script type="text/javascript" src="../handlers/style_handler.js"></script>
	<script type="text/javascript" src="../handlers/names_handler.js"></script>
	<script type="text/javascript" src="../handlers/selection_handler.js"></script>
   	
   	<!--******************* Comunication Classes *********************-->
    <script type="text/javascript" src="../comm/ajax.js"></script>
    <script type="text/javascript" src="../comm/comm_manager.js"></script>
    
   	<!--******************* Interface Classes *********************-->
   	
   	<script type="text/javascript" src="toolbar/ext_tooolbar/toolbar_callback.js"></script>
   	<script type="text/javascript" src="toolbar/ext_tooolbar/toolbar.js"></script>
   	
   	
   	
   	
   	<script type="text/javascript" src="./application/openFile_dialogBox.js"></script>
   	<script type="text/javascript" src="./application/colorPalette.js"></script>
   	
    <script type="text/javascript" src="./application/application.js"></script>
    <script type="text/javascript" src="./application/application_events.js"></script>

    <script type="text/javascript" src="./application/application_dialogs.js"></script>

	
	<!--
    <script type="text/javascript" src="./application/formulabar.js"></script>
    -->
    <script type="text/javascript" src="./grid/grid_navigation.js"></script>
    <script type="text/javascript" src="./grid/grid_operations.js"></script>
    <script type="text/javascript" src="./grid/grid.js"></script>
    <script type="text/javascript" src="./grid/grid_gui.js"></script>
    <script type="text/javascript" src="./grid/grid_scrollbar.js"></script>
    <script type="text/javascript" src="./grid/grid_components.js"></script>
    <script type="text/javascript" src="./grid/grid_events.js"></script>
    <script type="text/javascript" src="./grid/vcell.js"></script>
    <script type="text/javascript" src="./grid/vrow.js"></script>
    <script type="text/javascript" src="./grid/vcolumn.js"></script>
    <script type="text/javascript" src="style_wrapper.js"></script>
    <!--******************* Medium Layer *************************-->
    <script type="text/javascript" src="../controllers/sheet_controller.js"></script>
    <script type="text/javascript" src="../controllers/command_controller.js"></script>
    <script type="text/javascript" src="../controllers/font_style_controller.js"></script>    
    
    <script type="text/javascript" src="../medium_layer/data_model.js"></script>
    <script type="text/javascript" src="../medium_layer/data_model_style_operations.js"></script>
    <script type="text/javascript" src="../medium_layer/selection_handler.js"></script>
    
	<!--******************* Logic Classes *************************-->
	
	<script type="text/javascript" src="../logic/book.js"></script>
    <script type="text/javascript" src="../logic/sheet.js"></script>
    <script type="text/javascript" src="../logic/sheet_style_operations.js"></script>
    <script type="text/javascript" src="../logic/calculator.js"></script>
    <script type="text/javascript" src="../logic/references.js"></script>
    <script type="text/javascript" src="../logic/formula_parser.js"></script>
    <script type="text/javascript" src="../logic/cell.js"></script>
    <script type="text/javascript" src="../logic/row.js"></script>
    <script type="text/javascript" src="../logic/column.js"></script>
    <script type="text/javascript" >
    	function borrar(){
    		window.grid.adjustViewPort();
    		//alert(window.activeSheet.getHeight());
    		//alert("X " + window.grid.viewport.row + ", Y " + window.grid.viewport.col);
    		alert(fscFontsStyleToJSON());
    	}
    	
        function load(){			
        	var application = new Application(document.body);
			<?php if (isset($_GET['book'])) {  ?>
			        	loadData(<?php echo $_GET['book'] ?>);
			<?php } ?>
			<?php if (isset($_GET['id'])) { ?>
						window.ogID = <?php echo $_GET['id'] ?>;
			<?php } ?>
//   			application.model.refresh();
			
			var logo_div = document.getElementById('logo');
			if ( logo_div ) {
				logo_div.style.display = "block"; 
			}
        	
        	/*
        	var openfiledialog = new OpenFileDialog(100,100,300,300);
        	var file = {id:1,name:'Book1',creator:'perico',date:'01/01/2008'};
      
        	
        	openfiledialog.addFile(file);
        	var file2 = "";
        	file2.id = 2;
        	file2.name = "Otro";
        	file2.creator = "Perico";
        	file2.date = "01/01/2008";
        	openfiledialog.addFile(file2);
        	openfiledialog.addFile(file);
        	openfiledialog.addFile(file2);
        	
        	alert("VA");
        
        	document.body.appendChild(openfiledialog);
        	
        	
        	errorConsole = new Debugger();
        	EventManager = new EventHandler();
        	SelectionManager = new SelectionHandler();
        	
        	
            var grid = new Grid(700,500);
            document.body.appendChild(grid);
            */
        }
    </script>
</head>
<body id="body" onload="load();" >
  <div id="logo" style="z-index: 1001; display: none" ></div>
  <div id="west"></div>
  <div id="north">
  </div>
  <div id="center"></div>
  <div id="east" style="width:200px;height:200px;overflow:hidden;">
  </div>
  <div id="south"></div>
</body>
</html>
