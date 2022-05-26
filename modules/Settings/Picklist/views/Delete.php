<?php
/**
 * Delete picklist value.
 *
 * @package Settings.View
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

/**
 * Delete picklist value class.
 */
class Settings_Picklist_Delete_View extends \App\Controller\ModalSettings
{
	/** {@inheritdoc} */
	protected $pageTitle = 'LBL_DELETE';
	/** {@inheritdoc} */
	public $modalIcon = 'fas fa-trash-alt';
	/** {@inheritdoc} */
	public $modalSize = 'modal-md';

	/** {@inheritdoc} */
	public function getPageTitle(App\Request $request)
	{
		$moduleName = $request->getModule(false);
		$sourceModule = $request->getByType('source_module', \App\Purifier::ALNUM);
		$pickFieldName = $request->getByType('picklistName', \App\Purifier::ALNUM);
		$fieldModel = Settings_Picklist_Field_Model::getInstance($pickFieldName, Vtiger_Module_Model::getInstance($sourceModule));
		$value = \App\Fields\Picklist::getValues($fieldModel->getName())[$request->getInteger('fieldValueId')]['picklistValue'];

		return \App\Language::translate($this->pageTitle, $moduleName) . ': ' . \App\Language::translate($value, $sourceModule);
	}

	/** {@inheritdoc} */
	public function checkPermission(App\Request $request)
	{
		parent::checkPermission($request);
		$sourceModule = $request->getByType('source_module', \App\Purifier::ALNUM);
		$pickFieldName = $request->getByType('picklistName', \App\Purifier::ALNUM);
		if (!\array_key_exists($request->getInteger('fieldValueId'), \App\Fields\Picklist::getEditableValues($pickFieldName)) || !Settings_Picklist_Field_Model::getInstance($pickFieldName, Vtiger_Module_Model::getInstance($sourceModule))->isEditable()) {
			throw new \App\Exceptions\NoPermittedForAdmin('LBL_PERMISSION_DENIED');
		}
	}

	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$sourceModule = $request->getByType('source_module', \App\Purifier::ALNUM);
		$pickFieldName = $request->getByType('picklistName', \App\Purifier::ALNUM);
		$valueId = $request->getInteger('fieldValueId');
		$qualifiedName = $request->getModule(false);
		$fieldModel = Settings_Picklist_Field_Model::getInstance($pickFieldName, Vtiger_Module_Model::getInstance($sourceModule));

		$viewer = $this->getViewer($request);
		$viewer->assign('EDITABLE_VALUES', App\Fields\Picklist::getEditableValues($fieldModel->getName()));
		$viewer->assign('NON_EDITABLE_VALUES', App\Fields\Picklist::getNonEditableValues($fieldModel->getName()));
		$viewer->assign('SOURCE_MODULE', $sourceModule);
		$viewer->assign('SOURCE_MODULE_NAME', $sourceModule);
		$viewer->assign('FIELD_MODEL', $fieldModel);
		$viewer->assign('MODULE_NAME', $request->getModule());
		$viewer->assign('ITEM_MODEL', $fieldModel->getItemModel($valueId));
		$viewer->view('Delete.tpl', $qualifiedName);
	}
}
