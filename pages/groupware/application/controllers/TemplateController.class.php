<?php

class TemplateController extends ApplicationController {

	function __construct() {
		parent::__construct();
		prepare_company_website_controller($this, 'website');
		ajx_set_panel("administration");

		// Access permissios
		if(!logged_user()->isCompanyAdmin(owner_company())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
		} // if
	}

	function index() {
		if (!can_manage_templates(logged_user())) {
			flash_error(lang("no access permissions"));
			ajx_current("empty");
			return;
		}
		tpl_assign('templates', COTemplates::findAll());
	}

	function add() {
		if (!logged_user()->getCanManageTemplates()) {
			flash_error(lang("no access permissions"));
			ajx_current("empty");
			return;
		}
		$template = new COTemplate();
		$template_data = array_var($_POST, 'template');
		if (!is_array($template_data)) {
			$template_data = array(
				'name' => '',
				'description' => ''
				);
		} else {
			$cotemplate = new COTemplate();
			$cotemplate->setFromAttributes($template_data);
			try {
				DB::beginWork();
				$cotemplate->save();
				$objects = array_var($_POST, 'objects');
				foreach ($objects as $objid) {
					$split = split(":", $objid);
					$object = get_object_by_manager_and_id($split[1], $split[0]);
					$cotemplate->addObject($object);
				}
				$wss = Projects::findByCSVIds(array_var($_POST, "ws_ids"));
				WorkspaceTemplates::deleteByTemplate($cotemplate->getId());
				foreach ($wss as $ws){
					$obj = new WorkspaceTemplate();
					$obj->setWorkspaceId($ws->getId());
					$obj->setTemplateId($cotemplate->getId());
					$obj->setInludeSubWs(false);
					$obj->save();
				}
				DB::commit();
				ApplicationLogs::createLog($cotemplate, null, ApplicationLogs::ACTION_ADD);
				flash_success(lang("success add template"));
				if (array_var($_POST, "add_to")) {
					ajx_current("start");
				} else {
					ajx_current("back");
				}
			} catch (Exception $e) {
				DB::rollback();
				flash_error($e->getMessage());
				ajx_current("empty");
			}
		}
		$objects = array();
		if (array_var($_GET, 'id')) {
			$object = get_object_by_manager_and_id(array_var($_GET, 'id'), array_var($_GET, 'manager'));
			if ($object instanceof ProjectDataObject) {
				$objects[] = $object;
			}
		}
		tpl_assign('add_to', true);
		tpl_assign('objects', $objects);
		tpl_assign('cotemplate', $template);
		tpl_assign('template_data', $template_data);
	}

	function edit() {
		$this->setTemplate('add');

		$cotemplate = COTemplates::findById(get_id());
		if(!($cotemplate instanceof COTemplate)) {
			flash_error(lang('template dnx'));
			ajx_current("empty");
			return;
		} // if

		if(!$cotemplate->canEdit(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		} // if

		$template_data = array_var($_POST, 'template');
		if(!is_array($template_data)) {
			$template_data = array(
				'name' => $cotemplate->getName(),
				'description' => $cotemplate->getDescription(),
			); // array
		} else {
			$cotemplate->setFromAttributes($template_data);
			try {
				DB::beginWork();
				$cotemplate->save();
				$cotemplate->removeObjects();
				$objects = array_var($_POST, 'objects');
				foreach ($objects as $objid) {
					$split = split(":", $objid);
					$object = get_object_by_manager_and_id($split[1], $split[0]);
					$cotemplate->addObject($object);
				}
				$wss = Projects::findByCSVIds(array_var($_POST, "ws_ids"));
				WorkspaceTemplates::deleteByTemplate($cotemplate->getId());
				foreach ($wss as $ws){
					$obj = new WorkspaceTemplate();
					$obj->setWorkspaceId($ws->getId());
					$obj->setTemplateId($cotemplate->getId());
					$obj->setInludeSubWs(false);
					$obj->save();
				}
				DB::commit();
				ApplicationLogs::createLog($cotemplate, null, ApplicationLogs::ACTION_EDIT);
				flash_success(lang("success edit template"));
				ajx_current("back");
			} catch (Exception $e) {
				DB::rollback();
				flash_error($e->getMessage());
				ajx_current("empty");
			}
		}
		tpl_assign('objects', $cotemplate->getObjects());
		tpl_assign('cotemplate', $cotemplate);
		tpl_assign('template_data', $template_data);
	}

	function view() {
		$cotemplate = COTemplates::findById(get_id());
		if(!($cotemplate instanceof COTemplate)) {
			flash_error(lang('template dnx'));
			ajx_current("empty");
			return;
		} // if

		if(!$cotemplate->canView(logged_user())) {
			flash_error(lang('no access permissions'));
			ajx_current("empty");
			return;
		} // if

		tpl_assign('cotemplate', $cotemplate);
		ajx_set_no_toolbar(true);
	}

	function delete() {
		ajx_current("empty");
		$cotemplate = COTemplates::findById(get_id());
		if(!($cotemplate instanceof COTemplate)) {
			flash_error(lang('template dnx'));
			return;
		} // if

		if(!$cotemplate->canDelete(logged_user())) {
			flash_error(lang('no access permissions'));
			return;
		} // if

		try {
			DB::beginWork();
			$cotemplate->delete();
			ApplicationLogs::createLog($cotemplate, null, ApplicationLogs::ACTION_DELETE);
			DB::commit();
			flash_success(lang('success delete template', $cotemplate->getName()));
			if (array_var($_GET, 'popup', false)) {
				ajx_current("reload");
			} else {
				ajx_current("back");
			}
		} catch(Exception $e) {
			DB::rollback();
			flash_error($e->getMessage());
		} // try
	}

	function add_to() {
		$manager = array_var($_GET, 'manager');
		$id = get_id();
		$object = get_object_by_manager_and_id($id, $manager);
		$template_id = array_var($_GET, 'template');
		if ($template_id) {
			$template = COTemplates::findById($template_id);
			if ($template instanceof COTemplate) {
				try {
					DB::beginWork();
					$template->addObject($object);
					DB::commit();
					flash_success(lang('success add object to template'));
					ajx_current("start");
				} catch(Exception $e) {
					DB::rollback();
					flash_error($e->getMessage());
				}
			}
		}
		tpl_assign('templates', COTemplates::findAll());
		tpl_assign("object", $object);
	}

	function instantiate() {
		$id = get_id();
		$template = COTemplates::findById($id);
		if (!$template instanceof COTemplate) {
			flash_error(lang("template dnx"));
			ajx_current("empty");
			return;
		}

		$objects = $template->getObjects();
		foreach ($objects as $object) {
			if (!$object instanceof ProjectDataObject) continue;
			$copy = $object->copy();
			if ($copy->columnExists('is_template')) {
				$copy->setColumnValue('is_template', false);
			}
			if ($copy instanceof ProjectTask) $copy->setMilestoneId(0);
			$copy->save();
			$copy->addToWorkspace(active_or_personal_project());
			if ($copy instanceof ProjectTask) {
				ProjectTasks::copySubTasks($object, $copy, false);
			} else if ($copy instanceof ProjectMilestone) {
				ProjectMilestones::copyTasks($object, $copy, false);
			}
			$copy->copyCustomPropertiesFrom($object);
		}
		ajx_current("reload");
	}

	function assign_to_ws() {
		$template_id = get_id();
		$cotemplate = COTemplates::findById($template_id);
		if (!$cotemplate instanceof COTemplate) {
			flash_error(lang("template dnx"));
			ajx_current("empty");
			return;
		}
		$selected = WorkspaceTemplates::getWorkspacesByTemplate($template_id);
		tpl_assign('workspaces', logged_user()->getWorkspaces());
		tpl_assign('selected', $selected);
		tpl_assign('cotemplate', $cotemplate);
		$checked = array_var($_POST, 'ws_ids');
		if ($checked != null) {
			try {
				DB::beginWork();
				WorkspaceTemplates::deleteByTemplate($template_id);
				$wss = Projects::findByCSVIds($checked);
				foreach ($wss as $ws){
					$obj = new WorkspaceTemplate();
					$obj->setWorkspaceId($ws->getId());
					$obj->setTemplateId($template_id);
					$obj->setInludeSubWs(false);
					$obj->save();
				}
				DB::commit();
				flash_success(lang('success assign workspaces'));
				ajx_current("back");
			}
			catch (Exception $exc){
				flash_error(lang('error assign workspace') . $exc->getMessage());
				ajx_current("empty");
			}
		}
	}
}

?>