<?php
/**
 * Lock save
 * @package YetiForce.DataAccess
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */

/**
 * Class DataAccess_checkType
 */
class DataAccess_checkType
{

	/**
	 * Config
	 * @var array
	 */
	public $config = false;

	/**
	 *
	 * @param string $moduleName
	 * @param int $id
	 * @param array $recordData
	 * @param array $config
	 * @return array
	 */
	public function process($moduleName, $id, $recordData, $config)
	{
		if ((empty($recordData['storage_type']) || $recordData['storage_type'] == 'PLL_INTERNAL') && empty($recordData['parentid'])) {
			$result = (new App\Db\Query())->select(['u_yf_istorages.istorageid'])->from('u_#__istorages')->innerJoin('vtiger_crmentity', 'u_yf_istorages.istorageid = vtiger_crmentity.crmid')->where(['parentid' => 0, 'vtiger_crmentity.deleted' => 0])->scalar();
			if ($result) {
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
					'title' => \App\Language::translate('LBL_FAILED_TO_APPROVE_CHANGES', 'Settings:DataAccess'),
					'text' => \App\Language::translate('LBL_NOT_PARENT_STORAGE', $moduleName),
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
