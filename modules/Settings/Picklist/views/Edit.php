<?php
/**
 * Edit picklist value.
 *
 * @package Settings.View
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

/**
 * Edit picklist value class.
 */
class Settings_Picklist_Edit_View extends \App\Controller\ModalSettings
{
	/** {@inheritdoc} */
	protected $pageTitle = 'LBL_EDIT';

	/** {@inheritdoc} */
	public $modalIcon = 'yfi yfi-full-editing-view';

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
	public function process(App\Request $request)
	{
		$sourceModule = $request->getByType('source_module', \App\Purifier::ALNUM);
		$pickFieldName = $request->getByType('picklistName', \App\Purifier::ALNUM);
		$valueId = $request->getInteger('fieldValueId');
		$qualifiedName = $request->getModule(false);

		$fieldModel = Settings_Picklist_Field_Model::getInstance($pickFieldName, Vtiger_Module_Model::getInstance($sourceModule));
		$selectedFieldNonEditablePickListValues = App\Fields\Picklist::getNonEditableValues($fieldModel->getName());
		$picklistValueRow = App\Fields\Picklist::getValues($fieldModel->getName())[$valueId];

		$viewer = $this->getViewer($request);
		$picklistValueRow['picklist_valueid'] = $picklistValueRow['picklist_valueid'] ?? 0;
		$viewer->assign('EDITABLE', $fieldModel->isEditable() && !isset($selectedFieldNonEditablePickListValues[$valueId]));
		$viewer->assign('PICKLIST_VALUE', $picklistValueRow);
		$viewer->assign('SOURCE_MODULE', $sourceModule);
		$viewer->assign('SOURCE_MODULE_NAME', $sourceModule);
		$viewer->assign('FIELD_MODEL', $fieldModel);
		$viewer->assign('MODULE_NAME', $request->getModule());
		$viewer->assign('ITEM_MODEL', $fieldModel->getItemModel($valueId));
		$viewer->view('Edit.tpl', $qualifiedName);
	}
}
