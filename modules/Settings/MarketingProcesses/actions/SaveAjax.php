<?php

/**
 * Settings MarketingProcesses SaveAjax action class.
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
class Settings_MarketingProcesses_SaveAjax_Action extends Settings_Vtiger_Basic_Action
{
	/** {@inheritdoc} */
	public function __construct()
	{
		parent::__construct();
		$this->exposeMethod('updateConfig');
	}

	/**
	 * Update config.
	 *
	 * @param App\Request $request
	 */
	public function updateConfig(App\Request $request)
	{
		$type = $request->getByType('type', \App\Purifier::ALNUM);
		$param = $request->getByType('param');
		if ('conversion' === $type && 'mapping' === $param) {
			$value = \App\Json::encode($request->getArray('value', \App\Purifier::TEXT));
		} else {
			$value = $request->getArray('value', \App\Purifier::TEXT);
			$value = array_map('\App\Purifier::decodeHtml', $value);
		}
		$moduleModel = Settings_MarketingProcesses_Module_Model::getCleanInstance();
		$response = new Vtiger_Response();
		$response->setResult([
			'success' => $moduleModel->setConfig($param, $type, $value),
			'message' => \App\Language::translate('LBL_SAVE_CONFIG', $request->getModule(false)),
		]);
		$response->emit();
	}
}
