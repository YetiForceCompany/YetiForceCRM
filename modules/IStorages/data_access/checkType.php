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
		if ((empty($recordData['storage_type']) || $recordData['storage_type'] == 'PLL_INTERNAL') && empty($recordData['parentid'])) {
			$db = PearDatabase::getInstance();
			$query = "SELECT istorageid FROM u_yf_istorages WHERE parentid='0'";
			$result = $db->query($query);
			if ($db->getRowCount($result) > 0) {
				$row = $db->getSingleValue($result);
				if (!empty($id) && $row == $id) {
					$saveRecord = true;
				} else {
					$saveRecord = false;
				}
			} else {
				$saveRecord = true;
			}
		} else {
			$saveRecord = true;
		}
		if (!$saveRecord)
			return [
				'save_record' => $saveRecord,
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
