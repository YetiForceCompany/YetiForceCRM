<?php
/**
 * EditField View file.
 *
 * @package   Settings.View
 *
 * @copyright YetiForce S.A.
 * @license YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Tomasz Kur <t.kur@yetiforce.com>
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

/**
 * EditField View Class.
 */
class Settings_LayoutEditor_EditField_View extends Settings_Vtiger_BasicModal_View
{
	/** {@inheritdoc} */
	public function getSize(App\Request $request)
	{
		return 'modal-xl';
	}

	/** {@inheritdoc} */
	public function checkPermission(App\Request $request)
	{
		parent::checkPermission($request);
		if (!Settings_LayoutEditor_Field_Model::getInstance($request->getInteger('fieldId'))->isEditable()) {
			throw new \App\Exceptions\NoPermittedForAdmin('LBL_PERMISSION_DENIED');
		}
	}

	/** {@inheritdoc} */
	public function process(App\Request $request)
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
