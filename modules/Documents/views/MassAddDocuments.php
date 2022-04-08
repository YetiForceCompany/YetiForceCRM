<?php

/**
 * Action to mass upload files.
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */
class Documents_MassAddDocuments_View extends Vtiger_BasicModal_View
{
	/**
	 * Function to check permission.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\NoPermitted
	 */
	public function checkPermission(App\Request $request)
	{
		if (!\App\Privilege::isPermitted($request->getModule(), 'CreateView')) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}
	}

	/**
	 * Process.
	 *
	 * @param \App\Request $request
	 */
	public function process(App\Request $request)
	{
		parent::preProcess($request);
		$moduleName = $request->getModule();
		$viewer = $this->getViewer($request);
		$viewer->assign('MODULE', $moduleName);
		if ($request->has('sourceModule')) {
			$viewer->assign('SOURCE_MODULE', $request->getByType('sourceModule', \App\Purifier::ALNUM));
		}
		if ($request->has('sourceRecord')) {
			$viewer->assign('SOURCE_RECORD', $request->getInteger('sourceRecord'));
		}
		$viewer->view('MassAddDocuments.tpl', $moduleName);
		parent::postProcess($request);
	}
}
