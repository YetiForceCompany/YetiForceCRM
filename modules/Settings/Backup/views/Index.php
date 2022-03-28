<?php

/**
 * Backup class for config.
 *
 * @package Settings.View
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Dudek <a.dudek@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Settings_Backup_Index_View extends Settings_Vtiger_Index_View
{
	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$qualifiedModuleName = $request->getModule(false);
		$viewer = $this->getViewer($request);
		$viewer->assign('MODULE', $request->getModule());
		$catalogStructure = [];
		if (empty(\App\Utils\Backup::getBackupCatalogPath())) {
			$viewer->assign('CONFIG_ALERT', \App\Language::translate('ERR_CONFIG_ALERT_DESC', $qualifiedModuleName));
		} elseif (!\App\Fields\File::isAllowedDirectory(\App\Utils\Backup::getBackupCatalogPath())) {
			$viewer->assign('CONFIG_ALERT', \App\Language::translate('ERR_CONFIG_PATH_ALERT_DESC', $qualifiedModuleName));
		} else {
			$catalogPath = $request->isEmpty('catalog') ? '' : $request->getByType('catalog', \App\Purifier::PATH);
			$catalogStructure = \App\Utils\Backup::readCatalog($catalogPath);
		}
		$viewer->assign('STRUCTURE', $catalogStructure);
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
		$viewer->view('Index.tpl', $qualifiedModuleName);
	}
}
