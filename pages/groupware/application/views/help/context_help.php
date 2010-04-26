<?php $helpGenid = gen_id() ?> 

<div id='<?php echo $helpGenid ?>help' class='contextualHelp'>

<?php 
if($option_name){
	echo "<a class='internalLink' style='padding-left:6px;float:right;' href='javascript:void(0);' onclick=\"og.closeContextHelp('".$helpGenid."','".$option_name."');\" title='" . lang('remove context help') . "'>X</a>";
}else{
	echo "<a class='internalLink' style='padding-left:6px;float:right;' href='javascript:void(0);' onclick=\"og.closeContextHelp('".$helpGenid."');\" title='" . lang('remove context help') . "'>X</a>";
}

if(isset($helpDescription)){
	echo $helpDescription;
}

if(isset($helpTemplate)){
	$this->includeTemplate(get_template_path('context_help_' . $helpTemplate, 'help')); 
}?>

</div>