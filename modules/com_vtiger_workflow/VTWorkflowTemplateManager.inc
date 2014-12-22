<?php
/*+********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ********************************************************************************/

class VTWorkflowTemplateManager{
	public function __construct($adb){
		$this->adb = $adb;
	}
	
	/**
	 * Create anew template instance from a workflow
	 *
	 * This template instance will not be saved. The save 
	 * will have to be done explicitly.
	 *
	 * @param $title The title of the template
	 * @param $workflow A workflow instance.
	 */
	public function newTemplate($title, $workflow){
		$adb = $this->adb;
		$wms = new VTWorkflowManager($adb);
		$str = $wms->serializeWorkflow($workflow);
		$template = new VTWorkflowTemplate();
		$template->title = $title;
		$template->moduleName = $workflow->moduleName;
		$template->template = $str;
		return $template;
	}
	
	/**
	 * Retrieve a template given it's id
	 *
	 * @param $templateId The id of the template
	 * @return The template object
	 */
	public function retrieveTemplate($templateId){
		$adb = $this->adb;
		$result = $adb->pquery('select * from com_vtiger_workflowtemplates where template_id=?', array($templateId));
		$it = new SqlResultIterator($adb, $result);
		$data = $it->current();
		$template = new VTWorkflowTemplate();
		$template->id = $templateId;
		$template->title = $data->title;
		$template->moduleName = $data->module_name;
		$template->template = $data->template;
		return $template;
	}

	/**
	 * Create a workflow from a template
	 *
	 * The new workflow will also be added to the database.
	 *
	 * @param $template The template to use
	 * @return A workflow object.
	 */
	public function createWorkflow($template){
		$adb = $this->adb;
		$wfm = new VTWorkflowManager($adb);
		return $wfm->deserializeWorkflow($template->template);
	}

	/**
	 * Get template objects for a particular module.
	 *
	 * @param $moduleName The name of the module
	 * @return An array containing template objects
	 */
	public function getTemplatesForModule($moduleName){
		$adb = $this->adb;
		$result = $adb->pquery("select * from com_vtiger_workflowtemplates where module_name=?", array($moduleName));
		return $this->getTemplatesForResult($result);
	}

	/**
	 * Get all templates
	 * 
	 * Get all the templates as an array
	 *
	 * @return An array containing template objects.
	 */
	public function getTemplates(){
		$adb = $this->adb;
		$result = $adb->query("select * from com_vtiger_workflowtemplates");
		return $this->getTemplatesForResult($result);
	}

	/**
	 * Save a template
	 *
	 * If the object is a newly created template it
	 * will be added to the database and a field id containing 
	 * the new id will be added to the object.
	 *
	 * @param $template The template object to save.
	 */
	public function saveTemplate($template){
		$adb = $this->adb;
		if(is_numeric($template->id)){//How do I check whether a member exists in php?
				$templateId = $template->id;
				$adb->pquery("update com_vtiger_workflowtemplates set title=?,"+
										 " module_name=?, template=? where template_id=?", 
										 array($template->title, $template->moduleName, 
													 $template->template, $templateId));	
				return $templateId;
			}else{
				$templateId = $adb->getUniqueID("com_vtiger_workflowtemplates");
				$template->id = $templateId;
				$adb->pquery("insert into com_vtiger_workflowtemplates 
							(template_id, title, module_name, template) 
							values (?, ?, ?, ?)", 
					array($templateId, $template->title, 
								$template->moduleName, $template->template));
				return $templateId;
			}
	}

	/**
	 * Delete a template
	 *
	 * $templateId The id of the template to delete.
	 */
	public function deleteTemplate($templateId){
		$adb = $this->adb;
		$adb->pquery('delete from com_vtiger_workflowtemplates where template_id=?',
								 array($templateId));
	}
	
	
	/**
	 * Dump all the templates in vtiger into a string
	 *
	 * This can be used for exporting templates from one 
	 * machine to another
	 *
	 * @return The string dump of the templates.
	 */
	public function dumpAllTemplates(){
		$adb = $this->adb;
		$result = $adb->query("select * from com_vtiger_workflowtemplates");
		$it = new SqlResultIterator($adb, $result);
		$arr = array();
		foreach($it as $row){
			$el = array(
				'moduleName'=>$row->module_name,
				'title'=>$row->title,
				'template'=>$row->template
			);
			$arr[] = $el;
		}
		return Zend_Json::encode($arr);
	}
	
	/**
	 * Load templates form a dumped string
	 *
	 * @param $str The string dump generated from dumpAllTemplates
	 */
	public function loadTemplates($str){
			$arr = Zend_Json::decode($str);
			foreach($arr as $el){
				$template = new VTWorkflowTemplate();
				$template->moduleName = $el['moduleName'];
				$template->title = $el['title'];
				$template->template = $el['template'];
				$this->save($template);
				$this->createWorkflow($template);
			}
	}


	private function getTemplatesForResult($result){
		$adb = $this->adb;
		$it = new SqlResultIterator($adb, $result);
		$templates = array();
		foreach($it as $row){
			$template = new VTWorkflowTemplate();
			$template->id = $row->template_id;
			$template->title = $row->title;
			$tempalte->moduleName = $row->module_name;
			$template->template = $row->template;
			$templates[] = $template;
		}
		return $templates;
	}
}

class VTWorkflowTemplate{
	
}
?>