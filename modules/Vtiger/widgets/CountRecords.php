<?php

/**
 * Class for count records widget.
 *
 * @package Widget
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Kur <t.kur@yetiforce.com>
 */
class Vtiger_CountRecords_Widget extends Vtiger_Basic_Widget
{
	public $allowedModules = ['Campaigns'];

	public function getUrl()
	{
		$url = 'module=' . $this->Module . '&view=Detail&record=' . $this->Record . '&mode=showCountRecords';
		if (isset($this->Data['relatedModules'])) {
			foreach ($this->Data['relatedModules'] as $module) {
				$url .= '&relatedModules[]=' . $module;
			}
		}
		return $url;
	}

	public function getWidget()
	{
		$this->Config['tpl'] = 'CountRecords.tpl';
		$this->Config['url'] = $this->getUrl();
		$this->Config['relatedModules'] = $this->Data['relatedModules'];
		return $this->Config;
	}

	public function getConfigTplName()
	{
		return 'CountRecordsConfig';
	}

	public static function getCountRecords($modules, $recordId)
	{
		$countRecords = [];
		$parentRecordModel = Vtiger_Record_Model::getInstanceById($recordId);
		foreach ($modules as $relatedModuleName) {
			$relationListView = Vtiger_RelationListView_Model::getInstance($parentRecordModel, $relatedModuleName);
			if (!\App\Module::isModuleActive($relatedModuleName) || !$relationListView->getRelationModel()) {
				continue;
			}
			$countRecords[$relatedModuleName] = (int) $relationListView->getRelatedEntriesCount();
		}
		return $countRecords;
	}
}
