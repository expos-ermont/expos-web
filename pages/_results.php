<?php
$results = array(
	'Nationale 1B' => array(
		'Dunkerque' => array(16,4),
		'Ermont 1' => array(13,7),
		'Cherbourg' => array(11,9),
		'Rouen 2' => array(9,9),
		'Les Andelys' => array(5,13),
		'Valenciennes' => array(2,14)
),
	'Régionale 2' => array(
		'Saint Leu' => array(7,3),
		'Saints' => array(7,3),
		'Montigny 3' => array(6,4),
		'Ermont 2' => array(5,4),
		'BC92' => array(4,6),
		'Mantes' => array(0,9)
),
	'Régionale 3' => array(
		'Pontoise' => array(9,1),
		'Ermont 3' => array(7,3),
		'Herblay' => array(5,5),
		'BC92 2' => array(4,6),
		'Saints 2' => array(3,7),
		'Vauréal 2' => array(2,8)
),
	'Softball Mixte IDF' => array(
		'Chartres' => array(8,0),
		'Thiais' => array(8,0),
		'Le Thillay' => array(2,0),
		'BAT' => array(2,4),
		'BK' => array(2,6),
		'Limeil' => array(2,6),
		'Ermont' => array(0,8)
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