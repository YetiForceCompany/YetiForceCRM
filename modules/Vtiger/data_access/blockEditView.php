<?php

/**
  Return Description
  ------------------------
  Info type: error, info, success
  Info title: optional
  Info text: mandatory
  Type: 0 - notify
  Type: 1 - show quick create mondal
 * @package YetiForce.DataAccess
 * @license licenses/License.html
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class DataAccess_blockEditView
{

	public $config = false;

	/**
	 * Main process
	 * @param string $moduleName
	 * @param int $id
	 * @param array $recordForm
	 * @param array $config
	 * @return array
	 */
	public function process($moduleName, $id, $recordForm, $config)
	{

		return [
			'save_record' => false,
			'type' => 0,
			'info' => [
				'text' => App\Language::translate('LBL_BLOCK_EDITVIEW', 'DataAccess'),
				'type' => 'error'
			]
		];
	}

	/**
	 * Function to get config
	 * @param int $id
	 * @param string $module
	 * @param string $baseModule
	 * @return boolean
	 */
	public function getConfig($id, $module, $baseModule)
	{
		return false;
	}
}
