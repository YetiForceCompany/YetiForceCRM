<?php
/**
 * Settings Password Encryption View.
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Tomasz Kur <t.kur@yetiforce.com>
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

/**
 * View to configuration of encryption.
 */
class Settings_Password_Encryption_View extends Settings_Vtiger_Index_View
{
	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$mode = $request->getMode();
		$methods = App\Encryption::getMethods();
		$lengthVectors = [];
		foreach ($methods as $methodName) {
			$lengthVectors[$methodName] = \App\Encryption::getLengthVector($methodName);
		}
		$viewer->assign('CRON_TASK', \vtlib\Cron::getInstance('LBL_BATCH_METHODS'));
		$viewer->assign('AVAILABLE_METHODS', array_diff($methods, App\Encryption::$recommendedMethods));
		$viewer->assign('MAP_LENGTH_VECTORS_METHODS', $lengthVectors);
		$viewer->assign('RECOMENDED_METHODS', array_intersect(App\Encryption::$recommendedMethods, $methods));
		if ('moduleEncryption' === $mode) {
			$modules = Settings_Password_Record_Model::getEncryptionModules();
			$viewer->assign('MODULES', $modules);
			$viewer->assign('SELECTED_MODULE', $request->has('target') ? $request->getInteger('target') : key($modules));
			$viewer->view('EncryptionModuleTab.tpl', $request->getModule(false));
		} elseif ('settingsEncryption' === $mode) {
			$viewer->view('EncryptionSettingsTab.tpl', $request->getModule(false));
		} else {
			$viewer->view('Encryption.tpl', $request->getModule(false));
		}
	}

	/** {@inheritdoc} */
	public function getFooterScripts(App\Request $request)
	{
		return array_merge(parent::getFooterScripts($request), $this->checkAndConvertJsScripts([
			"modules.Settings.{$request->getModule()}.resources.Encryption",
		]));
	}
}
