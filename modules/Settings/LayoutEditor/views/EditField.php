<?php
/**
 * EditField View Class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */

/**
 * EditField View Class.
 */
class Settings_LayoutEditor_EditField_View extends Settings_Vtiger_BasicModal_View
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
		$fieldId = $request->getInteger('fieldId');
		$fieldModel = Settings_LayoutEditor_Field_Model::getInstance($fieldId);
		$viewer = $this->getViewer($request);
		$viewer->assign('FIELD_MODEL', $fieldModel);
		$viewer->assign('SELECTED_MODULE_NAME', $fieldModel->getModule()->getName());
		$viewer->view('EditField.tpl', $qualifiedModuleName);
		$this->postProcess($request);
	}
}
