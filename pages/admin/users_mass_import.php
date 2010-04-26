<?php
/**
 * Import a list of users from CSV file
 *
 * @author Florent Captier <florent@captier.org>
 * @filesource
 */

require_once('../../lib/config.inc.php');
require_once($_CONF['libRoot'].'Control.class.php');
require_once($_CONF['libRoot'].'Page.class.php');
require_once($_CONF['libRoot'].'Notification.class.php');

Control::accessToPage(__FILE__);

/**
 * Convert a file to an 2 dimensions array by converting all the lines in UTF-8
 * @param array $file File provided by $_FILES var
 * @param string $delimiter Fields delimiter
 * @return array
 */
function file2array(array $file , $delimiter) {
	// Check the correct uploap
	if($file['error'] !== UPLOAD_ERR_OK) {throw new Exception('Error during file upload (Code : '.$file['error'].')');}
	// Check if the file is an uploaded file
	if(!is_uploaded_file($file['tmp_name'])) {throw new Exception('The given file was not an uploaded file.');}
	
	$return = array();
	
	$stream = fopen($file['tmp_name'] , 'r');
	while($line = fgets($stream)) {
		$line = rtrim($line);
		$line_encoding = mb_detect_encoding($line , 'UTF-8,ASCII,ISO-8859-15,ISO-8859-1,Windows-1252' , true);
		$line = mb_convert_encoding($line , 'UTF-8' , $line_encoding);
		$line = explode($delimiter , $line);
		$return[] = $line;
	}
	fclose($stream);
	
	return $return;
}

if(isset($_SESSION['import_columns']) && isset($_SESSION['import_lines']) && isset($_GET['import'])) {
	//
	// STEP 3 : Importation in DB
	//
	$columns = $_SESSION['import_columns'];
	$lines = $_SESSION['import_lines'];
	unset($_SESSION['import_columns']);
	unset($_SESSION['import_lines']);
	
	// New lines
	if(isset($_POST['process_adds'])) {
		foreach($lines['toAdd'] as $line) {
			$t_combine = array_combine($columns , $line);
			if(!in_array('login' , array_keys($t_combine))) {
				$t_combine['login'] = strtolower($t_combine['prenom'].'.'.$t_combine['nom']);
			}
			if(User::add(array_keys($t_combine) , array_values($t_combine))) {
				try {
					Notification::accountCreation(User::checkData(array('nom' , 'prenom') , array($t_combine['nom'] , $t_combine['prenom'])));
				} catch(Exception $e) {
					$error .= 'Erreur d\'envoi de la notification pour '.$t_combine['prenom'].' '.$t_combine['nom'].'<br />';
				}
			}
		}
	}
	
	// Updates
	if(isset($_POST['process_updates'])) {
		foreach($lines['toUpdate'] as $line) {
			$t_combine = array_combine($columns , $line);
			User::update(
				User::checkData(array('nom' , 'prenom') , array($t_combine['nom'] , $t_combine['prenom'])),
				array_keys($t_combine),
				array_values($t_combine)
			);
		}
	}
	
	if(isset($error)) {
		$content = '<div class="error">'.$error.'</div>';
	} else {
		$content = '<div class="success">Importation réussie.</div>';
	}
} elseif(isset($_FILES['file'])) {
	//
	// STEP 2 : Validation of import
	//
	$t_to_import = file2array($_FILES['file'] , $_POST['delimiter']);
	$columns = current($t_to_import);
	$lines = array(
		'toUpdate' => array(),
		'toAdd' => array()
	);
	
	// Check the validity of columns
	foreach($columns as $column) {if(!in_array($column , User::getValidVariables() , true)) {$error .= $column.' n\'est pas un champ valide.<br />';}}
	if(!in_array('nom' , $columns)) {$error .= 'La colonne "nom" est manquante.<br />';}
	if(!in_array('prenom' , $columns)) {$error .= 'La colonne "prenom" est manquante.<br />';}
	
	if(!isset($error)) {
		// Format each line and check the validity
		while($values = next($t_to_import)) {
			$t_line = array_combine($columns , $values);
			if(empty($t_line['nom']) || empty($t_line['prenom'])) {continue;}
			foreach($t_line as $column => &$value) {$value = User::formatValue($column , $value);}
			if(User::checkData(array('nom' , 'prenom') , array($t_line['nom'] , $t_line['prenom'])) === false) {
				$lines['toAdd'][] = array_values($t_line);
			} else {
				$lines['toUpdate'][] = array_values($t_line);
			}
		}
		
		// Build the table
		$table_changes = '<table>';
		foreach($lines as $type => $values) {
			foreach($values as $line) {
				$t_line = array_combine($columns , $line);
				$table_changes .= '<tr>';
				$icon = 'add.png';
				if($type == 'toUpdate') {
					$icon = 'information.png';
					$old_data = '';
					$user = new User(User::checkData(array('nom' , 'prenom') , array($t_line['nom'] , $t_line['prenom'])));
					foreach($columns as $column) {$old_data .= $user->$column;}
					unset($user);
				}
				$table_changes .= '
					<td><img src="'.$_CONF['wwwRoot'].'picts/'.$icon.'" /></td>
					<td>
						'.(($type == 'toUpdate') ? '<s>'.$old_data.'</s><br />' : '').'
						'.implode(',' , $line).'
					</td>
				';
				$table_changes .= '</tr>';
			}
		}
		$table_changes .= '</table>';
		
		// Store the needed arrays into session
		$_SESSION['import_columns'] = $columns;
		$_SESSION['import_lines'] = $lines;
	}
	
	if(isset($error)) {
		$content = '
			<div class="error">'.$error.'</div>
			<a class="button" href="">Retour</a>
		';
	} else {
		$content = '
			'.$table_changes.'
			<form method="post" action="?import=1">
				<input type="checkbox" name="process_updates" value="1" /> Mettre à jour les utilisateurs<br /> 
				<input type="checkbox" name="process_adds" value="1" /> Ajouter les utilisateurs<br /> 
				<button type="submit">Valider</button>
			</form>
		';
	}

	$content = '
		<h2>Etape 2 : Validation de l\'importation</h2>
		'.$content.'
	';	
} else {
	//
	// STEP 1 : Upload of file to import
	//
	$ul_valid_columns = '<ul>';
	foreach(User::getValidVariables() as $column) {$ul_valid_columns .= '<li>'.$column.'</li>';}
	$ul_valid_columns .= '</ul>';
	
	$content = '
		<h2>Etape 1 : Importation du fichier</h2>
		<form method="post" enctype="multipart/form-data">
			Le fichier doit être un CSV (Comma-Separated Values) et la première ligne doit contenir les colonnes à importer.<br />
			Les colonnes nom et prénom sont OBLIGATOIRES.<br />
			Les colonnes valides sont : '.$ul_valid_columns.'<br />
			<div class="info">Attention si le fichier est généré sous Mac, l\'ouvrir dans "textedit" et le ré-enregistrer en format UTF-8 !</div>
			<label for="file">Fichier à importer : </label><input type="file" name="file" id="file" /><br />
			<label for="delimiter">Séparateur de champs : </label><input type="text" maxlength="1" size="1" name="delimiter" id="delimiter" /><br />
			<button type="submit">Suivant</button>
		</form>
	';
}

$page = new Page();
$page->add('content' , $content);
$page->send();
?>