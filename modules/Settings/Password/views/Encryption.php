<?php
/**
 * Settings Password Encryption View.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */

/**
 * View to configuration of encryption.
 */
class Settings_Password_Encryption_View extends Settings_Vtiger_Index_View
{
	/**
	 * {@inheritdoc}
	 */
	public function process(\App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$encryptionInstance = App\Encryption::getInstance();
		$methods = App\Encryption::getMethods();
		$lengthVectors = [];
		foreach ($methods as $methodName) {
			$lengthVectors[$methodName] = \App\Encryption::getLengthVector($methodName);
		}
		$recomendedMethods = App\Encryption::$recomendedMethods;
		$viewer->assign('ENCRYPT', $encryptionInstance);
		$viewer->assign('CRON_TASK', \vtlib\Cron::getInstance('LBL_BATCH_METHODS'));
		$viewer->assign('AVAILABLE_METHODS', array_diff($methods, $recomendedMethods));
		$viewer->assign('MAP_LENGTH_VECTORS_METHODS', $lengthVectors);
		$viewer->assign('RECOMENDED_METHODS', array_intersect($recomendedMethods, $methods));
		$viewer->assign('IS_RUN_ENCRYPT', Settings_Password_Record_Model::isRunEncrypt());
		$viewer->view('Encryption.tpl', $request->getModule(false));
	}

	/**
	 * {@inheritdoc}
	 */
	public function getFooterScripts(\App\Request $request)
	{
		return array_merge(parent::getFooterScripts($request), $this->checkAndConvertJsScripts([
				"modules.Settings.{$request->getModule()}.resources.Encryption",
		]));
	}
}
