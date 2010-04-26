<?php
class InitialDataLoader{
	
	private $soCount = -1;
	
	public function loadData(){
		$params = new DataLoaderParams();
		
		$timeBegin = microtime(true);
		try {
			//$this->clearData();
			$this->loadCompanies($params);
			$this->loadContacts($params);
			$this->loadMessages($params);
			$this->loadUsers($params);
			$this->loadWebpages($params);
			$this->loadTasks($params);
			$this->loadMilestones($params);
			$this->linkObjects($params);
		} catch (Exception $err){
			echo ($err->getMessage()); die();
		}
		$timeEnd = microtime(true);
		
		$totalTime = $timeEnd - $timeBegin;
		tpl_assign('params', $params->getParams());
		tpl_assign('time', $totalTime);
	}
	
	//----------------------------------------------
	// Data loader functions
	//----------------------------------------------
	
	function loadCompanies(DataLoaderParams $params){
		$count = $params->getParam('Companies');
		
		DB::beginWork();
		for ($i = 0; $i < $count; $i++){
			$company = new Company();
			$company->setName($params->getCompanyName());
			$company->setPhoneNumber("7897890");
			$company->setCity("Montevideo");
			$company->setAddress("hhjkhjklhjklhfd");
			$company->setCountry("Uruguay");
			$company->setEmail("chonwil@gmail.com");
			$company->setCreatedById(1);
			$company->setZipcode("12100");
			try{
				$company->save();
			} catch (Exception $err){
				DB::rollback();
				echo ($err->getMessage()); die();
			}
		}
		DB::commit();
	}
	
	function loadContacts(DataLoaderParams $params){
		$count = $params->getParam('Contacts');
		
		DB::beginWork();
		for ($i = 0; $i < $count; $i++){
			$contact = new Contact();
			$contact->setCompanyId(rand(0,2)==1?rand(0,$countCompanies - 1):0);
			$contact->setCreatedById(1);
			$contact->setDepartment("Deptio");
			$contact->setFirstname($params->getPersonFirstName());
			$contact->setLastname($params->getPersonLastName());
			try {
				$contact->save();
			} catch (Exception $err){
				DB::rollback();
				echo ($err->getMessage()); die();
			}
		}
		DB::commit();
	}
	
	function loadMessages(DataLoaderParams $params){
		$count = $params->getParam('Messages');
		
		$info = rand(0,10000000);
		DB::beginWork();
		for ($i = 0; $i < $count; $i++){
			try {
				$message = new ProjectMessage();
				$message->setText($params->getLoremIpsum());
				$message->setTitle($info . "-" . $i);
				$message->setCreatedById(1);
				$message->setProjectId(1);
				
				$message->save();
			} catch (Exception $err){
				DB::rollback();
				echo ($err->getMessage()); die();
			}
		}
		DB::commit();
	}
	
	function loadUsers(DataLoaderParams $params){
		$count = $params->getParam('Users');
		
		DB::beginWork();
		for ($i = 0; $i < $count; $i++){
			$user = new User();
			
			$fn = $params->getPersonFirstName();
			$ln = $params->getPersonLastName();
			$user->setDisplayName($fn . " " . $ln);
			$user->setEmail($fn . ".". $ln  . "@email" . $i . ".com");
			$user->setPassword("pass");
			$user->setUsername($fn.$i);
			$user->setCompanyId(1);
			$user->setToken("pass");
			
			try {
				$user->save();
			} catch (Exception $err){
				DB::rollback();
				echo ($err->getMessage()); die();
			}
		}
		DB::commit();
	}
	
	function loadWebpages(DataLoaderParams $params){
		$count = $params->getParam('Webpages');
		
		DB::beginWork();
		for ($i = 0; $i < $count; $i++){
			$webpage = new ProjectWebpage();
			$word = $params->getRandomWord();
			
			$webpage->setTitle($word);
			$webpage->setUrl("www." . $word . ".com");
			$webpage->setProjectId(1);
			
			try {
				$webpage->save();
			} catch (Exception $err){
				DB::rollback();
				echo ($err->getMessage()); die();
			}
		}
		DB::commit();
	}
	
	function loadTasks(DataLoaderParams $params){
		$count = $params->getParam('Tasks');
		DB::beginWork();
		
		for ($i = 0; $i < $count; $i++){
			$task = new ProjectTask();
			
			$task->setTitle($params->getRandomTaskName());
			$task->setProjectId(1);
			$task->setCreatedById(1);
			
			
			try {
				$task->save();
			} catch (Exception $err){
				DB::rollback();
				echo ($err->getMessage()); die();
			}
		}
		DB::commit();
	}
	
	function loadMilestones(DataLoaderParams $params){
		$count = $params->getParam('Milestones');
		
		DB::beginWork();
		for ($i = 0; $i < $count; $i++){
			$ms = new ProjectMilestone();
			$ms->setName($params->getRandomTaskName());
			$ms->setDueDate(new DateTimeValue(time()));
			$ms->setProjectId(1);
			$ms->setCreatedById(1);
			
			try {
				$ms->save();
			} catch (Exception $err){
				DB::rollback();
				echo ($err->getMessage()); die();
			}
		}
		DB::commit();
	}
	
	
	//----------------------------------------------
	// Other data manipulation functions
	//----------------------------------------------
	
	function linkObjects(DataLoaderParams $params){
		$count = $params->getParam('Link objects');
		
		DB::beginWork();
		for ($i = 0; $i < $count; $i++){
			try {
				$o1 = $this->getRandomObjectFromDB();
				$o2 = $this->getRandomObjectFromDB();
				
				if ($o1->getId() != $o2->getId())
					$o1->linkObject($o2);
			} catch (Exception $err){
				DB::rollback();
				echo ($err->getMessage()); die();
			}
		}
		DB::commit();
	}
	
	function getSOCount(){
		if ($this->soCount < 0 ){
			$res = DB::execute("SELECT count(*) as count from " . TABLE_PREFIX . "searchable_objects");
			$row = $res->fetchRow();
			$this->soCount = $row['count'];
		}
		return $this->soCount;
	}
	
	function getRandomObjectFromDB(){
		$count = $this->getSOCount();
		$rand = rand(1,$count);
		
		$res = DB::execute("SELECT rel_object_manager as manager, rel_object_id as id from " 
			. TABLE_PREFIX . "searchable_objects limit " . $rand . ",1");
		$row = $res->fetchRow();
		
		return get_object_by_manager_and_id($row['id'],$row['manager']);
	}
	
}
?>