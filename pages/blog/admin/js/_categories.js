
$(function(){$('form#delete-category').submit(function(){var c_id=$('#del_cat').val();var c_name=$('#del_cat option[value='+c_id+']').text();return window.confirm(dotclear.msg.confirm_delete_category.replace('%s',c_name));});});