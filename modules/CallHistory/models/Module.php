<?php

/**
 * Module model class
 * @package YetiForce.Model
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 */
class CallHistory_Module_Model extends Vtiger_Module_Model
{

	/**
	 * Function to check whether the module is an entity type module or not
	 * @return boolean true/false
	 */
	public function isQuickCreateSupported()
	{
		//PBXManager module is not enabled for quick create
		return false;
	}

	public function isWorkflowSupported()
	{
		return true;
	}

	/**
	 * Overided to make editview=false for this module
	 */
	public function isPermitted($actionName)
	{
		if ($actionName == 'EditView' || $actionName == 'Edit' || $actionName == 'CreateView')
			return false;
		else
			return ($this->isActive() && Users_Privileges_Model::isPermitted($this->getName(), $actionName));
	}

	/**
	 * Function to get Settings links
	 * @return <Array>
	 */
	public function getSettingLinks()
	{
		if (!$this->isEntityModule()) {
			return [];
		}
		Vtiger_Loader::includeOnce('~~modules/com_vtiger_workflow/VTWorkflowUtils.php');

		$editWorkflowsImagePath = Vtiger_Theme::getImagePath('EditWorkflows.png');
		$settingsLinks = [];

		if (VTWorkflowUtils::checkModuleWorkflow($this->getName())) {
			$settingsLinks[] = array(
				'linktype' => 'LISTVIEWSETTING',
				'linklabel' => 'LBL_EDIT_WORKFLOWS',
				'linkurl' => 'index.php?parent=Settings&module=Workflows&view=List&sourceModule=' . $this->getName(),
				'linkicon' => $editWorkflowsImagePath
			);
		}
		return $settingsLinks;
	}

	public function isListViewNameFieldNavigationEnabled()
	{
		return false;
	}
}
