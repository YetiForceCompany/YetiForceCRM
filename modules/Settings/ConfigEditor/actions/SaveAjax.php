<?php
/**
 * Config editor basic action file.
 *
 * @package   Settings.Action
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
/**
 * Config editor basic action class.
 */
class Settings_ConfigEditor_SaveAjax_Action extends Settings_Vtiger_Basic_Action
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
		$moduleModel = Settings_ConfigEditor_Module_Model::getInstance();
		try {
			$configFiles = [];
			foreach (array_keys($moduleModel->listFields) as $fieldName) {
				if ($request->has($fieldName)) {
					$fieldModel = $moduleModel->getFieldInstanceByName($fieldName);
					$source = $fieldModel->get('source');
					if (!isset($configFiles[$source])) {
						$configFiles[$source] = new \App\ConfigFile($source);
					}
					$configFiles[$source]->set($fieldName, $request->getRaw($fieldName));
				}
			}
			foreach ($configFiles as $configFile) {
				$configFile->create();
			}
			$response->setResult(true);
		} catch (\Throwable $e) {
			$response->setError(\App\Language::translate('LBL_ERROR', $qualifiedModuleName));
		}
		$response->emit();
	}
}
