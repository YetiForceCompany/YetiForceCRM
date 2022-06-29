<?php

/**
 * Settings modal for RecordCollector file.
 *
 * @package Settings.View
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Sławomir Rembiesa <t.poradzewski@yetiforce.com>
 */

/**
 * Settings modal for RecordCollector class.
 */
class Settings_RecordCollector_ConfigModal_View extends \App\Controller\ModalSettings
{
	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$qualifiedModuleName = $request->getModule(false);
		$viewer = $this->getViewer($request);
		$viewer->assign('FIELDS', $this->getFields($request->getByType('recordCollectorName')));
		$viewer->view('ConfigModal.tpl', $qualifiedModuleName);
	}

	/**
	 * Function fetching fields from Record Collector and making Field Instance.
	 *
	 * @param string $recordCollectorName
	 *
	 * @return array
	 */
	private function getFields(string $recordCollectorName): array
	{
		$fields = [];
		$collectorInstance = \App\RecordCollector::getInstance('App' . DIRECTORY_SEPARATOR . 'RecordCollectors' . DIRECTORY_SEPARATOR . $recordCollectorName, 'Accounts');
		$defaultParams = ['uitype' => 1, 'value' => '', 'displaytype' => 1, 'typeofdata' => 'V~M', 'presence' => '', 'isEditableReadOnly' => false, 'maximumlength' => '255'];
		$configData = (new \App\Db\Query())->select(['params'])->from('vtiger_links')->where(['linktype' => 'EDIT_VIEW_RECORD_COLLECTOR', 'linklabel' => $recordCollectorName])->scalar();
		$configData = $configData ? \App\Json::decode($configData) : [];

		foreach ($collectorInstance->settingsFields as $fieldName => $fieldParams) {
			$fieldParams['column'] = $fieldName;
			$fieldParams['name'] = $fieldName;
			if (\array_key_exists($fieldName, $configData)) {
				$fieldParams['value'] = $configData[$fieldName];
			}
			$fields[] = Settings_Vtiger_Field_Model::init($collectorInstance->moduleName, array_merge($defaultParams, $fieldParams));
		}
		return $fields;
	}
}
