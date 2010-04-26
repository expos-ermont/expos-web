<?php

class DataLoaderParams{
	
	private $firstNames;
	private $lastNames;
	private $wordList;
	private $params;
	private $nouns;
	private $adverbs;
	private $verbs;
	private $adjectives;
	
	function DataLoaderParams(){
		$this->loadNames();
		$this->params = array(
			"Companies" => 0,
			"Users" => 0,
			"Messages" => 0,
			"Emails" => 0, //No funciona
			"Contacts" => 0,
			"Documents" => 0, //No funciona
			"Webpages" => 0,
			"Tasks" => 0,
			"Milestones" => 0,
			"Link objects" => 100,
		);
	}
	
	function getLoremIpsum($max = 1000){
		$result = '';
		$count = rand(5, $max);
		$c = 0;
		$p = 0;
		$frlen = rand(5, 30);
		$plen = rand(3, 5);
		for	($i = 1; $i < $count; $i++){
			if ($c == 0)
				$result .= ucfirst($this->getRandomWord());
			else
				$result .= ' ' . $this->getRandomWord();
			$c++;
			
			if ($i == $count - 1 || $c % $frlen == 0){
				$result .= '. ';
				$c = 0;
				$frlen = rand(5, 30);
				$p ++;
				if ($p % $plen == 0){
					$p = 0;
					$result .= "\r\n\r\n";
				}
			}
		}
		return $result;
	}
	
	function getShortLoremIpsum(){
		return $this->getLoremIpsum(40);
	}
	
	function getParam($text){
		return $this->params[$text];
	}

	function getParams(){
		return $this->params;
	}
	
	function getPersonFirstName(){
		return $this->firstNames[rand(0,count($this->firstNames))];
	}
	
	function getPersonLastName(){
		return $this->lastNames[rand(0,count($this->lastNames))];
	}

	function getRandomWord(){
		return $this->wordList[rand(0,count($this->wordList))];
	}
	
	function getRandomNoun(){
		return $this->nouns[rand(0,count($this->nouns))];
	}
	
	function getRandomVerb(){
		return $this->verbs[rand(0,count($this->verbs))];
	}
	
	function getRandomAdjective(){
		return $this->adjectives[rand(0,count($this->adjectives))];
	}
	
	function getRandomAdverb(){
		return $this->adverbs[rand(0,count($this->adverbs))];
	}
	
	function getCompanyName(){
		return 'Compania' . rand(0,1000000);	
	}
	
	function getRandomTaskName(){
		return ucfirst($this->getRandomVerb()) ." the "
			. $this->getRandomAdjective() . " " 
			. $this->getRandomNoun(). " " 
			. $this->getRandomAdverb();
	}
	
	function loadNames(){
		$first = fopen(ROOT . "/environment/classes/dataloader/files/first.txt",'r');
		$last = fopen(ROOT . "/environment/classes/dataloader/files/last.txt",'r');
		$words = fopen(ROOT . "/environment/classes/dataloader/files/words.txt",'r');
		$nounsfile = fopen(ROOT . "/environment/classes/dataloader/files/nouns.txt",'r');
		$adverbsfile = fopen(ROOT . "/environment/classes/dataloader/files/adverbs.txt",'r');
		$verbsfile = fopen(ROOT . "/environment/classes/dataloader/files/verbs.txt",'r');
		$adjectivesfile = fopen(ROOT . "/environment/classes/dataloader/files/adjectives.txt",'r');
		
		$this->firstNames = array();
		$this->lastNames = array();
		$this->wordList = array();
		$this->nouns = array();
		$this->adjectives = array();
		$this->adverbs = array();
		$this->verbs = array();
		
		while($data = fgets($first)){
			$this->firstNames[] = ucfirst(strtolower(substr($data,0,strpos($data,' '))));
		}
	
		while($data = fgets($last)){
			$this->lastNames[] = ucfirst(strtolower(substr($data,0,strpos($data,' '))));
		}
		
		while($data = fgets($words)){
			$this->wordList[] = trim($data);
		}
		
		while($data = fgets($adjectivesfile)){
			$this->adjectives[] = trim($data);
		}
		
		while($data = fgets($verbsfile)){
			$this->verbs[] = trim($data);
		}
		
		while($data = fgets($adverbsfile)){
			$this->adverbs[] = trim($data);
		}
		
		while($data = fgets($nounsfile)){
			$this->nouns[] = trim($data);
		}
	}
}
?>