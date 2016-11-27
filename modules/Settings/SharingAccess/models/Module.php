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

/**
 * Sharng Access Vtiger Module Model Class
 */
class Settings_SharingAccess_Module_Model extends Vtiger_Module_Model
{

	/**
	 * Constants for mapping module's Sharing Access permissions editable
	 */
	const EDITABLE = 0;
	const READONLY = 1;
	const HIDDEN = 2;

	/**
	 * Constants used for mapping module's Sharing Access Permission
	 */
	const SHARING_ACCESS_READ_ONLY = 0;
	const SHARING_ACCESS_READ_CREATE = 1;
	const SHARING_ACCESS_PUBLIC = 2;
	const SHARING_ACCESS_PRIVATE = 3;

	public function getPermissionValue()
	{
		return $this->get('permission');
	}

	/**
	 * Function checks if the sharing access for the module is enabled or not
	 * @return boolean
	 */
	public function isSharingEditable()
	{
		return ($this->get('editstatus') == self::EDITABLE);
	}

	/**
	 * Function checks if the module is Private
	 * @return Boolean
	 */
	public function isPrivate()
	{
		return ((int) $this->get('permission') == self::SHARING_ACCESS_PRIVATE);
	}

	/**
	 * Function checks if the module is Public
	 * @return Boolean
	 */
	public function isPublic()
	{
		return ($this->get('editstatus') == self::SHARING_ACCESS_PUBLIC);
	}

	public function getRulesListUrl()
	{
		return '?module=SharingAccess&parent=Settings&view=IndexAjax&mode=showRules&for_module=' . $this->getId();
	}

	public function getCreateRuleUrl()
	{
		return '?module=SharingAccess&parent=Settings&view=IndexAjax&mode=editRule&for_module=' . $this->getId();
	}

	public function getSharingRules()
	{
		return Settings_SharingAccess_Rule_Model::getAllByModule($this);
	}

	public function getRules()
	{
		return Settings_SharingAccess_Rule_Model::getAllByModule($this);
	}

	public function save()
	{
		$db = PearDatabase::getInstance();
		$sql = 'UPDATE vtiger_def_org_share SET permission = ? WHERE tabid = ?';
		$params = [$this->get('permission'), $this->getId()];
		$db->pquery($sql, $params);
	}

	/**
	 * Static Function to get the instance of Vtiger Module Model for the given id or name
	 * @param mixed id or name of the module
	 */
	public static function getInstance($value)
	{
		$db = PearDatabase::getInstance();
		$instance = false;
		$query = false;
		if (vtlib\Utils::isNumber($value)) {
			$query = 'SELECT * FROM vtiger_def_org_share INNER JOIN vtiger_tab ON vtiger_tab.tabid = vtiger_def_org_share.tabid WHERE vtiger_tab.tabid=?';
		} else {
			$query = 'SELECT * FROM vtiger_def_org_share INNER JOIN vtiger_tab ON vtiger_tab.tabid = vtiger_def_org_share.tabid WHERE name=?';
		}
		$result = $db->pquery($query, [$value]);
		if ($db->num_rows($result)) {
			$row = $db->getRow($result);
			$instance = new Settings_SharingAccess_Module_Model();
			$instance->initialize($row);
			$instance->set('permission', $row['permission']);
			$instance->set('editstatus', $row['editstatus']);
		}
		return $instance;
	}

	/**
	 * Static Function to get the instance of Vtiger Module Model for all the modules
	 * @return <Array> - List of Vtiger Module Model or sub class instances
	 */
	public static function getAll($editable = false, $restrictedModulesList = [], $isEntityType = false)
	{
		$moduleModels = [];
		$query = (new \App\Db\Query())->from('vtiger_def_org_share')
				->innerJoin('vtiger_tab', 'vtiger_tab.tabid = vtiger_def_org_share.tabid')
				->where(['vtiger_tab.presence' => [0, 2]]);
		if ($editable) {
			$query->andWhere(['editstatus' => self::EDITABLE]);
		}
		$query->orderBy(['vtiger_def_org_share.tabid' => SORT_ASC]);
		$dataReader = $query->createCommand()->query();
		while ($row = $dataReader->read()) {
			$instance = new Settings_SharingAccess_Module_Model();
			$instance->initialize($row);
			$instance->set('permission', $row['permission']);
			$instance->set('editstatus', $row['editstatus']);
			$moduleModels[$row['tabid']] = $instance;
		}
		return $moduleModels;
	}

	/**
	 * Static Function to get the instance of Vtiger Module Model for all the modules
	 * @return <Array> - List of Vtiger Module Model or sub class instances
	 */
	public static function getDependentModules()
	{
		$dependentModulesList = [];
		$dependentModulesList['Accounts'] = ['HelpDesk'];

		return $dependentModulesList;
	}

	/**
	 * Function recalculate the sharing rules
	 */
	public static function recalculateSharingRules()
	{
		$phpMaxExecutionTime = vglobal('php_max_execution_time');
		set_time_limit($phpMaxExecutionTime);
		$db = PearDatabase::getInstance();

		require_once('modules/Users/CreateUserPrivilegeFile.php');
		$result = $db->pquery('SELECT id FROM vtiger_users WHERE deleted = ?', [0]);

		while (($id = $db->getSingleValue($result)) !== false) {
			createUserSharingPrivilegesfile($id);
		}
	}
}
