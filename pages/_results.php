<?php
$results = array(
	'Nationale 1B' => array(
		'Rouen 2' => array(4,0),
		'Ermont 1' => array(3,1),
		'Dunkerque' => array(3,1),
		'Cherbourg' => array(2,2),
		'Valenciennes' => array(0,4),
		'Les Andelys' => array(0,4)
	),
	'Régionale 2' => array(
		'BC92' => array(2,1),
		'Montigny 3' => array(2,1),
		'Saint Leu' => array(2,1),
		'Ermont 2' => array(1,1),
		'Saints' => array(1,1),
		'Mantes' => array(0,3)
	),
	'Régionale 3' => array(
		'Pontoise' => array(2,0),
		'BC92 2' => array(2,1),
		'Ermont 3' => array(2,1),
		'Herblay' => array(0,1),
		'Saints 2' => array(0,1),
		'Vauréal 2' => array(0,2)
	),
	'Minimes Coupe IDF' => array(
		'Vauréal' => array(4,0),
		'Ermont' => array(3,1),
		'BC92' => array(2,2),
		'Herblay' => array(1,3),
		'Cergy' => array(0,4)
	)
);

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