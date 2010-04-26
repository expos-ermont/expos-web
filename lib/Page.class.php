<?php
require_once('config.inc.php');
require_once($_CONF['libRoot'].'Control.class.php');

class Page {
	public $template = null;
	private $content = null;
	private $wwwRoot = null;
	private $toSubstitute = array();
	private $title = null; // can be considered as public, override by __get function
	public $keywords = null;
	public $description = null;
	
	public function __construct() {
		$this->_loadDefault();
	}
	
	public function __set($name , $value) {
		global $_CONF;
		if($name == 'title') $this->title = $value.' - '.$_CONF['title'];
	}
	
	public function add($name , $value) {
		$this->toSubstitute[$name] = $value;
	}
	
	public function getHTML() {
		$this->_loadTemplate();
		$this->_substituteVar('wwwRoot' , $this->wwwRoot);
		$this->_substituteVar('title' , $this->title);
		$this->_substituteVar('keywords' , $this->keywords);
		$this->_substituteVar('description' , $this->description);
		$this->_substituteVar('meta_expires' , gmdate('r' , time()+60*60*24*2));
		$this->toSubstitute['userMenu'] = $this->_generateUserMenu();
		foreach($this->toSubstitute as $key => $value) {$this->_substituteVar($key , $value);}
		$this->_substituteFiles();
		return $this->content;
	}
	
	public function send() {
		echo $this->getHTML();
	}
	
	private function _loadDefault() {
		global $_CONF;
		$this->template = 'default.xhtml';
		$this->title = $_CONF['title'];
		$this->keywords = $_CONF['keywords'];
		$this->description = $_CONF['description'];
		$this->wwwRoot = $_CONF['wwwRoot'];
	}
	
	private function _substituteVar($name , $value) {
		$this->content = preg_replace('/\[%var:'.$name.'%\]/' , mb_convert_encoding($value , 'UTF-8' , 'UTF-8,ASCII,Windows-1252') , $this->content);
	}

	private function _substituteFiles() {
		global $_CONF;
		$matches = array();
		preg_match_all('/\[%file:([^%]+)%\]/' , $this->content , $matches , PREG_SET_ORDER);
		foreach($matches as $match) {
			$this->content = preg_replace('/'.addcslashes($match[0],'/[]%').'/' , include($_CONF['root'].$match[1]) , $this->content);	
		}
	}
	
	private function _loadTemplate() {
		global $_CONF;
		$this->content = file_get_contents($_CONF['root'].'templates'.DIR_SEP.$this->template);
	}
	
	private function _generateUserMenu() {
		global $_CONF;
		
		$userItems = array(
			'pages/user_account.php' => 'Mon compte', 
			'pages/admin/actus_list.php' => 'Actus',
			'pages/admin/categories_list.php' => 'Catégories',
			'pages/admin/rights_list.php' => 'Droits',
			'pages/admin/pages_list.php' => 'Pages',
			'pages/admin/users_list.php' => 'Utilisateurs',
			'pages/admin/teams_list.php' => 'Equipes',
			'pages/user/infos_edit.php' => 'Mes infos'
		);
		
		if(!isset($_SESSION['user'])) {
			$return = '<a href="'.setGetVar('ref' , $_SERVER['REQUEST_URI'] , $_CONF['wwwRoot'].'pages/login.php').'" title="S\'authentifier"><img src="'.$_CONF['wwwRoot'].'picts/door_in.png" alt="" class="noBorder" /> Connexion</a>';;
		} else {
			$userListItems = '';
			foreach($userItems as $url => $name) {
				if(Control::accessToPage($url , false)){$userListItems .= '<li><a href="'.$_CONF['wwwRoot'].$url.'" title="'.$name.'">'.$name.'</a></li>';}
			}
			$return = '
				<ul>
					<li><a href="#" title="Mon compte">Bienvenue '.$_SESSION['user']->firstname.'</a>
						<ul>
							'.$userListItems.'
							<li><a href="'.setGetVar('ref' , $_SERVER['REQUEST_URI'] , $_CONF['wwwRoot'].'pages/logout.php').'" title="Se d�connecter">Deconnexion</a></li>
						</ul>
					</li>
				</ul>
				&nbsp;
			';
		}
		return $return;
	}
}
?>