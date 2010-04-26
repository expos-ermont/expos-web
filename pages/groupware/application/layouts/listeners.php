<?php
?>

<script>
//some event handlers
og.eventManager.addListener('tag changed', 
 	function (tag){ 
 		if (Ext.getCmp('tabs-panel').getActiveTab().id == 'tasks-panel') {
 			og.openLink('<?php echo get_url('task','new_list_tasks')?>',
 				{caller:'tasks-panel',
 				get:{tag:tag.name}}
 			);
 		}
 	}
);
og.eventManager.addListener('workspace changed', 
 	function (ws){ 

 	}
);

og.eventManager.addListener('company added', 
 	function (company) {
 		var elems = document.getElementsByName("contact[company_id]");
 		for (var i=0; i < elems.length; i++) {
 			if (elems[i].tagName == 'SELECT') {
	 			var opt = document.createElement('option');
	        	opt.value = company.id;
		        opt.innerHTML = company.name;
	 			elems[i].appendChild(opt);
 			}
 		}
 	}
);

og.eventManager.addListener('popup',
	function (args) {
		og.msg(args.title, args.message, 0, args.type, args.sound);
	}
);

og.eventManager.addListener('user config localization changed',
	function(val) {
		og.loadScripts([og.getUrl('access', 'get_javascript_translation')], {
			callback: function() {
				var spans = document.getElementsByName('og-lang');
				for (var i=0; i < spans.length; i++) {
					var key = spans[i].id.substring(8);
					spans[i].innerHTML = lang(key);
				}
			}
		});
	}
);

og.eventManager.addListener('download document',
	function(args) {
		if(args.reloadDocs){
			//og.openLink(og.getUrl('files', 'list_files'));
			og.panels.documents.reload();
		}	
		location.href = og.getUrl('files', 'download_file', {id: args.id, validate:0});
	}
);

og.eventManager.addListener('config checkout_notification_dialog changed',
	function(val) {
		if (val == "true") {
			og.showCheckoutNotification = true;
		} else {
			og.showCheckoutNotification = false;
		}
	}
);
</script>