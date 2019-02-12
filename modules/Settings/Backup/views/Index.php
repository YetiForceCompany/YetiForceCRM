<?php

/**
 * Backup class for config.
 *
 * @package   View
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Dudek <a.dudek@yetiforce.com>
 */
class Settings_Backup_Index_View extends Settings_Vtiger_Index_View
{
	/**
	 * {@inheritdoc}
	 */
	public function process(\App\Request $request)
	{
		$qualifiedModuleName = $request->getModule(false);
		$viewer = $this->getViewer($request);
		$viewer->assign('MODULE', $request->getModule());
		if (empty(\App\Utils\Backup::getBackupCatalogPath())) {
			$viewer->assign('SHOW_CONFIG_ALERT', true);
			$catalogStructure = [];
		} else {
			$catalogPath = $request->isEmpty('catalog') ? '' : $request->getByType('catalog', 'Path');
			$catalogStructure = \App\Utils\Backup::readCatalog($catalogPath);
		}
		$viewer->assign('STRUCTURE', $catalogStructure);
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
		$viewer->view('Index.tpl', $qualifiedModuleName);
	}
}
