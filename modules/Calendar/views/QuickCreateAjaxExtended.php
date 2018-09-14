<?php

/**
 * Calendar QuickCreateAjax Class.
 *
 * @package   View
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Adach <a.adach@yetiforce.com>
 */
class Calendar_QuickCreateAjaxExtended_View extends Vtiger_QuickCreateAjax_View
{
	/**
	 * {@inheritdoc}
	 */
	public function process(\App\Request $request)
	{
		parent::process($request);
	}

	/**
	 * {@inheritdoc}
	 */
	public function postProcessAjax(\App\Request $request)
	{
		$viewer = $this->getViewer($request);
		echo $viewer->view('Extended/QuickCreate.tpl', $request->getModule(), true);
		parent::postProcessAjax($request);
	}
}
