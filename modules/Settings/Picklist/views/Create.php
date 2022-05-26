<?php
/**
 * Create picklist value.
 *
 * @package Settings.View
 *
 * @copyright YetiForce S.A.
 * @license   YetiForce Public License 5.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

/**
 * Create picklist value class.
 */
class Settings_Picklist_Create_View extends \App\Controller\ModalSettings
{
	/** {@inheritdoc} */
	protected $pageTitle = 'LBL_ADD_ITEM_TO';

	/** {@inheritdoc} */
	public $modalIcon = 'fas fa-plus';

	/** {@inheritdoc} */
	public function getPageTitle(App\Request $request)
	{
		$moduleName = $request->getModule(false);
		$sourceModule = $request->getByType('source_module', \App\Purifier::ALNUM);
		$pickFieldName = $request->getByType('picklistName', \App\Purifier::ALNUM);
		$fieldModel = Settings_Picklist_Field_Model::getInstance($pickFieldName, Vtiger_Module_Model::getInstance($sourceModule));

		return \App\Language::translate($this->pageTitle, $moduleName) . ': ' . \App\Language::translate($fieldModel->getFieldLabel(), $fieldModel->getModuleName());
	}

	/** {@inheritdoc} */
	public function process(App\Request $request)
	{
		$sourceModule = $request->getByType('source_module', \App\Purifier::ALNUM);
		$pickFieldName = $request->getByType('picklistName', \App\Purifier::ALNUM);
		$fieldModel = Settings_Picklist_Field_Model::getInstance($pickFieldName, Vtiger_Module_Model::getInstance($sourceModule));
		$qualifiedName = $request->getModule(false);

		$viewer = $this->getViewer($request);
		$viewer->assign('EDITABLE', $fieldModel->isEditable());
		$viewer->assign('PICKLIST_VALUE', []);
		$viewer->assign('SOURCE_MODULE', $sourceModule);
		$viewer->assign('SOURCE_MODULE_NAME', $sourceModule);
		$viewer->assign('FIELD_MODEL', $fieldModel);
		$viewer->assign('MODULE_NAME', $request->getModule());
		$viewer->assign('ITEM_MODEL', $fieldModel->getItemModel());
		$viewer->view('Edit.tpl', $qualifiedName);
	}
}
