<?php
/**
 * EditField View Class
 * @package YetiForce.Settings.View
 * @license licenses/License.html
 * @author Tomasz Kur <t.kur@yetiforce.com>
 */

/**
 * EditField View Class
 */
class Settings_LayoutEditor_EditField_View extends Settings_Vtiger_BasicModal_View
{
	/**
	 * Check permission to view
	 * @param \App\Request $request
	 * @throws \Exception\NoPermittedForAdmin
	 */
	public function checkPermission(\App\Request $request)
	{
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		if (!$currentUserModel->isAdminUser() && !Settings_LayoutEditor_Field_Model::getInstance($request->get('fieldId')->isEditable())) {
			throw new \Exception\NoPermittedForAdmin('LBL_PERMISSION_DENIED');
		}
	}
	
	/**
	 * Main proccess view
	 * @param \App\Request $request
	 */
	public function process(\App\Request $request)
	{
		$this->preProcess($request);
		$qualifiedModuleName = $request->getModule(false);
		$fieldId = $request->get('fieldId');
		$fieldModel = Settings_LayoutEditor_Field_Model::getInstance($fieldId);
		$viewer = $this->getViewer($request);
		$viewer->assign('FIELD_MODEL', $fieldModel);
		$viewer->assign('SELECTED_MODULE_NAME', $fieldModel->getModule()->getName());
		$viewer->view('EditField.tpl', $qualifiedModuleName);
		$this->postProcess($request);
	}
}
