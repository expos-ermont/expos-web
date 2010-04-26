<?php

/**
 * Controller that is responsible for handling project files related requests
 *
 * @version 1.0
 * @author Ilija Studen <ilija.studen@gmail.com>,  Marcos Saiz <marcos.saiz@opengoo.org>
 */
class FilesController extends ApplicationController {

	/**
	 * Construct the FilesController
	 *
	 * @access public
	 * @param void
	 * @return FilesController
	 */
	function __construct() {
		parent::__construct();
		prepare_company_website_controller($this, 'website');
	} // __construct

	/**
	 * Show files index page (list recent files)
	 *
	 * @param void
	 * @return null
	 */
	function index() {
		tpl_assign('allParam', array_var($_GET,'all'));
		tpl_assign('userParam',  array_var($_GET,'user'));
		tpl_assign('projectParam',  array_var($_GET,'project'));
		tpl_assign('tagParam',  array_var($_GET,'tag'));
		tpl_assign('typeParam',  array_var($_GET,'type'));
		tpl_assign('tags', Tags::getTagNames());
		if(isset($error))
		tpl_assign('error', $error);
	} // index

	// ---------------------------------------------------
	//  Files
	// ---------------------------------------------------

	/**
	 * Show file details
	 *
	 * @param void
	 * @return null
	 */
	function file_details() {
		$this->addHelper('textile');
			
		$file = ProjectFiles::findById(get_id());
		if(!($file instanceof ProjectFile)) {
			flash_error(lang('file dnx'));
			ajx_current("empty");
			return;
		} // if
			
		if(!$file->canView(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		} // if

		$revisions = $file->getRevisions();
		if(!count($revisions) && $file->getType() == ProjectFiles::TYPE_DOCUMENT) {
			flash_error(lang('no file revisions in file'));
			ajx_current("empty");
			return;
		} // if

		tpl_assign('file', $file);
		tpl_assign('last_revision', $file->getLastRevision());
		tpl_assign('revisions', $revisions);
		tpl_assign('order', null);
		tpl_assign('page', null);
		ajx_extra_data(array("title" => $file->getFilename(), 'icon'=>'ico-file'));
		ajx_set_no_toolbar(true);

	} // file_details

	function slideshow() {
		$this->setLayout('slideshow');
		$fileid = array_var($_GET, 'fileId');
		$file = ProjectFiles::instance()->findById($fileid);
		if(!$file->canView(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		} // if
		$content = $error = null;
		if (!$file) {
			$error = 'File not found';
		} else if (strcmp($file->getTypeString(), 'prsn') != 0) {
			$error = 'File is not a presentation';
		} else {
			$content = remove_css_and_scripts($file->getFileContent());
		}
		tpl_assign('error', $error);
		tpl_assign('content', $content);
	}//slideshow

	/**
	 * Download specific file
	 *
	 * @param void
	 * @return null
	 */
	function download_file() {
		$inline = (boolean) array_var($_GET, 'inline', false);
			
		$file = ProjectFiles::findById(get_id());
		if(!($file instanceof ProjectFile)) {
			flash_error(lang('file dnx'));
			//ajx_current("empty");
			return;
		} // if
			
		if(!$file->canDownload(logged_user())) {
			flash_error(lang('no access permissions'));
			//ajx_current("empty");
			return;
		} // if

		if ($file->getTypeString() == 'sprd') die(lang("not implemented"));
		session_commit();

		if(array_var($_GET, 'checkout')){
			if(get_id('checkout') == 1){
				if(!$file->checkOut()){
					flash_error(lang('document checked out'));
					return;
				}
			}
		}

		if(get_id('validate') == 1){
			evt_add('download document', array('id' => get_id(), 'reloadDocs' => true));
			return;
		}

		if (FileRepository::getBackend() instanceof FileRepository_Backend_FileSystem) {
			$path = FileRepository::getBackend()->getFilePath($file->getLastRevision()->getRepositoryId());
			if (is_file($path)) {
				// this method allows downloading big files without exhausting php's memory
				download_file($path, $file->getTypeString(), $file->getFilename(), $file->getFileSize(), !$inline);
				die();
			}
		}
		download_contents($file->getFileContent(), $file->getTypeString(), $file->getFilename(), $file->getFileSize(), !$inline);
		die();
	} // download_file
	
	function download_image() {
		$inline = (boolean) array_var($_GET, 'inline', false);
			
		$file = ProjectFiles::findById(get_id());
		if(!($file instanceof ProjectFile)) {
			flash_error(lang('file dnx'));
			ajx_current("empty");
			return;
		} // if
			
		if(!$file->canDownload(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		} // if
		session_commit();
		if (FileRepository::getBackend() instanceof FileRepository_Backend_FileSystem) {
			$path = FileRepository::getBackend()->getFilePath($file->getLastRevision()->getRepositoryId());
			if (is_file($path)) {
				// this method allows downloading big files without exhausting php's memory
				download_file($path, $file->getTypeString(), $file->getFilename(), $file->getFileSize(), !$inline);
				die();
			}
		}
		download_contents($file->getFileContent(), $file->getTypeString(), $file->getFilename(), $file->getFileSize(), !$inline);
		die();
	} // download_file

	function checkout_file()
	{
		ajx_current("empty");
		$file = ProjectFiles::findById(get_id());
		if(!($file instanceof ProjectFile)) {
			flash_error(lang('file dnx'));
			return;
		} // if

		if(!$file->canEdit(logged_user())) {
			flash_error(lang('no access permissions'));
			return;
		} // if
		
		try{
			DB::beginWork();
			$file->checkOut();
			DB::commit();

			flash_success(lang('success checkout file'));
			ajx_current("reload");
		}
		catch(Exception $e)
		{
			DB::rollback();
			flash_error($e->getMessage());
		}
	}
	
	function undo_checkout(){
		ajx_current("empty");
		$file = ProjectFiles::findById(get_id());
		if(!$file instanceof ProjectFile) {
			flash_error(lang('file dnx'));
			return;
		} // if

		if(!$file->canCheckin(logged_user())) {
			flash_error(lang('no access permissions'));
			return;
		} // if
		
		try {
			$file->cancelCheckOut();
			
			flash_success(lang("success undo checkout file"));
			if (array_var($_GET, 'back', false)) {
				ajx_current("back");
			} else {
				ajx_current("reload");
			}
		} catch (Exception $e){
			Db::rollback();
			flash_error($e->getMessage());
		}
	}
	
	/**
	 * Download specific revision
	 *
	 * @param void
	 * @return null
	 */
	function download_revision() {
		$inline = (boolean) array_var($_GET, 'inline', false);
		$revision = ProjectFileRevisions::findById(get_id());
		if(!($revision instanceof ProjectFileRevision)) {
			flash_error(lang('file revision dnx'));
			ajx_current("empty");
			return;
		} // if
			
		$file = $revision->getFile();
		if(!($file instanceof ProjectFile)) {
			flash_error(lang('file dnx'));
			ajx_current("empty");
			return;
		} // if
			
		if(!($file->canDownload(logged_user()))) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		} // if
		session_commit();
		if (FileRepository::getBackend() instanceof FileRepository_Backend_FileSystem) {
			$path = FileRepository::getBackend()->getFilePath($revision->getRepositoryId());
			if (is_file($path)) {
				// this method allows downloading big files without exhausting php's memory
				download_file($path, $revision->getTypeString(), $file->getFilename(), $file->getFileSize(), !$inline);
				die();
			}
		}
		download_contents($revision->getFileContent(), $revision->getTypeString(), $file->getFilename(), $file->getFileSize(), !$inline);
		die();
	} // download_revision

	/**
	 * Add file
	 *
	 * @access public
	 * @param void
	 * @return null
	 */
	function add_file() {
		$file_data = array_var($_POST, 'file');

		$file = new ProjectFile();
			
		tpl_assign('file', $file);
		tpl_assign('file_data', $file_data);
		tpl_assign('tags', Tags::getTagNames());
			
		if (is_array(array_var($_POST, 'file'))) {
			$this->setLayout("html");
			$ids = array_var($_POST, "ws_ids", "");
			$enteredWS = Projects::findByCSVIds($ids);
			$validWS = array();
			foreach ($enteredWS as $ws) {
				if (ProjectFile::canAdd(logged_user(), $ws)) {
					$validWS[] = $ws;
				}
			}
			if (empty($validWS)) {
				flash_error(lang('must choose at least one workspace error'));
				ajx_current("empty");
				return;
			}
			
			$upload_option = array_var($file_data, 'upload_option');
			$skipSettings = false;
			try {
				DB::beginWork();
				if ($upload_option && $upload_option != -1){
					$skipSettings = true;
					$file = ProjectFiles::findById($upload_option);
					$old_subs = $file->getSubscribers();
					
					// Mantain old subscribers
					foreach($old_subs as $user) {
						$value = "user_" . $user->getId();
						if (is_array($_POST['subscribers'])) {
							if (array_var($_POST['subscribers'], $value, null) != 'checked')
								$_POST['subscribers'][$value] = 'checked';
						}
					}
					
					if ($file->isCheckedOut()){
						if (!$file->canCheckin(logged_user())){
							flash_error(lang('no access permissions'));
							ajx_current("empty");
							return;
						}
						$file->setCheckedOutById(0);
					} else {  // Check for edit permissions
						if (!$file->canEdit(logged_user())){
							flash_error(lang('no access permissions'));
							ajx_current("empty");
							return;
						}
					}
				} else {
					$type = array_var($file_data, 'type');
					$file->setType($type);
					$file->setFilename(array_var($file_data, 'name'));
					$file->setFromAttributes($file_data);
					$file->setIsPrivate(false);
	
					if(!logged_user()->isMemberOfOwnerCompany()) {
						$file->setIsImportant(false);
						$file->setCommentsEnabled(true);
						$file->setAnonymousCommentsEnabled(false);
					} // if
					$file->setIsVisible(true);
				}
				
				$file->save();
				if($file->getType() == ProjectFiles::TYPE_DOCUMENT){
					// handle uploaded file
					$upload_id = array_var($file_data, 'upload_id');
					$uploaded_file = array_var($_SESSION, $upload_id, array());
					$revision = $file->handleUploadedFile($uploaded_file, true); // handle uploaded file
					@unlink($uploaded_file['tmp_name']);
					unset($_SESSION[$upload_id]);
				}
				

				//Add properties
				if (!$skipSettings){
					$file->setTagsFromCSV(array_var($file_data, 'tags'));
					foreach ($validWS as $w) {
						$file->addToWorkspace($w);
					}
				}

				//Add links
			    $object_controller = new ObjectController();
			    $object_controller->link_to_new_object($file);
				$object_controller->add_subscribers($file);
				$object_controller->add_custom_properties($file);
				
				ApplicationLogs::createLog($file, $validWS, ApplicationLogs::ACTION_ADD);
				
				DB::commit();
				//flash_success(array_var($file_data, 'add_type'));
				
				flash_success(lang('success add file', $file->getFilename()));
	          	if (array_var($_POST, 'popup', false)) {
					ajx_current("reload");
	          	} else {
	          		ajx_current("back");
	          	}
	          	ajx_add("overview-panel", "reload");
			
			} catch(Exception $e) {
				DB::rollback();
				flash_error($e->getMessage());
				ajx_current("empty");

				// If we uploaded the file remove it from repository
				if(isset($revision) && ($revision instanceof ProjectFileRevision) && FileRepository::isInRepository($revision->getRepositoryId())) {
					FileRepository::deleteFile($revision->getRepositoryId());
				} // if
			} // try
		} // if
	} // add_file

	function temp_file_upload() {
		$id = array_var($_GET, 'id');
		$uploaded_file = array_var($_FILES, 'file_file');
		$fname = ROOT . "/tmp/$id";
		copy($uploaded_file['tmp_name'], $fname);
		$_SESSION[$id] = array(
			'name' => $uploaded_file['name'],
			'size' => $uploaded_file['size'],
			'type' => $uploaded_file['type'],
			'tmp_name' => $fname,
			'error' => $uploaded_file['error']
		);
	}
	
	function save_document() {
		ajx_current("empty");
		$postFile = array_var($_POST, 'file');
		$fileId = array_var($postFile, 'id');
		if($fileId > 0) {
			//edit document
			try {
				// edit document
				$file = ProjectFiles::findById($fileId);
				if (!$file->canEdit(logged_user())) {
					flash_error(lang('no access permissions'));
					ajx_current("empty");
					return;
				} // if
				DB::beginWork();
				$post_revision = array_var($_POST, 'new_revision_document') == 'checked'; // change file?
				$revision_comment = array_var($postFile, 'comment');

				$file_dt['name'] = $file->getFilename();
				$file_content = array_var($_POST, 'fileContent');
				$file_dt['size'] = strlen($file_content);
				$file_dt['type'] = array_var($_POST, 'fileMIME', 'text/html');
				$file_dt['tmp_name'] = './tmp/' . rand () ;
				$handler = fopen($file_dt['tmp_name'], 'w');
				fputs($handler,$file_content);
				fclose($handler);
				$name = array_var($postFile, 'name');

				$file->setFilename($name);
				$file->save();
				$file->setTagsFromCSV(array_var($file_data, 'tags'));
				$file->handleUploadedFile($file_dt, $post_revision, $revision_comment);
				
				if (array_var($_POST, 'checkin', false)) {
					$file->checkIn();
					ajx_current("back");
				}
				
				$ws = $file->getWorkspaces();
				ApplicationLogs::createLog($file, $ws, ApplicationLogs::ACTION_EDIT);
				DB::commit();
				unlink($file_dt['tmp_name']);

				flash_success(lang('success save file', $file->getFilename()));
				evt_add("document saved", array("id" => $file->getId(), "instance" => array_var($_POST, 'instanceName')));
				//$this->redirectTo('files', 'add_document', array('id' => $file->getId()));
				ajx_add("overview-panel", "reload");
			} catch(Exception $e) {
				DB::rollback();
				unlink($file_dt['tmp_name']);
				flash_error(lang('error while saving'), $e->getMessage());
				//$this->redirectToReferer(get_url('files'));
			} // try
		} else  {
			// new document
			if (!ProjectFile::canAdd(logged_user(), active_or_personal_project())) {
				flash_error(lang('no access permissions'));
				ajx_current("empty");
				return ;
			} // if

			// prepare the file object
			$file = new ProjectFile();
			$name = array_var($postFile, 'name');
			$file->setFilename($name);
			$file->setIsVisible(true);

			$file->setIsPrivate(false);
			$file->setIsImportant(false);
			$file->setCommentsEnabled(true);
			$file->setAnonymousCommentsEnabled(false);

			//seteo esto para despues setear atributos
			$file_content = array_var($_POST, 'fileContent');
			$file_dt['name'] = array_var($postFile,'name');
			$file_dt['size'] = strlen($file_content);
			$file_dt['type'] = array_var($_POST, 'fileMIME', 'text/html');

			$file->setCreatedOn(new DateTimeValue(time()) );
			try {
				DB::beginWork();
				$file_dt['tmp_name'] = './tmp/' . rand ();
				$handler = fopen($file_dt['tmp_name'], 'w');
				fputs($handler, array_var($_POST, 'fileContent'));
				fclose($handler);

				$revision_comment = array_var($postFile, 'comment');
				$file->save();
				$file->addToWorkspace(active_or_personal_project());
				$file->setTagsFromCSV('');
				$revision = $file->handleUploadedFile($file_dt, true, $revision_comment);

				if (config_option('checkout_for_editing_online')) {
					$file->checkOut(true, logged_user());
				}
				
				$ws = $file->getWorkspaces();
				ApplicationLogs::createLog($file, $ws, ApplicationLogs::ACTION_ADD);

				DB::commit();
				flash_success(lang('success save file', $file->getFilename()));
				evt_add("document saved", array("id" => $file->getId(), "instance" => array_var($_POST, 'instanceName')));
				unlink($file_dt['tmp_name']);
				//$this->redirectTo('files', 'add_document', array('id' => $file->getId()));
			} catch(Exception $e) {
				DB::rollback();

				unlink($file_dt['tmp_name']);
				// if we uploaded the file remove it from repository
				if	(isset($revision) && ($revision instanceof ProjectFileRevision) && FileRepository::isInRepository($revision->getRepositoryId())) {
					FileRepository::deleteFile($revision->getRepositoryId());
				} // if
				flash_error(lang('error while saving'));
				//$this->redirectToUrl(get_url('files'));
			} // try
		}
	}

	function save_presentation() {
		ajx_current("empty");
		$postFile = array_var($_POST, 'file');
		$fileid = array_var($postFile, 'id');
		if($fileid > 0) {
			//edit presentation
			try {
				$file = ProjectFiles::findById($fileid);
				if (!$file->canEdit(logged_user())) {
					flash_error(lang('no access permissions'));
					ajx_current("empty");
					return;
				} // if
				DB::beginWork();
				$post_revision = array_var($_POST, 'new_revision_document') == 'checked'; // change file?
				$revision_comment = '';

				$file_dt['name'] = $file->getFilename();
				$file_content = unescapeSLIM(array_var($_POST, 'slimContent'));
				$file_dt['size'] = strlen($file_content);
				$file_dt['type'] = 'prsn';
				$file_dt['tmp_name'] = './tmp/' . rand() ;
				$handler = fopen($file_dt['tmp_name'], 'w');
				fputs($handler,$file_content);
				fclose($handler);
				$file->setFilename(array_var($postFile, 'name'));
				$file->save();
				$file->setTagsFromCSV(array_var($file_data, 'tags'));
				$file->handleUploadedFile($file_dt, $post_revision, $revision_comment);
				
				if (array_var($_POST, 'checkin', false)) {
					$file->checkIn();
					ajx_current("back");
				}
				
				$ws = $file->getWorkspaces();
				ApplicationLogs::createLog($file, $ws, ApplicationLogs::ACTION_EDIT);

				DB::commit();
				unlink($file_dt['tmp_name']);

				flash_success(lang('success save file', $file->getFilename()));
				evt_add("presentation saved", array("id" => $file->getId()));
				//$this->redirectTo('files', 'add_presentation', array('id' => $file->getId()));
				ajx_add("overview-panel", "reload");
			} catch(Exception $e) {
				DB::rollback();
				unlink($file_dt['tmp_name']);
				flash_error(lang('error while saving'));
				//$this->redirectToUrl(get_url('files'));
			} // try
		} else  {
			// new presentation
			if (!ProjectFile::canAdd(logged_user(), active_or_personal_project())) {
				flash_error(lang('no access permissions'));
				$this->redirectToReferer(get_url('files'));
				return ;
			} // if

			// prepare the file object
			$file = new ProjectFile();
			$file->setFilename(array_var($postFile, 'name'));
			$file->setIsVisible(true);

			$file->setIsPrivate(false);
			$file->setIsImportant(false);
			$file->setCommentsEnabled(true);
			$file->setAnonymousCommentsEnabled(false);
				
			//seteo esto para despues setear atributos
			$file_content = unescapeSLIM(array_var($_POST, 'slimContent'));
			$file_dt['name'] = array_var($postFile, 'name');
			$file_dt['size'] = strlen($file_content);
			$file_dt['type'] = 'prsn';

			$file->setCreatedOn(new DateTimeValue(time()) );
			try {
				DB::beginWork();
				$file_dt['tmp_name'] = './tmp/' . rand ();
				$handler = fopen($file_dt['tmp_name'], 'w');
				fputs($handler, unescapeSLIM(array_var($_POST, 'slimContent')));
				fclose($handler);

				$file->save();
				$file->addToWorkspace(active_or_personal_project());
				$file->setTagsFromCSV('');
				$revision = $file->handleUploadedFile($file_dt, true);

				if (config_option('checkout_for_editing_online')) {
					$file->checkOut(true, logged_user());
				}
				
				$ws = $file->getWorkspaces();
				ApplicationLogs::createLog($file, $ws, ApplicationLogs::ACTION_ADD);

				DB::commit();
				flash_success(lang('success save file', $file->getFilename()));
				evt_add("presentation saved", array("id" => $file->getId()));
				unlink($file_dt['tmp_name']);
				//$this->redirectTo('files', 'add_presentation', array('id' => $file->getId()));
			} catch(Exception $e) {
				DB::rollback();

				//tpl_assign('error', $e);
				tpl_assign('file', new ProjectFile()); // reset file
				unlink($file_dt['tmp_name']);
				// if we uploaded the file remove it from repository
				if	(isset($revision) && ($revision instanceof ProjectFileRevision) && FileRepository::isInRepository($revision->getRepositoryId())) {
					FileRepository::deleteFile($revision->getRepositoryId());
				} // if
				flash_error(lang('error while saving'));
				//$this->redirectToUrl(get_url('files'));
			} // try
		}
	}

	function save_spreadsheet() {
		ajx_current("empty");
		$id = get_id();
		$file_content = array_var($_GET, "book");
		$name = trim(array_var($_GET, 'name', ''));
		if ($id > 0) {
			//edit spreadsheet
			if ($name == '') $name = $file->getFilename();
			try {
				$file = ProjectFiles::findById(get_id());
				if (!$file->canEdit(logged_user())) {
					flash_error(lang('no access permissions'));
					ajx_current("empty");
					return;
				} // if
				DB::beginWork();
				$file->setFilename($name);
				$post_revision = true;
				$revision_comment = '';

				$file_dt['name'] = $name;
				$file_dt['size'] = strlen($file_content);
				$file_dt['type'] = 'sprd';
				$file_dt['tmp_name'] = './tmp/' . rand () ;
				$handler = fopen($file_dt['tmp_name'], 'w');
				fputs($handler, $file_content);
				fclose($handler);
				$file->save();
				$file->handleUploadedFile($file_dt, $post_revision, $revision_comment);
				
				$ws = $file->getWorkspaces();
				ApplicationLogs::createLog($file, $ws, ApplicationLogs::ACTION_EDIT);
				
				DB::commit();
				unlink($file_dt['tmp_name']);

				flash_success(lang('success save file', $file->getFilename()));
				ajx_add("overview-panel", "reload");
				ajx_extra_data(array("sprdID" => $file->getId()));
			} catch(Exception $e) {
				DB::rollback();
				unlink($file_dt['tmp_name']);
				tpl_assign('error', $e);
				flash_error(lang('error while saving'));
			} // try
		} else  {
			//new spreadsheet
			if ($name == '') $name = lang('new spreadsheet');
			try {
				if(!ProjectFile::canAdd(logged_user(), active_or_personal_project())) {
					flash_error(lang('no access permissions'));
					$this->redirectToReferer(get_url('files'));
					return ;
				} // if
	
				// create the file object
				$file = new ProjectFile();
				$file->setFilename($name);
				$file->setIsVisible(true);
	
				$file->setIsPrivate(false);
				$file->setIsImportant(false);
				$file->setCommentsEnabled(true);
				$file->setAnonymousCommentsEnabled(false);
	
				//seteo esto para despues setear atributos
				$file_dt['name'] = $name;
				$file_dt['size'] = strlen($file_content);
				$file_dt['type'] = 'sprd';
				$file_dt['tmp_name'] = './tmp/' . rand ();
				$handler = fopen($file_dt['tmp_name'], 'w');
				fputs($handler, $file_content);
				fclose($handler);

				$file->setCreatedOn(new DateTimeValue(time()));
				
				DB::beginWork();

				$file->save();
				$file->addToWorkspace(active_or_personal_project());
				$file->setTagsFromCSV('');
				$revision = $file->handleUploadedFile($file_dt, true); // handle uploaded file
				
				$ws = $file->getWorkspaces();
				ApplicationLogs::createLog($file, $ws, ApplicationLogs::ACTION_ADD);

				DB::commit();
				unlink($file_dt['tmp_name']);
				flash_success(lang('success add file', $file->getFilename()));
				ajx_extra_data(array("sprdID" => $file->getId()));
			} catch(Exception $e) {
				DB::rollback();
					
				tpl_assign('error', $e);
				tpl_assign('file', new ProjectFile()); // reset file
				unlink($file_dt['tmp_name']);
				// if we uploaded the file remove it from repository
				if (isset($revision) && ($revision instanceof ProjectFileRevision) && FileRepository::isInRepository($revision->getRepositoryId())) {
					FileRepository::deleteFile($revision->getRepositoryId());
				} // if
				flash_error(lang('error while saving'));
			} // try
		}//new spreadsheet
	}

	function text_edit() {
		$file_data = array_var($_POST, 'file');
		if (!isset($file_data)) {
			// open text file
			$file = ProjectFiles::findById(get_id());
			if (!($file instanceof ProjectFile)) {
				flash_error(lang('file dnx'));
				ajx_current("empty");
				return;
			} // if

			if (!$file->canEdit(logged_user())) {
				flash_error(lang('no access permissions'));
				ajx_current("empty");
				return;
			} // if

				
			tpl_assign('file', $file);
		} else {
			ajx_current("empty");
			// save new file content
			try {
				$file = ProjectFiles::findById(array_var($file_data, 'id'));
				if (!($file instanceof ProjectFile)) {
					flash_error(lang('file dnx'));
					ajx_current("empty");
					return;
				} // if
				if (!$file->canEdit(logged_user())) {
					flash_error(lang('no access permissions'));
					return;
				} // if
				DB::beginWork();
				$post_revision = array_var($_POST, 'new_revision_document') == 'checked'; // change file?
				$revision_comment = '';

				$file_dt['name'] = $file->getFilename();
				$file_content = iconv(detect_encoding(array_var($_POST, 'fileContent'), array('UTF-8','ISO-8859-1')), array_var($file_data, 'encoding'),array_var($_POST, 'fileContent'));
				$file_dt['size'] = strlen($file_content);
				$file_dt['type'] = $file->getTypeString();
				$file_dt['tmp_name'] = './tmp/' . rand () ;
				$handler = fopen($file_dt['tmp_name'], 'w');
				fputs($handler, $file_content);
				fclose($handler);
				//$file->setFilename(array_var($postFile, 'name'));
				$file->save();
				$file->handleUploadedFile($file_dt, $post_revision, $revision_comment);
				
				$ws = $file->getWorkspaces();
				ApplicationLogs::createLog($file, $ws, ApplicationLogs::ACTION_EDIT);

				DB::commit();
				unlink($file_dt['tmp_name']);

				flash_success(lang('success save file', $file->getFilename()));
			} catch(Exception $e) {
				DB::rollback();
				unlink($file_dt['tmp_name']);
				flash_error(lang('error while saving'));
			} // try
		}// if
	} // text_edit

	function add_document() {
		if (get_id() > 0) {
			//open a document
			try {
				DB::beginWork();
				
				$this->setTemplate('add_document');
	
				$file = ProjectFiles::findById(get_id());
				if (!($file instanceof ProjectFile)) {
					throw new Exception(lang('file dnx'));
				} // if
	
				if(!$file->canEdit(logged_user()) || !$file->isModifiable()) {
					if ($file->isCheckedOut() && !$file->canCheckin(logged_user())) {
						throw new Exception(lang('error document checked out by another user'));
					} else {
						throw new Exception(lang('no access permissions'));
					}
				} // if
				
				if (config_option('checkout_for_editing_online')) {
					$file->checkOut(true, logged_user());
				}
				
				$file_data = array_var($_POST, 'file');
				if (!is_array($file_data)) {
					$tag_names = $file->getTagNames();
					$file_data = array(
					//deprecated'folder_id' => $file->getFolderId(),
						'description' => $file->getDescription(),
						'is_private' => $file->getIsPrivate(),
						'is_important' => $file->getIsImportant(),
						'comments_enabled' => $file->getCommentsEnabled(),
						'anonymous_comments_enabled' => $file->getAnonymousCommentsEnabled(),
						'tags' => is_array($tag_names) && count($tag_names) ? implode(', ', $tag_names) : '',
					); // array
				} // if
	
				tpl_assign('file', $file);
				tpl_assign('file_data', $file_data);
				DB::commit();
			} catch (Exception $e) {
				ajx_current("empty");
				DB::rollback();
				flash_error($e->getMessage());
			}
		} else {
			//new document
			if (!ProjectFile::canAdd(logged_user(), active_or_personal_project())) {
				flash_error(lang('no access permissions'));
				ajx_current("empty");
				return;
			} // if

			$file = new ProjectFile();
			$file_data = array_var($_POST, 'file');
			
			tpl_assign('file', $file);
			tpl_assign('file_data', $file_data);
		}//end new document
	} // add_document

	function add_spreadsheet() {
		if (get_id() > 0) {
			//open a spreadsheet
			$file = ProjectFiles::findById(get_id());
			if(!($file instanceof ProjectFile)) {
				flash_error(lang('file dnx'));
				ajx_current("empty");
				return;
			} // if

			if(!$file->canEdit(logged_user())) {
				flash_error(lang('no access permissions'));
				ajx_current("empty");
				return;
			} // if

			tpl_assign('file', $file);
		} else {
			// new spreadsheet
			if (!ProjectFile::canAdd(logged_user(), active_or_personal_project())) {
				flash_error(lang('no access permissions'));
				ajx_current("empty");
				return;
			} // if

			$file = new ProjectFile();
			tpl_assign('file', $file);
		}
	} // add_spreadsheet

	function add_presentation() {
		if (get_id() > 0) {
			//open presentation
			try {
				DB::beginWork();
				$this->setTemplate('add_presentation');
				$file = ProjectFiles::findById(get_id());
	
				if (!($file instanceof ProjectFile)) {
					throw new Exception(lang('file dnx'));
				} // if
	
				if (!$file->canEdit(logged_user())) {
					if ($file->isCheckedOut() && !$file->canCheckin(logged_user())) {
						throw new Exception(lang('error document checked out by another user'));
					} else {
						throw new Exception(lang('no access permissions'));
					}
				} // if
	
				if (config_option('checkout_for_editing_online')) {
					$file->checkOut(true, logged_user());
				}
				
				$file_data = array_var($_POST, 'file');
				if (!is_array($file_data)) {
					$tag_names = $file->getTagNames();
					$file_data = array(
					//'folder_id' => $file->getFolderId(),
						'description' => $file->getDescription(),
						'is_private' => $file->getIsPrivate(),
						'is_important' => $file->getIsImportant(),
						'comments_enabled' => $file->getCommentsEnabled(),
						'anonymous_comments_enabled' => $file->getAnonymousCommentsEnabled(),
						'tags' => is_array($tag_names) && count($tag_names) ? implode(', ', $tag_names) : '',
					); // array
				} // if
				tpl_assign('file', $file);
				tpl_assign('file_data', $file_data);
				DB::commit();
			} catch (Exception $e) {
				DB::rollback();
				flash_error($e->getMessage());
				ajx_current("empty");
			}
		} else {
			//new presentation
			if(!ProjectFile::canAdd(logged_user(), active_or_personal_project())) {
				flash_error(lang('no access permissions'));
				ajx_current("empty");
				return;
			} // if

			$file = new ProjectFile();
			$file_data = array_var($_POST, 'file');

			tpl_assign('file', $file);
			tpl_assign('file_data', $file_data);
		}
	}

	function list_files() {
		ajx_current("empty");
		 
		/* get query parameters */
		$start = (integer)array_var($_GET,'start');
		$limit = (integer)array_var($_GET,'limit');
		if (! $start) {
			$start = 0;
		}
		if (! $limit) {
			$limit = config_option('files_per_page');
		}
		$order = array_var($_GET,'sort');
		$orderdir = array_var($_GET,'dir');
		$page = (integer) ($start / $limit)+1;
		$hide_private = !logged_user()->isMemberOfOwnerCompany();
		$project_id = array_var($_GET, 'active_project', 0);
		$tag = array_var($_GET,'tag');
		$type = array_var($_GET,'type');
		$user = array_var($_GET,'user');

		/* if there's an action to execute, do so */
		if (array_var($_GET, 'action') == 'delete') {
			$ids = explode(',', array_var($_GET, 'objects'));
			$succ = 0; $err = 0;
			foreach ($ids as $id) {
				$file = ProjectFiles::findById($id);
				if (isset($file) && $file->canDelete(logged_user())) {
					try{
						DB::beginWork();
						$file->trash();
						ApplicationLogs::createLog($file, $file->getWorkspaces(), ApplicationLogs::ACTION_TRASH);
						DB::commit();
						$succ++;
					} catch(Exception $e){
						DB::rollback();
						$err++;
					}
				} else {
					$err++;
				}
			}
			if ($succ > 0) {
				flash_success(lang("success delete files", $succ));
			} else {
				flash_error(lang("error delete files", $err));
			}

		} else if (array_var($_GET, 'action') == 'tag') {
			$ids = explode(',', array_var($_GET, 'objects'));
			$tagTag = array_var($_GET, 'tagTag');
			
			foreach ($ids as $id) {
				$file = ProjectFiles::findById($id);
				if (isset($file) && $file->canEdit(logged_user())) {
					$arr_tags = $file->getTags();
					if (!array_search($tagTag, $arr_tags)) {
						$arr_tags[] = $tagTag;
						$file->setTagsFromCSV(implode(',', $arr_tags));
					}
				} else flash_error(lang('no access permissions'));
			}
/*
			list($succ, $err) = ObjectController::do_tag_object($tagTag, $ids,'ProjectFiles');
			if ($err > 0) {
				flash_error(lang('error tag objects', $err));
			} else {
				flash_success(lang('success tag objects', $succ));
			}
*/
		} else if (array_var($_GET, 'action') == 'zip_add') {
			$this->zip_add();
		}
		
		Hook::fire('classify_action', null, $ret);		

		$project = Projects::findById($project_id);
		/* perform query */
		$result = ProjectFiles::getProjectFiles($project, null,
		$hide_private, $order, $orderdir, $page, $limit, false, $tag, $type, $user);
		if (is_array($result)) {
			list($objects, $pagination) = $result;
			if ($pagination->getTotalItems() < (($page - 1) * $limit)){
				$start = 0;
				$page = 1;
				$result = ProjectFiles::getProjectFiles($project, null,
				$hide_private, $order, $orderdir, $page, $limit, false, $tag, $type, $user);
				if (is_array($result)) {
					list($objects, $pagination) = $result;
				}else {
					$objects = null;
					$pagination = 0 ;
				} // if
			}
		} else {
			$objects = null;
			$pagination = null ;
		} // if

		/* prepare response object */
		$listing = array(
			"totalCount" => ($pagination ? $pagination->getTotalItems() : 0),
			"start" => $start,
			"files" => array()
		);
		if($objects){
			foreach ($objects as $o) {
				$coName = "";
				$coId = $o->getCheckedOutById();
				if ($coId != 0)
				{
					if ($coId == logged_user()->getId())
						$coName = "self";
					else
						$coName = Users::findById($coId)->getUsername();
				}

				if ($o->getTypeString() == 'audio/mpeg') {
					$songname = $o->getProperty("songname");
					$artist = $o->getProperty("songartist");
					$album = $o->getProperty("songalbum");
					$track = $o->getProperty("songtrack");
					$year = $o->getProperty("songyear");
					$duration = $o->getProperty("songduration");
					$songInfo = json_encode(array($songname, $artist, $album, $track, $year, $duration, $o->getDownloadUrl(), $o->getFilename(), $o->getId()));
				} else {
					$songInfo = array();
				}

				$values = array(
					"id" => $o->getId(),
					"object_id" => $o->getId(),
					"name" => $o->getFilename(),
					"type" => $o->getObjectTypeName(),
					"mimeType" => $o->getTypeString(),
					"tags" => project_object_tags($o),
					"createdBy" => $o->getCreatedByDisplayName(),
					"createdById" => $o->getCreatedById(),
					"dateCreated" => $o->getCreatedOn() instanceof DateTimeValue ? $o->getCreatedOn()->getTimestamp() : 0,
					"updatedBy" => $o->getUpdatedByDisplayName(),
					"updatedById" => $o->getUpdatedById(),
					"dateUpdated" => $o->getUpdatedOn() instanceof DateTimeValue ? $o->getUpdatedOn()->getTimestamp() : 0,
					"icon" => $o->getTypeIconUrl(),
					"size" => $o->getFileSize(),
					"wsIds" => $o->getWorkspacesIdsCSV(logged_user()->getActiveProjectIdsCSV()),
					"url" => $o->getOpenUrl(),
					"manager" => get_class($o->manager()),
					"checkedOutByName" => $coName,
					"checkedOutById" => $coId,
					"isModifiable" => $o->isModifiable() && $o->canEdit(logged_user()),
					"modifyUrl" => $o->getModifyUrl(),
					"songInfo" => $songInfo,
					"ftype" => $o->getType(),
					"url" => $o->getUrl(),
				);
				Hook::fire('add_classification_value', $o, $values);
				$listing["files"][] = $values;
			}
		}
		ajx_extra_data($listing);
		tpl_assign("listing", $listing);
	}

	function open_file() {
		$fileId = $_GET['id'];
		$file = ProjectFiles::findById($fileId);
		if (!$file->canEdit(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		} // if
		if ($file) {
			$this->redirectToUrl($file->getModifyUrl());
		} else {
			flash_error(lang('file dnx'));
			ajx_current("empty");
		}
	}

	/**
	 * Tag file
	 *
	 * @access public
	 * @param void
	 * @return null
	 */
	function tag_file() {
		$tag = array_var($_GET, 'tag');
		$ids = explode(',', array_var($_GET, 'files'));
		list($succ, $err) = $this->do_tag_file($tag, $ids);
		if($err) {
			flash_error(lang('error tag files', $err));
		}
		if ($succ) {
			flash_success(lang('success tag files'), $succ);
		}
		ajx_current("empty");
	}

	function do_tag_file($tag, $ids) {
		$err = $succ = 0;
		foreach ($ids as $id) {
			if (trim($id) != '') {
				try {
					$file = ProjectFiles::findById($id);
					if (!$file->canEdit(logged_user())) {
						$err ++;
					} // if
					else {
						Tags::addObjectTag($tag, $file);
						ApplicationLogs::createLog($file, $file->getWorkspaces(), ApplicationLogs::ACTION_TAG,false,null,true,$tag);
						$succ++;
					}
				} catch (Exception $e) {
					$err ++;
				}
			}
		}
		return array($succ, $err);
	}

	/**
	 * Edit file properties
	 *
	 * @access public
	 * @param void
	 * @return null
	 */
	function edit_file() {
		$this->setTemplate('add_file');

		$file = ProjectFiles::findById(get_id());
		if(!($file instanceof ProjectFile)) {
			flash_error(lang('file dnx'));
			ajx_current("empty");
			return;
		} // if
			
		if(!$file->canEdit(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		} // if
			
		$file_data = array_var($_POST, 'file');
		if(!is_array($file_data)) {
			$tag_names = $file->getTagNames();
			$file_data = array(
			//'folder_id' => $file->getFolderId(),
				'description' => $file->getDescription(),
				'is_private' => $file->getIsPrivate(),
				'is_important' => $file->getIsImportant(),
				'comments_enabled' => $file->getCommentsEnabled(),
				'anonymous_comments_enabled' => $file->getAnonymousCommentsEnabled(),
				'tags' => is_array($tag_names) && count($tag_names) ? implode(', ', $tag_names) : '',
				'edit_name' => $file->getFilename(),
				'file_id' => get_id()
			); // array
		} // if
		tpl_assign('file', $file);
		tpl_assign('file_data', $file_data);

			
		if(is_array(array_var($_POST, 'file'))) {
			try {
				$ids = array_var($_POST, "ws_ids", "");
				$enteredWS = Projects::findByCSVIds($ids);
				$validWS = array();
				foreach ($enteredWS as $ws) {
					if (ProjectFile::canAdd(logged_user(), $ws)) {
						$validWS[] = $ws;
					}
				}
				if (empty($validWS)) {
					flash_error(lang('must choose at least one workspace error'));
					ajx_current("empty");
					return;
				}

				$old_is_private = $file->isPrivate();
				$old_is_important = $file->getIsImportant();
				$old_comments_enabled = $file->getCommentsEnabled();
				$old_anonymous_comments_enabled = $file->getAnonymousCommentsEnabled();
					
				DB::beginWork();
				$handle_file      = array_var($file_data, 'update_file') == 'checked'; // change file?
				$post_revision    = $handle_file && array_var($file_data, 'version_file_change') == 'checked'; // post revision?
				$revision_comment = $post_revision ? trim(array_var($file_data, 'revision_comment')) : ''; // user comment?

				$file->setFromAttributes($file_data);
				$file->setFilename(array_var($file_data, 'name'));
				
				if($file->getType() == ProjectFiles::TYPE_WEBLINK){
					$file->setUrl(array_var($file_data, 'url'));
				}

				if(!logged_user()->isMemberOfOwnerCompany()) {
					$file->setIsPrivate($old_is_private);
					$file->setIsImportant($old_is_important);
					$file->setCommentsEnabled($old_comments_enabled);
					$file->setAnonymousCommentsEnabled($old_anonymous_comments_enabled);
				} // if
				//$file->setFilename(array_var($file_data, 'name'));
				$file->save();
				$file->setTagsFromCSV(array_var($file_data, 'tags'));
				if( $handle_file) {
					// handle uploaded file
					$upload_id = array_var($file_data, 'upload_id');
					$uploaded_file = array_var($_SESSION, $upload_id, array());
					$file->handleUploadedFile($uploaded_file, $post_revision, $revision_comment); // handle uploaded file
					@unlink($uploaded_file['tmp_name']);
				} // if

				$file->removeFromWorkspaces(logged_user()->getActiveProjectIdsCSV());
				foreach ($validWS as $w) {
					$file->addToWorkspace($w);
				}

				$object_controller = new ObjectController();
				$object_controller->link_to_new_object($file);
				$object_controller->add_subscribers($file);
				$object_controller->add_custom_properties($file);

				ApplicationLogs::createLog($file, $validWS, ApplicationLogs::ACTION_EDIT);

				DB::commit();
								
				flash_success(lang('success edit file', $file->getFilename()));
				ajx_current("back");
			} catch(Exception $e) {
				//@unlink($file->getFilePath());
				DB::rollback();
				ajx_current("empty");
				flash_error($e->getMessage());
			} // try
		} // if
	} // edit_file

	function release_file() {
		ajx_current("empty");
		$id = array_var($_GET, 'id');
		$file = ProjectFiles::findById(get_id());
		if ($file instanceof ProjectFile) {
			$file->cancelCheckOut();
		}
	}
	
	function auto_checkin() {
		ajx_current("empty");
		ProjectFiles::closeAutoCheckedoutFilesByUser();
	}

	function auto_checkout(){
		$this->checkout_file();
	}
	function checkin_file() {
		$this->setTemplate('add_file');

		$file = ProjectFiles::findById(get_id());
		if(!($file instanceof ProjectFile)) {
			flash_error(lang('file dnx'));
			ajx_current("empty");
			return;
		} // if
			
		if(!$file->canEdit(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		} // if
			
		$file_data = array_var($_POST, 'file');
		if(!is_array($file_data)) {
			$tag_names = $file->getTagNames();
			$file_data = array(
			//'folder_id' => $file->getFolderId(),
				'description' => $file->getDescription(),
				'is_private' => $file->getIsPrivate(),
				'is_important' => $file->getIsImportant(),
				'comments_enabled' => $file->getCommentsEnabled(),
				'anonymous_comments_enabled' => $file->getAnonymousCommentsEnabled(),
				'tags' => is_array($tag_names) && count($tag_names) ? implode(', ', $tag_names) : '',
				'workspaces' => $file->getWorkspacesNamesCSV(logged_user()->getActiveProjectIdsCSV()),
			); // array
		} // if
		tpl_assign('file', $file);
		tpl_assign('file_data', $file_data);
		tpl_assign('checkin', true);
			
		if(is_array(array_var($_POST, 'file'))) {
			try {
				$old_is_private = $file->isPrivate();
				$old_is_important = $file->getIsImportant();
				$old_comments_enabled = $file->getCommentsEnabled();
				$old_anonymous_comments_enabled = $file->getAnonymousCommentsEnabled();
					
				DB::beginWork();
				$handle_file      = true; // change file?
				$post_revision    = $handle_file && array_var($file_data, 'version_file_change') == 'checked'; // post revision?
				$revision_comment = $post_revision ? trim(array_var($file_data, 'revision_comment')) : ''; // user comment?

				$file->setFromAttributes($file_data);
				$file->setFilename(array_var($file_data, 'name'));
				$file->checkIn();

				if(!logged_user()->isMemberOfOwnerCompany()) {
					$file->setIsPrivate($old_is_private);
					$file->setIsImportant($old_is_important);
					$file->setCommentsEnabled($old_comments_enabled);
					$file->setAnonymousCommentsEnabled($old_anonymous_comments_enabled);
				} // if
				$file->save();
				$file->setTagsFromCSV(array_var($file_data, 'tags'));
				if ($handle_file) {
					// handle uploaded file
					$upload_id = array_var($file_data, 'upload_id');
					$uploaded_file = array_var($_SESSION, $upload_id, array());
					$file->handleUploadedFile($uploaded_file, $post_revision, $revision_comment); // handle uploaded file
					@unlink($uploaded_file['tmp_name']);
				} // if

				$ws = $file->getWorkspaces();

				$object_controller = new ObjectController();
				$object_controller->link_to_new_object($file);
				$object_controller->add_subscribers($file);
				$object_controller->add_custom_properties($file);

				ApplicationLogs::createLog($file, $ws, ApplicationLogs::ACTION_EDIT);

				DB::commit();

				flash_success(lang('success add file', $file->getFilename()));
				ajx_current("back");
			} catch(Exception $e) {
				//@unlink($file->getFilePath());
				DB::rollback();
				flash_error($e->getMessage());
				ajx_current("empty");
			} // try
		} // if
	} // checkin_file

	/**
	 * Delete file
	 *
	 * @access public
	 * @param void
	 * @return null
	 */
	function delete_file() {
		$file = ProjectFiles::findById(get_id());
		if(!($file instanceof ProjectFile)) {
			flash_error(lang('file dnx'));
			ajx_current("empty");
			return;
		} // if
			
		if(!$file->canDelete(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		} // if
			
		try {
			DB::beginWork();
			$file->trash();

			$ws = $file->getWorkspaces();
			ApplicationLogs::createLog($file, $ws, ApplicationLogs::ACTION_TRASH);
			DB::commit();

			flash_success(lang('success delete file', $file->getFilename()));
			if (array_var($_POST, 'popup', false)) {
				ajx_current("reload");
			} else {
				ajx_current("back");
			}
			ajx_add("overview-panel", "reload");
		} catch(Exception $e) {
			flash_error(lang('error delete file'));
			ajx_current("empty");
		} // try
	}

	function check_filename(){
		ajx_current("empty");
		$filename = array_var($_POST, 'filename');
		$files = ProjectFiles::getAllByFilename($filename, logged_user()->getActiveProjectIdsCSV());

		if (is_array($files) && count($files) > 0){
			$files_array = array();

			foreach ($files as $file){
				if ($file->getId() != array_var($_GET, 'id')){
					$files_array[] = array(
						"id" => $file->getId(),
						"name" => $file->getFilename(),
						"description" => $file->getDescription(),
						"type" => $file->getTypeString(),
						"size" => $file->getFilesize(),
						"created_by_id" => $file->getCreatedById(),
						"created_by_name" => Users::findById($file->getCreatedById())->getDisplayName(),
						"created_on" => $file->getCreatedOn() instanceof DateTimeValue ? $file->getCreatedOn()->getTimestamp() : 0,
						"is_checked_out" => $file->isCheckedOut(),
						"checked_out_by_name" => $file->getCheckedOutByDisplayName(),
						"can_check_in" => $file->canCheckin(logged_user()),
						"can_edit" => $file->canEdit(logged_user()),
						"workspace_names" => $file->getWorkspacesNamesCSV(logged_user()->getActiveProjectIdsCSV()),
						"workspace_ids" => $file->getWorkspacesIdsCSV(logged_user()->getActiveProjectIdsCSV()),
						"workspace_colors" => $file->getWorkspaceColorsCSV(logged_user()->getActiveProjectIdsCSV()),
					);
				}
			}

			if (count($files_array) > 0){
				ajx_extra_data(array(
					"files" => $files_array
				));
			} else {
				ajx_extra_data(array(
					"id" => 0,
					"name" => $filename
				));
			}
		} else {
			ajx_extra_data(array(
				"id" => 0,
				"name" => $filename
			));
		}
	}

	function filenameExists($filename){
		$file = ProjectFiles::getByFilename($filename);
		return $file instanceof ProjectFile;
	}

	// ---------------------------------------------------
	//  Revisions
	// ---------------------------------------------------

	/**
	 * Update file revision (comment)
	 *
	 * @param void
	 * @return null
	 */
	function edit_file_revision() {
		$this->setTemplate('add_file_revision');
			
		$revision = ProjectFileRevisions::findById(get_id());
		if(!($revision instanceof ProjectFileRevision)) {
			flash_error(lang('file revision dnx'));
			ajx_current("empty");
			return;
		} // if
			
		$file = $revision->getFile();
		if(!($file instanceof ProjectFile)) {
			flash_error(lang('file dnx'));
			ajx_current("empty");
			return;
		} // if
			
		if(!$file->canDelete(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		} // if
			
		$revision_data = array_var($_POST, 'revision');
		if(!is_array($revision_data)) {
			$revision_data = array(
				'comment' => $revision->getComment(),
			); // array
		} // if
			
		tpl_assign('revision', $revision);
		tpl_assign('file', $file);
		tpl_assign('revision_data', $revision_data);
			
		if(is_array(array_var($_POST, 'revision'))) {
			try {
				DB::beginWork();
				$revision->setComment(array_var($revision_data, 'comment'));
				$revision->save();

				$ws = $revision->getWorkspaces();
				ApplicationLogs::createLog($revision, $ws, ApplicationLogs::ACTION_EDIT, $revision->isPrivate());

				DB::commit();

				flash_success(lang('success edit file revision'));
				ajx_current("back");
			} catch(Exception $e) {
				flash_error($e->getMessage());
				DB::rollback();
				ajx_current("empty");
			} // try
		} // if
	} // edit_file_revision

	/**
	 * Delete selected revision (if you have proper permissions)
	 *
	 * @param void
	 * @return null
	 */
	function delete_file_revision() {
		$revision = ProjectFileRevisions::findById(get_id());
		if(!($revision instanceof ProjectFileRevision)) {
			flash_error(lang('file revision dnx'));
			ajx_current("empty");
			return;
		} // if
			
		$file = $revision->getFile();
		if(!($file instanceof ProjectFile)) {
			flash_error(lang('file dnx'));
			ajx_current("empty");
			return;
		} // if
			
		$all_revisions = $file->getRevisions();
		if(count($all_revisions) == 1) {
			flash_error(lang('cant delete only revision'));
			ajx_current("empty");
			return;
		} // if
			
		if(!$file->canDelete(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		} // if
			
		try {
			DB::beginWork();
			$revision->trash();
			$ws = $revision->getWorkspaces();
			ApplicationLogs::createLog($revision, $ws, ApplicationLogs::ACTION_TRASH);
			DB::commit();

			flash_success(lang('success trash file revision'));
			ajx_current("reload");
		} catch(Exception $e) {
			DB::rollback();
			flash_error(lang('error trash object'));
			ajx_current("empty");
		} // try
	} // delete_file_revision

	/**
	 * Loads the logged user's mp3 files
	 *
	 */
	function get_mp3() {
		ajx_current("empty");

		/* get arguments */
		$project_id = array_var($_GET, 'active_project', 0);
		$project = Projects::findById($project_id);
		$tag = array_var($_GET, 'tag');
		$type = 'audio/mpeg';

		/* query */
		$files = ProjectFiles::getUserFiles(logged_user(), $project, $tag, $type,
		ProjectFiles::ORDER_BY_NAME, 'ASC');
		if (!is_array($files)) $files = array();

		/* prepare response object */
		$mp3 = array(
			'mp3' => array()
		);
		foreach ($files as $f) {
			$songname = $f->getProperty("songname");
			$artist = $f->getProperty("songartist");
			$album = $f->getProperty("songalbum");
			$track = $f->getProperty("songtrack");
			$year = $f->getProperty("songyear");
			$duration = $f->getProperty("songduration");
			$mp3["mp3"][] = array($songname, $artist, $album, $track, $year, $duration, $f->getDownloadUrl(), $f->getFilename(), $f->getId()
			);
		}
		ajx_extra_data($mp3);
	}

	function copy() {
		ajx_set_no_toolbar();
		$ws = active_or_personal_project();
		$id = get_id();
		$file = ProjectFiles::findById($id);
		if (!$file instanceof ProjectFile) {
			flash_error("file dnx");
			ajx_current("empty");
			return;
		}
		if (!can_read(logged_user(), $file)) {
			flash_error("no access permissions");
			ajx_current("empty");
			return;
		}
		if (!ProjectFile::canAdd(logged_user(), $ws)) {
			flash_error("no access permissions");
			ajx_current("empty");
			return;
		}
		try {
			DB::beginWork();
			$copy = $file->copy();
			$copy->setFilename(lang('copy of file', $file->getFilename()));
			$copy->save();
			$copy->addToWorkspace($ws);

			$rev_data = array();
			$rev_data['name'] = $copy->getFilename();
			$rev_data['size'] = $file->getFileSize();
			$rev_data['type'] = $file->getTypeString();
			$rev_data['tmp_name'] = './tmp/' . rand () ;
			$handler = fopen($rev_data['tmp_name'], 'w');
			$file_content = $file->getLastRevision()->getFileContent();
			fputs($handler, $file_content);
			fclose($handler);
			$copy->handleUploadedFile($rev_data, false, lang("copied from file", $file->getFilename(), $file->getUniqueObjectId()));
			DB::commit();

			$this->setTemplate('file_details');
			tpl_assign('file', $copy);
			tpl_assign('last_revision', $copy->getLastRevision());
			tpl_assign('revisions', $copy->getRevisions());
		} catch (Exception $ex) {
			DB::rollback();
			flash_error($ex->getMessage());
			ajx_current("empty");
		}
	}

	function zip_extract() {
		$fileId = array_var($_GET, 'id');
		ajx_current("empty");

		$file = ProjectFiles::findById($fileId);
		if (!$file->canEdit(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		}
		if (!$file) {
			flash_error(lang('file dnx'));
			ajx_current("empty");
		} else {
			session_commit();
			$content = $file->getLastRevision()->getFileContent();
			$filepath = ROOT.'/tmp/'.rand().'.zip';
			$handle = fopen($filepath, 'wb');
			fwrite($handle, $content);
			fclose($handle);

			$file_count = 0;
			$zip = new ZipArchive();
			if ($zip->open($filepath)) {
				$tmp_dir = ROOT.'/tmp/'.rand().'/';
				$zip->extractTo($tmp_dir);
				$i=0;
				while ($e_name = $zip->getNameIndex($i++)) {
					$tmp_path = $tmp_dir.$e_name;
					if (!is_dir($tmp_path)) {
						$workspaces = $file->getWorkspaces();
						$this->upload_file(null, $e_name, $tmp_path, $workspaces);
						$file_count++;
					}
				}
				$zip->close();
				delete_dir($tmp_dir);
			}
			unlink($filepath);
			ajx_current("reload");
			flash_success(lang('success extracting files', $file_count));
		}
	} // zip_extract

	private function upload_file($file, $filename, $path, $workspaces) {
		try {
			if ($file == null) {
				$file = new ProjectFile();
				$file->setFilename($filename);
				$file->setIsVisible(true);
				$file->setIsPrivate(false);
				$file->setIsImportant(false);
				$file->setCommentsEnabled(true);
				$file->setAnonymousCommentsEnabled(false);
				$file->setCreatedOn(new DateTimeValue(time()));
			}

			$handle = fopen($path, "r");
			$size = filesize($path);
			$content = fread($handle, $size);
			fclose($handle);

			$file_dt['name'] = $file->getFilename();
			$file_dt['size'] = strlen($content);
			$file_dt['tmp_name'] = $path;
			$extension = trim(get_file_extension($filename));
			$file_dt['type'] = Mime_Types::instance()->get_type($extension);

			if(!trim($file_dt['type'])) $file_dt['type'] = 'text/html';

			DB::beginWork();
			$file->save();
			if (is_array($workspaces)) {
				foreach ($workspaces as $ws) {
					$file->addToWorkspace($ws);
				}
			}
			$revision = $file->handleUploadedFile($file_dt, true, '');

			ApplicationLogs::createLog($file, $workspaces, ApplicationLogs::ACTION_ADD);
			DB::commit();
			return true;
		} catch (Exception $e) {
			DB::rollback();
			flash_error($e->getMessage());
			ajx_current("empty");
		}
		return false;
	} // upload_extracted_file

	function zip_add() {
		ajx_current("empty");

		$isnew = false;
		$file = null;
		if (array_var($_GET, 'filename')) {
			$filename = array_var($_GET, 'filename');
			$isnew = true;
		} else if (array_var($_GET, 'id')) {
			$file = ProjectFiles::findById(array_var($_GET, 'id'));
			$filename = $file->getFilename();
		}
		
		$tmp_zip_path = ROOT.'/tmp/'.rand().'.zip';
		$handle = fopen($tmp_zip_path, 'wb');
		if (!$isnew) {
			$content = $file->getLastRevision()->getFileContent();
			fwrite($handle, $content, $file->getLastRevision()->getFilesize());
		}
		fclose($handle);
		
		$zip = new ZipArchive();
		if (!$isnew) $zip->open($tmp_zip_path);
		else $zip->open($tmp_zip_path, ZipArchive::OVERWRITE);
		
		$new_file_ids = explode(',', array_var($_GET, 'objects'));
		$tmp_dir = ROOT.'/tmp/'.rand().'/';
		mkdir($tmp_dir);
		foreach ($new_file_ids as $id) {
			$file_to_add = ProjectFiles::findById($id);
			if ($file_to_add) {
				if (FileRepository::getBackend() instanceof FileRepository_Backend_FileSystem) {
					$file_to_add_path = FileRepository::getBackend()->getFilePath($file_to_add->getLastRevision()->getRepositoryId());
				} else {
					$file_to_add_path = $tmp_dir . $file_to_add->getFilename();
					$handle = fopen($file_to_add_path, 'wb');
					fwrite($handle, $file_to_add->getLastRevision()->getFileContent(), $file_to_add->getLastRevision()->getFilesize());
					fclose($handle);
				}
				$zip->addFile($file_to_add_path, $file_to_add->getFilename());
			}
		}
		$zip->close();
		delete_dir($tmp_dir);

		$workspaces = ($file == null ? array(active_or_personal_project()) : $file->getWorkspaces());
		$this->upload_file($file, $filename, $tmp_zip_path, $workspaces);
		unlink($tmp_zip_path);
		
		flash_success(lang('success compressing files'));
		ajx_current("reload");
	}
	
	function display_content() {
		$file = ProjectFiles::findById(get_id());
		if (!$file instanceof ProjectFile) {
			die(lang("file dnx"));
		}
		if (!$file->canView(logged_user())) {
			die(lang("no access permissions"));
		}
		
		$content = $file->getFileContent();
		$encoding = detect_encoding($content, array('UTF-8', 'ISO-8859-1', 'WINDOWS-1252'));
		
		header("Expires: " . gmdate("D, d M Y H:i:s", mktime(date("H") + 2, date("i"), date("s"), date("m"), date("d"), date("Y"))) . " GMT");
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
		header("Content-Type: " . $file->getTypeString() . ";charset=".$encoding);
		header("Content-Length: " . (string) $file->getFileSize());
		
		if ($file->getTypeString() == 'text/html') $content = purify_html($content);

		print($content);
		die();
	}

} // FilesController

?>