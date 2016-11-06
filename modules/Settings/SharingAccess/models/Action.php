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
 * Sharing Access Action Model Class
 */
class Settings_SharingAccess_Action_Model extends Vtiger_Base_Model
{

	static $nonConfigurableActions = array('Hide Details', 'Hide Details and Add Events', 'Show Details', 'Show Details and Add Events');

	public function getId()
	{
		return $this->get('share_action_id');
	}

	public function getName()
	{
		return $this->get('share_action_name');
	}

	public function isUtilityTool()
	{
		return false;
	}

	public function isModuleEnabled($module)
	{
		return (new \App\Db\Query())->from('vtiger_org_share_action2tab')
				->where(['tabid' => $module->getId(), 'share_action_id' => $this->getId()])
				->exists();
	}

	public static function getInstanceFromQResult($result, $rowNo = 0)
	{
		$db = PearDatabase::getInstance();
		$row = $db->query_result_rowdata($result, $rowNo);
		$actionModel = new self();
		return $actionModel->setData($row);
	}

	public static function getInstance($value)
	{
		$db = PearDatabase::getInstance();

		if (vtlib\Utils::isNumber($value)) {
			$sql = 'SELECT * FROM vtiger_org_share_action_mapping WHERE share_action_id = ?';
		} else {
			$sql = 'SELECT * FROM vtiger_org_share_action_mapping WHERE share_action_name = ?';
		}
		$params = array($value);
		$result = $db->pquery($sql, $params);
		if ($db->getRowCount($result) > 0) {
			$actionModel = new self();
			return $actionModel->setData($db->getRow($result));
		}
		return null;
	}

	public static function getAll($configurable = true)
	{
		$db = PearDatabase::getInstance();

		$sql = 'SELECT * FROM vtiger_org_share_action_mapping';
		$params = [];
		if ($configurable) {
			$sql .= sprintf(' WHERE share_action_name NOT IN (%s)', generateQuestionMarks(self::$nonConfigurableActions));
			array_push($params, self::$nonConfigurableActions);
		}
		$result = $db->pquery($sql, $params);
		$actionModels = [];
		while ($row = $db->getRow($result)) {
			$actionModel = new self();
			$actionModel->setData($row);
			$actionModels[] = $actionModel;
		}
		return $actionModels;
	}
}
