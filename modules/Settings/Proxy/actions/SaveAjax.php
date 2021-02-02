<?php
/**
 * Settings proxy save ajax action file.
 *
 * @package   Settings.Action
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Arkadiusz Sołek <a.solek@yetiforce.com>
 */
/**
 * Settings proxy save ajax action class.
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
		$params = $request->getMultiDimensionArray('param', [
			'proxyConnection' => 'Boolean',
			'proxyProtocol' => 'Text',
			'proxyHost' => 'Text',
			'proxyPort' => 'Integer',
			'proxyLogin' => 'Text',
			'proxyPassword' => 'Text',
		]);
		try {
			$configFile = new \App\ConfigFile('security');
			foreach ($params as $fieldName => $value) {
				$configFile->set($fieldName, $value);
			}
			$configFile->create();
			$response->setResult(['success' => true]);
		} catch (\Throwable $e) {
			$response->setError(\App\Language::translate('LBL_ERROR', $qualifiedModuleName));
		}
		$response->emit();
	}
}
