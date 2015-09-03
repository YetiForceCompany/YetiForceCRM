<?php
/**
 * Record Class for PDF Settings
 * @package YetiForce.Model
 * @license licenses/License.html
 * @author Maciej Stencel <m.stencel@yetiforce.com>
 */

class Settings_PDF_Record_Model extends Settings_Vtiger_Record_Model
{
	
	public function getId()
	{
		return $this->get('pdfid');
	}

	public function getName()
	{
		return $this->get('summary');
	}

	public function get($key)
	{
//		if($key == 'execution_condition') {
//			$executionCondition = parent::get($key);
//			$executionConditionAsLabel = Settings_PDF_Module_Model::$triggerTypes[$executionCondition];
//			return Vtiger_Language_Handler::getTranslatedString($executionConditionAsLabel, 'Settings:PDF');
//		}
//		if($key == 'module_name') {
//			$moduleName = parent::get($key);
//			return Vtiger_Language_Handler::getTranslatedString($moduleName, $moduleName);
//		}
		return parent::get($key);
	}

	public function getEditViewUrl()
	{
		return 'index.php?module=PDF&parent=Settings&view=Edit&record=' . $this->getId();
	}

	public function getTasksListUrl()
	{
		return 'index.php?module=PDF&parent=Settings&view=TasksList&record=' . $this->getId();
	}

	public function getAddTaskUrl()
	{
		return 'index.php?module=PDF&parent=Settings&view=EditTask&for_workflow=' . $this->getId();
	}

	protected function setWorkflowObject($wf)
	{
		$this->workflow_object = $wf;
		return $this;
	}

	public function getWorkflowObject()
	{
		return $this->workflow_object;
	}

	public function getModule()
	{
		return $this->module;
	}

	public function setModule($moduleName)
	{
		$this->module = Vtiger_Module_Model::getInstance($moduleName);
		return $this;
	}

	/**
	 * Function to get the list view actions for the record
	 * @return <Array> - Associate array of Vtiger_Link_Model instances
	 */
	public function getRecordLinks()
	{

		$links = array();

		$recordLinks = array(
//			array(
//				'linktype' => 'LISTVIEWRECORD',
//				'linklabel' => 'LBL_ACTIVATION_TASKS',
//				'linkurl' => 'javascript:Settings_PDF_List_Js.setChangeStatusTasks(this,' . $this->getId() . ',true);',
//				'linkicon' => 'glyphicon glyphicon-ok',
//				'class' => 'activeTasks'
//			),
//			array(
//				'linktype' => 'LISTVIEWRECORD',
//				'linklabel' => 'LBL_DEACTIVATION_TASKS',
//				'linkurl' => 'javascript:Settings_PDF_List_Js.setChangeStatusTasks(this,' . $this->getId() . ', false);',
//				'linkicon' => 'glyphicon glyphicon-remove',
//				'class' => 'deactiveTasks'
//			),
			array(
				'linktype' => 'LISTVIEWRECORD',
				'linklabel' => 'LBL_EDIT_RECORD',
				'linkurl' => $this->getEditViewUrl(),
				'linkicon' => 'glyphicon glyphicon-pencil'
			),
			array(
				'linktype' => 'LISTVIEWRECORD',
				'linklabel' => 'LBL_DELETE_RECORD',
				'linkurl' => 'javascript:Vtiger_List_Js.deleteRecord(' . $this->getId() . ');',
				'linkicon' => 'glyphicon glyphicon-trash'
			)
		);
		foreach ($recordLinks as $recordLink) {
			$links[] = Vtiger_Link_Model::getInstanceFromValues($recordLink);
		}

		return $links;
	}
	
	public static function getCleanInstance($moduleName)
	{
		$pdf = new self;
		$pdf->set('pdfid', '');
		$pdf->set('module_name', $moduleName);
		$pdf->set('summary', '');
		$pdf->set('cola', '');
		$pdf->set('colb', '');
		$pdf->set('colc', '');
		$pdf->set('cold', '');
		return $pdf;
	}

	public function save()
	{
		$db = PearDatabase::getInstance();
		
		if (!$this->getId()) {
			$query = 'INSERT INTO `a_yf_pdf` (`module_name`, `summary`, `cola`, `colb`, `colc`, `cold`) VALUES (?, ?, ?, ?, ?, ?);';
			$params = [$this->get('module_name'),$this->get('summary'),$this->get('cola'),$this->get('colb'),$this->get('colc'),$this->get('cold')];
			$result = $db->pquery($query, $params);
			
			$this->set('pdfid', $db->getLastInsertID());
			
			return $this->get('pdfid');
		}
	}
}
