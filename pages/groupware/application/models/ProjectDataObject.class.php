<?php

/**
 * Abstract class that implements methods that share all project objects (tags manipulation,
 * retriving data about object creator etc)
 *
 * Project object is application object with few extra functions
 *
 * @version 1.0
 * @author Ilija Studen <ilija.studen@gmail.com>
 */
abstract class ProjectDataObject extends ApplicationDataObject {

	/**
	 * Cached parent project reference
	 *
	 * @var Project
	 */
	protected $project = null;

	/**
	 * Cached parent workspaces reference
	 *
	 * @var array
	 */
	protected $workspaces = null;


	// ---------------------------------------------------
	//  Tags
	// ---------------------------------------------------

	/**
	 * If true this object will not throw object not taggable exception and will make tag methods available
	 *
	 * @var boolean
	 */
	protected $is_taggable = false;

	public $tags;

	// ---------------------------------------------------
	//  Comments
	// ---------------------------------------------------

	/**
	 * Set this property to true if you want to let users post comments on this objects
	 *
	 * @var boolean
	 */
	protected $is_commentable = false;

	/**
	 * Cached array of all comments
	 *
	 * @var array
	 */
	protected $all_comments;

	/**
	 * Cached array of comments
	 *
	 * @var array
	 */
	protected $comments;

	/**
	 * Number of all comments
	 *
	 * @var integer
	 */
	protected $all_comments_count;

	/**
	 * Number of comments. If user is not member of owner company private comments
	 * will be excluded from the count
	 *
	 * @var integer
	 */
	protected $comments_count;

	// ---------------------------------------------------
	//  Timeslots
	// ---------------------------------------------------

	/**
	 * If true this object will not throw no timeslots allowed exception and will make timeslot methods available
	 *
	 * @var boolean
	 */
	protected $allow_timeslots = false;

	/**
	 * Cached array of timeslots
	 *
	 * @var array
	 */
	public $timeslots;

	/**
	 * Number of timeslots.
	 *
	 * @var integer
	 */
	public $timeslots_count;

	// ---------------------------------------------------
	//  General Methods
	// ---------------------------------------------------

	/**
	 * Whether the object can have properties
	 *
	 * @var bool
	 */
	protected $is_property_container = true;

	/**
	 * Return owner project. If project_id field does not exists NULL is returned
	 *
	 * @param void
	 * @return Project
	 */
	function getProject() {
		if($this->isNew() && function_exists('active_project')) {
			if(active_project())
				return active_project();
			else
				return personal_project();
		} // if

		if(is_null($this->project)) {
			if($this->columnExists('project_id')) {
				$this->project = Projects::findById($this->getProjectId());
			} else {
				if (Env::isDebugging()) {
					//Logger::log("WARNING: Calling getProject() on an object with multiple workspaces.");
				}
				return null;
			}
		} // if
		return $this->project;
	} // getProject

	/**
	 * Returns the object's workspaces
	 *
	 * @return array
	 */
	function getWorkspaces($wsIds = null) {
		if ($this->isNew()) {
			return array(active_or_personal_project());
		} else if (!$this->columnExists('project_id')) {
			//if (is_null($this->workspaces)){
				$this->workspaces = WorkspaceObjects::getWorkspacesByObject($this->getObjectManagerName(), $this->getObjectId());
			//}

			if (!is_null($wsIds)){
				$result = array();
				foreach($this->workspaces as $w){
					if ($this->isInCsv($w->getId(),$wsIds))
					$result[] = $w;
				}
				return $result;
			} else
				return $this->workspaces;
		} else {
			$project = $this->getProject();
			if (is_null($wsIds) || (!is_null($wsIds) && $project instanceof Project && $this->isInCsv($project->getId(), $wsIds)))
				return array($project);
			else
				return array();
		}
	}

	/**
	 * Returns the object's workspaces names separated by a comma
	 *
	 * @return unknown
	 */
	function getWorkspacesNamesCSV($wsIds = null, $user = null) {
		if ($this->isNew()) {
			return active_or_personal_project()->getName();
		} else {
			$ids = array();
			$wss = $this->getWorkspaces($wsIds);
			if($wsIds){
				foreach ($wss as $w) {
					$ids[] = $w->getName();
				}
			}
			return join(", ", $ids);
		}
	}

	function getWorkspacesIdsCSV($wsIds = null) {
		if ($this->isNew()) {
			return active_or_personal_project()->getId();
		} else {
			$ids = array();
			$wss = $this->getWorkspaces($wsIds);
			if($wss){
				foreach ($wss as $w) {
					$ids[] = $w->getId();
				}
			}
			return join(", ", $ids);
		}
	}

	function getWorkspaceColorsCSV($wsIds = null) {
		if ($this->isNew()) {
			return active_or_personal_project()->getColor();
		} else {
			$ids = array();
			$wss = $this->getWorkspaces($wsIds);
			if($wss){
				foreach ($wss as $w) {
					$ids[] = $w->getColor();
				}
			}
			return join(", ", $ids);
		}
	}
	
	function getUserWorkspaces($user = null) {
		if (!$user instanceof User) {
			$user = logged_user();
			if (!$user instanceof User) return;
		}
		$wsIds = $user->getActiveProjectIdsCSV();
		return $this->getWorkspaces($wsIds);
	}
	
	function getUserWorkspaceNames($user = null) {
		$workspaces = $this->getUserWorkspaces($user);
		$names = array();
		foreach ($workspaces as $w) {
			$names[] = $w->getName();
		}
		return $names;
	}
	
	function getUserWorkspacePaths($user = null) {
		$workspaces = $this->getUserWorkspaces($user);
		$paths = array();
		foreach ($workspaces as $w) {
			$paths[] = $w->getPath();
		}
		return $paths;
	}

	/**
	 * Returns true if the object is in workspace $w.
	 *
	 * @param Project $w
	 * @return boolean
	 */
	function hasWorkspace($w) {
		if ($this->columnExists("project_id")) {
			return $w->getId() == $this->getColumnValue("project_id");
		} else {
			$object_manager = $this->getObjectManagerName();
			$object_id = $this->getId();
			$workspace_id = $w->getId();
			$exists = WorkspaceObjects::findOne(array("conditions" => array("`workspace_id` = ? AND `object_manager` = ? AND `object_id` = ? ", $workspace_id, $object_manager, $object_id)));
			return $exists != null;
		}
	}

	/**
	 * Adds the object to workspace $w.
	 *
	 * @param Project $w
	 */
	function addToWorkspace($ws) {
		if (!$ws instanceof Project) return;
		if ($this->columnExists("project_id")) {
			$this->setColumnValue('project_id', $ws->getId());
			$this->save();
		} else if (!$this->hasWorkspace($ws)) {
			WorkspaceObjects::addObjectToWorkspace($this, $ws);
		}
	}

	/**
	 * Returns the object's manager's name.
	 *
	 * @return string
	 */
	function getObjectManagerName() {
		return get_class($this->manager());
	}

	/**
	 * Removes the object from workspace $w.
	 *
	 * @param Project $w
	 */
	function removeFromWorkspace($w) {
		WorkspaceObjects::delete(array("`workspace_id` = ? AND `object_manager` = ? AND `object_id` = ?", $w->getId(), $this->getObjectManagerName(), $this->getId()));
	}

	/**
	 * Remove from all workspaces.
	 *
	 */
	function removeFromAllWorkspaces() {
		WorkspaceObjects::delete(array("`object_manager` = ? AND `object_id` = ?", $this->getObjectManagerName(), $this->getId()));
	}

	function removeFromWorkspaces($wsCSV) {
		WorkspaceObjects::delete(array("`object_manager` = ? AND `object_id` = ? AND `workspace_id` in ($wsCSV)", $this->getObjectManagerName(), $this->getId()));
	}

	// ---------------------------------------------------
	//  Permissions
	// ---------------------------------------------------

	/**
	 * Can $user view this object
	 *
	 * @param User $user
	 * @return boolean
	 */
	abstract function canView(User $user);

	/**
	 * Check if this user can add a new object to this project. This method is called staticly
	 *
	 * @param User $user
	 * @param Project $project
	 * @return boolean
	 */
	abstract function canAdd(User $user, Project $project);

	/**
	 * Returns true if this user can edit this object
	 *
	 * @param User $user
	 * @return boolean
	 */
	abstract function canEdit(User $user);

	/**
	 * Returns true if this user can delete this object
	 *
	 * @param User $user
	 * @return boolean
	 */
	abstract function canDelete(User $user);

	/**
	 * Check if specific user can comment on this object
	 *
	 * @param User $user
	 * @return boolean
	 * @throws InvalidInstanceError if $user is not instance of User or AnonymousUser
	 */
	function canComment($user) {
		return self::checkCommentsPermissions($user, ProjectUsers::CAN_WRITE_COMMENTS);
	} // canComment
	
	/**
	 * Check if specific user can read comments of this object
	 *
	 * @param User $user
	 * @return boolean
	 * @throws InvalidInstanceError if $user is not instance of User or AnonymousUser
	 */
	function canReadComments($user) {
		return self::checkCommentsPermissions($user, ProjectUsers::CAN_READ_COMMENTS);
	} // canReadComments
	
	private function checkCommentsPermissions($user, $accesLevel) {
		if(!$this->isCommentable()) return false;

		if(!($user instanceof User) && !($user instanceof AnonymousUser)) {
			throw new InvalidInstanceError('user', $user, 'User or AnonymousUser');
		} // if

		// Access permissions
		if($user instanceof User) {
			if($user->isAdministrator()) return true; // admins have all the permissions
			$ws = $this->getWorkspaces();
			$can = false;
			foreach ($ws as $w) {
				if($user->isProjectUser($w) && $user->getProjectPermission($w, $accesLevel)) {
					$can = true;
				}
			}
			if (!$can) return false;
		} // if

		if($this->columnExists('comments_enabled') && !$this->getCommentsEnabled()) return false;
		if($user instanceof AnonymousUser) {
			if($this->columnExists('anonymous_comments_enabled') && !$this->getAnonymousCommentsEnabled()) return false;
		} // if
		return true;
	}

	/**
	 * Check if specific user can add a timeslot on this object
	 *
	 * @param User $user
	 * @return boolean
	 * @throws InvalidInstanceError if $user is not instance of User
	 */
	function canAddTimeslot($user) {
		if(!$this->allowsTimeslots()) return false;

		return $this->canEdit($user);
	} // canComment

	// ---------------------------------------------------
	//  Private
	// ---------------------------------------------------

	/**
	 * Returns true if this object is private, false otherwise
	 *
	 * @param void
	 * @return boolean
	 */
	function isPrivate() {
		if($this->columnExists('is_private')) {
			return $this->getIsPrivate();
		} else {
			return false;
		} // if
	} // isPrivate

	// ---------------------------------------------------
	//  Tags
	// ---------------------------------------------------

	/**
	 * Returns true if this project is taggable
	 *
	 * @param void
	 * @return boolean
	 */
	function isTaggable() {
		return $this->is_taggable;
	} // isTaggable

	/**
	 * Return tags for this object
	 *
	 * @param void
	 * @return array
	 */
	function getTags() {
		if(!$this->isTaggable()) throw new Error('Object not taggable');
		if (is_null($this->tags)) {
			$this->tags = Tags::getTagsByObject($this, get_class($this->manager()));
			if (is_null($this->tags)) $this->tags = array();
		}
		return $this->tags;
	} // getTags

	/**
	 * Return tag names for this object
	 *
	 * @access public
	 * @param void
	 * @return array
	 */
	function getTagNames() {
		if(!$this->isTaggable())
		return array();
		if (!is_null($this->tags))
		{
			$result = array();
			for($i = 0; $i < count($this->tags); $i++){
				$result[] = $this->tags[$i]->getTag();
			}
			return $result;
		}
		return Tags::getTagNamesByObject($this, get_class($this->manager()));
	} // getTagNames

	/**
	 * Delete tag for this object
	 *
	 * @access public
	 * @param void
	 * @return array
	 */
	function deleteTag( $tag) {
		if(!$this->isTaggable()) throw new Error('Object not taggable');
		$result = Tags::deleteObjectTag($tag, $this->getId(), get_class($this->manager()));
		if ($result)
			$this->tags = null; //Initializes tags cache
		return $result;
	} // deleteTag

	/**
	 * Explode input string and set array of tags
	 *
	 * @param string $input
	 * @return boolean
	 */
	function setTagsFromCSV($input) {
		$tag_names = array();
		if(trim($input)) {
			$tag_set = array();
			$tags = explode(',', $input);
			foreach($tags as $k => $v) {
				$tag = trim($v);
				if($tag <> '' && array_var($tag_set, $tag) == null) {
					$tag_names[] = $tag;
					$tag_set[$tag] = true;
				}
			} // foreach
		} // if
		return $this->setTags($tag_names);
	} // setTagsFromCSV

	/**
	 * Set object tags. This function accepts tags as params
	 *
	 * @access public
	 * @param void
	 * @return boolean
	 */
	function setTags() {
		if(!$this->isTaggable())
			throw new Error('Object not taggable');
		$args = array_flat(func_get_args());
		Tags::setObjectTags($args, $this, get_class($this->manager()));
		if ($this->isSearchable())
			$this->addTagsToSearchableObject();
		$this->tags = null; // Initializes tags cache
		return true;
	} // setTags

	/**
	 * Clear object tags
	 *
	 * @access public
	 * @param void
	 * @return boolean
	 */
	function clearTags() {
		if(!$this->isTaggable()) throw new Error('Object not taggable');
		$result = Tags::clearObjectTags($this, get_class($this->manager()));
		if ($result)
			$this->tags = null; // Initializes tags cache
		return $result;
	} // clearTags

	 
	// ---------------------------------------------------
	//  Commentable
	// ---------------------------------------------------

	/**
	 * Returns true if users can post comments on this object
	 *
	 * @param void
	 * @return boolean
	 */
	function isCommentable() {
		return (boolean) $this->is_commentable;
	} // isCommentable

	/**
	 * Attach comment to this object
	 *
	 * @param Comment $comment
	 * @return Comment
	 */
	function attachComment(Comment $comment) {
		$manager_class = get_class($this->manager());
		$object_id = $this->getObjectId();

		if(($object_id == $comment->getRelObjectId()) && ($manager_class == $comment->getRelObjectManager())) {
			return true;
		} // if

		$comment->setRelObjectId($object_id);
		$comment->setRelObjectManager($manager_class);

		$comment->save();
		return $comment;
	} // attachComment

	/**
	 * Return all comments
	 *
	 * @param void
	 * @return boolean
	 */
	function getAllComments() {
		if(is_null($this->all_comments)) {
			$this->all_comments = Comments::getCommentsByObject($this);
		} // if
		return $this->all_comments;
	} // getAllComments

	/**
	 * Return object comments, filter private comments if user is not member of owner company
	 *
	 * @param void
	 * @return array
	 */
	function getComments() {
		if(logged_user() && logged_user()->isMemberOfOwnerCompany()) {
			return $this->getAllComments();
		} // if
		if(is_null($this->comments)) {
			$this->comments = Comments::getCommentsByObject($this, true);
		} // if
		return $this->comments;
	} // getComments

	/**
	 * This function will return number of all comments
	 *
	 * @param void
	 * @return integer
	 */
	function countAllComments() {
		if(is_null($this->all_comments_count)) {
			$this->all_comments_count = Comments::countCommentsByObject($this);
		} // if
		return $this->all_comments_count;
	} // countAllComments

	/**
	 * Return total number of comments
	 *
	 * @param void
	 * @return integer
	 */
	function countComments() {
		if(logged_user()->isMemberOfOwnerCompany()) {
			return $this->countAllComments();
		} // if
		if(is_null($this->comments_count)) {
			$this->comments_count = Comments::countCommentsByObject($this, true);
		} // if
		return $this->comments_count;
	} // countComments

	/**
	 * Return # of specific object
	 *
	 * @param Comment $comment
	 * @return integer
	 */
	function getCommentNum(Comment $comment) {
		$comments = $this->getComments();
		if(is_array($comments)) {
			$counter = 0;
			foreach($comments as $object_comment) {
				$counter++;
				if($comment->getId() == $object_comment->getId()) return $counter;
			} // foreach
		} // if
		return 0;
	} // getCommentNum

	/**
	 * Returns true if this function has associated comments
	 *
	 * @param void
	 * @return boolean
	 */
	function hasComments() {
		return (boolean) $this->countComments();
	} // hasComments

	/**
	 * Clear object comments
	 *
	 * @param void
	 * @return boolean
	 */
	function clearComments() {
		return Comments::dropCommentsByObject($this);
	} // clearComments

	/**
	 * This event is triggered when we create a new comments
	 *
	 * @param Comment $comment
	 * @return boolean
	 */
	function onAddComment(Comment $comment) {
		if ($this->isSearchable()){
			$project = $this->getProject();
			$searchable_object = new SearchableObject();
			 
			$searchable_object->setRelObjectManager(get_class($this->manager()));
			$searchable_object->setRelObjectId($this->getObjectId());
			$searchable_object->setColumnName('comment' . $comment->getId());
			$searchable_object->setContent($comment->getText());
			if($project instanceof Project) $searchable_object->setProjectId($project->getId());
			$searchable_object->setIsPrivate($this->isPrivate());
			 
			$searchable_object->save();
			try {
				Notifier::newObjectComment($comment, $this->getSubscribers());
			} catch(Exception $e) {
				// nothing here, just suppress error...
			} // try
		}
		return true;
	} // onAddComment

	/**
	 * This event is trigered when comment that belongs to this object is updated
	 *
	 * @param Comment $comment
	 * @return boolean
	 */
	function onEditComment(Comment $comment) {
		if ($this->isSearchable()){
			SearchableObjects::dropContentByObjectColumn($this,'comment' . $comment->getId());
			$project = $this->getProject();
			$searchable_object = new SearchableObject();
			 
			$searchable_object->setRelObjectManager(get_class($this->manager()));
			$searchable_object->setRelObjectId($this->getObjectId());
			$searchable_object->setColumnName('comment' . $comment->getId());
			$searchable_object->setContent($comment->getText());
			if($project instanceof Project) $searchable_object->setProjectId($project->getId());
			$searchable_object->setIsPrivate($this->isPrivate());
			 
			$searchable_object->save();
		}
		return true;
	} // onEditComment

	/**
	 * This event is triggered when comment that belongs to this object is deleted
	 *
	 * @param Comment $comment
	 * @return boolean
	 */
	function onDeleteComment(Comment $comment) {
		if ($this->isSearchable())
		SearchableObjects::dropContentByObjectColumn($this,'comment' . $comment->getId());
	} // onDeleteComment

	/**
	 * Per object comments lock. If there is no `comments_enabled` column this
	 * function will return false
	 *
	 * @param void
	 * @return boolean
	 */
	function commentsEnabled() {
		return $this->columnExists('comments_enabled') ? (boolean) $this->getCommentsEnabled() : false;
	} // commentsEnabled

	/**
	 * This function will return true if anonymous users can post comments on
	 * this object. If column `anonymous_comments_enabled` does not exists this
	 * function will return true
	 *
	 * @param void
	 * @return boolean
	 */
	function anonymousCommentsEnabled() {
		return $this->columnExists('anonymous_comments_enabled') ? (boolean) $this->getAnonymousCommentsEnabled() : false;
	} // anonymousCommentsEnabled

	// ---------------------------------------------------
	//  Timeslots
	// ---------------------------------------------------

	function addTimeslot(User $user){
		$manager_class = get_class($this->manager());
		$object_id = $this->getObjectId();

		if ($this->hasOpenTimeslots($user))
			throw new Error("Cannot add timeslot: user already has an open timeslot");

		$timeslot = new Timeslot();

		$dt = DateTimeValueLib::now();
		$timeslot->setStartTime($dt);
		$timeslot->setUserId($user->getId());
		$timeslot->setObjectManager($manager_class);
		$timeslot->setObjectId($object_id);

		$timeslot->save();
	}

	function hasOpenTimeslots($user = null){
		$userCondition = '';
		if ($user)
			$userCondition = ' and `user_id` = '. $user->getId();

		return Timeslots::findOne(array(
          'conditions' => array('`object_id` = ? AND `object_manager` = ? AND end_time = \'' . EMPTY_DATETIME . '\''  . $userCondition, $this->getObjectId(), get_class($this->manager()))
		)) instanceof Timeslot;
	}

	function closeTimeslots(User $user, $description = ''){
		$manager_class = get_class($this->manager());
		$object_id = $this->getObjectId();

		$timeslots = Timeslots::findAll(array('conditions' => 'user_id = ' . $user->getId() . ' AND object_manager = "' . $manager_class . '" AND object_id = ' . $object_id . ' AND end_time = "' . EMPTY_DATETIME . '"'));

		foreach($timeslots as $timeslot){
			$timeslot->close($description);
			$timeslot->save();
		}
	}

	function pauseTimeslots(User $user){
		$manager_class = get_class($this->manager());
		$object_id = $this->getObjectId();

		$timeslots = Timeslots::findAll(array('conditions' => 'user_id = ' . $user->getId() . ' AND object_manager = "' . $manager_class . '" AND object_id = ' . $object_id . ' AND end_time = "' . EMPTY_DATETIME . '" AND paused_on = "' . EMPTY_DATETIME . '"'));

		if ($timeslots)
		foreach($timeslots as $timeslot){
			$timeslot->pause();
			$timeslot->save();
		}
	}

	function resumeTimeslots(User $user){
		$manager_class = get_class($this->manager());
		$object_id = $this->getObjectId();

		$timeslots = Timeslots::findAll(array('conditions' => 'user_id = ' . $user->getId() . ' AND object_manager = "' . $manager_class . '" AND object_id = ' . $object_id . ' AND end_time = "' . EMPTY_DATETIME . '" AND paused_on != "' . EMPTY_DATETIME . '"'));

		if ($timeslots)
		foreach($timeslots as $timeslot){
			$timeslot->resume();
			$timeslot->save();
		}
	}

	/**
	 * Returns true if users can assign timeslots on this object
	 *
	 * @param void
	 * @return boolean
	 */
	function allowsTimeslots() {
		return (boolean) $this->allow_timeslots;
	} // allowsTimeslots

	/**
	 * Attach timeslot to this object
	 *
	 * @param Timeslot $timeslot
	 * @return Timeslot
	 */
	function attachTimeslot(Timeslot $timeslot) {
		$manager_class = get_class($this->manager());
		$object_id = $this->getObjectId();

		if(($object_id == $timeslot->getObjectId()) && ($manager_class == $timeslot->getObjectManager())) {
			return true;
		} // if

		$timeslot->setObjectId($object_id);
		$timeslot->setObjectManager($manager_class);

		$timeslot->save();
		return $timeslot;
	} // attachComment

	/**
	 * Return all timeslots
	 *
	 * @param void
	 * @return boolean
	 */
	function getTimeslots() {
		if(is_null($this->timeslots)) {
			$this->timeslots = Timeslots::getTimeslotsByObject($this);
		} // if
		return $this->timeslots;
	} // getTimeslots

	/**
	 * This function will return number of timeslots
	 *
	 * @param void
	 * @return integer
	 */
	function countTimeslots() {
		if(is_null($this->timeslots_count)) {
			$this->timeslots_count = Timeslots::countTimeslotsByObject($this);
		} // if
		return $this->timeslots_count;
	} // countTimeslots

	/**
	 * Return # of specific timeslot
	 *
	 * @param Timeslot $timeslot
	 * @return integer
	 */
	function getTimeslotNum(Timeslot $timeslot) {
		$timeslots = $this->getTimeslots();
		if(is_array($timeslots)) {
			$counter = 0;
			foreach($timeslots as $object_timeslot) {
				$counter++;
				if($timeslot->getId() == $object_timeslot->getId()) return $counter;
			} // foreach
		} // if
		return 0;
	} // getTimeslotNum

	/**
	 * Returns true if this function has associated comments
	 *
	 * @param void
	 * @return boolean
	 */
	function hasTimeslots() {
		return (boolean) $this->countTimeslots();
	} // hasComments

	/**
	 * Clear object comments
	 *
	 * @param void
	 * @return boolean
	 */
	function clearTimeslots() {
		return Timeslots::dropTimeslotsByObject($this);
	} // clearComments

	/**
	 * This event is triggered when we create a new timeslot
	 *
	 * @param Timeslot $timeslot
	 * @return boolean
	 */
	function onAddTimeslot(Timeslot $timeslot) {
		 
		return true;
	} // onAddTimeslot

	/**
	 * This event is trigered when Timeslot that belongs to this object is updated
	 *
	 * @param Timeslot $timeslot
	 * @return boolean
	 */
	function onEditTimeslot(Timeslot $timeslot) {
		 
		return true;
	} // onEditTimeslot

	/**
	 * This event is triggered when timeslot that belongs to this object is deleted
	 *
	 * @param Timeslot $timeslot
	 * @return boolean
	 */
	function onDeleteTimeslot(Timeslot $timeslot) {

		return true;
	} // onDeleteTimeslot

	/**
	 * This function returns the total amount of minutes worked in this task
	 *
	 * @return integer
	 */
	//
	function getTotalMinutes(){
		$timeslots = $this->getTimeslots();
		$totalMinutes = 0;
		if (is_array($timeslots)){
			foreach ($timeslots as $ts){
				if (!$ts->isOpen())
				$totalMinutes += $ts->getMinutes();
			}
		}
		return $totalMinutes;
	}

	/**
	 * This function returns the total amount of seconds worked in this task
	 *
	 * @return integer
	 */

	function getTotalSeconds(){
		$timeslots = $this->getTimeslots();
		$totalMinutes = 0;
		if (is_array($timeslots)){
			foreach ($timeslots as $ts){
				if (!$ts->isOpen())
				$totalMinutes += $ts->getSeconds();
			}
		}
		return $totalMinutes;
	}

	// ---------------------------------------------------
	//  System
	// ---------------------------------------------------

	/**
	 * Save object. If object is searchable this function will add content of searchable fields
	 * to search index
	 *
	 * @param void
	 * @return boolean
	 */
	function save() {
		$disk_space_used = config_option('disk_space_used');
		if ($disk_space_used && $disk_space_used > config_option('disk_space_max')){
			throw new Exception(lang('maximum disk space reached'));
		}
		if (parent::save()) {
			/*try {
				$user = logged_user();
				if ($user instanceof User && $this->isCommentable()) {
					$this->subscribeUser($user);
				}
			} catch (Exception $e) {}*/
			return true;
		}
		return false;
	} // save

	function addToSearchableObjects($wasNew){
		$columns_to_drop = array();
		if ($wasNew || ($this->columnExists('project_id') && $this->isColumnModified('project_id')))
			$columns_to_drop = $this->getSearchableColumns();
		else {
			foreach ($this->getSearchableColumns() as $column_name){
				if ($this->isColumnModified($column_name))
				$columns_to_drop[] = $column_name;
			}
		}
		 
		if (count($columns_to_drop) > 0){
			SearchableObjects::dropContentByObjectColumns($this,$columns_to_drop);

			foreach($columns_to_drop as $column_name) {
				$content = $this->getSearchableColumnContent($column_name);
				if(trim($content) <> '') {
					$searchable_object = new SearchableObject();
					 
					$searchable_object->setRelObjectManager(get_class($this->manager()));
					$searchable_object->setRelObjectId($this->getObjectId());
					$searchable_object->setColumnName($column_name);
					$searchable_object->setContent($content);
					if($this->getProject() instanceof Project)
						$searchable_object->setProjectId($this->getProject()->getId());
					else
						$searchable_object->setProjectId(0);
					$searchable_object->setIsPrivate(false);
					 
					$searchable_object->save();
				} // if
			} // foreach
		} // if
		 
		//Add Unique ID to search
		if ($wasNew || ($this->columnExists('project_id') && $this->isColumnModified('project_id'))){
			SearchableObjects::dropContentByObjectColumns($this,array('uid'));
			$searchable_object = new SearchableObject();

			$searchable_object->setRelObjectManager(get_class($this->manager()));
			$searchable_object->setRelObjectId($this->getObjectId());
			$searchable_object->setColumnName('uid');
			$searchable_object->setContent($this->getUniqueObjectId());
			if($this->getProject() instanceof Project)
				$searchable_object->setProjectId($this->getProject()->getId());
			else
				$searchable_object->setProjectId(0);
			$searchable_object->setIsPrivate(false);

			$searchable_object->save();
		}
	}

	function addPropertyToSearchableObject(ObjectProperty $property){
		$searchable_object = new SearchableObject();
		 
		$searchable_object->setRelObjectManager(get_class($this->manager()));
		$searchable_object->setRelObjectId($this->getObjectId());
		$searchable_object->setColumnName('property'.$property->getId());
		$searchable_object->setContent($property->getPropertyValue());
		if($this->getProject() instanceof Project)
		$searchable_object->setProjectId($this->getProject()->getId());
		else
		$searchable_object->setProjectId(0);
		$searchable_object->setIsPrivate(false);
	  
		$searchable_object->save();
	}

	function addTagsToSearchableObject(){
		$tag_names = $this->getTagNames();
		 
		if (is_array($tag_names) && count($tag_names) > 0){
			if (!$this->isNew())
				SearchableObjects::dropContentByObjectColumn($this,'tags');

			$searchable_object = new SearchableObject();

			$searchable_object->setRelObjectManager(get_class($this->manager()));
			$searchable_object->setRelObjectId($this->getObjectId());
			$searchable_object->setColumnName('tags');
			$searchable_object->setContent(implode(' ', $tag_names));
			if($this->getProject() instanceof Project)
				$searchable_object->setProjectId($this->getProject()->getId());
			else
				$searchable_object->setProjectId(0);
			$searchable_object->setIsPrivate($this->isPrivate());
			 
			$searchable_object->save();
		}
	}

	/**
	 * Delete object and drop content from search table
	 *
	 * @param void
	 * @return boolean
	 */
	function delete() {
		if($this->isTaggable()) {
			$this->clearTags();
		} // if
		if($this->isCommentable()) {
			$this->clearComments();
		} // if
		if($this->isPropertyContainer()){
			$this->clearObjectProperties();
		}
		$this->removeFromCOTemplates();
		$this->clearSubscriptions();
		$this->clearReminders();
		if ($this->allowsTimeslots())
			$this->clearTimeslots();
		WorkspaceObjects::delete(array("`object_manager` = ? AND `object_id` = ?", $this->getObjectManagerName(), $this->getId()));
		SharedObjects::delete(array("`object_manager` = ? AND `object_id` = ?", $this->getObjectManagerName(), $this->getId()));
		ObjectUserPermissions::delete(array("`rel_object_manager` = ? AND `rel_object_id` = ?", $this->getObjectManagerName(), $this->getId()));
		return parent::delete();
	} // delete

	function trash($trashDate = null) {
		if(!isset($trashDate))
			$trashDate = DateTimeValueLib::now();
		if ($this->columnExists('trashed_on')) {
			$this->setColumnValue('trashed_on', $trashDate);
		}
		if (function_exists('logged_user') && logged_user() instanceof User &&
				$this->columnExists('trashed_by_id')) {
			$this->setColumnValue('trashed_by_id', logged_user()->getId());
		}
		$this->save();
	}
	
	function untrash() {
		if ($this->columnExists('trashed_on')) {
			$this->setColumnValue('trashed_on', EMPTY_DATETIME);
		}
		if ($this->columnExists('trashed_by_id')) {
			$this->setColumnValue('trashed_by_id', 0);
		}
		$this->save();
	}
	
	function isTrashable() {
		return $this->columnExists('trashed_by_id');
	}
	
	function isTrashed() {
		if (!$this->isTrashable()) return false;
		return $this->getColumnValue('trashed_by_id') != 0;
	}
	
	function getTrashUrl() {
		return get_url('object', 'trash', array(
			'object_id' => $this->getId(),
			'manager' => get_class($this->manager())
		));
	}
	
	function getUntrashUrl() {
		return get_url('object', 'untrash', array(
			'object_id' => $this->getId(),
			'manager' => get_class($this->manager())
		));
	}
	
	function getDeletePermanentlyUrl() {
		return get_url('object', 'delete_permanently', array(
			'object_id' => $this->getId(),
			'manager' => get_class($this->manager())
		));
	}

	function getDashboardObject(){
		if($this->getUpdatedById()){
			$updated_by_id = $this->getUpdatedById();
			$updated_by_name = $this->getUpdatedByDisplayName();
			$updated_on=($this->getObjectUpdateTime())?$this->getObjectUpdateTime()->getTimestamp(): lang('n/a');
		}else {
			if($this->getCreatedById())
			$updated_by_id = $this->getCreatedById();
			else
			$updated_by_id = lang('n/a');
			$updated_by_name = $this->getCreatedByDisplayName();
			$updated_on =($this->getObjectCreationTime())? $this->getObjectCreationTime()->getTimestamp(): lang('n/a');
		}
		
		$deletedOn = $this->getTrashedOn() ? $this->getTrashedOn()->getTimestamp() : lang('n/a');
    	$deletedBy = Users::findById($this->getTrashedById());
    	if ($deletedBy instanceof User) {
    		$deletedBy = $deletedBy->getDisplayName();
    	} else {
    		$deletedBy = lang("n/a");
    	}
		 
		return array(
				"id" => $this->getObjectTypeName() . $this->getId(),
				"object_id" => $this->getId(),
				"name" => $this->getObjectName(),
				"type" => $this->getObjectTypeName(),
				"tags" => project_object_tags($this),
				"createdBy" => $this->getCreatedByDisplayName(),
				"createdById" => $this->getCreatedById(),
				"dateCreated" => ($this->getObjectCreationTime())?$this->getObjectCreationTime()->getTimestamp():lang('n/a'),
				"updatedBy" => $updated_by_name,
				"updatedById" => $updated_by_id,
				"dateUpdated" => $updated_on,
				"wsIds" => $this->getWorkspacesIdsCSV(logged_user()->getActiveProjectIdsCSV()),
				"url" => $this->getObjectUrl(),
				"manager" => get_class($this->manager()),
				"deletedById" => $this->getTrashedById(),
    			"deletedBy" => $deletedBy,
    			"dateDeleted" => $deletedOn
		);
	}

	// ---------------------------------------------------
	//  Subscriptions
	// ---------------------------------------------------

	/**
	 * Cached array of subscribers
	 *
	 * @var array
	 */
	private $subscribers;

	/**
	 * Return array of subscribers
	 *
	 * @param void
	 * @return array
	 */
	function getSubscribers() {
		if(is_null($this->subscribers)) $this->subscribers = ObjectSubscriptions::getUsersByObject($this);
		return $this->subscribers;
	} // getSubscribers

	/**
	 * Check if specific user is subscriber
	 *
	 * @param User $user
	 * @return boolean
	 */
	function isSubscriber(User $user) {
		if ($this->isNew()) return false;
		$subscription = ObjectSubscriptions::findById(array(
        	'object_id' => $this->getId(),
			'object_manager' => get_class($this->manager()),
        	'user_id' => $user->getId()
		)); // findById
		return $subscription instanceof ObjectSubscription;
	} // isSubscriber

	/**
	 * Subscribe specific user to this message
	 *
	 * @param User $user
	 * @return boolean
	 */
	function subscribeUser(User $user) {
		if($this->isNew()) {
			throw new Error('Can\'t subscribe user to object that is not saved');
		} // if
		if($this->isSubscriber($user)) {
			return true;
		} // if

		// New subscription
		$subscription = new ObjectSubscription();
		$subscription->setObjectId($this->getId());
		$subscription->setObjectManager(get_class($this->manager()));
		$subscription->setUserId($user->getId());
		return $subscription->save();
	} // subscribeUser

	/**
	 * Unsubscribe user
	 *
	 * @param User $user
	 * @return boolean
	 */
	function unsubscribeUser(User $user) {
		$subscription = ObjectSubscriptions::findById(array(
        'object_id' => $this->getId(),
		'object_manager' => get_class($this->manager()),
        'user_id' => $user->getId()
		)); // findById
		if($subscription instanceof ObjectSubscription) {
			return $subscription->delete();
		} else {
			return true;
		} // if
	} // unsubscribeUser

	/**
	 * Clear all object subscriptions
	 *
	 * @param void
	 * @return boolean
	 */
	function clearSubscriptions() {
		return ObjectSubscriptions::clearByObject($this);
	} // clearSubscriptions

	function clearReminders($user = null, $include_subscribers = false) {
		if (isset($user)) {
			return ObjectReminders::clearByObjectAndUser($this, $user, $include_subscribers);
		} else {
			return ObjectReminders::clearByObject($this);
		}
	}

	/**
	 * Return subscribe URL
	 *
	 * @param void
	 * @return boolean
	 */
	function getSubscribeUrl() {
		return get_url('object', 'subscribe', array(
			'id' => $this->getId(),
			'manager' => get_class($this->manager())
		));
	} // getSubscribeUrl

	/**
	 * Return unsubscribe URL
	 *
	 * @param void
	 * @return boolean
	 */
	function getUnsubscribeUrl() {
		return get_url('object', 'unsubscribe', array(
			'id' => $this->getId(),
			'manager' => get_class($this->manager())
		));
	} // getUnsubscribeUrl
	
	// TEMPLATES
	
	/**
	 * Returns true if the object can be set as a template
	 *
	 * @return boolean
	 */
	function canBeTemplate() {
		return $this->columnExists("is_template");
	}
	
	/**
	 * Returns true if the object is a template
	 * 
	 * @return boolean
	 */
	function isTemplate() {
		if (!$this->canBeTemplate()) return false;
		return $this->getColumnValue("is_template");
	}
	
	
	/**
	 * Removes this object from COTemplate objects
	 *
	 */
	function removeFromCOTemplates() {
		TemplateObjects::removeObjectFromTemplates($this);
	}
	
	
	// ---------------------------------------------------
	//  Sharing
	// ---------------------------------------------------
	
	function getShareUrl() {
		return get_url('object', 'share', array(
			'object_id' => $this->getId(),
			'manager' => get_class($this->manager())
		));
	}

} // ProjectDataObject

?>
