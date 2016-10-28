<?php

/**
 * Lock save
 * @package YetiForce.DataAccess
 * @license licenses/License.html
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class DataAccess_checkType
{

	public $config = false;

	public function process($moduleName, $id, $recordData, $config)
	{
		if ((empty($recordData['storage_type']) || $recordData['storage_type'] == 'PLL_INTERNAL') && empty($recordData['parentid'])) {
			$db = PearDatabase::getInstance();
			$query = 'SELECT u_yf_istorages.istorageid FROM u_yf_istorages 
				INNER JOIN vtiger_crmentity ON u_yf_istorages.istorageid = vtiger_crmentity.crmid 
				WHERE parentid = ? AND vtiger_crmentity.deleted = ?';
			$result = $db->pquery($query, [0, 0]);
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
