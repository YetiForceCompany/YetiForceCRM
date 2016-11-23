<?php
/* +***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 * *********************************************************************************************************************************** */

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
			return array();
		}
		vimport('~~modules/com_vtiger_workflow/VTWorkflowUtils.php');

		$editWorkflowsImagePath = Vtiger_Theme::getImagePath('EditWorkflows.png');
		$settingsLinks = array();

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
