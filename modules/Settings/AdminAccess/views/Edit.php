<?php

/**
 * Edit View Class for AdminAccess.
 *
 * @package Settings.View
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */
/**
 * Settings_AdminAccess_Edit_View class.
 */
class Settings_AdminAccess_Edit_View extends \App\Controller\ModalSettings
{
	/** {@inheritdoc} */
	protected $pageTitle = 'LBL_EDIT_ADMIN_ACCESS';

	/** {@inheritdoc} */
	public $modalIcon = 'yfi yfi-full-editing-view';

	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$moduleName = $request->getModule(false);
		$recordId = $request->getInteger('id', 0);
		$recordModel = Settings_AdminAccess_Record_Model::getInstance($recordId);
		$viewer = $this->getViewer($request);
		$viewer->assign('RECORD_STRUCTURES', $this->getStructure($recordModel));
		$viewer->assign('RECORD_MODEL', $recordModel);
		$viewer->view('Edit.tpl', $moduleName);
	}

	/**
	 * The function returns module fields.
	 *
	 * @param Settings_AdminAccess_Record_Model $recordModel
	 *
	 * @return array
	 */
	public function getStructure(Settings_AdminAccess_Record_Model $recordModel): array
	{
		$structures = [];
		foreach ($recordModel->getModule()->getEditFields() as $fieldModel) {
			if (16 === $fieldModel->getUiType()) {
				$fieldModel->uitype = 33;
			}
			if ($recordModel->getId()) {
				$fieldModel->set('fieldvalue', $fieldModel->getUITypeModel()->getDBValue($recordModel->get($fieldModel->getName())));
				$fieldModel->set('isEditableReadOnly', 'name' === $fieldModel->getName());
			}
			$structures[$fieldModel->getName()] = $fieldModel;
		}
		return $structures;
	}
}
