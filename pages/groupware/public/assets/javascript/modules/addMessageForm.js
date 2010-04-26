App.modules.addMessageForm = {
  
  /**
   * Show and hide additional text editor
   *
   * @param object show_hide_link Expend / Collapse link
   * @param string editor_id ID of editor
   * @param string expend_lang Expend in selected language
   * @param string collapse_lang Collapse in selected language
   */
  toggleAdditionalText: function(show_hide_link, editor_id, expand_lang, collapse_lang) {
    editor = Ext.getDom(editor_id);
    if(editor.style.display == 'block') {
      editor.style.display = 'none';
      show_hide_link.innerHTML = expand_lang;
    } else {
      editor.style.display = 'block';
      show_hide_link.innerHTML = collapse_lang;
    } // if
  }, // toggleAdditionalText
  
  /**
   * Click on company checkbox in email notification box. If checkbox is checked
   * all company members need to be checked. If not all members are unchecked
   *
   * @param integer company_id Company ID
   */
  emailNotifyClickCompany: function(company_id, genid, div_id, type) {
  	if (type == 'notification')
  		var cos = Ext.getDom(genid + div_id).notify_companies;
  	else if (type == 'invitation')
  		var cos = Ext.getDom(genid + div_id).invite_companies;
  	else return;
    var company_details = cos['company_' + company_id]; // get company details from hash
    if(!company_details) return;
    
    var company_checkbox = Ext.getDom(genid + company_details.checkbox_id);
    
    for(var i = 0; i < company_details.users.length; i++) {
      Ext.getDom(genid + company_details.users[i].checkbox_id).checked = company_checkbox.checked;
    } // if
  }, // emailNotifyClickCompany
  
  /**
   * Click on company member. If all members are checked company should be checked too,
   * false othervise
   *
   * @param integer company_id
   * @param integer user_id
   */
  emailNotifyClickUser: function(company_id, user_id, genid, div_id, type) {
  	if (type == 'notification')
  		var cos = Ext.getDom(genid + div_id).notify_companies;
  	else if (type == 'invitation')
  		var cos = Ext.getDom(genid + div_id).invite_companies;
  	else return;
    var company_details = cos['company_' + company_id]; // get company details from hash
    if(!company_details) return;
    
    // If we have all users checked check company box, else uncheck it... Simple :)
    var all_users_checked = true;
    for(var i = 0; i < company_details.users.length; i++) {
      if(!Ext.getDom(genid + company_details.users[i].checkbox_id).checked) all_users_checked = false;
    } // if
    
    Ext.getDom(genid + company_details.checkbox_id).checked = all_users_checked;
  }, // emailNotifyClickUser
  
  /**
   * Returned all checked user ids as a CSV string.
   *
   * @param integer company_id Company ID
   */
  getCheckedUsers: function(genid) {
  	var ids = "";
  	var notcomp = Ext.getDom(genid + 'notify_companies');
  	if (notcomp) {
	  	var cos = notcomp.notify_companies;
	  	for (var k in cos) {
	  		var company_details = cos[k];
	  		if(!company_details) continue;
	  		for(var i=0; i < company_details.users.length; i++) {
	      		if (Ext.getDom(genid + company_details.users[i].checkbox_id).checked) {
	      			if (ids != "") ids += ",";
	      			ids += company_details.users[i].id;
	      		}
	    	}
	  	}
  	}
    return ids;
  } // emailNotifyClickCompany
  
};