<?php

/**
 * Class to get category tree
 * @package YetiForce.WebserviceAction
 * @license licenses/License.html
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class API_TreesManager_GetTree extends BaseAction
{

	protected $requestMethod = ['GET'];

	private function getTemplateId($moduleName)
	{
		$moduleId = vtlib\Functions::getModuleId($moduleName);
		$db = PearDatabase::getInstance();
		$query = 'SELECT templateid FROM vtiger_trees_templates WHERE module = ?';
		return $db->getSingleValue($db->pquery($query, [$moduleId]));
	}

	public function get($moduleName)
	{
		$recordModel = Settings_TreesManager_Record_Model::getInstanceById($this->getTemplateId($moduleName));
		if($recordModel){
			return $recordModel->getTree();
		} else {
			throw new APIException('ERR_NOT_FOUND_TREE', 500);
		}
	}
}
