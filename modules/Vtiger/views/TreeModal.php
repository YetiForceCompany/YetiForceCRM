<?php

/**
 * Base tree modal view class.
 *
 * @package View
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
class Vtiger_TreeModal_View extends \App\Controller\Modal
{
	/**
	 * The name of the success button.
	 *
	 * @var string
	 */
	public $successBtn = 'LBL_SELECT_OPTION';
	/**
	 * Field model.
	 *
	 * @var Vtiger_Field_Model
	 */
	public $fieldModel;

	/** {@inheritdoc} */
	public function checkPermission(App\Request $request)
	{
		$userModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if (!$userModel->hasModulePermission($request->getModule())) {
			throw new \App\Exceptions\NoPermitted('LBL_PERMISSION_DENIED', 406);
		}

		$this->fieldModel = Vtiger_Module_Model::getInstance($request->getModule())->getFieldByName($request->getByType('fieldName', 2));
		if (!$this->fieldModel || !$this->fieldModel->isViewable() || !\in_array($this->fieldModel->getFieldDataType(), ['categoryMultipicklist', 'tree'])) {
			throw new \App\Exceptions\NoPermitted('LBL_NO_PERMISSIONS_TO_FIELD');
		}
	}

	/** {@inheritdoc} */
	public function preProcessAjax(App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$viewer->assign('FIELD_INSTANCE', $this->fieldModel);
		$this->pageTitle = $this->fieldModel->getFieldLabel();
		parent::preProcessAjax($request);
	}

	/** {@inheritdoc} */
	protected function preProcessTplName(App\Request $request)
	{
		return 'Modals/TreeHeader.tpl';
	}

	/**
	 * Tree in popup.
	 *
	 * @param \App\Request $request
	 */
	public function process(App\Request $request)
	{
		$moduleName = $request->getModule();
		$type = false;
		$templateId = (int) $this->fieldModel->getFieldParams();
		$recordModel = Settings_TreesManager_Record_Model::getInstanceById($templateId);
		if (!$recordModel) {
			throw new \App\Exceptions\AppException(\App\Language::translate('ERR_TREE_NOT_FOUND', $moduleName));
		}
		if ($request->getBoolean('multiple')) {
			$type = 'category';
		} else {
			$this->successBtn = '';
		}
		$viewer = $this->getViewer($request);
		$viewer->assign('TREE', \App\Json::encode($recordModel->getTree($type, $request->getByType('value', 'Text'))));
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('IS_MULTIPLE', $request->getBoolean('multiple'));
		$viewer->view('Modals/TreeModal.tpl', $moduleName);
	}

	/** {@inheritdoc} */
	public function getModalScripts(App\Request $request)
	{
		$moduleName = $request->getModule();
		$jsFileNames = ['~libraries/jstree/dist/jstree.js'];
		if ($request->getBoolean('multiple')) {
			$jsFileNames[] = '~layouts/resources/libraries/jstree.category.js';
			$jsFileNames[] = '~layouts/resources/libraries/jstree.checkbox.js';
		}
		$jsFileNames = array_merge($jsFileNames, [
			'modules.Vtiger.resources.TreeModal',
			"modules.$moduleName.resources.TreeModal",
		]);
		return array_merge(parent::getModalScripts($request), $this->checkAndConvertJsScripts($jsFileNames));
	}

	/** {@inheritdoc} */
	public function getModalCss(App\Request $request)
	{
		return array_merge(parent::getModalCss($request), $this->checkAndConvertCssStyles([
			'~libraries/jstree-bootstrap-theme/dist/themes/proton/style.css',
		]));
	}
}
