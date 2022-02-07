<?php
/**
 * Detail view.
 *
 * @package   View
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
/**
 * Occurrences_Detail_View class.
 */
class Occurrences_Detail_View extends Vtiger_Detail_View
{
	/** {@inheritdoc} */
	public function getFooterScripts(App\Request $request)
	{
		$moduleName = $request->getModule();
		return array_merge(parent::getFooterScripts($request), $this->checkAndConvertJsScripts([
			'modules.Vtiger.resources.List',
			"modules.{$moduleName}.resources.List"
		]));
	}
}
