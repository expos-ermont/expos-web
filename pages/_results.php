<?php
$yaml_file = $_CONF['root'].'/pages/_results.yaml';

$results = 	Spyc::YAMLLoad($yaml_file);

$return = '';

foreach($results as $championship => $teams) {
	$return .= '
		<table class="results">
			<caption>'.$championship.'</caption>
			<tr>
				<th>Equipe</th>
				<th>G</th>
				<th>W</th>
				<th>L</th>
				<th>avg.</th>
			</tr>
	';

	foreach($teams as $team => $stats) {
		$stats['W'] = $stats[0];
		$stats['L'] = $stats[1];
		$stats['G'] = $stats['W']+$stats['L'];
		$avg = ($stats['G'] == 0) ? 0 : round($stats['W']*1000/$stats['G']);
		$return .= '
			<tr>
				<td>'.$team.'</td>
				<td>'.$stats['G'].'</td>
				<td>'.$stats['W'].'</td>
				<td>'.$stats['L'].'</td>
				<td>.'.$avg.'</td>
			</tr>
		';
	}
	$return .= '
			<!--<tr class="last">
				<td colspan="5">&nbsp;</td>
			</tr>-->
		</table><br />';
}

return $return;
?>