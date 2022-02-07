<?php

/**
 * Occurrences calendar view class.
 *
 * @package View
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
/**
 * Occurrences_Calendar_View class.
 */
class Occurrences_Calendar_View extends Vtiger_Calendar_View
{
	/** {@inheritdoc} */
	public function postProcess(App\Request $request, $display = true)
	{
		$viewer = $this->getViewer($request);
		$viewer->assign('SHOW_TYPE', true);
		parent::postProcess($request);
	}
}
