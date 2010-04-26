<?php
/**
 * Provide a set of function to handle news
 * @author Florent Captier <florent@captier.org>
 * @filesource
 */

class Actu {
	private $id_actu = null;
	public $title;
	public $content;
	public $author;
	public $creation_date;
	public $creation_time;
	public $category;
	public $picture;
	
	public function __construct($id_actu = null) {
		if(!is_null($id_actu)) {
			$this->id_actu = $id_actu;
			$this->_loadFromDB();
		}
	}
	
	public function getPicture() {
		
	}
	
	private function __loadFromDB() {
		if(is_null($this->id_actu)) {return false;}
		$query = '
			SELECT
				title,
				content,
				date(time) AS date,
				time(time) AS time,
				CONCAT(prenom , " " , nom) AS author,
				picture,
				c.default_picture,
				c.name AS cat_name
			FROM
				actus a
				LEFT JOIN categories c USING(id_category)
				JOIN users m ON a.id_author = m.id_user
			WHERE id_actu = "'.$db->real_escape_string($id_actu).'"' : '').'
			ORDER BY
				date DESC, 
				time DESC
			LIMIT '.$db->real_escape_string($first_actu).' , '.$nb_actus.'
		';
	}
}
?>