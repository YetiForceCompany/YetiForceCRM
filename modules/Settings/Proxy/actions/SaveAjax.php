<?php
/**
 * Settings proxy save ajax action.
 *
 * @package   Settings.Action
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Arkadiusz Sołek <a.solek@yetiforce.com>
 */
class Settings_Proxy_SaveAjax_Action extends Settings_Vtiger_Basic_Action
{
	/**
	 * Process.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \ReflectionException
	 */
	public function process(App\Request $request)
	{
		$response = new Vtiger_Response();
		$qualifiedModuleName = $request->getModule(false);
		$moduleModel = Settings_Proxy_Module_Model::getInstance();
		try {
			$configFile = new \App\ConfigFile('security');
			foreach (array_keys($moduleModel->listFields) as $fieldName) {
				if ($request->has($fieldName)) {
					if ($request->getBoolean($fieldName)) {
						$proxyValue = $request->getBoolean($fieldName);
					} else {
						$proxyValue = $request->getByType($fieldName, 'Text');
					}
					$configFile->set($fieldName, $proxyValue);
				}
			}
			$configFile->create();
			$response->setResult(true);
		} catch (\Throwable $e) {
			$response->setError(\App\Language::translate('LBL_ERROR', $qualifiedModuleName));
		}
		$response->emit();
	}
}
