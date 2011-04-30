<?php
/**
 * Provide a set of function to handle news
 * @author Florent Captier <florent@captier.org>
 * @filesource
 */

require_once('Db.class.php');

class Actu {
	private $id = null;
	private $title = '';
	private $content = '';
	private $author = '';
	private $creation_date = '';
	private $creation_time = '';
	private $creation_unix_time = '';
	private $category = '';
	private $picture_url = '';
	private $promote = '';
	
	/**
	 * Get an array of Actu objects giving a certain amount
	 * 
	 * @param int $nb_actus Number of objects to retrieve
	 * @param int $num_page Index of the considered page
	 * @return array Array of Actu objects
	 */
	public static function getLimitedSet($nb_actus = 1, $num_page = 1) {
		$return = array();
		$low_limit = ($num_page - 1) * $nb_actus;
		$db = new Db();
		
		$query = '
			SELECT
				id_actu
			FROM
				actus
			ORDER BY
				promote DESC, 
				time DESC
			LIMIT
				'.$low_limit.','.$db->real_escape_string($nb_actus).'
		';
		$result = $db->query($query);
		while($data = $result->fetch_row()) {
			$return[] = new Actu($data[0]);
		}
		
		$db->close();
		
		return $return;
	}
	
	/**
	 * Get the total number of news stored in database
	 * @return int Total number of news
	 */
	public static function getTotalNb() {
		$db = new Db();
		$query = 'SELECT COUNT(*) FROM actus';
		$result = $db->query($query);
		$data = $result->fetch_row();
		$db->close();
		return $data[0];
	}
	
	/**
	 * Construct a new Actu object
	 * 
	 * @param int $id_actu OPTIONAL - If specified, loads a news from the database. If not, create a new one.
	 */
	public function __construct($id_actu = null) {
		if(!is_null($id_actu)) {
			$this->id = $id_actu;
			$this->_loadFromDB();
		}
	}
	
	/**
	 * Indicates if the news is promoted or not
	 * 
	 * @return bool
	 */
	public function isPromoted() {
		if($this->promote == 1) {
			return true;
		}
		return false;
	}
	
	/**
	 * Get the news ID
	 * 
	 * @return int
	 */
	public function getId() {
		return (int) $this->id;
	}
	
	/**
	 * Get the news title
	 * 
	 * @return string
	 */
	public function getTitle() {
		return $this->title;
	}
	
	/**
	 * Get the news creation date
	 * 
	 * @return string
	 */
	public function getDate() {
		return $this->creation_date;
	}
	
	/**
	 * Get the news creation time
	 * 
	 * @return string
	 */
	public function getTime() {
		return $this->creation_time;
	}
	
	/**
	 * Get the news creation time in Unixtime format
	 * 
	 * @return string
	 */
	public function getUnixTime() {
		return $this->creation_unix_time;
	}
	
	/**
	 * Get the news author
	 * 
	 * @return string
	 */
	public function getAuthor() {
		return $this->author;
	}
	
	/**
	 * Get the news category
	 * 
	 * @return string
	 */
	public function getCategory() {
		return $this->category;
	}
	
	/**
	 * Get the news content
	 * 
	 * @return string
	 */
	public function getContent() {
		return $this->content;
	}
	
	public function getPictureUrl() {
		return $this->picture_url;
	}
	
	private function _loadFromDB() {
		global $_CONF;
		
		if(is_null($this->id)) {return false;}
		
		$db = new Db();
		
		$query = '
			SELECT
				title,
				content,
				date(time) AS date,
				time(time) AS time,
				UNIX_TIMESTAMP(time) AS unix_time,
				CONCAT(prenom , " " , nom) AS author,
				picture,
				promote,
				c.default_picture,
				c.name AS cat_name
			FROM
				actus a
				LEFT JOIN categories c USING(id_category)
				JOIN users m ON a.id_author = m.id_user
			WHERE id_actu = "'.$db->real_escape_string($this->id).'"
		';
		$result = $db->query($query);
		$data = $result->fetch_assoc();
		$this->title = $data['title'];
		$this->content = $data['content'];
		$this->creation_date = $data['date'];
		$this->creation_time = $data['time'];
		$this->creation_unix_time = $data['unix_time'];
		$this->author = $data['author'];
		$this->category = $data['cat_name'];
		$this->promote = $data['promote'];
		
		// Define the picture to display
		if(!empty($data['picture'])) {
			$this->picture_url = $_CONF['medias']['wwwActuPictsRoot'].$data['picture'];
		} elseif(strlen($this->content)>300 && !empty($data['default_picture'])) {
			$this->picture_url = $_CONF['medias']['wwwActuPictsRoot'].$data['default_picture'];
		}
		
		$db->close();
		
		return true;
	}
}
?>