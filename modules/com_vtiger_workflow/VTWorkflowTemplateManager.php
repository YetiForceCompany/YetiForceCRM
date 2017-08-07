<?php
/* +********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ****************************************************************************** */

class VTWorkflowTemplateManager
{

	/**
	 * Create anew template instance from a workflow
	 *
	 * This template instance will not be saved. The save
	 * will have to be done explicitly.
	 *
	 * @param $title The title of the template
	 * @param $workflow A workflow instance.
	 */
	public function newTemplate($title, $workflow)
	{
		$wms = new VTWorkflowManager();
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
	public function retrieveTemplate($templateId)
	{
		$data = (new \App\Db\Query())->from('com_vtiger_workflowtemplates')->where(['template_id' => $templateId])->one();
		$template = new VTWorkflowTemplate();
		$template->id = $templateId;
		$template->title = $data['title'];
		$template->moduleName = $data['module_name'];
		$template->template = $data['template'];
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
	public function createWorkflow($template)
	{
		$wfm = new VTWorkflowManager();
		return $wfm->deserializeWorkflow($template->template);
	}

	/**
	 * Get template objects for a particular module.
	 *
	 * @param $moduleName The name of the module
	 * @return An array containing template objects
	 */
	public function getTemplatesForModule($moduleName)
	{
		$data = (new \App\Db\Query())->from('com_vtiger_workflowtemplates')->where(['module_name' => $moduleName])->all();
		return $this->getTemplatesForResult($data);
	}

	/**
	 * Get all templates
	 *
	 * Get all the templates as an array
	 *
	 * @return An array containing template objects.
	 */
	public function getTemplates()
	{
		$data = (new \App\Db\Query())->from('com_vtiger_workflowtemplates')->all();
		return $this->getTemplatesForResult($data);
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
	public function saveTemplate($template)
	{
		$db = \App\Db::getInstance();
		$dbCommand = \App\Db::getInstance()->createCommand();
		if (is_numeric($template->id)) {//How do I check whether a member exists in php?
			$templateId = $template->id;
			$dbCommand->createCommand()
				->update('com_vtiger_workflowtemplates', [
					'title' => $template->title,
					'module_name' => $template->moduleName,
					'template' => $template->template
					], ['template_id' => $templateId])
				->execute();
			return $templateId;
		} else {
			$templateId = $db->getUniqueID("com_vtiger_workflowtemplates");
			$template->id = $templateId;
			$dbCommand->insert('com_vtiger_workflowtemplates', [
				'template_id' => $templateId,
				'title' => $template->title,
				'module_name' => $template->moduleName,
				'template' => $template->template
			])->execute();
			return $templateId;
		}
	}

	/**
	 * Delete a template
	 *
	 * $templateId The id of the template to delete.
	 */
	public function deleteTemplate($templateId)
	{
		\App\Db::getInstance()->createCommand()->delete('com_vtiger_workflowtemplates', ['template_id' => $templateId])->execute();
	}

	/**
	 * Dump all the templates in vtiger into a string
	 *
	 * This can be used for exporting templates from one
	 * machine to another
	 *
	 * @return The string dump of the templates.
	 */
	public function dumpAllTemplates()
	{
		$query = (new \App\Db\Query())->from('com_vtiger_workflowtemplates');
		$arr = [];
		$dataReader = $query->createCommand()->query();
		while ($row = $dataReader->read()) {
			$arr[] = [
				'moduleName' => $row['module_name'],
				'title' => $row['title'],
				'template' => $row['template']
			];
		}
		return \App\Json::encode($arr);
	}

	/**
	 * Load templates form a dumped string
	 *
	 * @param $str The string dump generated from dumpAllTemplates
	 */
	public function loadTemplates($str)
	{
		$arr = \App\Json::decode($str);
		foreach ($arr as $el) {
			$template = new VTWorkflowTemplate();
			$template->moduleName = $el['moduleName'];
			$template->title = $el['title'];
			$template->template = $el['template'];
			$this->save($template);
			$this->createWorkflow($template);
		}
	}

	/**
	 * Get Templates objects from result
	 * @param array $result
	 * @return \VTWorkflowTemplate[]
	 */
	private function getTemplatesForResult($result)
	{
		foreach ($result as $row) {
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

class VTWorkflowTemplate
{

}
