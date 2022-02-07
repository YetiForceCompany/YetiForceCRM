<?php

/**
 * Speed test view class.
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Settings_ConfReport_Speed_View extends \App\Controller\ModalSettings
{
	/** {@inheritdoc} */
	public $showFooter = false;
	/** {@inheritdoc} */
	public $modalIcon = 'fas fa-stopwatch';

	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$qualifiedModule = $request->getModule(false);
		$viewer = $this->getViewer($request);
		$viewer->assign('BENCHMARKS', \App\Utils\Benchmarks::all());
		$viewer->assign('QUALIFIED_MODULE', $qualifiedModule);
		$viewer->view('Speed.tpl', $qualifiedModule);
	}

	/** {@inheritdoc} */
	public function getPageTitle(App\Request $request)
	{
		return \App\Language::translate('LBL_SERVER_SPEED_TEST', $request->getModule(false));
	}
}
