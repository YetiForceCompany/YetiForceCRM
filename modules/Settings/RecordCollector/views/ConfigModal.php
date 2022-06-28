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
		$recordCollectorName = $request->getByType('recordCollectorName');
		$qualifiedModuleName = $request->getModule(false);
		$viewer = $this->getViewer($request);
		$viewer->assign('FIELDS', $this->getFields($recordCollectorName, $qualifiedModuleName));
		$viewer->view('ConfigModal.tpl', $qualifiedModuleName);
	}

	/**
	 * Function fetching fields from Record Collector and making Field Instance.
	 *
	 * @param string $recordCollectorName
	 * @param string $moduleName
	 *
	 * @return array
	 */
	private function getFields($recordCollectorName, $moduleName): array
	{
		$fields = [];
		$collectorInstance = \App\RecordCollector::getInstance('App' . DIRECTORY_SEPARATOR . 'RecordCollectors' . DIRECTORY_SEPARATOR . $recordCollectorName, 'Accounts');

		foreach ($collectorInstance->getSettingsFields() as $fieldName => $fieldParams) {
			$params = ['uitype' => 1, 'column' => $fieldName, 'name' => $fieldName, 'value' => '', 'label' => \App\Language::translate('LBL_API_KEY', $moduleName), 'displaytype' => 1, 'typeofdata' => 'V~M', 'presence' => '', 'isEditableReadOnly' => false, 'maximumlength' => '255'];

			$fields[] = Settings_Vtiger_Field_Model::init($moduleName, array_merge($fieldParams, $params));
		}

		return $fields;
	}
}
