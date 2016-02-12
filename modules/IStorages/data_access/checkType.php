<?php

/**
 * Lock save
 * @package YetiForce.DataAccess
 * @license licenses/License.html
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class DataAccess_checkType
{

	var $config = false;

	public function process($moduleName, $id, $recordData, $config)
	{
		$db = PearDatabase::getInstance();
		if (empty($recordData['parentid'])) {
			$query = "SELECT COUNT(*) AS num FROM u_yf_istorages WHERE parentid='0'";
			$result = $db->query($query);
			if ($db->query_result($result, 0, 'num') > 0) {
				$save_record = false;
			} else {
				$save_record = true;
			}
		} else {
			$save_record = true;
		}
		if (!$save_record)
			return [
				'save_record' => $save_record,
				'type' => 0,
				'info' => [
					'title' => vtranslate('LBL_FAILED_TO_APPROVE_CHANGES', 'Settings:DataAccess'),
					'text' => vtranslate('LBL_NOT_PARENT_STORAGE', $moduleName),
					'type' => 'error'
				]
			];
		else
			return ['save_record' => true];
	}

	public function getConfig($id, $module, $baseModule)
	{
		return false;
	}
}
