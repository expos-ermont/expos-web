<?php

  /**
  * MailContents
  *
  * @author Carlos Palma <chonwil@gmail.com>
  */
  class MailContents extends BaseMailContents {
  	
  	/**
    * Return mails that belong to specific project
    *
    * @param Project $project
    * @return array
    */
    static function getProjectMails(Project $project) {
		$condstr = self::getWorkspaceString();
		return self::findAll(array(
			'conditions' => array($condstr, $project->getId())
		));
    } // getProjectMails
  	
	private static function getWorkspaceString(){
		return '`id` IN (SELECT `object_id` FROM `'.TABLE_PREFIX.'workspace_objects` WHERE `object_manager` = \'MailContents\' AND `workspace_id` = ?)';
	}
	
	function delete($condition) {
		if(isset($this) && instance_of($this, 'MailContents')) {
			if (FileRepository::getBackend() instanceof FileRepository_Backend_FileSystem) {
				// Delete contents from filesystem
				$sql = "SELECT `content_file_id` FROM ".self::instance()->getTableName(true)." WHERE $condition";
	      		$rows = DB::executeAll($sql);
				
	      		if (is_array($rows)) {
	      			$count = 0;$err=0;
	      			foreach ($rows as $row) {
						if (isset($row['content_file_id']) && $row['content_file_id'] != '') {
							try {
								FileRepository::deleteFile($row['content_file_id']);
								$count++;
							} catch (Exception $e) {
								$err++;
								Logger::log($e->getMessage());
							}
						}
					}
					Logger::log("Mails deleted: $count --- errors: $err");					
	      		}
			}
			return parent::delete($condition);
		} else {
			return MailContents::instance()->delete($condition);
		}
	}

  } // MailContents 

?>