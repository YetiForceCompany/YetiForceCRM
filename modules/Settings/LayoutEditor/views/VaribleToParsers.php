<?php
/**
 * VaribleToParsers View Class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

/**
 * VaribleToParsers View Class.
 */
class Settings_LayoutEditor_VaribleToParsers_View extends Settings_Vtiger_BasicModal_View
{
	/**
	 * Check permission to view.
	 *
	 * @param \App\Request $request
	 *
	 * @throws \App\Exceptions\NoPermittedForAdmin
	 */
	public function checkPermission(\App\Request $request)
	{
		if (!\App\User::getCurrentUserModel()->isAdmin() || !Settings_LayoutEditor_Field_Model::getInstance($request->getInteger('fieldId'))->isEditable()) {
			throw new \App\Exceptions\NoPermittedForAdmin('LBL_PERMISSION_DENIED');
		}
	}

	/**
	 * Main proccess view.
	 *
	 * @param \App\Request $request
	 */
	public function process(\App\Request $request)
	{
		$this->preProcess($request);
		$qualifiedModuleName = $request->getModule(false);
		$viewer = $this->getViewer($request);
		$viewer->assign('VARIBLES', \App\TextParser::$variableDates);
		$viewer->assign('DEFAULT_VALUE', $request->getByType('defaultValue', 'Text'));
		$viewer->view('VaribleToParsers.tpl', $qualifiedModuleName);
		$this->postProcess($request);
	}
}
