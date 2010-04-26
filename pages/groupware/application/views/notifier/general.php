------------------------------------------------------------<?php echo "\r\n"
?><?php echo lang('dont reply wraning') ?><?php echo "\r\n"
?>------------------------------------------------------------<?php echo "\r\n"
?><?php echo "\r\n"
?><?php echo $description ?>.<?php echo "\r\n"
?><?php echo "\r\n"
?><?php 
	foreach ($properties as $name => $value) {
		echo lang($name) .": $value\r\n\r\n"; 
	}
?>--<?php echo "\r\n"
?><?php echo ROOT_URL ?><?php echo "\r\n\r\n\r\n"
?>