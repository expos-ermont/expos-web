<?php
/**
 * Provide an interface to push a new result table
 * 
 * @author Florent Captier <florent@captier.org>
 * @filesource
 */

require_once('../../lib/config.inc.php');
require_once('Page.class.php');
require_once('Spyc/spyc.php');

Control::accessToPage(__FILE__);

$yaml_file = $_CONF['root'].'/pages/_results.yaml';

if(isset($_POST['results_table'])) {
	file_put_contents($yaml_file, $_POST['results_table']);
}

$yaml_content = '';
if(file_exists($yaml_file)) {
	$yaml_content = file_get_contents($yaml_file);
}

$content = '
	<h1>Mettre à jour les résultats</h1>
	<form method="post">
		<p>Afin de publier des résultats, merci de respecter le format suivant:</p>
		<pre>
Championnat 1: 
  Equipe 1: [4, 0]
  Equipe 2: [3, 1]
Championnat 2:
  Equipe 3: [4, 0]
  Equipe 4: [2, 3]
		</pre>
		<textarea cols="100" rows="20" name="results_table">'.$yaml_content.'</textarea><br />
		<button type="submit" class="button">Publier</button>
	</form>
';

$page = new Page();
$page->add('content', $content);
$page->send();
?>