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
 * @copyright YetiForce Sp. z o.o.
 * @license YetiForce Public License 2.0 (licenses/License.html or yetiforce.com)
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class DataAccess_blockEditView
{

	public $config = true;

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
		$message = 'LBL_BLOCK_EDITVIEW';
		if (!empty($config['message'])) {
			$message = $config['message'];
		}
		return [
			'save_record' => false,
			'type' => 0,
			'info' => [
				'text' => App\Language::translate($message, 'DataAccess'),
				'type' => 'error'
			]
		];
	}

	/**
	 * Function to get config
	 * @param int $id
	 * @param string $module
	 * @param string $baseModule
	 * @return array
	 */
	public function getConfig($id, $module, $baseModule)
	{
		return [];
	}
}
